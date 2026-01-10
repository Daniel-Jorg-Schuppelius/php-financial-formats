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

    public function testIsGpaForGpaApplication(): void {
        $gpaHeader = new BasicHeader(
            applicationId: 'A',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $finHeader = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $this->assertTrue($gpaHeader->isGpa());
        $this->assertFalse($finHeader->isGpa());
    }

    public function testToStringWithoutSessionAndSequence(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX'
        );

        $expected = '{1:F01COBADEFFAXXX}';
        $this->assertSame($expected, (string)$header);
    }

    public function testToStringWithSessionAndSequence(): void {
        $header = new BasicHeader(
            applicationId: 'F',
            serviceId: '01',
            logicalTerminalAddress: 'COBADEFFAXXX',
            sessionNumber: '1234',
            sequenceNumber: '567890'
        );

        $expected = '{1:F01COBADEFFAXXX1234567890}';
        $this->assertSame($expected, (string)$header);
    }

    public function testParseMinimal(): void {
        $raw = 'F01COBADEFFAXXX';
        $header = BasicHeader::parse($raw);

        $this->assertSame('F', $header->getApplicationId());
        $this->assertSame('01', $header->getServiceId());
        $this->assertSame('COBADEFFAXXX', $header->getLogicalTerminalAddress());
        $this->assertNull($header->getSessionNumber());
        $this->assertNull($header->getSequenceNumber());
    }

    public function testParseWithSessionAndSequence(): void {
        $raw = 'F01COBADEFFAXXX1234567890';
        $header = BasicHeader::parse($raw);

        $this->assertSame('F', $header->getApplicationId());
        $this->assertSame('01', $header->getServiceId());
        $this->assertSame('COBADEFFAXXX', $header->getLogicalTerminalAddress());
        $this->assertSame('1234', $header->getSessionNumber());
        $this->assertSame('567890', $header->getSequenceNumber());
    }

    public function testParseGpaHeader(): void {
        $raw = 'A21COBADEFFAXXX';
        $header = BasicHeader::parse($raw);

        $this->assertSame('A', $header->getApplicationId());
        $this->assertSame('21', $header->getServiceId());
        $this->assertTrue($header->isGpa());
        $this->assertFalse($header->isFin());
    }
}
