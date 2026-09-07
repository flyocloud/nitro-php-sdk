<?php

namespace Flyo\Test\Blocks;

use Flyo\Model\Block;
use Flyo\Model\BlockSlotValue;
use Flyo\ObjectSerializer;
use PHPUnit\Framework\TestCase;

/**
 * Proves the generated annotations describe what actually happens at runtime.
 *
 * The generated classes make static promises about a payload nobody hydrates, so the risk is that
 * those promises quietly become fiction. This test walks every access form the generated types
 * advertise, against a payload decoded exactly the way \Flyo\Api\PagesApi decodes one, and fails
 * if any of them stops working.
 */
class AccessPatternTest extends TestCase
{
    /** @var class-string<Block> */
    private const GENERATED = 'Fixture\Hero\BlockHero';

    public static function setUpBeforeClass(): void
    {
        // The golden files are excluded from the autoloader (they are fixtures, not library code),
        // so load the one we exercise by hand.
        require_once dirname(__DIR__) . '/fixtures/blocks/hero/expected/BlockHero.php';
    }

    // -- the access pattern the generated docblocks describe ----------------------------------

    public function testTopLevelContentIsReachableThroughTheGetter(): void
    {
        $block = self::block();

        $this->assertSame('Willkommen', $block->getContent()->title);
        $this->assertSame('Lorem ipsum.', $block->getContent()->text);
        $this->assertFalse($block->getContent()->_empty);
    }

    public function testNestedObjectsAreReachableByPropertyAccess(): void
    {
        $content = self::block()->getContent();

        $this->assertSame('https://storage.flyo.cloud/hero.jpg', $content->image->source);
        $this->assertSame('image/jpeg', $content->image->mime_type);
        $this->assertSame('/newsletter', $content->link->href);
    }

    /**
     * `number` maps to int|float because json_decode returns whichever the JSON actually held.
     */
    public function testANumberKeepsItsJsonType(): void
    {
        $this->assertSame(154311, self::block()->getContent()->image->id);
    }

    /**
     * An open map (additionalProperties, no properties) stays a \stdClass, which is what the
     * generated type says.
     */
    public function testOpenMapsAreStdClass(): void
    {
        $extras = self::block()->getContent()->link->extras;

        $this->assertInstanceOf(\stdClass::class, $extras);
        $this->assertSame('foobar.jpg', $extras->filename);
    }

    public function testConfigIsReachable(): void
    {
        $this->assertTrue(self::block()->getConfig()->_empty);
    }

    public function testItemsAreAListOfPlainObjects(): void
    {
        $items = self::block()->getItems();

        $this->assertIsArray($items);
        $this->assertCount(2, $items);
        $this->assertSame('my-detail-page', $items[0]->link->entity_slug);
        $this->assertSame(12, $items[0]->link->entity_type_id);
        // The "or false" idiom the oneOf describes.
        $this->assertFalse($items[1]->link->entity_type_id);
    }

    public function testItemRoutesAreAnOpenMap(): void
    {
        $routes = self::block()->getItems()[0]->link->routes;

        $this->assertInstanceOf(\stdClass::class, $routes);
        $this->assertSame('/foo-bar', $routes->detail);
    }

    public function testItemsAreIterable(): void
    {
        $slugs = [];
        foreach (self::block()->getItems() ?? [] as $item) {
            $slugs[] = $item->link->entity_slug;
        }

        $this->assertSame(['my-detail-page', 'another-page'], $slugs);
    }

    /**
     * Slots are the one place the SDK already builds real models, so the parent's own type
     * (array<string, BlockSlotValue>) is exact and the generator leaves it alone.
     */
    public function testSlotsHoldRealModelsWhoseContentIsRealBlocks(): void
    {
        $slots = self::block()->getSlots();

        $this->assertIsArray($slots);
        $this->assertArrayHasKey('main', $slots);
        $this->assertInstanceOf(BlockSlotValue::class, $slots['main']);

        $children = $slots['main']->getContent();

        $this->assertIsArray($children);
        $this->assertCount(1, $children);
        $this->assertInstanceOf(Block::class, $children[0]);
        $this->assertSame('nested', $children[0]->getIdentifier());
    }

