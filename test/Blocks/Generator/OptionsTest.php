<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\ExitCode;
use Flyo\Blocks\Generator\GeneratorException;
use Flyo\Blocks\Generator\Options;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The parser exists because getopt() fails silently in ways that produce wrong output rather than
 * an error. Every one of those failure modes has a test here.
 */
class OptionsTest extends TestCase
{
    public function testPositionalArguments(): void
    {
        $options = self::parse(['schemas.json', 'App\Blocks', 'src/Blocks']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Blocks', $options->namespace);
        $this->assertSame('src/Blocks', $options->target);
    }

    public function testShortOptions(): void
    {
        $options = self::parse(['-u', 'schemas.json', '-n', 'App\Blocks', '-t', 'src/Blocks']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Blocks', $options->namespace);
        $this->assertSame('src/Blocks', $options->target);
    }

    public function testLongOptionsWithInlineValues(): void
    {
        $options = self::parse(['--source=schemas.json', '--namespace=App\Blocks', '--target=src/Blocks']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Blocks', $options->namespace);
    }

    public function testLongOptionAliases(): void
    {
        $options = self::parse(['--url=schemas.json', '--ns=App\Blocks', '--dir=src/Blocks']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Blocks', $options->namespace);
        $this->assertSame('src/Blocks', $options->target);
    }

    /**
     * getopt() stops parsing options at the first non-option argument, so `<source> -t dir` would
     * silently lose the -t. This parser permutes.
     */
    public function testFlagsAfterPositionalArgumentsAreStillParsed(): void
    {
        $options = self::parse(['schemas.json', 'App\Blocks', '-t', 'src/Blocks', '--dry-run']);

        $this->assertSame('src/Blocks', $options->target);
        $this->assertTrue($options->dryRun);
    }

    /**
     * The headline getopt() trap: `-ns 'App\Blocks'` parses as -n with the value "s", generating
     * everything into a namespace called `s`.
     */
    public function testClusteredShortOptionWithAValueIsRejected(): void
    {
        $e = self::parseError(['-ns', 'App\Blocks', 'schemas.json']);

        $this->assertSame(ExitCode::USAGE, $e->exitCode());
        $this->assertStringContainsString('must stand alone', $e->getMessage());
        $this->assertStringContainsString('with the value "s"', implode("\n", $e->hints()));
    }

    public function testMissingValueIsAnError(): void
    {
        $e = self::parseError(['-u']);

        $this->assertSame(ExitCode::USAGE, $e->exitCode());
        $this->assertStringContainsString('-u requires a value', $e->getMessage());
    }

    public function testAnOptionCannotSwallowTheNextOptionAsItsValue(): void
    {
        $e = self::parseError(['-u', '-n', 'App\Blocks']);

        $this->assertStringContainsString('requires a value', $e->getMessage());
    }

    public function testUnknownLongOptionSuggestsTheClosestMatch(): void
    {
        $e = self::parseError(['--namespac', 'x', 'a', 'b', 'c']);

        $this->assertStringContainsString('unknown option "--namespac"', $e->getMessage());
        $this->assertStringContainsString('did you mean --namespace?', implode("\n", $e->hints()));
    }

    public function testUnknownShortOptionIsAnError(): void
    {
        $e = self::parseError(['-z', 'a', 'b', 'c']);

        $this->assertStringContainsString('unknown option "-z"', $e->getMessage());
    }

    public function testGivingTheSameValueTwiceIsAnError(): void
    {
        $e = self::parseError(['-u', 'a.json', '-u', 'b.json', 'App\Blocks', 'src']);

        $this->assertStringContainsString('given more than once', $e->getMessage());
    }

    public function testMixingAFlagAndAPositionalForTheSameValueIsAnError(): void
    {
        $e = self::parseError(['a.json', 'App\Blocks', 'src', '-u', 'b.json']);

        $this->assertStringContainsString('<source> was given twice', $e->getMessage());
    }

    public function testTooManyOperandsIsAnError(): void
    {
        $e = self::parseError(['a.json', 'App\Blocks', 'src', 'extra']);

        $this->assertStringContainsString('unexpected argument "extra"', $e->getMessage());
    }

    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function missingArguments(): array
    {
        return [
            'nothing' => [[], '<source>'],
            'source only' => [['a.json'], '<namespace>'],
            'no target' => [['a.json', 'App\Blocks'], '<target>'],
        ];
    }

    /**
     * @param list<string> $args
     */
    #[DataProvider('missingArguments')]
    public function testMissingArgument(array $args, string $expected): void
    {
        $e = self::parseError($args);

        $this->assertStringContainsString('missing ' . $expected, $e->getMessage());
    }

    public function testFlagsWithValuesAreRejected(): void
    {
        $e = self::parseError(['--dry-run=yes', 'a.json', 'App\Blocks', 'src']);

        $this->assertStringContainsString('is a flag and takes no value', $e->getMessage());
    }

    public function testDoubleDashEndsOptionParsing(): void
    {
        $options = self::parse(['--', '-weird-file.json', 'App\Blocks', 'src']);

        $this->assertSame('-weird-file.json', $options->source);
    }

    public function testASingleDashMeansStdin(): void
    {
        $this->assertSame('-', self::parse(['-', 'App\Blocks', 'src'])->source);
    }

    public function testForwardSlashesAreAcceptedAsNamespaceSeparators(): void
    {
        $this->assertSame('App\Blocks', self::parse(['a.json', 'App/Blocks', 'src'])->namespace);
    }

    public function testLeadingAndTrailingSeparatorsAreTrimmed(): void
    {
        $this->assertSame('App\Blocks', self::parse(['a.json', '\App\Blocks\\', 'src'])->namespace);
    }

    /**
     * "App\Blocks" unquoted in bash arrives as "AppBlocks", which is a valid namespace and so
     * cannot be rejected -- but it is almost never what was meant.
     */
    public function testWarnsWhenTheNamespaceLooksLikeTheShellAteTheBackslashes(): void
    {
        $options = self::parse(['a.json', 'AppBlocks', 'src']);

        $this->assertSame('AppBlocks', $options->namespace);
        $this->assertStringContainsString('contains no "\\" separator', implode("\n", $options->warnings));
    }

    public function testDoesNotWarnAboutASingleSegmentNamespace(): void
    {
        $this->assertSame([], self::parse(['a.json', 'Blocks', 'src'])->warnings);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidNamespaces(): array
    {
        return [
            'digit first' => ['2App\Blocks'],
            'hyphen' => ['App-Blocks'],
            'space' => ['App Blocks'],
            'empty segment' => ['App\\\\Blocks'],
            'punctuation' => ['App\Blocks!'],
        ];
    }

    #[DataProvider('invalidNamespaces')]
    public function testInvalidNamespaceIsRejected(string $namespace): void
    {
        $e = self::parseError(['a.json', $namespace, 'src']);

        $this->assertStringContainsString('is not a valid PHP namespace', $e->getMessage());
    }

    public function testWarnsAboutAnAbsoluteTargetAtTheFilesystemRoot(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', '/src']);

        $this->assertStringContainsString('directly under "/"', implode("\n", $options->warnings));
    }

    public function testTokenComesFromTheEnvironmentWhenNotGiven(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', 'src'], ['FLYO_TOKEN' => 'from-env']);

        $this->assertSame('from-env', $options->token);
    }

    public function testFlyoApiKeyIsAcceptedAsAFallback(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', 'src'], ['FLYO_API_KEY' => 'other']);

        $this->assertSame('other', $options->token);
    }

    public function testAnExplicitTokenBeatsTheEnvironment(): void
    {
        $options = self::parse(
            ['a.json', 'App\Blocks', 'src', '--token=explicit'],
            ['FLYO_TOKEN' => 'from-env']
        );

        $this->assertSame('explicit', $options->token);
    }

    public function testCheckImpliesDryRun(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', 'src', '--check']);

        $this->assertTrue($options->check);
        $this->assertTrue($options->dryRun, '--check must never write');
    }

    public function testCleanIsOnByDefaultAndDisabledByNoClean(): void
    {
        $this->assertTrue(self::parse(['a.json', 'App\Blocks', 'src'])->clean);
        $this->assertFalse(self::parse(['a.json', 'App\Blocks', 'src', '--no-clean'])->clean);
    }

    public function testQuietBeatsVerbose(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', 'src', '-v', '-q']);

        $this->assertSame(Options::VERBOSITY_QUIET, $options->verbosity);
    }

    public function testClusteredFlagsAreAllowed(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', 'src', '-dv']);

        $this->assertTrue($options->dryRun);
        $this->assertSame(Options::VERBOSITY_VERBOSE, $options->verbosity);
    }

    public function testHelpShortCircuitsBeforeArgumentsAreRequired(): void
    {
        $this->assertTrue(self::parse(['--help'])->help);
        $this->assertTrue(self::parse(['-h'])->help);
    }

    public function testVersionShortCircuitsBeforeArgumentsAreRequired(): void
    {
        $this->assertTrue(self::parse(['--version'])->version);
        $this->assertTrue(self::parse(['-V'])->version);
    }

    public function testHelpTextMentionsEveryOption(): void
    {
        $help = Options::help();

        foreach (['--source', '--namespace', '--target', '--token', '--check', '--dry-run',
            '--no-clean', '--allow-empty', '--quiet', '--verbose', '--help', '--version'] as $option) {
            $this->assertStringContainsString($option, $help);
        }
    }

    /**
     * @param list<string> $args
     * @param array<string, string> $env
     */
    private static function parse(array $args, array $env = []): Options
    {
        return Options::parse(['flyo-generate-blocks', ...$args], $env);
    }

    /**
     * @param list<string> $args
     */
    private static function parseError(array $args): GeneratorException
    {
        try {
            self::parse($args);
        } catch (GeneratorException $e) {
            return $e;
        }

        self::fail('expected a usage error for: ' . implode(' ', $args));
    }
}
