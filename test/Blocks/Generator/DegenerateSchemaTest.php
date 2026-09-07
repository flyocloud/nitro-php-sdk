<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\Document;
use Flyo\Blocks\Generator\Planner;
use Flyo\Blocks\Generator\Renderer;
use Flyo\Blocks\Generator\Warnings;
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
        $rendered = $source['BlockBare.php'];

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

        $this->assertSame(['BlockBare.php'], array_keys($source));
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

        $rendered = $source['BlockPartial.php'];

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

        $rendered = $source['BlockNasty.php'];

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

        $rendered = $source['BlockLong.php'];

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

        self::assertValidPhp($source['BlockBinary.php']);
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

        $rendered = $source['BlockOdd.php'];

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

        self::assertValidPhp($source['BlockNumeric.php']);
        $this->assertStringContainsString("'2024': string|null", $source['BlockNumeric.php']);
    }

    /**
     * @param array<string, mixed> $schemas
     * @return array<string, string>
     */
    private static function render(array $schemas): array
    {
        $document = new Document(['components' => ['schemas' => $schemas]]);
        $classes = (new Planner())->plan($document, 'D\Blocks', new Warnings());
        $renderer = new Renderer();

        $files = [];
        foreach ($classes as $class) {
            $files[$class->relativePath] = $renderer->render($class);
        }

        return $files;
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
