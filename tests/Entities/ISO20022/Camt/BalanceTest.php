<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BalanceTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\BalanceSubType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die CAMT Balance Entity.
 */
class BalanceTest extends BaseTestCase {
    public function testConstructorWithEnums(): void {
        $date = new DateTimeImmutable('2025-01-15');

        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: $date,
            currency: CurrencyCode::Euro,
            amount: 1234.56,
            type: 'CLBD',
            subType: BalanceSubType::INTM
        );

        $this->assertSame(CreditDebit::CREDIT, $balance->getCreditDebit());
        $this->assertEquals($date, $balance->getDate());
        $this->assertSame(CurrencyCode::Euro, $balance->getCurrency());
        $this->assertSame(1234.56, $balance->getAmount());
        $this->assertSame('CLBD', $balance->getType());
        $this->assertSame(BalanceSubType::INTM, $balance->getSubType());
    }

    public function testConstructorWithStrings(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::DEBIT,
            date: '2025-01-15',
            currency: 'EUR',
            amount: 500.00,
            type: 'OPBD'
        );

        $this->assertSame(CreditDebit::DEBIT, $balance->getCreditDebit());
        $this->assertSame('2025-01-15', $balance->getDate()->format('Y-m-d'));
        $this->assertSame(CurrencyCode::Euro, $balance->getCurrency());
        $this->assertSame(500.00, $balance->getAmount());
        $this->assertSame('OPBD', $balance->getType());
    }

    public function testAmountIsAlwaysPositive(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::DEBIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: -1000.00,
            type: 'CLBD'
        );

        $this->assertSame(1000.00, $balance->getAmount());
    }

    public function testTypeIsUppercase(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 100.00,
            type: 'clbd'
        );

        $this->assertSame('CLBD', $balance->getType());
    }

    public function testIsCreditAndIsDebit(): void {
        $creditBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 100.00
        );

        $debitBalance = new Balance(
            creditDebit: CreditDebit::DEBIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 100.00
        );

        $this->assertTrue($creditBalance->isCredit());
        $this->assertFalse($creditBalance->isDebit());
        $this->assertFalse($debitBalance->isCredit());
        $this->assertTrue($debitBalance->isDebit());
    }

    public function testInvalidCurrencyThrowsException(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ungültige Währung');

        new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: 'INVALID',
            amount: 100.00
        );
    }

    public function testCurrencyNormalization(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: 'eur',
            amount: 100.00
        );

        $this->assertSame(CurrencyCode::Euro, $balance->getCurrency());
    }

    public function testSupportedBalanceTypes(): void {
        $types = ['OPBD', 'CLBD', 'PRCD', 'CLAV', 'FWAV'];

        foreach ($types as $type) {
            $balance = new Balance(
                creditDebit: CreditDebit::CREDIT,
                date: new DateTimeImmutable(),
                currency: CurrencyCode::Euro,
                amount: 100.00,
                type: $type
            );

            $this->assertSame($type, $balance->getType());
        }
    }

    public function testSignedAmount(): void {
        $creditBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 500.00
        );

        $debitBalance = new Balance(
            creditDebit: CreditDebit::DEBIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 300.00
        );

        $this->assertSame(500.00, $creditBalance->getSignedAmount());
        $this->assertSame(-300.00, $debitBalance->getSignedAmount());
    }
}
