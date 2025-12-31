# php-financial-formats

PHP library for parsing, validating and processing financial and banking file formats such as **CAMT**, **MT (SWIFT)**, **PAIN** and **DATEV-FORMATS**.

---

## Scope

This library provides structured building blocks for working with banking and financial file formats, including:

- ISO 20022 CAMT (e.g. camt.052, camt.053, camt.054)
- SWIFT MT formats (e.g. MT940, MT942)
- SEPA PAIN formats (e.g. pain.001, pain.002, pain.008)
- Strongly typed value objects and domain models
- Parsers, documents and builders with clear responsibilities

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

---

## Installation

```bash
composer require dschuppelius/php-financial-formats
