# php-financial-formats

PHP library for parsing, validating and processing financial and banking file formats such as **CAMT**, **MT (SWIFT)**, **PAIN** and **DATEV-FORMATS**.

---

## Scope

This library provides structured building blocks for working with banking and financial file formats, including:

- **ISO 20022 CAMT** (camt.026-039, camt.052, camt.053, camt.054, camt.055, camt.056, camt.057-059, camt.087)
- **SWIFT MT formats** (MT940, MT941, MT942, MT101, MT103)
- **SEPA PAIN formats** (pain.001, pain.002, pain.007, pain.008, pain.009)
- **DATEV accounting formats** with dynamic version discovery (V700+)
- Strongly typed value objects and domain models
- Parsers, builders and converters with clear responsibilities

---

## Architecture

The library follows a layered architecture with shared abstractions:

```
src/
├── Builders/           # Fluent document builders (CAMT, MT, Pain, DATEV)
├── Contracts/          # Abstract base classes (Iso20022ParserAbstract, XmlParserAbstract)
├── Converters/         # Format converters (Camt053ToMt940, DATEV↔BankTransaction)
├── Entities/           # Immutable domain models
├── Enums/              # Typed enums with factory methods
├── Helper/             # Validators and file handlers
├── Parsers/            # Document parsers (CamtParser, PainParser, Mt940DocumentParser)
├── Registries/         # DATEV version discovery
└── Traits/             # Reusable traits
```

### Parser Hierarchy

All ISO 20022 parsers share a common base:

```
XmlParserAbstract (php-common-toolkit)
    └── Iso20022ParserAbstract
            ├── CamtParser
            └── PainParser
```

---

## Usage Examples

### Parsing CAMT.053 (Bank Statement)

```php
use CommonToolkit\FinancialFormats\Parsers\CamtParser;

$document = CamtParser::parseFile('bank-statement.xml');

echo $document->getId();
echo $document->getAccountIdentifier();

foreach ($document->getEntries() as $transaction) {
    echo $transaction->getBookingDate()->format('Y-m-d');
    echo $transaction->getAmount();
    echo $transaction->getCreditDebit()->value;
}
```

### Parsing MT940

```php
use CommonToolkit\FinancialFormats\Parsers\Mt940DocumentParser;

$document = Mt940DocumentParser::fromFile('mt940.sta');

foreach ($document->getTransactions() as $tx) {
    echo $tx->getValutaDate()->format('Y-m-d');
    echo $tx->getAmount();
}
```

### Converting CAMT.053 to MT940

```php
use CommonToolkit\FinancialFormats\Converters\Banking\Camt053ToMt940Converter;

$mt940 = Camt053ToMt940Converter::convert($camt053Document);
```

### Building a Pain.001 Document

```php
use CommonToolkit\FinancialFormats\Builders\Pain001DocumentBuilder;

$builder = new Pain001DocumentBuilder();
$document = $builder
    ->setMessageId('MSG-001')
    ->setInitiatingPartyName('Company GmbH')
    ->addPayment(...)
    ->build();
```

---

## Installation

```bash
composer require dschuppelius/php-financial-formats
```

## Requirements

- PHP 8.1+
- `dschuppelius/php-common-toolkit` (installed automatically)

---

## Supported Formats

| Format | Type | Parser | Builder |
|--------|------|--------|---------|
| CAMT.052 | Bank to Customer Account Report | ✅ | ✅ |
| CAMT.053 | Bank to Customer Statement | ✅ | ✅ |
| CAMT.054 | Bank to Customer Debit/Credit Notification | ✅ | ✅ |
| CAMT.026-039 | Exception & Investigation Messages | ✅ | ❌ |
| CAMT.055-059 | Payment Cancellation/Status | ✅ | ❌ |
| CAMT.087 | Request to Modify Payment | ✅ | ❌ |
| MT940 | Customer Statement | ✅ | ✅ |
| MT941 | Balance Report | ✅ | ✅ |
| MT942 | Interim Transaction Report | ✅ | ✅ |
| MT101 | Request for Transfer | ✅ | ❌ |
| MT103 | Single Customer Credit Transfer | ✅ | ❌ |
| Pain.001 | Customer Credit Transfer Initiation | ✅ | ✅ |
| Pain.002 | Payment Status Report | ✅ | ❌ |
| Pain.007 | Customer Payment Reversal | ✅ | ❌ |
| Pain.008 | Customer Direct Debit Initiation | ✅ | ✅ |
| Pain.009 | Mandate Initiation Request | ✅ | ❌ |
| DATEV | Accounting Export (V700+) | ✅ | ✅ |

---

## License

This project is licensed under the **GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)**.

### What this means

- You may use, modify and distribute this software freely **as long as you comply with the AGPL**.
- If you run this software as a service (e.g. API, web application, SaaS) and make it accessible to **third parties**, you must provide the **complete corresponding source code**, including your modifications, to those users.
- Pure private or internal use **without access by third parties** does **not** trigger any publication obligation.

### Commercial use

If you want to use this library in a **proprietary**, **closed-source** or **commercial environment** without fulfilling the AGPL obligations, a **commercial license is required**.

Please contact the author for commercial licensing terms.

---

## Commercial License

A commercial license allows you to:

- Use this library in proprietary or closed-source software
- Integrate it into commercial products or SaaS platforms
- Avoid AGPL disclosure obligations
- Receive optional support or custom extensions (by agreement)

For commercial licensing inquiries, contact:

**Daniel Joerg Schuppelius**
📧 info@schuppelius.org
