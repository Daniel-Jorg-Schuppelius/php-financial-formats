# PHP Financial Formats - AI Development Guide

## Architecture Overview

This is a banking/financial data processing library with a layered architecture:
- **Entities**: Immutable domain models for Banking (MT940/CAMT053/Pain) and DATEV accounting
- **Builders/Parsers**: Document construction and parsing with strict validation
- **Converters**: Format conversion between banking and accounting formats
- **Contracts**: Abstract base classes following PSR patterns with consistent error handling
- **Enums**: Comprehensive financial standards with typed factory methods
- **Registries**: Dynamic DATEV version discovery and management

**Dependency**: Extends `dschuppelius/php-common-toolkit` for CSV processing, helpers, and base utilities.

## Directory Structure (Critical - Preserve!)

The directory structure is essential and must be maintained:
```
src/
├── Builders/           # Fluent document builders
│   ├── DATEV/V700/     # DATEV V700 builders (BookingBatch, DebitorsCreditors, etc.)
│   ├── Camt*Builder    # CAMT 052/053/054 builders
│   ├── Mt9*Builder     # MT940/941/942 builders
│   └── Pain*Builder    # Pain.001/002/008 builders
├── Contracts/          # Abstract base classes and Interfaces
│   ├── Abstracts/      # Base classes (DocumentAbstract, DATEV/Document, etc.)
│   └── Interfaces/     # Interface definitions (DATEV/FieldHeaderInterface, etc.)
├── Converters/         # Format converters
│   ├── Banking/        # Camt053ToMt940Converter
│   └── DATEV/          # BankTransactionTo*, *ToBankTransaction converters
├── Entities/           # Immutable domain models
│   ├── Camt/           # CAMT 052/053/054 entities (Document, Transaction, Balance)
│   ├── Mt1/            # MT101/103 entities
│   ├── Mt9/            # MT940/941/942 entities (Document, Transaction, Purpose)
│   ├── Pain/           # Pain.001/002/008 entities
│   ├── Swift/          # Swift message entities
│   └── DATEV/          # DATEV entities (Documents, Header, MetaHeaderLine)
├── Enums/              # Typed enums with factory methods
│   ├── Camt/           # CAMT-specific enums (Camt052Code, Camt053Code, etc.)
│   └── DATEV/          # DATEV enums (HeaderFields, MetaFields, LockFlags)
├── Helper/             # Utility classes
│   ├── Data/           # CamtValidator, PainValidator (XSD validation)
│   └── FileSystem/     # Mt940File handler
├── Parsers/            # Document parsers
│   ├── CamtParser      # CAMT 052/053/054 parsing
│   ├── Mt940DocumentParser
│   ├── Pain*Parser     # Pain format parsers
│   ├── SwiftMessageParser
│   ├── DatevDocumentParser
│   └── BankTransactionParser
├── Registries/         # Dynamic version management
│   └── DATEV/          # VersionDiscovery, VersionManager, HeaderRegistry
└── Traits/             # Reusable traits (LockFlagTrait)

tests/                  # Mirrors src/ structure exactly
data/xsd/               # XSD schemas for validation (camt/, pain/)
docs/                   # Documentation (DATEV-Versionsverwaltung.md)
tools/                  # Code generation tools (generate-camt-enums.php)
.samples/               # Sample files for testing (Banking/, DATEV/)
```

**Important**: When adding new classes, place them in the correct subdirectory matching their domain and responsibility.

## Critical Architectural Patterns

