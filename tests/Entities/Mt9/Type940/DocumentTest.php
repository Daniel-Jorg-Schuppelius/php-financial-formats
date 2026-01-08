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

namespace Tests\Entities\Mt9\Type940;

use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Transaction;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
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
            type: 'F'
        );
    }

    private function createClosingBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00,
            type: 'F'
        );
    }

    public function testConstructorWithMinimalParameters(): void {
        $openingBalance = $this->createOpeningBalance();
        $closingBalance = $this->createClosingBalance();

        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-001',
            statementNumber: '001',
            openingBalance: $openingBalance,
            closingBalance: $closingBalance
        );

        $this->assertSame('DE89370400440532013000', $document->getAccountId());
        $this->assertSame('REF-001', $document->getReferenceId());
        $this->assertSame('001', $document->getStatementNumber());
        $this->assertSame($openingBalance, $document->getOpeningBalance());
        $this->assertSame($closingBalance, $document->getClosingBalance());
        $this->assertEmpty($document->getTransactions());
    }

    public function testConstructorWithAllParameters(): void {
        $openingBalance = $this->createOpeningBalance();
        $closingBalance = $this->createClosingBalance();
        $closingAvailable = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00,
            type: 'A'
        );

        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-001',
            statementNumber: '001',
            openingBalance: $openingBalance,
            closingBalance: $closingBalance,
            transactions: [],
            closingAvailableBalance: $closingAvailable,
            forwardAvailableBalances: null,
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00')
        );

        $this->assertSame($closingAvailable, $document->getClosingAvailableBalance());
        $this->assertNull($document->getForwardAvailableBalance());
    }

    public function testGetMtType(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-001',
            statementNumber: '001',
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $this->assertSame(MtType::MT940, $document->getMtType());
    }

    public function testCountEntriesWithEmptyTransactions(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-001',
            statementNumber: '001',
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $this->assertSame(0, $document->countEntries());
    }

    public function testCurrencyFromOpeningBalance(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-001',
            statementNumber: '001',
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
    }

    public function testToStringOutputsSwiftFormat(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-001',
            statementNumber: '001',
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $output = (string)$document;

        $this->assertNotEmpty($output);
        $this->assertStringContainsString(':20:', $output);
        $this->assertStringContainsString(':25:', $output);
        $this->assertStringContainsString(':60F:', $output);
        $this->assertStringContainsString(':62F:', $output);
    }
}
