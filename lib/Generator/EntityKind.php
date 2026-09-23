<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Entities: one class per `x-schema-type: entity` schema, narrowing \Flyo\Model\Entity.
 *
 * An entity schema describes the `model` of an entity response, the part whose shape depends on
 * the entity type, so the whole schema becomes the type of `getModel()`. Everything else on
 * \Flyo\Model\Entity is the same for every entity type and already typed by the SDK.
 */
final class EntityKind implements Kind
{
    /** The SDK model every entity class extends. */
    public const PARENT_CLASS = '\Flyo\Model\Entity';

    private const PREFIX = 'Entity';

    public function schemaType(): string
    {
        return 'entity';
    }

    public function directory(): string
    {
        return 'Entities';
    }

    public function noun(int $count): string
    {
        return $count === 1 ? 'entity' : 'entities';
    }

    public function summary(): string
    {
        return 'one class per entity type, narrowing \Flyo\Model\Entity';
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
            'entity',
            'Rename one of the entity types in Flyo to remove the ambiguity.',
        );

        // No classes to resolve to: the model is a plain decoded object all the way down, so a
        // reference to another entity schema describes a \stdClass, never an Entity instance.
        $types = new TypeMapper($document, [], $warnings);

        // Presence of `model` is declared on the generic entity response, like any other property.
        $required = in_array(
            'model',
            Schema::requiredKeys($document->schema(Document::GENERIC_ENTITY_KEY) ?? []),
            true,
        );

        $classes = [];
        foreach ($schemas as $schemaKey => $schema) {
            $classes[] = new ClassDef(
                $classNames[$schemaKey],
                $namespace,
                self::PARENT_CLASS,
                Schema::text($schema, 'title'),
                Schema::text($schema, 'description'),
                [],
                'Documentation-only type: at runtime an entity is always a ' . self::PARENT_CLASS
                    . ', and getModel() returns a plain \stdClass object. Annotate a variable with '
                    . '@var; do not instantiate this class.',
                [],
                [new MethodDef('getModel', $types->propertyType($schema, [$schemaKey], $required), '')],
            );
        }

        return $classes;
    }
}
