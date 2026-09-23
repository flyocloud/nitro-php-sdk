<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\Document;
use Flyo\Generator\Planner;
use Flyo\Generator\Profile;
use Flyo\Generator\Renderer;
use Flyo\Generator\Warnings;
use PHPUnit\Framework\TestCase;

/**
 * Malformed and half-populated schemas.
 *
 * The generator reads a document it does not control, over a wire, from a server that does not
 * validate `component` as a PHP-safe string. Anything missing must degrade to valid PHP rather
 * than a crash or a broken file.
 */
class DegenerateSchemaTest extends TestCase
{
    public function testABlockSchemaWithNoPropertiesAtAllStillProducesValidPhp(): void
    {
        $source = self::render(['BlockBare' => ['type' => 'object', 'x-schema-type' => 'block']]);

        $this->assertCount(1, $source);
        $rendered = $source['Blocks/BlockBare.php'];

        $this->assertStringContainsString('class BlockBare extends \Flyo\Model\Block', $rendered);
        $this->assertStringContainsString(Renderer::MARKER, $rendered);
        self::assertValidPhp($rendered);
    }

    /**
     * The schema key already carries the "Block" prefix, so the fallback must not double it.
     */
    public function testTheSchemaKeyFallbackDoesNotDoubleThePrefix(): void
    {
        $source = self::render(['BlockBare' => ['type' => 'object', 'x-schema-type' => 'block']]);

        $this->assertSame(['Blocks/BlockBare.php'], array_keys($source));
    }

