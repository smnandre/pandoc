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

use Pandoc\BinaryInterface;
use Pandoc\Conversion;

final class MockPandocBinary implements BinaryInterface
{
    private string $mockVersion = '3.5.0';
    /** @var list<string> */
    private array $mockInputFormats = [
        'markdown', 'commonmark', 'gfm', 'html', 'html4', 'html5', 'docx', 'odt',
        'rtf', 'latex', 'rst', 'textile', 'asciidoc', 'org', 'plain', 'json',
    ];
    /** @var list<string> */
    private array $mockOutputFormats = [
        'html', 'html4', 'html5', 'pdf', 'docx', 'odt', 'rtf', 'latex', 'markdown',
        'commonmark', 'gfm', 'rst', 'textile', 'asciidoc', 'org', 'plain', 'json', 'epub',
    ];
    private bool $shouldFail = false;
    private static bool $installed = true;
    private ?string $mockError = null;

    public function __construct(
        private readonly string $binaryPath = '/mock/pandoc',
    ) {
        self::$installed = true;
    }

    public function convert(Conversion $conversion): Conversion
    {
        $startTime = microtime(true);

        if ($this->shouldFail) {
            $duration = microtime(true) - $startTime;

            return $conversion->markExecuted(
                success: false,
                duration: $duration,
                error: $this->mockError ?? 'Mock conversion failed'
            );
        }

        try {
            $content = $this->execute($conversion);
            $duration = microtime(true) - $startTime;

            return $conversion->markExecuted(
                success: true,
                duration: $duration,
                outputPath: $conversion->getPath(),
                outputContent: $content
            );
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            return $conversion->markExecuted(
                success: false,
                duration: $duration,
                error: $e->getMessage()
            );
        }
    }

    public function execute(Conversion $conversion): string
    {
        if ($this->shouldFail) {
            throw new \RuntimeException($this->mockError ?? 'Mock execution failed');
        }

        $input = $conversion->getInputContent();
        if (null === $input && null !== $conversion->inputPath) {
            if (!file_exists($conversion->inputPath) || false === $input = @file_get_contents($conversion->inputPath)) {
                throw new \RuntimeException('Failed to read input file: '.$conversion->inputPath);
            }
        }
        $input = $input ?? '';

        $outputFormat = $conversion->outputFormat ?? $conversion->detectOutputFormat() ?? 'html';

        return $this->mockConvert($input, $outputFormat);
    }

    public function supports(string $from, string $to): bool
    {
        if ($this->shouldFail) {
            throw new \RuntimeException($this->mockError ?? 'Mock binary unavailable');
        }

        return \in_array($from, $this->mockInputFormats, true)
               && \in_array($to, $this->mockOutputFormats, true);
    }

    public function getVersion(): string
    {
        if ($this->shouldFail) {
            throw new \RuntimeException($this->mockError ?? 'Mock binary unavailable');
        }

        return $this->mockVersion;
    }

    /**
     * @return list<string>
     */
    public function getInputFormats(): array
    {
        if ($this->shouldFail) {
            throw new \RuntimeException($this->mockError ?? 'Mock binary unavailable');
        }

        return $this->mockInputFormats;
    }

    /**
     * @return list<string>
     */
    public function getOutputFormats(): array
    {
        if ($this->shouldFail) {
            throw new \RuntimeException($this->mockError ?? 'Mock binary unavailable');
        }

        return $this->mockOutputFormats;
    }

    /**
     * @return list<string>
     */
    public function getHighlightLanguages(): array
    {
        return ['php', 'python', 'javascript', 'bash', 'sql'];
    }

    /**
     * @return list<string>
     */
    public function getHighlightStyles(): array
    {
        return ['tango', 'pygments', 'breezedark', 'zenburn'];
    }

