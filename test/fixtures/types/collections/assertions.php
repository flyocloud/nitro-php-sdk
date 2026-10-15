<?php

declare(strict_types=1);

namespace Fixture\Collections;

use Fixture\Collections\Blocks\BlockEventList;
use Fixture\Collections\Blocks\BlockEventListCollection;
use Fixture\Collections\Blocks\BlockNewsFeed;
use Fixture\Collections\Blocks\BlockTagCloud;
use Fixture\Collections\Blocks\BlockText;

/**
 * Type assertions over the golden output in ./expected.
 *
 * Analysed by phpstan-generated.neon at level 9, never executed. Each function's declared return
 * type is the assertion: if a narrowed getter of a collection falls back to the type of the SDK
 * model, the declared type stops matching and CI fails.
 */

// -- a collection with its sort, links and page size pinned -----------------------------------

function eventListIsACollection(BlockEventList $block): true
{
    return $block->getCollection()->getEnabled();
}

/** @return 12 */
function eventListPerPage(BlockEventList $block): int
{
    return $block->getCollection()->getPerPage();
}

/** A collection always has a sort and links, so neither needs a null check. */
function eventListSort(BlockEventList $block): string
{
    return $block->getCollection()->getSort();
}

function eventListNextPage(BlockEventList $block): ?string
{
    return $block->getCollection()->getLinks()->getNext();
}

/** @return list<'position'|'title'|'date'|'price'> */
function eventListSortable(BlockEventList $block): array
{
    return $block->getCollection()->getSortable();
}

/** A facet is addressed by the name of its field, and each value is the SDK model. */
function eventListCategoryLabel(BlockEventList $block): ?string
{
    return $block->getCollection()->getFacets()['category'][0]->getLabel();
}

function eventListPriceCount(BlockEventList $block): int
{
    return $block->getCollection()->getFacets()['price'][0]->getCount();
}

/** The constants work without an instance, for checking a request before it reaches the API. */
function eventListAcceptsSort(string $field): bool
{
    return in_array($field, BlockEventListCollection::SORTABLE, true);
}

// -- a collection of a server that pins nothing beyond the fields --------------------------

function newsFeedSort(BlockNewsFeed $block): ?string
{
    return $block->getCollection()->getSort();
}

function newsFeedPerPage(BlockNewsFeed $block): int
{
    return $block->getCollection()->getPerPage();
}

/** @return array{topic: list<\Flyo\Model\BlockCollectionFacetsValue>} */
function newsFeedFacets(BlockNewsFeed $block): array
{
    return $block->getCollection()->getFacets();
}

// -- no collection, but facets -------------------------------------------------------------

function tagCloudIsNoCollection(BlockTagCloud $block): false
{
    return $block->getCollection()->getEnabled();
}

/** @return array{} */
function tagCloudSortable(BlockTagCloud $block): array
{
    return $block->getCollection()->getSortable();
}

// -- neither: the generic SDK model ------------------------------------------------------------

function textCollection(BlockText $block): \Flyo\Model\BlockCollection
{
    return $block->getCollection();
}
