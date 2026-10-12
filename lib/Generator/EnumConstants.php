<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Class constants naming the options of every field that has a fixed set of them.
 *
 * Each option becomes `<OBJECT>_<FIELD>_<OPTION>`: a block whose content has a `size` select with
 * the options `small` and `large` gets `CONTENT_SIZE_SMALL = 'small'` and
 * `CONTENT_SIZE_LARGE = 'large'`. The options are the field's own `enum`, or, for a select field,
 * the `enum` of its `value`. `null` is never one: it means nothing is selected.
 *
 * The values are exactly the strings {@see TypeMapper} puts in the field's type, so comparing the
 * field with a constant type-checks wherever comparing it with the literal does.
 */
final class EnumConstants
{
    /** The property a select field keeps the selected option in. */
    private const SELECT_VALUE = 'value';

    /** Keywords {@see TypeMapper::type()} acts on before it ever looks at an `enum`. */
    private const PRECEDING_KEYWORDS = ['$ref', 'oneOf', 'anyOf', 'allOf'];

    private function __construct()
    {
    }

    /**
     * The constants for the fields of some object schemas of one typed schema.
     *
     * A name that would stand for two different options is not generated at all, rather than
     * numbered: a number would silently move to another option once one is added in Flyo, while a
     * missing constant fails loudly wherever it is used.
     *
     * @param string $schemaKey the typed schema, for warnings
     * @param array<string, array<string, mixed>> $objects property => the object schema whose
     *  fields to scan, e.g. `content` => a block's content schema; the property names the prefix
     * @return list<ConstantDef> in document order, grouped by field
     */
    public static function plan(string $schemaKey, array $objects, Warnings $warnings): array
    {
        /** @var array<string, list<array{string, string}>> $claims name => [field path, option] */
        $claims = [];

        foreach ($objects as $property => $object) {
            $fields = $object['properties'] ?? null;

            if (!is_array($fields)) {
                continue;
            }

            $prefix = Naming::constantSegment($property);

            foreach ($fields as $field => $fieldSchema) {
                // A numeric-looking JSON key arrives as an int.
                $field = (string) $field;
                $options = is_array($fieldSchema) ? self::options($fieldSchema) : [];

                if ($options === []) {
                    continue;
                }

                $path = $property . '.' . $field;
                $fieldSegment = Naming::constantSegment($field);

                if ($fieldSegment === '') {
                    $warnings->add(sprintf(
                        '%s.%s: the field name has no letter or digit to name constants after, so its '
                            . 'options get none.',
                        $schemaKey,
                        $path,
                    ));

                    continue;
                }

                foreach ($options as $option) {
                    $optionSegment = Naming::constantSegment($option);

                    if ($optionSegment === '') {
                        $warnings->add(sprintf(
                            '%s.%s: the option "%s" has no letter or digit to name a constant after, so '
                                . 'it gets none.',
                            $schemaKey,
                            $path,
                            $option,
                        ));

                        continue;
                    }

                    $claims[$prefix . '_' . $fieldSegment . '_' . $optionSegment][] = [$path, $option];
                }
            }
        }

        $constants = [];

        foreach ($claims as $name => $claimants) {
            $options = array_values(array_unique(array_column($claimants, 1)));

            if (count($options) > 1) {
                $rendered = [];
                foreach ($claimants as [$path, $option]) {
                    $rendered[] = sprintf('%s "%s"', $path, $option);
                }

                $warnings->add(sprintf(
                    '%s: %s would stand for more than one option (%s), so it is not generated. '
                        . 'Rename one of the options in Flyo to remove the ambiguity.',
                    $schemaKey,
                    $name,
                    implode(', ', $rendered),
                ));

                continue;
            }

            $constants[] = new ConstantDef($name, $options[0], $claimants[0][0]);
        }

        return $constants;
    }

    /**
     * The options of one field, or `[]` when it has no fixed set of them.
     *
     * @param array<string, mixed> $field
     * @return list<string>
     */
    private static function options(array $field): array
    {
        $options = self::literals($field);

        if ($options !== null) {
            return $options;
        }

        // A select field: an object whose `value` is one of the options, or null.
        if (($field['type'] ?? null) !== 'object' || self::isDeferred($field)) {
            return [];
        }

        $value = Schema::property($field, self::SELECT_VALUE);

        return $value === null ? [] : (self::literals($value) ?? []);
    }

    /**
     * The non-null members of a node's `enum`, or null unless TypeMapper types the node as a union
     * of exactly those literals: a node typed `string`, or not typed at all.
     *
     * @param array<string, mixed> $node
     * @return list<string>|null
     */
    private static function literals(array $node): ?array
    {
        $type = $node['type'] ?? null;

        if (($type !== null && $type !== 'string') || self::isDeferred($node)) {
            return null;
        }

        $members = TypeMapper::enumMembers($node);

        return $members === null ? null : array_values(array_filter(
            $members,
            static fn (?string $member): bool => $member !== null,
        ));
    }

    /**
     * Whether TypeMapper types the node by something other than its own keywords.
     *
     * @param array<string, mixed> $node
     */
    private static function isDeferred(array $node): bool
    {
        foreach (self::PRECEDING_KEYWORDS as $keyword) {
            if (isset($node[$keyword])) {
                return true;
            }
        }

        return false;
    }
}
