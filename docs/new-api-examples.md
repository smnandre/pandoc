# Pandoc PHP - New API Design Examples

This document shows examples of both the current (legacy) API and the new improved API design.

## Current (Legacy) API

The current API works but has some limitations:

```php
use Pandoc\Options;
use Pandoc\Pandoc;

// Convert single file
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf')
    ->tableOfContent();

Pandoc::create()->convert($options);
```

**Issues with the legacy API:**
- Mixed I/O and configuration concerns
- No type safety for formats
- No return values from conversions
- Limited metadata support

## New API Design

The new API provides better separation of concerns, type safety, and more features:

### 1. Simple File Conversion

```php
use Pandoc\DocumentConverter;
use Pandoc\Format\{InputFormat, OutputFormat};
use Pandoc\IO\{InputSource, OutputTarget};

// Convert a single file
$converter = DocumentConverter::create();

$result = $converter->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF
);

echo "Converted in {$result->getDuration()}s";
echo "Output: {$result->getOutputPath()}";
```

### 2. String Conversion

```php
// Convert string content
$result = $converter->convert(
    InputSource::string('# Hello World', InputFormat::MARKDOWN),
    OutputTarget::string(),
    OutputFormat::HTML
);

echo $result->getContent(); // <h1>Hello World</h1>
```

### 3. With Configuration Options

```php
use Pandoc\Configuration\ConversionOptions;

$options = ConversionOptions::create()
    ->tableOfContents()
    ->numberSections()
    ->standalone()
    ->template('custom.html');

$result = $converter->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.html'),
    OutputFormat::HTML,
    $options
);
```

### 4. Batch Processing

```php
use Pandoc\BatchConverter;

// Simple batch conversion
$batch = $converter->batch()
    ->add(
        InputSource::file('chapter1.md'),
        OutputTarget::file('chapter1.html'),
        OutputFormat::HTML
    )
    ->add(
        InputSource::file('chapter2.md'),
        OutputTarget::file('chapter2.html'),
        OutputFormat::HTML
    )
    ->withOptions($commonOptions);

$results = $batch->execute();

echo "Processed {$results->getTotalCount()} files";
echo "Success rate: {$results->getSuccessRate()}%";
```

### 5. Batch from Directory

```php
// Convert all markdown files in a directory
$batch = BatchConverter::fromDirectories(
    inputDir: 'docs/',
    outputDir: 'html/',
    format: OutputFormat::HTML,
    pattern: '*.md'
);

$results = $batch->executeWithProgress(function($current, $total, $result, $error) {
    echo "Progress: {$current}/{$total}\n";
    if ($error) {
        echo "Error: {$error}\n";
    }
});
```

### 6. Working with Metadata

```php
use Pandoc\Result\DocumentMetadata;

$metadata = new DocumentMetadata(
    title: 'My Document',
    author: 'John Doe',
    date: new DateTime(),
    keywords: ['pandoc', 'php', 'conversion']
);

$result = $converter->convertWithMetadata(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF,
    $metadata
);

echo "Title: {$result->getMetadata()?->getTitle()}";
```

### 7. Capabilities and Format Detection

```php
// Check converter capabilities
$capabilities = $converter->getCapabilities();

echo "Pandoc version: {$capabilities->getPandocInfo()->getVersion()}";
echo "Supports PDF: " . ($capabilities->supportsOutputFormat(OutputFormat::PDF) ? 'Yes' : 'No');

// Auto-detect formats from file extensions
$inputFormat = InputFormat::fromExtension('md');  // InputFormat::MARKDOWN
$outputFormat = OutputFormat::fromExtension('pdf'); // OutputFormat::PDF

// Get formats for extension
$formats = $capabilities->getInputFormatsForExtension('md');
```

### 8. Advanced Features

```php
// Multiple input sources
$finder = new Symfony\Component\Finder\Finder();
$finder->files()->in('docs/')->name('*.md');

$result = $converter->convert(
    InputSource::finder($finder, InputFormat::MARKDOWN),
    OutputTarget::directory('output/'),
    OutputFormat::HTML
);

// Temporary output for processing
$tempOutput = OutputTarget::temporary('.html');
$result = $converter->convert($input, $tempOutput, OutputFormat::HTML);
$content = file_get_contents($result->getOutputPath());
$tempOutput->cleanup();
```

## Key Improvements

### Type Safety
- **Format Enums**: `InputFormat` and `OutputFormat` enums prevent typos and provide IDE support
- **Type-safe methods**: All methods have proper type hints

### Separation of Concerns
- **InputSource**: Handles input configuration (files, strings, Finder)
- **OutputTarget**: Handles output configuration (files, directories, strings)
- **ConversionOptions**: Pure Pandoc configuration options
- **DocumentConverter**: Main conversion logic

### Better Results
- **ConversionResult**: Get output paths, content, metadata, duration, warnings
- **BatchResult**: Comprehensive batch operation results
- **DocumentMetadata**: Structured metadata handling

### Enhanced Batch Processing
- **Progress tracking**: Real-time progress callbacks
- **Error handling**: Individual job error handling
- **Convenience methods**: Create batches from directories or file lists

### Format Intelligence
- **Auto-detection**: Formats detected from file extensions
- **Capability checking**: Verify format support before conversion
- **Format metadata**: Get display names, required tools, etc.

## Migration Path

The new API is designed to coexist with the legacy API:

```php
// Legacy API still works
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf');
Pandoc::create()->convert($options);

// New API provides additional features
$result = DocumentConverter::create()->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF
);
```

The new API provides significant improvements while maintaining backward compatibility.