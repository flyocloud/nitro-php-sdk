<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Defensive readers for a single schema node.
 *
 * The document comes over the wire from a server this package does not control, so nothing in it
 * is assumed to have the type the OpenAPI specification says it has.
 */
final class Schema
{
    private function __construct()
    {
    }

    /**
     * A string keyword of the node, or `''` when it is absent or not a string.
     *
     * @param array<int|string, mixed> $schema
     */
    public static function text(array $schema, string $key): string
    {
        $value = $schema[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * The node's `required` list, keeping only the string entries.
     *
     * @param array<int|string, mixed> $schema
     * @return list<string>
     */
    public static function requiredKeys(array $schema): array
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

    /**
     * The schema of one declared property, or null when it is missing or not a schema.
     *
     * @param array<int|string, mixed> $schema
     * @return array<string, mixed>|null
     */
    public static function property(array $schema, string $name): ?array
    {
        $properties = $schema['properties'] ?? null;
        $property = is_array($properties) ? ($properties[$name] ?? null) : null;

        if (!is_array($property)) {
            return null;
        }

        /** @var array<string, mixed> $property */
        return $property;
    }
}
