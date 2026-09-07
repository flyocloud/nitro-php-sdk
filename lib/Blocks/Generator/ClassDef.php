<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * One generated block class, fully resolved and ready to render.
 */
final class ClassDef
{
    /**
     * @param list<MethodDef> $methods
     * @param list<string> $slots declared slot identifiers, in document order
     */
    public function __construct(
        public readonly string $className,
        public readonly string $namespace,
        public readonly string $relativePath,
        public readonly string $title,
        public readonly string $description,
        public readonly string $identifier,
        public readonly string $component,
        public readonly array $slots,
        public readonly array $methods,
    ) {
    }
}
