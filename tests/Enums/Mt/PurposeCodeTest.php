<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PurposeCodeTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\CommonToolkit\FinancialFormats\Enums\Mt;

use CommonToolkit\FinancialFormats\Enums\Mt\PurposeCode;
use Tests\Contracts\BaseTestCase;

class PurposeCodeTest extends BaseTestCase {

    public function testTryFromString(): void {
        $this->assertEquals(PurposeCode::SALA, PurposeCode::tryFromString('SALA'));
        $this->assertEquals(PurposeCode::SALA, PurposeCode::tryFromString('sala'));
        $this->assertEquals(PurposeCode::SALA, PurposeCode::tryFromString('  SALA  '));
        $this->assertNull(PurposeCode::tryFromString('XXXX'));
    }

    public function testDescription(): void {
        $this->assertEquals('Salary Payment', PurposeCode::SALA->description());
        $this->assertEquals('Gehaltszahlung', PurposeCode::SALA->descriptionDe());
        $this->assertEquals('Rent Payment', PurposeCode::RENT->description());
        $this->assertEquals('Mietzahlung', PurposeCode::RENT->descriptionDe());
    }

    public function testCategory(): void {
        $this->assertEquals('salary', PurposeCode::SALA->category());
        $this->assertEquals('salary', PurposeCode::PENS->category());
        $this->assertEquals('salary', PurposeCode::BONU->category());

        $this->assertEquals('trade', PurposeCode::GDDS->category());
        $this->assertEquals('trade', PurposeCode::SUPP->category());

        $this->assertEquals('government', PurposeCode::TAXS->category());
        $this->assertEquals('government', PurposeCode::VATX->category());

        $this->assertEquals('financial', PurposeCode::LOAN->category());
        $this->assertEquals('financial', PurposeCode::INSU->category());

        $this->assertEquals('utilities', PurposeCode::ELEC->category());
        $this->assertEquals('utilities', PurposeCode::RENT->category());

        $this->assertEquals('other', PurposeCode::OTHR->category());
        $this->assertEquals('other', PurposeCode::CHAR->category());
    }

    public function testAllCasesHaveDescriptions(): void {
        foreach (PurposeCode::cases() as $code) {
            $this->assertNotEmpty($code->description(), "Missing description for {$code->name}");
            $this->assertNotEmpty($code->descriptionDe(), "Missing German description for {$code->name}");
            $this->assertNotEmpty($code->category(), "Missing category for {$code->name}");
        }
    }
}
