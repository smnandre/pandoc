# Pandoc PHP - Documents Convertor

[![PHP Version](https://img.shields.io/badge/%C2%A0php-%3E%3D%208.3-777BB4.svg?logo=php&logoColor=white)](https://github.com/smnandre/pandoc/blob/main/composer.json)
[![CI](https://github.com/smnandre/pandoc/actions/workflows/CI.yaml/badge.svg)](https://github.com/smnandre/pandoc/actions)
[![Release](https://img.shields.io/github/v/release/smnandre/pandoc)](https://github.com/smnandre/pandoc/releases)
[![License](https://img.shields.io/github/license/smnandre/pandoc?color=cc67ff)](https://github.com/smnandre/pandoc/blob/main/LICENSE)
[![Codecov](https://codecov.io/gh/smnandre/pandoc/graph/badge.svg?token=RC8Z6F4SPC)](https://codecov.io/gh/smnandre/pandoc)

This PHP library offers a modern PHP wrapper for the [Pandoc](https://pandoc.org/) document converter.

```
pandoc --pdf-engine --help 
Argument of --pdf-engine must be one of weasyprint, wkhtmltopdf, pagedjs-cli, prince, pdflatex, lualatex, xelatex, latexmk, tectonic, pdfroff, typst, context
```


## Pandoc PHP

## Installation

```bash
composer require smnandre/pandoc
```

## Basic Usage

### Convert single file


```php
use Pandoc\Options;
use Pandoc\Pandoc;
use Symfony\Component\Finder\Finder;

// 1. Convert a single file with options:
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf')
    ->tableOfContent();

Pandoc::create()->convert($options);
```

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

## Contributing

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

## Credits

[Pandoc PHP](https://github.com/smnandre/pandoc) is maintained by [Simon André](https://github.com/smnandre)

Pandoc is a project by [John MacFarlane](https://johnmacfarlane.net/) and contributors.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


