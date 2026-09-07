<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Loads and decodes the OpenAPI document.
 *
 * Guzzle rather than `file_get_contents()`: it is already a hard dependency of this package, it
 * reports transport failures as exceptions instead of `false`, and its MockHandler makes every
 * HTTP path testable without a network.
 */
final class Source
{
    private const TIMEOUT = 30;

    private const CONNECT_TIMEOUT = 10;

    /** @var resource|null */
    private $stdin;

    /**
     * @param resource|null $stdin
     */
    public function __construct(
        private readonly ?ClientInterface $http = null,
        $stdin = null,
    ) {
        $this->stdin = $stdin;
    }

    /**
     * @param array<string, string> $env
     * @return array<string, mixed>
     * @throws GeneratorException
     */
    public function load(Options $options, array $env = []): array
    {
        return self::decode($this->fetch($options, $env));
    }

    /**
     * @param array<string, string> $env
     * @throws GeneratorException
     */
    private function fetch(Options $options, array $env): string
    {
        $source = $options->source;

        if ($source === '-') {
            return $this->readStdin();
        }

        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+.-]*)://#', $source, $match) === 1) {
            $scheme = strtolower($match[1]);

            if ($scheme === 'http' || $scheme === 'https') {
                return $this->request(self::withToken($source, $options->token), $env);
            }

            if ($scheme === 'file') {
                return self::readFile((string) preg_replace('#^file://#i', '', $source));
            }

            throw GeneratorException::fetch(
                sprintf('unsupported source scheme "%s".', $scheme),
                ['use http(s), a local file path, or - to read the document from stdin.'],
            );
        }

        return self::readFile($source);
    }

    /**
     * @throws GeneratorException
     */
    private function readStdin(): string
    {
        $stream = $this->stdin ?? STDIN;

        if ($this->stdin === null && function_exists('posix_isatty') && @posix_isatty(STDIN)) {
            throw GeneratorException::fetch(
                'the source is "-" but stdin is a terminal.',
                ['pipe the document in, e.g. cat schemas.json | ' . Options::PROGRAM . ' - \'App\Blocks\' src/Blocks'],
            );
        }

        $contents = stream_get_contents($stream);

        if ($contents === false) {
            throw GeneratorException::fetch('could not read the document from stdin.');
        }

        return $contents;
    }

    /**
     * @throws GeneratorException
     */
    private static function readFile(string $path): string
    {
        if (is_dir($path)) {
            throw GeneratorException::fetch(sprintf('the source is a directory: %s', $path));
        }

        if (!is_file($path)) {
            throw GeneratorException::fetch(sprintf('source file not found: %s', $path));
        }

        if (!is_readable($path)) {
            throw GeneratorException::fetch(sprintf('source file not readable: %s', $path));
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw GeneratorException::fetch(sprintf('could not read the source file: %s', $path));
        }

        return $contents;
    }

    /**
     * @param array<string, string> $env
     * @throws GeneratorException
     */
    private function request(string $url, array $env): string
    {
        $options = [
            'timeout' => self::TIMEOUT,
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Cache-Control' => 'no-cache',
                'User-Agent' => Options::PROGRAM . ' (' . Options::PACKAGE . ')',
            ],
        ];

        // Guzzle does not read proxy environment variables by itself, and CI runners often need one.
        $proxy = $env['HTTPS_PROXY'] ?? $env['https_proxy'] ?? $env['HTTP_PROXY'] ?? $env['http_proxy'] ?? null;
        if ($proxy !== null && $proxy !== '') {
            $options['proxy'] = [
                'https' => $proxy,
                'http' => $env['HTTP_PROXY'] ?? $env['http_proxy'] ?? $proxy,
                'no' => array_values(array_filter(explode(',', $env['NO_PROXY'] ?? $env['no_proxy'] ?? ''))),
            ];
        }

        $client = $this->http ?? new Client();

        try {
            $response = $client->request('GET', $url, $options);
        } catch (GuzzleException $e) {
            throw GeneratorException::fetch(
                sprintf('could not fetch %s: %s', self::redact($url), $e->getMessage()),
            );
        }

        $status = $response->getStatusCode();

        if ($status < 200 || $status > 299) {
            throw GeneratorException::fetch(
                sprintf(
                    '%s returned HTTP %d %s.',
                    self::redact($url),
                    $status,
                    $response->getReasonPhrase(),
                ),
                self::statusHints($status),
            );
        }

        return (string) $response->getBody();
    }

    /**
     * @return list<string>
     */
    private static function statusHints(int $status): array
    {
        if ($status === 401 || $status === 403) {
            return [
                'typed block schemas require a token. Pass --token=<key> or set $FLYO_TOKEN.',
            ];
        }

        if ($status === 404) {
            return [
                'typed blocks live at https://api.flyo.cloud/nitro/v1/openapi/schemas — '
                    . 'the public /nitro/v1/openapi endpoint has none.',
            ];
        }

        if ($status >= 500) {
            return ['this is a server-side error; try again.'];
        }

        return [];
    }

    /**
     * Merges the token into the URL's query string, leaving an existing `token` alone.
     *
     * The Flyo API takes its key as a query parameter rather than a header — see
     * \Flyo\Api\PagesApi, which does the same.
     */
    public static function withToken(string $url, ?string $token): string
    {
        if ($token === null || $token === '') {
            return $url;
        }

        $parts = parse_url($url);

        if ($parts === false || !isset($parts['host'])) {
            throw GeneratorException::fetch(sprintf('not a valid URL: %s', $url));
        }

        parse_str($parts['query'] ?? '', $query);

        if (isset($query['token'])) {
            return $url;
        }

        $query['token'] = $token;

        $userInfo = '';
        if (isset($parts['user'])) {
            $userInfo = $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . '@';
        }

        return ($parts['scheme'] ?? 'https') . '://'
            . $userInfo
            . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . ($parts['path'] ?? '')
            . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986)
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * Replaces the token value with `***`, so a URL can appear in an error message.
     */
    public static function redact(string $url): string
    {
        return (string) preg_replace('/([?&]token=)[^&#]*/i', '$1***', $url);
    }

    /**
     * @return array<string, mixed>
     * @throws GeneratorException
     */
    private static function decode(string $raw): array
    {
        if (trim($raw) === '') {
            throw GeneratorException::parse('the source returned an empty body.');
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw GeneratorException::parse(
                sprintf('the source is not valid JSON: %s', $e->getMessage()),
                [
                    'an HTML response usually means a wrong URL or a missing token.',
                    sprintf('first %d bytes: %s', min(120, strlen($raw)), self::preview($raw)),
                ],
            );
        }

        if (!is_array($decoded) || array_is_list($decoded)) {
            throw GeneratorException::parse('the document root is not a JSON object.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private static function preview(string $raw): string
    {
        $preview = substr($raw, 0, 120);
        $preview = (string) preg_replace('/\s+/', ' ', $preview);

        return trim($preview);
    }
}
