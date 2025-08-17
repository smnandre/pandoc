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

namespace Pandoc;

interface ConverterInterface
{
    /**
     * Set input content or file path.
     */
    public function input(string $contentOrPath): self;

    /**
     * Set raw input content explicitly.
     */
    public function content(string $content): self;

    /**
     * Set input as a file path explicitly.
     */
    public function file(string $filename): self;

    /**
     * Set input format (optional - can be guessed from file extension or content).
     */
    public function from(?string $format): self;

    /**
     * Set output format (optional - can be guessed from output file extension).
     */
    public function to(?string $format): self;

    /**
     * Set output file or directory (auto-naming for directories).
     */
    public function output(?string $fileOrDir): self;

    /**
     * Set conversion options.
     *
     * @param Options|array<string, mixed> $options
     */
    public function options(Options|array $options): self;

    /**
     * Set single option.
     */
    public function option(string $key, mixed $value): self;

    /**
     * Set single variable.
     */
    public function variable(string $key, string $value): self;

    /**
     * Set multiple variables.
     *
     * @param array<string, string> $variables
     */
    public function variables(array $variables): self;

    /**
     * Set single metadata.
     */
    public function metadata(string $key, string $value): self;

    /**
     * Set multiple metadata.
     *
     * @param array<string, string> $metadata
     */
    public function metadatas(array $metadata): self;

    /**
     * Execute conversion.
     */
    public function convert(): Conversion;

    /**
     * Shortcut for convert()->getContent().
     */
    public function getContent(): string;

    /**
     * Shortcut for convert()->getPath().
     */
    public function getPath(): ?string;

    /**
     * Create fresh converter instance (clean slate).
     */
    public function fresh(): self;
}
