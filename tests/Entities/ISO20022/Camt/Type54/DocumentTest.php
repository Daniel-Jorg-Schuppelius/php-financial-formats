<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Camt\Type54;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class DocumentTest extends BaseTestCase {
    public function testConstructorWithMinimalParameters(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15T10:30:00');

        $document = new Document(
            id: 'NTFCTN-001',
            creationDateTime: $creationDateTime,
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame('NTFCTN-001', $document->getId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertSame('DE89370400440532013000', $document->getAccountIdentifier());
        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
        $this->assertEmpty($document->getEntries());
    }

    public function testConstructorWithAllParameters(): void {
        $document = new Document(
            id: 'NTFCTN-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro,
            accountOwner: 'Test GmbH',
            servicerBic: 'COBADEFFXXX',
            messageId: 'MSG-001',
            sequenceNumber: '001'
        );

        $this->assertSame('Test GmbH', $document->getAccountOwner());
        $this->assertSame('COBADEFFXXX', $document->getServicerBic());
        $this->assertSame('MSG-001', $document->getMessageId());
        $this->assertSame('001', $document->getSequenceNumber());
    }

    public function testGetCamtType(): void {
        $document = new Document(
            id: 'NTFCTN-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $this->assertSame(CamtType::CAMT054, $document->getCamtType());
    }

    public function testToXmlGeneratesOutput(): void {
        $document = new Document(
            id: 'NTFCTN-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $xml = $document->toXml();

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('camt.054', $xml);
    }

    public function testToStringGeneratesXml(): void {
        $document = new Document(
            id: 'NTFCTN-001',
            creationDateTime: new DateTimeImmutable(),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );

        $string = (string)$document;
        $xml = $document->toXml();

        $this->assertSame($xml, $string);
    }
}
