<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die Pain PaymentIdentification Entity.
 */
class PaymentIdentificationTest extends BaseTestCase {
    public function testConstructorWithAllFields(): void {
        $identification = new PaymentIdentification(
            endToEndId: 'E2E-12345',
            instructionId: 'INSTR-001',
            uetr: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
        );

        $this->assertSame('E2E-12345', $identification->getEndToEndId());
        $this->assertSame('INSTR-001', $identification->getInstructionId());
        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $identification->getUetr());
    }

    public function testConstructorWithMinimalFields(): void {
        $identification = new PaymentIdentification(
            endToEndId: 'E2E-001'
        );

        $this->assertSame('E2E-001', $identification->getEndToEndId());
        $this->assertNull($identification->getInstructionId());
        $this->assertNull($identification->getUetr());
    }

    public function testFromEndToEndId(): void {
        $identification = PaymentIdentification::fromEndToEndId('E2E-12345');

        $this->assertSame('E2E-12345', $identification->getEndToEndId());
        $this->assertNull($identification->getInstructionId());
        $this->assertNull($identification->getUetr());
    }

    public function testCreate(): void {
        $identification = PaymentIdentification::create(
            endToEndId: 'E2E-001',
            instructionId: 'INSTR-001'
        );

        $this->assertSame('E2E-001', $identification->getEndToEndId());
        $this->assertSame('INSTR-001', $identification->getInstructionId());
        $this->assertNull($identification->getUetr());
    }

    public function testWithUetr(): void {
        $identification = PaymentIdentification::withUetr(
            endToEndId: 'E2E-001',
            instructionId: 'INSTR-001'
        );

        $this->assertSame('E2E-001', $identification->getEndToEndId());
        $this->assertSame('INSTR-001', $identification->getInstructionId());
        $this->assertNotNull($identification->getUetr());
        // UUID v4 Format prüfen
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $identification->getUetr()
        );
    }

    public function testGenerateUetr(): void {
        $uetr = PaymentIdentification::generateUetr();

        $this->assertNotEmpty($uetr);
        // UUID v4 Format prüfen
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uetr
        );
    }

    public function testGenerateUetrIsUnique(): void {
        $uetr1 = PaymentIdentification::generateUetr();
        $uetr2 = PaymentIdentification::generateUetr();

        $this->assertNotSame($uetr1, $uetr2);
    }

    public function testReadonlyClass(): void {
        $identification = new PaymentIdentification(
            endToEndId: 'E2E-001'
        );

        $reflection = new \ReflectionClass($identification);
        $this->assertTrue($reflection->isReadOnly());
    }
}
