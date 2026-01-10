<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt9\Type920;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type920\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000'
        );

        $this->assertSame('TXN-12345', $doc->getTransactionReference());
        $this->assertSame('940', $doc->getRequestedMessageType());
        $this->assertSame('DE89370400440532013000', $doc->getAccountId());
    }

    #[Test]
    public function getMtType(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000'
        );

        $this->assertSame(MtType::MT920, $doc->getMtType());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000',
            floorLimitCurrency: 'EUR',
            floorLimitAmount: 1000.00,
            floorLimitIndicator: 'D',
            creationDateTime: new DateTimeImmutable('2026-01-09 08:00:00')
        );

        $this->assertSame('EUR', $doc->getFloorLimitCurrency());
        $this->assertSame(1000.00, $doc->getFloorLimitAmount());
        $this->assertSame('D', $doc->getFloorLimitIndicator());
        $this->assertNotNull($doc->getCreationDateTime());
    }

    #[Test]
    public function requestedMessageType940(): void {
        $doc = new Document(
            transactionReference: 'TXN-940',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000'
        );

        $this->assertSame('940', $doc->getRequestedMessageType());
    }

    #[Test]
    public function requestedMessageType941(): void {
        $doc = new Document(
            transactionReference: 'TXN-941',
            requestedMessageType: '941',
            accountId: 'DE89370400440532013000'
        );

        $this->assertSame('941', $doc->getRequestedMessageType());
    }

    #[Test]
    public function requestedMessageType942(): void {
        $doc = new Document(
            transactionReference: 'TXN-942',
            requestedMessageType: '942',
            accountId: 'DE89370400440532013000'
        );

        $this->assertSame('942', $doc->getRequestedMessageType());
    }

    #[Test]
    public function requestedMessageType950(): void {
        $doc = new Document(
            transactionReference: 'TXN-950',
            requestedMessageType: '950',
            accountId: 'DE89370400440532013000'
        );

        $this->assertSame('950', $doc->getRequestedMessageType());
    }

    #[Test]
    public function floorLimitIndicatorDebit(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000',
            floorLimitIndicator: 'D'
        );

        $this->assertSame('D', $doc->getFloorLimitIndicator());
    }

    #[Test]
    public function floorLimitIndicatorCredit(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000',
            floorLimitIndicator: 'C'
        );

        $this->assertSame('C', $doc->getFloorLimitIndicator());
    }

    #[Test]
    public function optionalFieldsAreNull(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            requestedMessageType: '940',
            accountId: 'DE89370400440532013000'
        );

        $this->assertNull($doc->getFloorLimitCurrency());
        $this->assertNull($doc->getFloorLimitAmount());
        $this->assertNull($doc->getFloorLimitIndicator());
        $this->assertNull($doc->getCreationDateTime());
    }
}
