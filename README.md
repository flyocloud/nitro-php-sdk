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
├── Entities/      EntityArticle.php, ...                  one per entity type, narrowing \Flyo\Model\Entity
└── Wysiwyg/       WysiwygNodePersonCard.php, ...          one per custom WYSIWYG element
```

A directory only appears once the document has something for it, and more kinds will follow as
the API gains them. A schema type this version does not know yet is reported and skipped.

### Wire it into composer.json

Generating is something you repeat on every schema change, so keep the arguments in your project's
`composer.json` instead of retyping them:

```json
{
    "scripts": {
        "flyo:types": "[ ! -f .env ] || . ./.env; vendor/bin/flyo-generate-types \"https://api.flyo.cloud/nitro/v1/openapi/schemas?token=${FLYO_TOKEN:?set FLYO_TOKEN in .env}\" App/Flyo app/Flyo"
    }
}
```

```sh
# .env
FLYO_TOKEN=your-nitro-token
```

```sh
composer flyo:types              # regenerate
composer flyo:types -- --check   # exit 6 when the committed classes are out of date
```

Composer appends anything after `--` to the script, so one entry covers both the write and the CI
check. The token stays out of `composer.json`: Composer does not read `.env` itself, so the script
sources it when there is one, and `${FLYO_TOKEN:?...}` stops with a clear message when the token is
missing. In CI, where there is usually no `.env`, set `FLYO_TOKEN` as a secret instead. The same
pattern works for any source that takes a token, such as an integration's components definition:
`"https://api.flyo.cloud/integration/organisation/<id>/components-definition/<key>?token=${FLYO_TOKEN:?set FLYO_TOKEN in .env}"`.

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
| `<namespace>` | PSR-4 root namespace; the kinds become `<namespace>\Blocks`, `<namespace>\Containers`, `<namespace>\Entities`, `<namespace>\Wysiwyg` |
| `<target>` | the directory that namespace maps to |

### Where the classes go

`<namespace>` and `<target>` have to agree with the PSR-4 map in your `composer.json`, otherwise
the classes are written but never autoloaded. The common setups:

| project | `autoload.psr-4` | `<namespace>` | `<target>` |
|---------|------------------|---------------|------------|
| Laravel | `"App\\": "app/"` | `App/Flyo` | `app/Flyo` |
| Symfony | `"App\\": "src/"` | `App/Flyo` | `src/Flyo` |
| Own package or plain PSR-4 | `"Acme\\Site\\": "src/"` | `Acme/Site/Flyo` | `src/Flyo` |
| Yii 2 (basic template, add `--lowercase`) | `@app` alias, `app\` = project root | `app/flyo` | `flyo` |

The rule behind the table: take the prefix and its directory from `autoload.psr-4`, then append the
same trailing segment to both. The target directory is created if it does not exist.

#### Lower-case directories (Yii 2)

Some frameworks map lower-case directories to lower-case namespaces. In Yii 2's basic template the
root namespace `app` is the project root itself, so there is no `app/` directory:
`app\flyo\blocks\BlockHero` lives in `flyo/blocks/BlockHero.php`. Pass `--lowercase` and the kind
directories and sub-namespaces become `blocks`, `containers`, `entities` and `wysiwyg`; the class
names keep their case:

```sh
vendor/bin/flyo-generate-types <source> app/flyo flyo --lowercase
```

```
flyo/
├── blocks/        BlockHero.php        namespace app\flyo\blocks
├── containers/    ContainerMain.php    namespace app\flyo\containers
├── entities/      EntityArticle.php    namespace app\flyo\entities
└── wysiwyg/       WysiwygNodeBox.php   namespace app\flyo\wysiwyg
```

Use the same flag for every run, `--check` included: switching it changes every file.

Typed schemas only exist on the **authenticated** `/nitro/v1/openapi/schemas` endpoint. The public
`/nitro/v1/openapi` has none. Pass the token through `FLYO_TOKEN` (or `FLYO_API_KEY`) rather than
`--token`: an argument is visible to anyone who can run `ps`.

### Using them

The generated classes are documentation-only, except the WYSIWYG elements (see below): nothing
instantiates them, and at runtime the API still returns the plain SDK models. You swap the
annotation at the framework boundary and everything below it becomes typed.

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

Each block class also carries `IDENTIFIER`, `COMPONENT` and `SLOTS` constants, plus one constant
per option of every content and config field that has a fixed set of them, named
`CONTENT_<FIELD>_<OPTION>` and `CONFIG_<FIELD>_<OPTION>`:

```php
use App\Flyo\Blocks\BlockHero;

