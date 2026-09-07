<?php

namespace Flyo\Test\Blocks\Generator;

use Flyo\Blocks\Generator\ExitCode;
use Flyo\Blocks\Generator\GeneratorException;
use Flyo\Blocks\Generator\Options;
use Flyo\Blocks\Generator\Source;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    // -- token merging, pure ------------------------------------------------------------------

    /**
     * @return array<string, array{string, ?string, string}>
     */
    public static function tokenUrls(): array
    {
        return [
            'no query' => [
                'https://api.flyo.cloud/nitro/v1/openapi/schemas',
                'KEY',
                'https://api.flyo.cloud/nitro/v1/openapi/schemas?token=KEY',
            ],
            'existing query' => [
                'https://api.flyo.cloud/x?lang=de',
                'KEY',
                'https://api.flyo.cloud/x?lang=de&token=KEY',
            ],
            'token already present is left alone' => [
                'https://api.flyo.cloud/x?token=ORIGINAL',
                'KEY',
                'https://api.flyo.cloud/x?token=ORIGINAL',
            ],
            'no token to add' => [
                'https://api.flyo.cloud/x',
                null,
                'https://api.flyo.cloud/x',
            ],
            'empty token to add' => [
                'https://api.flyo.cloud/x',
                '',
                'https://api.flyo.cloud/x',
            ],
            'port is preserved' => [
                'https://api.flyo.cloud:8443/x',
                'KEY',
                'https://api.flyo.cloud:8443/x?token=KEY',
            ],
            'fragment is preserved' => [
                'https://api.flyo.cloud/x#frag',
                'KEY',
                'https://api.flyo.cloud/x?token=KEY#frag',
            ],
            'token is url encoded' => [
                'https://api.flyo.cloud/x',
                'a b/c+d',
                'https://api.flyo.cloud/x?token=a%20b%2Fc%2Bd',
            ],
        ];
    }

    #[DataProvider('tokenUrls')]
    public function testWithToken(string $url, ?string $token, string $expected): void
    {
        $this->assertSame($expected, Source::withToken($url, $token));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function redactions(): array
    {
        return [
            'only parameter' => ['https://a/b?token=SECRET', 'https://a/b?token=***'],
            'among others' => ['https://a/b?lang=de&token=SECRET', 'https://a/b?lang=de&token=***'],
            'followed by others' => ['https://a/b?token=SECRET&lang=de', 'https://a/b?token=***&lang=de'],
            'before a fragment' => ['https://a/b?token=SECRET#x', 'https://a/b?token=***#x'],
            'nothing to redact' => ['https://a/b?lang=de', 'https://a/b?lang=de'],
        ];
    }

    #[DataProvider('redactions')]
    public function testRedactHidesTheToken(string $url, string $expected): void
    {
        $this->assertSame($expected, Source::redact($url));
    }

    // -- local files -------------------------------------------------------------------------

    public function testReadsALocalFile(): void
    {
        $document = (new Source())->load(self::options(self::fixture()));

        $this->assertArrayHasKey('components', $document);
    }

    public function testMissingFile(): void
    {
        $e = self::loadError(self::options('/does/not/exist.json'));

        $this->assertSame(ExitCode::FETCH, $e->exitCode());
        $this->assertStringContainsString('source file not found', $e->getMessage());
    }

    public function testDirectoryAsSource(): void
    {
        $e = self::loadError(self::options(dirname(self::fixture())));

        $this->assertStringContainsString('is a directory', $e->getMessage());
    }

    public function testFileScheme(): void
    {
        $document = (new Source())->load(self::options('file://' . self::fixture()));

        $this->assertArrayHasKey('components', $document);
    }

    public function testUnsupportedScheme(): void
    {
        $e = self::loadError(self::options('ftp://example.com/x.json'));

        $this->assertStringContainsString('unsupported source scheme "ftp"', $e->getMessage());
    }

    // -- stdin -------------------------------------------------------------------------------

    public function testReadsStdin(): void
    {
        $stream = fopen('php://memory', 'r+');
        self::assertIsResource($stream);
        fwrite($stream, '{"components":{"schemas":{}}}');
        rewind($stream);

        $document = (new Source(null, $stream))->load(self::options('-'));

        $this->assertSame(['components' => ['schemas' => []]], $document);
    }

    // -- http --------------------------------------------------------------------------------

    public function testFetchesOverHttp(): void
    {
        $source = self::httpSource(new Response(200, [], '{"components":{"schemas":{}}}'));

        $this->assertSame(
            ['components' => ['schemas' => []]],
            $source->load(self::options('https://api.flyo.cloud/nitro/v1/openapi/schemas'))
        );
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function errorStatuses(): array
    {
        return [
            'unauthorized' => [401, 'require a token'],
            'forbidden' => [403, 'require a token'],
            'not found' => [404, 'the public /nitro/v1/openapi endpoint has none'],
            'server error' => [502, 'server-side error'],
        ];
    }

    #[DataProvider('errorStatuses')]
    public function testHttpErrorStatus(int $status, string $expectedHint): void
    {
        $source = self::httpSource(new Response($status, [], 'nope'));
        $e = self::loadError(self::options('https://api.flyo.cloud/x'), $source);

        $this->assertSame(ExitCode::FETCH, $e->exitCode());
        $this->assertStringContainsString('HTTP ' . $status, $e->getMessage());
        $this->assertStringContainsString($expectedHint, implode("\n", $e->hints()));
    }

    /**
     * A failing request must never echo the token back.
     */
    public function testTheTokenIsNotLeakedIntoAnErrorMessage(): void
    {
        $source = self::httpSource(new Response(401, [], ''));
        $options = Options::parse(
            ['x', 'https://api.flyo.cloud/x', 'App\Blocks', 'src', '--token=SUPERSECRET'],
        );

        $e = self::loadError($options, $source);

        $this->assertStringNotContainsString('SUPERSECRET', $e->getMessage());
        $this->assertStringContainsString('token=***', $e->getMessage());
    }

    // -- decoding ----------------------------------------------------------------------------

    public function testEmptyBody(): void
    {
        $e = self::loadError(self::options('https://a/b'), self::httpSource(new Response(200, [], '   ')));

        $this->assertSame(ExitCode::PARSE, $e->exitCode());
        $this->assertStringContainsString('empty body', $e->getMessage());
    }

    public function testHtmlInsteadOfJson(): void
    {
        $html = '<!DOCTYPE html><html><body>Login required</body></html>';
        $e = self::loadError(self::options('https://a/b'), self::httpSource(new Response(200, [], $html)));

        $this->assertSame(ExitCode::PARSE, $e->exitCode());
        $this->assertStringContainsString('not valid JSON', $e->getMessage());

        $hints = implode("\n", $e->hints());
        $this->assertStringContainsString('wrong URL or a missing token', $hints);
        $this->assertStringContainsString('<!DOCTYPE html>', $hints);
    }

    public function testJsonArrayRootIsRejected(): void
    {
        $e = self::loadError(self::options('https://a/b'), self::httpSource(new Response(200, [], '[1,2,3]')));

        $this->assertStringContainsString('root is not a JSON object', $e->getMessage());
    }

    public function testJsonScalarRootIsRejected(): void
    {
        $e = self::loadError(self::options('https://a/b'), self::httpSource(new Response(200, [], '42')));

        $this->assertStringContainsString('root is not a JSON object', $e->getMessage());
    }

    // -- helpers -----------------------------------------------------------------------------

    private static function fixture(): string
    {
        return dirname(__DIR__, 2) . '/fixtures/blocks/hero/openapi.json';
    }

    private static function options(string $source): Options
    {
        return Options::parse(['x', $source, 'App\Blocks', 'src/Blocks']);
    }

    private static function httpSource(Response $response): Source
    {
        $stack = HandlerStack::create(new MockHandler([$response]));

        return new Source(new Client(['handler' => $stack]));
    }

    private static function loadError(Options $options, ?Source $source = null): GeneratorException
    {
        try {
            ($source ?? new Source())->load($options);
        } catch (GeneratorException $e) {
            return $e;
        }

        self::fail('expected a failure for source: ' . $options->source);
    }
}
