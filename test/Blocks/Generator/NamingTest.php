<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\Naming;
use Flyo\Blocks\Generator\Warnings;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NamingTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function studlyCases(): array
    {
        return [
            // Inner case must survive, or every already-studly component would be mangled.
            'already studly' => ['BlockHero', 'BlockHero'],
            'snake case' => ['hero_banner', 'HeroBanner'],
            'kebab case' => ['hero-banner', 'HeroBanner'],
            'spaced' => ['Hero Banner', 'HeroBanner'],
            'mixed separators' => ['a-b_c 1', 'ABC1'],
            'trailing digits' => ['cta-1', 'Cta1'],
            // A PHP identifier cannot start with a digit.
            'leading digit' => ['2col', 'N2col'],
            'only digits' => ['11', 'N11'],
            'lowercase' => ['hero', 'Hero'],
            'accented' => ['Héro', 'Héro'],
            'umlaut' => ['grüezi_wohl', 'GrüeziWohl'],
            'empty' => ['', 'Unnamed'],
            'separators only' => ['__', 'Unnamed'],
            'punctuation only' => ['---', 'Unnamed'],
            'screaming' => ['ITEMS', 'ITEMS'],
        ];
    }

    #[DataProvider('studlyCases')]
    public function testStudly(string $raw, string $expected): void
    {
        $this->assertSame($expected, Naming::studly($raw));
    }

    public function testClassNameComesFromTheComponent(): void
    {
        $names = Naming::classNames(
            ['BlockHero' => self::schema('hero', 'Hero')],
            new Warnings()
        );

        $this->assertSame(['BlockHero' => 'BlockHero'], $names);
    }

    /**
     * The server keys the schema `'Block' . $component`, and does not validate the component as a
     * PHP-safe string, so an empty one yields a schema keyed exactly "Block".
     */
    public function testFallsBackToTheIdentifierWhenTheComponentIsEmpty(): void
    {
        $names = Naming::classNames(
            ['Block' => self::schema('nameless', '')],
            new Warnings()
        );

        $this->assertSame(['Block' => 'BlockNameless'], $names);
    }

    /**
     * The server keys block schemas `'Block' . $component`, so the key usually already carries the
     * prefix and must not get a second one.
     */
    public function testFallsBackToTheSchemaKeyWhenBothAreEmpty(): void
    {
        $names = Naming::classNames(
            ['BlockMystery' => ['properties' => []]],
            new Warnings()
        );

        $this->assertSame(['BlockMystery' => 'BlockMystery'], $names);
    }

    public function testTheSchemaKeyFallbackStillAddsThePrefixWhenTheKeyLacksIt(): void
    {
        $names = Naming::classNames(
            ['mystery_thing' => ['properties' => []]],
            new Warnings()
        );

        $this->assertSame(['mystery_thing' => 'BlockMysteryThing'], $names);
    }

    public function testCollisionsGetNumberedInSchemaKeyOrder(): void
    {
        $warnings = new Warnings();

        $names = Naming::classNames([
            'BlockHero Banner' => self::schema('spaced', 'Hero Banner'),
            'BlockHeroBanner' => self::schema('studly', 'HeroBanner'),
        ], $warnings);

        $this->assertSame([
            'BlockHero Banner' => 'BlockHeroBanner',
            'BlockHeroBanner' => 'BlockHeroBanner2',
        ], $names);

        $this->assertStringContainsString('want the class name BlockHeroBanner', implode('', $warnings->all()));
    }

    /**
     * The assignment must not depend on the order the schemas arrive in, or the generated file
     * names would flip whenever an editor reorders blocks.
     */
    public function testCollisionAssignmentIsIndependentOfInputOrder(): void
    {
        $a = ['BlockHero Banner' => self::schema('spaced', 'Hero Banner')];
        $b = ['BlockHeroBanner' => self::schema('studly', 'HeroBanner')];

        $forward = Naming::classNames([...$a, ...$b], new Warnings());
        $reverse = Naming::classNames([...$b, ...$a], new Warnings());

        ksort($forward);
        ksort($reverse);

        $this->assertSame($forward, $reverse);
    }

    /**
     * A numbered suffix must not land on a name another block already owns.
     */
    public function testSuffixSkipsANameThatIsAlreadyTaken(): void
    {
        $names = Naming::classNames([
            'BlockHero Banner' => self::schema('spaced', 'Hero Banner'),
            'BlockHeroBanner' => self::schema('studly', 'HeroBanner'),
            'BlockHeroBanner2' => self::schema('numbered', 'HeroBanner2'),
        ], new Warnings());

        $this->assertSame(count($names), count(array_unique($names)));
        $this->assertContains('BlockHeroBanner', $names);
        $this->assertContains('BlockHeroBanner2', $names);
        $this->assertContains('BlockHeroBanner3', $names);
    }

    public function testNoWarningWhenThereIsNoCollision(): void
    {
        $warnings = new Warnings();

        Naming::classNames([
            'BlockHero' => self::schema('hero', 'Hero'),
            'BlockTeaser' => self::schema('teaser', 'Teaser'),
        ], $warnings);

        $this->assertTrue($warnings->isEmpty());
    }

    public function testEnumValueCastsANumericIdentifierToString(): void
    {
        $this->assertSame('11', Naming::enumValue(self::schema(11, 'TwoCol'), 'identifier'));
    }

    public function testEnumValueIsEmptyWhenAbsent(): void
    {
        $this->assertSame('', Naming::enumValue(['properties' => []], 'identifier'));
        $this->assertSame('', Naming::enumValue([], 'component'));
        $this->assertSame('', Naming::enumValue(['properties' => ['identifier' => []]], 'identifier'));
    }

    /**
     * @return array<string, mixed>
     */
    private static function schema(string|int $identifier, string $component): array
    {
        return [
            'type' => 'object',
            'x-schema-type' => 'block',
            'properties' => [
                'identifier' => ['type' => 'string', 'enum' => [$identifier]],
                'component' => ['type' => 'string', 'enum' => [$component]],
            ],
        ];
    }
}
