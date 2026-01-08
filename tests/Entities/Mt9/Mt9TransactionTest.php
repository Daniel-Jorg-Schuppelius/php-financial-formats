<?php
/*
 * Created on   : Wed May 07 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940TransactionTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\CommonToolkit\FinancialFormats\Entities\Mt9;

use CommonToolkit\FinancialFormats\Entities\Mt9\{Reference, Type940\Transaction};
use CommonToolkit\Enums\{CreditDebit, CurrencyCode};
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt9TransactionTest extends BaseTestCase {

    private function createReference(): Reference {
        return new Reference('TRF', 'ABC123XYZ');
    }

    public function testGetterMethodsReturnCorrectValues(): void {
        $transaction = new Transaction(
            bookingDate: DateTimeImmutable::createFromFormat('ymd', '240501'),
            valutaDate: null,
            amount: 1234.56,
            creditDebit: CreditDebit::CREDIT,
            currency: CurrencyCode::Euro,
            reference: $this->createReference(),
            purpose: 'Testzahlung'
        );

        $this->assertEquals('240501', $transaction->getDate()->format('ymd'));
        $this->assertEquals(1234.56, $transaction->getAmount());
        $this->assertEquals('C', $transaction->getCreditDebit()->toMt940Code());
        $this->assertEquals('EUR', $transaction->getCurrency()->value);
        $this->assertEquals('TRF', $transaction->getReference()->getTransactionCode());
        $this->assertEquals('ABC123XYZ', $transaction->getReference()->getReference());
        $this->assertEquals('Testzahlung', $transaction->getPurpose());
    }

    public function testFormattedAmountReturnsString(): void {
        $transaction = new Transaction(
            bookingDate: DateTimeImmutable::createFromFormat('ymd', '240501'),
            valutaDate: null,
            amount: 1234.56,
            creditDebit: CreditDebit::DEBIT,
            currency: CurrencyCode::Euro,
            reference: $this->createReference(),
            purpose: null
        );

        $formatted = $transaction->getFormattedAmount('de_DE');
        $this->assertIsString($formatted);
        $this->assertStringContainsString('€', $formatted);
    }

    public function testIsDebitAndCredit(): void {
        $date = DateTimeImmutable::createFromFormat('ymd', '240501');
        $reference = $this->createReference();

        $credit = new Transaction($date, null, 100, CreditDebit::CREDIT, CurrencyCode::Euro, $reference, 'raw');
        $debit  = new Transaction($date, null, 50,  CreditDebit::DEBIT, CurrencyCode::Euro, $reference, 'raw');

        $this->assertTrue($credit->isCredit());
        $this->assertFalse($credit->isDebit());

        $this->assertTrue($debit->isDebit());
        $this->assertFalse($debit->isCredit());
    }

    public function testGetSign(): void {
        $reference = $this->createReference();

        $credit = new Transaction('240501', '0525', 100, CreditDebit::CREDIT, CurrencyCode::Euro, $reference, 'raw');
        $debit  = new Transaction('240501', '0526', 100, CreditDebit::DEBIT, CurrencyCode::Euro, $reference, 'raw');

        $this->assertEquals('+', $credit->getSign());
        $this->assertEquals('-', $debit->getSign());
        $this->assertEquals('240501', $credit->getDate()->format('ymd'));
        $this->assertEquals('240525', $credit->getValutaDate()->format('ymd'));
    }

    public function testToMt940LinesGeneratesCorrectFormat(): void {
        $reference = new Reference('TRF', 'ABC123XYZ');
        $transaction = new Transaction(
            bookingDate: DateTimeImmutable::createFromFormat('ymd', '240501'),
            valutaDate: null,
            amount: 1234.56,
            creditDebit: CreditDebit::CREDIT,
            currency: CurrencyCode::Euro,
            reference: $reference,
            purpose: 'SEPA Überweisung Max Mustermann GmbH für Rechnung 123456 vom 01.05.2024'
        );

        $lines = explode("\r\n", trim((string)$transaction));

        // Erste Zeile muss mit :61: beginnen
        $this->assertStringStartsWith(':61:', $lines[0]);

        // Zweite Zeile beginnt mit :86:
        $this->assertStringStartsWith(':86:', $lines[1]);

        // SWIFT MT940: :86: Narrative bis 6*65x
        $this->assertLessThanOrEqual(6, count($lines) - 1);
        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            if ($i === 1) {
                $this->assertLessThanOrEqual(69, strlen($line)); // :86: + 65
            } else {
                $this->assertLessThanOrEqual(65, strlen($line));
            }
        }

        // Purpose extrahieren und normalisieren
        $purposeLines = array_slice($lines, 1);
        $plainPurpose = preg_replace('/^:86:/', '', array_shift($purposeLines));
        $plainPurpose .= implode('', $purposeLines);

        $this->assertStringContainsString('Rechnung', $plainPurpose);
        $this->assertStringContainsString('123456', $plainPurpose);
    }
}
