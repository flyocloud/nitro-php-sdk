# Upgrade

## From 3.5 to 3.6

Nothing in `lib/Api` or `lib/Model` changed and no runtime behaviour changed. The block generator
grew into a generator for every typed schema, under a new name.

### New: `vendor/bin/flyo-generate-types`

Takes the same arguments and options as `flyo-generate-blocks`, but generates every kind of typed
schema the document declares, each into a sub-namespace and directory of its own:

| kind | directory | extends | narrows |
|------|-----------|---------|---------|
| blocks | `<target>/Blocks` | `\Flyo\Model\Block` | `getContent()`, `getConfig()`, `getItems()` |
| containers | `<target>/Containers` | `\Flyo\Model\ConfigResponseContainersValue` | `getItems()` |
| container items | `<target>/Containers` | `\Flyo\Model\ContainerPage` | `getProperties()`, `getChildren()` |
| entities | `<target>/Entities` | `\Flyo\Model\Entity` | `getModel()` |

So `<namespace>` is now a root: with `App/Flyo` and `app/Flyo`, blocks land in
`App\Flyo\Blocks`, containers in `App\Flyo\Containers` and entities in `App\Flyo\Entities`. Like
the block classes, all of them are documentation-only; see the "Typed Schemas" section of the
README for how each one is used.

A schema carrying an `x-schema-type` this version cannot generate yet is skipped with a warning,
so a kind added to the API later never disappears silently.

### Deprecated: `vendor/bin/flyo-generate-blocks`

It keeps working until the next major release and keeps generating byte-identical files, so a
committed target and its `--check` keep passing after the upgrade. The only difference is a
deprecation warning on stderr, printed on every run, `--quiet` included, like every warning.

To migrate, switch the script to the new command with a root namespace. The class names are the
same, only their namespace gains the kind:

```diff
  "scripts": {
-     "flyo:types": "vendor/bin/flyo-generate-blocks <source> App/Blocks app/Blocks"
+     "flyo:types": "vendor/bin/flyo-generate-types <source> App/Flyo app/Flyo"
  }
```

```php
// 3.5
/** @var \App\Blocks\BlockHero $block */

// 3.6
/** @var \App\Flyo\Blocks\BlockHero $block */
```

Then delete the old target directory. If you would rather keep the directory, pointing the new
command at it works too: blocks generated there by the old command carry the `@generated` marker
and are cleaned up, and the new command warns when the namespace you pass already ends in
`Blocks`, because the blocks would then land in `...\Blocks\Blocks`.

### Moved: the generator classes

The generator itself moved from `Flyo\Blocks\Generator` to `Flyo\Generator`. Those classes are
the internals of the two binaries and were never documented. Of the old namespace, only
`\Flyo\Blocks\Generator\Cli::main()` remains, deprecated, as the entry point of
`flyo-generate-blocks`. If you called any other class of it directly, call
`\Flyo\Generator\Cli::main()` or the binary instead.

## From 3.3 to 3.4

Generated against OpenAPI spec `2.45` (was `2.44`).

### The 404 body of the entity endpoints is now `EntityNotFoundResponse`

`EntitiesApi::entityByUniqueid()` and `EntitiesApi::entityBySlug()` both answer a 404 with the same
body, so the schema was renamed from `entityByUniqueid_404_response` to `entityNotFoundResponse`.
The model is unchanged field for field — `name`, `message`, `status`, `entity_unique_id`, `href` —
only the class name moved:

```php
// 3.3
/** @var \Flyo\Model\Entity|\Flyo\Model\EntityByUniqueid404Response $result */

// 3.4
/** @var \Flyo\Model\Entity|\Flyo\Model\EntityNotFoundResponse $result */
$result = (new \Flyo\Api\EntitiesApi())->entityByUniqueid($uniqueidOrDraftToken);

if ($result instanceof \Flyo\Model\EntityNotFoundResponse) {
    $href = $result->getHref(); // where it lives now, or null
}
```

`\Flyo\Model\EntityByUniqueid404Response` still exists and is now a deprecated subclass of
`EntityNotFoundResponse`, so nothing fails to autoload and anything you built against the old
class still passes where the new one is expected. It will be removed in a future major release.

One thing to fix while upgrading: the endpoints now *return* an `EntityNotFoundResponse`, which is
the parent class, so `instanceof \Flyo\Model\EntityByUniqueid404Response` checks and parameter
types hinting the old class no longer match the returned object. Replace those with
`EntityNotFoundResponse`. Reading the response — getters, array access, `json_encode()` — is
unaffected.

## From 3.2 to 3.3

Purely additive. Nothing in `lib/Api` or `lib/Model` changed, no runtime behaviour changed, and
no existing code needs touching.

### New: `vendor/bin/flyo-generate-blocks`

The package now ships a binary that generates one typed PHP class per Nitro block from your
integration's OpenAPI document, so `$block->getContent()->image->source` is understood by your IDE
and PHPStan instead of being a bare `\stdClass`. See the "Typed Blocks" section of the README.

