<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * One public class constant of a generated class.
 *
 * A list value is rendered as an array literal with a `@var list<string>` annotation, so PHPStan
 * does not widen it to `array<int, string>`.
 */
final class ConstantDef
{
    /**
     * @param string|list<string> $value
     */
    public function __construct(
        public readonly string $name,
        public readonly string|array $value,
    ) {
    }
}
