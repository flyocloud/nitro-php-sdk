<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * Derives generated class names from block schemas.
 *
 * The class name is the only identifier this generator invents, since nested shapes are described
 * inline rather than as named types. It comes from the block's `component` because that is what
 * the server itself uses to key the schema (`'Block' . $component`), which keeps
 * `views/flyo/Hero.php` <-> `BlockHero` <-> schema `BlockHero` lined up.
 */
final class Naming
{
    private const PREFIX = 'Block';

    /**
     * Upper-cases the first letter of every alphanumeric run and joins them.
     *
     * Inner case is preserved on purpose: `studly('BlockHero')` must stay `BlockHero`, not
     * `Blockhero`. Identifiers are author-supplied and may be any language, so splitting is done
     * on unicode letter/number classes.
     */
    public static function studly(string $raw): string
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $out = '';

        foreach ($parts ?: [] as $part) {
            $out .= mb_strtoupper(mb_substr($part, 0, 1)) . mb_substr($part, 1);
        }

        if ($out === '') {
            return 'Unnamed';
        }

        // A PHP identifier cannot start with a digit, so "2col" needs a prefix.
        if (preg_match('/^\p{N}/u', $out) === 1) {
            $out = 'N' . $out;
        }

        return $out;
    }

    /**
     * The class name each block schema gets, keyed by schema name.
     *
     * `$blockSchemas` must already be sorted (see {@see Document::blockSchemas()}): collision
     * suffixes are handed out in that order, which is what makes them deterministic.
     *
     * @param array<string, array<string, mixed>> $blockSchemas
     * @return array<string, string>
     */
    public static function classNames(array $blockSchemas, Warnings $warnings): array
    {
        /** @var array<string, list<string>> $groups preferred name => schema keys wanting it */
        $groups = [];
        foreach ($blockSchemas as $schemaKey => $schema) {
            $groups[self::preferredName($schemaKey, $schema)][] = $schemaKey;
        }

        ksort($groups, SORT_STRING);

        $names = [];
        $taken = [];

        // Pass 1: every group's first claimant takes the preferred name. Groups are keyed by that
        // name, so these can never conflict with each other -- and reserving them all up front is
        // what stops a suffix below from stealing a name that is another block's own.
        foreach ($groups as $preferred => $schemaKeys) {
            sort($schemaKeys, SORT_STRING);
            $groups[$preferred] = $schemaKeys;

            $names[$schemaKeys[0]] = $preferred;
            $taken[$preferred] = true;
        }

        // Pass 2: number the rest, skipping anything already reserved.
        foreach ($groups as $preferred => $schemaKeys) {
            foreach (array_slice($schemaKeys, 1) as $schemaKey) {
                $suffix = 2;
                while (isset($taken[$preferred . $suffix])) {
                    $suffix++;
                }

                $name = $preferred . $suffix;
                $taken[$name] = true;
                $names[$schemaKey] = $name;
            }

            if (count($schemaKeys) > 1) {
                $rendered = [];
                foreach ($schemaKeys as $schemaKey) {
                    $rendered[] = sprintf('  %s -> %s', $schemaKey, $names[$schemaKey]);
                }

                $warnings->add(sprintf(
                    "%d block schemas want the class name %s:\n%s\n  "
                        . 'Rename one of the block components in Flyo to remove the ambiguity.',
                    count($schemaKeys),
                    $preferred,
                    implode("\n", $rendered),
                ));
            }
        }

        return $names;
    }

    /**
     * `Block` + the studly component, falling back to the identifier.
     *
     * `component` is not validated as a PHP-safe string server-side, so it may be empty (the
     * schema is then keyed exactly `Block`), contain spaces, or start with a digit.
     *
     * @param array<string, mixed> $schema
     */
    private static function preferredName(string $schemaKey, array $schema): string
    {
        $component = self::enumValue($schema, 'component');
        $name = $component === '' ? '' : self::PREFIX . self::studly($component);

        if ($name === '' || $name === self::PREFIX) {
            $identifier = self::enumValue($schema, 'identifier');
            $name = $identifier === '' ? '' : self::PREFIX . self::studly($identifier);
        }

        if ($name === '' || $name === self::PREFIX) {
            // Nothing usable in the schema itself; the key at least distinguishes it. The server
            // keys these `'Block' . $component`, so the key usually already carries the prefix.
            $fromKey = self::studly($schemaKey);
            $name = str_starts_with($fromKey, self::PREFIX) ? $fromKey : self::PREFIX . $fromKey;
        }

        return $name === self::PREFIX ? self::PREFIX . 'Unnamed' : $name;
    }

    /**
     * The single-value enum the server puts on `identifier` and `component`, as a string.
     *
     * A block `id` may be a number, so the enum member's JSON type does not always match the
     * declared `type: string`.
     *
     * @param array<string, mixed> $schema
     */
    public static function enumValue(array $schema, string $property): string
    {
        $properties = $schema['properties'] ?? null;
        if (!is_array($properties)) {
            return '';
        }

        $node = $properties[$property] ?? null;
        if (!is_array($node)) {
            return '';
        }

        $enum = $node['enum'] ?? null;
        if (!is_array($enum) || $enum === []) {
            return '';
        }

        $first = reset($enum);

        return is_scalar($first) ? (string) $first : '';
    }
}
