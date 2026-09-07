<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * Collects non-fatal problems found while planning, so they can be reported once at the end
 * rather than interleaved with progress output.
 */
final class Warnings
{
    /** @var list<string> */
    private array $messages = [];

    public function add(string $message): void
    {
        if (!in_array($message, $this->messages, true)) {
            $this->messages[] = $message;
        }
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return $this->messages;
    }

    public function isEmpty(): bool
    {
        return $this->messages === [];
    }
}
