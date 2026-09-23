<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Derives generated class names from typed schemas.
 *
 * The class name is the only identifier this generator invents, since nested shapes are described
 * inline rather than as named types. Every name keeps its kind's prefix (`BlockHero`,
 * `EntityArticle`, `ContainerMain`), exactly as the server keys the schema: that keeps a class
 * lined up with its schema, and it means no author-supplied name can ever produce a reserved word
 * such as `List` or `Default` as a class name.
 */
final class Naming
{
    private const BLOCK_PREFIX = 'Block';

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
     * @param array<string, array<string, mixed>> $blockSchemas
     * @return array<string, string>
     */
    public static function blockClassNames(array $blockSchemas, Warnings $warnings): array
    {
        $preferred = [];
        foreach ($blockSchemas as $schemaKey => $schema) {
            $preferred[$schemaKey] = self::blockName($schemaKey, $schema);
        }

        return self::resolve(
            $preferred,
            $warnings,
            'block',
            'Rename one of the block components in Flyo to remove the ambiguity.',
        );
    }

    /**
     * Settles the final class names of one namespace, keyed by schema name.
     *
     * Two schemas can want the same name once their keys are normalised (`Hero Banner` and
     * `HeroBanner`). Collision suffixes are handed out in schema key order, so the result does not
     * depend on the order the schemas arrive in.
     *
     * @param array<string, string> $preferred schema key => the class name it would like
     * @param string $noun what the schemas are, for the warning: "block", "entity", ...
     * @param string $renameHint how an editor removes the ambiguity in Flyo
     * @return array<string, string>
     */
    public static function resolve(array $preferred, Warnings $warnings, string $noun, string $renameHint): array
    {
        /** @var array<string, list<string>> $groups preferred name => schema keys wanting it */
        $groups = [];
        foreach ($preferred as $schemaKey => $name) {
            $groups[$name][] = $schemaKey;
        }

        ksort($groups, SORT_STRING);

        $names = [];
        $taken = [];

        // Pass 1: every group's first claimant takes the preferred name. Groups are keyed by that
        // name, so these can never conflict with each other -- and reserving them all up front is
        // what stops a suffix below from stealing a name that is another schema's own.
        foreach ($groups as $name => $schemaKeys) {
            sort($schemaKeys, SORT_STRING);
            $groups[$name] = $schemaKeys;

            $names[$schemaKeys[0]] = $name;
            $taken[$name] = true;
        }

        // Pass 2: number the rest, skipping anything already reserved.
        foreach ($groups as $name => $schemaKeys) {
            foreach (array_slice($schemaKeys, 1) as $schemaKey) {
                $suffix = 2;
                while (isset($taken[$name . $suffix])) {
                    $suffix++;
                }

                $taken[$name . $suffix] = true;
                $names[$schemaKey] = $name . $suffix;
            }

            if (count($schemaKeys) > 1) {
                $rendered = [];
                foreach ($schemaKeys as $schemaKey) {
                    $rendered[] = sprintf('  %s -> %s', $schemaKey, $names[$schemaKey]);
                }

                $warnings->add(sprintf(
                    "%d %s schemas want the class name %s:\n%s\n  %s",
                    count($schemaKeys),
                    $noun,
                    $name,
                    implode("\n", $rendered),
                    $renameHint,
                ));
            }
        }

        return $names;
    }

    /**
     * `Block` + the studly component, falling back to the identifier.
     *
     * It comes from the block's `component` because that is what the server itself uses to key
     * the schema (`'Block' . $component`), which keeps `views/flyo/Hero.php` <-> `BlockHero` <->
     * schema `BlockHero` lined up.
     *
     * `component` is not validated as a PHP-safe string server-side, so it may be empty (the
     * schema is then keyed exactly `Block`), contain spaces, or start with a digit.
     *
     * @param array<string, mixed> $schema
     */
    public static function blockName(string $schemaKey, array $schema): string
    {
        $component = self::enumValue($schema, 'component');
        $name = $component === '' ? '' : self::BLOCK_PREFIX . self::studly($component);

        if ($name === '' || $name === self::BLOCK_PREFIX) {
            $identifier = self::enumValue($schema, 'identifier');
            $name = $identifier === '' ? '' : self::BLOCK_PREFIX . self::studly($identifier);
        }

        if ($name === '' || $name === self::BLOCK_PREFIX) {
            $name = self::prefixedKeyName(self::BLOCK_PREFIX, $schemaKey);
        }

        return $name;
    }

    /**
     * `$prefix` + the studly schema key, without doubling a prefix the key already carries.
     *
     * The server keys typed schemas `'<Prefix>' . <name>` (`EntityArticle`, `ContainerMain`), so
     * the key usually carries the prefix already.
     */
    public static function prefixedKeyName(string $prefix, string $schemaKey): string
    {
        $fromKey = self::studly($schemaKey);
        $name = str_starts_with($fromKey, $prefix) ? $fromKey : $prefix . $fromKey;

        return $name === $prefix ? $prefix . 'Unnamed' : $name;
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
