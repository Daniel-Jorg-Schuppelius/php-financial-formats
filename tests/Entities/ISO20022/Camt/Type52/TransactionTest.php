<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransactionTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\ISO20022\Camt\Type52;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Transaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\FinancialInstitutionIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ReturnReason;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TechnicalInputChannel;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionDomain;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionFamily;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionPurpose;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionSubFamily;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT
        );

        $this->assertSame(100.00, $tx->getAmount());
        $this->assertSame(CurrencyCode::Euro, $tx->getCurrency());
        $this->assertSame(CreditDebit::CREDIT, $tx->getCreditDebit());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $debtor = new PartyIdentification(name: 'Debtor GmbH');
        $creditor = new PartyIdentification(name: 'Creditor AG');
        $debtorAgent = new FinancialInstitutionIdentification(bic: 'DEUTDEFF');
        $creditorAgent = new FinancialInstitutionIdentification(bic: 'COBADEFF');

        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: new DateTimeImmutable('2026-01-10'),
            amount: 1500.50,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT,
            entryReference: 'REF123',
            accountServicerReference: 'ASVR456',
            status: 'BOOK',
            isReversal: false,
            purpose: 'Payment for services',
            purposeCode: TransactionPurpose::SALA,
            additionalInfo: 'Additional info',
            bankTransactionCode: 'PMNT',
            domainCode: TransactionDomain::PMNT,
            familyCode: TransactionFamily::RCDT,
            subFamilyCode: TransactionSubFamily::ESCT,
            returnReason: null,
            technicalInputChannel: TechnicalInputChannel::FAXI,
            counterpartyName: 'Max Mustermann',
            counterpartyIban: 'DE89370400440532013000',
            counterpartyBic: 'COBADEFF',
            remittanceInfo: 'Invoice 12345',
            debtor: $debtor,
            creditor: $creditor,
            debtorAgent: $debtorAgent,
            creditorAgent: $creditorAgent
        );

        $this->assertSame(1500.50, $tx->getAmount());
        $this->assertSame(CreditDebit::DEBIT, $tx->getCreditDebit());
        $this->assertSame('REF123', $tx->getEntryReference());
        $this->assertSame('ASVR456', $tx->getAccountServicerReference());
        $this->assertSame('Payment for services', $tx->getPurpose());
        $this->assertSame(TransactionPurpose::SALA, $tx->getPurposeCode());
        $this->assertSame('Additional info', $tx->getAdditionalInfo());
        $this->assertSame('PMNT', $tx->getBankTransactionCode());
        $this->assertSame(TransactionDomain::PMNT, $tx->getDomainCode());
        $this->assertSame(TransactionFamily::RCDT, $tx->getFamilyCode());
        $this->assertSame(TransactionSubFamily::ESCT, $tx->getSubFamilyCode());
        $this->assertSame(TechnicalInputChannel::FAXI, $tx->getTechnicalInputChannel());
        $this->assertSame('Max Mustermann', $tx->getCounterpartyName());
        $this->assertSame('DE89370400440532013000', $tx->getCounterpartyIban());
        $this->assertSame('COBADEFF', $tx->getCounterpartyBic());
        $this->assertSame('Invoice 12345', $tx->getRemittanceInfo());
        $this->assertSame($debtor, $tx->getDebtor());
        $this->assertSame($creditor, $tx->getCreditor());
        $this->assertSame($debtorAgent, $tx->getDebtorAgent());
        $this->assertSame($creditorAgent, $tx->getCreditorAgent());
    }

    #[Test]
    public function enumFromStrings(): void {
        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            purposeCode: 'SALA',
            domainCode: 'PMNT',
            familyCode: 'RCDT',
            subFamilyCode: 'ESCT',
            technicalInputChannel: 'FAXI'
        );

        $this->assertSame(TransactionPurpose::SALA, $tx->getPurposeCode());
        $this->assertSame(TransactionDomain::PMNT, $tx->getDomainCode());
        $this->assertSame(TransactionFamily::RCDT, $tx->getFamilyCode());
        $this->assertSame(TransactionSubFamily::ESCT, $tx->getSubFamilyCode());
        $this->assertSame(TechnicalInputChannel::FAXI, $tx->getTechnicalInputChannel());
    }

    #[Test]
    public function returnReasonHandling(): void {
        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            returnReason: ReturnReason::AC01
        );

        $this->assertSame(ReturnReason::AC01, $tx->getReturnReason());
    }

    #[Test]
    public function returnReasonFromString(): void {
        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            returnReason: 'AC01'
        );

        $this->assertSame(ReturnReason::AC01, $tx->getReturnReason());
    }

    #[Test]
    public function signedAmount(): void {
        $credit = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT
        );

        $debit = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT
        );

        $this->assertSame(100.00, $credit->getSignedAmount());
        $this->assertSame(-100.00, $debit->getSignedAmount());
    }

    #[Test]
    public function isCreditAndIsDebit(): void {
        $credit = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT
        );

        $debit = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT
        );

        $this->assertTrue($credit->isCredit());
        $this->assertFalse($credit->isDebit());
        $this->assertTrue($debit->isDebit());
        $this->assertFalse($debit->isCredit());
    }

    #[Test]
    public function getFullTransactionCode(): void {
        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: null,
            amount: 100.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::CREDIT,
            domainCode: TransactionDomain::PMNT,
            familyCode: TransactionFamily::RCDT,
            subFamilyCode: TransactionSubFamily::ESCT
        );

        $code = $tx->getFullTransactionCode();

        $this->assertStringContainsString('PMNT', $code);
        $this->assertStringContainsString('RCDT', $code);
        $this->assertStringContainsString('ESCT', $code);
    }
}
