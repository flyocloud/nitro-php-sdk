<?php

namespace Flyo\Test\Generator;

use Flyo\Generator\Warnings;
use PHPUnit\Framework\TestCase;

class WysiwygNodeKindTest extends TestCase
{
    use FixtureTrait;

    /**
     * `s` would become getAttrS(), which PHP treats as the base class's getAttrs(), and `cta-link`
     * and `cta_link` both want getAttrCtaLink(). None of them may get a getter, and each must be
     * reported.
     */
    public function testUnusableGetterNamesAreSkippedAndReported(): void
    {
        $warnings = new Warnings();
        $files = self::renderAll(self::loadDocument('types/wysiwyg'), 'Fixture\Nodes', 'types', $warnings);

        $divider = $files['Wysiwyg/WysiwygNodeDivider.php'];
        $this->assertStringNotContainsStringIgnoringCase('function getAttrS()', $divider);
        $this->assertStringNotContainsString('getAttrCtaLink', $divider);

        $joined = implode("\n", $warnings->all());
        $this->assertStringContainsString('WysiwygNodeDivider.attrs.s: the getter would be getAttrS()', $joined);
        $this->assertStringContainsString('the attributes cta-link, cta_link would all get the getter getAttrCtaLink()', $joined);
        $this->assertSame(1, substr_count($joined, 'getAttrCtaLink'));
    }

    public function testElementWithoutAttrsGetsNoGetters(): void
    {
        $files = self::renderAll([
            'components' => ['schemas' => ['WysiwygNodeRule' => [
                'title' => 'Rule',
                'type' => 'object',
                'properties' => ['type' => ['type' => 'string', 'enum' => ['rule']]],
                'x-schema-type' => 'wysiwygNode',
            ]]],
        ], 'App\Flyo', 'types');

        $this->assertSame(['Wysiwyg/WysiwygNodeRule.php'], array_keys($files));
        $this->assertStringContainsString("public const IDENTIFIER = 'rule';", $files['Wysiwyg/WysiwygNodeRule.php']);
        $this->assertStringNotContainsString('function ', $files['Wysiwyg/WysiwygNodeRule.php']);
    }

    public function testElementsAreNotReportedAsUnsupported(): void
    {
        $warnings = new Warnings();
        self::renderAll(self::loadDocument('types/wysiwyg'), 'Fixture\Nodes', 'types', $warnings);

        $this->assertStringNotContainsString('wysiwygNode', implode("\n", $warnings->all()));
    }
}
