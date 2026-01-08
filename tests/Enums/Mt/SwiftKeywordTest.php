<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SwiftKeywordTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\CommonToolkit\FinancialFormats\Enums\Mt;

use CommonToolkit\FinancialFormats\Enums\Mt\SwiftKeyword;
use Tests\Contracts\BaseTestCase;

class SwiftKeywordTest extends BaseTestCase {

    public function testFormat(): void {
        $this->assertEquals('/EREF/ORDER12345/', SwiftKeyword::EREF->format('ORDER12345'));
        $this->assertEquals('/MREF/MANDATE001/', SwiftKeyword::MREF->format('MANDATE001'));
        $this->assertEquals('/REMI/Payment info/', SwiftKeyword::REMI->format('Payment info'));
    }

    public function testFormatWithSubKeyword(): void {
        $this->assertEquals('/BENM/NAME/Max Mustermann/', SwiftKeyword::BENM->format('Max Mustermann', 'NAME'));
        $this->assertEquals('/ORDP/NAME/Test Company/', SwiftKeyword::ORDP->format('Test Company', 'NAME'));
        $this->assertEquals('/PURP/CD/SALA/', SwiftKeyword::PURP->format('SALA', 'CD'));
    }

    public function testDescription(): void {
        $this->assertEquals('End-to-End Reference', SwiftKeyword::EREF->description());
        $this->assertEquals('End-to-End-Referenz', SwiftKeyword::EREF->descriptionDe());
        $this->assertEquals('Remittance Information', SwiftKeyword::REMI->description());
        $this->assertEquals('Verwendungszweck', SwiftKeyword::REMI->descriptionDe());
    }

    public function testMaxLength(): void {
        $this->assertEquals(35, SwiftKeyword::EREF->maxLength());
        $this->assertEquals(140, SwiftKeyword::REMI->maxLength());
        $this->assertEquals(70, SwiftKeyword::BENM->maxLength());
        $this->assertEquals(17, SwiftKeyword::TR->maxLength());
    }

    public function testHasSubKeywords(): void {
        $this->assertTrue(SwiftKeyword::BENM->hasSubKeywords());
        $this->assertTrue(SwiftKeyword::ORDP->hasSubKeywords());
        $this->assertTrue(SwiftKeyword::PURP->hasSubKeywords());
        $this->assertFalse(SwiftKeyword::EREF->hasSubKeywords());
        $this->assertFalse(SwiftKeyword::REMI->hasSubKeywords());
    }

    public function testSubKeywords(): void {
        $this->assertEquals(['NAME', 'ADDR', 'CITY', 'CTRY'], SwiftKeyword::BENM->subKeywords());
        $this->assertEquals(['CD', 'PRTRY'], SwiftKeyword::PURP->subKeywords());
        $this->assertEquals([], SwiftKeyword::EREF->subKeywords());
    }

    public function testFormatTruncatesLongValues(): void {
        $longValue = str_repeat('A', 50);
        $formatted = SwiftKeyword::EREF->format($longValue);

        // EREF has max length of 35
        $this->assertEquals('/EREF/' . str_repeat('A', 35) . '/', $formatted);
    }

    public function testAllCasesHaveDescriptions(): void {
        foreach (SwiftKeyword::cases() as $keyword) {
            $this->assertNotEmpty($keyword->description(), "Missing description for {$keyword->name}");
            $this->assertNotEmpty($keyword->descriptionDe(), "Missing German description for {$keyword->name}");
        }
    }
}
