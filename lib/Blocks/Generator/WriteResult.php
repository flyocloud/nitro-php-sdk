<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * What a {@see Writer} run did, or would have done under --dry-run.
 */
final class WriteResult
{
    /**
     * @param list<string> $written
     * @param list<string> $unchanged
     * @param list<string> $removed
     */
    public function __construct(
        public readonly array $written,
        public readonly array $unchanged,
        public readonly array $removed,
        public readonly string $target,
    ) {
    }

    /**
     * Whether the target differs from what the generator produces. This is what --check reports.
     */
    public function changed(): bool
    {
        return $this->written !== [] || $this->removed !== [];
    }
}
