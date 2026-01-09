<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt910GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt910DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt910Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt910GeneratorTest extends BaseTestCase {
    public function testGenerateSimpleMessage(): void {
        $document = Mt910DocumentBuilder::create('CREDIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(2500.00, CurrencyCode::Euro)
            ->build();

        $generator = new Mt910Generator();
        $message = $generator->generate($document);

        // Feldprüfungen
        $this->assertStringContainsString(':20:CREDIT-001', $message);
        $this->assertStringContainsString(':21:REL-001', $message);
        $this->assertStringContainsString(':25:DE89370400440532013000', $message);
        $this->assertStringContainsString(':32A:', $message);
        $this->assertStringContainsString('EUR', $message);
    }

    public function testGenerateWithOrderingCustomer(): void {
        $document = Mt910DocumentBuilder::create('CREDIT-002', 'REL-002')
            ->account('107044863')
            ->valueDate(new DateTimeImmutable('2024-03-27'))
            ->amount(13.01, CurrencyCode::USDollar)
            ->orderingCustomer('107044863', 'Petrochem', '29955 Lake Rd')
            ->orderingInstitution('GSCRUS30')
            ->senderToReceiverInfo('/EREF/TEST/FCCY/USD/DACT/107044863')
            ->build();

        $generator = new Mt910Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':50K:', $message);
        $this->assertStringContainsString('Petrochem', $message);
        $this->assertStringContainsString(':52A:GSCRUS30', $message);
        $this->assertStringContainsString(':72:/EREF/TEST/FCCY/USD', $message);
    }

    public function testGenerateWithAllOptionalFields(): void {
        $document = Mt910DocumentBuilder::create('CREDIT-003', 'REL-003')
            ->account('GB33GSLD04296852369741')
            ->valueDate(new DateTimeImmutable('2024-03-25'))
            ->amount(10000.00, CurrencyCode::BritishPound)
            ->dateTimeIndication(new DateTimeImmutable('2024-03-25 13:14:00'))
            ->orderingCustomer('DE11520513735120710131', 'Max Mustermann')
            ->orderingInstitution('COBADEFFXXX')
            ->intermediary('INGBDEFFXXX')
            ->senderToReceiverInfo('/ACC/Additional info')
            ->build();

        $generator = new Mt910Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':13D:', $message);
        $this->assertStringContainsString(':50K:', $message);
        $this->assertStringContainsString(':52A:COBADEFFXXX', $message);
        $this->assertStringContainsString(':56A:INGBDEFFXXX', $message);
        $this->assertStringContainsString(':72:', $message);
    }

    public function testField32AFormat(): void {
        $document = Mt910DocumentBuilder::create('FORMAT-TEST', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-12-31'))
            ->amount(99999.99, CurrencyCode::SwissFranc)
            ->build();

        $generator = new Mt910Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':32A:241231CHF99999,99', $message);
    }

    public function testMessageEndsWithMarker(): void {
        $document = Mt910DocumentBuilder::create('END-TEST', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100.00, CurrencyCode::Euro)
            ->build();

        $generator = new Mt910Generator();
        $message = $generator->generate($document);

        $this->assertStringEndsWith('-', trim($message));
    }
}
