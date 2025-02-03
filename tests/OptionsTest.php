<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests;

use Pandoc\Options;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Options::class)]
class OptionsTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $options = Options::create();
        $this->assertInstanceOf(Options::class, $options);
    }

    #[Test]
    public function it_can_set_and_get_input(): void
    {
        $options = Options::create()->setInput(['input.md']);
        $this->assertSame(['input.md'], $options->getInput());
    }

    #[Test]
    public function it_can_set_input_with_deprecated_method(): void
    {
        $options = Options::create()->input('input.md');
        $this->assertEquals('input.md', $options->toArray()['-i']);
    }

    #[Test]
    public function it_can_set_and_get_output(): void
    {
        $options = Options::create()->setOutput('output.pdf');
        $this->assertSame('output.pdf', $options->getOutput());
    }

    #[Test]
    public function it_can_set_output_with_deprecated_method(): void
    {
        $options = Options::create()->output('output.pdf');
        $this->assertEquals('output.pdf', $options->toArray()['-o']);
    }

    #[Test]
    public function it_can_set_and_get_format(): void
    {
        $options = Options::create()->setFormat('pdf');
        $this->assertSame('pdf', $options->getFormat());
    }

    #[Test]
    public function it_can_handle_boolean_options(): void
    {
        $options = Options::create()
            ->tableOfContent()
            ->numberSections();

        $this->assertSame('true', $options->toArray()['--toc']);
        $this->assertSame('true', $options->toArray()['--number-sections']);

        $options = Options::create()
            ->failIfWarnings(false);
        $this->assertArrayNotHasKey('--fail-if-warnings', $options->toArray());
    }

    #[Test]
    public function it_can_convert_to_string(): void
    {
        $options = Options::create()
            ->tableOfContent();

        $this->assertSame('--toc=true', (string) $options);
    }

    #[Test]
    public function it_can_return_options_as_array(): void
    {
        $options = Options::create()
            ->tocDepth(1)
            ->columns(80);

        $this->assertSame([
            '--columns' => '80',
            '--toc-depth' => '1',
        ], $options->toArray());
    }

    #[Test]
    public function it_implements_countable(): void
    {
        $options = Options::create()
            ->tocDepth(1)
            ->columns(80);

        $this->assertSame(2, count($options));
    }

    #[Test]
    public function it_implements_iterator_aggregate(): void
    {
        $options = Options::create()
            ->tocDepth(1)
            ->columns(80);

        $iterator = $options->getIterator();
        $this->assertInstanceOf(\Traversable::class, $iterator);

        $result = [];
        foreach ($iterator as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame([
            '--columns' => '80',
            '--toc-depth' => '1',
        ], $result);
    }

    #[Test]
    public function it_can_set_title_prefix(): void
    {
        $options = Options::create()->titlePrefix('Prefix');
        $this->assertSame('Prefix', $options->toArray()['--title-prefix']);
    }

    #[Test]
    public function it_can_set_to(): void
    {
        $options = Options::create()->to('html');
        $this->assertSame('html', $options->toArray()['--to']);
    }

    #[Test]
    public function it_can_set_toc_option(): void
    {
        $options = Options::create()->toc();
        $this->assertSame('true', $options->toArray()['--toc']);

        $options = Options::create()->toc(false);
        $this->assertArrayNotHasKey('--toc', $options->toArray());
    }

    #[Test]
    public function it_can_set_output_format(): void
    {
        $options = Options::create()->outputFormat('html');
        $this->assertSame('html', $options->toArray()['-t']);
    }

    #[Test]
    public function it_can_set_preserve_tabs(): void
    {
        $options = Options::create()->preserveTabs();
        $this->assertSame('true', $options->toArray()['--preserve-tabs']);

        $options = Options::create()->preserveTabs(false);
        $this->assertArrayNotHasKey('--preserve-tabs', $options->toArray());
    }

    #[Test]
    public function it_can_set_reference_links(): void
    {
        $options = Options::create()->referenceLinks();
        $this->assertSame('true', $options->toArray()['--reference-links']);

        $options = Options::create()->referenceLinks(false);
        $this->assertArrayNotHasKey('--reference-links', $options->toArray());
    }

    #[Test]
    public function it_can_set_sandbox(): void
    {
        $options = Options::create()->sandbox();
        $this->assertSame('true', $options->toArray()['--sandbox']);

        $options = Options::create()->sandbox(false);
        $this->assertArrayNotHasKey('--sandbox', $options->toArray());
    }

    #[Test]
    public function it_can_set_shift_heading_level_by(): void
    {
        $options = Options::create()->shiftHeadingLevelBy(2);
        $this->assertSame('2', $options->toArray()['--shift-heading-level-by']);
    }

    #[Test]
    public function it_can_set_standalone(): void
    {
        $options = Options::create()->standalone();
        $this->assertSame('true', $options->toArray()['--standalone']);

        $options = Options::create()->standalone(false);
        $this->assertArrayNotHasKey('--standalone', $options->toArray());
    }

    #[Test]
    public function it_can_set_strip_comments(): void
    {
        $options = Options::create()->stripComments();
        $this->assertSame('true', $options->toArray()['--strip-comments']);

        $options = Options::create()->stripComments(false);
        $this->assertArrayNotHasKey('--strip-comments', $options->toArray());
    }

    #[Test]
    public function it_can_set_tab_stop(): void
    {
        $options = Options::create()->tabStop(8);
        $this->assertSame('8', $options->toArray()['--tab-stop']);
    }

    #[Test]
    public function it_can_set_list_tables(): void
    {
        $options = Options::create()->listTables();
        $this->assertSame('true', $options->toArray()['--list-tables']);

        $options = Options::create()->listTables(false);
        $this->assertArrayNotHasKey('--list-tables', $options->toArray());
    }

    #[Test]
    public function it_can_set_id_prefix(): void
    {
        $options = Options::create()->idPrefix('test-');
        $this->assertSame('test-', $options->toArray()['--id-prefix']);
    }

    #[Test]
    public function it_can_set_file_scope(): void
    {
        $options = Options::create()->fileScope();
        $this->assertSame('true', $options->toArray()['--file-scope']);

        $options = Options::create()->fileScope(false);
        $this->assertArrayNotHasKey('--file-scope', $options->toArray());
    }

    #[Test]
    public function it_can_set_data_dir(): void
    {
        $options = Options::create()->dataDir('/tmp');
        $this->assertSame('/tmp', $options->toArray()['--data-dir']);
    }

    #[Test]
    public function it_can_set_and_get_output_dir(): void
    {
        $options = Options::create()->setOutputDir('/tmp/output');
        $this->assertSame('/tmp/output', $options->getOutputDir());
    }

    #[Test]
    public function it_can_set_from(): void
    {
        $options = Options::create()->from('markdown');
        $this->assertSame('markdown', $options->toArray()['--from']);
    }

    #[Test]
    public function it_can_set_input(): void
    {
        $options = Options::create()->setInput(['input.md']);
        $this->assertSame(['input.md'], $options->getInput());

        $options = Options::create()->setInput('input.md');
        $this->assertSame(['input.md'], $options->getInput());
    }

    #[Test]
    public function it_can_set_output(): void
    {
        $options = Options::create()->setOutput('output.pdf');
        $this->assertSame('output.pdf', $options->getOutput());
    }

    #[Test]
    public function it_can_merge_options(): void
    {
        $options1 = Options::create()
            ->tableOfContent()
            ->numberSections()
            ->setInput(['input1.md'])
            ->setOutput('output1.html');

        $options2 = Options::create()
            ->failIfWarnings()
            ->shiftHeadingLevelBy(2)
            ->setInput(['input2.md'])
            ->setOutput('output2.pdf')
            ->setFormat('pdf');

        $mergedOptions = $options1->merge($options2);

        $this->assertEquals([
            '--toc' => 'true',
            '--number-sections' => 'true',
            '--fail-if-warnings' => 'true',
            '--shift-heading-level-by' => '2',
        ], $mergedOptions->toArray());

        $this->assertEquals(['input2.md'], $mergedOptions->getInput());
        $this->assertEquals('output2.pdf', $mergedOptions->getOutput());
        $this->assertEquals('pdf', $mergedOptions->getFormat());
    }

    #[Test]
    public function it_preserves_original_options_on_merge(): void
    {
        $options1 = Options::create()
            ->tableOfContent()
            ->setInput(['input1.md']);
        $options2 = Options::create()
            ->failIfWarnings();

        $mergedOptions = $options1->merge($options2);

        $this->assertEquals(['--toc' => 'true'], $options1->toArray());
        $this->assertEquals(['input1.md'], $options1->getInput());
        $this->assertEquals(['--fail-if-warnings' => 'true'], $options2->toArray());
    }

    #[Test]
    public function it_can_merge_output_dir(): void
    {
        $options1 = Options::create()
            ->setOutputDir('/tmp/output1');
        $options2 = Options::create()
            ->setOutputDir('/tmp/output2');

        $mergedOptions = $options1->merge($options2);

        $this->assertEquals('/tmp/output2', $mergedOptions->getOutputDir());
    }
}
