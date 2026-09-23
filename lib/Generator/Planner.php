<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * Hands each of a profile's kinds its typed schemas and collects the classes they plan.
 *
 * Pure: no IO, no filesystem, no clock. That is what lets the golden test compare rendered
 * strings without touching disk.
 */
final class Planner
{
    public function plan(Document $document, Profile $profile, string $namespace, Warnings $warnings): Plan
    {
        $classes = [];
        $counts = [];

        foreach ($profile->kinds as $kind) {
            $schemas = $document->typedSchemas($kind->schemaType());
            $counts[$kind->schemaType()] = count($schemas);

            if ($schemas === []) {
                continue;
            }

            foreach ($kind->plan($document, $schemas, $profile->namespaceFor($kind, $namespace), $warnings) as $class) {
                $classes[$profile->pathFor($kind, $class->className)] = $class;
            }
        }

        ksort($classes, SORT_STRING);

        if ($profile->reportUnsupported) {
            $this->reportUnsupported($document, $counts, $warnings);
        }

        return new Plan($classes, $counts);
    }

    /**
     * Names every marker the profile does not generate from, so a schema type the server added
     * after this release is not skipped silently.
     *
     * @param array<string, int> $supported
     */
    private function reportUnsupported(Document $document, array $supported, Warnings $warnings): void
    {
        foreach ($document->schemaTypeCounts() as $type => $count) {
            if ($type === '' || isset($supported[$type])) {
                continue;
            }

            $warnings->add(sprintf(
                '%d %s x-schema-type "%s", which this version of %s does not generate; %s skipped.',
                $count,
                $count === 1 ? 'schema carries' : 'schemas carry',
                $type,
                Options::PACKAGE,
                $count === 1 ? 'it was' : 'they were',
            ));
        }
    }
}
