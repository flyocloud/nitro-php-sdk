# Upgrade

## From 2.3 to 2.4

Generated against OpenAPI spec `2.35` (was `2.30`).

### `sitemap()` returns its own model

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

// 2.4
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

### Draft links on `Entity`

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
