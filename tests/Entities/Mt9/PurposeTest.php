<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PurposeTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt9;

use CommonToolkit\FinancialFormats\Entities\Mt9\Purpose;
use CommonToolkit\FinancialFormats\Enums\Mt\GvcCode;
use CommonToolkit\FinancialFormats\Enums\Mt\PurposeCode;
use CommonToolkit\FinancialFormats\Enums\Mt\TextKeyExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PurposeTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $purpose = new Purpose();

        $this->assertNull($purpose->getGvcCode());
        $this->assertNull($purpose->getBookingText());
        $this->assertNull($purpose->getRawText());
    }

    #[Test]
    public function constructorWithDatevFields(): void {
        $purpose = new Purpose(
            gvcCode: GvcCode::SEPA_CT_SINGLE_DEBIT,
            bookingText: 'SEPA Überweisung',
            primanotenNr: '12345',
            purposeLines: ['Verwendungszweck Zeile 1', 'Zeile 2'],
            payerBlz: 'DEUTDEFF',
            payerAccount: 'DE89370400440532013000',
            payerName1: 'Max Mustermann',
            payerName2: 'GmbH'
        );

        $this->assertSame(GvcCode::SEPA_CT_SINGLE_DEBIT, $purpose->getGvcCode());
        $this->assertSame('SEPA Überweisung', $purpose->getBookingText());
        $this->assertSame('12345', $purpose->getPrimanotenNr());
        $this->assertCount(2, $purpose->getPurposeLines());
        $this->assertSame('DEUTDEFF', $purpose->getPayerBlz());
        $this->assertSame('DE89370400440532013000', $purpose->getPayerAccount());
        $this->assertSame('Max Mustermann', $purpose->getPayerName1());
        $this->assertSame('GmbH', $purpose->getPayerName2());
    }

    #[Test]
    public function constructorWithSwiftKeywords(): void {
        $purpose = new Purpose(
            endToEndReference: 'E2E-REF-12345',
            paymentInfoId: 'PREF-001',
            instructionId: 'IREF-001',
            mandateReference: 'MREF-001',
            creditorId: 'DE98ZZZ09999999999',
            remittanceInfo: 'Invoice 12345',
            beneficiaryName: 'Beneficiary GmbH',
            orderingPartyName: 'Ordering AG'
        );

        $this->assertSame('E2E-REF-12345', $purpose->getEndToEndReference());
        $this->assertSame('PREF-001', $purpose->getPaymentInfoId());
        $this->assertSame('IREF-001', $purpose->getInstructionId());
        $this->assertSame('MREF-001', $purpose->getMandateReference());
        $this->assertSame('DE98ZZZ09999999999', $purpose->getCreditorId());
        $this->assertSame('Invoice 12345', $purpose->getRemittanceInfo());
        $this->assertSame('Beneficiary GmbH', $purpose->getBeneficiaryName());
        $this->assertSame('Ordering AG', $purpose->getOrderingPartyName());
    }

    #[Test]
    public function textKeyExtension(): void {
        $purpose = new Purpose(
            textKeyExt: TextKeyExtension::STANDARD
        );

        $this->assertSame(TextKeyExtension::STANDARD, $purpose->getTextKeyExt());
    }

    #[Test]
    public function purposeCodeHandling(): void {
        $purpose = new Purpose(
            purposeCode: PurposeCode::SALA
        );

        $this->assertSame(PurposeCode::SALA, $purpose->getPurposeCode());
    }

    #[Test]
    public function ultimatePartyNames(): void {
        $purpose = new Purpose(
            ultimateDebtorName: 'Ultimate Debtor',
            ultimateCreditorName: 'Ultimate Creditor'
        );

        $this->assertSame('Ultimate Debtor', $purpose->getUltimateDebtorName());
        $this->assertSame('Ultimate Creditor', $purpose->getUltimateCreditorName());
    }

    #[Test]
    public function bankInfoFields(): void {
        $purpose = new Purpose(
            beneficiaryBank: 'COBADEFF',
            orderingBank: 'DEUTDEFF'
        );

        $this->assertSame('COBADEFF', $purpose->getBeneficiaryBank());
        $this->assertSame('DEUTDEFF', $purpose->getOrderingBank());
    }

    #[Test]
    public function amountAndCurrencyFields(): void {
        $purpose = new Purpose(
            originalAmount: 'EUR1500,00',
            charges: 'EUR5,00',
            exchangeRate: '1,0856'
        );

        $this->assertSame('EUR1500,00', $purpose->getOriginalAmount());
        $this->assertSame('EUR5,00', $purpose->getCharges());
        $this->assertSame('1,0856', $purpose->getExchangeRate());
    }

    #[Test]
    public function rawText(): void {
        $purpose = new Purpose(rawText: 'Some raw purpose text');

        $this->assertSame('Some raw purpose text', $purpose->getRawText());
    }

    #[Test]
    public function returnReason(): void {
        $purpose = new Purpose(returnReason: 'AC01');

        $this->assertSame('AC01', $purpose->getReturnReason());
    }

    #[Test]
    public function payerName(): void {
        $purpose = new Purpose(
            payerName1: 'Max Mustermann',
            payerName2: 'GmbH & Co. KG'
        );

        $fullName = $purpose->getPayerName();

        $this->assertStringContainsString('Max Mustermann', $fullName);
        $this->assertStringContainsString('GmbH & Co. KG', $fullName);
    }

    #[Test]
    public function purposeTextFromLines(): void {
        $purpose = new Purpose(
            purposeLines: ['Zeile 1', 'Zeile 2', 'Zeile 3']
        );

        $lines = $purpose->getPurposeLines();

        $this->assertCount(3, $lines);
        $this->assertSame('Zeile 1', $lines[0]);
        $this->assertSame('Zeile 2', $lines[1]);
        $this->assertSame('Zeile 3', $lines[2]);
    }

    #[Test]
    public function virtualAccountAndTransactionReference(): void {
        $purpose = new Purpose(
            transactionReference: 'TR-12345',
            virtualAccount: 'VACC-001'
        );

        $this->assertSame('TR-12345', $purpose->getTransactionReference());
        $this->assertSame('VACC-001', $purpose->getVirtualAccount());
    }
}