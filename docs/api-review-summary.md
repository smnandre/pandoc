# API Review and New Design Summary

## Analysis of Current API

After reviewing the entire codebase of the `smnandre/pandoc` library, I identified several areas for improvement in the current API design:

### Current API Structure

**Main Classes:**
- `Pandoc` - Main facade class implementing `ConverterInterface`
- `Options` - Configuration builder with fluent interface
- `PandocInfo` - Simple data class for pandoc executable information
- `ConverterInterface` - Interface for converters
- `ProcessConverter` - Main implementation using Symfony Process

**Current Usage Pattern:**
```php
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf')
    ->tableOfContent();

Pandoc::create()->convert($options);
```

### Issues Identified

1. **Mixed Concerns**: The `Options` class handles both I/O configuration and Pandoc command-line options
2. **No Type Safety**: Format strings are just strings, no enum or type safety
3. **Limited Return Values**: Convert method returns void, no information about conversion results
4. **Complex Options Class**: Single class doing too much (violates SRP)
5. **Limited Metadata Support**: No way to extract or work with document metadata
6. **Missing Builder Patterns**: Could benefit from more builder patterns for complex configurations
7. **Limited Batch Processing**: Basic support but could be more sophisticated
8. **Basic Error Handling**: Could be more specific about different types of failures

## New API Design

### Key Improvements

1. **Separation of Concerns**: Split I/O handling from pandoc configuration
2. **Type Safety**: Enums for input/output formats
3. **Result Objects**: Rich return values with metadata, duration, warnings
4. **Better Batch Processing**: Sophisticated batch operations with progress tracking
5. **Metadata Support**: Structured document metadata handling
6. **Fluent Interface**: Improved discoverability and usability

### New Class Structure

#### Format Enums
- `InputFormat` - Type-safe input format enumeration (35+ formats)
- `OutputFormat` - Type-safe output format enumeration (45+ formats)

#### I/O Classes
- `InputSource` - Handles input configuration (files, strings, Finder, stdin)
- `OutputTarget` - Handles output configuration (files, directories, strings, stdout)
- `InputSourceType` / `OutputTargetType` - Enums for source/target types

#### Configuration
- `ConversionOptions` - Pure Pandoc configuration options (separate from I/O)

#### Results
- `ConversionResult` - Rich result object with paths, content, metadata, duration, warnings
- `BatchResult` - Comprehensive batch operation results
- `DocumentMetadata` - Structured metadata with title, author, date, keywords, custom fields

#### Main API
- `DocumentConverter` - New main entry point with improved API
- `BatchConverter` - Sophisticated batch processing
- `ConverterCapabilities` - Introspection and capability checking

### Usage Examples

#### Simple Conversion
```php
use Pandoc\DocumentConverter;
use Pandoc\Format\{InputFormat, OutputFormat};
use Pandoc\IO\{InputSource, OutputTarget};

$converter = DocumentConverter::create();

$result = $converter->convert(
    InputSource::file('input.md'),
    OutputTarget::file('output.pdf'),
    OutputFormat::PDF
);

echo "Converted in {$result->getDuration()}s";
```

#### String Conversion
```php
$result = $converter->convert(
    InputSource::string('# Hello World', InputFormat::MARKDOWN),
    OutputTarget::string(),
    OutputFormat::HTML
);

echo $result->getContent(); // <h1>Hello World</h1>
```

#### Batch Processing
```php
$batch = $converter->batch()
    ->add(InputSource::file('ch1.md'), OutputTarget::file('ch1.html'), OutputFormat::HTML)
    ->add(InputSource::file('ch2.md'), OutputTarget::file('ch2.html'), OutputFormat::HTML)
    ->withOptions($options);

$results = $batch->executeWithProgress(function($current, $total, $result, $error) {
    echo "Progress: {$current}/{$total}\n";
});

echo "Success rate: {$results->getSuccessRate()}%";
```

#### Directory Batch
```php
$batch = BatchConverter::fromDirectories(
    inputDir: 'docs/',
    outputDir: 'html/',
    format: OutputFormat::HTML,
    pattern: '*.md'
);

$results = $batch->execute();
```

