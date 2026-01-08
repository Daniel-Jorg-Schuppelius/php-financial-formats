<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SepaKeywordTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\CommonToolkit\FinancialFormats\Enums\Mt;

use CommonToolkit\FinancialFormats\Enums\Mt\SepaKeyword;
use Tests\Contracts\BaseTestCase;

class SepaKeywordTest extends BaseTestCase {

    public function testFormat(): void {
        $this->assertEquals('EREF+ORDER12345', SepaKeyword::EREF->format('ORDER12345'));
        $this->assertEquals('MREF+MANDATE001', SepaKeyword::MREF->format('MANDATE001'));
        $this->assertEquals('SVWZ+Payment for invoice', SepaKeyword::SVWZ->format('Payment for invoice'));
    }

    public function testExtract(): void {
        $text = 'EREF+ORDER12345MREF+MANDATE001SVWZ+Payment for invoice';

        $this->assertEquals('ORDER12345', SepaKeyword::EREF->extract($text));
        $this->assertEquals('MANDATE001', SepaKeyword::MREF->extract($text));
        $this->assertEquals('Payment for invoice', SepaKeyword::SVWZ->extract($text));
    }

    public function testExtractNotFound(): void {
        $text = 'EREF+ORDER12345';

        $this->assertEquals('ORDER12345', SepaKeyword::EREF->extract($text));
        $this->assertNull(SepaKeyword::MREF->extract($text));
        $this->assertNull(SepaKeyword::SVWZ->extract($text));
    }

    public function testParseAll(): void {
        $text = 'EREF+ORDER12345MREF+MANDATE001CRED+DE98ZZZ09999999999SVWZ+Rechnung 2025';

        $result = SepaKeyword::parseAll($text);

        $this->assertArrayHasKey('EREF', $result);
        $this->assertArrayHasKey('MREF', $result);
        $this->assertArrayHasKey('CRED', $result);
        $this->assertArrayHasKey('SVWZ', $result);

        $this->assertEquals('ORDER12345', $result['EREF']);
        $this->assertEquals('MANDATE001', $result['MREF']);
        $this->assertEquals('DE98ZZZ09999999999', $result['CRED']);
        $this->assertEquals('Rechnung 2025', $result['SVWZ']);
    }

    public function testDescription(): void {
        $this->assertEquals('End-to-End Reference', SepaKeyword::EREF->description());
        $this->assertEquals('End-to-End-Referenz', SepaKeyword::EREF->descriptionDe());
        $this->assertEquals('Remittance Information', SepaKeyword::SVWZ->description());
        $this->assertEquals('SEPA-Verwendungszweck', SepaKeyword::SVWZ->descriptionDe());
    }

    public function testMaxLength(): void {
        $this->assertEquals(35, SepaKeyword::EREF->maxLength());
        $this->assertEquals(140, SepaKeyword::SVWZ->maxLength());
        $this->assertEquals(34, SepaKeyword::IBAN->maxLength());
        $this->assertEquals(11, SepaKeyword::BIC->maxLength());
    }

    public function testFormatTruncatesLongValues(): void {
        $longValue = str_repeat('A', 50);
        $formatted = SepaKeyword::EREF->format($longValue);

        // EREF has max length of 35
        $this->assertEquals('EREF+' . str_repeat('A', 35), $formatted);
    }
}
