<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Everything one run will generate, before rendering.
 */
final class Plan
{
    /**
     * @param array<string, ClassDef> $classes relative path => class, sorted by path
     * @param array<string, int> $counts x-schema-type => how many typed schemas of it were found
     */
    public function __construct(
        public readonly array $classes,
        public readonly array $counts,
    ) {
    }

    public function count(Kind $kind): int
    {
        return $this->counts[$kind->schemaType()] ?? 0;
    }

    /**
     * Whether the document declared no typed schema of any kind the profile generates.
     */
    public function isEmpty(): bool
    {
        return array_sum($this->counts) === 0;
    }
}
