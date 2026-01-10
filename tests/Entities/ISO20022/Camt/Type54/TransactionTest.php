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

namespace CommonToolkit\FinancialFormats\Tests\Entities\ISO20022\Camt\Type54;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Transaction;
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
        $instructingAgent = new FinancialInstitutionIdentification(bic: 'INSTDEFF');
        $instructedAgent = new FinancialInstitutionIdentification(bic: 'INSTDEFF2');

        $tx = new Transaction(
            bookingDate: new DateTimeImmutable('2026-01-09'),
            valutaDate: new DateTimeImmutable('2026-01-10'),
            amount: 2500.00,
            currency: CurrencyCode::Euro,
            creditDebit: CreditDebit::DEBIT,
            entryReference: 'REF054',
            accountServicerReference: 'ASVR054',
            status: 'BOOK',
            isReversal: false,
            instructionId: 'INSTR001',
            endToEndId: 'E2E001',
            remittanceInfo: 'Invoice 54321',
            purposeCode: TransactionPurpose::SALA,
            bankTransactionCode: 'PMNT',
            domainCode: TransactionDomain::PMNT,
            familyCode: TransactionFamily::RCDT,
            subFamilyCode: TransactionSubFamily::ESCT,
            returnReason: null,
            technicalInputChannel: TechnicalInputChannel::WEBI,
            localInstrumentCode: 'CORE',
            instructingAgentBic: 'INSTDEFF',
            instructedAgentBic: 'INSTDEFF2',
            debtorAgentBic: 'DEUTDEFF',
            creditorAgentBic: 'COBADEFF',
            debtor: $debtor,
            creditor: $creditor,
            debtorAgent: $debtorAgent,
            creditorAgent: $creditorAgent,
            instructingAgent: $instructingAgent,
            instructedAgent: $instructedAgent
        );

        $this->assertSame(2500.00, $tx->getAmount());
        $this->assertSame('INSTR001', $tx->getInstructionId());
        $this->assertSame('E2E001', $tx->getEndToEndId());
        $this->assertSame('Invoice 54321', $tx->getRemittanceInfo());
        $this->assertSame(TransactionPurpose::SALA, $tx->getPurposeCode());
        $this->assertSame('CORE', $tx->getLocalInstrumentCode());
        $this->assertSame('INSTDEFF', $tx->getInstructingAgentBic());
        $this->assertSame('INSTDEFF2', $tx->getInstructedAgentBic());
        $this->assertSame('DEUTDEFF', $tx->getDebtorAgentBic());
        $this->assertSame('COBADEFF', $tx->getCreditorAgentBic());
        $this->assertSame($debtor, $tx->getDebtor());
        $this->assertSame($creditor, $tx->getCreditor());
        $this->assertSame($debtorAgent, $tx->getDebtorAgent());
        $this->assertSame($creditorAgent, $tx->getCreditorAgent());
        $this->assertSame($instructingAgent, $tx->getInstructingAgent());
        $this->assertSame($instructedAgent, $tx->getInstructedAgent());
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
            technicalInputChannel: 'WEBI'
        );

        $this->assertSame(TransactionPurpose::SALA, $tx->getPurposeCode());
        $this->assertSame(TransactionDomain::PMNT, $tx->getDomainCode());
        $this->assertSame(TransactionFamily::RCDT, $tx->getFamilyCode());
        $this->assertSame(TransactionSubFamily::ESCT, $tx->getSubFamilyCode());
        $this->assertSame(TechnicalInputChannel::WEBI, $tx->getTechnicalInputChannel());
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

        $this->assertTrue($credit->isCredit());
        $this->assertFalse($credit->isDebit());
    }

    #[Test]
    public function returnReasonFromEnum(): void {
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
            returnReason: 'MS02'
        );

        $this->assertSame(ReturnReason::MS02, $tx->getReturnReason());
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
            familyCode: TransactionFamily::ICDT,
            subFamilyCode: TransactionSubFamily::ESCT
        );

        $code = $tx->getFullTransactionCode();

        $this->assertStringContainsString('PMNT', $code);
        $this->assertStringContainsString('ICDT', $code);
        $this->assertStringContainsString('ESCT', $code);
    }
}
