<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransferDetailsTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt1;

use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransferDetailsTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertSame(1500.50, $details->getAmount());
        $this->assertSame(CurrencyCode::Euro, $details->getCurrency());
        $this->assertEquals(new DateTimeImmutable('2026-01-09'), $details->getValueDate());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50,
            originalCurrency: CurrencyCode::USDollar,
            originalAmount: 1600.00,
            exchangeRate: 1.0856
        );

        $this->assertSame(CurrencyCode::USDollar, $details->getOriginalCurrency());
        $this->assertSame(1600.00, $details->getOriginalAmount());
        $this->assertSame(1.0856, $details->getExchangeRate());
    }

    #[Test]
    public function hasCurrencyConversionReturnsTrueWhenDifferentCurrencies(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50,
            originalCurrency: CurrencyCode::USDollar,
            originalAmount: 1600.00
        );

        $this->assertTrue($details->hasCurrencyConversion());
    }

    #[Test]
    public function hasCurrencyConversionReturnsFalseWhenSameCurrency(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50,
            originalCurrency: CurrencyCode::Euro,
            originalAmount: 1500.50
        );

        $this->assertFalse($details->hasCurrencyConversion());
    }

    #[Test]
    public function hasCurrencyConversionReturnsFalseWhenNoOriginal(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertFalse($details->hasCurrencyConversion());
    }

    #[Test]
    public function getFormattedAmount(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $formatted = $details->getFormattedAmount();

        $this->assertStringContainsString('1.500,50', $formatted);
        $this->assertStringContainsString('EUR', $formatted);
    }

    #[Test]
    public function getValueDateReturnsImmutable(): void {
        $date = new DateTimeImmutable('2026-01-09');
        $details = new TransferDetails(
            valueDate: $date,
            currency: CurrencyCode::Euro,
            amount: 100.00
        );

        $this->assertEquals($date, $details->getValueDate());
    }

    #[Test]
    public function exchangeRateIsNullByDefault(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 100.00
        );

        $this->assertNull($details->getExchangeRate());
    }

    #[Test]
    public function originalAmountIsNullByDefault(): void {
        $details = new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 100.00
        );

        $this->assertNull($details->getOriginalAmount());
    }
}
