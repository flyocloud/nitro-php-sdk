<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * One kind of typed schema the generator knows how to turn into classes.
 *
 * A kind is selected by the `x-schema-type` marker the server puts on each typed schema, and owns
 * everything specific to it: which SDK model the classes narrow, which getters they override and
 * how the classes are named. Supporting a new schema type means adding one implementation and
 * listing it in {@see Profile::types()}.
 */
interface Kind
{
    /**
     * The `x-schema-type` value this kind generates from, e.g. `block`.
     */
    public function schemaType(): string;

    /**
     * The sub-namespace and directory the classes are written to, e.g. `Blocks`.
     */
    public function directory(): string;

    /**
     * The noun for `$count` schemas of this kind, for messages: `block` or `blocks`.
     */
    public function noun(int $count): string;

    /**
     * One line for the help text: what the generated classes are.
     */
    public function summary(): string;

    /**
     * Plans the classes for this kind's schemas.
     *
     * Must be pure: no IO, no filesystem, no clock. May return more classes than there are
     * schemas when a kind needs a class for a schema that is only referenced.
     *
     * @param array<string, array<string, mixed>> $schemas this kind's typed schemas, sorted by key
     * @return list<ClassDef>
     */
    public function plan(Document $document, array $schemas, string $namespace, Warnings $warnings): array;
}
