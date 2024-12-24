# Pandoc PHP - Documents Convertor

[![PHP Version](https://img.shields.io/badge/%C2%A0php-%3E%3D%208.3-777BB4.svg?logo=php&logoColor=white)](https://github.com/smnandre/pandoc/blob/main/composer.json)
[![CI](https://github.com/smnandre/pandoc/actions/workflows/CI.yaml/badge.svg)](https://github.com/smnandre/pandoc/actions)
[![Release](https://img.shields.io/github/v/release/smnandre/pandoc)](https://github.com/smnandre/pandoc/releases)
[![License](https://img.shields.io/github/license/smnandre/pandoc?color=cc67ff)](https://github.com/smnandre/pandoc/blob/main/LICENSE)
[![Codecov](https://codecov.io/gh/smnandre/pandoc/graph/badge.svg?token=RC8Z6F4SPC)](https://codecov.io/gh/smnandre/pandoc)

This PHP library offers a modern PHP wrapper for the [Pandoc](https://pandoc.org/) document converter.

```php
use Pandoc\Pandoc;

// Basic usage
$pandoc = Pandoc::create()
$pandoc->convert('input.md', 'output.pdf');

// Advanced usage
$pandoc = Pandoc::create()          // options, logger, cache, filesystems
    ->files('*.md')                 // path, glob, callable, Finder
    ->format('pdf')                 // 'html', 'docx' and 100 others
    ->output('output.pdf')          // path, callable
    ->convert();                    // or ->run() as process
```

## Pandoc PHP


## Installation


```bash
composer require smnandre/pandoc
```

## Basic Usage

## Formats

## Convertors

## Options

## FAQ

## Resources

* https://pandoc.org/
* https://github.com/pandoc/dockerfiles
* https://github.com/pandoc/actions/tree/main/setup
* https://pandoc.org/MANUAL.html
* https://github.com/dalibo/pandocker

## Contributing


## Credits


## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


