<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * A failure the CLI reports and turns into an exit code.
 *
 * The message is written to stderr as-is, so it is phrased for a human. `$hints` are printed
 * underneath as `hint:` lines and carry the "what to do about it" half.
 */
final class GeneratorException extends \RuntimeException
{
    /**
     * @param list<string> $hints
     */
    public function __construct(
        string $message,
        private readonly int $exitCode,
        private readonly array $hints = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @param list<string> $hints
     */
    public static function usage(string $message, array $hints = []): self
    {
        return new self($message, ExitCode::USAGE, $hints);
    }

    /**
     * @param list<string> $hints
     */
    public static function filesystem(string $message, array $hints = []): self
    {
        return new self($message, ExitCode::FILESYSTEM, $hints);
    }

    /**
     * @param list<string> $hints
     */
    public static function fetch(string $message, array $hints = []): self
    {
        return new self($message, ExitCode::FETCH, $hints);
    }

    /**
     * @param list<string> $hints
     */
    public static function parse(string $message, array $hints = []): self
    {
        return new self($message, ExitCode::PARSE, $hints);
    }

    /**
     * @param list<string> $hints
     */
    public static function noBlocks(string $message, array $hints = []): self
    {
        return new self($message, ExitCode::NO_BLOCKS, $hints);
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @return list<string>
     */
    public function hints(): array
    {
        return $this->hints;
    }
}
