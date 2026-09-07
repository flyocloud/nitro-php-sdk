<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\Document;
use Flyo\Blocks\Generator\TypeMapper;
use Flyo\Blocks\Generator\Warnings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The type mapping table, executable. One case per input shape the server can emit.
 */
class TypeMapperTest extends TestCase
{
    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function types(): array
    {
        return [
            'string' => [['type' => 'string'], 'string'],
            'integer' => [['type' => 'integer'], 'int'],
            'bounded integer' => [['type' => 'integer', 'minimum' => 1, 'maximum' => 7], 'int<1, 7>'],
            // The server uses `number` for both integral and fractional values.
            'number' => [['type' => 'number'], 'int|float'],
            'boolean' => [['type' => 'boolean'], 'bool'],
            'string with format is still a string' => [
                ['type' => 'string', 'format' => 'date-time'],
                'string',
            ],
            'single value enum' => [['type' => 'string', 'enum' => ['only']], "'only'"],
            'multi value enum' => [
                ['type' => 'string', 'enum' => ['wide', 'narrow']],
                "'wide'|'narrow'",
            ],
            'enum without a type' => [['enum' => ['a', 'b']], "'a'|'b'"],
            'enum values are deduplicated' => [
                ['type' => 'string', 'enum' => ['a', 'a', 'b']],
                "'a'|'b'",
            ],
            'enum with a quote is escaped' => [
                ['type' => 'string', 'enum' => ["it's"]],
                "'it\\'s'",
            ],
            // A block id may be numeric even though the property is typed string.
            'numeric enum member' => [['type' => 'string', 'enum' => [11]], "'11'"],
            'enum with a newline falls back to string' => [
                ['type' => 'string', 'enum' => ["a\nb"]],
                'string',
            ],
            'object with properties' => [
                ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]],
                'object{a: string|null}',
            ],
            'nested objects' => [
                [
                    'type' => 'object',
                    'properties' => [
                        'a' => ['type' => 'object', 'properties' => ['b' => ['type' => 'string']]],
                    ],
                ],
                'object{a: object{b: string|null}|null}',
            ],
            // additionalProperties with no properties is a deliberate open map.
            'open map' => [['type' => 'object', 'additionalProperties' => true], '\stdClass'],
            'typed additional properties' => [
                ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
                '\stdClass',
            ],
            'bare object' => [['type' => 'object'], '\stdClass'],
            'object with an empty properties map' => [
                ['type' => 'object', 'properties' => []],
                '\stdClass',
            ],
            'list of objects' => [
                [
                    'type' => 'array',
                    'items' => ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]],
                ],
                'array<int, object{a: string|null}>',
            ],
            'list of scalars' => [
                ['type' => 'array', 'items' => ['type' => 'string']],
                'array<int, string>',
            ],
            'list of open maps' => [
                ['type' => 'array', 'items' => ['type' => 'object', 'additionalProperties' => true]],
                'array<int, \stdClass>',
            ],
            'list without items' => [['type' => 'array'], 'array<int, mixed>'],
            'list with empty items' => [['type' => 'array', 'items' => []], 'array<int, mixed>'],
            'list of generic blocks' => [
                ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/block']],
                'array<int, \Flyo\Model\Block>',
            ],
            'oneOf int or bool' => [
                ['oneOf' => [['type' => 'integer'], ['type' => 'boolean']]],
                'bool|int',
            ],
            'oneOf string or bool' => [
                ['oneOf' => [['type' => 'string'], ['type' => 'boolean']]],
                'bool|string',
            ],
            'anyOf deduplicates atoms' => [
                ['anyOf' => [['type' => 'string'], ['type' => 'integer'], ['type' => 'number']]],
                'float|int|string',
            ],
            'union members are ordered deterministically' => [
                ['anyOf' => [['type' => 'string'], ['type' => 'boolean'], ['type' => 'integer']]],
                'bool|int|string',
            ],
            'mergeable allOf' => [
                [
                    'allOf' => [
                        ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]],
                        ['type' => 'object', 'properties' => ['b' => ['type' => 'integer']]],
                    ],
                ],
                'object{a: string|null, b: int|null}',
            ],
            'unmergeable allOf' => [
                [
                    'allOf' => [
                        ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]],
                        ['type' => 'string'],
                    ],
                ],
                'mixed',
            ],
            'ref to the generic block' => [
                ['$ref' => '#/components/schemas/block'],
                '\Flyo\Model\Block',
            ],
            'ref to another typed block' => [
                ['$ref' => '#/components/schemas/BlockOther'],
                '\App\Blocks\BlockOther',
            ],
            'ref to an unmarked schema is inlined' => [
                ['$ref' => '#/components/schemas/Plain'],
                'object{name: string|null}',
            ],
            'dangling ref' => [['$ref' => '#/components/schemas/Nope'], 'mixed'],
            'external ref' => [['$ref' => 'https://example.com/x.json#/Foo'], 'mixed'],
            'no type at all' => [['title' => 'Mapped column', 'description' => 'anything'], 'mixed'],
            'empty schema' => [[], 'mixed'],
            // OpenAPI 3.1 style, handled defensively.
            'type as a list' => [['type' => ['string', 'null']], 'string|null'],
            'unknown type' => [['type' => 'weird'], 'mixed'],
        ];
    }

    /**
     * @param array<string, mixed> $schema
     */
    #[DataProvider('types')]
    public function testMapsSchemaToType(array $schema, string $expected): void
    {
        $this->assertSame($expected, self::mapper()->type($schema, ['field']));
    }

    /**
     * Nullability comes from the enclosing schema's `required` list, so a property absent from it
     * is nullable. Today the server emits no `required` at all, which makes everything nullable.
     */
    public function testPropertyIsNullableUnlessRequired(): void
    {
        $mapper = self::mapper();

        $this->assertSame('string', $mapper->propertyType(['type' => 'string'], ['a'], true));
        $this->assertSame('string|null', $mapper->propertyType(['type' => 'string'], ['a'], false));
    }

    public function testExplicitlyNullableIsNullableEvenWhenRequired(): void
    {
        $this->assertSame(
            'string|null',
            self::mapper()->propertyType(['type' => 'string', 'nullable' => true], ['a'], true)
        );
    }

    public function testMixedIsNotSuffixedWithNull(): void
    {
        $this->assertSame('mixed', self::mapper()->propertyType([], ['a'], false));
    }

    public function testNullIsNotAppendedTwice(): void
    {
        $this->assertSame(
            'string|null',
            self::mapper()->propertyType(['type' => ['string', 'null']], ['a'], false)
        );
    }

    public function testRequiredIsHonouredInsideAShape(): void
    {
        $type = self::mapper()->type([
            'type' => 'object',
            'required' => ['here'],
            'properties' => [
                'here' => ['type' => 'string'],
                'gone' => ['type' => 'string'],
            ],
        ], ['field']);

        $this->assertSame('object{here: string, gone: string|null}', $type);
    }

    public function testKeysThatAreNotPhpIdentifiersAreQuoted(): void
    {
        $type = self::mapper()->type([
            'type' => 'object',
            'properties' => [
                'ok_key' => ['type' => 'string'],
                'my-field' => ['type' => 'string'],
                'length (cm)' => ['type' => 'number'],
                '_empty' => ['type' => 'boolean'],
            ],
        ], ['field']);

        $this->assertSame(
            "object{\n    ok_key: string|null,\n    'my-field': string|null,"
                . "\n    'length (cm)': int|float|null,\n    _empty: bool|null\n}",
            $type
        );
    }

    public function testLargeShapesAreRenderedMultiline(): void
    {
        $properties = [];
        foreach (['alpha', 'bravo', 'charlie', 'delta', 'echo'] as $name) {
            $properties[$name] = ['type' => 'string'];
        }

        $type = self::mapper()->type(['type' => 'object', 'properties' => $properties], ['field']);

        $this->assertStringContainsString("object{\n", $type);
        $this->assertStringEndsWith("\n}", $type);
    }

    public function testRecursiveRefIsCutOffRatherThanLoopingForever(): void
    {
        $document = new Document([
            'components' => [
                'schemas' => [
                    'Loop' => [
                        'type' => 'object',
                        'properties' => ['self' => ['$ref' => '#/components/schemas/Loop']],
                    ],
                ],
            ],
        ]);

        $warnings = new Warnings();
        $type = (new TypeMapper($document, [], $warnings))
            ->type(['$ref' => '#/components/schemas/Loop'], ['field']);

        $this->assertStringContainsString('mixed', $type);
        $this->assertStringContainsString('cycle', implode("\n", $warnings->all()));
    }

    public function testDeepNestingIsCappedWithAWarning(): void
    {
        // One object nested MAX_DEPTH + 5 times.
        $schema = ['type' => 'string'];
        for ($i = 0; $i < TypeMapper::MAX_DEPTH + 5; $i++) {
            $schema = ['type' => 'object', 'properties' => ['down' => $schema]];
        }

        $warnings = new Warnings();
        $type = self::mapper($warnings)->type($schema, ['field']);

        $this->assertStringContainsString('mixed', $type);
        $this->assertStringContainsString('nested deeper than', implode("\n", $warnings->all()));
    }

    public function testWarnsAboutADanglingReference(): void
    {
        $warnings = new Warnings();
        self::mapper($warnings)->type(['$ref' => '#/components/schemas/Nope'], ['field']);

        $this->assertStringContainsString(
            'points at a schema that is not in the document',
            implode("\n", $warnings->all())
        );
    }

    private static function mapper(?Warnings $warnings = null): TypeMapper
    {
        $document = new Document([
            'components' => [
                'schemas' => [
                    'block' => ['type' => 'object', 'properties' => ['uid' => ['type' => 'string']]],
                    'BlockOther' => [
                        'type' => 'object',
                        'x-schema-type' => 'block',
                        'properties' => ['identifier' => ['enum' => ['other'], 'type' => 'string']],
                    ],
                    'Plain' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
                ],
            ],
        ]);

        return new TypeMapper($document, ['BlockOther' => 'App\Blocks\BlockOther'], $warnings ?? new Warnings());
    }
}
