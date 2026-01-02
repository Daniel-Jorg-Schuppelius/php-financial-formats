<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ApplicationHeaderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\Swift;

use CommonToolkit\FinancialFormats\Entities\Swift\ApplicationHeader;
use CommonToolkit\FinancialFormats\Enums\MtType;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class ApplicationHeaderTest extends BaseTestCase {
    public function testConstructorWithInputHeader(): void {
        $header = new ApplicationHeader(
            isOutput: false,
            messageType: MtType::MT940,
            receiverBic: 'COBADEFFXXX',
            priority: 'N'
        );

        $this->assertFalse($header->isOutput());
        $this->assertTrue($header->isInput());
        $this->assertSame(MtType::MT940, $header->getMessageType());
        $this->assertSame('COBADEFFXXX', $header->getReceiverBic());
        $this->assertSame('N', $header->getPriority());
    }

    public function testConstructorWithOutputHeader(): void {
        $inputDate = new DateTimeImmutable('2025-01-15');
        $outputDate = new DateTimeImmutable('2025-01-15');

        $header = new ApplicationHeader(
            isOutput: true,
            messageType: MtType::MT940,
            priority: 'N',
            inputTime: '1200',
            inputDate: $inputDate,
            messageInputReference: 'COBADEFF1234567890',
            outputDate: $outputDate,
            outputTime: '121500'
        );

        $this->assertTrue($header->isOutput());
        $this->assertFalse($header->isInput());
        $this->assertSame('1200', $header->getInputTime());
        $this->assertEquals($inputDate, $header->getInputDate());
        $this->assertSame('COBADEFF1234567890', $header->getMessageInputReference());
        $this->assertEquals($outputDate, $header->getOutputDate());
        $this->assertSame('121500', $header->getOutputTime());
    }

    public function testGetPriorityDescription(): void {
        $normalHeader = new ApplicationHeader(
            isOutput: false,
            messageType: MtType::MT940,
            priority: 'N'
        );

        $urgentHeader = new ApplicationHeader(
            isOutput: false,
            messageType: MtType::MT940,
            priority: 'U'
        );

        $systemHeader = new ApplicationHeader(
            isOutput: false,
            messageType: MtType::MT940,
            priority: 'S'
        );

        $unknownHeader = new ApplicationHeader(
            isOutput: false,
            messageType: MtType::MT940,
            priority: 'X'
        );

        $this->assertSame('Normal', $normalHeader->getPriorityDescription());
        $this->assertSame('Urgent', $urgentHeader->getPriorityDescription());
        $this->assertSame('System', $systemHeader->getPriorityDescription());
        $this->assertSame('Unknown', $unknownHeader->getPriorityDescription());
    }

    public function testNullableFieldsDefaultToNull(): void {
        $header = new ApplicationHeader(
            isOutput: false,
            messageType: MtType::MT940
        );

        $this->assertNull($header->getReceiverBic());
        $this->assertNull($header->getPriority());
        $this->assertNull($header->getInputTime());
        $this->assertNull($header->getInputDate());
        $this->assertNull($header->getMessageInputReference());
        $this->assertNull($header->getOutputDate());
        $this->assertNull($header->getOutputTime());
    }
}