    /**
     * @return array{version: string, binary_path: string, input_formats: list<string>, output_formats: list<string>, highlight_languages: list<string>, highlight_styles: list<string>, loaded_at: int}
     */
    public function getCapabilities(): array
    {
        return [
            'version' => $this->mockVersion,
            'binary_path' => $this->binaryPath,
            'input_formats' => $this->mockInputFormats,
            'output_formats' => $this->mockOutputFormats,
            'highlight_languages' => $this->getHighlightLanguages(),
            'highlight_styles' => $this->getHighlightStyles(),
            'loaded_at' => time(),
        ];
    }

    public function getBinaryPath(): string
    {
        return $this->binaryPath;
    }

    public function isAvailable(): bool
    {
        return !$this->shouldFail;
    }

    public static function isInstalled(): bool
    {
        return self::$installed;
    }

    public function shouldFail(bool $fail = true, ?string $error = null): self
    {
        $this->shouldFail = $fail;
        $this->mockError = $error;
        self::$installed = !$fail;

        return $this;
    }

    public function setVersion(string $version): self
    {
        $this->mockVersion = $version;

        return $this;
    }

    /**
     * @param list<string> $formats
     */
    public function setInputFormats(array $formats): self
    {
        $this->mockInputFormats = $formats;

        return $this;
    }

    /**
     * @param list<string> $formats
     */
    public function setOutputFormats(array $formats): self
    {
        $this->mockOutputFormats = $formats;

        return $this;
    }

    private function mockConvert(string $input, string $outputFormat): string
    {
        return match ($outputFormat) {
            'html', 'html4', 'html5' => $this->mockToHtml($input),
            'markdown', 'commonmark', 'gfm' => $this->mockToMarkdown($input),
            'pdf' => '%PDF-1.4 Mock PDF content',
            'docx' => 'Mock DOCX content',
            'latex' => $this->mockToLatex($input),
            'plain' => strip_tags($input),
            'json' => json_encode(['mock' => 'content', 'input' => $input]) ?: '{}',
            default => "Mock {$outputFormat} output from: {$input}",
        };
    }

    private function mockToHtml(string $input): string
    {
        $html = $input;

        $html = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html) ?? $html;

        $html = preg_replace_callback('/^# (.+)$/m', function ($matches) {
            $id = strtolower(str_replace([' ', '.', '!', '?'], ['-', '', '', ''], $matches[1]));

            return '<h1 id="'.$id.'">'.$matches[1].'</h1>';
        }, $html) ?? $html;

        $html = preg_replace_callback('/^## (.+)$/m', function ($matches) {
            $id = strtolower(str_replace([' ', '.', '!', '?'], ['-', '', '', ''], $matches[1]));

            return '<h2 id="'.$id.'">'.$matches[1].'</h2>';
        }, $html) ?? $html;

        $lines = explode("\n", $html);
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            } elseif (!str_starts_with($line, '<h')) {
                $result[] = '<p>'.$line.'</p>';
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    private function mockToMarkdown(string $input): string
    {
        $markdown = $input;

        $markdown = preg_replace('/<h1[^>]*>(.+?)<\/h1>/', '# $1', $markdown) ?? $markdown;
        $markdown = preg_replace('/<h2[^>]*>(.+?)<\/h2>/', '## $1', $markdown) ?? $markdown;
        $markdown = preg_replace('/<strong>(.+?)<\/strong>/', '**$1**', $markdown) ?? $markdown;
        $markdown = preg_replace('/<p>(.+?)<\/p>/', '$1', $markdown) ?? $markdown;

        return trim($markdown);
    }

    private function mockToLatex(string $input): string
    {
        $latex = $input;
        $latex = preg_replace('/^# (.+)$/m', '\\section{$1}', $latex) ?? $latex;
        $latex = preg_replace('/^## (.+)$/m', '\\subsection{$1}', $latex) ?? $latex;
        $latex = preg_replace('/\*\*(.+?)\*\*/', '\\textbf{$1}', $latex) ?? $latex;

        return "\\documentclass{article}\n\\begin{document}\n{$latex}\n\\end{document}";
    }
}
