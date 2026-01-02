<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ReferenceTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\Mt9;

use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

class ReferenceTest extends BaseTestCase {
    public function testConstructorWithMinimalParameters(): void {
        $reference = new Reference(
            transactionCode: 'TRF',
            reference: 'REF123'
        );

        $this->assertSame('TRF', $reference->getTransactionCode());
        $this->assertSame('REF123', $reference->getReference());
        $this->assertNull($reference->getBankReference());
    }

    public function testConstructorWithBankReference(): void {
        $reference = new Reference(
            transactionCode: 'CHK',
            reference: 'REF456',
            bankReference: 'BANK789'
        );

        $this->assertSame('CHK', $reference->getTransactionCode());
        $this->assertSame('REF456', $reference->getReference());
        $this->assertSame('BANK789', $reference->getBankReference());
    }

    public function testConstructorThrowsExceptionForTooLongReference(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MT9xx-Referenzüberschreitung');

        // TRF + 14 Zeichen = 17 Zeichen > max 16
        new Reference(
            transactionCode: 'TRF',
            reference: '12345678901234' // 14 chars + 3 = 17
        );
    }

    public function testMaxLengthReferenceAccepted(): void {
        // TRF (3) + 13 Zeichen = 16 -> OK
        $reference = new Reference(
            transactionCode: 'TRF',
            reference: '1234567890123'
        );

        $this->assertSame('1234567890123', $reference->getReference());
    }

    public function testToStringWithoutBankReference(): void {
        $reference = new Reference('TRF', 'REF123');

        $this->assertSame('NTRFREF123', (string)$reference);
    }

    public function testToStringWithBankReference(): void {
        $reference = new Reference('CHK', 'REF456', 'BANK789');

        $this->assertSame('NCHKREF456//BANK789', (string)$reference);
    }

    public function testFromSwiftFieldWithNPrefix(): void {
        $reference = Reference::fromSwiftField('NTRFREF12345');

        $this->assertSame('TRF', $reference->getTransactionCode());
        $this->assertSame('REF12345', $reference->getReference());
    }

    public function testFromSwiftFieldWithoutNPrefix(): void {
        $reference = Reference::fromSwiftField('CHKREF67890');

        $this->assertSame('CHK', $reference->getTransactionCode());
        $this->assertSame('REF67890', $reference->getReference());
    }

    public function testFromSwiftFieldWithBankReference(): void {
        $reference = Reference::fromSwiftField('NTRFREF123//BANKREF');

        $this->assertSame('TRF', $reference->getTransactionCode());
        $this->assertSame('REF123', $reference->getReference());
        $this->assertSame('BANKREF', $reference->getBankReference());
    }

    public function testFromSwiftFieldFallback(): void {
        // Kein gültiges Format (weniger als 3 Großbuchstaben) -> Fallback zu TRF
        $reference = Reference::fromSwiftField('12345');

        $this->assertSame('TRF', $reference->getTransactionCode());
        $this->assertSame('12345', $reference->getReference());
    }
}
