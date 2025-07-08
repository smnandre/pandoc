<div align="center">

# Pandoc PHP - Advanced Document Converter

<p>Markdown - HTML - PDF - DOCX - RST - LaTeX - Reveal Slides</p>

[![PHP Version](https://img.shields.io/badge/%C2%A0php-%3E%3D%208.3-777BB4.svg?logo=php&logoColor=white)](https://github.com/smnandre/pandoc/blob/main/composer.json)
[![CI](https://github.com/smnandre/pandoc/actions/workflows/CI.yaml/badge.svg)](https://github.com/smnandre/pandoc/actions)
[![Release](https://img.shields.io/github/v/release/smnandre/pandoc)](https://github.com/smnandre/pandoc/releases)
[![License](https://img.shields.io/github/license/smnandre/pandoc?color=cc67ff)](https://github.com/smnandre/pandoc/blob/main/LICENSE)
[![Codecov](https://codecov.io/gh/smnandre/pandoc/graph/badge.svg?token=RC8Z6F4SPC)](https://codecov.io/gh/smnandre/pandoc)

This PHP library offers a modern PHP wrapper for the [Pandoc](https://pandoc.org/) document converter.

</div>

## Installation

```bash
composer require smnandre/pandoc
```

## Quick Start

### New API (Recommended)

The new API provides better type safety, separation of concerns, and additional features:

```php
use Pandoc\DocumentConverter;
use Pandoc\Format\{InputFormat, OutputFormat};
use Pandoc\IO\{InputSource, OutputTarget};
use Pandoc\Configuration\ConversionOptions;

// Simple conversion
$converter = DocumentConverter::create();

$result = $converter->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF
);

echo "Converted in {$result->getDuration()}s";
```

### Legacy API

The legacy API is still fully supported:

```php
use Pandoc\Options;
use Pandoc\Pandoc;
use Symfony\Component\Finder\Finder;

// Convert a single file with options:
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf')
    ->tableOfContent();

Pandoc::create()->convert($options);
```

## New API Examples

### String Conversion

```php
// Convert markdown string to HTML
$result = $converter->convert(
    InputSource::string('# Hello World', InputFormat::MARKDOWN),
    OutputTarget::string(),
    OutputFormat::HTML
);

echo $result->getContent(); // <h1>Hello World</h1>
```

### Batch Processing

```php
// Convert multiple files with progress tracking
$batch = $converter->batch()
    ->add(InputSource::file('ch1.md'), OutputTarget::file('ch1.html'), OutputFormat::HTML)
    ->add(InputSource::file('ch2.md'), OutputTarget::file('ch2.html'), OutputFormat::HTML);

$results = $batch->executeWithProgress(function($current, $total, $result, $error) {
    echo "Progress: {$current}/{$total}\n";
});

echo "Success rate: {$results->getSuccessRate()}%";
```

### Directory Batch Conversion

```php
// Convert all markdown files in a directory
$batch = BatchConverter::fromDirectories(
    inputDir: 'docs/',
    outputDir: 'html/',
    format: OutputFormat::HTML,
    pattern: '*.md'
);

$results = $batch->execute();
```

### Advanced Configuration

```php
$options = ConversionOptions::create()
    ->tableOfContents()
    ->numberSections()
    ->standalone()
    ->template('custom.html')
    ->variable('title', 'My Document')
    ->highlightStyle('github');

$result = $converter->convert($input, $output, $format, $options);
```

## Migration from Legacy API

Existing code continues to work unchanged. For new features, consider migrating:

```php
// Easy migration path
$legacyPandoc = Pandoc::create();
$newConverter = $legacyPandoc->newApi();

// Now use the new API features
$result = $newConverter->convert($input, $output, $format);
```

## Key Improvements in New API

- **Type Safety**: Format enums prevent typos and provide IDE support
- **Separation of Concerns**: I/O handling separate from Pandoc configuration  
- **Rich Results**: Get output paths, content, metadata, duration, and warnings
- **Enhanced Batch Processing**: Progress tracking and sophisticated error handling
- **Metadata Support**: Structured document metadata extraction and injection
- **Format Intelligence**: Auto-detection and capability checking
- **Better Developer Experience**: Improved discoverability and IDE support

## Documentation

- **[New API Examples](docs/new-api-examples.md)** - Comprehensive examples using the new API
- **[API Review Summary](docs/api-review-summary.md)** - Detailed analysis and improvements  
- **[New API Design](docs/new-api-design.md)** - Technical design documentation

## Legacy Examples

### Convert multiple files using Finder

```php
$finder = Finder::create()->files()->in('docs')->name('*.md');
$options = Options::create()
    ->setInput($finder)
    ->setOutputDir('output')
    ->setFormat('html');

Pandoc::create()->convert($options);

## Options

### Default Options

```php
$defaultOptions = Options::create()
    ->setFormat('html')
    ->tableOfContent();

$pandoc = Pandoc::create(defaultOptions: $defaultOptions);
```

### Override default options

Use default options, override output for this specific file:

```php
$options = Options::create()->setInput(['chapter1.md'])->setOutput('chapter1.html');
$pandoc->convert($options);
```

### Advanced Options

```php
$defaultOptions = Options::create()
    ->setInput(Finder::create()->files()->in('docs')->name('*.md'))
    ->setOutputDir('output')
    ->setFormat('html');
    
$pandoc = Pandoc::create(null, $defaultOptions);
$pandoc->convert(Options::create()); // Will use default options
```

## Input / Output

### Default Output

```php
$options = Options::create()
    ->setInput(['input.md'])
    ->setFormat('html')
    ->tableOfContent();
Pandoc::create()->convert($options);
```

## Technical Details

### Formats

* list input formats
* list output formats

## Resources

### Pandoc
* https://pandoc.org/
* https://pandoc.org/MANUAL.html

### Pandoc Docker

* https://github.com/pandoc/dockerfiles
* https://github.com/dalibo/pandocker

### GitHub Actions

* https://github.com/pandoc/actions/tree/main/setup


Any contribution is welcome!

### Suggestions

You can suggest new features or improvements by [opening an RFC](https://github.com/smnanre/pandoc/issues/new)
or a [Pull Request](https://github.com/smnanre/pandoc/issues/new) on the GitHub repository of **Pandoc PHP**.

### Issues

If you encounter any issues, please [open an issue](https://github.com/smnanre/pandoc/issues/new) on the GitHub 
repository of **Pandoc PHP**.

### Testing

Before submitting a Pull Request, make sure to run the following commands, and that they all pass.

If you have any questions, feel free to ask on the [GitHub repository](https://github.com/smandre/pandoc) of **Pandoc PHP**.

```bash
php vendor/bin/php-cs-fixer check
php vendor/bin/phpstan analyse
php vendor/bin/phpunit
```

[Pandoc PHP](https://github.com/smnandre/pandoc) is maintained by [Simon André](https://github.com/smnandre)

Pandoc is a project by [John MacFarlane](https://johnmacfarlane.net/) and contributors.## Contributing

## Credits


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


