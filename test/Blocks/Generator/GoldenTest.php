<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\Warnings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Compares generated output against the committed `expected/` trees.
 *
 * Run with UPDATE_FIXTURES=1 to rewrite them after an intentional change, then read the diff.
 */
class GoldenTest extends TestCase
{
    use FixtureTrait;

    #[DataProvider('fixtures')]
    public function testMatchesGoldenFiles(string $fixture, string $namespace): void
    {
        $actual = self::renderAll(self::loadDocument($fixture), $namespace);

        if (getenv('UPDATE_FIXTURES') === '1') {
            $dir = self::fixtureDir($fixture) . '/expected';

            foreach ($actual as $relative => $contents) {
                file_put_contents($dir . '/' . $relative, $contents);
            }

            $this->markTestSkipped('fixtures updated');
        }

        $this->assertSame(self::readExpected($fixture), $actual);
    }

    /**
     * Rendering twice must produce identical bytes: nothing in the output may depend on a clock,
     * a version, or any other ambient state.
     */
    #[DataProvider('fixtures')]
    public function testIsIdempotent(string $fixture, string $namespace): void
    {
        $raw = self::loadDocument($fixture);

        $this->assertSame(
            self::renderAll($raw, $namespace),
            self::renderAll($raw, $namespace)
        );
    }

    /**
     * The server builds components.schemas in block-definition order, which changes whenever an
     * editor reorders blocks. Reordering the input must not change one byte of the output.
     */
    #[DataProvider('fixtures')]
    public function testIsIndependentOfSchemaOrder(string $fixture, string $namespace): void
    {
        $raw = self::loadDocument($fixture);
        $expected = self::renderAll($raw, $namespace);

        $reordered = $raw;
        $reordered['components']['schemas'] = array_reverse($raw['components']['schemas'], true);

        $this->assertSame($expected, self::renderAll($reordered, $namespace));
    }

    /**
     * Every generated file must carry the marker, or the Writer would never clean it up.
     */
    #[DataProvider('fixtures')]
    public function testEveryFileCarriesTheGeneratedMarker(string $fixture, string $namespace): void
    {
        $files = self::renderAll(self::loadDocument($fixture), $namespace);

        $this->assertNotEmpty($files);

        foreach ($files as $relative => $contents) {
            $this->assertStringContainsString(
                \Flyo\Blocks\Generator\Renderer::MARKER,
                $contents,
                $relative . ' is missing the @generated marker'
            );
        }
    }

    /**
     * Generated files must be syntactically valid PHP declaring exactly the expected class.
     */
    #[DataProvider('fixtures')]
    public function testGeneratedFilesAreValidPhp(string $fixture, string $namespace): void
    {
        foreach (self::renderAll(self::loadDocument($fixture), $namespace) as $relative => $contents) {
            $path = tempnam(sys_get_temp_dir(), 'flyo-block-') . '.php';
            file_put_contents($path, $contents);

            $output = [];
            $status = 0;
            exec(escapeshellcmd(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
            unlink($path);

            $this->assertSame(0, $status, $relative . ': ' . implode("\n", $output));

            $expectedClass = basename($relative, '.php');
            $this->assertStringContainsString(
                'class ' . $expectedClass . ' extends \Flyo\Model\Block',
                $contents,
                $relative . ' should declare class ' . $expectedClass
            );
            $this->assertStringContainsString('namespace ' . $namespace . ';', $contents);
        }
    }

    /**
     * The coverage fixture deliberately contains an unmergeable allOf and a dangling $ref, so the
     * generator must say so rather than emit a silently wrong type.
     */
    public function testCoverageFixtureReportsItsDeliberateProblems(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('full'), 'Fixture\Full', $warnings);

        $joined = implode("\n", $warnings->all());

        $this->assertStringContainsString('all_of_unmergeable', $joined);
        $this->assertStringContainsString('ref_dangling', $joined);
    }

    public function testCollisionsAreReportedWithBothClassNames(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('collisions'), 'Fixture\Collisions', $warnings);

        $joined = implode("\n", $warnings->all());

        $this->assertStringContainsString('want the class name BlockHeroBanner', $joined);
        $this->assertStringContainsString('BlockHeroBanner2', $joined);
    }

    /**
     * A document with no typed blocks produces no files at all -- not an empty class, not a stub.
     */
    public function testDocumentWithoutTypedBlocksProducesNothing(): void
    {
        $this->assertSame([], self::renderAll(self::loadDocument('entities-only'), 'Fixture\None'));
    }
}
