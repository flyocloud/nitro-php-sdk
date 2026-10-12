<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\ConstantDef;
use Flyo\Generator\EnumConstants;
use Flyo\Generator\Warnings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnumConstantsTest extends TestCase
{
    public function testAFieldWithAnEnumGetsOneConstantPerOption(): void
    {
        $this->assertSame(
            ['CONTENT_ALIGN_LEFT' => 'left', 'CONTENT_ALIGN_RIGHT' => 'right'],
            self::constants(['content' => self::object([
                'align' => ['type' => 'string', 'enum' => ['left', 'right']],
            ])])
        );
    }

    /**
     * A select field is an object whose `value` holds the option, so the options come from there
     * and the name leaves `value` out. `null` means nothing is selected; it is not an option.
     */
    public function testASelectFieldIsNamedAfterTheFieldNotItsValue(): void
    {
        $this->assertSame(
            [
                'CONTENT_SIZE_SMALL' => 'small',
                'CONTENT_SIZE_MEDIUM' => 'medium',
                'CONTENT_SIZE_LARGE' => 'large',
            ],
            self::constants(['content' => self::object([
                'size' => self::select(['small', 'medium', 'large', null]),
            ])])
        );
    }

    public function testContentAndConfigGetAPrefixEach(): void
    {
        $this->assertSame(
            [
                'CONTENT_SIZE_SMALL' => 'small',
                'CONTENT_SIZE_LARGE' => 'large',
                'CONFIG_SIZE_SMALL' => 'small',
            ],
            self::constants([
                'content' => self::object(['size' => self::select(['small', 'large', null])]),
                'config' => self::object(['size' => self::select(['small', null])]),
            ])
        );
    }

    public function testNamesAreUpperSnakeCaseAndValuesStayVerbatim(): void
    {
        $this->assertSame(
            [
                'CONTENT_BACKGROUND_COLOR_DARK_BLUE' => 'darkBlue',
                'CONTENT_BACKGROUND_COLOR_LIGHT_GRAY' => 'light-gray',
                'CONTENT_BACKGROUND_COLOR_IT_S_RED' => "it's red",
                'CONTENT_COLUMNS_2' => '2',
            ],
            self::constants(['content' => self::object([
                'background-color' => self::select(['darkBlue', 'light-gray', "it's red"]),
                'columns' => ['type' => 'string', 'enum' => ['2']],
            ])])
        );
    }

    /**
     * The value must be the literal the field is typed with, so a number listed in a string enum
     * becomes a string constant, the same way it becomes a string literal in the type.
     */
    public function testANumericMemberOfAStringEnumIsAString(): void
    {
        $constants = self::plan(['content' => self::object([
            'columns' => ['type' => 'string', 'enum' => [1, 2]],
        ])]);

        $this->assertSame(['1', '2'], array_map(static fn (ConstantDef $c): string|array => $c->value, $constants));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function fieldsWithoutOptions(): array
    {
        return [
            'plain string' => [['type' => 'string']],
            // Typed `int`, not as a union of literals.
            'integer enum' => [['type' => 'integer', 'enum' => [1, 2]]],
            // The whole enum falls back to `string`.
            'enum with an object member' => [['type' => 'string', 'enum' => ['a', ['nested' => true]]]],
            'enum with a newline' => [['type' => 'string', 'enum' => ["a\nb", 'c']]],
            'enum with a comment terminator' => [['type' => 'string', 'enum' => ['a */ b', 'c']]],
            'enum of only null' => [['type' => 'string', 'nullable' => true, 'enum' => [null]]],
            'empty enum' => [['type' => 'string', 'enum' => []]],
            // The type comes from the reference or the union, not from the enum next to it.
            'enum beside a $ref' => [['$ref' => '#/components/schemas/Other', 'enum' => ['a']]],
            'enum beside oneOf' => [['oneOf' => [['type' => 'string']], 'enum' => ['a']]],
            // An untyped node with properties is typed `mixed`, so its `value` is never looked at.
            'untyped select' => [['properties' => ['value' => ['type' => 'string', 'enum' => ['a']]]]],
            'select with an integer value' => [[
                'type' => 'object',
                'properties' => ['value' => ['type' => 'integer', 'enum' => [1, 2]]],
            ]],
            // Options are only looked for on the field and on a select's `value`, nowhere deeper.
            'enum nested deeper' => [[
                'type' => 'object',
                'properties' => ['inner' => ['type' => 'object', 'properties' => [
                    'value' => ['type' => 'string', 'enum' => ['a']],
                ]]],
            ]],
            'not a schema' => [['type' => 'object', 'properties' => ['value' => 'a']]],
        ];
    }

    /**
     * @param array<string, mixed> $field
     */
    #[DataProvider('fieldsWithoutOptions')]
    public function testAFieldWithoutAFixedSetOfOptionsGetsNoConstants(array $field): void
    {
        $warnings = new Warnings();

        $this->assertSame([], self::plan(['content' => self::object(['field' => $field])], $warnings));
        $this->assertTrue($warnings->isEmpty());
    }

    public function testAnObjectWithoutPropertiesGetsNoConstants(): void
    {
        $this->assertSame([], self::plan(['content' => ['type' => 'object'], 'config' => []]));
    }

    /**
     * Neither option gets the name: numbering one of them would silently hand the name to another
     * option once an editor adds one in Flyo.
     */
    public function testOptionsThatWantTheSameNameGetNone(): void
    {
        $warnings = new Warnings();

        $this->assertSame(
            ['CONTENT_SIZE_LARGE' => 'large'],
            self::constants(['content' => self::object([
                'size' => self::select(['Small', 'small', 'large', null]),
            ])], $warnings)
        );
        $this->assertSame(
            ['BlockCard: CONTENT_SIZE_SMALL would stand for more than one option (content.size "Small", '
                . 'content.size "small"), so it is not generated. Rename one of the options in Flyo to '
                . 'remove the ambiguity.'],
            $warnings->all()
        );
    }

    public function testFieldsWhoseOptionsWantTheSameNameGetNone(): void
    {
        $warnings = new Warnings();

        $this->assertSame(
            ['CONTENT_SIZE_LARGE' => 'large'],
            self::constants(['content' => self::object([
                'size' => self::select(['small_wide', 'large']),
                'size_small' => self::select(['wide']),
            ])], $warnings)
        );
        $this->assertStringContainsString(
            'CONTENT_SIZE_SMALL_WIDE would stand for more than one option (content.size "small_wide", '
                . 'content.size_small "wide")',
            implode("\n", $warnings->all())
        );
    }

    /**
     * Two spellings of one field name with the same option still mean the same string, so the one
     * constant is right for both.
     */
    public function testTheSameOptionUnderTheSameNameIsNoCollision(): void
    {
        $warnings = new Warnings();

        $this->assertSame(
            ['CONTENT_FONT_SIZE_SMALL' => 'small'],
            self::constants(['content' => self::object([
                'font-size' => ['type' => 'string', 'enum' => ['small']],
                'font_size' => ['type' => 'string', 'enum' => ['small']],
            ])], $warnings)
        );
        $this->assertTrue($warnings->isEmpty());
    }

    public function testAnOptionWithNothingToNameItAfterIsSkippedWithAWarning(): void
    {
        $warnings = new Warnings();

        $this->assertSame(
            ['CONTENT_SIZE_LARGE' => 'large'],
            self::constants(['content' => self::object(['size' => self::select(['', '--', 'large'])])], $warnings)
        );
        $this->assertSame(
            [
                'BlockCard.content.size: the option "" has no letter or digit to name a constant after, '
                    . 'so it gets none.',
                'BlockCard.content.size: the option "--" has no letter or digit to name a constant after, '
                    . 'so it gets none.',
            ],
            $warnings->all()
        );
    }

    public function testAFieldWithNothingToNameItAfterIsSkippedWithAWarning(): void
    {
        $warnings = new Warnings();

        $this->assertSame(
            [],
            self::constants(['content' => self::object(['--' => self::select(['a', 'b'])])], $warnings)
        );
        $this->assertSame(
            ['BlockCard.content.--: the field name has no letter or digit to name constants after, so its '
                . 'options get none.'],
            $warnings->all()
        );
    }

    /**
     * The options of one field are rendered as one block, so each carries the field as its group.
     */
    public function testConstantsAreGroupedByFieldInDocumentOrder(): void
    {
        $constants = self::plan([
            'content' => self::object([
                'size' => self::select(['small', 'large']),
                'align' => ['enum' => ['left']],
            ]),
            'config' => self::object(['size' => self::select(['small'])]),
        ]);

        $this->assertSame(
            [
                ['CONTENT_SIZE_SMALL', 'content.size'],
                ['CONTENT_SIZE_LARGE', 'content.size'],
                ['CONTENT_ALIGN_LEFT', 'content.align'],
                ['CONFIG_SIZE_SMALL', 'config.size'],
            ],
            array_map(static fn (ConstantDef $c): array => [$c->name, $c->group], $constants)
        );
    }

    /**
     * @param array<string, array<string, mixed>> $objects
     * @return list<ConstantDef>
     */
    private static function plan(array $objects, ?Warnings $warnings = null): array
    {
        return EnumConstants::plan('BlockCard', $objects, $warnings ?? new Warnings());
    }

    /**
     * @param array<string, array<string, mixed>> $objects
     * @return array<string, string|list<string>> name => value
     */
    private static function constants(array $objects, ?Warnings $warnings = null): array
    {
        $constants = [];
        foreach (self::plan($objects, $warnings) as $constant) {
            $constants[$constant->name] = $constant->value;
        }

        return $constants;
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    private static function object(array $fields): array
    {
        return ['type' => 'object', 'properties' => $fields];
    }

    /**
     * A select field, the way the server describes one.
     *
     * @param list<string|null> $options
     * @return array<string, mixed>
     */
    private static function select(array $options): array
    {
        return [
            'type' => 'object',
            'required' => ['value', 'options', 'label'],
            'properties' => [
                'value' => ['type' => 'string', 'nullable' => true, 'enum' => $options],
                'options' => [
                    'oneOf' => [
                        ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
                        ['type' => 'array', 'maxItems' => 0, 'items' => []],
                    ],
                ],
                'label' => ['type' => 'string', 'nullable' => true],
            ],
        ];
    }
}
