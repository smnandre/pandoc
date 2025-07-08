<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Format;

/**
 * Enumeration of supported output formats.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum OutputFormat: string
{
    case ASC_II_DOC = 'asciidoc';
    case BEAMER = 'beamer';
    case COMMONMARK = 'commonmark';
    case CONTEXT = 'context';
    case DOCBOOK = 'docbook';
    case DOCBOOK4 = 'docbook4';
    case DOCBOOK5 = 'docbook5';
    case DOCX = 'docx';
    case DOKUWIKI = 'dokuwiki';
    case EPUB = 'epub';
    case EPUB2 = 'epub2';
    case EPUB3 = 'epub3';
    case FB2 = 'fb2';
    case GFM = 'gfm';
    case HADDOCK = 'haddock';
    case HTML = 'html';
    case HTML4 = 'html4';
    case HTML5 = 'html5';
    case ICML = 'icml';
    case IPYNB = 'ipynb';
    case JATS_ARCHIVING = 'jats_archiving';
    case JATS_ARTICLEAUTHORING = 'jats_articleauthoring';
    case JATS_PUBLISHING = 'jats_publishing';
    case JSON = 'json';
    case LATEX = 'latex';
    case MAN = 'man';
    case MARKDOWN = 'markdown';
    case MARKDOWN_MMD = 'markdown_mmd';
    case MARKDOWN_PHPEXTRA = 'markdown_phpextra';
    case MARKDOWN_STRICT = 'markdown_strict';
    case MEDIAWIKI = 'mediawiki';
    case MS = 'ms';
    case MUSE = 'muse';
    case NATIVE = 'native';
    case ODT = 'odt';
    case OPML = 'opml';
    case OPENDOCUMENT = 'opendocument';
    case ORG = 'org';
    case PDF = 'pdf';
    case PLAIN = 'plain';
    case PPTX = 'pptx';
    case RST = 'rst';
    case RTF = 'rtf';
    case REVEALJS = 'revealjs';
    case S5 = 's5';
    case SLIDEOUS = 'slideous';
    case SLIDY = 'slidy';
    case TEI = 'tei';
    case TEXINFO = 'texinfo';
    case TEXTILE = 'textile';
    case XWIKI = 'xwiki';
    case ZIMWIKI = 'zimwiki';

    /**
     * Get the display name for the format.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::ASC_II_DOC => 'AsciiDoc',
            self::BEAMER => 'LaTeX Beamer',
            self::COMMONMARK => 'CommonMark',
            self::CONTEXT => 'ConTeXt',
            self::DOCBOOK => 'DocBook',
            self::DOCBOOK4 => 'DocBook 4',
            self::DOCBOOK5 => 'DocBook 5',
            self::DOCX => 'Word (DOCX)',
            self::DOKUWIKI => 'DokuWiki',
            self::EPUB => 'EPUB',
            self::EPUB2 => 'EPUB 2',
            self::EPUB3 => 'EPUB 3',
            self::FB2 => 'FictionBook2',
            self::GFM => 'GitHub Flavored Markdown',
            self::HADDOCK => 'Haddock',
            self::HTML => 'HTML',
            self::HTML4 => 'HTML 4',
            self::HTML5 => 'HTML 5',
            self::ICML => 'InCopy ICML',
            self::IPYNB => 'Jupyter Notebook',
            self::JATS_ARCHIVING => 'JATS Archiving',
            self::JATS_ARTICLEAUTHORING => 'JATS Article Authoring',
            self::JATS_PUBLISHING => 'JATS Publishing',
            self::JSON => 'JSON',
            self::LATEX => 'LaTeX',
            self::MAN => 'Manual Page',
            self::MARKDOWN => 'Markdown',
            self::MARKDOWN_MMD => 'MultiMarkdown',
            self::MARKDOWN_PHPEXTRA => 'PHP Markdown Extra',
            self::MARKDOWN_STRICT => 'Strict Markdown',
            self::MEDIAWIKI => 'MediaWiki',
            self::MS => 'Groff ms',
            self::MUSE => 'Muse',
            self::NATIVE => 'Native Pandoc',
            self::ODT => 'OpenDocument Text',
            self::OPML => 'OPML',
            self::OPENDOCUMENT => 'OpenDocument',
            self::ORG => 'Org Mode',
            self::PDF => 'PDF',
            self::PLAIN => 'Plain Text',
            self::PPTX => 'PowerPoint (PPTX)',
            self::RST => 'reStructuredText',
            self::RTF => 'Rich Text Format',
            self::REVEALJS => 'reveal.js',
            self::S5 => 'S5',
            self::SLIDEOUS => 'Slideous',
            self::SLIDY => 'Slidy',
            self::TEI => 'TEI Simple',
            self::TEXINFO => 'Texinfo',
            self::TEXTILE => 'Textile',
            self::XWIKI => 'XWiki',
            self::ZIMWIKI => 'ZimWiki',
        };
    }

    /**
     * Get the typical file extension for this format.
     */
    public function getExtension(): string
    {
        return match ($this) {
            self::ASC_II_DOC => 'adoc',
            self::BEAMER => 'tex',
            self::COMMONMARK, self::GFM, self::MARKDOWN, self::MARKDOWN_MMD, 
            self::MARKDOWN_PHPEXTRA, self::MARKDOWN_STRICT => 'md',
            self::CONTEXT => 'tex',
            self::DOCBOOK, self::DOCBOOK4, self::DOCBOOK5 => 'xml',
            self::DOCX => 'docx',
            self::DOKUWIKI => 'txt',
            self::EPUB, self::EPUB2, self::EPUB3 => 'epub',
            self::FB2 => 'fb2',
            self::HADDOCK => 'hs',
            self::HTML, self::HTML4, self::HTML5 => 'html',
            self::ICML => 'icml',
            self::IPYNB => 'ipynb',
            self::JATS_ARCHIVING, self::JATS_ARTICLEAUTHORING, self::JATS_PUBLISHING => 'xml',
            self::JSON => 'json',
            self::LATEX => 'tex',
            self::MAN => '1',
            self::MEDIAWIKI => 'wiki',
            self::MS => 'ms',
            self::MUSE => 'muse',
            self::NATIVE => 'hs',
            self::ODT => 'odt',
            self::OPML => 'opml',
            self::OPENDOCUMENT => 'odt',
            self::ORG => 'org',
            self::PDF => 'pdf',
            self::PLAIN => 'txt',
            self::PPTX => 'pptx',
            self::RST => 'rst',
            self::RTF => 'rtf',
            self::REVEALJS => 'html',
            self::S5, self::SLIDEOUS, self::SLIDY => 'html',
            self::TEI => 'xml',
            self::TEXINFO => 'texi',
            self::TEXTILE => 'textile',
            self::XWIKI => 'wiki',
            self::ZIMWIKI => 'wiki',
        };
    }

    /**
     * Check if this format is a presentation format.
     */
    public function isPresentation(): bool
    {
        return match ($this) {
            self::BEAMER, self::REVEALJS, self::S5, self::SLIDEOUS, self::SLIDY, self::PPTX => true,
            default => false,
        };
    }

    /**
     * Check if this format requires LaTeX for PDF generation.
     */
    public function requiresLatex(): bool
    {
        return match ($this) {
            self::PDF, self::LATEX, self::BEAMER => true,
            default => false,
        };
    }

    /**
     * Get format from file extension.
     */
    public static function fromExtension(string $extension): ?self
    {
        $extension = ltrim(strtolower($extension), '.');
        
        foreach (self::cases() as $format) {
            if ($format->getExtension() === $extension) {
                return $format;
            }
        }
        
        return null;
    }
}