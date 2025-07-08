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

use Pandoc\Converter\ConverterInterface;
use Pandoc\Converter\Process\ProcessConverter;

/**
 * Legacy Pandoc converter class.
 *
 * @deprecated Use DocumentConverter for new projects, which provides better type safety,
 *             separation of concerns, and additional features.
 *
 * @see DocumentConverter For the new improved API
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class Pandoc implements ConverterInterface
{
    private ConverterInterface $converter;

    private ?Options $defaultOptions;

    public function __construct(?ConverterInterface $converter = null, ?Options $defaultOptions = null)
    {
        $this->converter = $converter ?? new ProcessConverter();
        $this->defaultOptions = $defaultOptions;
    }

    public static function create(?ConverterInterface $converter = null, ?Options $defaultOptions = null): self
    {
        return new self($converter, $defaultOptions);
    }

    /**
     * @throws Exception\PandocException
     */
    public function convert(Options $options): void
    {
        if ($this->defaultOptions !== null) {
            $options = $this->defaultOptions->merge($options);
            ;
        }

        $this->converter->convert($options);
    }

    public function getPandocInfo(): PandocInfo
    {
        return $this->converter->getPandocInfo();
    }

    /**
     * @return list<string>
     */
    public function listHighlightLanguages(): array
    {
        return $this->converter->listHighlightLanguages();
    }

    /**
     * @return list<string>
     */
    public function listHighlightStyles(): array
    {
        return $this->converter->listHighlightStyles();
    }

    /**
     * @return list<string>
     */
    public function listInputFormats(): array
    {
        return $this->converter->listInputFormats();
    }

    /**
     * @return list<string>
     */
    public function listOutputFormats(): array
    {
        return $this->converter->listOutputFormats();
    }

    /**
     * Get a new DocumentConverter instance for using the improved API.
     *
     * This provides an easy migration path from the legacy API to the new API.
     *
     * @return DocumentConverter
     */
    public function newApi(): DocumentConverter
    {
        return DocumentConverter::create($this->converter);
    }
}
