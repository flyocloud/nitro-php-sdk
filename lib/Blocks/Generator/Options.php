<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * Parsed command line, as an immutable value.
 *
 * Parsing is hand-rolled rather than `getopt()`-based, for three reasons that all bite in
 * practice: `getopt()` reads `$_SERVER['argv']` out of global state (so it cannot be unit tested
 * without mutating globals), it stops parsing options at the first positional argument, and it
 * fails silently in ways that produce wrong output rather than an error — a missing option value
 * makes it drop *every* parsed option, unknown flags are ignored, and `-ns 'App\Blocks'` parses as
 * `-n` with the value `s`, quietly generating into a namespace called `s`.
 */
final class Options
{
    public const PACKAGE = 'flyo/nitro-php';

    public const PROGRAM = 'flyo-generate-blocks';

    /** Long options that take a value. */
    private const VALUE_OPTIONS = ['source', 'namespace', 'target', 'token'];

    /** Long options that are booleans. */
    private const FLAG_OPTIONS = [
        'dry-run',
        'check',
        'no-clean',
        'allow-empty',
        'quiet',
        'verbose',
        'help',
        'version',
    ];

    /** Accepted spellings for the same long option. */
    private const ALIASES = [
        'url' => 'source',
        'ns' => 'namespace',
        'dir' => 'target',
    ];

    private const SHORT = [
        'u' => 'source',
        'n' => 'namespace',
        't' => 'target',
        'k' => 'token',
        'd' => 'dry-run',
        'q' => 'quiet',
        'v' => 'verbose',
        'h' => 'help',
        'V' => 'version',
    ];

    private const ENV_TOKEN = ['FLYO_TOKEN', 'FLYO_API_KEY'];

    public const VERBOSITY_QUIET = 0;

    public const VERBOSITY_NORMAL = 1;

    public const VERBOSITY_VERBOSE = 2;

    /**
     * @param list<string> $warnings
     */
    private function __construct(
        public readonly string $source,
        public readonly string $namespace,
        public readonly string $target,
        public readonly ?string $token,
        public readonly bool $clean,
        public readonly bool $allowEmpty,
        public readonly bool $dryRun,
        public readonly bool $check,
        public readonly int $verbosity,
        public readonly bool $help,
        public readonly bool $version,
        public readonly array $warnings,
    ) {
    }

    /**
     * @param list<string> $argv full argv, including $argv[0]
     * @param array<string, string> $env
     * @throws GeneratorException on any usage error
     */
    public static function parse(array $argv, array $env = []): self
    {
        [$values, $flags, $operands] = self::tokenize($argv);

        // --help and --version short-circuit before anything is required.
        if (isset($flags['help'])) {
            return self::informational(help: true);
        }

        if (isset($flags['version'])) {
            return self::informational(version: true);
        }

        $warnings = [];

        $source = self::pick($values, $operands, 'source', '<source>', 0);
        $namespace = self::pick($values, $operands, 'namespace', '<namespace>', 1);
        $target = self::pick($values, $operands, 'target', '<target>', 2);

        if (count($operands) > 3) {
            throw GeneratorException::usage(
                sprintf('unexpected argument "%s".', $operands[3]),
                [self::usage()],
            );
        }

        $namespace = self::normalizeNamespace($namespace, $warnings);

        if (str_starts_with($target, '/') && substr_count(rtrim($target, '/'), '/') === 1) {
            $warnings[] = sprintf(
                'target "%s" is an absolute path directly under "/". Did you mean "./%s"?',
                $target,
                ltrim($target, '/'),
            );
        }

        $token = $values['token'] ?? null;
        foreach (self::ENV_TOKEN as $name) {
            if ($token === null && ($env[$name] ?? '') !== '') {
                $token = $env[$name];
            }
        }

        $verbosity = self::VERBOSITY_NORMAL;
        if (isset($flags['verbose'])) {
            $verbosity = self::VERBOSITY_VERBOSE;
        }
        if (isset($flags['quiet'])) {
            $verbosity = self::VERBOSITY_QUIET;
        }

        return new self(
            source: $source,
            namespace: $namespace,
            target: $target,
            token: $token,
            clean: !isset($flags['no-clean']),
            allowEmpty: isset($flags['allow-empty']),
            // --check implies a dry run: it reports drift and must never write.
            dryRun: isset($flags['dry-run']) || isset($flags['check']),
            check: isset($flags['check']),
            verbosity: $verbosity,
            help: false,
            version: false,
            warnings: $warnings,
        );
    }

