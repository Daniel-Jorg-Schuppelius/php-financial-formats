<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GvcCodeTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\CommonToolkit\FinancialFormats\Enums\Mt;

use CommonToolkit\FinancialFormats\Enums\Mt\GvcCode;
use Tests\Contracts\BaseTestCase;

class GvcCodeTest extends BaseTestCase {

    public function testFromString(): void {
        $this->assertEquals(GvcCode::SEPA_CREDIT_TRANSFER, GvcCode::fromString('166'));
        $this->assertEquals(GvcCode::SEPA_CREDIT_TRANSFER, GvcCode::fromString('  166  '));
        $this->assertEquals(GvcCode::DIRECT_DEBIT, GvcCode::fromString('005'));
        $this->assertEquals(GvcCode::DIRECT_DEBIT, GvcCode::fromString('5')); // Should pad with zeros
    }

    public function testTryFromString(): void {
        $this->assertEquals(GvcCode::SEPA_CREDIT_TRANSFER, GvcCode::tryFromString('166'));
        $this->assertNull(GvcCode::tryFromString('999'));
        $this->assertNull(GvcCode::tryFromString('abc'));
    }

    public function testFromStringInvalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        GvcCode::fromString('999');
    }

    public function testDescription(): void {
        $this->assertEquals('SEPA-Überweisung', GvcCode::SEPA_CREDIT_TRANSFER->description());
        $this->assertEquals('SEPA Credit Transfer', GvcCode::SEPA_CREDIT_TRANSFER->descriptionEn());
    }

    public function testIsCredit(): void {
        $this->assertTrue(GvcCode::CREDIT->isCredit());
        $this->assertTrue(GvcCode::SEPA_CREDIT_TRANSFER->isCredit());
        $this->assertTrue(GvcCode::INTEREST_CREDIT->isCredit());
        $this->assertFalse(GvcCode::TRANSFER->isCredit());
        $this->assertFalse(GvcCode::DEBIT_SEPA->isCredit());
    }

    public function testIsDebit(): void {
        $this->assertTrue(GvcCode::TRANSFER->isDebit());
        $this->assertTrue(GvcCode::TRANSFER_SEPA->isDebit());
        $this->assertTrue(GvcCode::FEE->isDebit());
        $this->assertFalse(GvcCode::CREDIT->isDebit());
    }

    public function testIsSepa(): void {
        $this->assertTrue(GvcCode::TRANSFER_SEPA->isSepa());
        $this->assertTrue(GvcCode::SEPA_CREDIT_TRANSFER->isSepa());
        $this->assertTrue(GvcCode::SEPA_DIRECT_DEBIT->isSepa());
        $this->assertTrue(GvcCode::SEPA_RETURN->isSepa());
        $this->assertFalse(GvcCode::TRANSFER->isSepa());
        $this->assertFalse(GvcCode::INTERNATIONAL_TRANSFER->isSepa());
    }

    public function testIsReturn(): void {
        $this->assertTrue(GvcCode::DIRECT_DEBIT_RETURN->isReturn());
        $this->assertTrue(GvcCode::SEPA_RETURN->isReturn());
        $this->assertTrue(GvcCode::SEPA_REVERSAL->isReturn());
        $this->assertTrue(GvcCode::CHECK_RETURN->isReturn());
        $this->assertFalse(GvcCode::TRANSFER->isReturn());
    }

    public function testAllCasesHaveDescriptions(): void {
        foreach (GvcCode::cases() as $code) {
            $this->assertNotEmpty($code->description(), "Missing description for {$code->name}");
            $this->assertNotEmpty($code->descriptionEn(), "Missing English description for {$code->name}");
        }
    }
}
