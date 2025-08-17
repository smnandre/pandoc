<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc;

/**
 * Type-safe enumeration of Pandoc formats with data-driven implementation.
 */
enum Format: string
{
    // Text formats
    case MARKDOWN = 'markdown';
    case COMMONMARK = 'commonmark';
    case GFM = 'gfm';
    case MARKDOWN_STRICT = 'markdown_strict';
    case PLAIN = 'plain';
    case RST = 'rst';
    case TEXTILE = 'textile';
    case ASCIIDOC = 'asciidoc';
    case ORG = 'org';

    // Markup formats
    case HTML = 'html';
    case HTML4 = 'html4';
    case HTML5 = 'html5';
    case XHTML = 'xhtml';
    case XML = 'xml';
    case DOCBOOK = 'docbook';
    case JATS = 'jats';
    case TEI = 'tei';

    // Document formats
    case DOCX = 'docx';
    case ODT = 'odt';
    case RTF = 'rtf';
    case EPUB = 'epub';
    case EPUB2 = 'epub2';
    case EPUB3 = 'epub3';
    case FB2 = 'fb2';

    // LaTeX formats
    case LATEX = 'latex';
    case BEAMER = 'beamer';
    case CONTEXT = 'context';
    case TEXINFO = 'texinfo';

    // Presentation formats
    case SLIDY = 'slidy';
    case SLIDEOUS = 'slideous';
    case DZSLIDES = 'dzslides';
    case REVEALJS = 'revealjs';
    case S5 = 's5';
    case PPTX = 'pptx';

    // Output-only formats
    case PDF = 'pdf';
    case MS = 'ms';
    case MAN = 'man';
    case JSON = 'json';
    case NATIVE = 'native';
    case IPYNB = 'ipynb';
    case TYPST = 'typst';

    /**
     * Format metadata - all the data-driven logic.
     *
     * @const array<string, array{
     *     ext: string, // Common file extension
     *     desc: string, // Human-readable description
     *     toc: bool, // Supports table of contents
     *     standalone: bool, // Typically requires standalone mode
     *     input: bool // Is this an input format?
     * }>
     */
    private const array METADATA = [
        // Text formats
        'markdown' => ['ext' => 'md', 'desc' => 'Markdown', 'toc' => false, 'standalone' => false, 'input' => true],
        'commonmark' => ['ext' => 'md', 'desc' => 'CommonMark', 'toc' => false, 'standalone' => false, 'input' => true],
        'gfm' => ['ext' => 'md', 'desc' => 'GitHub Flavored Markdown', 'toc' => false, 'standalone' => false, 'input' => true],
        'markdown_strict' => ['ext' => 'md', 'desc' => 'Strict Markdown', 'toc' => false, 'standalone' => false, 'input' => true],
        'plain' => ['ext' => 'txt', 'desc' => 'Plain Text', 'toc' => false, 'standalone' => false, 'input' => true],
        'rst' => ['ext' => 'rst', 'desc' => 'reStructuredText', 'toc' => false, 'standalone' => false, 'input' => true],
        'textile' => ['ext' => 'textile', 'desc' => 'Textile', 'toc' => false, 'standalone' => false, 'input' => true],
        'asciidoc' => ['ext' => 'adoc', 'desc' => 'AsciiDoc', 'toc' => true, 'standalone' => false, 'input' => true],
        'org' => ['ext' => 'org', 'desc' => 'Emacs Org-Mode', 'toc' => true, 'standalone' => false, 'input' => true],

        // Markup formats
        'html' => ['ext' => 'html', 'desc' => 'HTML', 'toc' => true, 'standalone' => false, 'input' => true],
        'html4' => ['ext' => 'html', 'desc' => 'HTML 4', 'toc' => true, 'standalone' => false, 'input' => true],
        'html5' => ['ext' => 'html', 'desc' => 'HTML 5', 'toc' => true, 'standalone' => false, 'input' => true],
        'xhtml' => ['ext' => 'xhtml', 'desc' => 'XHTML', 'toc' => true, 'standalone' => false, 'input' => true],
        'xml' => ['ext' => 'xml', 'desc' => 'XML', 'toc' => false, 'standalone' => false, 'input' => true],
        'docbook' => ['ext' => 'xml', 'desc' => 'DocBook', 'toc' => true, 'standalone' => true, 'input' => true],
        'jats' => ['ext' => 'xml', 'desc' => 'JATS', 'toc' => true, 'standalone' => true, 'input' => true],
        'tei' => ['ext' => 'xml', 'desc' => 'TEI Simple', 'toc' => true, 'standalone' => true, 'input' => true],

        // Document formats
        'docx' => ['ext' => 'docx', 'desc' => 'Microsoft Word (DOCX)', 'toc' => true, 'standalone' => true, 'input' => true],
        'odt' => ['ext' => 'odt', 'desc' => 'OpenDocument Text', 'toc' => true, 'standalone' => true, 'input' => true],
        'rtf' => ['ext' => 'rtf', 'desc' => 'Rich Text Format', 'toc' => false, 'standalone' => true, 'input' => true],
        'epub' => ['ext' => 'epub', 'desc' => 'EPUB', 'toc' => true, 'standalone' => true, 'input' => true],
        'epub2' => ['ext' => 'epub', 'desc' => 'EPUB 2', 'toc' => true, 'standalone' => true, 'input' => true],
        'epub3' => ['ext' => 'epub', 'desc' => 'EPUB 3', 'toc' => true, 'standalone' => true, 'input' => true],
        'fb2' => ['ext' => 'fb2', 'desc' => 'FictionBook2', 'toc' => true, 'standalone' => true, 'input' => true],

        // LaTeX formats
        'latex' => ['ext' => 'tex', 'desc' => 'LaTeX', 'toc' => true, 'standalone' => true, 'input' => true],
        'beamer' => ['ext' => 'tex', 'desc' => 'LaTeX Beamer', 'toc' => true, 'standalone' => true, 'input' => true],
        'context' => ['ext' => 'tex', 'desc' => 'ConTeXt', 'toc' => true, 'standalone' => true, 'input' => true],
        'texinfo' => ['ext' => 'texi', 'desc' => 'Texinfo', 'toc' => true, 'standalone' => true, 'input' => true],

        // Presentation formats
        'slidy' => ['ext' => 'html', 'desc' => 'Slidy Presentation', 'toc' => false, 'standalone' => true, 'input' => false],
        'slideous' => ['ext' => 'html', 'desc' => 'Slideous Presentation', 'toc' => false, 'standalone' => true, 'input' => false],
        'dzslides' => ['ext' => 'html', 'desc' => 'DZSlides Presentation', 'toc' => false, 'standalone' => true, 'input' => false],
        'revealjs' => ['ext' => 'html', 'desc' => 'reveal.js Presentation', 'toc' => true, 'standalone' => true, 'input' => false],
        's5' => ['ext' => 'html', 'desc' => 'S5 Presentation', 'toc' => false, 'standalone' => true, 'input' => false],
        'pptx' => ['ext' => 'pptx', 'desc' => 'PowerPoint (PPTX)', 'toc' => true, 'standalone' => true, 'input' => true],

        // Output-only formats
        'pdf' => ['ext' => 'pdf', 'desc' => 'PDF', 'toc' => true, 'standalone' => true, 'input' => false],
        'ms' => ['ext' => 'ms', 'desc' => 'Groff ms', 'toc' => false, 'standalone' => true, 'input' => false],
        'man' => ['ext' => '1', 'desc' => 'Man page', 'toc' => false, 'standalone' => true, 'input' => false],
        'json' => ['ext' => 'json', 'desc' => 'JSON', 'toc' => false, 'standalone' => false, 'input' => true],
        'native' => ['ext' => 'hs', 'desc' => 'Pandoc native', 'toc' => false, 'standalone' => false, 'input' => true],
        'ipynb' => ['ext' => 'ipynb', 'desc' => 'Jupyter Notebook', 'toc' => false, 'standalone' => false, 'input' => true],
        'typst' => ['ext' => 'typ', 'desc' => 'Typst', 'toc' => true, 'standalone' => true, 'input' => true],
    ];

