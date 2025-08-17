<h1><picture>
<source media="(prefers-color-scheme: light)" srcset="./pandoc.svg" />
<img src="./pandoc.dark.svg" alt="PANDOC PHP - Document Converter" width="100%" />
</picture></h1>
<div align="center">

&nbsp; ![PHP Version](https://img.shields.io/badge/PHP-8.3+-2e7d32?logoColor=6AB76E&labelColor=010)
&nbsp; ![CI](https://img.shields.io/github/actions/workflow/status/smnandre/pandoc/CI.yaml?branch=main&label=Tests&logoColor=white&logoSize=auto&labelColor=010&color=388e3c)
&nbsp; ![Release](https://img.shields.io/github/v/release/smnandre/pandoc?label=Stable&logoColor=white&logoSize=auto&labelColor=010&color=43a047)
&nbsp; [![GitHub Sponsors](https://img.shields.io/github/sponsors/smnandre?logo=github-sponsors&logoColor=66bb6a&logoSize=auto&label=%20Sponsor&labelColor=010&color=a5d6a7)](https://github.com/sponsors/smnandre)
&nbsp; ![License](https://img.shields.io/github/license/smnandre/pandoc?label=License&logoColor=white&logoSize=auto&labelColor=010&color=2e7d32)

PHP [Pandoc](https://pandoc.org/) Document Converter. Typed, tested & mockable.

</div>

---

## Why Pandoc PHP

Replace shell commands with type-safe objects and structured error handling.

```php
$html = Pandoc::convert('# Hello', 'html');     // Static facade for quick tasks

$html = new Converter()                         // ConverterInterface 
    ->file('document.txt')
    ->to('html')
    ->getContent();                             // Traceable, testable, mockable
    
Pandoc::file('reporting.md')                    // Options, variables,  metadata      
    ->option('toc', true)
    ->metadata('author', 'John Doe')
    ->variable('title', 'My Document')
```

- **60+ formats** - **Markdown**, **HTML**, PDF, DOCX, LaTeX, **RST**, EPUB, RTF, and more
- **Dual API Design** - Static `Pandoc` facade for quick tasks, `ConverterInterface` for dependency injection
- **Smart Format Detection** - Auto-detects input/output formats from file extensions and content patterns
- **Unit Testing** - `MockConverter` for tests without Pandoc installation, real Pandoc for integration
- **Production Ready** - PHPStan level 10, full test coverage, rich error handling

---

## Installation

### Requirements

- PHP 8.3 or higher
- Pandoc (see below)

### Pandoc PHP

Install via Composer.

```bash
composer require smnandre/pandoc
```

### Pandoc Binary

Install Pandoc first using your system package manager.

```bash
# Ubuntu/Debian
sudo apt-get install pandoc

# macOS  
brew install pandoc
```

---

## Usage

### Input

Specify source content using string input, file paths, or explicit format overrides when automatic detection fails.

```php
// String content with automatic format detection
$converter = Pandoc::content('# Hello World');

// File input with extension-based format detection
$converter = Pandoc::file('document.md');

// Override automatic format detection when needed
$converter = Pandoc::content($data)->from('markdown');

// Check format compatibility
if (Pandoc::supports('markdown', 'pdf')) {
    // Proceed with conversion
}
```

### Output

Control conversion targets through format specification, file destinations, or directory outputs.

```php
// Get content as string
$htmlContent = Pandoc::file('doc.md')->to('html')->convert()->getContent();

// Save to specific file path
Pandoc::file('doc.md')->to('pdf')->output('reports/final-report.pdf')->convert();

// Save to directory (filename inferred from input)
Pandoc::file('doc.md')->to('pdf')->output('reports/')->convert();

// Save to file (format inferred from extension)
Pandoc::file('doc.md')->output('documentation.html')->convert();
```

### Options

Configure Pandoc behavior using individual options, batch option arrays, template variables, or document metadata.

```php
// Individual option configuration
$converter->option('toc', true)->option('standalone', true);

// Batch option setting
$converter->options(['toc' => true, 'number-sections' => true]);

// Template variables and metadata
$converter->variable('title', 'Report')->variable('grade', 'A+');

// Document metadata for publishing workflows
$converter->metadata('author', 'The Team')->metadata('date', '2025-01-01');
```

### Configuration

Framework integration and custom binary paths for non-standard installations.

```php
// Custom binary path configuration
$binary = PandocBinary::fromPath('/opt/pandoc/bin/pandoc');
Pandoc::setDefaultBinary($binary);

// Check supported formats
Pandoc::getInputFormats();  // Get all supported input formats
Pandoc::getOutputFormats(); // Get all supported output formats
Pandoc::supports('markdown', 'html'); // Check if conversion is supported
```

### Test

Unit testing with `MockConverter` records method calls without requiring Pandoc installation. Integration testing
verifies actual Pandoc functionality.

```php
// Unit testing with MockConverter
class DocumentServiceTest extends TestCase
{
    public function testGenerateReport(): void
    {
        $mock = new MockConverter();
        $service = new DocumentService($mock);
        $service->createReport('# Test');
        
        // Verify calls without external dependencies
        $calls = $mock->getCalls();

        $this->assertCount(1, $calls);
        $this->assertEquals('# Test', $calls[0]['inputContent']);
        $this->assertEquals('html', $calls[0]['outputFormat']);
        $this->assertTrue($calls[0]['options']['standalone']);
    }
}

// Integration testing with real Pandoc
class PandocIntegrationTest extends TestCase
{
    public function testConversion(): void
    {
        if (!Pandoc::isInstalled()) {
            $this->markTestSkipped('Pandoc not installed');
        }
        
        $html = Pandoc::content('# Test')->to('html')->convert()->getContent();
        $this->assertStringContainsString('<h1>Test</h1>', $html);
    }
}
```

---

## Examples

Convert **multiple Markdown files** to **HTML**

```php
$posts = glob('content/*.md');
foreach ($posts as $post) {
    Pandoc::convertFile($post, 'public/'.basename($post, '.md').'.html');
}
```

Generate **academic paper** with **citations and formatting**

```php
$result = Pandoc::file('manuscript.md')
    ->to('pdf')
    ->option('toc', true)
    ->option('bibliography', 'references.bib')
    ->variable('title', 'Research Paper')
    ->variable('author', 'The Team')
    ->convert();
```

Handle **document conversion** in web applications with **error handling**

```php
class DocumentController {
    public function convert(Request $request): Response {
        $result = $this->converter->content($request->getContent())->to('html')->convert();
        
        if (!$result->isSuccess()) {
            throw new BadRequestHttpException($result->getError());
        }
        
        return new Response($result->getContent());
    }
}
```

---

## Troubleshooting

### Pandoc not found

This error occurs when the library cannot find the pandoc executable in your system PATH.

```php
// Solution: Specify custom binary path
use Pandoc\PandocBinary;

$binary = PandocBinary::fromPath('/opt/pandoc/bin/pandoc');
Pandoc::setDefaultBinary($binary);
```

### Format detection

Automatic format detection fails when file extensions are missing or content format is ambiguous.

```php
// Solution: Explicitly specify input and output formats
$result = Pandoc::content($unknownContent)
    ->from('markdown')  // Override input format detection
    ->to('html')        // Ensure output format is clear
    ->convert();
```

### Conversion errors

Pandoc conversion failures provide detailed error messages through the result object.

```php
$result = Pandoc::file('input.md')->to('pdf')->convert();

if (!$result->isSuccess()) {
    // Access detailed error information from Pandoc
    $errorMessage = $result->getError();
    echo "Conversion failed: " . $errorMessage;
}
```

---

## Contributing

Contributions are welcome! Please start by creating an issue to discuss your changes.

---

## Credits

Built on [Pandoc](https://pandoc.org/) by John MacFarlane.

Created and maintained by [Simon André](https://github.com/smnandre).

> [!TIP]
> This library is developed and maintained by a single developer in their free time.
>
> To ensure continued maintenance and improvements, consider [sponsoring development](https://github.com/sponsors/smnandre).

---

## License

MIT License - see [LICENSE](LICENSE) file for details.
