<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Camt\Type52;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Transaction;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class DocumentTest extends BaseTestCase {
    private function createOpeningBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-14'),
            currency: CurrencyCode::Euro,
            amount: 10000.00,
            type: 'OPBD'
        );
    }

    private function createClosingBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00,
            type: 'CLBD'
        );
    }

    public function testConstructorWithMinimalParameters(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15T10:30:00');

        $document = new Document(
            id: 'RPT-001',
            creationDateTime: $creationDateTime,
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame('RPT-001', $document->getId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertSame('DE89370400440532013000', $document->getAccountIdentifier());
        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
        $this->assertNull($document->getOpeningBalance());
        $this->assertNull($document->getClosingBalance());
        $this->assertEmpty($document->getEntries());
    }

    public function testConstructorWithAllParameters(): void {
        $openingBalance = $this->createOpeningBalance();
        $closingBalance = $this->createClosingBalance();

        $document = new Document(
            id: 'RPT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro,
            accountOwner: 'Test GmbH',
            servicerBic: 'COBADEFFXXX',
            messageId: 'MSG-001',
            sequenceNumber: '001',
            openingBalance: $openingBalance,
            closingBalance: $closingBalance
        );

        $this->assertSame($openingBalance, $document->getOpeningBalance());
        $this->assertSame($closingBalance, $document->getClosingBalance());
        $this->assertSame('Test GmbH', $document->getAccountOwner());
        $this->assertSame('COBADEFFXXX', $document->getServicerBic());
    }

    public function testGetCamtType(): void {
        $document = new Document(
            id: 'RPT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame(CamtType::CAMT052, $document->getCamtType());
    }

    public function testWithOpeningBalance(): void {
        $document = new Document(
            id: 'RPT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $balance = $this->createOpeningBalance();
        $newDocument = $document->withOpeningBalance($balance);

        $this->assertNull($document->getOpeningBalance());
        $this->assertSame($balance, $newDocument->getOpeningBalance());
        $this->assertNotSame($document, $newDocument);
    }

    public function testWithClosingBalance(): void {
        $document = new Document(
            id: 'RPT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $balance = $this->createClosingBalance();
        $newDocument = $document->withClosingBalance($balance);

        $this->assertNull($document->getClosingBalance());
        $this->assertSame($balance, $newDocument->getClosingBalance());
    }

    public function testToXmlGeneratesOutput(): void {
        $document = new Document(
            id: 'RPT-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro,
            openingBalance: $this->createOpeningBalance()
        );

        $xml = $document->toXml();

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('camt.052', $xml);
    }
}
