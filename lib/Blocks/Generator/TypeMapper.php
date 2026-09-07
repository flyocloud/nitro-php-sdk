<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * Turns one OpenAPI schema node into the PHPDoc type that describes it.
 *
 * Object nodes become inline `object{...}` shapes rather than named classes: the runtime value is
 * a `\stdClass` (the API layer decodes with `json_decode($json, false)` and ObjectSerializer
 * passes `object`-typed values through untouched), and a shape describes exactly that without
 * inventing a type name nothing is an instance of.
 *
 * Output may be multiline, indented four spaces per nesting level and never trailing-newline
 * terminated. {@see Renderer} prefixes each line for the docblock it lands in.
 */
final class TypeMapper
{
    /**
     * Hard nesting cap. A malformed or pathological document must not exhaust the stack or emit a
     * megabyte of type.
     */
    public const MAX_DEPTH = 32;

    private const INDENT = '    ';

    /** A shape at or under this rendered length stays on one line. */
    private const MAX_ONE_LINE = 64;

    /**
     * `null` sorts last so a union reads the same way as the `|null` nullability suffix; the rest
     * are alphabetical for stability.
     */
    private const NULL_RANK = 1;

    /**
     * @param array<string, string> $blockClasses schema key => fully qualified generated class
     */
    public function __construct(
        private readonly Document $document,
        private readonly array $blockClasses,
        private readonly Warnings $warnings,
    ) {
    }

    /**
     * The type of a property, including its nullability.
     *
     * OpenAPI marks presence with the enclosing schema's `required` list, so a property absent
     * from it may legitimately be missing and is therefore nullable. Today the server emits no
     * `required` at all, which makes everything nullable; adding it server-side relaxes this with
     * no change here.
     *
     * @param array<string, mixed> $schema
     * @param list<string> $path property path, for warning messages
     * @param list<string> $refs the `$ref` chain walked so far, for cycle detection
     */
    public function propertyType(
        array $schema,
        array $path,
        bool $required,
        int $depth = 0,
        array $refs = [],
    ): string {
        $type = $this->type($schema, $path, $depth, $refs);

        if ($type === 'mixed') {
            return $type;
        }

        $nullable = !$required || ($schema['nullable'] ?? null) === true;

        if (!$nullable || $this->endsWithNull($type)) {
            return $type;
        }

        return $type . '|null';
    }

    /**
     * The type of a schema node, ignoring nullability.
     *
     * @param array<string, mixed> $schema
     * @param list<string> $path
     * @param list<string> $refs the `$ref` chain walked so far, for cycle detection
     */
    public function type(array $schema, array $path, int $depth = 0, array $refs = []): string
    {
        if ($depth > self::MAX_DEPTH) {
            $this->warnings->add(sprintf(
                '%s: nested deeper than %d levels; emitting "mixed" there.',
                self::describe($path),
                self::MAX_DEPTH,
            ));

            return 'mixed';
        }

        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            return $this->ref($schema['$ref'], $path, $depth, $refs);
        }

        foreach (['oneOf', 'anyOf'] as $key) {
            $members = $schema[$key] ?? null;
            if (is_array($members) && $members !== []) {
                $mapped = [];
                foreach ($members as $member) {
                    $mapped[] = is_array($member)
                        ? $this->type($member, $path, $depth + 1, $refs)
                        : 'mixed';
                }

                return $this->union($mapped);
            }
        }

        $allOf = $schema['allOf'] ?? null;
        if (is_array($allOf) && $allOf !== []) {
            return $this->allOf($allOf, $path, $depth, $refs);
        }

        $type = $schema['type'] ?? null;

        // OpenAPI 3.1 allows a list of types. Not emitted by Flyo today; handled defensively.
        if (is_array($type)) {
            $mapped = [];
            foreach ($type as $single) {
                $mapped[] = is_string($single)
                    ? $this->scalar($single, $schema, $path, $depth, $refs)
                    : 'mixed';
            }

            return $this->union($mapped);
        }

        if (!is_string($type)) {
            // An `enum` pins the value down even when `type` is absent.
            $literals = $this->literalUnion($schema);

            // Otherwise: a mapping column with no declared output group has a title and nothing
            // else, so the value really can be anything.
            return $literals ?? 'mixed';
        }

