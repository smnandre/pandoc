<?php

declare(strict_types=1);

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Unit;

use Pandoc\Options;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Options::class)]
final class OptionsTest extends TestCase
{
    public function testCreateWithDefaultOptions(): void
    {
        $options = Options::create();
        $this->assertSame([], $options->getOptions());
        $this->assertSame([], $options->getVariables());
        $this->assertSame([], $options->getMetadata());
    }

    public function testCreateWithCustomOptions(): void
    {
        $options = Options::create(['standalone' => true, 'toc' => true]);
        $this->assertSame(['standalone' => true, 'toc' => true], $options->getOptions());
    }

    public function testMergeCombinesOptionsVariablesAndMetadata(): void
    {
        $options1 = Options::create(['standalone' => true])->variable('author', 'John')->metadata('title', 'Document');
        $options2 = Options::create(['toc' => true])->variable('date', '2023')->metadata('subtitle', 'Example');

        $merged = $options1->merge($options2);

        $this->assertSame(['standalone' => true, 'toc' => true], $merged->getOptions());
        $this->assertSame(['author' => 'John', 'date' => '2023'], $merged->getVariables());
        $this->assertSame(['title' => 'Document', 'subtitle' => 'Example'], $merged->getMetadata());
    }

    public function testToStringGeneratesCorrectCommandLine(): void
    {
        $options = Options::create(['standalone' => true, 'toc' => true])
            ->variable('author', 'John')
            ->metadata('title', 'Document');

        $this->assertSame('--standalone --toc --variable=author:John --metadata=title:Document', (string) $options);
    }

    public function testCountReturnsCorrectNumberOfElements(): void
    {
        $options = Options::create(['standalone' => true, 'toc' => true])
            ->variable('author', 'John')
            ->metadata('title', 'Document');

        $this->assertSame(4, $options->count());
    }

    public function testWithVariablesMergesVariablesCorrectly(): void
    {
        $options = Options::create()->withVariables(['author' => 'John', 'date' => '2023']);
        $this->assertSame(['author' => 'John', 'date' => '2023'], $options->getVariables());
    }

    public function testWithMetadataMergesMetadataCorrectly(): void
    {
        $options = Options::create()->withMetadata(['title' => 'Document', 'subtitle' => 'Example']);
        $this->assertSame(['title' => 'Document', 'subtitle' => 'Example'], $options->getMetadata());
    }

    public function testVariableAddsVariableCorrectly(): void
    {
        $options = Options::create()->variable('author', 'John');
        $this->assertSame(['author' => 'John'], $options->getVariables());
    }

    public function testMetadataAddsMetadataCorrectly(): void
    {
        $options = Options::create()->metadata('title', 'Document');
        $this->assertSame(['title' => 'Document'], $options->getMetadata());
    }

    public function testOptionAddsOptionCorrectly(): void
    {
        $options = Options::create()->option('standalone', true);
        $this->assertSame(['standalone' => true], $options->getOptions());
    }

    public function testMultipleVariablesCanBeAdded(): void
    {
        $options = Options::create()
            ->variable('author', 'John')
            ->variable('date', '2023');

        $this->assertSame(['author' => 'John', 'date' => '2023'], $options->getVariables());
    }

    public function testMultipleMetadataCanBeAdded(): void
    {
        $options = Options::create()
            ->metadata('title', 'Document')
            ->metadata('subtitle', 'Example');

        $this->assertSame(['title' => 'Document', 'subtitle' => 'Example'], $options->getMetadata());
    }

    public function testMultipleOptionsCanBeAdded(): void
    {
        $options = Options::create()
            ->option('standalone', true)
            ->option('toc', true);

        $this->assertSame(['standalone' => true, 'toc' => true], $options->getOptions());
    }

    public function testMergeOverridesExistingValues(): void
    {
        $options1 = Options::create(['standalone' => true])->variable('author', 'John');
        $options2 = Options::create(['standalone' => false])->variable('author', 'Jane');

        $merged = $options1->merge($options2);

        $this->assertSame(['standalone' => false], $merged->getOptions());
        $this->assertSame(['author' => 'Jane'], $merged->getVariables());
    }

    public function testToStringWithEmptyOptions(): void
    {
        $options = Options::create();
        $this->assertSame('', (string) $options);
    }

    public function testToStringWithOnlyVariables(): void
    {
        $options = Options::create()->variable('author', 'John');
        $this->assertSame('--variable=author:John', (string) $options);
    }

    public function testToStringWithOnlyMetadata(): void
    {
        $options = Options::create()->metadata('title', 'Document');
        $this->assertSame('--metadata=title:Document', (string) $options);
    }

    public function testCountWithEmptyOptions(): void
    {
        $options = Options::create();
        $this->assertSame(0, $options->count());
    }

    public function testCountWithOnlyOptions(): void
    {
        $options = Options::create(['standalone' => true, 'toc' => true]);
        $this->assertSame(2, $options->count());
    }

    public function testWithVariablesOverridesExistingVariables(): void
    {
        $options = Options::create()
            ->variable('author', 'John')
            ->withVariables(['author' => 'Jane', 'date' => '2023']);

        $this->assertSame(['author' => 'Jane', 'date' => '2023'], $options->getVariables());
    }

