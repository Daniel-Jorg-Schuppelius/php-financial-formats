<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MandateTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\Pain\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\Pain\SequenceType;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class MandateTest extends BaseTestCase {
    private function createTestMandate(): Mandate {
        return new Mandate(
            mandateId: 'MNDT-2025-001',
            dateOfSignature: new DateTimeImmutable('2025-01-01'),
            creditor: PartyIdentification::fromName('Gläubiger GmbH'),
            creditorAccount: AccountIdentification::fromIban('DE89370400440532013000'),
            creditorAgent: FinancialInstitution::fromBic('COBADEFFXXX'),
            debtor: PartyIdentification::fromName('Schuldner AG'),
            debtorAccount: AccountIdentification::fromIban('DE89370400440000000001'),
            debtorAgent: FinancialInstitution::fromBic('DEUTDEFFXXX'),
            creditorSchemeId: 'DE98ZZZ09999999999',
            localInstrument: LocalInstrument::SEPA_CORE,
            sequenceType: SequenceType::FIRST
        );
    }

    public function testConstructor(): void {
        $mandate = $this->createTestMandate();

        $this->assertSame('MNDT-2025-001', $mandate->getMandateId());
        $this->assertSame('2025-01-01', $mandate->getDateOfSignature()->format('Y-m-d'));
        $this->assertSame('Gläubiger GmbH', $mandate->getCreditor()->getName());
        $this->assertSame('DE89370400440532013000', $mandate->getCreditorAccount()->getIban());
        $this->assertSame('COBADEFFXXX', $mandate->getCreditorAgent()->getBic());
        $this->assertSame('Schuldner AG', $mandate->getDebtor()->getName());
        $this->assertSame('DE89370400440000000001', $mandate->getDebtorAccount()->getIban());
        $this->assertSame('DEUTDEFFXXX', $mandate->getDebtorAgent()->getBic());
        $this->assertSame('DE98ZZZ09999999999', $mandate->getCreditorSchemeId());
        $this->assertSame(LocalInstrument::SEPA_CORE, $mandate->getLocalInstrument());
        $this->assertSame(SequenceType::FIRST, $mandate->getSequenceType());
    }

    public function testSepaCore(): void {
        $mandate = Mandate::sepaCore(
            mandateId: 'CORE-001',
            dateOfSignature: new DateTimeImmutable('2025-01-01'),
            creditorName: 'Test Creditor',
            creditorIban: 'DE89370400440532013000',
            creditorBic: 'COBADEFFXXX',
            creditorSchemeId: 'DE98ZZZ09999999999',
            debtorName: 'Test Debtor',
            debtorIban: 'DE89370400440000000001',
            debtorBic: 'DEUTDEFFXXX'
        );

        $this->assertSame('CORE-001', $mandate->getMandateId());
        $this->assertSame(LocalInstrument::SEPA_CORE, $mandate->getLocalInstrument());
        $this->assertSame('Test Creditor', $mandate->getCreditor()->getName());
        $this->assertSame('Test Debtor', $mandate->getDebtor()->getName());
    }

    public function testSepaB2B(): void {
        $mandate = Mandate::sepaB2B(
            mandateId: 'B2B-001',
            dateOfSignature: new DateTimeImmutable('2025-01-01'),
            creditorName: 'B2B Creditor',
            creditorIban: 'DE89370400440532013000',
            creditorBic: 'COBADEFFXXX',
            creditorSchemeId: 'DE98ZZZ09999999999',
            debtorName: 'B2B Debtor',
            debtorIban: 'DE89370400440000000001',
            debtorBic: 'DEUTDEFFXXX'
        );

        $this->assertSame('B2B-001', $mandate->getMandateId());
        $this->assertSame(LocalInstrument::SEPA_B2B, $mandate->getLocalInstrument());
    }

    public function testOptionalFields(): void {
        $mandate = new Mandate(
            mandateId: 'MNDT-001',
            dateOfSignature: new DateTimeImmutable(),
            creditor: PartyIdentification::fromName('Creditor'),
            creditorAccount: AccountIdentification::fromIban('DE89370400440532013000'),
            creditorAgent: FinancialInstitution::fromBic('COBADEFFXXX'),
            debtor: PartyIdentification::fromName('Debtor'),
            debtorAccount: AccountIdentification::fromIban('DE89370400440000000001'),
            debtorAgent: FinancialInstitution::fromBic('DEUTDEFFXXX'),
            maxAmount: 1000.00,
            electronicSignature: 'BASE64SIGNATURE',
            mandateReason: 'Mitgliedsbeitrag'
        );

        $this->assertSame(1000.00, $mandate->getMaxAmount());
        $this->assertSame('BASE64SIGNATURE', $mandate->getElectronicSignature());
        $this->assertSame('Mitgliedsbeitrag', $mandate->getMandateReason());
    }

    public function testReadonlyClass(): void {
        $mandate = $this->createTestMandate();
        $reflection = new \ReflectionClass($mandate);
        $this->assertTrue($reflection->isReadOnly());
    }
}
