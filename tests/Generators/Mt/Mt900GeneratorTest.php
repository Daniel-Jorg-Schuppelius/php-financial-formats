<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt900GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt900DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt900Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt900GeneratorTest extends BaseTestCase {
    public function testGenerateSimpleMessage(): void {
        $document = Mt900DocumentBuilder::create('DEBIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(1500.00, CurrencyCode::Euro)
            ->build();

        $generator = new Mt900Generator();
        $message = $generator->generate($document);

        // Feldprüfungen
        $this->assertStringContainsString(':20:DEBIT-001', $message);
        $this->assertStringContainsString(':21:REL-001', $message);
        $this->assertStringContainsString(':25:DE89370400440532013000', $message);
        $this->assertStringContainsString(':32A:', $message);
        $this->assertStringContainsString('EUR', $message);
    }

    public function testGenerateWithOptionalFields(): void {
        $document = Mt900DocumentBuilder::create('DEBIT-002', 'REL-002')
            ->account('GB33GSLD04296852369741')
            ->valueDate(new DateTimeImmutable('2024-04-28'))
            ->amount(90.01, CurrencyCode::BritishPound)
            ->dateTimeIndication(new DateTimeImmutable('2024-04-28 08:14:00-0400'))
            ->orderingInstitution('BJKWTN40')
            ->senderToReceiverInfo('/EREF/TEST-REF/CREF/payment-info')
            ->build();

        $generator = new Mt900Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':13D:', $message);
        $this->assertStringContainsString(':52A:BJKWTN40', $message);
        $this->assertStringContainsString(':72:/EREF/TEST-REF/CREF/payment-info', $message);
        $this->assertStringContainsString('GBP', $message);
    }

    public function testField32AFormat(): void {
        $document = Mt900DocumentBuilder::create('FORMAT-TEST', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-06-30'))
            ->amount(12345.67, CurrencyCode::USDollar)
            ->build();

        $generator = new Mt900Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':32A:240630USD12345,67', $message);
    }

    public function testMessageEndsWithMarker(): void {
        $document = Mt900DocumentBuilder::create('END-TEST', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100.00, CurrencyCode::Euro)
            ->build();

        $generator = new Mt900Generator();
        $message = $generator->generate($document);

        $this->assertStringEndsWith('-', trim($message));
    }
}