#### Metadata Handling
```php
use Pandoc\Result\DocumentMetadata;

$metadata = new DocumentMetadata(
    title: 'My Document',
    author: 'John Doe',
    date: new DateTime(),
    keywords: ['pandoc', 'php']
);

$result = $converter->convertWithMetadata($input, $output, $format, $metadata);
```

#### Capabilities
```php
$capabilities = $converter->getCapabilities();

echo "Pandoc version: {$capabilities->getPandocInfo()->getVersion()}";
echo "Supports PDF: " . ($capabilities->supportsOutputFormat(OutputFormat::PDF) ? 'Yes' : 'No');

// Auto-detect formats
$inputFormat = InputFormat::fromExtension('md');
$formats = $capabilities->getInputFormatsForExtension('md');
```

## Backward Compatibility

The new API is designed to coexist with the legacy API:

1. **Legacy API preserved**: All existing functionality remains unchanged
2. **Migration helper**: Added `newApi()` method to legacy `Pandoc` class
3. **Deprecation notices**: Added deprecation notice to legacy `Pandoc` class
4. **Gradual migration**: Users can migrate incrementally

```php
// Legacy API still works
$options = Options::create()
    ->setInput(['input.md'])
    ->setOutput('output.pdf')
    ->setFormat('pdf');
Pandoc::create()->convert($options);

// Easy migration path
$newConverter = Pandoc::create()->newApi();
$result = $newConverter->convert($input, $output, $format);
```

## Benefits of New API

### Type Safety
- **Format Enums**: Prevent typos, provide IDE support
- **Strong typing**: All methods properly typed

### Better Architecture
- **Single Responsibility**: Each class has a clear purpose
- **Separation of Concerns**: I/O separate from configuration
- **Immutable objects**: Thread-safe and predictable

### Enhanced Features
- **Rich results**: Get detailed information about conversions
- **Batch processing**: Sophisticated batch operations
- **Progress tracking**: Real-time feedback for long operations
- **Error handling**: Individual job error handling
- **Metadata support**: Structured document metadata

### Developer Experience
- **IDE support**: Better autocomplete and type hints
- **Discoverability**: Clear method names and structure
- **Documentation**: Rich docblocks and examples
- **Testing**: Comprehensive test coverage

## Implementation Quality

### Code Quality
- **PSR standards**: Follows PHP-FIG standards
- **Type hints**: Full PHP 8.3+ type system usage
- **Enums**: Modern PHP 8.1+ enum usage
- **Immutability**: Value objects are immutable
- **Error handling**: Comprehensive exception handling

### Testing
- **Unit tests**: Tests for all new components
- **Edge cases**: Validation of edge cases
- **Mock support**: Easy testing with mocks

### Documentation
- **Comprehensive examples**: Both legacy and new API
- **Migration guide**: Clear upgrade path
- **API documentation**: Rich docblocks

## Recommendations

1. **Adopt New API**: For new projects, use `DocumentConverter`
2. **Gradual Migration**: Existing projects can migrate incrementally
3. **Deprecation Timeline**: Consider deprecating legacy API in future major version
4. **Enhanced Testing**: Add integration tests with real pandoc executable
5. **Performance**: Consider caching format capabilities for better performance

## Files Created/Modified

### New Files
- `src/Format/InputFormat.php` - Input format enumeration
- `src/Format/OutputFormat.php` - Output format enumeration
- `src/IO/InputSource.php` - Input source handling
- `src/IO/OutputTarget.php` - Output target handling
- `src/IO/InputSourceType.php` / `OutputTargetType.php` - Type enums
- `src/Configuration/ConversionOptions.php` - Pure configuration options
- `src/Result/ConversionResult.php` - Conversion result object
- `src/Result/BatchResult.php` - Batch result object
- `src/Result/DocumentMetadata.php` - Document metadata object
- `src/DocumentConverter.php` - New main API entry point
- `src/BatchConverter.php` - Batch processing
- `src/ConverterCapabilities.php` - Capability introspection
- `docs/new-api-design.md` - Design documentation
- `docs/new-api-examples.md` - Usage examples
- `tests/NewApi/*` - Test files for new API

### Modified Files
- `src/Pandoc.php` - Added deprecation notice and migration helper

The new API provides significant improvements while maintaining full backward compatibility, offering a clear upgrade path for existing users and a modern, type-safe experience for new users.