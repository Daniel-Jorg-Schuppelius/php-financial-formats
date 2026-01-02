<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ReferenceTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Camt\Type53;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Reference;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die CAMT.053 Reference Entity.
 */
class ReferenceTest extends BaseTestCase {
    public function testConstructorWithAllFields(): void {
        $reference = new Reference(
            endToEndId: 'EREF-12345',
            mandateId: 'MREF-67890',
            creditorId: 'DE98ZZZ09999999999',
            entryReference: 'ENTRY-001',
            accountServicerReference: 'ACCT-SVC-REF',
            paymentInformationId: 'PMT-INFO-001',
            instructionId: 'KREF-12345',
            additional: 'Zusätzliche Info'
        );

        $this->assertSame('EREF-12345', $reference->getEndToEndId());
        $this->assertSame('MREF-67890', $reference->getMandateId());
        $this->assertSame('DE98ZZZ09999999999', $reference->getCreditorId());
        $this->assertSame('ENTRY-001', $reference->getEntryReference());
        $this->assertSame('ACCT-SVC-REF', $reference->getAccountServicerReference());
        $this->assertSame('PMT-INFO-001', $reference->getPaymentInformationId());
        $this->assertSame('KREF-12345', $reference->getInstructionId());
        $this->assertSame('Zusätzliche Info', $reference->getAdditional());
    }

    public function testConstructorWithDefaults(): void {
        $reference = new Reference();

        $this->assertNull($reference->getEndToEndId());
        $this->assertNull($reference->getMandateId());
        $this->assertNull($reference->getCreditorId());
        $this->assertNull($reference->getEntryReference());
        $this->assertNull($reference->getAccountServicerReference());
        $this->assertNull($reference->getPaymentInformationId());
        $this->assertNull($reference->getInstructionId());
        $this->assertNull($reference->getAdditional());
    }

    public function testPartialReferences(): void {
        $reference = new Reference(
            endToEndId: 'EREF-001',
            mandateId: 'MREF-001'
        );

        $this->assertSame('EREF-001', $reference->getEndToEndId());
        $this->assertSame('MREF-001', $reference->getMandateId());
        $this->assertNull($reference->getCreditorId());
        $this->assertNull($reference->getInstructionId());
    }

    public function testHasAnyReference(): void {
        $emptyReference = new Reference();
        $this->assertFalse($emptyReference->hasAnyReference());

        $withEndToEnd = new Reference(endToEndId: 'EREF-001');
        $this->assertTrue($withEndToEnd->hasAnyReference());

        $withMandate = new Reference(mandateId: 'MREF-001');
        $this->assertTrue($withMandate->hasAnyReference());
    }
}
