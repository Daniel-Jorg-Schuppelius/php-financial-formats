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

    public function testFromValue(): void {
        $this->assertEquals(GvcCode::SEPA_CT_SINGLE_CREDIT, GvcCode::fromValue('166'));
        $this->assertEquals(GvcCode::SEPA_CT_SINGLE_CREDIT, GvcCode::fromValue('  166  '));
        $this->assertEquals(GvcCode::CREDIT_CARD_SETTLEMENT, GvcCode::fromValue('006'));
        $this->assertEquals(GvcCode::CREDIT_CARD_SETTLEMENT, GvcCode::fromValue('6')); // Should pad with zeros
    }

    public function testTryFromValue(): void {
        $this->assertEquals(GvcCode::SEPA_CT_SINGLE_CREDIT, GvcCode::tryFromValue('166'));
        $this->assertEquals(GvcCode::UNSTRUCTURED, GvcCode::tryFromValue('999'));
        $this->assertNull(GvcCode::tryFromValue('abc'));
        $this->assertNull(GvcCode::tryFromValue('000')); // Unknown code
    }

    public function testFromValueInvalid(): void {
        $this->expectException(\InvalidArgumentException::class);
        GvcCode::fromValue('000'); // Unknown code
    }

    public function testDescription(): void {
        $this->assertEquals('SEPA-Überweisung Einzelbuchung Haben', GvcCode::SEPA_CT_SINGLE_CREDIT->description());
        $this->assertEquals('SEPA Credit Transfer Single Credit', GvcCode::SEPA_CT_SINGLE_CREDIT->descriptionEn());
        $this->assertEquals('Kreditkartenabrechnung', GvcCode::CREDIT_CARD_SETTLEMENT->description());
        $this->assertEquals('Storno', GvcCode::REVERSAL->description());
    }

    public function testIsSepa(): void {
        $this->assertTrue(GvcCode::SEPA_CT_SINGLE_CREDIT->isSepa());
        $this->assertTrue(GvcCode::SEPA_DD_SINGLE_CORE->isSepa());
        $this->assertTrue(GvcCode::SEPA_DD_RETURN_CORE->isSepa());
        $this->assertTrue(GvcCode::SEPA_CT_INSTANT_CREDIT->isSepa());
        $this->assertFalse(GvcCode::FOREIGN_TRANSFER->isSepa());
        $this->assertFalse(GvcCode::SECURITIES->isSepa());
    }

    public function testIsReturn(): void {
        $this->assertTrue(GvcCode::SEPA_DD_RETURN_B2B->isReturn());
        $this->assertTrue(GvcCode::SEPA_DD_RETURN_CORE->isReturn());
        $this->assertTrue(GvcCode::SEPA_CT_RETURN->isReturn());
        $this->assertTrue(GvcCode::REVERSAL->isReturn());
        $this->assertTrue(GvcCode::CHECK_REVERSAL->isReturn());
        $this->assertFalse(GvcCode::SEPA_CT_SINGLE_CREDIT->isReturn());
    }

    public function testIsInstant(): void {
        $this->assertTrue(GvcCode::SEPA_CT_INSTANT_SINGLE_DEBIT->isInstant());
        $this->assertTrue(GvcCode::SEPA_CT_INSTANT_CREDIT->isInstant());
        $this->assertTrue(GvcCode::SEPA_CT_INSTANT_SALARY->isInstant());
        $this->assertFalse(GvcCode::SEPA_CT_SINGLE_CREDIT->isInstant());
        $this->assertFalse(GvcCode::SEPA_DD_SINGLE_CORE->isInstant());
    }

    public function testIsB2B(): void {
        $this->assertTrue(GvcCode::SEPA_DD_SINGLE_B2B->isB2B());
        $this->assertTrue(GvcCode::SEPA_DD_RETURN_B2B->isB2B());
        $this->assertTrue(GvcCode::SEPA_DD_COLLECTION_B2B->isB2B());
        $this->assertFalse(GvcCode::SEPA_DD_SINGLE_CORE->isB2B());
        $this->assertFalse(GvcCode::SEPA_CT_SINGLE_CREDIT->isB2B());
    }

    public function testGetCategory(): void {
        $this->assertEquals(0, GvcCode::CREDIT_CARD_SETTLEMENT->getCategory());
        $this->assertEquals(1, GvcCode::SEPA_CT_SINGLE_CREDIT->getCategory());
        $this->assertEquals(2, GvcCode::FOREIGN_TRANSFER->getCategory());
        $this->assertEquals(3, GvcCode::SECURITIES->getCategory());
        $this->assertEquals(4, GvcCode::FX_SPOT->getCategory());
        $this->assertEquals(6, GvcCode::LOAN_INTEREST->getCategory());
        $this->assertEquals(8, GvcCode::FEES->getCategory());
        $this->assertEquals(9, GvcCode::UNSTRUCTURED->getCategory());
    }

    public function testGetCategoryName(): void {
        $this->assertEquals('Zahlungsverkehr EU/EWR', GvcCode::CREDIT_CARD_SETTLEMENT->getCategoryName());
        $this->assertEquals('Zahlungsverkehr EU/EWR', GvcCode::SEPA_CT_SINGLE_CREDIT->getCategoryName());
        $this->assertEquals('Auslandsgeschäft', GvcCode::FOREIGN_TRANSFER->getCategoryName());
        $this->assertEquals('Wertpapiergeschäft', GvcCode::SECURITIES->getCategoryName());
        $this->assertEquals('Devisengeschäft', GvcCode::FX_SPOT->getCategoryName());
        $this->assertEquals('Kreditgeschäft', GvcCode::LOAN_INTEREST->getCategoryName());
        $this->assertEquals('Sonstige', GvcCode::FEES->getCategoryName());
        $this->assertEquals('Unstrukturiert', GvcCode::UNSTRUCTURED->getCategoryName());
    }

    public function testForCategory(): void {
        $category2 = GvcCode::forCategory(2);
        $this->assertNotEmpty($category2);
        foreach ($category2 as $code) {
            $this->assertEquals(2, $code->getCategory());
        }

        $category3 = GvcCode::forCategory(3);
        $this->assertNotEmpty($category3);
        $this->assertContains(GvcCode::SECURITIES, $category3);
    }

    public function testAllCasesHaveDescriptions(): void {
        foreach (GvcCode::cases() as $code) {
            $this->assertNotEmpty($code->description(), "Missing description for {$code->name}");
            $this->assertNotEmpty($code->descriptionEn(), "Missing English description for {$code->name}");
        }
    }

    public function testDeprecatedMethods(): void {
        // Test backward compatibility
        $this->assertEquals(GvcCode::SEPA_CT_SINGLE_CREDIT, GvcCode::fromString('166'));
        $this->assertEquals(GvcCode::SEPA_CT_SINGLE_CREDIT, GvcCode::tryFromString('166'));
        $this->assertNull(GvcCode::tryFromString('000'));
    }
}
