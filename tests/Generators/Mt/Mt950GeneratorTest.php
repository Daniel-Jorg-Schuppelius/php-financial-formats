<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt950GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt950DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Transaction;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt950Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt950GeneratorTest extends BaseTestCase {
    private function createOpeningBalance(float $amount = 10000.00): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-14'),
            currency: CurrencyCode::Euro,
            amount: $amount,
            type: 'F'
        );
    }

    private function createClosingBalance(float $amount = 10500.00): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-15'),
            currency: CurrencyCode::Euro,
            amount: $amount,
            type: 'F'
        );
    }

    public function testGenerateSimpleMessage(): void {
        $document = Mt950DocumentBuilder::create('STMT-001')
            ->account('DE89370400440532013000')
            ->statementNumber('00001')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->skipBalanceValidation()
            ->build();

        $generator = new Mt950Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':20:STMT-001', $message);
        $this->assertStringContainsString(':25:DE89370400440532013000', $message);
        $this->assertStringContainsString(':28C:00001', $message);
        $this->assertStringContainsString(':60F:', $message);
        $this->assertStringContainsString(':62F:', $message);
    }

    public function testGenerateWithTransactions(): void {
        $openingBalance = $this->createOpeningBalance(10000.00);
        $closingBalance = $this->createClosingBalance(10500.00);

        $transaction = new Transaction(
            bookingDate: new DateTimeImmutable('2024-03-15'),
            valutaDate: new DateTimeImmutable('2024-03-15'),
            amount: 500.00,
            creditDebit: CreditDebit::CREDIT,
            currency: CurrencyCode::Euro,
            reference: new Reference('TRF', 'TX-001', 'BANK-REF')
        );

        $document = Mt950DocumentBuilder::create('STMT-002')
            ->account('DE89370400440532013000')
            ->statementNumber('00002')
            ->openingBalance($openingBalance)
            ->closingBalance($closingBalance)
            ->addTransaction($transaction)
            ->build();

        $generator = new Mt950Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':61:', $message);
        $this->assertStringContainsString('NTRF', $message);  // N(booking key) + TRF(code)
    }

    public function testGenerateWithClosingAvailableBalance(): void {
        $closingAvailable = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2024-03-15'),
            currency: CurrencyCode::Euro,
            amount: 10400.00,
            type: 'F'
        );

        $document = Mt950DocumentBuilder::create('STMT-003')
            ->account('DE89370400440532013000')
            ->statementNumber('00003')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->closingAvailableBalance($closingAvailable)
            ->skipBalanceValidation()
            ->build();

        $generator = new Mt950Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':64:', $message);
    }

    public function testMessageEndsWithMarker(): void {
        $document = Mt950DocumentBuilder::create('END-TEST')
            ->account('DE89370400440532013000')
            ->statementNumber('00001')
            ->openingBalance($this->createOpeningBalance())
            ->closingBalance($this->createClosingBalance())
            ->skipBalanceValidation()
            ->build();

        $generator = new Mt950Generator();
        $message = $generator->generate($document);

        $this->assertStringEndsWith('-', trim($message));
    }
}
