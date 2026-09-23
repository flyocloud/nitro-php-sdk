<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\Cli;
use Flyo\Generator\ExitCode;
use Flyo\Generator\Renderer;
use Flyo\Generator\Source;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * flyo-generate-types end to end, with streams injected so nothing reaches the real
 * stdout/stderr.
 */
class CliTest extends TestCase
{
    private const SITE = 'types/site';

    private string $dir = '';

    protected function setUp(): void
    {
        $dir = tempnam(sys_get_temp_dir(), 'flyo-types-cli-');
        self::assertIsString($dir);
        unlink($dir);
        mkdir($dir);

        $this->dir = $dir;
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
        }

        rmdir($this->dir);
    }

    public function testWritesOneSubNamespacePerKind(): void
    {
        [$status, $out] = $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);

        foreach ([
            'Blocks/BlockTeaser.php' => 'App\Flyo\Blocks',
            'Containers/ContainerFooter.php' => 'App\Flyo\Containers',
            'Containers/ContainerItem.php' => 'App\Flyo\Containers',
            'Containers/ContainerMain.php' => 'App\Flyo\Containers',
            'Entities/EntityArticle.php' => 'App\Flyo\Entities',
            'Entities/EntityEvent.php' => 'App\Flyo\Entities',
        ] as $file => $namespace) {
            $this->assertStringContainsString('wrote: ' . $file, $out);
            $this->assertStringContainsString(
                'namespace ' . $namespace . ';',
                (string) file_get_contents($this->dir . '/' . $file)
            );
        }

        $this->assertStringContainsString('1 block, 2 containers, 2 entities -> ', $out);
    }

    /**
     * Yii 2 and similar frameworks map lower-case directories to lower-case namespaces, e.g.
     * app\flyo\blocks in app/flyo/blocks. The class names keep their case.
     */
    public function testLowercaseNamesTheKindDirectoriesAndSubNamespacesInLowerCase(): void
    {
        [$status, $out] = $this->invoke([self::fixture(self::SITE), 'app/flyo', $this->dir, '--lowercase']);

        $this->assertSame(ExitCode::OK, $status);

        foreach ([
            'blocks/BlockTeaser.php' => 'app\flyo\blocks',
            'containers/ContainerItem.php' => 'app\flyo\containers',
            'entities/EntityArticle.php' => 'app\flyo\entities',
        ] as $file => $namespace) {
            $this->assertStringContainsString('wrote: ' . $file, $out);
            $this->assertStringContainsString(
                'namespace ' . $namespace . ';',
                (string) file_get_contents($this->dir . '/' . $file)
            );
        }

        $this->assertStringContainsString(
            '@return array<int, \app\flyo\containers\ContainerItem>',
            (string) file_get_contents($this->dir . '/containers/ContainerMain.php')
        );
        $this->assertDirectoryDoesNotExist($this->dir . '/Blocks');
    }

    public function testLowercaseCleansTheLowerCaseDirectoriesAndPassesCheck(): void
    {
        $this->invoke([self::fixture(self::SITE), 'app/flyo', $this->dir, '--lowercase', '-q']);

        [$status] = $this->invoke([self::fixture(self::SITE), 'app/flyo', $this->dir, '--lowercase', '--check']);
        $this->assertSame(ExitCode::OK, $status);

        [$status, $out] = $this->invoke([self::fixture('blocks/hero'), 'app/flyo', $this->dir, '--lowercase']);
        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('removed: entities/EntityArticle.php', $out);
        $this->assertFileDoesNotExist($this->dir . '/entities/EntityArticle.php');
    }

    public function testTheMarkerNamesTheNewCommand(): void
    {
        $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir]);

        $this->assertStringContainsString(
            'regenerate with `vendor/bin/flyo-generate-types`',
            (string) file_get_contents($this->dir . '/Blocks/BlockTeaser.php')
        );
    }

    public function testThereIsNoDeprecationWarning(): void
    {
        [, , $err] = $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir]);

        $this->assertStringNotContainsString('deprecated', $err);
    }

    public function testUnsupportedSchemaTypesAreWarnedAbout(): void
    {
        [$status, , $err] = $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('warning: 1 schema carries x-schema-type "gadget"', $err);
    }

    public function testCheckPassesWhenTheTargetIsCurrentAndFailsWhenAKindDirectoryDrifted(): void
    {
        $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir, '-q']);

        [$status] = $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir, '--check']);
        $this->assertSame(ExitCode::OK, $status);

        file_put_contents($this->dir . '/Entities/EntityEvent.php', "<?php\n// tampered\n");

        [$status, $out] = $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir, '--check']);
        $this->assertSame(ExitCode::DRIFT, $status);
        $this->assertStringContainsString('would write: Entities/EntityEvent.php', $out);
        $this->assertSame("<?php\n// tampered\n", file_get_contents($this->dir . '/Entities/EntityEvent.php'));
    }

    /**
     * The committed golden tree is exactly what the binary produces, which is what CI checks too.
     */
    public function testTheCommittedFixtureIsCurrent(): void
    {
        [$status] = $this->invoke([
            self::fixture(self::SITE),
            'Fixture\Site',
            dirname(__DIR__) . '/fixtures/types/site/expected',
            '--check',
        ]);

        $this->assertSame(ExitCode::OK, $status);
    }

    public function testStaleFilesAreRemovedFromEveryKindDirectory(): void
    {
        $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir, '-q']);
        file_put_contents($this->dir . '/Entities/MyHelper.php', "<?php\nclass MyHelper {}\n");

        // The hero fixture has a block and nothing else, so the containers and entities go stale.
        [$status, $out] = $this->invoke([self::fixture('blocks/hero'), 'App\Flyo', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('removed: Blocks/BlockTeaser.php', $out);
        $this->assertStringContainsString('removed: Containers/ContainerItem.php', $out);
        $this->assertStringContainsString('removed: Entities/EntityArticle.php', $out);
        $this->assertStringContainsString('1 block, 0 containers, 0 entities -> ', $out);
        $this->assertFileExists($this->dir . '/Blocks/BlockHero.php');
        $this->assertFileExists($this->dir . '/Entities/MyHelper.php');
        $this->assertFileDoesNotExist($this->dir . '/Entities/EntityArticle.php');
    }

    /**
     * Nothing is generated into the target itself, so a marked file there is left over from
     * pointing the deprecated command at the same directory.
     */
    public function testBlocksLeftInTheTargetByTheDeprecatedCommandAreRemoved(): void
    {
        file_put_contents($this->dir . '/BlockOld.php', "<?php\n/** " . Renderer::MARKER . " */\nclass BlockOld {}\n");
        file_put_contents($this->dir . '/Mine.php', "<?php\nclass Mine {}\n");

        [, $out] = $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir]);

        $this->assertStringContainsString('removed: BlockOld.php', $out);
        $this->assertFileDoesNotExist($this->dir . '/BlockOld.php');
        $this->assertFileExists($this->dir . '/Mine.php');
    }

    public function testASubdirectoryNoKindOwnsIsNeverTouched(): void
    {
        mkdir($this->dir . '/Custom');
        $marked = "<?php\n/** " . Renderer::MARKER . " */\nclass Other {}\n";
        file_put_contents($this->dir . '/Custom/Other.php', $marked);

        $this->invoke([self::fixture(self::SITE), 'App\Flyo', $this->dir, '-q']);

        $this->assertSame($marked, file_get_contents($this->dir . '/Custom/Other.php'));
    }

    public function testADocumentWithOnlySomeKindsIsFine(): void
    {
        [$status, $out] = $this->invoke([self::fixture('blocks/entities-only'), 'App\Flyo', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('0 blocks, 0 containers, 2 entities -> ', $out);
        $this->assertDirectoryDoesNotExist($this->dir . '/Blocks');
        $this->assertDirectoryDoesNotExist($this->dir . '/Containers');
    }

    public function testADocumentWithoutAnyTypedSchemaFails(): void
    {
        $source = new Source(self::mockClient(new Response(200, [], (string) json_encode([
            'openapi' => '3.0.3',
            'components' => ['schemas' => [
                'block' => ['type' => 'object'],
                'GadgetClock' => ['type' => 'object', 'x-schema-type' => 'gadget'],
            ]],
        ]))));

        [$status, , $err] = $this->invoke(['https://api.flyo.cloud/x', 'App\Flyo', $this->dir], $source);

        $this->assertSame(ExitCode::NO_TYPES, $status);
        $this->assertStringContainsString(
            'the document declares no typed schemas: 0 of 2 schemas carry an x-schema-type of '
                . '"block", "container", "entity" (1 has none, 1 is "gadget").',
            $err
        );
        $this->assertStringContainsString('--allow-empty', $err);
    }

    public function testNamespaceEndingInAKindDirectoryIsWarnedAbout(): void
    {
        [$status, , $err] = $this->invoke([self::fixture(self::SITE), 'App\Blocks', $this->dir, '-q']);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('ends in "Blocks"', $err);
    }

    public function testErrorsCarryTheProgramName(): void
    {
        [$status, , $err] = $this->invoke(['schemas.json']);

        $this->assertSame(ExitCode::USAGE, $status);
        $this->assertStringContainsString('flyo-generate-types: error:', $err);
        $this->assertStringContainsString('usage: flyo-generate-types', $err);
    }

    public function testHelpAndVersionNameTheProgram(): void
    {
        [$status, $out] = $this->invoke(['--help']);
        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('usage: flyo-generate-types', $out);

        [$status, $out] = $this->invoke(['--version']);
        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringStartsWith('flyo-generate-types (flyo/nitro-php ', $out);
    }

    public function testSendsItsOwnUserAgent(): void
    {
        $body = (string) file_get_contents(self::fixture(self::SITE));
        $handler = new MockHandler([new Response(200, [], $body)]);
        $source = new Source(new Client(['handler' => HandlerStack::create($handler)]));

        [$status] = $this->invoke(
            ['https://api.flyo.cloud/nitro/v1/openapi/schemas', 'App\Flyo', $this->dir, '-q'],
            $source,
            ['FLYO_TOKEN' => 'SECRET'],
        );

        $this->assertSame(ExitCode::OK, $status);

        $request = $handler->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('token=SECRET', $request->getUri()->getQuery());
        $this->assertSame('flyo-generate-types (flyo/nitro-php)', $request->getHeaderLine('User-Agent'));
    }

    private static function fixture(string $name): string
    {
        return dirname(__DIR__) . '/fixtures/' . $name . '/openapi.json';
    }

    private static function mockClient(Response $response): Client
    {
        return new Client(['handler' => HandlerStack::create(new MockHandler([$response]))]);
    }

    /**
     * @param list<string> $args
     * @param array<string, string> $env
     * @return array{int, string, string} status, stdout, stderr
     */
    private function invoke(array $args, ?Source $source = null, array $env = []): array
    {
        $out = fopen('php://memory', 'r+');
        $err = fopen('php://memory', 'r+');
        self::assertIsResource($out);
        self::assertIsResource($err);

        $status = Cli::main(['flyo-generate-types', ...$args], $env, $out, $err, $source);

        rewind($out);
        rewind($err);

        return [$status, (string) stream_get_contents($out), (string) stream_get_contents($err)];
    }
}
