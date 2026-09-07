<?php

declare(strict_types=1);

namespace Flyo\Blocks\Generator;

/**
 * One generated getter override.
 *
 * The body is always `return parent::<name>();` — these classes exist to carry a type, not
 * behaviour. The narrow type is emitted twice, as `@return` and as an inline `@var` on the return
 * statement: narrowing the signature is legal, but without the `@var` the return *statement* still
 * has the parent's wider type and PHPStan reports it from level 7 up.
 */
final class MethodDef
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $description,
    ) {
    }
}
