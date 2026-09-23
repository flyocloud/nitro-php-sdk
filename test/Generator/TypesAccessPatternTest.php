<?php

namespace Flyo\Test\Generator;

use Flyo\Model\ConfigResponse;
use Flyo\Model\ConfigResponseContainersValue;
use Flyo\Model\ContainerPage;
use Flyo\Model\Entity;
use Flyo\Model\EntityInterface;
use Flyo\ObjectSerializer;
use PHPUnit\Framework\TestCase;

/**
 * Proves the generated entity and container annotations describe what actually happens at
 * runtime, the way {@see AccessPatternTest} does for blocks.
 *
 * Payloads are decoded exactly the way the Api classes decode a response, and every access form
 * the generated types advertise is walked against them.
 */
class TypesAccessPatternTest extends TestCase
{
    /** @var class-string<Entity> */
    private const ENTITY = 'Fixture\Site\Entities\EntityArticle';

    /** @var class-string<ConfigResponseContainersValue> */
    private const CONTAINER = 'Fixture\Site\Containers\ContainerMain';

    /** @var class-string<ContainerPage> */
    private const ITEM = 'Fixture\Site\Containers\ContainerItem';

    public static function setUpBeforeClass(): void
    {
        // The golden files are excluded from the autoloader (they are fixtures, not library code),
        // so load the ones we exercise by hand.
        $expected = dirname(__DIR__) . '/fixtures/types/site/expected';

        require_once $expected . '/Entities/EntityArticle.php';
        require_once $expected . '/Containers/ContainerItem.php';
        require_once $expected . '/Containers/ContainerMain.php';
    }

    // -- entities ------------------------------------------------------------------------------

    /**
     * `model` is typed `object` on \Flyo\Model\Entity, which ObjectSerializer passes through
     * untouched: a plain \stdClass all the way down, exactly what `object{...}` describes.
     */
    public function testTheModelIsAPlainObjectAllTheWayDown(): void
    {
        $model = self::entity()->getModel();

        $this->assertInstanceOf(\stdClass::class, $model);
        $this->assertSame('Hello world', $model->headline);
        $this->assertSame(1700000000, $model->published_at);
        $this->assertInstanceOf(\stdClass::class, $model->image);
        $this->assertSame(154311, $model->image->id);
        $this->assertSame('news', $model->category->value);
        $this->assertSame('first-topic', $model->tags->topics[0]->slug);
        $this->assertSame('evt1', $model->events[0]->unique_id);
    }

    public function testTheRestOfTheEntityKeepsTheSdkModels(): void
    {
        $entity = self::entity();

        $this->assertInstanceOf(EntityInterface::class, $entity->getEntity());
        $this->assertSame('hello-world', $entity->getEntity()->getEntitySlug());
        $this->assertSame('de', $entity->getLanguage());
    }

    public function testTheGeneratedEntityIsAnEntityAndSerializesIdentically(): void
    {
        $payload = self::entityPayload();

        $generic = ObjectSerializer::deserialize($payload, Entity::class, []);
        $typed = ObjectSerializer::deserialize($payload, self::ENTITY, []);

        $this->assertInstanceOf(Entity::class, $typed);
        $this->assertEquals($generic->getModel(), $typed->getModel());
        $this->assertSame(json_encode($generic), json_encode($typed));
    }

    // -- containers ----------------------------------------------------------------------------

    /**
     * Documents why the container classes are annotations, not return types: the config
     * endpoint hydrates every container into the generic model, whatever its identifier.
     */
    public function testContainersArriveAsTheGenericModelUnderTheirIdentifier(): void
    {
        $containers = self::config()->getContainers();

        $this->assertIsArray($containers);
        $this->assertArrayHasKey(constant(self::CONTAINER . '::IDENTIFIER'), $containers);
        $this->assertInstanceOf(ConfigResponseContainersValue::class, $containers['main']);
        $this->assertNotInstanceOf(self::CONTAINER, $containers['main']);
    }

    public function testItemsAndTheirChildrenAreSdkModels(): void
    {
        $items = self::main()->getItems();

        $this->assertIsArray($items);
        $this->assertInstanceOf(ContainerPage::class, $items[0]);
        $this->assertSame('Home', $items[0]->getLabel());

        $children = $items[0]->getChildren();

        $this->assertIsArray($children);
        $this->assertInstanceOf(ContainerPage::class, $children[0]);
        $this->assertSame([], $children[0]->getChildren());
    }

    /**
     * `properties` is typed `array<string,mixed>`, which ObjectSerializer builds with
     * settype($data, 'array'): an array at the top, decoded JSON below it. That is why this one
     * shape is `array{...}` while everything under it is `object{...}`.
     */
    public function testPagePropertiesAreAnArrayOfDecodedValues(): void
    {
        $properties = self::main()->getItems()[0]->getProperties();

        $this->assertIsArray($properties);
        $this->assertSame('house', $properties['icon']);
        $this->assertTrue($properties['highlight']);
        $this->assertInstanceOf(\stdClass::class, $properties['teaser-image']);
        $this->assertSame('https://storage.flyo.cloud/home.jpg', $properties['teaser-image']->source);
    }

