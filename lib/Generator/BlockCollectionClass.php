<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * The class narrowing \Flyo\Model\BlockCollection for one block, from the `collection` property of
 * its typed schema.
 *
 * Unlike `content`, `config` and `items`, the collection is hydrated into SDK models (the
 * collection, its links and every facet value), so it can not be described by an inline shape. It
 * gets a class of its own instead, which the block class returns from getCollection().
 *
 * Only the getters the typed schema says more about than the SDK model are overridden: a getter
 * whose type would be the model's own is left alone. That keeps the class in step with the server,
 * which pins more of the collection per block over time.
 */
final class BlockCollectionClass
{
    /** The SDK model every collection class extends. */
    public const PARENT_CLASS = '\Flyo\Model\BlockCollection';

    /** Appended to the name of the block class. */
    public const SUFFIX = 'Collection';

    private const LINKS_CLASS = '\Flyo\Model\BlockCollectionLinks';

    private const FACET_VALUE_CLASS = '\Flyo\Model\BlockCollectionFacetsValue';

    private function __construct()
    {
    }

    /**
     * The class name of the collection of each block that gets one, keyed by schema name.
     *
     * A block gets one when its items are a collection, or when it aggregates facets: a block
     * whose collection schema only says that it is none would get a class with nothing to tell.
     *
     * The block classes keep their names: a collection class whose name is taken by a block is
     * numbered instead, so a block class never changes its name because another block became a
     * collection.
     *
     * @param array<string, array<string, mixed>> $schemas block schemas, sorted by key
     * @param array<string, string> $blockClassNames schema key => the settled block class name
     * @return array<string, string>
     */
    public static function classNames(array $schemas, array $blockClassNames, Warnings $warnings): array
    {
        $taken = array_fill_keys(array_values($blockClassNames), true);
        $names = [];

        foreach ($schemas as $schemaKey => $schema) {
            $collection = Schema::property($schema, 'collection');

            if ($collection === null || !self::wanted($collection)) {
                continue;
            }

            $preferred = $blockClassNames[$schemaKey] . self::SUFFIX;
            $name = $preferred;
            $suffix = 2;
            while (isset($taken[$name])) {
                $name = $preferred . $suffix++;
            }

            if ($name !== $preferred) {
                $warnings->add(sprintf(
                    "The collection of the block schema %s wants the class name %s, which a block already has:\n"
                        . "  %s -> %s\n  Rename the block component in Flyo to remove the ambiguity.",
                    $schemaKey,
                    $preferred,
                    $schemaKey,
                    $name,
                ));
            }

            $taken[$name] = true;
            $names[$schemaKey] = $name;
        }

        return $names;
    }

    /**
     * @param array<string, mixed> $schema the block schema
     * @param array<string, mixed> $collection its `collection` property
     */
    public static function classDef(
        string $schemaKey,
        array $schema,
        array $collection,
        string $className,
        string $blockClassName,
        string $namespace,
        TypeMapper $types,
    ): ClassDef {
        $path = [$schemaKey, 'collection'];
        $sortable = self::fieldList($collection, 'sortable');
        $filterable = self::fieldList($collection, 'filterable');
        $facets = self::facetFields($collection);

        $constants = [];
        if ($sortable !== null && $sortable !== []) {
            $constants[] = new ConstantDef('SORTABLE', $sortable);
        }
        if ($filterable !== null && $filterable !== []) {
            $constants[] = new ConstantDef('FILTERABLE', $filterable);
        }
        if ($facets !== null && $facets !== []) {
            $constants[] = new ConstantDef('FACETS', $facets);
        }

        $methods = [];
        foreach (self::types($collection, $path, $types) as $property => [$getter, $type]) {
            $methods[] = new MethodDef(
                $getter,
                $type,
                Schema::text(Schema::property($collection, $property) ?? [], 'description'),
            );
        }

        $identifier = Naming::enumValue($schema, 'identifier');
        $component = Naming::enumValue($schema, 'component');
        $block = $identifier === ''
            ? $schemaKey
            : '"' . $identifier . '"' . ($component === '' ? '' : ' (component: ' . $component . ')');

        return new ClassDef(
            $className,
            $namespace,
            self::PARENT_CLASS,
            Schema::text($collection, 'title'),
            Schema::text($collection, 'description'),
            ['The collection of the block ' . $block . ', returned by ' . $blockClassName . '::getCollection().'],
            'Documentation-only type: at runtime a collection is always a ' . self::PARENT_CLASS
                . ', and its links and facet values are SDK models as well. Reach it through the block '
                . 'class or annotate a variable with @var; do not instantiate this class.'
                . ($constants === [] ? '' : ' The constants can be used at runtime.'),
            $constants,
            $methods,
        );
    }

    /**
     * Whether the collection schema of a block says enough to give it a class of its own.
     *
     * @param array<string, mixed> $collection
     */
    private static function wanted(array $collection): bool
    {
        $enabled = Schema::property($collection, 'enabled');

        return ($enabled !== null && ($enabled['enum'] ?? null) === [true])
            || (self::facetFields($collection) ?? []) !== [];
    }