    public function testAPartialBlockOnlyGetsTheGettersItHasSchemasFor(): void
    {
        $source = self::render([
            'BlockPartial' => [
                'type' => 'object',
                'x-schema-type' => 'block',
                'properties' => [
                    'identifier' => ['enum' => ['partial'], 'type' => 'string'],
                    'content' => ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]],
                ],
            ],
        ]);

        $rendered = $source['Blocks/BlockPartial.php'];

        $this->assertStringContainsString("public const IDENTIFIER = 'partial';", $rendered);
        $this->assertStringContainsString('public function getContent()', $rendered);
        $this->assertStringNotContainsString('public function getConfig()', $rendered);
        $this->assertStringNotContainsString('public function getItems()', $rendered);
        $this->assertStringNotContainsString('COMPONENT', $rendered);
        $this->assertStringNotContainsString('SLOTS', $rendered);
        self::assertValidPhp($rendered);
    }

    /**
     * Labels and hints are author-supplied free text, so they can close the comment, look like a
     * phpdoc tag, or span lines. All three would produce a broken or misleading file.
     */
    public function testHostileDescriptionsCannotBreakOutOfTheDocblock(): void
    {
        $source = self::render([
            'BlockNasty' => [
                'type' => 'object',
                'x-schema-type' => 'block',
                'title' => "Ends the comment */ and then some\nnewlines",
                'description' => "@return int injected tag, and */ again",
                'properties' => [
                    'component' => ['enum' => ['Nasty'], 'type' => 'string'],
                    'content' => [
                        'type' => 'object',
                        'description' => "*/ echo 'hi'; /*",
                        'properties' => ['a' => ['type' => 'string']],
                    ],
                ],
            ],
        ]);

        $rendered = $source['Blocks/BlockNasty.php'];

        // The text itself may survive as comment prose -- what must not survive is its ability to
        // close the docblock or to be read as a tag.
        $this->assertSame(
            substr_count($rendered, '/**'),
            substr_count($rendered, '*/'),
            'unbalanced comment delimiters: a description closed a docblock early'
        );
        $this->assertStringContainsString('*&#47;', $rendered, 'the comment terminator should be escaped');
        $this->assertStringNotContainsString('@return int injected', $rendered);
        $this->assertStringContainsString('&#64;return int injected', $rendered);

        // A newline in a description must not break out of the ' * ' prefix.
        foreach (explode("\n", $rendered) as $line) {
            if ($line !== '' && !str_starts_with(ltrim($line), '*') && !str_starts_with(ltrim($line), '/*')) {
                $this->assertDoesNotMatchRegularExpression(
                    '/^\s*(and then some|newlines)/',
                    $line,
                    'a description leaked outside its docblock'
                );
            }
        }

        self::assertValidPhp($rendered);
    }

    public function testAVeryLongDescriptionIsTruncatedRatherThanEmitted(): void
    {
        $source = self::render([
            'BlockLong' => [
                'type' => 'object',
                'x-schema-type' => 'block',
                'component' => 'Long',
                'title' => 'Long',
                'description' => str_repeat('lorem ipsum dolor sit amet ', 200),
                'properties' => ['component' => ['enum' => ['Long'], 'type' => 'string']],
            ],
        ]);

        $rendered = $source['Blocks/BlockLong.php'];

        $this->assertStringContainsString('…', $rendered);
        self::assertValidPhp($rendered);

        foreach (explode("\n", $rendered) as $line) {
            $this->assertLessThanOrEqual(120, mb_strlen($line), 'line too long: ' . $line);
        }
    }

    public function testInvalidUtf8InADescriptionIsDropped(): void
    {
        $source = self::render([
            'BlockBinary' => [
                'type' => 'object',
                'x-schema-type' => 'block',
                'title' => "bad \xC3\x28 bytes",
                'properties' => ['component' => ['enum' => ['Binary'], 'type' => 'string']],
            ],
        ]);

        self::assertValidPhp($source['Blocks/BlockBinary.php']);
    }

    public function testPropertiesThatAreNotObjectsAreSkipped(): void
    {
        $source = self::render([
            'BlockOdd' => [
                'type' => 'object',
                'x-schema-type' => 'block',
                'properties' => [
                    'component' => ['enum' => ['Odd'], 'type' => 'string'],
                    'content' => [
                        'type' => 'object',
                        'properties' => [
                            'fine' => ['type' => 'string'],
                            'broken' => 'not a schema at all',
                        ],
                    ],
                ],
            ],
        ]);

        $rendered = $source['Blocks/BlockOdd.php'];

        $this->assertStringContainsString('fine: string|null', $rendered);
        $this->assertStringNotContainsString('broken', $rendered);
        self::assertValidPhp($rendered);
    }

    public function testANumericPropertyKeyIsHandled(): void
    {
        $source = self::render([
            'BlockNumeric' => [
                'type' => 'object',
                'x-schema-type' => 'block',
                'properties' => [
                    'component' => ['enum' => ['Numeric'], 'type' => 'string'],
                    'content' => [
                        'type' => 'object',
                        'properties' => ['2024' => ['type' => 'string']],
                    ],
                ],
            ],
        ]);

        self::assertValidPhp($source['Blocks/BlockNumeric.php']);
        $this->assertStringContainsString("'2024': string|null", $source['Blocks/BlockNumeric.php']);
    }

    // -- entities ----------------------------------------------------------------------------

    public function testAnEntityWithNoPropertiesIsAnOpaqueObject(): void
    {
        $source = self::render(['EntityEmpty' => ['type' => 'object', 'x-schema-type' => 'entity']]);

        $rendered = $source['Entities/EntityEmpty.php'];

        $this->assertStringContainsString('class EntityEmpty extends \Flyo\Model\Entity', $rendered);
        $this->assertStringContainsString('@return \stdClass|null', $rendered);
        self::assertValidPhp($rendered);
    }

    public function testAnEntityKeyWithoutThePrefixGetsIt(): void
    {
        $source = self::render(['news' => ['type' => 'object', 'x-schema-type' => 'entity']]);

        $this->assertSame(['Entities/EntityNews.php'], array_keys($source));
    }

    /**
     * Presence is declared by the enclosing schema, which for `model` is the generic entity.
     */
    public function testTheModelIsNotNullableOnceTheGenericEntityRequiresIt(): void
    {
        $entity = [
            'type' => 'object',
            'x-schema-type' => 'entity',
            'properties' => ['title' => ['type' => 'string']],
        ];

        $optional = self::render(['EntityNews' => $entity])['Entities/EntityNews.php'];
        $required = self::render([
            'entity' => ['type' => 'object', 'required' => ['model']],
            'EntityNews' => $entity,
        ])['Entities/EntityNews.php'];

        self::assertReturns('object{title: string|null}|null', $optional);
        self::assertReturns('object{title: string|null}', $required);
    }

    /**
     * The model is a plain decoded object all the way down, so a reference to another entity
     * schema is described inline instead of pointing at a class nothing is an instance of.
     */
    public function testAReferenceToAnotherEntityIsInlined(): void
    {
        $source = self::render([
            'EntityA' => [
                'type' => 'object',
                'x-schema-type' => 'entity',
                'properties' => ['other' => ['$ref' => '#/components/schemas/EntityB']],
            ],
            'EntityB' => [
                'type' => 'object',
                'x-schema-type' => 'entity',
                'properties' => ['name' => ['type' => 'string']],
            ],
        ]);

        $this->assertStringContainsString('other: object{name: string|null}|null', $source['Entities/EntityA.php']);
    }

    // -- containers --------------------------------------------------------------------------

    public function testAContainerWithoutAnItemReferenceKeepsTheInheritedGetter(): void
    {
        $source = self::render([
            'ContainerMain' => [
                'type' => 'object',
                'x-schema-type' => 'container',
                'properties' => [
                    'identifier' => ['enum' => ['main'], 'type' => 'string'],
                    'items' => ['type' => 'array', 'items' => ['type' => 'object']],
                ],
            ],
        ]);

        $this->assertSame(['Containers/ContainerMain.php'], array_keys($source));

        $rendered = $source['Containers/ContainerMain.php'];

        $this->assertStringContainsString("public const IDENTIFIER = 'main';", $rendered);
        $this->assertStringNotContainsString('getItems', $rendered);
        self::assertValidPhp($rendered);
    }

    public function testADanglingItemReferenceKeepsTheInheritedGetter(): void
    {
        $source = self::render([
            'ContainerMain' => [
                'type' => 'object',
                'x-schema-type' => 'container',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Gone']],
                ],
            ],
        ]);

        $this->assertSame(['Containers/ContainerMain.php'], array_keys($source));
        $this->assertStringNotContainsString('getItems', $source['Containers/ContainerMain.php']);
    }

    /**
     * Items are found by reference rather than by name, and a different schema for the children
     * gets a class of its own.
     */
    public function testItemSchemasAreFollowedThroughTheirChildren(): void
    {
        $source = self::render([
            'ContainerMain' => [
                'type' => 'object',
                'x-schema-type' => 'container',
                'required' => ['items'],
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/NavEntry']],
                ],
            ],
            'NavEntry' => [
                'type' => 'object',
                'properties' => [
                    'children' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/NavLeaf']],
                ],
            ],
            'NavLeaf' => ['type' => 'object', 'properties' => ['label' => ['type' => 'string']]],
        ]);

        $this->assertSame(
            ['Containers/ContainerMain.php', 'Containers/ContainerNavEntry.php', 'Containers/ContainerNavLeaf.php'],
            array_keys($source)
        );
        self::assertReturns('array<int, \D\Containers\ContainerNavEntry>', $source['Containers/ContainerMain.php']);
        self::assertReturns('array<int, \D\Containers\ContainerNavLeaf>|null', $source['Containers/ContainerNavEntry.php']);

        foreach ($source as $rendered) {
            self::assertValidPhp($rendered);
        }
    }

    public function testItemPropertiesWithoutDeclaredKeysAreAnOpenArray(): void
    {
        $source = self::render([
            'ContainerMain' => [
                'type' => 'object',
                'x-schema-type' => 'container',
                'properties' => [
                    'items' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ContainerItem']],
                ],
            ],
            'ContainerItem' => [
                'type' => 'object',
                'required' => ['properties'],
                'properties' => ['properties' => ['type' => 'object', 'properties' => []]],
            ],
        ]);

        self::assertReturns('array<string, mixed>', $source['Containers/ContainerItem.php']);
    }

    /**
     * @param array<string, mixed> $schemas
     * @return array<string, string>
     */
    private static function render(array $schemas): array
    {
        $document = new Document(['components' => ['schemas' => $schemas]]);
        $profile = Profile::types();
        $plan = (new Planner())->plan($document, $profile, 'D', new Warnings());
        $renderer = new Renderer($profile->program);

        $files = [];
        foreach ($plan->classes as $path => $class) {
            $files[$path] = $renderer->render($class);
        }

        return $files;
    }

    /**
     * The exact `@return` type, whether the docblock spans lines or collapsed to one.
     */
    private static function assertReturns(string $type, string $rendered): void
    {
        self::assertMatchesRegularExpression('/@return ' . preg_quote($type, '/') . '( \*\/)?$/m', $rendered);
    }

    private static function assertValidPhp(string $source): void
    {
        $path = tempnam(sys_get_temp_dir(), 'flyo-degenerate-') . '.php';
        file_put_contents($path, $source);

        $output = [];
        $status = 0;
        exec(escapeshellcmd(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
        unlink($path);

        self::assertSame(0, $status, implode("\n", $output) . "\n---\n" . $source);
    }
}
