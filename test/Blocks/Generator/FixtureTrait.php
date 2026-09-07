<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\Document;
use Flyo\Blocks\Generator\Planner;
use Flyo\Blocks\Generator\Renderer;
use Flyo\Blocks\Generator\Warnings;

/**
 * Shared fixture plumbing.
 *
 * Generation goes through Planner + Renderer rather than the Writer, so the golden comparison is
 * pure string work with no filesystem involved.
 */
trait FixtureTrait
{
    /**
     * Each fixture directory and the namespace its golden files are generated under. A distinct
     * namespace per fixture keeps the committed classes from colliding when PHPStan analyses them
     * all together.
     *
     * @var array<string, string>
     */
    private const FIXTURE_NAMESPACES = [
        'hero' => 'Fixture\Hero',
        'full' => 'Fixture\Full',
        'collisions' => 'Fixture\Collisions',
    ];

    /**
     * @return array<string, array{string, string}>
     */
    public static function fixtures(): array
    {
        $cases = [];

        foreach (self::FIXTURE_NAMESPACES as $fixture => $namespace) {
            $cases[$fixture] = [$fixture, $namespace];
        }

        return $cases;
    }

    private static function fixtureDir(string $fixture): string
    {
        return dirname(__DIR__, 2) . '/fixtures/blocks/' . $fixture;
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
    private static function renderAll(array $raw, string $namespace, ?Warnings $warnings = null): array
    {
        $warnings ??= new Warnings();
        $classes = (new Planner())->plan(new Document($raw), $namespace, $warnings);
        $renderer = new Renderer();

        $files = [];
        foreach ($classes as $class) {
            $files[$class->relativePath] = $renderer->render($class);
        }

        ksort($files, SORT_STRING);

        return $files;
    }

    /**
     * @return array<string, string> relative path => committed source
     */
    private static function readExpected(string $fixture): array
    {
        $dir = self::fixtureDir($fixture) . '/expected';
        $files = [];

        foreach (glob($dir . '/*.php') ?: [] as $path) {
            $contents = file_get_contents($path);
            $files[basename($path)] = $contents === false ? '' : $contents;
        }

        ksort($files, SORT_STRING);

        return $files;
    }
}
