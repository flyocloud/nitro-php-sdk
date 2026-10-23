<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * WYSIWYG elements: one class per `x-schema-type: wysiwygNode` schema, extending
 * \Flyo\Wysiwyg\WysiwygNode.
 *
 * A custom element of the organisation's WYSIWYG library is delivered as one node of the TipTap
 * json of a WYSIWYG field: `{"type": "person_card", "attrs": {...}, "content": [...]}`. Its class
 * gets one `getAttr<Name>()` per attribute, so the attributes are the contract a renderer is
 * written against.
 *
 * Unlike every other kind these classes are instantiated: a renderer callback typed
 * `fn (WysiwygNodePersonCard $node)` is checked by PHP at runtime, so the parser has to hand it a
 * real instance. The base class implements nadar/prosemirror-json-parser's NodeInterface, which
 * is what makes the parser build one. The classes still carry no logic of their own: the base
 * class reads the values.
 */
final class WysiwygNodeKind implements Kind
{
    /** The runtime class every node class extends. */
    public const PARENT_CLASS = '\Flyo\Wysiwyg\WysiwygNode';

    private const PREFIX = 'WysiwygNode';

    private const GETTER_PREFIX = 'getAttr';

    /**
     * Methods of the base class a getter must not redeclare, in lower case: PHP method names are
     * case-insensitive, so an attribute `s` would turn `getAttrS()` into `getAttrs()`.
     */
    private const RESERVED_GETTERS = ['getattrs'];

    public function schemaType(): string
    {
        return 'wysiwygNode';
    }

    public function directory(): string
    {
        return 'Wysiwyg';
    }

    public function noun(int $count): string
    {
        return $count === 1 ? 'WYSIWYG element' : 'WYSIWYG elements';
    }

    public function summary(): string
    {
        return 'one class per custom WYSIWYG element, extending ' . self::PARENT_CLASS;
    }

    public function plan(Document $document, array $schemas, string $namespace, Warnings $warnings): array
    {
        $preferred = [];
        foreach (array_keys($schemas) as $schemaKey) {
            $preferred[$schemaKey] = Naming::prefixedKeyName(self::PREFIX, $schemaKey);
        }

        $classNames = Naming::resolve(
            $preferred,
            $warnings,
            'WYSIWYG element',
            'Rename one of the WYSIWYG elements in Flyo to remove the ambiguity.',
        );

        // Attribute values are plain decoded objects, so nothing in them resolves to a class.
        $types = new TypeMapper($document, [], $warnings);

        $classes = [];
        foreach ($schemas as $schemaKey => $schema) {
            $attrs = Schema::property($schema, 'attrs') ?? [];

            $constants = [];
            $identifier = Naming::enumValue($schema, 'type');
            if ($identifier !== '') {
                $constants[] = new ConstantDef('IDENTIFIER', $identifier);
            }
            $title = Schema::text($schema, 'title');
            if ($title !== '') {
                $constants[] = new ConstantDef('TITLE', $title);
            }
            $constants = [...$constants, ...EnumConstants::plan($schemaKey, ['attr' => $attrs], $warnings)];

            $classes[] = new ClassDef(
                $classNames[$schemaKey],
                $namespace,
                self::PARENT_CLASS,
                $title,
                Schema::text($schema, 'description'),
                Schema::property($schema, 'content') === null
                    ? []
                    : ['Wraps rich text: renderContent() renders it, getContent() holds its nodes.'],
                'A real class, unlike the other generated types: the WYSIWYG parser\'s addNode() '
                    . 'passes an instance to a renderer whose parameter is typed with it, and '
                    . '::from($node) builds one from a decoded node. The attribute values are plain '
                    . '\stdClass objects.',
                $constants,
                $this->getters(
                    $schemaKey,
                    $attrs,
                    in_array('attrs', Schema::requiredKeys($schema), true),
                    $types,
                    $warnings,
                ),
            );
        }

        return $classes;
    }

    /**
     * One getter per attribute, in document order.
     *
     * A getter name that two attributes would share is not generated at all, for the same reason
     * {@see EnumConstants} does not number a shared constant name: a number would quietly move to
     * the other attribute once one is renamed in Flyo.
     *
     * @param array<string, mixed> $attrs the `attrs` schema of the element
     * @param bool $attrsRequired whether every node carries `attrs`; an element that was never
     *  filled in may carry none, and then no attribute is there either
     * @return list<MethodDef>
     */
    private function getters(
        string $schemaKey,
        array $attrs,
        bool $attrsRequired,
        TypeMapper $types,
        Warnings $warnings,
    ): array {
        $properties = $attrs['properties'] ?? null;
        if (!is_array($properties)) {
            return [];
        }

        $required = Schema::requiredKeys($attrs);

        /** @var array<string, list<string>> $claims lower-cased getter => attribute identifiers */
        $claims = [];
        /** @var array<string, string> $names attribute identifier => getter */
        $names = [];

        foreach ($properties as $name => $propSchema) {
            // A numeric-looking JSON key arrives as an int.
            $name = (string) $name;

            if (!is_array($propSchema)) {
                continue;
            }

            if (Naming::constantSegment($name) === '') {
                $warnings->add(sprintf(
                    '%s.attrs.%s: the attribute identifier has no letter or digit to name a getter '
                        . 'after, so it gets none.',
                    $schemaKey,
                    $name,
                ));

                continue;
            }

            $names[$name] = self::GETTER_PREFIX . Naming::studly($name);
            $claims[strtolower($names[$name])][] = $name;
        }

        $methods = [];

        foreach ($properties as $name => $propSchema) {
            $name = (string) $name;

            if (!isset($names[$name]) || !is_array($propSchema)) {
                continue;
            }

            $claimants = $claims[strtolower($names[$name])];

            if (in_array(strtolower($names[$name]), self::RESERVED_GETTERS, true)) {
                $warnings->add(sprintf(
                    '%s.attrs.%s: the getter would be %s(), which the base class already has, so the '
                        . 'attribute gets none. Read it with getAttrs()->%s or rename it in Flyo.',
                    $schemaKey,
                    $name,
                    $names[$name],
                    $name,
                ));

                continue;
            }

            if (count($claimants) > 1) {
                // Reported once, by the first attribute of the group.
                if ($claimants[0] === $name) {
                    $warnings->add(sprintf(
                        '%s: the attributes %s would all get the getter %s(), so none of them gets '
                            . 'one. Rename one of them in Flyo to remove the ambiguity.',
                        $schemaKey,
                        implode(', ', $claimants),
                        $names[$name],
                    ));
                }

                continue;
            }

            $methods[] = new MethodDef(
                $names[$name],
                $types->propertyType(
                    $propSchema,
                    [$schemaKey, 'attrs', $name],
                    $attrsRequired && in_array($name, $required, true),
                ),
                self::describe($propSchema),
                '$this->attr(' . self::literal($name) . ')',
            );
        }

        return $methods;
    }

    /**
     * The attribute's label in Flyo, followed by its hint when it has one.
     *
     * @param array<string, mixed> $schema
     */
    private static function describe(array $schema): string
    {
        $title = Schema::text($schema, 'title');
        $description = Schema::text($schema, 'description');

        if ($title === '' || $description === '' || $title === $description) {
            return $title !== '' ? $title : $description;
        }

        return rtrim($title, '.:') . ': ' . $description;
    }

    private static function literal(string $value): string
    {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }
}
