<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

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
    private function __construct($stdout, $stderr, private readonly ?Source $source)
    {
        $this->stdout = $stdout ?? STDOUT;
        $this->stderr = $stderr ?? STDERR;
    }

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
        $cli = new self($stdout, $stderr, $source);

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
        $options = Options::parse($argv, $env);

        if ($options->help) {
            $this->line(Options::help());

            return ExitCode::OK;
        }

        if ($options->version) {
            $this->line(Options::PROGRAM . ' (' . Options::PACKAGE . ' ' . self::installedVersion() . ')');

            return ExitCode::OK;
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

        $schemas = $document->blockSchemas();

        if ($schemas === []) {
            return $this->noBlocks($document, $options);
        }

        $warnings = new Warnings();
        $classes = (new Planner())->plan($document, $options->namespace, $warnings);
        $renderer = new Renderer();

        $files = [];
        foreach ($classes as $class) {
            $files[$class->relativePath] = $renderer->render($class);
        }

        $result = (new Writer())->write($files, $options);

        foreach ($warnings->all() as $warning) {
            $this->warn($warning);
        }

        $this->report($result, $options, count($classes));

        return $options->check && $result->changed() ? ExitCode::DRIFT : ExitCode::OK;
    }

    /**
     * @throws GeneratorException
     */
    private function noBlocks(Document $document, Options $options): int
    {
        $counts = [];
        foreach ($document->schemaTypeCounts() as $type => $count) {
            $counts[] = $type === ''
                ? sprintf('%d %s none', $count, $count === 1 ? 'has' : 'have')
                : sprintf('%d %s "%s"', $count, $count === 1 ? 'is' : 'are', $type);
        }

        $message = sprintf(
            'the document declares no typed blocks: 0 of %d schemas carry x-schema-type "%s"%s.',
            $document->schemaCount(),
            Document::SCHEMA_TYPE,
            $counts === [] ? '' : ' (' . implode(', ', $counts) . ')',
        );

        if ($options->allowEmpty) {
            $this->warn($message);

            return ExitCode::OK;
        }

        throw GeneratorException::noBlocks($message, [
            'typed blocks live at https://api.flyo.cloud/nitro/v1/openapi/schemas and need a '
                . 'token; the public /nitro/v1/openapi endpoint has none.',
            'pass --allow-empty to treat this as success.',
        ]);
    }

    private function report(WriteResult $result, Options $options, int $blocks): void
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

        $this->line(sprintf(
            '%d block%s -> %s (%s, %d unchanged%s)',
            $blocks,
            $blocks === 1 ? '' : 's',
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
        fwrite($this->stderr, Options::PROGRAM . ': error: ' . $message . "\n");
    }

    private function hint(string $message): void
    {
        fwrite($this->stderr, '  hint: ' . $message . "\n");
    }
}
