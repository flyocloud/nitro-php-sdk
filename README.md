# Flyo Nitro PHP SDK

🚀 **Supercharge your PHP applications with Flyo Nitro!** This SDK provides a clean, intuitive interface to interact with the Flyo Nitro API, enabling you to manage content, configurations, and entities with ease.

## 📦 Installation

Install the SDK via Composer:

```sh
composer require flyo/nitro-php
```

## 🚀 Quick Start

Get up and running in just a few lines of code:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure authentication
$config = new Flyo\Configuration();
$config->setApiKey('token', 'YOUR_FLYO_AUTH_TOKEN');

Flyo\Configuration::setDefaultConfiguration($config);

// Make your first API call
$api = new Flyo\Api\ConfigApi();
try {
    $result = $api->config();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConfigApi->config: ', $e->getMessage(), PHP_EOL;
}
```

## 🧩 Typed Blocks

`vendor/bin/flyo-generate-blocks` turns your integration's OpenAPI document into one PHP class per
block, so your IDE and PHPStan know the shape of every `content`, `config` and `items` field
instead of handing you a bare `\stdClass`.

### Wire it into composer.json

Generating is something you repeat on every schema change, so keep the arguments in your project's
`composer.json` instead of retyping them:

```json
{
    "scripts": {
        "flyo:types": "vendor/bin/flyo-generate-blocks https://api.flyo.cloud/nitro/v1/openapi/schemas App/Blocks app/Blocks"
    }
}
```

```sh
export FLYO_TOKEN=your-nitro-token

composer flyo:types              # regenerate
composer flyo:types -- --check   # exit 6 when the committed classes are out of date
```

Composer appends anything after `--` to the script, so one entry covers both the write and the CI
check. The token stays out of `composer.json`: Composer does not read a framework `.env`, so export
it in your shell or hand it in as a CI secret.

**Use forward slashes for the namespace in `composer.json`.** `App/Blocks` is normalised to
`App\Blocks`, and unlike a backslash it has nothing to survive: a backslash has to get past JSON
*and* the shell, which is how a namespace ends up as `AppBlocks` or with an empty segment. On a
POSIX shell `"... 'App\\Blocks' ..."` and `"... App\\\\Blocks ..."` are the two spellings that
come out right, and neither of them does on Windows.

On the command line, quote it instead:

```sh
vendor/bin/flyo-generate-blocks \
    https://api.flyo.cloud/nitro/v1/openapi/schemas \
    'App\Blocks' \
    src/Blocks
```

| argument | meaning |
|----------|---------|
| `<source>` | OpenAPI URL, a local `.json` path, or `-` to read from stdin |
| `<namespace>` | PSR-4 namespace for the generated classes |
| `<target>` | the directory that namespace maps to |

### Where the classes go

`<namespace>` and `<target>` have to agree with the PSR-4 map in your `composer.json`, otherwise
the classes are written but never autoloaded. The common setups:

| project | `autoload.psr-4` | `<namespace>` | `<target>` |
|---------|------------------|---------------|------------|
| Laravel | `"App\\": "app/"` | `App/Blocks` | `app/Blocks` |
| Laravel, grouped with the HTTP layer | `"App\\": "app/"` | `App/Http/Blocks` | `app/Http/Blocks` |
| Symfony | `"App\\": "src/"` | `App/Blocks` | `src/Blocks` |
| Own package or plain PSR-4 | `"Acme\\Site\\": "src/"` | `Acme/Site/Blocks` | `src/Blocks` |

The rule behind the table: take the prefix and its directory from `autoload.psr-4`, then append the
same trailing segment to both. The target directory is created if it does not exist.

Typed block schemas only exist on the **authenticated** `/nitro/v1/openapi/schemas` endpoint. The
public `/nitro/v1/openapi` has none. Pass the token through `FLYO_TOKEN` (or `FLYO_API_KEY`) rather
than `--token`: an argument is visible to anyone who can run `ps`.

### Using them

The generated classes are documentation-only: nothing instantiates them, and at runtime a block
stays a `\Flyo\Model\Block`. You swap the annotation at the framework boundary and everything below
it becomes typed:

```php
/** @var \App\Blocks\BlockHero $block */   // instead of \Flyo\Model\Block

echo $block->getContent()->title;             // string|null
echo $block->getContent()->image->source;     // string|null

foreach ($block->getItems() ?? [] as $item) {
    echo $item->link->href;                   // string|null
}
```

Values below the top level really are plain objects — the API decodes with
`json_decode($json, false)` — so property access there needs no support from the SDK, and a typo
like `->titl` is reported by PHPStan as an undefined property.

Each class also carries `IDENTIFIER`, `COMPONENT` and `SLOTS` constants, which is handy for
dispatching:

```php
match ($block->getIdentifier()) {
    \App\Blocks\BlockHero::IDENTIFIER => $this->renderHero($block),
    // ...
};
```

### Keeping them current

Commit the generated files and run `composer flyo:types -- --check` in CI: it writes nothing and
exits `6` as soon as the schema has moved on.

Files are marked `@generated by flyo/nitro-php`; regenerating replaces them and removes ones that
are no longer produced. Hand-written files in the same directory are never touched. Run
`vendor/bin/flyo-generate-blocks --help` for all options.

## 📚 Available APIs

The SDK provides access to the following API endpoints:

| API | Description | Source |
|-----|-------------|--------|
| **ConfigApi** | Retrieve configuration and settings | [`lib/Api/ConfigApi.php`](lib/Api/ConfigApi.php) |
| **EntitiesApi** | Manage and query entities | [`lib/Api/EntitiesApi.php`](lib/Api/EntitiesApi.php) |
| **PagesApi** | Handle page content and structure | [`lib/Api/PagesApi.php`](lib/Api/PagesApi.php) |
| **SearchApi** | Perform content searches | [`lib/Api/SearchApi.php`](lib/Api/SearchApi.php) |
| **SitemapApi** | Generate and manage sitemaps | [`lib/Api/SitemapApi.php`](lib/Api/SitemapApi.php) |
| **VersionApi** | Check API version information | [`lib/Api/VersionApi.php`](lib/Api/VersionApi.php) |

## 📖 Documentation

For comprehensive guides, examples, and API reference:

- 📘 [Full Documentation](https://docs.flyo.cloud/doc/integrations-nitro-cms-sdks)
- 🔍 [API Reference](docs/)