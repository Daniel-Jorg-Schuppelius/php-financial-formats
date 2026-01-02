<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BalanceTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\Mt9;

use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

class BalanceTest extends BaseTestCase {
    public function testConstructorWithDateTimeImmutable(): void {
        $date = new DateTimeImmutable('2025-01-15');

        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: $date,
            currency: CurrencyCode::Euro,
            amount: 12500.50
        );

        $this->assertSame(CreditDebit::CREDIT, $balance->getCreditDebit());
        $this->assertEquals($date, $balance->getDate());
        $this->assertSame(CurrencyCode::Euro, $balance->getCurrency());
        $this->assertSame(12500.50, $balance->getAmount());
        $this->assertSame('F', $balance->getType());
    }

    public function testConstructorWithStringDate(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::DEBIT,
            date: '250115', // ymd format
            currency: CurrencyCode::USDollar,
            amount: 5000.00
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $balance->getDate());
        $this->assertSame('2025-01-15', $balance->getDate()->format('Y-m-d'));
    }

    public function testConstructorWithInvalidDateThrowsException(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ungültiges Datum');

        new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: 'invalid',
            currency: CurrencyCode::Euro,
            amount: 1000.00
        );
    }

    public function testAmountIsRoundedToTwoDecimals(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 12345.6789
        );

        $this->assertSame(12345.68, $balance->getAmount());
    }

    public function testCustomBalanceType(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 1000.00,
            type: 'M' // Interim
        );

        $this->assertSame('M', $balance->getType());
    }

    public function testIsFinal(): void {
        $finalBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 1000.00,
            type: 'F'
        );

        $interimBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 1000.00,
            type: 'M'
        );

        $this->assertTrue($finalBalance->isFinal());
        $this->assertFalse($interimBalance->isFinal());
    }

    public function testIsInterim(): void {
        $interimBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 1000.00,
            type: 'M'
        );

        $finalBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 1000.00,
            type: 'F'
        );

        $this->assertTrue($interimBalance->isInterim());
        $this->assertFalse($finalBalance->isInterim());
    }

    public function testIsCreditAndIsDebit(): void {
        $creditBalance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 1000.00
        );

        $debitBalance = new Balance(
            creditDebit: CreditDebit::DEBIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 500.00
        );

        $this->assertTrue($creditBalance->isCredit());
        $this->assertFalse($creditBalance->isDebit());

        $this->assertTrue($debitBalance->isDebit());
        $this->assertFalse($debitBalance->isCredit());
    }

    public function testGetFormattedAmount(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable(),
            currency: CurrencyCode::Euro,
            amount: 12500.50
        );

        $formatted = $balance->getFormattedAmount('de_DE');

        $this->assertNotEmpty($formatted);
    }

    public function testToStringSwiftFormat(): void {
        $date = new DateTimeImmutable('2025-01-15');
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: $date,
            currency: CurrencyCode::Euro,
            amount: 12500.50
        );

        $swift = (string)$balance;

        // Format: [C/D][Datum YYMMDD][Währung][Betrag mit Komma]
        $this->assertStringContainsString('250115', $swift);
        $this->assertStringContainsString('EUR', $swift);
        $this->assertStringContainsString('12500,50', $swift);
    }
}
