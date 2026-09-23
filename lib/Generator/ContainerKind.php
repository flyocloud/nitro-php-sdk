<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Navigation containers: one class per `x-schema-type: container` schema, plus one for the item
 * schema their pages reference.
 *
 * Unlike a block's content, a container is hydrated into real SDK models all the way down:
 * ConfigApi::config() returns each container as a \Flyo\Model\ConfigResponseContainersValue whose
 * items and their children are \Flyo\Model\ContainerPage. So the item schema becomes a class of
 * its own rather than an inline shape, and only `properties`, the one untyped map on
 * ContainerPage, is described as a shape. It is also the part that differs per site.
 */
final class ContainerKind implements Kind
{
    /** The SDK model every container class extends. */
    public const PARENT_CLASS = '\Flyo\Model\ConfigResponseContainersValue';

    /** The SDK model every container item class extends. */
    public const ITEM_PARENT_CLASS = '\Flyo\Model\ContainerPage';

    private const PREFIX = 'Container';

    public function schemaType(): string
    {
        return 'container';
    }

    public function directory(): string
    {
        return 'Containers';
    }

    public function noun(int $count): string
    {
        return $count === 1 ? 'container' : 'containers';
    }

    public function summary(): string
    {
        return 'one class per navigation container, plus its item class';
    }

    public function plan(Document $document, array $schemas, string $namespace, Warnings $warnings): array
    {
        $itemSchemas = $this->itemSchemas($document, $schemas);

        $preferred = [];
        foreach ([...array_keys($schemas), ...array_keys($itemSchemas)] as $schemaKey) {
            $preferred[$schemaKey] = Naming::prefixedKeyName(self::PREFIX, $schemaKey);
        }

        $classNames = Naming::resolve(
            $preferred,
            $warnings,
            'container',
            'Rename one of the containers in Flyo to remove the ambiguity.',
        );

        $itemClasses = [];
        foreach (array_keys($itemSchemas) as $schemaKey) {
            $itemClasses[$schemaKey] = '\\' . $namespace . '\\' . $classNames[$schemaKey];
        }

        // Page properties are plain decoded values, so nothing in them resolves to a class.
        $types = new TypeMapper($document, [], $warnings);

        $classes = [];

        foreach ($schemas as $schemaKey => $schema) {
            $methods = [];

            $items = $this->listOf($document, $schema, 'items', $itemClasses);
            if ($items !== null) {
                $methods[] = $items;
            }

            $identifier = Naming::enumValue($schema, 'identifier');

            $classes[] = new ClassDef(
                $classNames[$schemaKey],
                $namespace,
                self::PARENT_CLASS,
                Schema::text($schema, 'title'),
                Schema::text($schema, 'description'),
                [],
                'Documentation-only type: at runtime a container is always a ' . self::PARENT_CLASS
                    . ', found under its IDENTIFIER in ConfigResponse::getContainers(). Annotate a '
                    . 'variable with @var; do not instantiate this class.',
                $identifier === '' ? [] : [new ConstantDef('IDENTIFIER', $identifier)],
                $methods,
            );
        }

        foreach ($itemSchemas as $schemaKey => $schema) {
            $methods = [];

            $properties = Schema::property($schema, 'properties');
            if ($properties !== null) {
                $methods[] = new MethodDef(
                    'getProperties',
                    $types->arrayShapeType(
                        $properties,
                        [$schemaKey, 'properties'],
                        in_array('properties', Schema::requiredKeys($schema), true),
                    ),
                    Schema::text($properties, 'description'),
                );
            }

            $children = $this->listOf($document, $schema, 'children', $itemClasses);
            if ($children !== null) {
                $methods[] = $children;
            }

            $classes[] = new ClassDef(
                $classNames[$schemaKey],
                $namespace,
                self::ITEM_PARENT_CLASS,
                Schema::text($schema, 'title'),
                Schema::text($schema, 'description'),
                [],
                'Documentation-only type: at runtime a container item is always a '
                    . self::ITEM_PARENT_CLASS . ', and getProperties() returns a plain array of '
                    . 'decoded values. Annotate a variable with @var; do not instantiate this class.',
                [],
                $methods,
            );
        }

        return $classes;
    }

    /**
     * The item schemas the containers' pages reference, keyed by schema name, sorted.
     *
     * Found by following `items` from each container and then `children` from each item, so an
     * item schema is picked up by what references it rather than by its name.
     *
     * @param array<string, array<string, mixed>> $containers
     * @return array<string, array<string, mixed>>
     */
    private function itemSchemas(Document $document, array $containers): array
    {
        $queue = [];
        foreach ($containers as $schema) {
            $queue[] = $this->listRef($document, $schema, 'items');
        }

        $items = [];

        while ($queue !== []) {
            $key = array_shift($queue);

            if ($key === null || isset($items[$key]) || isset($containers[$key])) {
                continue;
            }

            $schema = $document->schema($key);

            if ($schema === null) {
                continue;
            }

            $items[$key] = $schema;
            $queue[] = $this->listRef($document, $schema, 'children');
        }

        ksort($items, SORT_STRING);

        return $items;
    }

    /**
     * The override for a getter returning a list of items, or null when the list does not
     * reference a known item schema. The parent's `ContainerPage[]` is still correct then, so
     * leaving the getter alone is better than narrowing it to something vaguer.
     *
     * @param array<string, mixed> $schema
     * @param array<string, string> $itemClasses
     */
    private function listOf(Document $document, array $schema, string $property, array $itemClasses): ?MethodDef
    {
        $key = $this->listRef($document, $schema, $property);

        if ($key === null || !isset($itemClasses[$key])) {
            return null;
        }

        $list = Schema::property($schema, $property) ?? [];
        $nullable = !in_array($property, Schema::requiredKeys($schema), true) || ($list['nullable'] ?? null) === true;

        return new MethodDef(
            'get' . ucfirst($property),
            'array<int, ' . $itemClasses[$key] . '>' . ($nullable ? '|null' : ''),
            Schema::text($list, 'description'),
        );
    }

    /**
     * The schema key an array property's items `$ref` points at, or null.
     *
     * @param array<string, mixed> $schema
     */
    private function listRef(Document $document, array $schema, string $property): ?string
    {
        $list = Schema::property($schema, $property);
        $items = $list === null ? null : ($list['items'] ?? null);
        $ref = is_array($items) ? ($items['$ref'] ?? null) : null;

        return is_string($ref) ? $document->refKey($ref) : null;
    }
}
