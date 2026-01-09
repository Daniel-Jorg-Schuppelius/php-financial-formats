<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt104DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt104DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type104\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt104DocumentBuilderTest extends BaseTestCase {
    public function testCreateDocument(): void {
        $document = Mt104DocumentBuilder::create('REF-001')
            ->creditor('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE91100000000123456789', 'Max Mustermann')
            ->remittanceInfo('Lastschrift 2024-001')
            ->done()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('REF-001', $document->getSendersReference());
        $this->assertEquals(MtType::MT104, $document->getMtType());
        $this->assertEquals(1, $document->getTransactionCount());
        $this->assertEquals(500.00, $document->getTotalAmount());
    }

    public function testMultipleTransactions(): void {
        $document = Mt104DocumentBuilder::create('BATCH-001')
            ->creditor('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->beginTransaction('TXN-001')
            ->amount(100.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE91100000000123456789', 'Kunde A')
            ->done()
            ->beginTransaction('TXN-002')
            ->amount(200.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE92200000000123456789', 'Kunde B')
            ->done()
            ->beginTransaction('TXN-003')
            ->amount(300.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE93300000000123456789', 'Kunde C')
            ->done()
            ->build();

        $this->assertEquals(3, $document->getTransactionCount());
        $this->assertEquals(600.00, $document->getTotalAmount());
    }

    public function testRequiresCreditor(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Creditor is required');

        Mt104DocumentBuilder::create('REF-001')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    public function testRequiresExecutionDate(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requested Execution Date is required');

        Mt104DocumentBuilder::create('REF-001')
            ->creditor('DE89370400440532013000', 'Firma GmbH')
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    public function testTransactionRequiresDebtor(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Debtor is required');

        Mt104DocumentBuilder::create('REF-001')
            ->creditor('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->done()
            ->build();
    }

    public function testMandateReference(): void {
        $document = Mt104DocumentBuilder::create('REF-001')
            ->mandateReference('MANDATE-123')
            ->creditor('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertEquals('MANDATE-123', $document->getMandateReference());
    }

    public function testEndToEndReference(): void {
        $document = Mt104DocumentBuilder::create('REF-001')
            ->creditor('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE91100000000123456789', 'Max Mustermann')
            ->endToEndReference('E2E-123')
            ->done()
            ->build();

        $transactions = $document->getTransactions();
        $this->assertEquals('E2E-123', $transactions[0]->getEndToEndReference());
    }
}
