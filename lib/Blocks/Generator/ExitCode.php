<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * Process exit codes. Distinct per failure class so a CI job can react to each.
 */
final class ExitCode
{
    public const OK = 0;

    /** Bad, missing, duplicate or unknown command line argument. */
    public const USAGE = 1;

    /** Target directory missing, not writable, or occupied by a file. */
    public const FILESYSTEM = 2;

    /** The source could not be fetched (network, HTTP status, unreadable file). */
    public const FETCH = 3;

    /** The source was fetched but is not a usable OpenAPI document. */
    public const PARSE = 4;

    /** The document is valid but declares no typed blocks. */
    public const NO_BLOCKS = 5;

    /** --check found the target out of date. */
    public const DRIFT = 6;

    private function __construct()
    {
    }
}
