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

namespace Pandoc\Test;

use Pandoc\Conversion;
use Pandoc\ConverterInterface;
use Pandoc\Options;

/**
 * Mock converter for testing - no external pandoc dependency required
 * Similar to Symfony's MockHttpClient.
 */
final class MockConverter implements ConverterInterface
{
    /**
     * @var array<int, array<string, mixed>> Recorded calls
     *                                       Each call is an associative array with keys:
     *                                       - input
     *                                       - inputFormat
     *                                       - outputFormat
     *                                       - output
     *                                       - options
     *                                       - variables
     *                                       - metadata
     *                                       - timestamp
     */
    private array $calls = [];
    /** @var array<int, Conversion> */
    private array $responses;
    private int $responseIndex = 0;

    private ?string $inputPath = null;
    private ?string $inputContent = null;
    private ?string $inputFormat = null;
    private ?string $outputFormat = null;
    private ?string $output = null;
    /** @var array<string, mixed> */
    private array $options = [];
    /** @var array<string, string> */
    private array $variables = [];
    /** @var array<string, string> */
    private array $metadata = [];

    /**
     * @param array<int, Conversion> $responses Pre-configured responses for testing
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function input(string $contentOrPath): ConverterInterface
    {
        return $this->file($contentOrPath);
    }

    public function content(string $content): ConverterInterface
    {
        $clone = clone $this;
        $clone->inputContent = $content;
        $clone->inputPath = null;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function file(string $filename): ConverterInterface
    {
        $clone = clone $this;
        $clone->inputPath = $filename;
        $clone->inputContent = null;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function from(?string $format): ConverterInterface
    {
        $clone = clone $this;
        $clone->inputFormat = $format;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function to(?string $format): ConverterInterface
    {
        $clone = clone $this;
        $clone->outputFormat = $format;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function output(?string $fileOrDir): ConverterInterface
    {
        $clone = clone $this;
        $clone->output = $fileOrDir;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    /**
     * @param Options|array<string, mixed> $options
     */
    public function options(Options|array $options): ConverterInterface
    {
        $clone = clone $this;

        if ($options instanceof Options) {
            $optionsArray = $options->toArray();
            $clone->options = array_merge($clone->options, $optionsArray['options']);
            $clone->variables = array_merge($clone->variables, $optionsArray['variables']);
            $clone->metadata = array_merge($clone->metadata, $optionsArray['metadata']);
        } else {
            $clone->options = array_merge($clone->options, $options);
        }

        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    /**
     * Alias for options(array $options) to mirror Converter API.
     *
     * @param array<string,mixed> $options
     */
    public function with(array $options): ConverterInterface
    {
        return $this->options($options);
    }

    public function option(string $key, mixed $value): ConverterInterface
    {
        $clone = clone $this;
        $clone->options[$key] = $value;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function variable(string $key, string $value): ConverterInterface
    {
        $clone = clone $this;
        $clone->variables[$key] = $value;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    /**
     * @param array<string, string> $variables
     */
    public function variables(array $variables): ConverterInterface
    {
        $clone = clone $this;
        $clone->variables = array_merge($clone->variables, $variables);
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function metadata(string $key, string $value): ConverterInterface
    {
        $clone = clone $this;
        $clone->metadata[$key] = $value;
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    /**
     * @param array<string, string> $metadata
     */
    public function metadatas(array $metadata): ConverterInterface
    {
        $clone = clone $this;
        $clone->metadata = array_merge($clone->metadata, $metadata);
        $clone->responses = &$this->responses;
        $clone->responseIndex = &$this->responseIndex;
        $clone->calls = &$this->calls;

        return $clone;
    }

    public function convert(): Conversion
    {
        $this->calls[] = [
            'inputPath' => $this->inputPath,
            'inputContent' => $this->inputContent,
            'inputFormat' => $this->inputFormat,
            'outputFormat' => $this->outputFormat,
            'output' => $this->output,
            'options' => $this->options,
            'variables' => $this->variables,
            'metadata' => $this->metadata,
            'timestamp' => microtime(true),
        ];

        if (isset($this->responses[$this->responseIndex])) {
            return $this->responses[$this->responseIndex++];
        }

        $content = $this->generateMockContent();

        $options = Options::create($this->options)
            ->withVariables($this->variables)
            ->withMetadata($this->metadata);

        $conversion = new Conversion(
            inputPath: $this->inputPath,
            inputContent: $this->inputContent,
            inputFormat: $this->inputFormat,
            outputFormat: $this->outputFormat,
            outputPath: $this->output,
            options: $options
        );

        return $conversion->markExecuted(
            success: true,
            duration: 0.001,
            outputPath: $this->output,
            outputContent: $content
        );
    }

    public function getContent(): string
    {
        return $this->convert()->getContent();
    }

    public function getPath(): ?string
    {
        return $this->convert()->getPath();
    }

    public function fresh(): ConverterInterface
    {
        return new self($this->responses);
    }

    public function setResponse(Conversion $response): void
    {
        $this->responses[] = $response;
    }

    /**
     * @param array<int, Conversion> $responses
     */
    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
        $this->responseIndex = 0;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastCall(): ?array
    {
        return end($this->calls) ?: null;
    }

    public function getCallCount(): int
    {
        return \count($this->calls);
    }

    public function reset(): void
    {
        $this->calls = [];
        $this->responses = [];
        $this->responseIndex = 0;
    }

    private function generateMockContent(): string
    {
        return match ($this->outputFormat) {
            'html' => '<h1>Mock HTML Content</h1>',
            'pdf' => '%PDF-1.4 Mock PDF content',
            'docx' => 'PK Mock DOCX content',
            'markdown' => '# Mock Markdown Content',
            default => 'Mock converted content',
        };
    }
}
