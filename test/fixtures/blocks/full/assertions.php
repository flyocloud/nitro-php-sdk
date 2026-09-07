<?php

declare(strict_types=1);

namespace Fixture\Full;

/**
 * Type assertions over the golden output in ./expected, one per row of the type mapping table.
 *
 * Analysed by phpstan-generated.neon at level 9, never executed. The declared return types are the
 * assertions.
 */

/**
 * `content` is listed in the block schema's `required`, so the getter is NOT nullable -- and
 * neither is `required_string` inside it. This is the shape the whole nullability rule turns on.
 */
function requiredIsNotNullable(BlockCoverage $block): string
{
    return $block->getContent()->required_string;
}

function plainString(BlockCoverage $block): ?string
{
    return $block->getContent()->plain_string;
}

function anInteger(BlockCoverage $block): ?int
{
    return $block->getContent()->an_integer;
}

function boundedInteger(BlockCoverage $block): ?int
{
    return $block->getContent()->bounded_integer;
}

function aNumber(BlockCoverage $block): int|float|null
{
    return $block->getContent()->a_number;
}

function aBoolean(BlockCoverage $block): ?bool
{
    return $block->getContent()->a_boolean;
}

function explicitlyNullable(BlockCoverage $block): ?string
{
    return $block->getContent()->nullable_string;
}

function singleEnum(BlockCoverage $block): ?string
{
    return $block->getContent()->single_enum;
}

function multiEnum(BlockCoverage $block): ?string
{
    return $block->getContent()->multi_enum;
}

function enumWithoutType(BlockCoverage $block): ?string
{
    return $block->getContent()->enum_without_type;
}

function enumWithAQuote(BlockCoverage $block): ?string
{
    return $block->getContent()->enum_with_quote;
}

/** A literal union is enforced: comparing against a value outside it is reported. */
function enumIsAnActualUnion(BlockCoverage $block): bool
{
    return $block->getContent()->multi_enum === 'wide';
}

function deeplyNested(BlockCoverage $block): ?string
{
    return $block->getContent()->nested_object?->deep?->deeper;
}

function openMap(BlockCoverage $block): ?\stdClass
{
    return $block->getContent()->open_map;
}

function typedAdditionalProperties(BlockCoverage $block): ?\stdClass
{
    return $block->getContent()->string_map;
}

function bareObject(BlockCoverage $block): ?\stdClass
{
    return $block->getContent()->bare_object;
}

function objectListLabel(BlockCoverage $block): ?string
{
    return ($block->getContent()->object_list ?? [])[0]->label;
}

function objectListWeight(BlockCoverage $block): int|float|null
{
    return ($block->getContent()->object_list ?? [])[0]->weight;
}

function scalarList(BlockCoverage $block): int
{
    $total = 0;
    foreach ($block->getContent()->scalar_list ?? [] as $value) {
        $total += strlen($value);
    }

    return $total;
}

function openMapList(BlockCoverage $block): \stdClass
{
    return ($block->getContent()->open_map_list ?? [])[0];
}

function oneOfIntBool(BlockCoverage $block): bool|int|null
{
    return $block->getContent()->one_of_int_bool;
}

function oneOfStringBool(BlockCoverage $block): bool|string|null
{
    return $block->getContent()->one_of_string_bool;
}

function anyOfScalars(BlockCoverage $block): int|float|string|null
{
    return $block->getContent()->any_of_scalars;
}

/** allOf members merge, and each member's own `required` survives the merge. */
function allOfMergedRequired(BlockCoverage $block): ?string
{
    return $block->getContent()->all_of_merged?->from_first;
}

function allOfMergedOptional(BlockCoverage $block): ?int
{
    return $block->getContent()->all_of_merged?->from_second;
}

/** A $ref to another typed block resolves to that block's generated class... */
function refToOtherBlock(BlockCoverage $block): ?BlockOther
{
    return $block->getContent()->ref_to_other_block;
}

/** ...and is therefore usable as one. */
function refToOtherBlockIsUsable(BlockCoverage $block): ?string
{
    return $block->getContent()->ref_to_other_block?->getContent()?->headline;
}

/** A $ref to the generic block schema resolves to the real SDK model. */
function refToGenericBlock(BlockCoverage $block): ?\Flyo\Model\Block
{
    return $block->getContent()->ref_to_generic_block;
}

/** A $ref to an unmarked schema is inlined as a shape rather than named. */
function refToUnmarkedSchema(BlockCoverage $block): ?string
{
    return $block->getContent()->ref_to_unmarked?->name;
}

/** Keys that are not PHP identifiers are quoted in the shape and reachable at runtime. */
function hyphenatedKey(BlockCoverage $block): ?string
{
    return $block->getContent()->{'my-field'};
}

function keyWithSpacesAndParens(BlockCoverage $block): int|float|null
{
    return $block->getContent()->{'length (cm)'};
}

function itemTypeId(BlockCoverage $block): bool|int|null
{
    return ($block->getItems() ?? [])[0]->link?->entity_type_id;
}

function slots(BlockCoverage $block): int
{
    $slots = $block->getSlots() ?? [];
    $slot = $slots['main'] ?? null;

    return $slot === null ? 0 : count($slot->getContent() ?? []);
}

function declaredSlotsConstant(): string
{
    return implode(',', BlockCoverage::SLOTS);
}

/** The second and third blocks in the fixture must be usable too. */
function otherBlockHeadline(BlockOther $block): ?string
{
    return $block->getContent()?->headline;
}

function textBlockHtml(BlockText $block): ?string
{
    return $block->getContent()?->body?->html;
}

function textBlockJson(BlockText $block): ?\stdClass
{
    return $block->getContent()?->body?->json;
}
