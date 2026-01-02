<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BasicHeaderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\Swift;

use CommonToolkit\FinancialFormats\Entities\Swift\BasicHeader;
use Tests\Contracts\BaseTestCase;

class BasicHeaderTest extends BaseTestCase {
    public function testConstructorWithMinimalParameters(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFXXX'
        );

        $this->assertSame('F', $header->getApplicationId());
        $this->assertSame('01', $header->getServiceId());
        $this->assertSame('COBADEFFXXX', $header->getLogicalTerminalAddress());
        $this->assertNull($header->getSessionNumber());
        $this->assertNull($header->getSequenceNumber());
    }

    public function testConstructorWithAllParameters(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX',
            sessionNumber: '1234',
            sequenceNumber: '567890'
        );

        $this->assertSame('1234', $header->getSessionNumber());
        $this->assertSame('567890', $header->getSequenceNumber());
    }

    public function testGetBicFromLogicalTerminalAddress(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $this->assertSame('COBADEFF', $header->getBic());
    }

    public function testGetTerminalCodeFromLogicalTerminalAddress(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $this->assertSame('A', $header->getTerminalCode());
    }

    public function testGetBranchFromLogicalTerminalAddress(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $this->assertSame('XXX', $header->getBranch());
    }

    public function testIsFinForFinApplication(): void {
        $finHeader = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $gpaHeader = new BasicHeader(
            applicationId: 'A',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $this->assertTrue($finHeader->isFin());
        $this->assertFalse($gpaHeader->isFin());
    }
}
