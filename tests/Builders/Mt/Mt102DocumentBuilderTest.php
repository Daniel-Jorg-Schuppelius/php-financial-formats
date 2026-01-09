<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt102DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt102DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type102\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt102DocumentBuilderTest extends BaseTestCase {
    public function testCreateDocument(): void {
        $document = Mt102DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->remittanceInfo('Rechnung 2024-001')
            ->done()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('REF-001', $document->getSendersReference());
        $this->assertEquals(MtType::MT102, $document->getMtType());
        $this->assertEquals(1, $document->getTransactionCount());
        $this->assertEquals(1000.00, $document->getTotalAmount());
    }

    public function testMultipleTransactions(): void {
        $document = Mt102DocumentBuilder::create('BATCH-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->beginTransaction('TXN-002')
            ->amount(750.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE92200000000123456789', 'Erika Mustermann')
            ->done()
            ->beginTransaction('TXN-003')
            ->amount(250.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE93300000000123456789', 'Hans Meier')
            ->done()
            ->build();

        $this->assertEquals(3, $document->getTransactionCount());
        $this->assertEquals(1500.00, $document->getTotalAmount());
    }

    public function testRequiresOrderingCustomer(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ordering Customer is required');

        Mt102DocumentBuilder::create('REF-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    public function testRequiresValueDate(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value Date is required');

        Mt102DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    public function testRequiresAtLeastOneTransaction(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one transaction is required');

        Mt102DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->build();
    }

    public function testTransactionRequiresBeneficiary(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beneficiary is required');

        Mt102DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->done()
            ->build();
    }

    public function testBankOperationCode(): void {
        $document = Mt102DocumentBuilder::create('REF-001')
            ->bankOperationCode('SPAY')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertEquals('SPAY', $document->getBankOperationCode());
    }

    public function testToField32A(): void {
        $document = Mt102DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->beginTransaction('TXN-001')
            ->amount(1234.56, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertStringContainsString('EUR', $document->toField32A());
        $this->assertStringContainsString('240315', $document->toField32A());
    }
}