    /**
     * "A property a page never filled in is null": the key is still there, which is why the
     * shape has no optional keys, only nullable values.
     */
    public function testAnUnfilledPagePropertyIsPresentAndNull(): void
    {
        $properties = self::main()->getItems()[0]->getChildren()[0]->getProperties();

        $this->assertIsArray($properties);
        $this->assertArrayHasKey('icon', $properties);
        $this->assertNull($properties['icon']);
        $this->assertNull($properties['teaser-image']);
    }

    public function testTheGeneratedClassesAreTheSdkModelsAndSerializeIdentically(): void
    {
        $payload = self::configPayload()->containers->main;

        $generic = ObjectSerializer::deserialize($payload, ConfigResponseContainersValue::class, []);
        $typed = ObjectSerializer::deserialize($payload, self::CONTAINER, []);

        $this->assertInstanceOf(ConfigResponseContainersValue::class, $typed);
        $this->assertSame(json_encode($generic), json_encode($typed));

        $item = $payload->items[0];
        $genericItem = ObjectSerializer::deserialize($item, ContainerPage::class, []);
        $typedItem = ObjectSerializer::deserialize($item, self::ITEM, []);

        $this->assertInstanceOf(ContainerPage::class, $typedItem);
        $this->assertEquals($genericItem->getProperties(), $typedItem->getProperties());
        $this->assertEquals($genericItem->getChildren(), $typedItem->getChildren());
        $this->assertSame(json_encode($genericItem), json_encode($typedItem));
    }

    /**
     * The deepest chains the generated types promise, with no diagnostics of any kind.
     */
    public function testTheWholeAdvertisedChainEmitsNoDiagnostics(): void
    {
        $seen = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$seen): bool {
            $seen[] = $errno . ': ' . $errstr;

            return true;
        });

        try {
            self::entity()->getModel()->tags->topics[0]->slug;
            self::entity()->getModel()->image->source;
            self::main()->getItems()[0]->getProperties()['teaser-image']->source;
            self::main()->getItems()[0]->getChildren()[0]->getProperties()['highlight'];
            self::main()->getItems()[0]->getChildren()[0]->getHref();
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $seen);
    }

    // -- helpers -------------------------------------------------------------------------------

    private static function entity(): Entity
    {
        return ObjectSerializer::deserialize(self::entityPayload(), self::ENTITY, []);
    }

    private static function config(): ConfigResponse
    {
        return ObjectSerializer::deserialize(self::configPayload(), ConfigResponse::class, []);
    }

    private static function main(): ConfigResponseContainersValue
    {
        return (self::config()->getContainers() ?? [])['main'];
    }

    private static function entityPayload(): \stdClass
    {
        return self::decode(<<<'JSON'
        {
            "entity": { "entity_unique_id": "abc123", "entity_slug": "hello-world", "entity_type_id": 1 },
            "model": {
                "headline": "Hello world",
                "published_at": 1700000000,
                "image": { "source": "https://storage.flyo.cloud/hello.jpg", "caption": "Jane", "id": 154311 },
                "content": { "html": "<p>Hi</p>", "json": { "type": "doc" } },
                "category": { "value": "news", "options": { "news": "News", "report": "Report" }, "label": "News" },
                "tags": { "topics": [{ "title": "First topic", "slug": "first-topic" }] },
                "events": [{ "id": 7, "unique_id": "evt1" }]
            },
            "language": "de",
            "is_draft": false
        }
        JSON);
    }

    private static function configPayload(): \stdClass
    {
        return self::decode(<<<'JSON'
        {
            "containers": {
                "main": {
                    "uid": "c1",
                    "identifier": "main",
                    "label": "Main navigation",
                    "items": [
                        {
                            "type": "page",
                            "target": "_self",
                            "label": "Home",
                            "href": "/",
                            "slug": "home",
                            "properties": {
                                "icon": "house",
                                "highlight": true,
                                "teaser-image": { "source": "https://storage.flyo.cloud/home.jpg", "caption": "Home" }
                            },
                            "children": [
                                {
                                    "type": "url",
                                    "target": "_blank",
                                    "label": "Elsewhere",
                                    "href": "https://example.com",
                                    "slug": "elsewhere",
                                    "properties": { "icon": null, "highlight": null, "teaser-image": null },
                                    "children": []
                                }
                            ]
                        }
                    ]
                }
            }
        }
        JSON);
    }

    /**
     * Decoded the way the Api classes do it: json_decode with assoc = false.
     */
    private static function decode(string $json): \stdClass
    {
        /** @var \stdClass $decoded */
        $decoded = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
