<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\ExitCode;
use Flyo\Generator\GeneratorException;
use Flyo\Generator\Options;
use Flyo\Generator\Profile;
use Flyo\Generator\Renderer;
use Flyo\Generator\Writer;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    private string $dir = '';

    protected function setUp(): void
    {
        $dir = tempnam(sys_get_temp_dir(), 'flyo-writer-');
        self::assertIsString($dir);
        unlink($dir);

        $this->dir = $dir;
    }

    protected function tearDown(): void
    {
        if ($this->dir !== '' && is_dir($this->dir)) {
            foreach (glob($this->dir . '/*/*') ?: [] as $file) {
                unlink($file);
            }

            foreach (glob($this->dir . '/*') ?: [] as $file) {
                is_dir($file) ? rmdir($file) : unlink($file);
            }

            rmdir($this->dir);
        }
    }

    public function testCreatesTheTargetDirectoryRecursively(): void
    {
        $nested = $this->dir . '/a/b/c';
        $result = (new Writer())->write(['X.php' => 'hello'], self::options($nested));

        $this->assertSame(['X.php'], $result->written);
        $this->assertFileExists($nested . '/X.php');
        $this->assertSame('hello', file_get_contents($nested . '/X.php'));

        // tearDown only handles one level.
        unlink($nested . '/X.php');
        rmdir($nested);
        rmdir($this->dir . '/a/b');
        rmdir($this->dir . '/a');
    }

    public function testUnchangedFilesAreNotRewritten(): void
    {
        $writer = new Writer();
        $files = ['X.php' => self::generated('X')];

        $writer->write($files, self::options($this->dir));
        $before = filemtime($this->dir . '/X.php');

        // Same bytes: the file must be left completely alone, mtime included.
        $result = $writer->write($files, self::options($this->dir));

        clearstatcache();

        $this->assertSame(['X.php'], $result->unchanged);
        $this->assertSame([], $result->written);
        $this->assertSame($before, filemtime($this->dir . '/X.php'));
        $this->assertFalse($result->changed());
    }

    public function testChangedContentIsRewritten(): void
    {
        $writer = new Writer();

        $writer->write(['X.php' => self::generated('X')], self::options($this->dir));
        $result = $writer->write(['X.php' => self::generated('X') . '// more'], self::options($this->dir));

        $this->assertSame(['X.php'], $result->written);
        $this->assertTrue($result->changed());
    }

    public function testStaleGeneratedFilesAreRemoved(): void
    {
        $writer = new Writer();
        $writer->write(
            ['Keep.php' => self::generated('Keep'), 'Gone.php' => self::generated('Gone')],
            self::options($this->dir)
        );

        $result = $writer->write(['Keep.php' => self::generated('Keep')], self::options($this->dir));

        $this->assertSame(['Gone.php'], $result->removed);
        $this->assertFileDoesNotExist($this->dir . '/Gone.php');
        $this->assertFileExists($this->dir . '/Keep.php');
        $this->assertTrue($result->changed());
    }

    /**
     * The whole point of the marker: a file we did not write is never deleted, so hand-written
     * code can share the directory safely.
     */
    public function testFilesWithoutTheMarkerAreNeverTouched(): void
    {
        mkdir($this->dir);
        $handWritten = "<?php\n// my own helper\nclass MyHelper {}\n";
        file_put_contents($this->dir . '/MyHelper.php', $handWritten);

        $result = (new Writer())->write(['X.php' => self::generated('X')], self::options($this->dir));

        $this->assertSame([], $result->removed);
        $this->assertFileExists($this->dir . '/MyHelper.php');
        $this->assertSame($handWritten, file_get_contents($this->dir . '/MyHelper.php'));
    }

    public function testNoCleanKeepsStaleFilesButStillReportsNothing(): void
    {
        $writer = new Writer();
        $writer->write(['Gone.php' => self::generated('Gone')], self::options($this->dir));

        $result = $writer->write(['X.php' => self::generated('X')], self::options($this->dir, ['--no-clean']));

        $this->assertSame([], $result->removed);
        $this->assertFileExists($this->dir . '/Gone.php');
    }

    public function testDryRunWritesNothing(): void
    {
        $result = (new Writer())->write(
            ['X.php' => self::generated('X')],
            self::options($this->dir, ['--dry-run'])
        );

        $this->assertSame(['X.php'], $result->written, 'it should still report what it would write');
        $this->assertFileDoesNotExist($this->dir . '/X.php');
        $this->assertDirectoryDoesNotExist($this->dir);
    }

    public function testDryRunRemovesNothing(): void
    {
        (new Writer())->write(['Gone.php' => self::generated('Gone')], self::options($this->dir));

        $result = (new Writer())->write(
            ['X.php' => self::generated('X')],
            self::options($this->dir, ['--dry-run'])
        );

        $this->assertSame(['Gone.php'], $result->removed, 'it should still report what it would remove');
        $this->assertFileExists($this->dir . '/Gone.php');
    }

    public function testATargetThatIsAFileIsAnError(): void
    {
        file_put_contents($this->dir, 'not a directory');

        try {
            (new Writer())->write(['X.php' => 'x'], self::options($this->dir));
            $this->fail('expected a filesystem error');
        } catch (GeneratorException $e) {
            $this->assertSame(ExitCode::FILESYSTEM, $e->exitCode());
            $this->assertStringContainsString('is a file, not a directory', $e->getMessage());
        } finally {
            unlink($this->dir);
            $this->dir = '';
        }
    }

    public function testResultReportsTheResolvedAbsoluteTarget(): void
    {
        $result = (new Writer())->write(['X.php' => self::generated('X')], self::options($this->dir));

        $this->assertSame(realpath($this->dir), $result->target);
    }

    public function testOnlyPhpFilesAreConsideredForCleanup(): void
    {
        mkdir($this->dir);
        file_put_contents($this->dir . '/notes.txt', Renderer::MARKER);

        $result = (new Writer())->write(['X.php' => self::generated('X')], self::options($this->dir));

        $this->assertSame([], $result->removed);
        $this->assertFileExists($this->dir . '/notes.txt');
    }

    public function testStaleFilesAreRemovedFromEveryKindDirectory(): void
    {
        $writer = new Writer();
        $writer->write(
            ['Blocks/BlockKeep.php' => self::generated('BlockKeep'), 'Entities/EntityGone.php' => self::generated('EntityGone')],
            self::options($this->dir)
        );

        $result = $writer->write(['Blocks/BlockKeep.php' => self::generated('BlockKeep')], self::options($this->dir));

        $this->assertSame(['Entities/EntityGone.php'], $result->removed);
        $this->assertFileDoesNotExist($this->dir . '/Entities/EntityGone.php');
        $this->assertFileExists($this->dir . '/Blocks/BlockKeep.php');
    }

    public function testADirectoryNoKindOwnsIsNotScanned(): void
    {
        mkdir($this->dir . '/Other', 0777, true);
        file_put_contents($this->dir . '/Other/Old.php', self::generated('Old'));

        $result = (new Writer())->write(['Blocks/BlockX.php' => self::generated('BlockX')], self::options($this->dir));

        $this->assertSame([], $result->removed);
        $this->assertFileExists($this->dir . '/Other/Old.php');
    }

    /**
     * The deprecated command writes straight into the target, so its kind-named subdirectories
     * may well be the consumer's own. It must keep cleaning only where it writes.
     */
    public function testTheDeprecatedLayoutOnlyCleansTheTargetItself(): void
    {
        mkdir($this->dir . '/Entities', 0777, true);
        file_put_contents($this->dir . '/Entities/EntityOld.php', self::generated('EntityOld'));
        file_put_contents($this->dir . '/BlockGone.php', self::generated('BlockGone'));

        $options = Options::parse(['x', 'schemas.json', 'App\Blocks', $this->dir], [], Profile::blocks());
        $result = (new Writer())->write(['BlockX.php' => self::generated('BlockX')], $options);

        $this->assertSame(['BlockGone.php'], $result->removed);
        $this->assertFileExists($this->dir . '/Entities/EntityOld.php');
    }

    private static function generated(string $class): string
    {
        return "<?php\n\n/**\n * " . Renderer::MARKER . " - do not edit.\n */\nclass {$class} {}\n";
    }

    /**
     * @param list<string> $extra
     */
    private static function options(string $target, array $extra = []): Options
    {
        return Options::parse(['x', 'schemas.json', 'App\Flyo', $target, ...$extra]);
    }
}