/** @var BlockHero $block */
$modifier = match ($block->getContent()->size->value) {
    BlockHero::CONTENT_SIZE_SMALL => 'hero--small',       // 'small'
    BlockHero::CONTENT_SIZE_MEDIUM => 'hero--medium',     // 'medium'
    BlockHero::CONTENT_SIZE_LARGE => 'hero--large',       // 'large'
    null => '',
};

if ($block->getConfig()->theme === BlockHero::CONFIG_THEME_DARK) {
    // ...
}
```

A select field's constants are named after the field, not after its `value`. Names are upper snake
case: the field `background-color` with the option `darkBlue` gives
`CONTENT_BACKGROUND_COLOR_DARK_BLUE`, whose value is `'darkBlue'`, the option exactly as Flyo lists
it. PHPStan knows each constant's literal value, so it reports a `match` that misses an option. If
two options would get the same name, neither gets a constant and the generator prints a warning, so
a name never quietly starts to mean a different option. Only `flyo-generate-types` generates these
constants; the deprecated `flyo-generate-blocks` does not.

A block whose items are a **collection** (paged, sorted and filtered through
`/blocks/collection/{key}`), or that aggregates facets, gets a second class, `<Block>Collection`,
which its `getCollection()` returns. It narrows the getters of `\Flyo\Model\BlockCollection` to what
the block definition allows, and lists the fields in `SORTABLE`, `FILTERABLE` and `FACETS`:

```php
use App\Flyo\Blocks\BlockEventList;
use App\Flyo\Blocks\BlockEventListCollection;

/** @var BlockEventList $block */
$collection = $block->getCollection();              // BlockEventListCollection

$collection->getSortable();                         // list<'position'|'title'|'date'>
foreach ($collection->getFacets()['category'] as $facet) {   // a typo is an unknown offset
    echo $facet->getLabel(), ' (', $facet->getCount(), ')';
}

// the constants need no instance: check a request before it reaches the API
$sort = in_array($requested, BlockEventListCollection::SORTABLE, true) ? $requested : 'position';

/** @var BlockEventList $page */                   // the endpoint answers the same block
$page = (new \Flyo\Api\BlocksApi())->blockCollection($collection->getKey(), 2, $sort);
```

Unlike content, config and items, the collection is an SDK model at runtime, and so are its links
and the facet values. A facet's `getValue()` is always a string, also for a number or a date field,
because the SDK model declares it so. A getter is only narrowed where the document says more than
the model does, so the class gets more precise as the server pins more of a collection (its page
size, or that a collection always has a sort and links). A block that is neither a collection nor
aggregates facets gets no collection class. The deprecated `flyo-generate-blocks` generates none.

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

**WYSIWYG elements** are the custom elements of your organisation's WYSIWYG library, the nodes you
render with `addNode()` of [nadar/prosemirror-json-parser](https://github.com/nadar/prosemirror-json-parser),
directly or through `Wysiwyg::render()` of [flyo/nitro-php-bridge](https://github.com/flyocloud/nitro-php-bridge).
Each class carries the element's node name and label and one getter per attribute, so the
attributes are the contract a renderer is written against:

```php
use App\Flyo\Wysiwyg\WysiwygNodePersonCard;
use Flyo\Bridge\Wysiwyg;

echo Wysiwyg::render($block->getContent()->text->json, fn (Wysiwyg $parser) => $parser
    ->addNode(WysiwygNodePersonCard::IDENTIFIER, fn (WysiwygNodePersonCard $node) =>
        '<h3>' . $node->getAttrUser()?->name . '</h3>'     // string|null
        . '<p>' . $node->getTitle() . '</p>'               // 'Person Card', the label in Flyo
        . $node->renderContent()));                        // the rich text it wraps, if any
```

These are the only generated classes that exist at runtime, because PHP checks a closure's
parameter type. They extend `\Flyo\Wysiwyg\WysiwygNode`, which implements the parser's
`NodeInterface`: when a renderer's parameter is typed with one, `addNode()` hands it an instance
instead of the parser's own node. A renderer typed with a class of another element throws as soon as
that node is rendered. `WysiwygNodePersonCard::from($node)` builds one from any decoded node,
arrays or objects.

A getter is named `getAttr` plus the attribute's identifier in studly case (`card-size` gives
`getAttrCardSize()`), and an attribute that is not always there returns null. Options get constants
like a block's, prefixed `ATTR_`: `WysiwygNodePersonCard::ATTR_CARD_SIZE_WIDE`. Two attributes that
would share a getter get none, and the generator prints a warning.

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