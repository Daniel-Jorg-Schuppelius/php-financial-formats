<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Camt\Type53;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Reference;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Transaction;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die CAMT.053 Document Entity.
 */
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

    private function createTransaction(float $amount, CreditDebit $creditDebit): Transaction {
        return new Transaction(
            bookingDate: new DateTimeImmutable('2025-01-15'),
            valutaDate: new DateTimeImmutable('2025-01-15'),
            amount: $amount,
            currency: CurrencyCode::Euro,
            creditDebit: $creditDebit,
            reference: new Reference(endToEndId: 'TEST-' . $amount)
        );
    }

    public function testConstructorWithMinimalParameters(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15T10:30:00');

        $document = new Document(
            id: 'STMT-001',
            creationDateTime: $creationDateTime,
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame('STMT-001', $document->getId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertSame('DE89370400440532013000', $document->getAccountIdentifier());
        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
        $this->assertNull($document->getAccountOwner());
        $this->assertNull($document->getServicerBic());
        $this->assertNull($document->getOpeningBalance());
        $this->assertNull($document->getClosingBalance());
        $this->assertEmpty($document->getEntries());
    }

    public function testConstructorWithAllParameters(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15T10:30:00');
        $openingBalance = $this->createOpeningBalance();
        $closingBalance = $this->createClosingBalance();

        $document = new Document(
            id: 'STMT-001',
            creationDateTime: $creationDateTime,
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro,
            accountOwner: 'Test GmbH',
            servicerBic: 'COBADEFFXXX',
            messageId: 'MSG-001',
            sequenceNumber: '001',
            openingBalance: $openingBalance,
            closingBalance: $closingBalance
        );

        $this->assertSame('Test GmbH', $document->getAccountOwner());
        $this->assertSame('COBADEFFXXX', $document->getServicerBic());
        $this->assertSame('MSG-001', $document->getMessageId());
        $this->assertSame('001', $document->getSequenceNumber());
        $this->assertSame($openingBalance, $document->getOpeningBalance());
        $this->assertSame($closingBalance, $document->getClosingBalance());
    }

    public function testGetCamtType(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame(CamtType::CAMT053, $document->getCamtType());
    }

    public function testAddEntry(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $transaction1 = $this->createTransaction(100.00, CreditDebit::CREDIT);
        $transaction2 = $this->createTransaction(50.00, CreditDebit::DEBIT);

        $document->addEntry($transaction1);
        $document->addEntry($transaction2);

        $entries = $document->getEntries();
        $this->assertCount(2, $entries);
        $this->assertSame($transaction1, $entries[0]);
        $this->assertSame($transaction2, $entries[1]);
    }

    public function testWithOpeningBalance(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $balance = $this->createOpeningBalance();
        $newDocument = $document->withOpeningBalance($balance);

        // Original bleibt unverändert (Immutabilität)
        $this->assertNull($document->getOpeningBalance());
        $this->assertSame($balance, $newDocument->getOpeningBalance());
        $this->assertNotSame($document, $newDocument);
    }

    public function testWithClosingBalance(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $balance = $this->createClosingBalance();
        $newDocument = $document->withClosingBalance($balance);

        // Original bleibt unverändert (Immutabilität)
        $this->assertNull($document->getClosingBalance());
        $this->assertSame($balance, $newDocument->getClosingBalance());
        $this->assertNotSame($document, $newDocument);
    }

    public function testConstructorWithStringDate(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: '2025-01-15T10:30:00',
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame('2025-01-15', $document->getCreationDateTime()->format('Y-m-d'));
    }

    public function testConstructorWithStringCurrency(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: 'EUR'
        );

        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
    }

    public function testToXmlGeneratesOutput(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro,
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $document->addEntry($this->createTransaction(100.00, CreditDebit::CREDIT));

        $xml = $document->toXml();

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('camt.053', $xml);
        $this->assertStringContainsString('STMT-001', $xml);
        $this->assertStringContainsString('DE89370400440532013000', $xml);
    }

    public function testToStringGeneratesXml(): void {
        $document = new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $string = (string)$document;
        $xml = $document->toXml();

        $this->assertSame($xml, $string);
    }
}