    /**
     * The deepest chain the generated types promise, in one go, and with no diagnostics of any
     * kind -- no warning, no deprecation, no notice.
     */
    public function testTheWholeAdvertisedChainEmitsNoDiagnostics(): void
    {
        $seen = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$seen): bool {
            $seen[] = $errno . ': ' . $errstr;

            return true;
        });

        try {
            $block = self::block();

            $block->getContent()->title;
            $block->getContent()->image->source;
            $block->getContent()->link->extras->filename;
            $block->getItems()[0]->link->entity_slug;
            $block->getItems()[0]->link->routes->detail;
            $block->getSlots()['main']->getContent()[0]->getIdentifier();
            $block->getIdentifier();
            $block->getUid();
            $block->getComponent();
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $seen);
    }

    // -- why the getters, and not $block->content ---------------------------------------------

    /**
     * Documents the reason the generated classes narrow getters rather than declaring properties:
     * Block keeps everything in a protected container and has no __get, so `$block->content` is
     * simply not a thing. Promising it in a docblock would be fiction.
     */
    public function testTopLevelPropertyAccessIsNotAvailableOnTheModel(): void
    {
        $block = self::block();

        $this->assertFalse(property_exists($block, 'content'));
        $this->assertFalse(method_exists($block, '__get'));

        // ArrayAccess is the other supported route, and it works.
        $this->assertSame('Willkommen', $block['content']->title);
    }

    /**
     * Below the top level there is no container and no magic: the values really are plain objects,
     * which is exactly what `object{...}` describes.
     */
    public function testValuesBelowTheTopLevelAreGenuinelyPlainObjects(): void
    {
        $content = self::block()->getContent();

        $this->assertInstanceOf(\stdClass::class, $content);
        $this->assertInstanceOf(\stdClass::class, $content->image);
    }

    // -- the generated class is a drop-in for the generic model -------------------------------

    public function testTheGeneratedClassIsABlock(): void
    {
        $this->assertInstanceOf(Block::class, self::block());
    }

    public function testConstantsMatchTheSchema(): void
    {
        $class = self::GENERATED;

        $this->assertSame('hero', $class::IDENTIFIER);
        $this->assertSame('Hero', $class::COMPONENT);
    }

    /**
     * The overrides only forward, so a typed block and a generic one must serialize identically.
     * If that ever diverges, the generated classes have started changing behaviour.
     */
    public function testSerializesIdenticallyToTheGenericModel(): void
    {
        $payload = self::payload();

        $generic = ObjectSerializer::deserialize($payload, Block::class, []);
        $typed = ObjectSerializer::deserialize($payload, self::GENERATED, []);

        $this->assertSame(json_encode($generic), json_encode($typed));
        $this->assertSame((string) $generic, (string) $typed);
    }

    public function testGettersReturnTheSameValuesAsTheGenericModel(): void
    {
        $payload = self::payload();

        $generic = ObjectSerializer::deserialize($payload, Block::class, []);
        $typed = ObjectSerializer::deserialize($payload, self::GENERATED, []);

        $this->assertEquals($generic->getContent(), $typed->getContent());
        $this->assertEquals($generic->getConfig(), $typed->getConfig());
        $this->assertEquals($generic->getItems(), $typed->getItems());
        $this->assertEquals($generic->getSlots(), $typed->getSlots());
    }

    /**
     * A block whose optional parts are simply absent must not explode: the getters return null,
     * which is why every non-required field is typed `|null`.
     */
    public function testAnEmptyPayloadYieldsNullsRatherThanErrors(): void
    {
        $block = ObjectSerializer::deserialize(json_decode('{"identifier":"hero"}'), self::GENERATED, []);

        $this->assertSame('hero', $block->getIdentifier());
        $this->assertNull($block->getContent());
        $this->assertNull($block->getConfig());
        $this->assertNull($block->getItems());
        $this->assertNull($block->getSlots());
    }

    // -- helpers -------------------------------------------------------------------------------

    private static function block(): Block
    {
        return ObjectSerializer::deserialize(self::payload(), self::GENERATED, []);
    }

    /**
     * Decoded the way \Flyo\Api\PagesApi does it: json_decode with assoc = false, which is why
     * every `object`-typed value ends up a \stdClass.
     */
    private static function payload(): \stdClass
    {
        $json = <<<'JSON'
        {
            "identifier": "hero",
            "uid": "b7f3a1",
            "component": "Hero",
            "content": {
                "title": "Willkommen",
                "text": "Lorem ipsum.",
                "image": {
                    "source": "https://storage.flyo.cloud/hero.jpg",
                    "caption": "John Doe",
                    "copyright": "shutterstock",
                    "name": "hero.jpg",
                    "id": 154311,
                    "mime_type": "image/jpeg"
                },
                "link": {
                    "type": "nitropagelink",
                    "target": "_blank",
                    "raw": "newsletter",
                    "href": "/newsletter",
                    "extras": { "filename": "foobar.jpg" }
                },
                "_empty": false
            },
            "config": { "_empty": true },
            "items": [
                {
                    "link": {
                        "entity_unique_id": "fhdjvjd",
                        "entity_slug": "my-detail-page",
                        "entity_type_id": 12,
                        "routes": { "detail": "/foo-bar", "_empty": false }
                    }
                },
                {
                    "link": {
                        "entity_unique_id": "ksjdfhs",
                        "entity_slug": "another-page",
                        "entity_type_id": false,
                        "routes": { "_empty": true }
                    }
                }
            ],
            "slots": {
                "main": {
                    "identifier": "main",
                    "content": [
                        { "identifier": "nested", "uid": "c1", "component": "Nested" }
                    ]
                }
            }
        }
        JSON;

        /** @var \stdClass $decoded */
        $decoded = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
