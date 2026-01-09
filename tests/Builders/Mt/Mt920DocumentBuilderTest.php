<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt920DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt920DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type920\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt920DocumentBuilderTest extends BaseTestCase {
    public function testCreateBuilder(): void {
        $builder = Mt920DocumentBuilder::create('REQ-001');
        $this->assertInstanceOf(Mt920DocumentBuilder::class, $builder);
    }

    public function testBuildRequestMt940(): void {
        $document = Mt920DocumentBuilder::create('REQ-001')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('REQ-001', $document->getTransactionReference());
        $this->assertSame('DE89370400440532013000', $document->getAccountId());
        $this->assertSame('940', $document->getRequestedMessageType());
        $this->assertSame(MtType::MT920, $document->getMtType());
    }

    public function testBuildRequestMt941(): void {
        $document = Mt920DocumentBuilder::create('REQ-002')
            ->account('DE89370400440532013000')
            ->requestMt941()
            ->build();

        $this->assertSame('941', $document->getRequestedMessageType());
    }

    public function testBuildRequestMt942(): void {
        $document = Mt920DocumentBuilder::create('REQ-003')
            ->account('DE89370400440532013000')
            ->requestMt942()
            ->build();

        $this->assertSame('942', $document->getRequestedMessageType());
    }

    public function testBuildRequestMt950(): void {
        $document = Mt920DocumentBuilder::create('REQ-004')
            ->account('DE89370400440532013000')
            ->requestMt950()
            ->build();

        $this->assertSame('950', $document->getRequestedMessageType());
    }

    public function testBuildWithFloorLimit(): void {
        $document = Mt920DocumentBuilder::create('REQ-005')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->floorLimit('EUR', 1000.00)
            ->build();

        $this->assertTrue($document->hasFloorLimit());
        $this->assertSame('EUR', $document->getFloorLimitCurrency());
        $this->assertEquals(1000.00, $document->getFloorLimitAmount());
    }

    public function testBuildWithFloorLimitIndicator(): void {
        $document = Mt920DocumentBuilder::create('REQ-006')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->floorLimit('EUR', 500.00, 'D')
            ->build();

        $this->assertSame('D', $document->getFloorLimitIndicator());
        $this->assertSame('EURD500,00', $document->toField34F());
    }

    public function testField34FFormat(): void {
        $document = Mt920DocumentBuilder::create('FORMAT-TEST')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->floorLimit('USD', 2500.50, 'C')
            ->build();

        $this->assertSame('USDC2500,50', $document->toField34F());
    }

    public function testThrowsOnMissingAccount(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt920DocumentBuilder::create('REQ-001')
            ->requestMt940()
            ->build();
    }

    public function testThrowsOnMissingMessageType(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt920DocumentBuilder::create('REQ-001')
            ->account('DE89370400440532013000')
            ->build();
    }

    public function testThrowsOnInvalidMessageType(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt920DocumentBuilder::create('REQ-001')
            ->account('DE89370400440532013000')
            ->requestMessageType('999')
            ->build();
    }

    public function testThrowsOnInvalidFloorLimitIndicator(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt920DocumentBuilder::create('REQ-001')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->floorLimit('EUR', 100.00, 'X')
            ->build();
    }
}
