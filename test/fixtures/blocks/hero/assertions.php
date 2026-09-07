<?php

declare(strict_types=1);

namespace Fixture\Hero;

/**
 * Type assertions over the golden output in ./expected.
 *
 * Analysed by phpstan-generated.neon at level 9, never executed. Each function's declared return
 * type is the assertion: if a generated shape degrades to `mixed` or `object`, or loses a nested
 * level, the declared type stops matching and CI fails. This is what proves the generator's output
 * is actually useful to a consumer, rather than merely well-formed.
 */

function title(BlockHero $block): ?string
{
    return $block->getContent()?->title;
}

function text(BlockHero $block): ?string
{
    return $block->getContent()?->text;
}

function imageSource(BlockHero $block): ?string
{
    return $block->getContent()?->image?->source;
}

function imageCaption(BlockHero $block): ?string
{
    return $block->getContent()?->image?->caption;
}

/** `number` keeps both possibilities, because json_decode returns whichever the payload held. */
function imageId(BlockHero $block): int|float|null
{
    return $block->getContent()?->image?->id;
}

function linkHref(BlockHero $block): ?string
{
    return $block->getContent()?->link?->href;
}

/** An open map stays a \stdClass: its keys vary by link type. */
function linkExtras(BlockHero $block): ?\stdClass
{
    return $block->getContent()?->link?->extras;
}

function contentEmpty(BlockHero $block): ?bool
{
    return $block->getContent()?->_empty;
}

function configEmpty(BlockHero $block): ?bool
{
    return $block->getConfig()?->_empty;
}

function firstItemSlug(BlockHero $block): ?string
{
    return ($block->getItems() ?? [])[0]->link?->entity_slug;
}

/** The "or false when unset" idiom, from oneOf [integer, boolean]. */
function firstItemTypeId(BlockHero $block): int|bool|null
{
    return ($block->getItems() ?? [])[0]->link?->entity_type_id;
}

function firstItemRoutes(BlockHero $block): ?\stdClass
{
    return ($block->getItems() ?? [])[0]->link?->routes;
}

function itemsAreIterable(BlockHero $block): int
{
    $count = 0;
    foreach ($block->getItems() ?? [] as $item) {
        $count += $item->link === null ? 0 : 1;
    }

    return $count;
}

/** Inherited getters keep the parent's types; the generator does not narrow them. */
function inheritedGetters(BlockHero $block): string
{
    return (string) $block->getIdentifier()
        . (string) $block->getUid()
        . (string) $block->getComponent();
}

/** Slots are already real models on the parent, so this is the parent's exact type. */
function slotChildren(BlockHero $block): int
{
    $slots = $block->getSlots() ?? [];
    $slot = $slots['main'] ?? null;

    return $slot === null ? 0 : count($slot->getContent() ?? []);
}

function constants(): string
{
    return BlockHero::IDENTIFIER . BlockHero::COMPONENT;
}

/** A typed block must be accepted anywhere the generic model is expected. */
function acceptedAsGenericBlock(BlockHero $block): \Flyo\Model\Block
{
    return $block;
}

/** And the annotation-at-the-boundary pattern the README documents must type-check. */
function annotatedAtTheBoundary(\Flyo\Model\Block $generic): ?string
{
    /** @var BlockHero $block */
    $block = $generic;

    return $block->getContent()?->title;
}
