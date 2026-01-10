# php-financial-formats

PHP library for parsing, validating and processing financial and banking file formats such as **CAMT**, **MT (SWIFT)**, **PAIN** and **DATEV-FORMATS**.

[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-AGPL--3.0--or--later-blue)](LICENSE)

---

## Scope

This library provides structured building blocks for working with banking and financial file formats, including:

- **ISO 20022 CAMT** (camt.026-039, camt.052, camt.053, camt.054, camt.055, camt.056, camt.057-059, camt.087)
- **SWIFT MT formats** (MT940, MT941, MT942, MT101, MT103)
- **SEPA PAIN formats** (pain.001, pain.002, pain.007, pain.008, pain.009-014, pain.017, pain.018)
- **DATEV accounting formats** with dynamic version discovery (V700+)
- Strongly typed value objects and domain models
- Parsers, builders, generators and converters with clear responsibilities

---

## Architecture

The library follows a layered architecture with shared abstractions:

```
src/
├── Builders/           # Fluent document builders (CAMT, MT, Pain, DATEV)
├── Contracts/          # Abstract base classes (ParserAbstract, XmlParserAbstract)
├── Converters/         # Format converters (Camt053ToMt940, DATEV↔BankTransaction)
├── Entities/           # Immutable domain models
├── Enums/              # Typed enums with factory methods
├── Generators/         # XML/SWIFT output generators (CAMT, MT, Pain)
├── Helper/             # Validators and file handlers
├── Parsers/            # Document parsers (CamtParser, PainParser, Mt940DocumentParser)
├── Registries/         # DATEV version discovery
└── Traits/             # Reusable traits
```

### Parser Hierarchy

All ISO 20022 parsers share a common base:

```
XmlParserAbstract (php-common-toolkit)
    └── ParserAbstract
            ├── CamtParser
            └── PainParser
```

---

## Usage Examples

### Parsing CAMT.053 (Bank Statement)

```php
use CommonToolkit\FinancialFormats\Parsers\ISO20022\CamtParser;

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
use CommonToolkit\FinancialFormats\Builders\Pain\Pain001DocumentBuilder;

$builder = new Pain001DocumentBuilder();
$document = $builder
    ->setMessageId('MSG-001')
    ->setInitiatingPartyName('Company GmbH')
    ->addPayment(...)
    ->build();
```

### Building a Pain.008 Direct Debit

```php
use CommonToolkit\FinancialFormats\Builders\Pain\Pain008DocumentBuilder;
use CommonToolkit\FinancialFormats\Enums\Pain\SequenceType;

$document = Pain008DocumentBuilder::createSepaDirectDebit(
    messageId: 'DD-001',
    creditorName: 'Company GmbH',
    creditorIban: 'DE89370400440532013000',
    creditorBic: 'COBADEFFXXX',
    creditorSchemeId: 'DE98ZZZ09999999999',
    debtorName: 'Max Mustermann',
    debtorIban: 'DE91100000000123456789',
    amount: 100.00,
    mandateId: 'MANDATE-001',
    mandateDate: new DateTimeImmutable('2024-01-01'),
    reference: 'Rechnung 2024-001',
    sequenceType: SequenceType::FIRST
);
```

### Building an MT101 Batch Payment

```php
use CommonToolkit\FinancialFormats\Builders\Mt\Mt101DocumentBuilder;
use CommonToolkit\Enums\CurrencyCode;

$document = Mt101DocumentBuilder::create('BATCH-001')
    ->orderingCustomer('DE89370400440532013000', 'Company GmbH')
    ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
    ->beginTransaction('TXN-001')
    ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
    ->beneficiary('DE91100000000123456789', 'Max Mustermann')
    ->remittanceInfo('Payment for Invoice 2025-001')
    ->done()
    ->build();
```

### Building an MT103 Single Transfer

```php
use CommonToolkit\FinancialFormats\Builders\Mt\Mt103DocumentBuilder;

$document = Mt103DocumentBuilder::createSimple(
    sendersReference: 'REF-001',
    orderingAccount: 'DE89370400440532013000',
    orderingName: 'Company GmbH',
    beneficiaryAccount: 'DE91100000000123456789',
    beneficiaryName: 'Max Mustermann',
    amount: 1500.00,
    valueDate: new DateTimeImmutable('2025-03-15'),
    remittanceInfo: 'Payment for services'
);
```

### Creating a DATEV Booking Batch

```php
use CommonToolkit\FinancialFormats\Builders\DATEV\V700\BookingDocumentBuilder;
use CommonToolkit\Enums\CurrencyCode;

$builder = new BookingDocumentBuilder();
$document = $builder
    ->setConsultantNumber(12345)
    ->setClientNumber(1)
    ->setFiscalYearBegin(new DateTimeImmutable('2025-01-01'))
    ->setAccountLength(4)
    ->addBooking(
        amount: 1190.00,
        offsetAccount: 8400,
        bookingDate: new DateTimeImmutable('2025-01-15'),
        bookingText: 'Sales',
        account: 1200,
        currency: CurrencyCode::Euro
    )
    ->build();
```

### DATEV Version Discovery

