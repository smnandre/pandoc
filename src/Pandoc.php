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
}
