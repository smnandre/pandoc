<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Convertor;

/**
 * @see https://pandoc.org/MANUAL.html#exit-codes
 */
enum ExitCode: int
{
    case Success = 0;
    case IOError = 1;
    case FailOnWarningError = 3;
    case AppError = 4;
    case TemplateError = 5;
    case OptionError = 6;
    case UnknownReaderError = 21;
    case UnknownWriterError = 22;
    case UnsupportedExtensionError = 23;
    case CiteprocError = 24;
    case BibliographyError = 25;
    case EpubSubdirectoryError = 31;
    case PDFError = 43;
    case XMLError = 44;
    case PDFProgramNotFoundError = 47;
    case HttpError = 61;
    case ShouldNeverHappenError = 62;
    case SomeError = 63;
    case ParseError = 64;
    case MakePDFError = 66;
    case SyntaxMapError = 67;
    case FilterError = 83;
    case LuaError = 84;
    case NoScriptingEngine = 89;
    case MacroLoop = 91;
    case UTF8DecodingError = 92;
    case IpynbDecodingError = 93;
    case UnsupportedCharsetError = 94;
    case CouldNotFindDataFileError = 97;
    case CouldNotFindMetadataFileError = 98;
    case ResourceNotFound = 99;

    public function isSuccess(): bool
    {
        return self::Success === $this;
    }

    public function isError(): bool
    {
        return !$this->isSuccess();
    }
}