```php
use CommonToolkit\FinancialFormats\Registries\DATEV\VersionDiscovery;
use CommonToolkit\FinancialFormats\Registries\DATEV\HeaderRegistry;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;

// Get available versions
$versions = VersionDiscovery::getAvailableVersions(); // [700, ...]

// Check format support
if (VersionDiscovery::isFormatSupported(Category::Buchungsstapel, 700)) {
    $definition = HeaderRegistry::getFormatDefinition(Category::Buchungsstapel, 700);
}
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

| Format | Type | Parser | Builder | Generator |
|--------|------|--------|---------|-----------|
| MT101 | Request for Transfer | ✅ | ✅ | ✅ |
| MT103 | Single Customer Credit Transfer | ✅ | ✅ | ✅ |
| MT940 | Customer Statement | ✅ | ✅ | ✅ |
| MT941 | Balance Report | ✅ | ✅ | ✅ |
| MT942 | Interim Transaction Report | ✅ | ✅ | ✅ |
| CAMT.026 | Unable to Apply | ✅ | ✅ | ✅ |
| CAMT.027 | Claim Non Receipt | ✅ | ✅ | ✅ |
| CAMT.028 | Additional Payment Information | ✅ | ✅ | ✅ |
| CAMT.029 | Resolution of Investigation | ✅ | ✅ | ✅ |
| CAMT.030 | Notification of Case Assignment | ✅ | ✅ | ✅ |
| CAMT.031 | Reject Investigation | ✅ | ✅ | ✅ |
| CAMT.033 | Request for Duplicate | ✅ | ✅ | ✅ |
| CAMT.034 | Duplicate | ✅ | ✅ | ✅ |
| CAMT.035 | Proprietary Format Investigation | ✅ | ✅ | ✅ |
| CAMT.036 | Debit Authorisation Response | ✅ | ✅ | ✅ |
| CAMT.037 | Debit Authorisation Request | ✅ | ✅ | ✅ |
| CAMT.038 | Case Status Report Request | ✅ | ✅ | ✅ |
| CAMT.039 | Case Status Report | ✅ | ✅ | ✅ |
| CAMT.052 | Bank to Customer Account Report | ✅ | ✅ | ✅ |
| CAMT.053 | Bank to Customer Statement | ✅ | ✅ | ✅ |
| CAMT.054 | Bank to Customer Debit/Credit Notification | ✅ | ✅ | ✅ |
| CAMT.055 | Customer Payment Cancellation Request | ✅ | ✅ | ✅ |
| CAMT.056 | FI to FI Payment Cancellation Request | ✅ | ✅ | ✅ |
| CAMT.057 | Notification To Receive | ✅ | ✅ | ✅ |
| CAMT.058 | Notification To Receive Cancellation Advice | ✅ | ✅ | ✅ |
| CAMT.059 | Notification To Receive Status Report | ✅ | ✅ | ✅ |
| CAMT.087 | Request to Modify Payment | ✅ | ✅ | ✅ |
| Pain.001 | Customer Credit Transfer Initiation | ✅ | ✅ | ✅ |
| Pain.002 | Payment Status Report | ✅ | ✅ | ✅ |
| Pain.007 | Customer Payment Reversal | ✅ | ✅ | ✅ |
| Pain.008 | Customer Direct Debit Initiation | ✅ | ✅ | ✅ |
| Pain.009 | Mandate Initiation Request | ✅ | ✅ | ✅ |
| Pain.010 | Mandate Amendment Request | ✅ | ✅ | ✅ |
| Pain.011 | Mandate Cancellation Request | ✅ | ✅ | ✅ |
| Pain.012 | Mandate Acceptance Report | ✅ | ✅ | ✅ |
| Pain.013 | Creditor Payment Activation Request | ✅ | ✅ | ✅ |
| Pain.014 | Creditor Payment Activation Status | ✅ | ✅ | ✅ |
| Pain.017 | Mandate Copy Request | ✅ | ✅ | ✅ |
| Pain.018 | Mandate Suspension Request | ✅ | ✅ | ✅ |

### DATEV Formats (V700+)

The system supports dynamic version detection for all DATEV formats. New versions are automatically detected.

| Format | Category | Description | Parser | Builder | Generator |
|--------|-----------|--------------|--------|---------|-----------|
| Buchungsstapel | 21 | Booking batch for financial accounting | ✅ | ✅ | ✅ |
| Debitoren/Kreditoren | 16 | Master data for debtors and creditors | ✅ | ✅ | ✅ |
| Kontenbeschriftungen | 20 | Account labels (chart of accounts) | ✅ | ✅ | ✅ |
| Zahlungsbedingungen | 46 | Payment terms and discount rules | ✅ | ✅ | ✅ |
| Diverse Adressen | 48 | Additional address data | ✅ | ✅ | ✅ |
| Wiederkehrende Buchungen | 65 | Standing orders and recurring bookings | ✅ | ✅ | ✅ |
| Natural-Stapel | 66 | Agricultural/forestry bookings | ✅ | ✅ | ✅ |
| ASCII-Banktransaktionen | - | ASCII processing file (without MetaHeader) | ✅ | ✅ | ✅ |

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
