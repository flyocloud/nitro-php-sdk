<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * One generated getter.
 *
 * The body is `return parent::<name>();` unless `$body` says otherwise. These classes exist to
 * carry a type, not behaviour, so a getter of its own only ever returns one expression. The narrow
 * type is emitted twice, as `@return` and as an inline `@var` on the return statement: narrowing
 * the signature is legal, but without the `@var` the return *statement* still has the parent's
 * wider type and PHPStan reports it from level 7 up.
 */
final class MethodDef
{
    /**
     * @param string|null $body the expression the getter returns, e.g. `$this->attr('user')`;
     *  null overrides the parent getter of the same name
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $description,
        public readonly ?string $body = null,
    ) {
    }
}
