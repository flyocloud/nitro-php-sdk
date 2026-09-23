<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Command line entry point: wires the pieces together, owns every message, and maps failures to
 * exit codes.
 *
 * Streams are injectable so the whole command can be exercised in a test without touching the
 * process' own stdout/stderr.
 */
final class Cli
{
    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    /**
     * @param resource|null $stdout
     * @param resource|null $stderr
     */
    private function __construct(
        $stdout,
        $stderr,
        private readonly ?Source $source,
        private readonly Profile $profile,
    ) {
        $this->stdout = $stdout ?? STDOUT;
        $this->stderr = $stderr ?? STDERR;
    }

    /**
     * @param list<string> $argv
     * @param array<string, string>|null $env
     * @param resource|null $stdout
     * @param resource|null $stderr
     * @param Profile|null $profile defaults to {@see Profile::types()}
     */
    public static function main(
        array $argv,
        ?array $env = null,
        $stdout = null,
        $stderr = null,
        ?Source $source = null,
        ?Profile $profile = null,
    ): int {
        $cli = new self($stdout, $stderr, $source, $profile ?? Profile::types());

        if ($env === null) {
            /** @var array<string, string> $env */
            $env = array_filter(getenv(), 'is_string');
        }

        try {
            return $cli->run($argv, $env);
        } catch (GeneratorException $e) {
            $cli->error($e->getMessage());

            foreach ($e->hints() as $hint) {
                $cli->hint($hint);
            }

            return $e->exitCode();
        }
    }

    /**
     * @param list<string> $argv
     * @param array<string, string> $env
     * @throws GeneratorException
     */
    private function run(array $argv, array $env): int
    {
        $options = Options::parse($argv, $env, $this->profile);

        if ($options->help) {
            $this->line($this->profile->help());

            return ExitCode::OK;
        }

        if ($options->version) {
            $this->line($this->profile->program . ' (' . Options::PACKAGE . ' ' . self::installedVersion() . ')');

            return ExitCode::OK;
        }

        if ($this->profile->deprecation !== null) {
            $this->warn($this->profile->deprecation);
        }

        foreach ($options->warnings as $warning) {
            $this->warn($warning);
        }

        $document = new Document(($this->source ?? new Source())->load($options, $env));

        if (!$document->hasComponentsSchemas()) {
            $version = $document->openapiVersion();

            throw GeneratorException::parse(
                sprintf(
                    'no "components.schemas" object in the document (openapi: %s).',
                    $version === '' ? 'absent' : '"' . $version . '"',
                ),
                ['is this an OpenAPI 3 document?'],
            );
        }

        $warnings = new Warnings();
        // The parsed profile, which carries per-run layout choices such as --lowercase.
        $plan = (new Planner())->plan($document, $options->profile, $options->namespace, $warnings);

        if ($plan->isEmpty()) {
            return $this->noTypes($document, $options);
        }

        $renderer = new Renderer($this->profile->program);

        $files = [];
        foreach ($plan->classes as $path => $class) {
            $files[$path] = $renderer->render($class);
        }

        $result = (new Writer())->write($files, $options);

        foreach ($warnings->all() as $warning) {
            $this->warn($warning);
        }

        $this->report($result, $options, $plan);

        return $options->check && $result->changed() ? ExitCode::DRIFT : ExitCode::OK;
    }

    /**
     * @throws GeneratorException
     */
    private function noTypes(Document $document, Options $options): int
    {
        $counts = [];
        foreach ($document->schemaTypeCounts() as $type => $count) {
            $counts[] = $type === ''
                ? sprintf('%d %s none', $count, $count === 1 ? 'has' : 'have')
                : sprintf('%d %s "%s"', $count, $count === 1 ? 'is' : 'are', $type);
        }

        $types = array_map(static fn (Kind $kind): string => '"' . $kind->schemaType() . '"', $this->profile->kinds);
        $subject = $this->profile->subject();

        $message = sprintf(
            'the document declares no typed %s: 0 of %d schemas carry %s%s.',
            $subject,
            $document->schemaCount(),
            count($types) === 1
                ? 'x-schema-type ' . $types[0]
                : 'an x-schema-type of ' . implode(', ', $types),
            $counts === [] ? '' : ' (' . implode(', ', $counts) . ')',
        );

        if ($options->allowEmpty) {
            $this->warn($message);

            return ExitCode::OK;
        }

        throw GeneratorException::noTypes($message, [
            sprintf('typed %s live at https://api.flyo.cloud/nitro/v1/openapi/schemas and need a ', $subject)
                . 'token; the public /nitro/v1/openapi endpoint has none.',
            'pass --allow-empty to treat this as success.',
        ]);
    }

    private function report(WriteResult $result, Options $options, Plan $plan): void
    {
        if ($options->verbosity === Options::VERBOSITY_QUIET) {
            return;
        }

        $verb = $options->dryRun ? 'would write' : 'wrote';

        foreach ($result->written as $file) {
            $this->line(sprintf('%s: %s', $verb, $file));
        }

        foreach ($result->removed as $file) {
            $this->line(sprintf('%s: %s', $options->dryRun ? 'would remove' : 'removed', $file));
        }

        if ($options->verbosity >= Options::VERBOSITY_VERBOSE) {
            foreach ($result->unchanged as $file) {
                $this->line(sprintf('unchanged: %s', $file));
            }
        }

        $found = [];
        foreach ($this->profile->kinds as $kind) {
            $count = $plan->count($kind);
            $found[] = sprintf('%d %s', $count, $kind->noun($count));
        }

        $this->line(sprintf(
            '%s -> %s (%s, %d unchanged%s)',
            implode(', ', $found),
            $result->target,
            sprintf('%d %s', count($result->written), $options->dryRun ? 'to write' : 'written'),
            count($result->unchanged),
            $result->removed === [] ? '' : sprintf(', %d removed', count($result->removed)),
        ));

        if ($options->check && $result->changed()) {
            $this->line('the target is out of date; regenerate it.');
        }
    }

    private static function installedVersion(): string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                return \Composer\InstalledVersions::getPrettyVersion(Options::PACKAGE) ?? 'unknown version';
            } catch (\OutOfBoundsException) {
                // Running from a checkout of the package itself rather than as a dependency.
            }
        }

        return 'unknown version';
    }

    private function line(string $message): void
    {
        fwrite($this->stdout, $message . "\n");
    }

    private function warn(string $message): void
    {
        fwrite($this->stderr, 'warning: ' . $message . "\n");
    }

    private function error(string $message): void
    {
        fwrite($this->stderr, $this->profile->program . ': error: ' . $message . "\n");
    }

    private function hint(string $message): void
    {
        fwrite($this->stderr, '  hint: ' . $message . "\n");
    }
}