    /**
     * Map of common file extensions to format values.
     *
     * @const array<string, string>
     */
    private const array EXTENSION_MAP = [
        'md' => 'markdown',
        'markdown' => 'markdown',
        'html' => 'html',
        'htm' => 'html',
        'pdf' => 'pdf',
        'docx' => 'docx',
        'odt' => 'odt',
        'rtf' => 'rtf',
        'tex' => 'latex',
        'rst' => 'rst',
        'txt' => 'plain',
        'epub' => 'epub',
        'json' => 'json',
        'xml' => 'xml',
        'pptx' => 'pptx',
        'adoc' => 'asciidoc',
        'org' => 'org',
        'typ' => 'typst',
    ];

    /**
     * Get format from file path.
     */
    public static function fromFile(string $path): ?self
    {
        $extension = strtolower(pathinfo($path, \PATHINFO_EXTENSION));

        return self::fromExtension($extension);
    }

    /**
     * Get format from file extension.
     */
    public static function fromExtension(string $extension): ?self
    {
        $formatValue = self::EXTENSION_MAP[strtolower($extension)] ?? null;

        return $formatValue ? self::tryFrom($formatValue) : null;
    }

    /**
     * Get common file extension for this format.
     */
    public function getExtension(): string
    {
        return self::METADATA[$this->value]['ext'];
    }

    /**
     * Get human-readable description.
     */
    public function getDescription(): string
    {
        return self::METADATA[$this->value]['desc'];
    }

    /**
     * Check if this format supports table of contents.
     */
    public function supportsToc(): bool
    {
        return self::METADATA[$this->value]['toc'];
    }

    /**
     * Check if this format typically requires standalone mode.
     */
    public function requiresStandalone(): bool
    {
        return self::METADATA[$this->value]['standalone'];
    }

    /**
     * Check if format supports input.
     */
    public function isInputFormat(): bool
    {
        return self::METADATA[$this->value]['input'];
    }

    /**
     * Check if format supports output (most do).
     */
    public function isOutputFormat(): bool
    {
        return true;
    }

    /**
     * Get all input formats.
     *
     * @return Format[]
     */
    public static function inputFormats(): array
    {
        return array_filter(self::cases(), fn ($format) => $format->isInputFormat());
    }

    /**
     * Get all output formats.
     *
     * @return Format[]
     */
    public static function outputFormats(): array
    {
        return self::cases();
    }

    /**
     * Get formats by category.
     *
     * @return Format[]
     */
    public static function getByCategory(string $category): array
    {
        return match ($category) {
            'text' => [self::MARKDOWN, self::COMMONMARK, self::GFM, self::PLAIN, self::RST],
            'web' => [self::HTML, self::HTML4, self::HTML5, self::XHTML],
            'document' => [self::DOCX, self::ODT, self::RTF, self::PDF],
            'ebook' => [self::EPUB, self::EPUB2, self::EPUB3, self::FB2],
            'presentation' => [self::REVEALJS, self::SLIDY, self::PPTX, self::BEAMER],
            'academic' => [self::LATEX, self::BEAMER, self::DOCBOOK, self::JATS, self::TEI],
            default => [],
        };
    }
}