    /**
     * The narrowed getters, in the order of the SDK model, each with its type.
     *
     * @param array<string, mixed> $collection
     * @param list<string> $path
     * @return array<string, array{string, string}> property => [getter, type]
     */
    private static function types(array $collection, array $path, TypeMapper $types): array
    {
        $narrowed = [];
        $required = Schema::requiredKeys($collection);

        $literal = static function (string $property) use ($collection): ?string {
            $schema = Schema::property($collection, $property);

            return $schema === null ? null : self::scalarLiterals($schema);
        };

        foreach (['enabled' => 'getEnabled', 'per_page' => 'getPerPage', 'inline_first_page' => 'getInlineFirstPage'] as $property => $getter) {
            $type = $literal($property);
            if ($type !== null) {
                $narrowed[$property] = [$getter, $type];
            }
        }

        // `pattern` has no phpdoc type, so a sort narrows only by losing its null.
        $sort = Schema::property($collection, 'sort');
        if ($sort !== null && !self::nullable($sort, 'sort', $required)) {
            $narrowed['sort'] = ['getSort', $types->type($sort, [...$path, 'sort'])];
        }

        foreach (['sortable' => 'getSortable', 'filterable' => 'getFilterable'] as $property => $getter) {
            $schema = Schema::property($collection, $property);
            $type = $schema === null ? null : self::fieldListType($schema, [...$path, $property], $types);
            if ($type !== null) {
                $narrowed[$property] = [$getter, $type];
            }
        }

        $links = Schema::property($collection, 'links');
        if ($links !== null && !self::nullable($links, 'links', $required)) {
            $narrowed['links'] = ['getLinks', self::LINKS_CLASS];
        }

        $facets = Schema::property($collection, 'facets');
        $facetFields = self::facetFields($collection);
        if ($facets !== null && $facetFields !== null) {
            $facetRequired = Schema::requiredKeys($facets);
            $lines = [];
            foreach ($facetFields as $field) {
                $lines[] = TypeMapper::key($field) . (in_array($field, $facetRequired, true) ? '' : '?')
                    . ': list<' . self::FACET_VALUE_CLASS . '>';
            }

            $narrowed['facets'] = ['getFacets', $lines === [] ? 'array{}' : TypeMapper::shape('array{', $lines)];
        }

        return $narrowed;
    }

    /**
     * A boolean or integer property pinned by its `enum`, as a union of literals, or null when it
     * is not pinned. `true`, `false` and `24` are what json_decode and the SDK model hand out.
     *
     * @param array<string, mixed> $schema
     */
    private static function scalarLiterals(array $schema): ?string
    {
        $enum = $schema['enum'] ?? null;
        $type = $schema['type'] ?? null;

        if (!is_array($enum) || $enum === [] || !in_array($type, ['boolean', 'integer'], true)) {
            return null;
        }

        $literals = [];
        foreach ($enum as $value) {
            $literal = match (true) {
                $type === 'boolean' && is_bool($value) => $value ? 'true' : 'false',
                $type === 'integer' && is_int($value) => (string) $value,
                default => null,
            };

            if ($literal === null) {
                return null;
            }

            if (!in_array($literal, $literals, true)) {
                $literals[] = $literal;
            }
        }

        // Both booleans is the model's own `bool`.
        return $type === 'boolean' && count($literals) > 1 ? null : implode('|', $literals);
    }

    /**
     * `sortable` or `filterable` as the SDK hands it out: a list of the fields the schema names, or
     * an empty array when it can never hold one. Null when the schema names none, which is the
     * model's own `string[]`.
     *
     * @param array<string, mixed> $schema
     * @param list<string> $path
     */
    private static function fieldListType(array $schema, array $path, TypeMapper $types): ?string
    {
        if (($schema['maxItems'] ?? null) === 0) {
            return 'array{}';
        }

        $items = $schema['items'] ?? null;
        if (!is_array($items) || TypeMapper::enumMembers($items) === null) {
            return null;
        }

        return 'list<' . $types->type($items, $path) . '>';
    }

    /**
     * The fields of `sortable` or `filterable`, an empty list when it can never hold one, or null
     * when the schema does not name them.
     *
     * @param array<string, mixed> $collection
     * @return list<string>|null
     */
    private static function fieldList(array $collection, string $property): ?array
    {
        $schema = Schema::property($collection, $property);
        if ($schema === null) {
            return null;
        }

        if (($schema['maxItems'] ?? null) === 0) {
            return [];
        }

        $items = $schema['items'] ?? null;
        $members = is_array($items) ? TypeMapper::enumMembers($items) : null;

        return $members === null ? null : array_values(array_filter($members, 'is_string'));
    }

    /**
     * The facet fields a typed collection declares, in document order, or null when its facets
     * are the open map of the generic schema.
     *
     * A typed collection closes the map (`additionalProperties: false`), so its declared fields
     * are all there is; a block that aggregates nothing declares none.
     *
     * @param array<string, mixed> $collection
     * @return list<string>|null
     */
    private static function facetFields(array $collection): ?array
    {
        $facets = Schema::property($collection, 'facets');
        if ($facets === null || ($facets['additionalProperties'] ?? null) !== false) {
            return null;
        }

        $properties = $facets['properties'] ?? [];
        if (!is_array($properties)) {
            return null;
        }

        $fields = [];
        foreach ($properties as $field => $fieldSchema) {
            // A numeric-looking JSON key arrives as an int.
            $field = (string) $field;

            if ($field !== '' && is_array($fieldSchema)) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $schema
     * @param list<string> $required
     */
    private static function nullable(array $schema, string $property, array $required): bool
    {
        return !in_array($property, $required, true) || ($schema['nullable'] ?? null) === true;
    }
}
