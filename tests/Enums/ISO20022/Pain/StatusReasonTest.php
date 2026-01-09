<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : StatusReasonTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Enums\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\StatusReasonCode;
use Tests\Contracts\BaseTestCase;

class StatusReasonTest extends BaseTestCase {
    public function testFromValidCode(): void {
        $reason = StatusReasonCode::from('AC01');
        $this->assertEquals(StatusReasonCode::AC01, $reason);
        $this->assertEquals('AC01', $reason->value);
    }

    public function testTryFromInvalidCode(): void {
        $reason = StatusReasonCode::tryFrom('INVALID');
        $this->assertNull($reason);
    }

    public function testTryFromString(): void {
        $reason = StatusReasonCode::tryFromString('AM04');
        $this->assertEquals(StatusReasonCode::AM04, $reason);

        $reason = StatusReasonCode::tryFromString('UNKNOWN');
        $this->assertNull($reason);
    }

    public function testGetDescription(): void {
        $this->assertEquals('Incorrect Account Number', StatusReasonCode::AC01->getDescription());
        $this->assertEquals('Insufficient Funds', StatusReasonCode::AM04->getDescription());
        $this->assertEquals('No Mandate', StatusReasonCode::MD01->getDescription());
        $this->assertEquals('Invalid File Format', StatusReasonCode::FF01->getDescription());
        $this->assertEquals('Order Cancelled', StatusReasonCode::DS02->getDescription());
    }

    public function testIsRejection(): void {
        $this->assertTrue(StatusReasonCode::AC01->isRejection());
        $this->assertTrue(StatusReasonCode::AM04->isRejection());
        $this->assertTrue(StatusReasonCode::FF01->isRejection());
        $this->assertTrue(StatusReasonCode::MD01->isRejection());

        $this->assertFalse(StatusReasonCode::CUST->isRejection());
        $this->assertFalse(StatusReasonCode::FOCR->isRejection());
    }

    public function testIsCancellation(): void {
        $this->assertTrue(StatusReasonCode::CUST->isCancellation());
        $this->assertTrue(StatusReasonCode::DS02->isCancellation());
        $this->assertTrue(StatusReasonCode::FOCR->isCancellation());
        $this->assertTrue(StatusReasonCode::MD06->isCancellation());

        $this->assertFalse(StatusReasonCode::AC01->isCancellation());
        $this->assertFalse(StatusReasonCode::AM04->isCancellation());
    }

    public function testIsTimeout(): void {
        $this->assertTrue(StatusReasonCode::AB05->isTimeout());
        $this->assertTrue(StatusReasonCode::AB10->isTimeout());
        $this->assertTrue(StatusReasonCode::AB11->isTimeout());
        $this->assertTrue(StatusReasonCode::TM01->isTimeout());

        $this->assertFalse(StatusReasonCode::AC01->isTimeout());
        $this->assertFalse(StatusReasonCode::AM04->isTimeout());
    }

    public function testAllSepaCodesPresent(): void {
        // SEPA-relevante Codes aus Anlage 3
        $sepaCodes = [
            'AC01',
            'AC02',
            'AC03',
            'AC04',
            'AC06',
            'AC13',
            'AG01',
            'AG02',
            'AG03',
            'AM01',
            'AM02',
            'AM03',
            'AM04',
            'AM05',
            'AM06',
            'AM07',
            'AM09',
            'AM10',
            'BE01',
            'BE04',
            'BE05',
            'BE06',
            'CUST',
            'DS01',
            'DS02',
            'DT01',
            'FF01',
            'MD01',
            'MD02',
            'MD06',
            'MD07',
            'MS02',
            'MS03',
            'NARR',
            'RC01',
            'RF01',
            'RR01',
            'RR02',
            'RR03',
            'RR04',
            'SL01',
            'TM01',
        ];

        foreach ($sepaCodes as $code) {
            $reason = StatusReasonCode::tryFrom($code);
            $this->assertNotNull($reason, "Missing SEPA code: $code");
        }
    }

    public function testCasesCount(): void {
        $cases = StatusReasonCode::cases();
        $this->assertGreaterThanOrEqual(70, count($cases));
    }
}
