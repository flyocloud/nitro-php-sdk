<?php

declare(strict_types=1);

namespace Fixture\Enums;

use Fixture\Enums\Blocks\BlockCard;

/**
 * Type assertions over the enum constants in ./expected.
 *
 * Analysed by phpstan-generated.neon at level 9, never executed. A `match` over a field is only
 * exhaustive when its constants cover exactly the literals of the field's type, and a strict
 * comparison against a value the field cannot hold is reported, so a constant that drifted from
 * its field fails the build here.
 */

/** A select field's options are named after the field, without its `value`. */
function contentSize(BlockCard $block): string
{
    return match ($block->getContent()->size->value) {
        BlockCard::CONTENT_SIZE_SMALL => 'S',
        BlockCard::CONTENT_SIZE_MEDIUM => 'M',
        BlockCard::CONTENT_SIZE_LARGE => 'L',
        null => '',
    };
}

/** The same field name in config gets constants of its own, for its own options. */
function configSize(BlockCard $block): string
{
    return match ($block->getConfig()->size->value) {
        BlockCard::CONFIG_SIZE_SMALL => 'S',
        BlockCard::CONFIG_SIZE_LARGE => 'L',
        null => '',
    };
}

/** A field with an `enum` of its own. */
function contentAlign(BlockCard $block): string
{
    return match ($block->getContent()->align) {
        BlockCard::CONTENT_ALIGN_LEFT => 'text-left',
        BlockCard::CONTENT_ALIGN_CENTER => 'text-center',
        BlockCard::CONTENT_ALIGN_RIGHT => 'text-right',
        null => '',
    };
}

/** Kebab-case field and camelCase option alike end up as upper snake case. */
function contentIsDarkBlue(BlockCard $block): bool
{
    $value = $block->getContent()->{'background-color'}?->value;

    return $value === BlockCard::CONTENT_BACKGROUND_COLOR_DARK_BLUE;
}

/** A numeric option keeps its string value, the literal the field is typed with. */
function contentColumns(BlockCard $block): int
{
    return match ($block->getContent()->columns) {
        BlockCard::CONTENT_COLUMNS_1 => 1,
        BlockCard::CONTENT_COLUMNS_2 => 2,
        BlockCard::CONTENT_COLUMNS_3 => 3,
        null => 0,
    };
}

/** A constant's type is its literal, not just `string`. */
function constantIsALiteral(): string
{
    $small = BlockCard::CONTENT_SIZE_SMALL;

    return match ($small) {
        'small' => $small,
    };
}
