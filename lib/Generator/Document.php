<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Read-only access to a decoded OpenAPI document.
 *
 * Only `components.schemas` matters here. A schema is typed when it carries an `x-schema-type`
 * marker (`block`, `entity`, `container`, ...), which is what each {@see Kind} selects on. The
 * generic `block` and `entity` schemas carry no marker and are never generated themselves.
 */
final class Document
{
    /** The schema key of the generic block model, which maps to \Flyo\Model\Block. */
    public const GENERIC_BLOCK_KEY = 'block';

    /** The schema key of the generic entity response, which maps to \Flyo\Model\Entity. */
    public const GENERIC_ENTITY_KEY = 'entity';

    private const REF_PREFIX = '#/components/schemas/';

    /** @var array<string, array<string, mixed>> */
    private readonly array $schemas;

    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(private readonly array $raw)
    {
        $components = $this->raw['components'] ?? null;
        $schemas = is_array($components) ? ($components['schemas'] ?? null) : null;

        $clean = [];
        if (is_array($schemas)) {
            foreach ($schemas as $key => $schema) {
                if (is_string($key) && is_array($schema)) {
                    $clean[$key] = $schema;
                }
            }
        }

        $this->schemas = $clean;
    }

    /**
     * Every schema carrying `x-schema-type: $type`, keyed by schema name, sorted byte-wise.
     *
     * The server builds `components.schemas` with `array_merge()` in definition order, which
     * changes whenever an editor reorders blocks. Sorting here is what makes the output stable.
     *
     * @return array<string, array<string, mixed>>
     */
    public function typedSchemas(string $type): array
    {
        $typed = [];
        foreach ($this->schemas as $key => $schema) {
            if (($schema['x-schema-type'] ?? null) === $type) {
                $typed[$key] = $schema;
            }
        }

        ksort($typed, SORT_STRING);

        return $typed;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function schema(string $key): ?array
    {
        return $this->schemas[$key] ?? null;
    }

    /**
     * The schema key a local `$ref` points at, or null when it is not a local schema reference.
     */
    public function refKey(string $ref): ?string
    {
        if (!str_starts_with($ref, self::REF_PREFIX)) {
            return null;
        }

        $key = substr($ref, strlen(self::REF_PREFIX));

        return $key === '' ? null : rawurldecode($key);
    }

    public function schemaCount(): int
    {
        return count($this->schemas);
    }

    /**
     * How many schemas carry each `x-schema-type`, for the "no typed schemas" diagnostic.
     * The key `''` counts schemas with no marker at all.
     *
     * @return array<string, int>
     */
    public function schemaTypeCounts(): array
    {
        $counts = [];
        foreach ($this->schemas as $schema) {
            $type = $schema['x-schema-type'] ?? '';
            $type = is_string($type) ? $type : '';
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        ksort($counts, SORT_STRING);

        return $counts;
    }

    /**
     * The document's declared OpenAPI version, for error messages. Empty when absent.
     */
    public function openapiVersion(): string
    {
        $version = $this->raw['openapi'] ?? null;

        return is_string($version) ? $version : '';
    }

    public function hasComponentsSchemas(): bool
    {
        $components = $this->raw['components'] ?? null;

        return is_array($components) && is_array($components['schemas'] ?? null);
    }
}
