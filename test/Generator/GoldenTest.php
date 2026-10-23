<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\Renderer;
use Flyo\Generator\Warnings;
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
    public function testMatchesGoldenFiles(string $fixture, string $namespace, string $profile): void
    {
        $actual = self::renderAll(self::loadDocument($fixture), $namespace, $profile);

        if (getenv('UPDATE_FIXTURES') === '1') {
            $dir = self::fixtureDir($fixture) . '/expected';

            foreach ($actual as $relative => $contents) {
                if (!is_dir(dirname($dir . '/' . $relative))) {
                    mkdir(dirname($dir . '/' . $relative), 0777, true);
                }

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
    public function testIsIdempotent(string $fixture, string $namespace, string $profile): void
    {
        $raw = self::loadDocument($fixture);

        $this->assertSame(
            self::renderAll($raw, $namespace, $profile),
            self::renderAll($raw, $namespace, $profile)
        );
    }

    /**
     * The server builds components.schemas in definition order, which changes whenever an editor
     * reorders blocks. Reordering the input must not change one byte of the output.
     */
    #[DataProvider('fixtures')]
    public function testIsIndependentOfSchemaOrder(string $fixture, string $namespace, string $profile): void
    {
        $raw = self::loadDocument($fixture);
        $expected = self::renderAll($raw, $namespace, $profile);

        $reordered = $raw;
        $reordered['components']['schemas'] = array_reverse($raw['components']['schemas'], true);

        $this->assertSame($expected, self::renderAll($reordered, $namespace, $profile));
    }

    /**
     * Every generated file must carry the marker, or the Writer would never clean it up.
     */
    #[DataProvider('fixtures')]
    public function testEveryFileCarriesTheGeneratedMarker(string $fixture, string $namespace, string $profile): void
    {
        $files = self::renderAll(self::loadDocument($fixture), $namespace, $profile);

        $this->assertNotEmpty($files);

        foreach ($files as $relative => $contents) {
            $this->assertStringContainsString(
                Renderer::MARKER,
                $contents,
                $relative . ' is missing the @generated marker'
            );
        }
    }

    /**
     * Generated files must be syntactically valid PHP declaring exactly the expected class, in
     * the namespace its directory maps to under PSR-4.
     */
    #[DataProvider('fixtures')]
    public function testGeneratedFilesAreValidPhp(string $fixture, string $namespace, string $profile): void
    {
        foreach (self::renderAll(self::loadDocument($fixture), $namespace, $profile) as $relative => $contents) {
            $path = tempnam(sys_get_temp_dir(), 'flyo-types-') . '.php';
            file_put_contents($path, $contents);

            $output = [];
            $status = 0;
            exec(escapeshellcmd(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
            unlink($path);

            $this->assertSame(0, $status, $relative . ': ' . implode("\n", $output));

            $expectedClass = basename($relative, '.php');
            $this->assertMatchesRegularExpression(
                '/^class ' . $expectedClass . ' extends \\\\Flyo\\\\(Model\\\\\w+|Wysiwyg\\\\WysiwygNode)$/m',
                $contents,
                $relative . ' should declare class ' . $expectedClass
            );

            $directory = dirname($relative);
            $expectedNamespace = $namespace . ($directory === '.' ? '' : '\\' . str_replace('/', '\\', $directory));
            $this->assertStringContainsString('namespace ' . $expectedNamespace . ';', $contents);
        }
    }

    /**
     * The coverage fixture deliberately contains an unmergeable allOf and a dangling $ref, so the
     * generator must say so rather than emit a silently wrong type.
     */
    public function testCoverageFixtureReportsItsDeliberateProblems(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('blocks/full'), 'Fixture\Full', 'blocks', $warnings);

        $joined = implode("\n", $warnings->all());

        $this->assertStringContainsString('all_of_unmergeable', $joined);
        $this->assertStringContainsString('ref_dangling', $joined);
    }

    public function testCollisionsAreReportedWithBothClassNames(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('blocks/collisions'), 'Fixture\Collisions', 'blocks', $warnings);

        $joined = implode("\n", $warnings->all());

        $this->assertStringContainsString('want the class name BlockHeroBanner', $joined);
        $this->assertStringContainsString('BlockHeroBanner2', $joined);
    }

    /**
     * A document with no typed blocks produces nothing for the deprecated command -- not an empty
     * class, not a stub. Its entity schemas were never its business.
     */
    public function testTheDeprecatedCommandIgnoresEverythingButBlocks(): void
    {
        $this->assertSame([], self::renderAll(self::loadDocument('blocks/entities-only'), 'Fixture\None', 'blocks'));
        $this->assertSame(
            ['BlockTeaser.php'],
            array_keys(self::renderAll(self::loadDocument('types/site'), 'Fixture\Legacy', 'blocks'))
        );
    }

    public function testTheTypesCommandWritesOneDirectoryPerKind(): void
    {
        $this->assertSame(
            ['Entities/EntityArticle.php', 'Entities/EntityEvent.php'],
            array_keys(self::renderAll(self::loadDocument('blocks/entities-only'), 'Fixture\None', 'types'))
        );
    }

    /**
     * Migrating from flyo-generate-blocks must not change a block class beyond what the move
     * itself implies: its namespace, and the command the marker line names.
     */
    public function testABlockClassIsTheSameUnderBothCommands(): void
    {
        $raw = self::loadDocument('types/site');

        $legacy = self::renderAll($raw, 'App\Blocks', 'blocks')['BlockTeaser.php'];
        $types = self::renderAll($raw, 'App', 'types')['Blocks/BlockTeaser.php'];

        $this->assertSame(
            str_replace('vendor/bin/flyo-generate-blocks', 'vendor/bin/flyo-generate-types', $legacy),
            $types
        );
    }

    /**
     * The enum constants came after flyo-generate-blocks was deprecated, and its output stays as it
     * was. Apart from them, a block with options is the same class under both commands.
     */
    public function testOnlyTheTypesCommandGeneratesEnumConstants(): void
    {
        $raw = self::loadDocument('types/enums');

        $legacy = self::renderAll($raw, 'App\Blocks', 'blocks')['BlockCard.php'];
        $types = self::renderAll($raw, 'App', 'types')['Blocks/BlockCard.php'];

        $this->assertStringNotContainsString('public const CONTENT_', $legacy);
        $this->assertStringNotContainsString('public const CONFIG_', $legacy);
        $this->assertStringContainsString("    public const CONTENT_SIZE_SMALL = 'small';\n", $types);
        $this->assertStringContainsString("    public const CONFIG_SIZE_SMALL = 'small';\n", $types);

        $withoutEnumConstants = preg_replace('/\n(    public const CON(TENT|FIG)_\w+ = .+;\n)+/', '', $types);

        $this->assertSame(
            str_replace('vendor/bin/flyo-generate-blocks', 'vendor/bin/flyo-generate-types', $legacy),
            $withoutEnumConstants
        );
    }

    /**
     * The options of one field are rendered as one block of constants, a blank line between fields.
     */
    public function testEnumConstantsAreGroupedByField(): void
    {
        $types = self::renderAll(self::loadDocument('types/enums'), 'App', 'types')['Blocks/BlockCard.php'];

        $this->assertStringContainsString(
            "    public const COMPONENT = 'Card';\n"
                . "\n"
                . "    public const CONTENT_SIZE_SMALL = 'small';\n"
                . "    public const CONTENT_SIZE_MEDIUM = 'medium';\n"
                . "    public const CONTENT_SIZE_LARGE = 'large';\n"
                . "\n"
                . "    public const CONTENT_ALIGN_LEFT = 'left';\n",
            $types
        );
    }

    /**
     * A schema type added server-side after this release must be named, not skipped silently.
     */
    public function testUnsupportedSchemaTypesAreReported(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('types/site'), 'Fixture\Site', 'types', $warnings);

        $this->assertSame(
            ['1 schema carries x-schema-type "gadget", which this version of flyo/nitro-php does not '
                . 'generate; it was skipped.'],
            $warnings->all()
        );
    }

    public function testTheDeprecatedCommandDoesNotReportOtherSchemaTypes(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('types/site'), 'Fixture\Site', 'blocks', $warnings);

        $this->assertTrue($warnings->isEmpty());
    }

    /**
     * The item schema carries no marker; it is generated because the containers reference it.
     */
    public function testTheContainerItemSchemaIsGeneratedThroughItsReferences(): void
    {
        $files = self::renderAll(self::loadDocument('types/site'), 'Fixture\Site', 'types');

        $this->assertArrayHasKey('Containers/ContainerItem.php', $files);
        $this->assertStringContainsString(
            'class ContainerItem extends \Flyo\Model\ContainerPage',
            $files['Containers/ContainerItem.php']
        );
        $this->assertArrayNotHasKey('Containers/Containers.php', $files);
    }

    /**
     * The collection classes came after flyo-generate-blocks was deprecated, like the enum
     * constants: under it, a block keeps the getCollection() of the SDK model.
     */
    public function testOnlyTheTypesCommandGeneratesCollectionClasses(): void
    {
        $raw = self::loadDocument('types/collections');

        $legacy = self::renderAll($raw, 'App\Blocks', 'blocks');
        $types = self::renderAll($raw, 'App', 'types');

        $this->assertSame(
            ['BlockEventList.php', 'BlockNewsFeed.php', 'BlockTagCloud.php', 'BlockText.php'],
            array_keys($legacy)
        );
        foreach ($legacy as $relative => $contents) {
            $this->assertStringNotContainsString('getCollection', $contents, $relative);
        }

        $this->assertSame(
            [
                'Blocks/BlockEventList.php',
                'Blocks/BlockEventListCollection.php',
                'Blocks/BlockNewsFeed.php',
                'Blocks/BlockNewsFeedCollection.php',
                'Blocks/BlockTagCloud.php',
                'Blocks/BlockTagCloudCollection.php',
                'Blocks/BlockText.php',
            ],
            array_keys($types)
        );
    }

    /**
     * A block keeps its class name when it collides with the collection class of another block:
     * the collection class is numbered instead, and the generator says so.
     */
    public function testACollectionClassNeverTakesTheNameOfABlock(): void
    {
        $raw = self::loadDocument('types/collections');
        $schemas = $raw['components']['schemas'];
        $raw['components']['schemas'] = [
            'BlockEventList' => $schemas['BlockEventList'],
            'BlockEventListCollection' => $schemas['BlockText'],
        ];
        $raw['components']['schemas']['BlockEventListCollection']['properties']['component']['enum'] = ['EventListCollection'];

        $warnings = new Warnings();
        $files = self::renderAll($raw, 'App', 'types', $warnings);

        $this->assertStringContainsString(
            'class BlockEventListCollection extends \Flyo\Model\Block' . "\n",
            $files['Blocks/BlockEventListCollection.php']
        );
        $this->assertStringContainsString(
            'class BlockEventListCollection2 extends \Flyo\Model\BlockCollection',
            $files['Blocks/BlockEventListCollection2.php']
        );
        $this->assertStringContainsString(
            '@return \App\Blocks\BlockEventListCollection2',
            $files['Blocks/BlockEventList.php']
        );
        $this->assertStringContainsString('BlockEventList -> BlockEventListCollection2', implode("\n", $warnings->all()));
    }
}