### Namespace Convention
All classes use the `CommonToolkit\FinancialFormats\` namespace prefix:
```php
namespace CommonToolkit\FinancialFormats\Entities\Camt\Type53;
namespace CommonToolkit\FinancialFormats\Parsers;
namespace CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700;
```

### Entity Design: Immutable Value Objects
Banking entities follow strict patterns:
```php
// Constructor validation with typed enums
new Transaction($date, $valutaDate, $amount, CreditDebit::DEBIT, CurrencyCode::EUR)
// Date handling: DateTimeImmutable with flexible string parsing
// Enum factories: CurrencyCode::fromSymbol('€'), DocumentLinkType::fromString('BEDI')
```

### Data Flow Architecture: Builder → Entity → Parser
1. **Builders** create documents fluently: `Mt940DocumentBuilder->addTransaction()->build()`
2. **Entities** are immutable with validation: MT940/CAMT053 banking transactions, DATEV bookings
3. **Parsers** handle complex formats: banking format regex extraction, DATEV CSV parsing
4. **Key pattern**: Use `match()` expressions for type-safe branching in PHP 8.1+

### DATEV Dynamic Version Discovery
The DATEV module uses runtime discovery for version management:
```php
use CommonToolkit\FinancialFormats\Registries\DATEV\VersionDiscovery;
use CommonToolkit\FinancialFormats\Registries\DATEV\HeaderRegistry;

// Automatic version detection from filesystem
$versions = VersionDiscovery::getAvailableVersions(); // [700, ...]
$definition = HeaderRegistry::get(700);
$formatEnum = HeaderRegistry::getFormatEnum(Category::Buchungsstapel, 700);
```

## Essential Development Patterns

### Enum Design with Traits
Financial enums use consistent patterns:
```php
// Binary state enums (0/1 flags)
enum PostingLock: int { 
    use LockFlagTrait;
    case NONE = 0; case LOCKED = 1; 
}
// Factory methods: fromInt(), fromStringValue(), isLocked()

// Complex enums with validation
CurrencyCode::fromSymbol('€') // → CurrencyCode::EUR
CountryCode::fromAlpha2('DE') // → CountryCode::Germany
```

### DATEV Integration Specifics
German accounting format with rigid structure:
- **MetaHeader**: 31-field header with regex validation patterns
- **Field validation**: Each field has specific regex via `MetaHeaderField::pattern()`
- **DocumentLink**: GUID-based document references with type validation
- **Builder pattern**: Fluent API with automatic field header generation
- **Version Discovery**: New versions auto-detected from `src/Enums/DATEV/HeaderFields/VXX/`

### Banking Format Parsing
```php
// CAMT parsing (052/053/054)
$document = CamtParser::parseFile('statement.xml');
$transactions = $document->getTransactions();

// MT940 parsing
$document = Mt940DocumentParser::fromFile('mt940.sta');
$transactions = $document->getTransactions();

// Format conversion
$mt940 = Camt053ToMt940Converter::convert($camt053);
```

## Development Workflows

### Testing & Validation
```bash
composer test                           # Run all PHPUnit tests
vendor/bin/phpunit --testdox          # Verbose test descriptions
# Test structure mirrors src/ with BaseTestCase setup
```
- **BaseTestCase**: Auto-configures error logging via `LoggerRegistry`
- **Sample files**: Use `.samples/Banking/` and `.samples/DATEV/` for test data
- **XSD Validation**: CAMT/Pain documents can be validated against `data/xsd/` schemas

### Adding a New DATEV Version
1. Create `src/Entities/DATEV/Header/V800/MetaHeaderDefinition.php`
2. Create `src/Enums/DATEV/HeaderFields/V800/*.php` enums implementing `FieldHeaderInterface`
3. The system auto-discovers the new version at runtime

### Adding New Banking Formats
1. Create Entity classes in `src/Entities/{Format}/`
2. Create Parser in `src/Parsers/`
3. Create Builder in `src/Builders/`
4. Add XSD schemas to `data/xsd/` if applicable
5. Add sample files to `.samples/Banking/`

## Critical Implementation Details

- **Date handling**: Prefer `DateTimeImmutable`, flexible constructor parsing (`'ymd'`, `'Ymd'`)
- **Validation**: Use `bcmod()` for IBAN checksum, regex patterns for BIC/BLZ
- **Error handling**: `ErrorLog` trait with PSR-3 logging, custom exceptions from `ERRORToolkit`
- **English locale**: Domain comments and error messages in english for banking compliance
- **Type safety**: Strict types enabled, comprehensive type hints, match expressions
- **XSD Validation**: Optional XML schema validation for CAMT/Pain documents