The generated classes extend `\Flyo\Model\Block` and are documentation-only: nothing instantiates
them, and a block returned by `PagesApi` is still a plain `\Flyo\Model\Block` at runtime. Adopt
them by changing an annotation at your framework boundary:

```php
// before
/** @var \Flyo\Model\Block $block */

// after
/** @var \App\Blocks\BlockHero $block */
```

Typed block schemas come from the authenticated `/nitro/v1/openapi/schemas` endpoint; the public
`/nitro/v1/openapi` does not carry them.

## From 2.3 to 3.0

Generated against OpenAPI spec `2.35` (was `2.30`).

This is a major release: the sitemap endpoint returns a different model with fewer fields,
which is a breaking change for anyone reading presentation data off sitemap items. Everything
else in this release is additive.

### `sitemap()` returns its own model (breaking)

`SitemapApi::sitemap()` now returns `\Flyo\Model\SitemapinterfaceInner[]` instead of
`\Flyo\Model\EntityinterfaceInner[]`. The sitemap response has been reduced to the fields
a sitemap actually needs, so the new model only carries `entity_unique_id`, `updated_at`
and `href`, plus the deprecated `entity_type`, `entity_slug` and `routes`.

These getters no longer exist on sitemap items:

| Removed from sitemap items | Still available on |
| --- | --- |
| `getEntityTitle()` | `SearchApi::search()`, `EntitiesApi` |
| `getEntityTeaser()` | `SearchApi::search()`, `EntitiesApi` |
| `getEntityImage()` | `SearchApi::search()`, `EntitiesApi` |
| `getEntityTimeStart()` | `SearchApi::search()`, `EntitiesApi` |
| `getEntityTypeId()` | `SearchApi::search()`, `EntitiesApi` |

```php
// 2.3
/** @var \Flyo\Model\EntityinterfaceInner[] $items */
$items = (new \Flyo\Api\SitemapApi())->sitemap();
$items[0]->getEntityTitle();

// 3.0
/** @var \Flyo\Model\SitemapinterfaceInner[] $items */
$items = (new \Flyo\Api\SitemapApi())->sitemap();
$items[0]->getHref();
$items[0]->getUpdatedAt();       // use as <lastmod>
$items[0]->getEntityUniqueId();  // correlate with the entities endpoint
```

If you type-hinted the sitemap result against `\Flyo\Model\EntityinterfaceInner`, replace it
with `\Flyo\Model\SitemapinterfaceInner`. `EntityinterfaceInner` itself is unchanged and is
still what `SearchApi::search()` returns.

Two further behaviour notes from the spec: entries without a resolvable URL are now omitted
from the sitemap, and `updated_at` on a Nitro page only moves when the delivered content
actually changed (a rebuild producing identical output does not bump it).

Prefer `getHref()` over `getEntityType()`, `getEntitySlug()` and `getRoutes()` — those three
are marked deprecated and only remain for consumers that build URLs themselves.

### Draft links on `Entity` (new)

`\Flyo\Model\Entity` gained `is_draft` (`bool`) and `draft_expires_at` (`float|null`). A draft
link is an expiring snapshot of an entity that is still offline in Flyo, addressed by a token
that takes the place of the entity's unique id or slug, so it is resolved through the existing
`EntitiesApi::entityByUniqueid()` and `EntitiesApi::entityBySlug()` calls.

```php
$entity = (new \Flyo\Api\EntitiesApi())->entityByUniqueid($uniqueidOrDraftToken);

if ($entity->getIsDraft()) {
    // not the live page - render a hint, and optionally show when the link dies
    $expiresAt = $entity->getDraftExpiresAt(); // Unix timestamp
}
```

`is_draft` is `false` for every regular request. Two things to watch out for: a draft token
does not look like a normal slug or unique id, so a router that validates those parameters
against a pattern has to let it through; and `typeId` does not apply to a draft token, so omit
that parameter when resolving one.

### Documentation-only changes

`SearchApi::search()` is unchanged in signature, but the spec now documents its matching
behaviour: partial-word and diacritic-insensitive matching (`t shirt` finds `T-Shirt`,
`Zurich`/`Zuerich` find `Zürich`), a typo-tolerant second pass for words of at least four
characters, and how `score` weighs title over teaser and phrase matches over scattered words.

## From 2.2 to 2.3

Generated against OpenAPI spec `2.30` (was `2.28`).

### `routes` is a plain array again

The `Routes` model class introduced in 2.2 has been removed. `EntityInterface::getRoutes()`
and `EntityinterfaceInner::getRoutes()` return `array<string,mixed>|null` again, so all
route keys are accessible and no longer swallowed by the model.

```php
// 2.2
$entity->getRoutes()->getEmpty();

// 2.3
$routes = $entity->getRoutes();
$routes['_empty']; // bool: true when no route could be resolved
$routes['detail']; // string: "/foo-bar"
```

If you type-hinted against `\Flyo\Model\Routes`, replace it with `array`.