    private static function informational(bool $help = false, bool $version = false): self
    {
        return new self('', '', '', null, true, false, false, false, self::VERBOSITY_NORMAL, $help, $version, []);
    }

    /**
     * Splits argv into option values, flags and positional operands.
     *
     * GNU-style permutation: options are recognised wherever they appear, so
     * `<source> -t src/Blocks` works as well as `-t src/Blocks <source>`.
     *
     * @param list<string> $argv
     * @return array{array<string, string>, array<string, bool>, list<string>}
     */
    private static function tokenize(array $argv): array
    {
        $values = [];
        $flags = [];
        $operands = [];
        $endOfOptions = false;

        $count = count($argv);
        for ($i = 1; $i < $count; $i++) {
            $arg = $argv[$i];

            if ($endOfOptions || $arg === '-' || !str_starts_with($arg, '-')) {
                // A lone "-" means stdin, so it is an operand rather than an option.
                $operands[] = $arg;
                continue;
            }

            if ($arg === '--') {
                $endOfOptions = true;
                continue;
            }

            if (str_starts_with($arg, '--')) {
                $name = substr($arg, 2);
                $inline = null;

                if (str_contains($name, '=')) {
                    [$name, $inline] = explode('=', $name, 2);
                }

                $name = self::resolveLong($name);

                if (in_array($name, self::VALUE_OPTIONS, true)) {
                    $value = $inline ?? ($argv[$i + 1] ?? null);
                    if ($inline === null) {
                        $i++;
                    }

                    self::setValue($values, $name, $value, '--' . $name, $inline === null);
                    continue;
                }

                if ($inline !== null) {
                    throw GeneratorException::usage(
                        sprintf('--%s is a flag and takes no value.', $name),
                        [self::usage()],
                    );
                }

                $flags[$name] = true;
                continue;
            }

            // Short options. A cluster is only allowed when every member is a flag, and a
            // value-taking option must stand alone -- this is what turns the classic `-ns` mistake
            // into an error instead of a silent misparse.
            $cluster = substr($arg, 1);
            $letters = preg_split('//u', $cluster, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($letters as $position => $letter) {
                $name = self::SHORT[$letter] ?? null;

                if ($name === null) {
                    throw GeneratorException::usage(
                        sprintf('unknown option "-%s".', $letter),
                        [self::usage()],
                    );
                }

                if (in_array($name, self::VALUE_OPTIONS, true)) {
                    if (count($letters) > 1) {
                        throw GeneratorException::usage(
                            sprintf('-%s takes a value and must stand alone.', $letter),
                            [
                                sprintf(
                                    '"-%s" would be read as "-%s" with the value "%s".',
                                    $cluster,
                                    $letter,
                                    implode('', array_slice($letters, $position + 1)),
                                ),
                                sprintf("use -%s <value> or --%s=<value>.", $letter, $name),
                            ],
                        );
                    }

                    $value = $argv[$i + 1] ?? null;
                    $i++;

                    self::setValue($values, $name, $value, '-' . $letter);
                    continue;
                }

                $flags[$name] = true;
            }
        }

        return [$values, $flags, $operands];
    }

    /**
     * @param array<string, string> $values
     * @param bool $lookahead whether the value was taken from the next argv element rather than
     *  from an inline `=value`, in which case something that looks like an option is a mistake
     *  (`-u -n ...`) rather than a legitimate value
     * @throws GeneratorException
     */
    private static function setValue(
        array &$values,
        string $name,
        ?string $value,
        string $as,
        bool $lookahead = true,
    ): void {
        $looksLikeOption = $lookahead && $value !== null && $value !== '-' && str_starts_with($value, '-');

        if ($value === null || $value === '' || $looksLikeOption) {
            throw GeneratorException::usage(
                sprintf('%s requires a value.', $as),
                [self::usage()],
            );
        }

        if (isset($values[$name])) {
            throw GeneratorException::usage(
                sprintf('%s was given more than once.', $as),
                [self::usage()],
            );
        }

        $values[$name] = $value;
    }

    /**
     * @throws GeneratorException on an unknown option, with a suggestion when one is close.
     */
    private static function resolveLong(string $name): string
    {
        $name = self::ALIASES[$name] ?? $name;

        if (in_array($name, self::VALUE_OPTIONS, true) || in_array($name, self::FLAG_OPTIONS, true)) {
            return $name;
        }

        $known = [...self::VALUE_OPTIONS, ...self::FLAG_OPTIONS, ...array_keys(self::ALIASES)];
        $hints = [];

        foreach ($known as $candidate) {
            if ($name !== '' && levenshtein($name, $candidate) <= 2) {
                $hints[] = sprintf('did you mean --%s?', $candidate);
            }
        }

        throw GeneratorException::usage(
            sprintf('unknown option "--%s".', $name),
            [...array_slice($hints, 0, 2), self::usage()],
        );
    }

    /**
     * An option value, or the positional argument at `$position`.
     *
     * @param array<string, string> $values
     * @param list<string> $operands
     * @throws GeneratorException
     */
    private static function pick(array $values, array $operands, string $name, string $label, int $position): string
    {
        $flag = $values[$name] ?? null;
        $operand = $operands[$position] ?? null;

        if ($flag !== null && $operand !== null) {
            throw GeneratorException::usage(
                sprintf('%s was given twice, as --%s and as a positional argument.', $label, $name),
                [self::usage()],
            );
        }

        $value = $flag ?? $operand;

        if ($value === null || $value === '') {
            throw GeneratorException::usage(sprintf('missing %s.', $label), [self::usage()]);
        }

        return $value;
    }

    /**
     * @param list<string> $warnings
     * @throws GeneratorException
     */
    private static function normalizeNamespace(string $namespace, array &$warnings): string
    {
        // Accept forward slashes as a separator: it is what a shell leaves behind when the
        // backslashes are eaten, and what people type by analogy with paths.
        $namespace = trim(str_replace('/', '\\', $namespace), '\\');

        $segment = '[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*';

        if ($namespace === '' || preg_match('/^' . $segment . '(\\\\' . $segment . ')*$/', $namespace) !== 1) {
            throw GeneratorException::usage(
                sprintf('"%s" is not a valid PHP namespace.', $namespace),
                ['quote it so the shell keeps the backslashes, e.g. \'App\\Blocks\'.'],
            );
        }

        // "App\Blocks" unquoted in bash arrives as "AppBlocks". Detectable, and worth saying.
        if (!str_contains($namespace, '\\') && preg_match_all('/\p{Lu}/u', $namespace) > 1) {
            $warnings[] = sprintf(
                'namespace "%s" contains no "\\" separator. If you meant a nested namespace, '
                    . 'quote it so the shell keeps the backslashes: \'App\\Blocks\'.',
                $namespace,
            );
        }

        return $namespace;
    }

    public static function usage(): string
    {
        return sprintf('usage: %s <source> <namespace> <target> [options]', self::PROGRAM);
    }

    public static function help(): string
    {
        $usage = self::usage();
        $program = self::PROGRAM;

        return <<<TXT
        {$usage}

        Generates one PHP class per Flyo Nitro block from an OpenAPI document. The classes carry
        no logic: they narrow the inherited getters of \\Flyo\\Model\\Block so an IDE and PHPStan
        know the shape of a block's content, config and items.

        Arguments:
          <source>              OpenAPI URL, a local .json path, or - for stdin
          <namespace>           PSR-4 namespace for the generated classes, e.g. 'App\\Blocks'
          <target>              directory that namespace maps to, e.g. src/Blocks

        Options:
          -u, --url, --source   same as <source>
          -n, --ns, --namespace same as <namespace>
          -t, --dir, --target   same as <target>
          -k, --token KEY       API token, merged in as ?token=. Defaults to \$FLYO_TOKEN or
                                \$FLYO_API_KEY. Prefer the environment variable: an argument is
                                visible to anyone who can run ps.
              --no-clean        keep previously generated files that are no longer produced
              --allow-empty     exit 0 when the document declares no typed blocks
          -d, --dry-run         report what would change, write nothing
              --check           like --dry-run, but exit 6 if anything is out of date
          -q, --quiet           suppress all output except warnings and errors
          -v, --verbose         also report unchanged files
          -h, --help            show this help
          -V, --version         show the installed package version

        Typed block schemas only exist on the authenticated endpoints; the public
        /nitro/v1/openapi has none. Use:

          export FLYO_TOKEN=...
          {$program} https://api.flyo.cloud/nitro/v1/openapi/schemas 'App\\Blocks' src/Blocks

        Generated files are marked "@generated by flyo/nitro-php". Hand-written files in the same
        directory are never touched.
        TXT;
    }
}
