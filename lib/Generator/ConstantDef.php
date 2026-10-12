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
     * @param string|null $group constants that share a group are rendered without a blank line
     *  between them, like the options of one field
     */
    public function __construct(
        public readonly string $name,
        public readonly string|array $value,
        public readonly ?string $group = null,
    ) {
    }
}
