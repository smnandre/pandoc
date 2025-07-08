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
 * Enumeration of supported input formats.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum InputFormat: string
{
    case COMMONMARK = 'commonmark';
    case COMMONMARK_X = 'commonmark_x';
    case CREOLE = 'creole';
    case CSV = 'csv';
    case DOCBOOK = 'docbook';
    case DOCX = 'docx';
    case DOKUWIKI = 'dokuwiki';
    case EPUB = 'epub';
    case FB2 = 'fb2';
    case GFM = 'gfm';
    case HADDOCK = 'haddock';
    case HTML = 'html';
    case IPYNB = 'ipynb';
    case JATS = 'jats';
    case JSON = 'json';
    case LATEX = 'latex';
    case MARKDOWN = 'markdown';
    case MARKDOWN_MMD = 'markdown_mmd';
    case MARKDOWN_PHPEXTRA = 'markdown_phpextra';
    case MARKDOWN_STRICT = 'markdown_strict';
    case MEDIAWIKI = 'mediawiki';
    case MAN = 'man';
    case MUSE = 'muse';
    case NATIVE = 'native';
    case ODT = 'odt';
    case OPML = 'opml';
    case ORG = 'org';
    case RST = 'rst';
    case RTF = 'rtf';
    case TEI = 'tei';
    case TEXTILE = 'textile';
    case TIKIWIKI = 'tikiwiki';
    case TWO_COLUMN = 'twocolumn';
    case VIMWIKI = 'vimwiki';
    case WORD2007 = 'word2007';

    /**
     * Get the display name for the format.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::COMMONMARK => 'CommonMark',
            self::COMMONMARK_X => 'CommonMark Extensions',
            self::CREOLE => 'Creole',
            self::CSV => 'CSV',
            self::DOCBOOK => 'DocBook',
            self::DOCX => 'Word (DOCX)',
            self::DOKUWIKI => 'DokuWiki',
            self::EPUB => 'EPUB',
            self::FB2 => 'FictionBook2',
            self::GFM => 'GitHub Flavored Markdown',
            self::HADDOCK => 'Haddock',
            self::HTML => 'HTML',
            self::IPYNB => 'Jupyter Notebook',
            self::JATS => 'JATS',
            self::JSON => 'JSON',
            self::LATEX => 'LaTeX',
            self::MARKDOWN => 'Markdown',
            self::MARKDOWN_MMD => 'MultiMarkdown',
            self::MARKDOWN_PHPEXTRA => 'PHP Markdown Extra',
            self::MARKDOWN_STRICT => 'Strict Markdown',
            self::MEDIAWIKI => 'MediaWiki',
            self::MAN => 'Manual Page',
            self::MUSE => 'Muse',
            self::NATIVE => 'Native Pandoc',
            self::ODT => 'OpenDocument Text',
            self::OPML => 'OPML',
            self::ORG => 'Org Mode',
            self::RST => 'reStructuredText',
            self::RTF => 'Rich Text Format',
            self::TEI => 'TEI Simple',
            self::TEXTILE => 'Textile',
            self::TIKIWIKI => 'TikiWiki',
            self::TWO_COLUMN => 'Two Column',
            self::VIMWIKI => 'VimWiki',
            self::WORD2007 => 'Word 2007',
        };
    }

    /**
     * Check if this format supports the given file extension.
     */
    public function supportsExtension(string $extension): bool
    {
        $extension = ltrim(strtolower($extension), '.');

        return match ($this) {
            self::MARKDOWN, self::GFM, self::COMMONMARK => in_array($extension, ['md', 'markdown', 'mkd', 'mdown'], true),
            self::HTML => in_array($extension, ['html', 'htm'], true),
            self::DOCX => $extension === 'docx',
            self::ODT => $extension === 'odt',
            self::EPUB => $extension === 'epub',
            self::LATEX => in_array($extension, ['tex', 'latex'], true),
            self::RST => in_array($extension, ['rst', 'rest'], true),
            self::IPYNB => $extension === 'ipynb',
            self::JSON => $extension === 'json',
            self::CSV => $extension === 'csv',
            self::RTF => $extension === 'rtf',
            self::ORG => $extension === 'org',
            default => false,
        };
    }

    /**
     * Auto-detect format from file extension.
     */
    public static function fromExtension(string $extension): ?self
    {
        foreach (self::cases() as $format) {
            if ($format->supportsExtension($extension)) {
                return $format;
            }
        }

        return null;
    }
}
