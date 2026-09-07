<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\Cli;
use Flyo\Blocks\Generator\ExitCode;
use Flyo\Blocks\Generator\Source;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * The command end to end, with streams injected so nothing reaches the real stdout/stderr.
 */
class CliTest extends TestCase
{
    private string $dir = '';

    protected function setUp(): void
    {
        $dir = tempnam(sys_get_temp_dir(), 'flyo-cli-');
        self::assertIsString($dir);
        unlink($dir);
        mkdir($dir);

        $this->dir = $dir;
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }

        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    public function testGeneratesFromALocalFile(): void
    {
        [$status, $out, $err] = $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('wrote: BlockHero.php', $out);
        $this->assertSame('', $err);
        $this->assertFileExists($this->dir . '/BlockHero.php');
        $this->assertStringContainsString('namespace App\Blocks;', (string) file_get_contents($this->dir . '/BlockHero.php'));
    }

    public function testSecondRunReportsEverythingUnchanged(): void
    {
        $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir]);
        [$status, $out] = $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir, '-v']);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('unchanged: BlockHero.php', $out);
        $this->assertStringNotContainsString('wrote:', $out);
    }

    public function testCheckPassesWhenTheTargetIsCurrent(): void
    {
        $this->invoke([self::fixture('full'), 'App\Blocks', $this->dir]);
        [$status] = $this->invoke([self::fixture('full'), 'App\Blocks', $this->dir, '--check']);

        $this->assertSame(ExitCode::OK, $status);
    }

    public function testCheckReportsDriftWithoutWriting(): void
    {
        $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir]);
        file_put_contents($this->dir . '/BlockHero.php', "<?php\n// tampered\n");

        [$status, $out] = $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir, '--check']);

        $this->assertSame(ExitCode::DRIFT, $status);
        $this->assertStringContainsString('out of date', $out);
        $this->assertSame("<?php\n// tampered\n", file_get_contents($this->dir . '/BlockHero.php'));
    }

    public function testQuietPrintsNothingOnSuccess(): void
    {
        [$status, $out, $err] = $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir, '-q']);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertSame('', $out);
        $this->assertSame('', $err);
    }

    public function testWarningsGoToStderrEvenWhenQuiet(): void
    {
        [$status, $out, $err] = $this->invoke([self::fixture('full'), 'App\Blocks', $this->dir, '-q']);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertSame('', $out);
        $this->assertStringContainsString('warning:', $err);
        $this->assertStringContainsString('ref_dangling', $err);
    }

    public function testCollisionWarningIsReported(): void
    {
        [$status, , $err] = $this->invoke([self::fixture('collisions'), 'App\Blocks', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('want the class name BlockHeroBanner', $err);
    }

    public function testDocumentWithoutTypedBlocksFails(): void
    {
        [$status, , $err] = $this->invoke([self::fixture('entities-only'), 'App\Blocks', $this->dir]);

        $this->assertSame(ExitCode::NO_BLOCKS, $status);
        $this->assertStringContainsString('no typed blocks', $err);
        $this->assertStringContainsString('2 are "entity"', $err);
        $this->assertStringContainsString('--allow-empty', $err);
    }

    public function testAllowEmptyTurnsThatIntoSuccess(): void
    {
        [$status, , $err] = $this->invoke(
            [self::fixture('entities-only'), 'App\Blocks', $this->dir, '--allow-empty']
        );

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('warning:', $err);
    }

    public function testUsageErrorsGoToStderrWithTheProgramName(): void
    {
        [$status, $out, $err] = $this->invoke(['-ns', 'App\Blocks']);

        $this->assertSame(ExitCode::USAGE, $status);
        $this->assertSame('', $out);
        $this->assertStringContainsString('flyo-generate-blocks: error:', $err);
        $this->assertStringContainsString('hint:', $err);
    }

    public function testHelpGoesToStdoutAndSucceeds(): void
    {
        [$status, $out, $err] = $this->invoke(['--help']);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('usage: flyo-generate-blocks', $out);
        $this->assertSame('', $err);
    }

    public function testVersionGoesToStdoutAndSucceeds(): void
    {
        [$status, $out] = $this->invoke(['--version']);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('flyo/nitro-php', $out);
    }

    public function testMissingFileIsAFetchError(): void
    {
        [$status, , $err] = $this->invoke(['/does/not/exist.json', 'App\Blocks', $this->dir]);

        $this->assertSame(ExitCode::FETCH, $status);
        $this->assertStringContainsString('source file not found', $err);
    }

    public function testADocumentWithoutComponentsSchemasIsAParseError(): void
    {
        $source = new Source(self::mockClient(new Response(200, [], '{"openapi":"3.0.3","paths":{}}')));

        [$status, , $err] = $this->invoke(['https://api.flyo.cloud/x', 'App\Blocks', $this->dir], $source);

        $this->assertSame(ExitCode::PARSE, $status);
        $this->assertStringContainsString('no "components.schemas"', $err);
        $this->assertStringContainsString('"3.0.3"', $err);
    }

    public function testGeneratesFromHttpWithATokenFromTheEnvironment(): void
    {
        $body = (string) file_get_contents(self::fixture('hero'));
        $handler = new MockHandler([new Response(200, [], $body)]);
        $stack = HandlerStack::create($handler);
        $source = new Source(new Client(['handler' => $stack]));

        [$status] = $this->invoke(
            ['https://api.flyo.cloud/nitro/v1/openapi/schemas', 'App\Blocks', $this->dir],
            $source,
            ['FLYO_TOKEN' => 'SECRET'],
        );

        $this->assertSame(ExitCode::OK, $status);
        $this->assertFileExists($this->dir . '/BlockHero.php');

        $request = $handler->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('token=SECRET', $request->getUri()->getQuery());
    }

    public function testStaleFilesAreCleanedAndHandWrittenOnesSurvive(): void
    {
        $this->invoke([self::fixture('full'), 'App\Blocks', $this->dir, '-q']);
        file_put_contents($this->dir . '/MyHelper.php', "<?php\nclass MyHelper {}\n");

        // The hero fixture produces only BlockHero.php, so the full fixture's classes go stale.
        [$status, $out] = $this->invoke([self::fixture('hero'), 'App\Blocks', $this->dir]);

        $this->assertSame(ExitCode::OK, $status);
        $this->assertStringContainsString('removed: BlockCoverage.php', $out);
        $this->assertFileDoesNotExist($this->dir . '/BlockCoverage.php');
        $this->assertFileExists($this->dir . '/MyHelper.php');
    }

    private static function fixture(string $name): string
    {
        return dirname(__DIR__, 2) . '/fixtures/blocks/' . $name . '/openapi.json';
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

        $status = Cli::main(['flyo-generate-blocks', ...$args], $env, $out, $err, $source);

        rewind($out);
        rewind($err);

        return [$status, (string) stream_get_contents($out), (string) stream_get_contents($err)];
    }
}
