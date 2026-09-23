<?php

declare(strict_types=1);

namespace Flyo\Generator;

/**
 * What one binary generates, and where it puts it.
 *
 * `flyo-generate-types` writes every kind into a sub-namespace and directory of its own
 * (`<namespace>\Blocks`, `<namespace>\Containers`, `<namespace>\Entities`). The deprecated
 * `flyo-generate-blocks` generates blocks only, straight into `<namespace>`, and must keep
 * producing byte-identical files for as long as it exists, or upgrading the package would make
 * every consumer's `--check` fail.
 */
final class Profile
{
    /**
     * @param list<Kind> $kinds in the order they are reported
     * @param bool $flat write every class straight into the target instead of a directory per kind
     * @param bool $reportUnsupported warn about `x-schema-type` markers no kind generates from
     * @param bool $lowercase name the kind directories and sub-namespaces `blocks`, `entities`, ...
     *  instead of `Blocks`, `Entities`, ..., for frameworks whose namespaces follow lower-case
     *  directories, such as Yii 2's `app\flyo\blocks`
     */
    private function __construct(
        public readonly string $program,
        public readonly array $kinds,
        public readonly bool $flat,
        public readonly bool $reportUnsupported,
        public readonly ?string $deprecation,
        public readonly string $exampleNamespace,
        public readonly string $exampleTarget,
        public readonly bool $lowercase = false,
    ) {
    }

    public static function types(): self
    {
        return new self(
            program: 'flyo-generate-types',
            kinds: [new BlockKind(), new ContainerKind(), new EntityKind()],
            flat: false,
            reportUnsupported: true,
            deprecation: null,
            exampleNamespace: 'App\Flyo',
            exampleTarget: 'app/Flyo',
        );
    }

    /**
     * @deprecated only for the deprecated `flyo-generate-blocks` binary
     */
    public static function blocks(): self
    {
        return new self(
            program: 'flyo-generate-blocks',
            kinds: [new BlockKind()],
            flat: true,
            reportUnsupported: false,
            deprecation: 'flyo-generate-blocks is deprecated and will be removed in the next major '
                . 'release. Use flyo-generate-types, which also generates containers and entities: '
                . 'https://github.com/flyocloud/nitro-php-sdk#-typed-schemas',
            exampleNamespace: 'App\Blocks',
            exampleTarget: 'src/Blocks',
        );
    }

    /**
     * The same profile, with every kind directory and sub-namespace in lower case.
     */
    public function withLowercaseDirectories(): self
    {
        return new self(
            $this->program,
            $this->kinds,
            $this->flat,
            $this->reportUnsupported,
            $this->deprecation,
            $this->exampleNamespace,
            $this->exampleTarget,
            lowercase: true,
        );
    }

    public function namespaceFor(Kind $kind, string $namespace): string
    {
        return $this->flat ? $namespace : $namespace . '\\' . $this->directoryOf($kind);
    }

    public function pathFor(Kind $kind, string $className): string
    {
        return ($this->flat ? '' : $this->directoryOf($kind) . '/') . $className . '.php';
    }

    /**
     * The directory, and sub-namespace segment, a kind's classes go to under this profile.
     */
    public function directoryOf(Kind $kind): string
    {
        return $this->lowercase ? strtolower($kind->directory()) : $kind->directory();
    }

    /**
     * The directories, relative to the target, whose generated files a run may remove.
     *
     * The target itself is included in the per-kind layout too: nothing is ever generated there,
     * so a marked file in it is left over from pointing the deprecated binary at the same place.
     *
     * @return list<string>
     */
    public function managedDirectories(): array
    {
        if ($this->flat) {
            return [''];
        }

        return ['', ...array_map(fn (Kind $kind): string => $this->directoryOf($kind), $this->kinds)];
    }

    /**
     * What the generated classes are, for messages: "blocks", or "schemas" for several kinds.
     */
    public function subject(): string
    {
        return count($this->kinds) === 1 ? $this->kinds[0]->noun(2) : 'schemas';
    }

    /**
     * Warnings about a namespace that is valid but probably not what was meant.
     *
     * @return list<string>
     */
    public function namespaceWarnings(string $namespace): array
    {
        if ($this->flat) {
            return [];
        }

        $segments = explode('\\', $namespace);
        $last = end($segments);

        foreach ($this->kinds as $kind) {
            if (strcasecmp($last, $kind->directory()) === 0) {
                return [sprintf(
                    'namespace "%s" ends in "%s", but %s adds a sub-namespace per kind itself, so '
                        . 'blocks land in %s\\%s. Did you mean the root, e.g. "%s"?',
                    $namespace,
                    $last,
                    $this->program,
                    $namespace,
                    $this->directoryOf($this->kinds[0]),
                    $this->exampleNamespace,
                )];
            }
        }

        return [];
    }

    public function usage(): string
    {
        return sprintf('usage: %s <source> <namespace> <target> [options]', $this->program);
    }

