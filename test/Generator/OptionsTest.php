<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\ExitCode;
use Flyo\Generator\GeneratorException;
use Flyo\Generator\Options;
use Flyo\Generator\Profile;
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
        $options = self::parse(['schemas.json', 'App\Flyo', 'src/Flyo']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Flyo', $options->namespace);
        $this->assertSame('src/Flyo', $options->target);
    }

    public function testShortOptions(): void
    {
        $options = self::parse(['-u', 'schemas.json', '-n', 'App\Flyo', '-t', 'src/Flyo']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Flyo', $options->namespace);
        $this->assertSame('src/Flyo', $options->target);
    }

    public function testLongOptionsWithInlineValues(): void
    {
        $options = self::parse(['--source=schemas.json', '--namespace=App\Flyo', '--target=src/Flyo']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Flyo', $options->namespace);
    }

    public function testLongOptionAliases(): void
    {
        $options = self::parse(['--url=schemas.json', '--ns=App\Flyo', '--dir=src/Flyo']);

        $this->assertSame('schemas.json', $options->source);
        $this->assertSame('App\Flyo', $options->namespace);
        $this->assertSame('src/Flyo', $options->target);
    }

    /**
     * getopt() stops parsing options at the first non-option argument, so `<source> -t dir` would
     * silently lose the -t. This parser permutes.
     */
    public function testFlagsAfterPositionalArgumentsAreStillParsed(): void
    {
        $options = self::parse(['schemas.json', 'App\Flyo', '-t', 'src/Flyo', '--dry-run']);

        $this->assertSame('src/Flyo', $options->target);
        $this->assertTrue($options->dryRun);
    }

    /**
     * The headline getopt() trap: `-ns 'App\Flyo'` parses as -n with the value "s", generating
     * everything into a namespace called `s`.
     */
    public function testClusteredShortOptionWithAValueIsRejected(): void
    {
        $e = self::parseError(['-ns', 'App\Flyo', 'schemas.json']);

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
        $e = self::parseError(['-u', '-n', 'App\Flyo']);

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
        $e = self::parseError(['-u', 'a.json', '-u', 'b.json', 'App\Flyo', 'src']);

        $this->assertStringContainsString('given more than once', $e->getMessage());
    }

    public function testMixingAFlagAndAPositionalForTheSameValueIsAnError(): void
    {
        $e = self::parseError(['a.json', 'App\Flyo', 'src', '-u', 'b.json']);

        $this->assertStringContainsString('<source> was given twice', $e->getMessage());
    }

    public function testTooManyOperandsIsAnError(): void
    {
        $e = self::parseError(['a.json', 'App\Flyo', 'src', 'extra']);

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
            'no target' => [['a.json', 'App\Flyo'], '<target>'],
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
        $e = self::parseError(['--dry-run=yes', 'a.json', 'App\Flyo', 'src']);

        $this->assertStringContainsString('is a flag and takes no value', $e->getMessage());
    }

    public function testDoubleDashEndsOptionParsing(): void
    {
        $options = self::parse(['--', '-weird-file.json', 'App\Flyo', 'src']);

        $this->assertSame('-weird-file.json', $options->source);
    }

    public function testASingleDashMeansStdin(): void
    {
        $this->assertSame('-', self::parse(['-', 'App\Flyo', 'src'])->source);
    }

    public function testForwardSlashesAreAcceptedAsNamespaceSeparators(): void
    {
        $this->assertSame('App\Flyo', self::parse(['a.json', 'App/Flyo', 'src'])->namespace);
    }

    public function testLeadingAndTrailingSeparatorsAreTrimmed(): void
    {
        $this->assertSame('App\Flyo', self::parse(['a.json', '\App\Flyo\\', 'src'])->namespace);
    }

    /**
     * "App\Flyo" unquoted in bash arrives as "AppFlyo", which is a valid namespace and so
     * cannot be rejected -- but it is almost never what was meant.
     */
    public function testWarnsWhenTheNamespaceLooksLikeTheShellAteTheBackslashes(): void
    {
        $options = self::parse(['a.json', 'AppFlyo', 'src']);

        $this->assertSame('AppFlyo', $options->namespace);
        $this->assertStringContainsString('contains no "\\" separator', implode("\n", $options->warnings));
    }

    public function testDoesNotWarnAboutASingleSegmentNamespace(): void
    {
        $this->assertSame([], self::parse(['a.json', 'Flyo', 'src'])->warnings);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidNamespaces(): array
    {
        return [
            'digit first' => ['2App\Flyo'],
            'hyphen' => ['App-Blocks'],
            'space' => ['App Blocks'],
            'empty segment' => ['App\\\\Blocks'],
            'punctuation' => ['App\Flyo!'],
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
        $options = self::parse(['a.json', 'App\Flyo', '/src']);

        $this->assertStringContainsString('directly under "/"', implode("\n", $options->warnings));
    }

    public function testTokenComesFromTheEnvironmentWhenNotGiven(): void
    {
        $options = self::parse(['a.json', 'App\Flyo', 'src'], ['FLYO_TOKEN' => 'from-env']);

        $this->assertSame('from-env', $options->token);
    }

    public function testFlyoApiKeyIsAcceptedAsAFallback(): void
    {
        $options = self::parse(['a.json', 'App\Flyo', 'src'], ['FLYO_API_KEY' => 'other']);

        $this->assertSame('other', $options->token);
    }

    public function testAnExplicitTokenBeatsTheEnvironment(): void
    {
        $options = self::parse(
            ['a.json', 'App\Flyo', 'src', '--token=explicit'],
            ['FLYO_TOKEN' => 'from-env']
        );

        $this->assertSame('explicit', $options->token);
    }

    public function testCheckImpliesDryRun(): void
    {
        $options = self::parse(['a.json', 'App\Flyo', 'src', '--check']);

        $this->assertTrue($options->check);
        $this->assertTrue($options->dryRun, '--check must never write');
    }

    public function testCleanIsOnByDefaultAndDisabledByNoClean(): void
    {
        $this->assertTrue(self::parse(['a.json', 'App\Flyo', 'src'])->clean);
        $this->assertFalse(self::parse(['a.json', 'App\Flyo', 'src', '--no-clean'])->clean);
    }

    public function testQuietBeatsVerbose(): void
    {
        $options = self::parse(['a.json', 'App\Flyo', 'src', '-v', '-q']);

        $this->assertSame(Options::VERBOSITY_QUIET, $options->verbosity);
    }

    public function testClusteredFlagsAreAllowed(): void
    {
        $options = self::parse(['a.json', 'App\Flyo', 'src', '-dv']);

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

    /**
     * @return array<string, array{Profile}>
     */
    public static function profiles(): array
    {
        return [
            'flyo-generate-types' => [Profile::types()],
            'flyo-generate-blocks' => [Profile::blocks()],
        ];
    }

    #[DataProvider('profiles')]
    public function testHelpTextMentionsEveryOption(Profile $profile): void
    {
        $help = $profile->help();

        foreach (['--source', '--namespace', '--target', '--token', '--check', '--dry-run',
            '--no-clean', '--allow-empty', '--quiet', '--verbose', '--help', '--version'] as $option) {
            $this->assertStringContainsString($option, $help);
        }
    }

    public function testHelpTextListsTheDirectoryOfEveryKind(): void
    {
        $help = Profile::types()->help();

        $this->assertStringContainsString('<target>/Blocks', $help);
        $this->assertStringContainsString('<target>/Containers', $help);
        $this->assertStringContainsString('<target>/Entities', $help);
    }

    public function testTheDeprecatedHelpTextSaysSoAndNamesTheReplacement(): void
    {
        $help = Profile::blocks()->help();

        $this->assertStringContainsString('usage: flyo-generate-blocks', $help);
        $this->assertStringContainsString('DEPRECATED', $help);
        $this->assertStringContainsString('flyo-generate-types', $help);
    }

    /**
     * Pointing the new command at a namespace from the old one would put blocks in
     * App\Blocks\Blocks: valid, but almost certainly not what was meant.
     */
    public function testWarnsWhenTheNamespaceAlreadyEndsInAKindDirectory(): void
    {
        $options = self::parse(['a.json', 'App\Blocks', 'app/Blocks']);

        $this->assertStringContainsString('ends in "Blocks"', implode("\n", $options->warnings));
        $this->assertStringContainsString('App\Blocks\Blocks', implode("\n", $options->warnings));
    }

    public function testTheDeprecatedCommandDoesNotWarnAboutABlocksNamespace(): void
    {
        $options = Options::parse(['flyo-generate-blocks', 'a.json', 'App\Blocks', 'app/Blocks'], [], Profile::blocks());

        $this->assertSame([], $options->warnings);
    }

    public function testUsageErrorsNameTheProgramOfTheProfile(): void
    {
        try {
            Options::parse(['flyo-generate-blocks'], [], Profile::blocks());
            $this->fail('expected a usage error');
        } catch (GeneratorException $e) {
            $this->assertSame(['usage: flyo-generate-blocks <source> <namespace> <target> [options]'], $e->hints());
        }
    }

    /**
     * The composer.json spelling is the one most projects end up using, and the one where the
     * escaping goes wrong, so --help has to show it.
     */
    public function testHelpTextShowsTheComposerScriptSpelling(): void
    {
        $help = Profile::types()->help();

        $this->assertStringContainsString('composer.json', $help);
        $this->assertStringContainsString('App/Flyo', $help);
    }

    /**
     * @param list<string> $args
     * @param array<string, string> $env
     */
    private static function parse(array $args, array $env = []): Options
    {
        return Options::parse(['flyo-generate-types', ...$args], $env);
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
