<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * One generated class, fully resolved and ready to render.
 */
final class ClassDef
{
    /**
     * @param string $parentClass the SDK model the class narrows, fully qualified with a leading "\"
     * @param list<string> $notes extra paragraphs for the class docblock, after the description
     * @param string $runtimeNote what the value really is at runtime, and how to use the class
     * @param list<ConstantDef> $constants
     * @param list<MethodDef> $methods
     */
    public function __construct(
        public readonly string $className,
        public readonly string $namespace,
        public readonly string $parentClass,
        public readonly string $title,
        public readonly string $description,
        public readonly array $notes,
        public readonly string $runtimeNote,
        public readonly array $constants,
        public readonly array $methods,
    ) {
    }
}
