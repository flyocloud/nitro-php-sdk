<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Blocks: one class per `x-schema-type: block` schema, narrowing \Flyo\Model\Block.
 *
 * The deprecated `flyo-generate-blocks` renders these same classes, so any change to their output
 * shows up as drift in every consumer's `--check` and has to be deliberate.
 */
final class BlockKind implements Kind
{
    /** The SDK model every block class extends. */
    public const PARENT_CLASS = '\Flyo\Model\Block';

    /**
     * Only these three getters are overridden. `getSlots()`, `getIdentifier()`, `getComponent()`
     * and `getUid()` are already exactly typed by \Flyo\Model\Block, so narrowing them would add
     * noise and no information.
     *
     * @var array<string, string> block schema property => getter name
     */
    private const GETTERS = [
        'content' => 'getContent',
        'config' => 'getConfig',
        'items' => 'getItems',
    ];

    /** Never a real slot: the server adds it as a bookkeeping flag. */
    private const SLOT_FLAG = '_empty';

    public function schemaType(): string
    {
        return 'block';
    }

    public function directory(): string
    {
        return 'Blocks';
    }

    public function noun(int $count): string
    {
        return $count === 1 ? 'block' : 'blocks';
    }

    public function summary(): string
    {
        return 'one class per block, narrowing \Flyo\Model\Block';
    }

    public function plan(Document $document, array $schemas, string $namespace, Warnings $warnings): array
    {
        // Pass 1: settle every class name, so a $ref from one block to another can resolve to the
        // final, post-collision name.
        $classNames = Naming::blockClassNames($schemas, $warnings);

        $blockClasses = [];
        foreach ($classNames as $schemaKey => $className) {
            $blockClasses[$schemaKey] = $namespace . '\\' . $className;
        }

        $types = new TypeMapper($document, $blockClasses, $warnings);

        // Pass 2: plan.
        $classes = [];
        foreach ($schemas as $schemaKey => $schema) {
            $classes[] = $this->classDef($schemaKey, $schema, $classNames[$schemaKey], $namespace, $types);
        }

        return $classes;
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function classDef(
        string $schemaKey,
        array $schema,
        string $className,
        string $namespace,
        TypeMapper $types,
    ): ClassDef {
        $required = Schema::requiredKeys($schema);

        $methods = [];
        foreach (self::GETTERS as $property => $getter) {
            $propSchema = Schema::property($schema, $property);

            // A block schema always carries all four, but never assume the document is well formed.
            if ($propSchema === null) {
                continue;
            }

            $methods[] = new MethodDef(
                $getter,
                $types->propertyType($propSchema, [$schemaKey, $property], in_array($property, $required, true)),
                Schema::text($propSchema, 'description'),
            );
        }

        $identifier = Naming::enumValue($schema, 'identifier');
        $component = Naming::enumValue($schema, 'component');
        $slots = self::slots($schema);

        $constants = [];
        if ($identifier !== '') {
            $constants[] = new ConstantDef('IDENTIFIER', $identifier);
        }
        if ($component !== '') {
            $constants[] = new ConstantDef('COMPONENT', $component);
        }
        if ($slots !== []) {
            $constants[] = new ConstantDef('SLOTS', $slots);
        }

        return new ClassDef(
            $className,
            $namespace,
            self::PARENT_CLASS,
            Schema::text($schema, 'title'),
            Schema::text($schema, 'description'),
            $slots === [] ? [] : ['Declared slots: ' . implode(', ', $slots) . '.'],
            'Documentation-only type: at runtime a block is always a ' . self::PARENT_CLASS
                . ', and the values these getters return are plain \stdClass objects. Annotate a '
                . 'variable with @var; do not instantiate this class.',
            $constants,
            $methods,
        );
    }

    /**
     * The declared slot identifiers, in document order.
     *
     * @param array<string, mixed> $schema
     * @return list<string>
     */
    private static function slots(array $schema): array
    {
        $slots = Schema::property($schema, 'slots');
        $slotProperties = $slots === null ? null : ($slots['properties'] ?? null);

        if (!is_array($slotProperties)) {
            return [];
        }

        $names = [];
        foreach (array_keys($slotProperties) as $name) {
            $name = (string) $name;

            if ($name !== self::SLOT_FLAG && $name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }
}