        return $this->scalar($type, $schema, $path, $depth, $refs);
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $path
     * @param list<string> $refs
     */
    private function scalar(string $type, array $schema, array $path, int $depth, array $refs): string
    {
        return match ($type) {
            'string' => $this->stringType($schema),
            'integer' => $this->intType($schema),
            // The server uses `number` for integral values (zip: 5000, id: 154311) as well as
            // fractional ones (cords_lat: 47.39), and json_decode reflects that difference.
            'number' => 'int|float',
            'boolean' => 'bool',
            'null' => 'null',
            'object' => $this->objectType($schema, $path, $depth, $refs),
            'array' => $this->arrayType($schema, $path, $depth, $refs),
            default => 'mixed',
        };
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function stringType(array $schema): string
    {
        return $this->literalUnion($schema) ?? 'string';
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function intType(array $schema): string
    {
        $min = $schema['minimum'] ?? null;
        $max = $schema['maximum'] ?? null;

        if (is_int($min) && is_int($max) && $min <= $max) {
            return sprintf('int<%d, %d>', $min, $max);
        }

        return 'int';
    }

    /**
     * An `enum` rendered as a union of literal strings, or null when that is not safe.
     *
     * @param array<string, mixed> $schema
     */
    private function literalUnion(array $schema): ?string
    {
        $enum = $schema['enum'] ?? null;
        if (!is_array($enum) || $enum === []) {
            return null;
        }

        $literals = [];
        foreach ($enum as $value) {
            // A block id may be a number even though the property is typed string.
            if (!is_scalar($value)) {
                return null;
            }

            $string = is_bool($value) ? ($value ? '1' : '') : (string) $value;

            // A newline would break out of the docblock; not worth escaping for.
            if (preg_match('/[\r\n]/', $string) === 1) {
                return null;
            }

            $literal = "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $string) . "'";
            if (!in_array($literal, $literals, true)) {
                $literals[] = $literal;
            }
        }

        return implode('|', $literals);
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $path
     * @param list<string> $refs
     */
    private function objectType(array $schema, array $path, int $depth, array $refs): string
    {
        $properties = $schema['properties'] ?? null;

        // `additionalProperties` with no `properties` is a deliberate open map: `routes`, a link's
        // `extras` and a pool's `data` rows all vary by context. See the comment on
        // OpenApiHelper::nitroRoutesSchema(), which keeps them property-less on purpose.
        if (!is_array($properties) || $properties === []) {
            return '\stdClass';
        }

        $required = self::requiredKeys($schema);
        $lines = [];

        foreach ($properties as $name => $propSchema) {
            // A numeric-looking JSON key arrives as an int.
            $name = (string) $name;

            if (!is_array($propSchema)) {
                continue;
            }

            $lines[] = self::key($name) . ': ' . $this->propertyType(
                $propSchema,
                [...$path, $name],
                in_array($name, $required, true),
                $depth + 1,
                $refs,
            );
        }

        if ($lines === []) {
            return '\stdClass';
        }

        // A small shape reads better on one line. Purely length-driven, so it stays deterministic,
        // and because inner shapes are rendered first an outer one collapses whenever its children
        // did.
        $oneLine = 'object{' . implode(', ', $lines) . '}';
        if (!str_contains($oneLine, "\n") && mb_strlen($oneLine) <= self::MAX_ONE_LINE) {
            return $oneLine;
        }

        return "object{\n" . self::indent(implode(",\n", $lines)) . "\n}";
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $path
     * @param list<string> $refs
     */
    private function arrayType(array $schema, array $path, int $depth, array $refs): string
    {
        $items = $schema['items'] ?? null;

        if (!is_array($items) || $items === []) {
            return 'array<int, mixed>';
        }

        return 'array<int, ' . $this->type($items, $path, $depth + 1, $refs) . '>';
    }

    /**
     * @param list<string> $path
     * @param list<string> $refs
     */
    private function ref(string $ref, array $path, int $depth, array $refs): string
    {
        $key = $this->document->refKey($ref);

        if ($key === null) {
            $this->warnings->add(sprintf(
                '%s: cannot resolve the reference "%s"; emitting "mixed" there.',
                self::describe($path),
                $ref,
            ));

            return 'mixed';
        }

        // Slot content references the generic block schema, which is already a real SDK model.
        if ($key === Document::GENERIC_BLOCK_KEY) {
            return '\Flyo\Model\Block';
        }

        if (isset($this->blockClasses[$key])) {
            return '\\' . ltrim($this->blockClasses[$key], '\\');
        }

        if (in_array($key, $refs, true)) {
            $this->warnings->add(sprintf(
                '%s: the reference "%s" is part of a cycle; emitting "mixed" there.',
                self::describe($path),
                $ref,
            ));

            return 'mixed';
        }

        $target = $this->document->schema($key);

        if ($target === null) {
            $this->warnings->add(sprintf(
                '%s: the reference "%s" points at a schema that is not in the document; '
                    . 'emitting "mixed" there.',
                self::describe($path),
                $ref,
            ));

            return 'mixed';
        }

        return $this->type($target, $path, $depth + 1, [...$refs, $key]);
    }

    /**
     * Merges `allOf` members into one shape, which only works when every member is an object with
     * declared properties.
     *
     * @param array<int|string, mixed> $members
     * @param list<string> $path
     * @param list<string> $refs
     */
    private function allOf(array $members, array $path, int $depth, array $refs): string
    {
        /** @var array<string, mixed> $properties */
        $properties = [];
        /** @var list<string> $required */
        $required = [];

        foreach ($members as $member) {
            if (!is_array($member)) {
                return $this->unmergeable($path);
            }

            if (isset($member['$ref']) && is_string($member['$ref'])) {
                $key = $this->document->refKey($member['$ref']);
                $resolved = $key === null ? null : $this->document->schema($key);

                if ($resolved === null || in_array($key, $refs, true)) {
                    return $this->unmergeable($path);
                }

                $refs = [...$refs, (string) $key];
                $member = $resolved;
            }

            $memberProperties = $member['properties'] ?? null;

            if (($member['type'] ?? null) !== 'object' || !is_array($memberProperties)) {
                return $this->unmergeable($path);
            }

            $properties = array_merge($properties, $memberProperties);
            $required = [...$required, ...self::requiredKeys($member)];
        }

        if ($properties === []) {
            return '\stdClass';
        }

        return $this->objectType(
            ['type' => 'object', 'properties' => $properties, 'required' => $required],
            $path,
            $depth,
            $refs,
        );
    }

    /**
     * @param list<string> $path
     */
    private function unmergeable(array $path): string
    {
        $this->warnings->add(sprintf(
            '%s: "allOf" members are not all objects with declared properties, so they cannot be '
                . 'merged into one shape; emitting "mixed" there.',
            self::describe($path),
        ));

        return 'mixed';
    }

    /**
     * Joins union members deterministically.
     *
     * Members that are shapes cannot be split on "|" (their own property types contain it), so
     * they are deduplicated whole and left in document order.
     *
     * @param list<string> $members
     */
    private function union(array $members): string
    {
        $members = array_values(array_filter($members, static fn (string $m): bool => $m !== ''));

        if ($members === []) {
            return 'mixed';
        }

        if (in_array('mixed', $members, true)) {
            return 'mixed';
        }

        foreach ($members as $member) {
            if (str_contains($member, '{') || str_contains($member, "\n")) {
                return implode('|', array_values(array_unique($members)));
            }
        }

        $atoms = [];
        foreach ($members as $member) {
            foreach (explode('|', $member) as $atom) {
                if ($atom !== '' && !in_array($atom, $atoms, true)) {
                    $atoms[] = $atom;
                }
            }
        }

        usort($atoms, static function (string $a, string $b): int {
            $rankA = $a === 'null' ? self::NULL_RANK : 0;
            $rankB = $b === 'null' ? self::NULL_RANK : 0;

            return $rankA === $rankB ? strcmp($a, $b) : $rankA <=> $rankB;
        });

        return implode('|', $atoms);
    }

    private function endsWithNull(string $type): bool
    {
        return $type === 'null' || str_ends_with($type, '|null');
    }

    /**
     * A shape key, quoted when it is not a bare PHP identifier (`my-field`, `length (cm)`).
     */
    private static function key(string $name): string
    {
        if (preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name) === 1) {
            return $name;
        }

        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $name) . "'";
    }

    /**
     * @param array<int|string, mixed> $schema
     * @return list<string>
     */
    private static function requiredKeys(array $schema): array
    {
        $required = $schema['required'] ?? null;

        if (!is_array($required)) {
            return [];
        }

        $keys = [];
        foreach ($required as $key) {
            if (is_string($key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    private static function indent(string $block): string
    {
        return self::INDENT . str_replace("\n", "\n" . self::INDENT, $block);
    }

    /**
     * @param list<string> $path
     */
    private static function describe(array $path): string
    {
        return $path === [] ? '(document root)' : implode('.', $path);
    }
}