    public function help(): string
    {
        return $this->flat ? $this->legacyHelp() : $this->typesHelp();
    }

    private function typesHelp(): string
    {
        $usage = $this->usage();
        $program = $this->program;

        $kinds = [];
        foreach ($this->kinds as $kind) {
            $kinds[] = sprintf('  %-22s%s', '<target>/' . $kind->directory(), $kind->summary());
        }
        $kinds = implode("\n", $kinds);

        return <<<TXT
        {$usage}

        Generates typed PHP classes from a Flyo Nitro OpenAPI document, one directory per kind:

        {$kinds}

        Each directory is a sub-namespace of <namespace>, e.g. App\\Flyo\\Blocks. The classes carry
        no logic: they narrow inherited getters of the SDK models so an IDE and PHPStan know the
        shape of the data. Pass --lowercase for lower-case directories and sub-namespaces, e.g.
        for Yii 2: app/flyo flyo --lowercase puts app\\flyo\\blocks in flyo/blocks.

        Arguments:
          <source>              OpenAPI URL, a local .json path, or - for stdin
          <namespace>           PSR-4 root namespace for the generated classes, e.g. 'App\\Flyo'
          <target>              directory that namespace maps to, e.g. app/Flyo

        {$this->optionsHelp('schemas', true)}

        Typed schemas only exist on the authenticated endpoint; the public /nitro/v1/openapi has
        none. Use:

          export FLYO_TOKEN=...
          {$program} https://api.flyo.cloud/nitro/v1/openapi/schemas 'App\\Flyo' app/Flyo

        In a composer.json script, write the namespace with forward slashes ("App/Flyo"), so that
        neither JSON nor the shell can eat the backslashes:

          "scripts": {
              "flyo:types": "vendor/bin/{$program} <source> App/Flyo app/Flyo"
          }

        Namespace and target must match the PSR-4 map of the project: App/Flyo and app/Flyo for
        Laravel's "App\\\\": "app/", App/Flyo and src/Flyo for Symfony's "App\\\\": "src/".

        Generated files are marked "@generated by flyo/nitro-php". Hand-written files in the same
        directories are never touched.
        TXT;
    }

    private function legacyHelp(): string
    {
        $usage = $this->usage();
        $program = $this->program;

        return <<<TXT
        {$usage}

        DEPRECATED: use flyo-generate-types, which generates blocks, containers and entities into
        one sub-namespace each. This command keeps working until the next major release.

        Generates one PHP class per Flyo Nitro block from an OpenAPI document. The classes carry
        no logic: they narrow the inherited getters of \\Flyo\\Model\\Block so an IDE and PHPStan
        know the shape of a block's content, config and items.

        Arguments:
          <source>              OpenAPI URL, a local .json path, or - for stdin
          <namespace>           PSR-4 namespace for the generated classes, e.g. 'App\\Blocks'
          <target>              directory that namespace maps to, e.g. src/Blocks

        {$this->optionsHelp('blocks')}

        Typed block schemas only exist on the authenticated endpoints; the public
        /nitro/v1/openapi has none. Use:

          export FLYO_TOKEN=...
          {$program} https://api.flyo.cloud/nitro/v1/openapi/schemas 'App\\Blocks' src/Blocks

        In a composer.json script, write the namespace with forward slashes ("App/Blocks"), so
        that neither JSON nor the shell can eat the backslashes:

          "scripts": {
              "flyo:types": "vendor/bin/{$program} <source> App/Blocks app/Blocks"
          }

        Namespace and target must match the PSR-4 map of the project: App/Blocks and app/Blocks
        for Laravel's "App\\\\": "app/", App/Blocks and src/Blocks for Symfony's
        "App\\\\": "src/".

        Generated files are marked "@generated by flyo/nitro-php". Hand-written files in the same
        directory are never touched.
        TXT;
    }

    private function optionsHelp(string $subject, bool $kindDirectories = false): string
    {
        $lowercase = $kindDirectories
            ? "\n      --lowercase       name the kind directories and sub-namespaces blocks,\n"
                . '                        containers, entities instead of Blocks, Containers, Entities'
            : '';

        return <<<TXT
        Options:
          -u, --url, --source   same as <source>
          -n, --ns, --namespace same as <namespace>
          -t, --dir, --target   same as <target>
          -k, --token KEY       API token, merged in as ?token=. Defaults to \$FLYO_TOKEN or
                                \$FLYO_API_KEY. Prefer the environment variable: an argument is
                                visible to anyone who can run ps.
              --no-clean        keep previously generated files that are no longer produced
              --allow-empty     exit 0 when the document declares no typed {$subject}{$lowercase}
          -d, --dry-run         report what would change, write nothing
              --check           like --dry-run, but exit 6 if anything is out of date
          -q, --quiet           suppress all output except warnings and errors
          -v, --verbose         also report unchanged files
          -h, --help            show this help
          -V, --version         show the installed package version
        TXT;
    }
}
