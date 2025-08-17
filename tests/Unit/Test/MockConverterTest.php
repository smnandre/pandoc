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

namespace Pandoc\Tests\Unit\Test;

use Pandoc\Conversion;
use Pandoc\Options;
use Pandoc\Test\MockConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockConverter::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Options::class)]
final class MockConverterTest extends TestCase
{
    private MockConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new MockConverter();
    }

    public function testConstructorWithPreConfiguredResponses(): void
    {
        $response = new Conversion(outputFormat: 'html');
        $response->markExecuted(true, 0.1, outputContent: 'test content');

        $converter = new MockConverter([$response]);
        $result = $converter->content('# Test')->to('html')->convert();

        $this->assertSame('test content', $result->getContent());
    }

    public function testInputMethod(): void
    {
        $result = $this->converter->input('test.md');

        $this->assertInstanceOf(MockConverter::class, $result);
        $this->assertNotSame($this->converter, $result);
    }

    public function testOptionsWithOptionsObject(): void
    {
        $options = Options::create()
            ->toc(true)
            ->variable('title', 'Test')
            ->metadata('author', 'Test Author');

        $result = $this->converter->options($options);

        $conversion = $result->content('# Test')->to('html')->convert();
        $calls = $result->getCalls();

        $this->assertArrayHasKey('options', $calls[0]);
        $this->assertArrayHasKey('variables', $calls[0]);
        $this->assertArrayHasKey('metadata', $calls[0]);
        $this->assertTrue($calls[0]['options']['toc']);
        $this->assertSame('Test', $calls[0]['variables']['title']);
        $this->assertSame('Test Author', $calls[0]['metadata']['author']);
    }

    public function testOptionsWithArray(): void
    {
        $result = $this->converter->options(['toc' => true, 'standalone' => false]);

        $conversion = $result->content('# Test')->to('html')->convert();
        $calls = $result->getCalls();

        $this->assertTrue($calls[0]['options']['toc']);
        $this->assertFalse($calls[0]['options']['standalone']);
    }

    public function testWithMethod(): void
    {
        $result = $this->converter->with(['toc' => true]);

        $this->assertInstanceOf(MockConverter::class, $result);
    }

    public function testVariablesMethod(): void
    {
        $variables = ['title' => 'Test Title', 'author' => 'Test Author'];
        $result = $this->converter->variables($variables);

        $conversion = $result->content('# Test')->to('html')->convert();
        $calls = $result->getCalls();

        $this->assertSame($variables, $calls[0]['variables']);
    }

    public function testMetadatasMethod(): void
    {
        $metadata = ['date' => '2024-01-01', 'keywords' => 'test'];
        $result = $this->converter->metadatas($metadata);

        $conversion = $result->content('# Test')->to('html')->convert();
        $calls = $result->getCalls();

        $this->assertSame($metadata, $calls[0]['metadata']);
    }

    public function testConvertWithMultipleResponses(): void
    {
        $response1 = new Conversion(outputFormat: 'html');
        $response1->markExecuted(true, 0.1, outputContent: 'first response');

        $response2 = new Conversion(outputFormat: 'pdf');
        $response2->markExecuted(true, 0.2, outputContent: 'second response');

        $this->converter->setResponses([$response1, $response2]);

        $result1 = $this->converter->content('# Test 1')->to('html')->convert();
        $result2 = $this->converter->content('# Test 2')->to('pdf')->convert();

        $this->assertSame('first response', $result1->getContent());
        $this->assertSame('second response', $result2->getContent());
    }

    public function testConvertWithoutPreConfiguredResponses(): void
    {
        $result = $this->converter->content('# Test')->to('html')->convert();

        $this->assertTrue($result->isSuccess());
        $this->assertSame('<h1>Mock HTML Content</h1>', $result->getContent());
    }

    public function testGenerateMockContentWithDifferentFormats(): void
    {
        $testCases = [
            'html' => '<h1>Mock HTML Content</h1>',
            'pdf' => '%PDF-1.4 Mock PDF content',
            'docx' => 'PK Mock DOCX content',
            'markdown' => '# Mock Markdown Content',
            'unknown' => 'Mock converted content',
        ];

        foreach ($testCases as $format => $expected) {
            $result = $this->converter->content('# Test')->to($format)->convert();
            $this->assertSame($expected, $result->getContent());
        }
    }

    public function testGetContentShortcut(): void
    {
        $content = $this->converter->content('# Test')->to('html')->getContent();

        $this->assertSame('<h1>Mock HTML Content</h1>', $content);
    }

    public function testGetPathShortcut(): void
    {
        $path = $this->converter->content('# Test')->to('html')->output('test.html')->getPath();

        $this->assertSame('test.html', $path);
    }

    public function testFreshMethod(): void
    {
        $this->converter->content('# Test')->to('html')->convert();

        $fresh = $this->converter->fresh();

        $this->assertInstanceOf(MockConverter::class, $fresh);
        $this->assertNotSame($this->converter, $fresh);
        $this->assertEmpty($fresh->getCalls());
    }

    public function testSetResponse(): void
    {
        $response = new Conversion(outputFormat: 'html');
        $response->markExecuted(true, 0.1, outputContent: 'custom content');

        $this->converter->setResponse($response);
        $result = $this->converter->content('# Test')->to('html')->convert();

        $this->assertSame('custom content', $result->getContent());
    }

    public function testGetLastCallWithNoCalls(): void
    {
        $lastCall = $this->converter->getLastCall();

        $this->assertNull($lastCall);
    }

    public function testGetLastCallWithCalls(): void
    {
        $this->converter->content('# Test 1')->to('html')->convert();
        $this->converter->content('# Test 2')->to('pdf')->convert();

        $lastCall = $this->converter->getLastCall();

        $this->assertNotNull($lastCall);
        $this->assertSame('# Test 2', $lastCall['inputContent']);
        $this->assertSame('pdf', $lastCall['outputFormat']);
    }

    public function testGetCallCount(): void
    {
        $this->assertSame(0, $this->converter->getCallCount());

        $this->converter->content('# Test 1')->to('html')->convert();
        $this->assertSame(1, $this->converter->getCallCount());

        $this->converter->content('# Test 2')->to('pdf')->convert();
        $this->assertSame(2, $this->converter->getCallCount());
    }

    public function testReset(): void
    {
        $response = new Conversion(outputFormat: 'html');
        $this->converter->setResponse($response);
        $this->converter->content('# Test')->to('html')->convert();

        $this->assertGreaterThan(0, $this->converter->getCallCount());

        $this->converter->reset();

        $this->assertSame(0, $this->converter->getCallCount());
        $this->assertEmpty($this->converter->getCalls());
    }

    public function testClonePreservesResponseReferences(): void
    {
        $response = new Conversion(outputFormat: 'html');
        $response->markExecuted(true, 0.1, outputContent: 'shared response');

        $this->converter->setResponse($response);

        $clone = $this->converter->content('# Test');
        $result = $clone->to('html')->convert();

        $this->assertSame('shared response', $result->getContent());
        $this->assertSame(1, $this->converter->getCallCount());
    }

    public function testAllBuilderMethods(): void
    {
        $result = $this->converter
            ->file('/test/input.md')
            ->from('markdown')
            ->to('html')
            ->output('/test/output.html')
            ->option('toc', true)
            ->variable('title', 'Test Document')
            ->metadata('author', 'Test Author')
            ->convert();

        $calls = $this->converter->getCalls();
        $call = $calls[0];

        $this->assertSame('/test/input.md', $call['inputPath']);
        $this->assertSame('markdown', $call['inputFormat']);
        $this->assertSame('html', $call['outputFormat']);
        $this->assertSame('/test/output.html', $call['output']);
        $this->assertTrue($call['options']['toc']);
        $this->assertSame('Test Document', $call['variables']['title']);
        $this->assertSame('Test Author', $call['metadata']['author']);
        $this->assertIsFloat($call['timestamp']);
    }
}
