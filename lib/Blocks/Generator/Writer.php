<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * The only part of the generator that touches the filesystem.
 *
 * Two properties worth keeping: a file whose bytes already match is not rewritten (so mtimes stay
 * put and file watchers and OPcache do not churn), and a file is only ever deleted when it carries
 * {@see Renderer::MARKER}. That makes hand-written files in the same directory safe by
 * construction rather than by convention.
 */
final class Writer
{
    /** Enough to reach the marker in the class docblock of any generated file. */
    private const MARKER_SCAN_BYTES = 4096;

    /**
     * @param array<string, string> $files relative path => file contents
     * @throws GeneratorException
     */
    public function write(array $files, Options $options): WriteResult
    {
        $target = rtrim($options->target, '/');
        $target = $target === '' ? '.' : $target;

        $this->assertUsable($target, $options->dryRun);

        ksort($files, SORT_STRING);

        $written = [];
        $unchanged = [];

        foreach ($files as $relative => $contents) {
            $path = $target . '/' . $relative;

            if (is_file($path) && file_get_contents($path) === $contents) {
                $unchanged[] = $relative;
                continue;
            }

            $written[] = $relative;

            if ($options->dryRun) {
                continue;
            }

            $this->writeFile($path, $contents);
        }

        $removed = $options->clean
            ? $this->removeStale($target, array_keys($files), $options->dryRun)
            : [];

        return new WriteResult($written, $unchanged, $removed, $this->describe($target));
    }

    /**
     * @throws GeneratorException
     */
    private function assertUsable(string $target, bool $dryRun): void
    {
        if (is_file($target)) {
            throw GeneratorException::filesystem(
                sprintf('the target exists and is a file, not a directory: %s', $this->describe($target)),
            );
        }

        if (is_dir($target)) {
            if (!is_writable($target) && !$dryRun) {
                throw GeneratorException::filesystem(
                    sprintf('the target directory is not writable: %s', $this->describe($target)),
                );
            }

            return;
        }

        if ($dryRun) {
            // Nothing to create, and everything counts as new.
            return;
        }

        if (!@mkdir($target, 0777, true) && !is_dir($target)) {
            throw GeneratorException::filesystem(
                sprintf('could not create the target directory: %s', $this->describe($target)),
                ['check that the parent directory exists and is writable.'],
            );
        }
    }

    /**
     * @throws GeneratorException
     */
    private function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);

        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw GeneratorException::filesystem(
                sprintf('could not create the directory: %s', $this->describe($directory)),
            );
        }

        if (@file_put_contents($path, $contents) === false) {
            throw GeneratorException::filesystem(
                sprintf('could not write: %s', $this->describe($path)),
            );
        }
    }

    /**
     * Deletes generated files the current run no longer produces.
     *
     * @param list<string> $keep relative paths this run produced
     * @return list<string>
     */
    private function removeStale(string $target, array $keep, bool $dryRun): array
    {
        if (!is_dir($target)) {
            return [];
        }

        $existing = glob($target . '/*.php') ?: [];
        sort($existing, SORT_STRING);

        $removed = [];

        foreach ($existing as $path) {
            $relative = basename($path);

            if (in_array($relative, $keep, true) || !$this->isGenerated($path)) {
                continue;
            }

            $removed[] = $relative;

            if (!$dryRun) {
                @unlink($path);
            }
        }

        return $removed;
    }

    private function isGenerated(string $path): bool
    {
        $handle = @fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $head = fread($handle, self::MARKER_SCAN_BYTES);
        fclose($handle);

        return is_string($head) && str_contains($head, Renderer::MARKER);
    }

    /**
     * An absolute path where possible, so a mistyped relative target is obvious in a message.
     */
    private function describe(string $path): string
    {
        $real = realpath($path);

        return $real === false ? $path : $real;
    }
}
