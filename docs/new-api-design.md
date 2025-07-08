# New API Design for Pandoc PHP

## Current Issues with Existing API

1. **Mixed Concerns**: The `Options` class handles both I/O configuration and Pandoc command-line options
2. **No Type Safety**: Format strings are not type-safe
3. **No Return Values**: Convert operations return void, no result information
4. **Limited Metadata Support**: No way to work with document metadata
5. **Complex Options Class**: Single class doing too much

## Proposed New API Structure

### 1. Separate Input/Output Configuration

```php
// New Input/Output classes
class InputSource {
    public static function file(string $path): self
    public static function files(array $paths): self  
    public static function finder(Finder $finder): self
    public static function string(string $content): self
}

class OutputTarget {
    public static function file(string $path): self
    public static function directory(string $dir): self
    public static function string(): self // return as string
    public static function stdout(): self
}
```

### 2. Type-Safe Format Handling

```php
enum InputFormat: string {
    case MARKDOWN = 'markdown';
    case HTML = 'html';
    case DOCX = 'docx';
    case PDF = 'pdf';
    case RST = 'rst';
    case LATEX = 'latex';
    // ... more formats
}

enum OutputFormat: string {
    case HTML = 'html';
    case PDF = 'pdf';
    case DOCX = 'docx';
    case LATEX = 'latex';
    case EPUB = 'epub';
    // ... more formats
}
```

### 3. Conversion Configuration

```php
class ConversionOptions {
    public static function create(): self
    public function tableOfContents(bool $enabled = true): self
    public function numberSections(bool $enabled = true): self
    public function standalone(bool $enabled = true): self
    public function template(string $template): self
    public function variable(string $name, string $value): self
    // ... other pandoc options
}
```

### 4. Result Objects

```php
class ConversionResult {
    public function getOutputPaths(): array
    public function getContent(): ?string // for string outputs
    public function getMetadata(): DocumentMetadata
    public function getDuration(): float
    public function getWarnings(): array
}

class DocumentMetadata {
    public function getTitle(): ?string
    public function getAuthor(): ?string
    public function getDate(): ?\DateTimeInterface
    public function getKeywords(): array
    public function getCustomFields(): array
}
```

### 5. New Main API

```php
class DocumentConverter {
    public static function create(?ConverterInterface $converter = null): self
    
    public function convert(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        ?ConversionOptions $options = null
    ): ConversionResult
    
    public function convertWithMetadata(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        DocumentMetadata $metadata,
        ?ConversionOptions $options = null
    ): ConversionResult
    
    public function batch(): BatchConverter
}

class BatchConverter {
    public function add(InputSource $input, OutputTarget $output, OutputFormat $format): self
    public function withOptions(ConversionOptions $options): self
    public function execute(): BatchResult
}
```

### 6. New Usage Examples

```php
// Simple file conversion
$result = DocumentConverter::create()->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF
);

// With options
$options = ConversionOptions::create()
    ->tableOfContents()
    ->numberSections()
    ->template('custom.latex');
    
$result = DocumentConverter::create()->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF,
    $options
);

// Batch conversion
$converter = DocumentConverter::create();
$results = $converter->batch()
    ->add(InputSource::file('ch1.md'), OutputTarget::file('ch1.html'), OutputFormat::HTML)
    ->add(InputSource::file('ch2.md'), OutputTarget::file('ch2.html'), OutputFormat::HTML)
    ->withOptions($options)
    ->execute();

// String conversion
$result = DocumentConverter::create()->convert(
    InputSource::string('# Hello World'),
    OutputTarget::string(),
    OutputFormat::HTML
);
echo $result->getContent(); // <h1>Hello World</h1>
```

## Backward Compatibility

The existing API will be maintained for backward compatibility:

```php
// Old API still works
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf');
    
Pandoc::create()->convert($options);
```

## Migration Strategy

1. Add new classes alongside existing ones
2. Keep existing API fully functional
3. Add deprecation notices to guide migration
4. Provide migration utilities/helpers
5. Update documentation with examples of both APIs