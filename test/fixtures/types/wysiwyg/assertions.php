<?php

declare(strict_types=1);

namespace Fixture\Nodes;

use Fixture\Nodes\Wysiwyg\WysiwygNodeDivider;
use Fixture\Nodes\Wysiwyg\WysiwygNodeInfoBox;
use Fixture\Nodes\Wysiwyg\WysiwygNodePersonCard;

/**
 * Type assertions over the golden output in ./expected.
 *
 * Analysed by phpstan-generated.neon at level 9, never executed. Each function's declared return
 * type is the assertion: if a generated getter degrades to `mixed` or loses a nested level, the
 * declared type stops matching and CI fails.
 */

/** `attrs` is not required, so even a required field of the object behind it needs a `?->`. */
function personUniqueId(WysiwygNodePersonCard $node): ?string
{
    return $node->getAttrUser()?->unique_id;
}

function personEmail(WysiwygNodePersonCard $node): ?string
{
    return $node->getAttrUser()?->email;
}

/** A quoted identifier (`card-size`) still gets a getter, named after its words. */
function personCardSize(WysiwygNodePersonCard $node): string
{
    return match ($node->getAttrCardSize()?->value) {
        WysiwygNodePersonCard::ATTR_CARD_SIZE_COMPACT => 'card--compact',
        WysiwygNodePersonCard::ATTR_CARD_SIZE_WIDE => 'card--wide',
        null => '',
    };
}

/** @return int<0, 80>|null */
function personYears(WysiwygNodePersonCard $node): ?int
{
    return $node->getAttrYears();
}

/** Both `attrs` and `heading` are required, so the heading is never null. */
function infoBoxHeading(WysiwygNodeInfoBox $node): string
{
    return $node->getAttrHeading();
}

/** The element-level API comes from the runtime base class. */
function infoBox(WysiwygNodeInfoBox $node): string
{
    return $node->getTitle() . $node->getType() . $node->renderContent();
}

/**
 * The renderer a project hands to the parser: the parameter type is what makes the parser build
 * the generated class instead of passing its own node.
 *
 * @param array<mixed> $json
 */
function render(array $json): string
{
    return (new \Nadar\ProseMirror\Parser())
        ->addNode(WysiwygNodePersonCard::IDENTIFIER, fn (WysiwygNodePersonCard $node): string
            => '<h3>' . ($node->getAttrUser()->name ?? '') . '</h3>')
        ->toHtml($json);
}

function constants(): string
{
    return WysiwygNodePersonCard::IDENTIFIER . WysiwygNodePersonCard::TITLE
        . WysiwygNodeInfoBox::ATTR_TONE_WARNING . WysiwygNodeDivider::IDENTIFIER;
}

function fromArray(): WysiwygNodePersonCard
{
    return WysiwygNodePersonCard::from(['type' => 'person_card', 'attrs' => ['role' => 'Lead']]);
}
