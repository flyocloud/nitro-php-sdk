<?php

declare(strict_types=1);

namespace Flyo\Wysiwyg;

use Nadar\ProseMirror\Node;
use Nadar\ProseMirror\NodeInterface;

/**
 * One custom element of the organisation's WYSIWYG library, as a node of the TipTap json of a
 * WYSIWYG field.
 *
 * The base of the classes `flyo-generate-types` writes into `<namespace>\Wysiwyg`: each adds the
 * element's IDENTIFIER and TITLE and one typed `getAttr<Name>()` per attribute. Unlike the other
 * generated classes these exist at runtime. They implement nadar/prosemirror-json-parser's
 * NodeInterface, so a renderer whose parameter is typed with one is handed an instance by the
 * parser (and by flyo/nitro-php-bridge's Wysiwyg, which extends it):
 *
 * ```php
 * Wysiwyg::render($json, fn (Wysiwyg $parser) => $parser
 *     ->addNode(WysiwygNodePersonCard::IDENTIFIER, fn (WysiwygNodePersonCard $node) =>
 *         '<h3>' . $node->getAttrUser()?->name . '</h3>' . $node->renderContent()));
 * ```
 */
class WysiwygNode implements NodeInterface
{
    /** The node name of the element, which is the `type` of the node in the json. */
    public const IDENTIFIER = '';

    /** The label of the element in Flyo. */
    public const TITLE = '';

    /**
     * @param (\Closure(): string)|null $renderContent renders the nodes this one wraps
     */
    final public function __construct(
        private readonly \stdClass $node,
        private readonly ?\Closure $renderContent = null,
    ) {
    }

    /**
     * Builds the node from the node the parser is rendering, which renders the wrapped content.
     *
     * @throws \InvalidArgumentException when the node is another element
     */
    public static function fromNode(Node $node): static
    {
        return static::from(
            ['type' => $node->getType(), 'attrs' => $node->getAttrs(), 'content' => $node->getContent()],
            static fn (): string => $node->renderContent(),
        );
    }

    /**
     * Builds the node from its decoded json, whether it was decoded to arrays or to objects.
     *
     * Attribute values always come out as \stdClass objects, the same as everywhere else in the
     * SDK, so the getters read the same whichever way the json was decoded.
     *
     * @param array<array-key, mixed>|object $node
     * @param (\Closure(): string)|null $renderContent renders the nodes this one wraps
     * @throws \InvalidArgumentException when the node is not an object, or is another element
     */
    public static function from(array|object $node, ?\Closure $renderContent = null): static
    {
        $decoded = json_decode(json_encode($node, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);

        if (!$decoded instanceof \stdClass) {
            throw new \InvalidArgumentException('A WYSIWYG node must be a json object.');
        }

        $type = $decoded->type ?? null;

        if (static::IDENTIFIER !== '' && $type !== static::IDENTIFIER) {
            throw new \InvalidArgumentException(sprintf(
                '%s describes nodes of type "%s", not "%s".',
                static::class,
                static::IDENTIFIER,
                is_scalar($type) ? (string) $type : get_debug_type($type),
            ));
        }

        return new static($decoded, $renderContent);
    }

    /**
     * The node name, which is IDENTIFIER for a generated class.
     */
    public function getType(): string
    {
        $type = $this->node->type ?? null;

        return is_string($type) ? $type : '';
    }

    /**
     * The label of the element in Flyo.
     */
    public function getTitle(): string
    {
        return static::TITLE;
    }

    /**
     * The values the editor filled in, keyed by the identifier of the attribute. Prefer the typed
     * `getAttr<Name>()` getters of a generated class.
     */
    public function getAttrs(): \stdClass
    {
        $attrs = $this->node->attrs ?? null;

        // A node that was never filled in may carry an empty list instead of an empty map.
        return $attrs instanceof \stdClass ? $attrs : new \stdClass();
    }

    /**
     * The rich text nodes the element wraps, in TipTap notation. Empty for an element without
     * content.
     *
     * @return list<mixed>
     */
    public function getContent(): array
    {
        $content = $this->node->content ?? null;

        return is_array($content) ? array_values($content) : [];
    }

    /**
     * The rich text the element wraps, rendered as HTML, or `''` when nothing was given to render
     * it with.
     */
    public function renderContent(): string
    {
        return $this->renderContent === null ? '' : ($this->renderContent)();
    }

    /**
     * One attribute value, or null when the node does not carry it.
     */
    protected function attr(string $name): mixed
    {
        $attrs = $this->getAttrs();

        return property_exists($attrs, $name) ? $attrs->{$name} : null;
    }
}
