<?php

declare(strict_types=1);

namespace Fixture\Site;

use Fixture\Site\Blocks\BlockTeaser;
use Fixture\Site\Containers\ContainerFooter;
use Fixture\Site\Containers\ContainerItem;
use Fixture\Site\Containers\ContainerMain;
use Fixture\Site\Entities\EntityArticle;
use Fixture\Site\Entities\EntityEvent;

/**
 * Type assertions over the golden output in ./expected, one sub-namespace per kind.
 *
 * Analysed by phpstan-generated.neon at level 9, never executed. Each function's declared return
 * type is the assertion: if a generated shape degrades to `mixed` or loses a nested level, the
 * declared type stops matching and CI fails.
 */

// -- blocks ----------------------------------------------------------------------------------

/** `content` is required and `title` is required and not nullable, so neither needs a `?->`. */
function blockTitle(BlockTeaser $block): string
{
    return $block->getContent()->title;
}

function blockImageSource(BlockTeaser $block): ?string
{
    return $block->getContent()->image?->source;
}

function blockConfigDark(BlockTeaser $block): ?bool
{
    return $block->getConfig()->dark;
}

function blockFirstItemSlug(BlockTeaser $block): ?string
{
    return $block->getItems()[0]->link?->entity_slug;
}

function blockConstants(): string
{
    return BlockTeaser::IDENTIFIER . BlockTeaser::COMPONENT . implode(',', BlockTeaser::SLOTS);
}

// -- entities --------------------------------------------------------------------------------

/** The generic entity schema does not require `model`, so it stays nullable. */
function articleHeadline(EntityArticle $entity): ?string
{
    return $entity->getModel()?->headline;
}

function articlePublishedAt(EntityArticle $entity): ?int
{
    return $entity->getModel()?->published_at;
}

function articleImageId(EntityArticle $entity): int|float|null
{
    return $entity->getModel()?->image?->id;
}

function articleHtml(EntityArticle $entity): ?string
{
    return $entity->getModel()?->content?->html;
}

/** An option's `value` keeps its enum, so a match over it can be exhaustive. */
function articleCategory(EntityArticle $entity): string
{
    return match ($entity->getModel()?->category?->value) {
        'news' => 'News',
        'report' => 'Report',
        null => '',
    };
}

/** @return list<string> */
function articleTopicSlugs(EntityArticle $entity): array
{
    $slugs = [];
    foreach ($entity->getModel()?->tags?->topics ?? [] as $topic) {
        $slugs[] = (string) $topic->slug;
    }

    return $slugs;
}

function articleFirstEventUniqueId(EntityArticle $entity): ?string
{
    return ($entity->getModel()?->events ?? [])[0]->unique_id;
}

function eventCity(EntityEvent $entity): ?string
{
    return $entity->getModel()?->venue?->city;
}

function eventSoldOut(EntityEvent $entity): ?bool
{
    return $entity->getModel()?->sold_out;
}

/** Everything that is the same for every entity type keeps the SDK's own types. */
function entityInheritedGetters(EntityArticle $entity): ?\Flyo\Model\EntityInterface
{
    return $entity->getEntity();
}

/** A typed entity must be accepted anywhere the generic model is expected. */
function acceptedAsGenericEntity(EntityArticle $entity): \Flyo\Model\Entity
{
    return $entity;
}

/** The annotation-at-the-boundary pattern the README documents must type-check. */
function entityAnnotatedAtTheBoundary(\Flyo\Model\Entity $generic): ?string
{
    /** @var EntityArticle $entity */
    $entity = $generic;

    return $entity->getModel()?->headline;
}

// -- containers ------------------------------------------------------------------------------

/** `items` is required, so the list is never null, and each entry is the generated item class. */
function mainFirstItem(ContainerMain $container): ContainerItem
{
    return $container->getItems()[0];
}

function mainLabels(ContainerMain $container): string
{
    $labels = '';
    foreach ($container->getItems() as $item) {
        $labels .= (string) $item->getLabel();
    }

    return $labels;
}

/** Page properties are an array shape: the SDK builds that map with settype($data, 'array'). */
function itemIcon(ContainerItem $item): ?string
{
    return $item->getProperties()['icon'];
}

function itemHighlight(ContainerItem $item): ?bool
{
    return $item->getProperties()['highlight'];
}

/** Below the top level the values are decoded JSON again, so a nested object is an object. */
function itemTeaserImage(ContainerItem $item): ?string
{
    return $item->getProperties()['teaser-image']?->source;
}

/** Children are items too, all the way down the tree. */
function itemDepth(ContainerItem $item): int
{
    $depth = 0;
    foreach ($item->getChildren() as $child) {
        $depth = max($depth, 1 + itemDepth($child));
    }

    return $depth;
}

/** The rest of the item keeps the SDK's own types. */
function itemHref(ContainerItem $item): ?string
{
    return $item->getHref();
}

function containerIdentifiers(): string
{
    return ContainerMain::IDENTIFIER . ContainerFooter::IDENTIFIER;
}

/** A typed container must be accepted anywhere the generic model is expected. */
function acceptedAsGenericContainer(ContainerFooter $container): \Flyo\Model\ConfigResponseContainersValue
{
    return $container;
}

function itemAcceptedAsGenericPage(ContainerItem $item): \Flyo\Model\ContainerPage
{
    return $item;
}

/** And the lookup the README documents, annotated once at the boundary. */
function containerFromConfig(\Flyo\Model\ConfigResponse $config): ?string
{
    /** @var ContainerMain|null $main */
    $main = ($config->getContainers() ?? [])[ContainerMain::IDENTIFIER] ?? null;

    return $main?->getItems()[0]->getProperties()['icon'];
}
