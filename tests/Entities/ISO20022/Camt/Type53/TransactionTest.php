<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransactionTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Camt\Type53;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Reference;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Transaction;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ReturnReason;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionDomain;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionFamily;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionPurpose;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionSubFamily;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die CAMT.053 Transaction Entity.
 */
class TransactionTest extends BaseTestCase {
    private function createTestReference(): Reference {
        return new Reference(
            endToEndId: 'EREF-12345',
            mandateId: 'MREF-67890',
            creditorId: 'DE98ZZZ09999999999'
        );
    }

    public function testConstructorWithMinimalParameters(): void {
        $bookingDate = new DateTimeImmutable('2025-01-15');
        $reference = $this->createTestReference();

        $transaction = new Transaction(
            bookingDate: $bookingDate,
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            reference: $reference
        );

        $this->assertEquals($bookingDate, $transaction->getBookingDate());
        $this->assertNull($transaction->getValutaDate());
        $this->assertSame(100.00, $transaction->getAmount());
        $this->assertSame(CurrencyCode::Euro, $transaction->getCurrency());
        $this->assertSame(CreditDebit::CREDIT, $transaction->getCreditDebit());
        $this->assertSame($reference, $transaction->getReference());
        $this->assertSame('BOOK', $transaction->getStatus());
        $this->assertFalse($transaction->isReversal());
    }

    public function testConstructorWithAllParameters(): void {
        $bookingDate = new DateTimeImmutable('2025-01-15');
        $valutaDate = new DateTimeImmutable('2025-01-16');
        $reference = $this->createTestReference();

        $transaction = new Transaction(
            bookingDate: $bookingDate,
            valutaDate: $valutaDate,
            amount: 250.50,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT,
            reference: $reference,
            entryReference: 'NTRY-001',
            accountServicerReference: 'ACCT-SVC-001',
            status: 'PDNG',
            isReversal: true,
            purpose: 'Miete Januar 2025',
            purposeCode: TransactionPurpose::RENT,
            additionalInfo: 'Zusätzliche Buchungsinfo',
            transactionCode: '051',
            domainCode: TransactionDomain::PMNT,
            familyCode: TransactionFamily::RCDT,
            subFamilyCode: TransactionSubFamily::AUTT,
            returnReason: null,
            counterpartyName: 'Max Mustermann',
            counterpartyIban: 'DE89370400440532013000',
            counterpartyBic: 'COBADEFFXXX'
        );

        $this->assertEquals($bookingDate, $transaction->getBookingDate());
        $this->assertEquals($valutaDate, $transaction->getValutaDate());
        $this->assertSame(250.50, $transaction->getAmount());
        $this->assertSame(CurrencyCode::Euro, $transaction->getCurrency());
        $this->assertSame(CreditDebit::DEBIT, $transaction->getCreditDebit());
        $this->assertSame('NTRY-001', $transaction->getEntryReference());
        $this->assertSame('ACCT-SVC-001', $transaction->getAccountServicerReference());
        $this->assertSame('PDNG', $transaction->getStatus());
        $this->assertTrue($transaction->isReversal());
        $this->assertSame('Miete Januar 2025', $transaction->getPurpose());
        $this->assertSame(TransactionPurpose::RENT, $transaction->getPurposeCode());
        $this->assertSame('Zusätzliche Buchungsinfo', $transaction->getAdditionalInfo());
        $this->assertSame('051', $transaction->getTransactionCode());
        $this->assertSame(TransactionDomain::PMNT, $transaction->getDomainCode());
        $this->assertSame(TransactionFamily::RCDT, $transaction->getFamilyCode());
        $this->assertSame(TransactionSubFamily::AUTT, $transaction->getSubFamilyCode());
        $this->assertSame('Max Mustermann', $transaction->getCounterpartyName());
        $this->assertSame('DE89370400440532013000', $transaction->getCounterpartyIban());
        $this->assertSame('COBADEFFXXX', $transaction->getCounterpartyBic());
    }

    public function testEnumFromStrings(): void {
        $transaction = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            reference: $this->createTestReference(),
            purposeCode: 'RENT',
            domainCode: 'PMNT',
            familyCode: 'RCDT',
            subFamilyCode: 'AUTT'
        );

        $this->assertSame(TransactionPurpose::RENT, $transaction->getPurposeCode());
        $this->assertSame(TransactionDomain::PMNT, $transaction->getDomainCode());
        $this->assertSame(TransactionFamily::RCDT, $transaction->getFamilyCode());
        $this->assertSame(TransactionSubFamily::AUTT, $transaction->getSubFamilyCode());
    }

    public function testGetFullTransactionCode(): void {
        $transactionWithCodes = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            reference: $this->createTestReference(),
            domainCode: TransactionDomain::PMNT,
            familyCode: TransactionFamily::RCDT,
            subFamilyCode: TransactionSubFamily::AUTT
        );

        $this->assertSame('PMNT/RCDT/AUTT', $transactionWithCodes->getFullTransactionCode());

        $transactionWithGvc = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            reference: $this->createTestReference(),
            transactionCode: '051'
        );

        $this->assertSame('051', $transactionWithGvc->getFullTransactionCode());
    }

    public function testSignedAmount(): void {
        $reference = $this->createTestReference();

        $creditTransaction = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 500.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            reference: $reference
        );

        $debitTransaction = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 300.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT,
            reference: $reference
        );

        $this->assertSame(500.00, $creditTransaction->getSignedAmount());
        $this->assertSame(-300.00, $debitTransaction->getSignedAmount());
    }

    public function testIsCreditAndIsDebit(): void {
        $reference = $this->createTestReference();

        $creditTransaction = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            reference: $reference
        );

        $debitTransaction = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT,
            reference: $reference
        );

        $this->assertTrue($creditTransaction->isCredit());
        $this->assertFalse($creditTransaction->isDebit());
        $this->assertFalse($debitTransaction->isCredit());
        $this->assertTrue($debitTransaction->isDebit());
    }

    public function testReturnReasonFromString(): void {
        $transaction = new Transaction(
            bookingDate: new DateTimeImmutable(),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT,
            reference: $this->createTestReference(),
            returnReason: 'AC01'
        );

        $this->assertSame(ReturnReason::AC01, $transaction->getReturnReason());
    }
}