    public function testWithMetadataOverridesExistingMetadata(): void
    {
        $options = Options::create()
            ->metadata('title', 'Old Title')
            ->withMetadata(['title' => 'New Title', 'subtitle' => 'Example']);

        $this->assertSame(['title' => 'New Title', 'subtitle' => 'Example'], $options->getMetadata());
    }

    public function testVariableWithSpecialCharacters(): void
    {
        $options = Options::create()->variable('title', 'Document: A Study');
        $this->assertSame('--variable=title:Document: A Study', (string) $options);
    }

    public function testMetadataWithSpecialCharacters(): void
    {
        $options = Options::create()->metadata('description', 'A document with: special chars');
        $this->assertSame('--metadata=description:A document with: special chars', (string) $options);
    }

    public function testColumnsOptionIsSetCorrectly(): void
    {
        $options = Options::create()->columns(80);
        $this->assertSame(['columns' => 80], $options->getOptions());
    }

    public function testDataDirOptionIsSetCorrectly(): void
    {
        $options = Options::create()->dataDir('/path/to/data');
        $this->assertSame(['data-dir' => '/path/to/data'], $options->getOptions());
    }

    public function testFailIfWarningsOptionDefaultsToTrue(): void
    {
        $options = Options::create()->failIfWarnings();
        $this->assertSame(['fail-if-warnings' => true], $options->getOptions());
    }

    public function testFailIfWarningsOptionCanBeSetToFalse(): void
    {
        $options = Options::create()->failIfWarnings(false);
        $this->assertSame(['fail-if-warnings' => false], $options->getOptions());
    }

    public function testFileScopeOptionDefaultsToTrue(): void
    {
        $options = Options::create()->fileScope();
        $this->assertSame(['file-scope' => true], $options->getOptions());
    }

    public function testIdPrefixOptionIsSetCorrectly(): void
    {
        $options = Options::create()->idPrefix('prefix-');
        $this->assertSame(['id-prefix' => 'prefix-'], $options->getOptions());
    }

    public function testNumberSectionsOptionDefaultsToTrue(): void
    {
        $options = Options::create()->numberSections();
        $this->assertSame(['number-sections' => true], $options->getOptions());
    }

    public function testPreserveTabsOptionDefaultsToTrue(): void
    {
        $options = Options::create()->preserveTabs();
        $this->assertSame(['preserve-tabs' => true], $options->getOptions());
    }

    public function testReferenceLinksOptionDefaultsToTrue(): void
    {
        $options = Options::create()->referenceLinks();
        $this->assertSame(['reference-links' => true], $options->getOptions());
    }

    public function testSandboxOptionDefaultsToTrue(): void
    {
        $options = Options::create()->sandbox();
        $this->assertSame(['sandbox' => true], $options->getOptions());
    }

    public function testShiftHeadingLevelByOptionIsSetCorrectly(): void
    {
        $options = Options::create()->shiftHeadingLevelBy(2);
        $this->assertSame(['shift-heading-level-by' => 2], $options->getOptions());
    }

    public function testStandaloneOptionDefaultsToTrue(): void
    {
        $options = Options::create()->standalone();
        $this->assertSame(['standalone' => true], $options->getOptions());
    }

    public function testStripCommentsOptionDefaultsToTrue(): void
    {
        $options = Options::create()->stripComments();
        $this->assertSame(['strip-comments' => true], $options->getOptions());
    }

    public function testTabStopOptionIsSetCorrectly(): void
    {
        $options = Options::create()->tabStop(4);
        $this->assertSame(['tab-stop' => 4], $options->getOptions());
    }

    public function testTableOfContentOptionDefaultsToTrue(): void
    {
        $options = Options::create()->tableOfContent();
        $this->assertSame(['toc' => true], $options->getOptions());
    }

    public function testTocDepthOptionIsSetCorrectly(): void
    {
        $options = Options::create()->tocDepth(3);
        $this->assertSame(['toc-depth' => 3], $options->getOptions());
    }

    public function testTitlePrefixOptionIsSetCorrectly(): void
    {
        $options = Options::create()->titlePrefix('Chapter ');
        $this->assertSame(['title-prefix' => 'Chapter '], $options->getOptions());
    }

    public function testGetIteratorReturnsTraversable(): void
    {
        $options = Options::create(['standalone' => true, 'toc' => true]);
        $iterator = $options->getIterator();

        $this->assertInstanceOf(\Traversable::class, $iterator);
        $this->assertSame(['standalone' => true, 'toc' => true], iterator_to_array($iterator));
    }

    public function testGetIteratorWithEmptyOptions(): void
    {
        $options = Options::create();
        $iterator = $options->getIterator();

        $this->assertInstanceOf(\Traversable::class, $iterator);
        $this->assertSame([], iterator_to_array($iterator));
    }

    public function testTocOptionDefaultsToTrue(): void
    {
        $options = Options::create()->toc();
        $this->assertSame(['toc' => true], $options->getOptions());
    }

    public function testTocOptionCanBeSetToFalse(): void
    {
        $options = Options::create()->toc(false);
        $this->assertSame(['toc' => false], $options->getOptions());
    }
}
