<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Test;

use Pandoc\Converter\ConverterInterface;
use Pandoc\Options;
use Pandoc\PandocInfo;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
class ConverterMock implements ConverterInterface
{
    private ?Options $lastOptions = null;
    private ?PandocInfo $pandocInfo;

    public function __construct(?PandocInfo $pandocInfo = null)
    {
        $this->pandocInfo = $pandocInfo;
    }

    public function convert(Options $options): void
    {
        $this->lastOptions = $options;
        $output = $options->getOutput();
        file_put_contents('/tmp/mock_converter_debug.log', "ConverterMock: getOutput() = " . var_export($output, true) . "\n", FILE_APPEND);
        $outputDir = $options->getOutputDir();
        $created = false;
        // Always try to create the output file at the path expected by DocumentConverter
        if ($output !== null) {
            file_put_contents('/tmp/mock_converter_debug.log', "CREATING output: $output\n", FILE_APPEND);
            file_put_contents($output, '<h1 id="hello-world">Hello World</h1>');
            $created = true;
        } elseif ($outputDir !== null) {
            file_put_contents('/tmp/mock_converter_debug.log', "CREATING outputDir: $outputDir\n", FILE_APPEND);
            file_put_contents($outputDir, '<h1 id="hello-world">Hello World</h1>');
            $created = true;
        }
        // Remove unnecessary is_iterable() check
        if (!$created) {
            $inputs = $options->getInput();
            foreach ($inputs as $input) {
                if (is_string($input) && preg_match('/pandoc_[^\/]+\\.html$/', $input)) {
                    // Defensive: ensure parent directory exists
                    $dir = dirname($input);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    file_put_contents('/tmp/mock_converter_debug.log', "CREATING input: $input\n", FILE_APPEND);
                    file_put_contents($input, '<h1 id="hello-world">Hello World</h1>');
                    $created = true;
                }
            }
        }
        // Remove unnecessary is_string() check and dead code
        if (!$created) {
            $cmd = (string) $options;
            if (preg_match('/(\/[^\s]+pandoc_[^\n\s]+\.html)/', $cmd, $matches)) {
                $dir = dirname($matches[1]);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents('/tmp/mock_converter_debug.log', "CREATING from string: {$matches[1]}\n", FILE_APPEND);
                file_put_contents($matches[1], '<h1 id="hello-world">Hello World</h1>');
            }
        }
    }

    public function getLastOptions(): ?Options
    {
        return $this->lastOptions;
    }

    public function getPandocInfo(): PandocInfo
    {
        return $this->pandocInfo ??= new PandocInfo(
            '/pandoc',
            '0.0.0',
            '0.0',
        );
    }

    /**
     * @return list<string>
     */
    public function listHighlightLanguages(): array
    {
        return ['html', 'php', 'js'];
    }

    /**
     * @return list<string>
     */
    public function listHighlightStyles(): array
    {
        return ['breezedark', 'haddock', 'kate'];
    }

    /**
     * @return list<string>
     */
    public function listInputFormats(): array
    {
        return ['markdown', 'rst'];
    }

    /**
     * @return list<string>
     */
    public function listOutputFormats(): array
    {
        return ['html', 'docx', 'pdf'];
    }
}
