<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

use Flyo\Generator\Profile;
use Flyo\Generator\Source;

/**
 * Entry point of the deprecated `vendor/bin/flyo-generate-blocks`.
 *
 * It generates exactly what it always did, byte for byte, so a committed target keeps passing
 * `--check` after an upgrade. It only adds a deprecation warning on stderr.
 *
 * @deprecated since 3.6, use `vendor/bin/flyo-generate-types` ({@see \Flyo\Generator\Cli}), which
 *  also generates containers and entities, each into a sub-namespace of its own. Will be removed
 *  in the next major release.
 */
final class Cli
{
    /**
     * @param list<string> $argv
     * @param array<string, string>|null $env
     * @param resource|null $stdout
     * @param resource|null $stderr
     */
    public static function main(
        array $argv,
        ?array $env = null,
        $stdout = null,
        $stderr = null,
        ?Source $source = null,
    ): int {
        return \Flyo\Generator\Cli::main($argv, $env, $stdout, $stderr, $source, Profile::blocks());
    }
}
