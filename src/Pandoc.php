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

use Pandoc\Convertor\ConvertorInterface;
use Pandoc\Convertor\SystemConvertor;
use Psr\Log\LoggerInterface;

final class Pandoc implements ConvertorInterface
{
    public function __construct(
        private readonly ConvertorInterface $convertor,
        private readonly Options $options,
    ) {}

    public static function create(
        ?Options $options = null,
        ?LoggerInterface $logger = null,
    ): self {
        $convertor = SystemConvertor::create();
        //            $this->convertor = $this->createSystemConvertor();
        // detect pandoc executable
        // $executable = 'pandoc';
        $convertor = SystemConvertor::create($options, $logger);

        return new self($convertor, $options ?? Options::create());
    }

    public function convert(string $input, string $output, ?Options $options = null): string
    {
        return $this->convertor->convert($input, $output, $options ?? $this->options);
    }

    public function listInputFormats(): array
    {
        return $this->convertor->listInputFormats();
    }

    public function listOutputFormats(): array
    {
        return $this->convertor->listOutputFormats();
    }
}
