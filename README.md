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

## 🧩 Typed Schemas

`vendor/bin/flyo-generate-types` turns your integration's OpenAPI document into typed PHP classes,
so your IDE and PHPStan know the shape of your blocks, entities and navigation containers instead
of handing you a bare `\stdClass` or `array<string, mixed>`. Each kind gets a directory and a
sub-namespace of its own:

```
app/Flyo/
├── Blocks/        BlockHero.php, BlockText.php, ...       one per block, narrowing \Flyo\Model\Block
├── Containers/    ContainerMain.php, ContainerItem.php    one per navigation container, plus its item class
└── Entities/      EntityArticle.php, ...                  one per entity type, narrowing \Flyo\Model\Entity
```

A directory only appears once the document has something for it, and more kinds will follow as
the API gains them. A schema type this version does not know yet is reported and skipped.

### Wire it into composer.json

Generating is something you repeat on every schema change, so keep the arguments in your project's
`composer.json` instead of retyping them:

```json
{
    "scripts": {
        "flyo:types": "vendor/bin/flyo-generate-types https://api.flyo.cloud/nitro/v1/openapi/schemas App/Flyo app/Flyo"
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

**Use forward slashes for the namespace in `composer.json`.** `App/Flyo` is normalised to
`App\Flyo`, and unlike a backslash it has nothing to survive: a backslash has to get past JSON
*and* the shell, which is how a namespace ends up as `AppFlyo` or with an empty segment. On a
POSIX shell `"... 'App\\Flyo' ..."` and `"... App\\\\Flyo ..."` are the two spellings that
come out right, and neither of them does on Windows.

On the command line, quote it instead:

```sh
vendor/bin/flyo-generate-types \
    https://api.flyo.cloud/nitro/v1/openapi/schemas \
    'App\Flyo' \
    app/Flyo
```

| argument | meaning |
|----------|---------|
| `<source>` | OpenAPI URL, a local `.json` path, or `-` to read from stdin |
| `<namespace>` | PSR-4 root namespace; the kinds become `<namespace>\Blocks`, `<namespace>\Containers`, `<namespace>\Entities` |
| `<target>` | the directory that namespace maps to |

### Where the classes go

`<namespace>` and `<target>` have to agree with the PSR-4 map in your `composer.json`, otherwise
the classes are written but never autoloaded. The common setups:

| project | `autoload.psr-4` | `<namespace>` | `<target>` |
|---------|------------------|---------------|------------|
| Laravel | `"App\\": "app/"` | `App/Flyo` | `app/Flyo` |
| Symfony | `"App\\": "src/"` | `App/Flyo` | `src/Flyo` |
| Own package or plain PSR-4 | `"Acme\\Site\\": "src/"` | `Acme/Site/Flyo` | `src/Flyo` |

The rule behind the table: take the prefix and its directory from `autoload.psr-4`, then append the
same trailing segment to both. The target directory is created if it does not exist.

#### Lower-case directories (Yii 2)

Some frameworks map lower-case directories to lower-case namespaces, like Yii 2's
`app\flyo\blocks\BlockHero` in `app/flyo/blocks/BlockHero.php`. Pass `--lowercase` and the kind
directories and sub-namespaces become `blocks`, `containers` and `entities`; the class names keep
their case:

```sh
vendor/bin/flyo-generate-types <source> app/flyo flyo --lowercase
```

For Yii 2's basic template, where `@app` is the project root, that writes `flyo/blocks/*.php` in
the namespace `app\flyo\blocks`. Use the same flag for every run, `--check` included: switching
it changes every file.

Typed schemas only exist on the **authenticated** `/nitro/v1/openapi/schemas` endpoint. The public
`/nitro/v1/openapi` has none. Pass the token through `FLYO_TOKEN` (or `FLYO_API_KEY`) rather than
`--token`: an argument is visible to anyone who can run `ps`.

### Using them

The generated classes are documentation-only: nothing instantiates them, and at runtime the API
still returns the plain SDK models. You swap the annotation at the framework boundary and
everything below it becomes typed.

**Blocks** narrow `getContent()`, `getConfig()` and `getItems()`:

```php
/** @var \App\Flyo\Blocks\BlockHero $block */   // instead of \Flyo\Model\Block

echo $block->getContent()->title;                  // string|null
echo $block->getContent()->image->source;          // string|null

foreach ($block->getItems() ?? [] as $item) {
    echo $item->link->href;                        // string|null
}

match ($block->getIdentifier()) {
    \App\Flyo\Blocks\BlockHero::IDENTIFIER => $this->renderHero($block),
    // ...
};
```

Each block class also carries `IDENTIFIER`, `COMPONENT` and `SLOTS` constants.

**Entities** narrow `getModel()`, the part of an entity response whose shape depends on its type:

```php
$entity = (new \Flyo\Api\EntitiesApi())->entityBySlug('my-article');

if ($entity instanceof \Flyo\Model\Entity) {
    /** @var \App\Flyo\Entities\EntityArticle $entity */
    echo $entity->getModel()?->title;               // string|null
    echo $entity->getModel()?->image?->source;      // string|null
}
```

**Containers** narrow `getItems()` to the generated item class, whose `getProperties()` knows the
page properties your site makes available to the navigation and whose `getChildren()` are items
again, all the way down the tree:

```php
$config = (new \Flyo\Api\ConfigApi())->config();

/** @var \App\Flyo\Containers\ContainerMain $main */
$main = ($config->getContainers() ?? [])[\App\Flyo\Containers\ContainerMain::IDENTIFIER];

foreach ($main->getItems() as $item) {
    echo $item->getLabel(), $item->getHref();
    echo $item->getProperties()['icon'];           // whatever type the property has

    foreach ($item->getChildren() as $child) {
        // ...
    }
}
```

Values below the top level really are plain objects (the API decodes with
`json_decode($json, false)`) so property access there needs no support from the SDK, and a typo
like `->titl` or `['iocn']` is reported by PHPStan as an undefined property or offset.

### Keeping them current

Commit the generated files and run `composer flyo:types -- --check` in CI: it writes nothing and
exits `6` as soon as the schema has moved on.

Files are marked `@generated by flyo/nitro-php`; regenerating replaces them and removes ones that
are no longer produced. Only the target and its kind directories are cleaned, and hand-written
files in them are never touched. Run `vendor/bin/flyo-generate-types --help` for all options.

### Migrating from `flyo-generate-blocks`

`vendor/bin/flyo-generate-blocks` is deprecated and will be removed in the next major release. It
keeps generating exactly what it did, so an existing setup and its `--check` keep passing, and it
prints a deprecation warning on every run. To move over, point the new command at a root namespace
instead of the blocks namespace, and update your imports: the class names do not change.

```diff
- "flyo:types": "vendor/bin/flyo-generate-blocks <source> App/Blocks app/Blocks"
+ "flyo:types": "vendor/bin/flyo-generate-types <source> App/Flyo app/Flyo"
```

```diff
- /** @var \App\Blocks\BlockHero $block */
+ /** @var \App\Flyo\Blocks\BlockHero $block */
```

Then delete the old `app/Blocks` directory. See [UPGRADE.md](UPGRADE.md) for details.

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