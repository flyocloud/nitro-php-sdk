<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\Document;
use Flyo\Generator\Planner;
use Flyo\Generator\Profile;
use Flyo\Generator\Renderer;
use Flyo\Generator\Warnings;

/**
 * Shared fixture plumbing.
 *
 * Generation goes through Planner + Renderer rather than the Writer, so the golden comparison is
 * pure string work with no filesystem involved.
 */
trait FixtureTrait
{
    /**
     * Each fixture directory, the namespace its golden files are generated under, and the profile
     * that generates them. A distinct namespace per fixture keeps the committed classes from
     * colliding when PHPStan analyses them all together.
     *
     * The `blocks/` fixtures are the output of the deprecated flyo-generate-blocks, which must not
     * change by a single byte for as long as that command exists.
     *
     * @var array<string, array{string, string}>
     */
    private const FIXTURES = [
        'blocks/hero' => ['Fixture\Hero', 'blocks'],
        'blocks/full' => ['Fixture\Full', 'blocks'],
        'blocks/collisions' => ['Fixture\Collisions', 'blocks'],
        'types/site' => ['Fixture\Site', 'types'],
    ];

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function fixtures(): array
    {
        $cases = [];

        foreach (self::FIXTURES as $fixture => [$namespace, $profile]) {
            $cases[$fixture] = [$fixture, $namespace, $profile];
        }

        return $cases;
    }

    private static function profile(string $name): Profile
    {
        return $name === 'blocks' ? Profile::blocks() : Profile::types();
    }

    private static function fixtureDir(string $fixture): string
    {
        return dirname(__DIR__) . '/fixtures/' . $fixture;
    }

    /**
     * @return array<string, mixed>
     */
    private static function loadDocument(string $fixture): array
    {
        $path = self::fixtureDir($fixture) . '/openapi.json';
        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new \RuntimeException('missing fixture: ' . $path);
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * @param array<string, mixed> $raw
     * @return array<string, string> relative path => rendered source
     */
    private static function renderAll(
        array $raw,
        string $namespace,
        string $profile,
        ?Warnings $warnings = null,
    ): array {
        $warnings ??= new Warnings();
        $profile = self::profile($profile);
        $plan = (new Planner())->plan(new Document($raw), $profile, $namespace, $warnings);
        $renderer = new Renderer($profile->program);

        $files = [];
        foreach ($plan->classes as $path => $class) {
            $files[$path] = $renderer->render($class);
        }

        return $files;
    }

    /**
     * @return array<string, string> relative path => committed source
     */
    private static function readExpected(string $fixture): array
    {
        $dir = self::fixtureDir($fixture) . '/expected';
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($dir) + 1));
            $contents = file_get_contents($file->getPathname());
            $files[$relative] = $contents === false ? '' : $contents;
        }

        ksort($files, SORT_STRING);

        return $files;
    }
}
