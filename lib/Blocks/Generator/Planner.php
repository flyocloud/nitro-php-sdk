<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * Walks a document's typed block schemas and produces one {@see ClassDef} per block.
 *
 * Pure: no IO, no filesystem, no clock. That is what lets the golden test compare rendered
 * strings without touching disk.
 */
final class Planner
{
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

    /**
     * @return list<ClassDef>
     */
    public function plan(Document $document, string $namespace, Warnings $warnings): array
    {
        $schemas = $document->blockSchemas();

        // Pass 1: settle every class name, so a $ref from one block to another can resolve to the
        // final, post-collision name.
        $classNames = Naming::classNames($schemas, $warnings);

        $blockClasses = [];
        foreach ($classNames as $schemaKey => $className) {
            $blockClasses[$schemaKey] = $namespace . '\\' . $className;
        }

        $types = new TypeMapper($document, $blockClasses, $warnings);

        // Pass 2: render.
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
        $properties = $schema['properties'] ?? null;
        $properties = is_array($properties) ? $properties : [];
        $required = self::requiredKeys($schema);

        $methods = [];
        foreach (self::GETTERS as $property => $getter) {
            $propSchema = $properties[$property] ?? null;

            // A block schema always carries all four, but never assume the document is well formed.
            if (!is_array($propSchema)) {
                continue;
            }

            $methods[] = new MethodDef(
                $getter,
                $types->propertyType($propSchema, [$schemaKey, $property], in_array($property, $required, true)),
                self::text($propSchema, 'description'),
            );
        }

        return new ClassDef(
            $className,
            $namespace,
            $className . '.php',
            self::text($schema, 'title'),
            self::text($schema, 'description'),
            Naming::enumValue($schema, 'identifier'),
            Naming::enumValue($schema, 'component'),
            self::slots($properties),
            $methods,
        );
    }

    /**
     * The declared slot identifiers, in document order.
     *
     * @param array<int|string, mixed> $properties
     * @return list<string>
     */
    private static function slots(array $properties): array
    {
        $slots = $properties['slots'] ?? null;
        $slotProperties = is_array($slots) ? ($slots['properties'] ?? null) : null;

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

    /**
     * @param array<int|string, mixed> $schema
     */
    private static function text(array $schema, string $key): string
    {
        $value = $schema[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<int|string, mixed> $schema
     * @return list<string>
     */
    private static function requiredKeys(array $schema): array
    {
        $required = $schema['required'] ?? null;

        if (!is_array($required)) {
            return [];
        }

        $keys = [];
        foreach ($required as $key) {
            if (is_string($key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
