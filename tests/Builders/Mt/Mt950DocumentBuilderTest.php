<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt950DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt950DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Transaction;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt950DocumentBuilderTest extends BaseTestCase {
    private function createOpeningBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-14'),
            currency: CurrencyCode::Euro,
            amount: 10000.00,
            type: 'F'
        );
    }

    private function createClosingBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00,
            type: 'F'
        );
    }

    private function createTransaction(float $amount, CreditDebit $creditDebit): Transaction {
        return new Transaction(
            bookingDate: new DateTimeImmutable('2024-03-15'),
            valutaDate: new DateTimeImmutable('2024-03-15'),
            amount: $amount,
            creditDebit: $creditDebit,
            currency: CurrencyCode::Euro,
            reference: new Reference('NTRF', 'TX-001', 'BANK-REF')
        );
    }

    public function testCreateBuilder(): void {
        $builder = Mt950DocumentBuilder::create('STMT-001');
        $this->assertInstanceOf(Mt950DocumentBuilder::class, $builder);
    }

    public function testBuildMinimalDocument(): void {
        $document = Mt950DocumentBuilder::create('STMT-001')
            ->account('DE89370400440532013000')
            ->statementNumber('00001')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->skipBalanceValidation()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('STMT-001', $document->getReferenceId());
        $this->assertSame('DE89370400440532013000', $document->getAccountId());
        $this->assertSame('00001', $document->getStatementNumber());
        $this->assertSame(MtType::MT950, $document->getMtType());
    }

    public function testBuildWithTransactions(): void {
        $openingBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-14'),
            currency: CurrencyCode::Euro,
            amount: 10000.00,
            type: 'F'
        );

        $closingBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00, // 10000 + 500 credit
            type: 'F'
        );

        $transaction = $this->createTransaction(500.00, CreditDebit::CREDIT);

        $document = Mt950DocumentBuilder::create('STMT-002')
            ->account('DE89370400440532013000')
            ->statementNumber('00002')
            ->openingBalance($openingBalance)
            ->closingBalance($closingBalance)
            ->addTransaction($transaction)
            ->build();

        $this->assertCount(1, $document->getTransactions());
        $this->assertEquals(500.00, $document->getTotalCredit());
        $this->assertEquals(0.00, $document->getTotalDebit());
    }

    public function testBuildWithRelatedReference(): void {
        $document = Mt950DocumentBuilder::create('STMT-003')
            ->account('DE89370400440532013000')
            ->relatedReference('REQ-920-001')
            ->statementNumber('00003')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->skipBalanceValidation()
            ->build();

        $this->assertSame('REQ-920-001', $document->getRelatedReference());
    }

    public function testBuildWithClosingAvailableBalance(): void {
        $closingAvailable = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-15'),
            currency: CurrencyCode::Euro,
            amount: 10400.00,
            type: 'F'
        );

        $document = Mt950DocumentBuilder::create('STMT-004')
            ->account('DE89370400440532013000')
            ->statementNumber('00004')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->closingAvailableBalance($closingAvailable)
            ->skipBalanceValidation()
            ->build();

        $this->assertNotNull($document->getClosingAvailableBalance());
        $this->assertEquals(10400.00, $document->getClosingAvailableBalance()->getAmount());
    }

    public function testThrowsOnMissingAccount(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt950DocumentBuilder::create('STMT-001')
            ->statementNumber('00001')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->build();
    }

    public function testThrowsOnMissingOpeningBalance(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt950DocumentBuilder::create('STMT-001')
            ->account('DE89370400440532013000')
            ->statementNumber('00001')
            ->closingBalance($this->createClosingBalance())
            ->build();
    }

    public function testThrowsOnMissingClosingBalance(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt950DocumentBuilder::create('STMT-001')
            ->account('DE89370400440532013000')
            ->statementNumber('00001')
            ->openingBalance($this->createOpeningBalance())
            ->build();
    }
}
