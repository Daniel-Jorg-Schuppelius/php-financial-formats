<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt087DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt087DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\ModificationRequest;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt087DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');
        $originalCreationDateTime = new DateTimeImmutable('2025-01-10 08:00:00');
        $settlementDate = new DateTimeImmutable('2025-01-12');

        $document = Camt087DocumentBuilder::create('ASSIGN-002')
            ->withCreationDateTime($creationDateTime)
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withOriginalMessage('MSG-001', 'pain.001.001.03', $originalCreationDateTime)
            ->withOriginalTransaction('TXN-001', 'E2E-001')
            ->withOriginalAmount(5000.00, CurrencyCode::Euro, $settlementDate)
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('MSG-001', $document->getOriginalMessageId());
        $this->assertEquals('pain.001.001.03', $document->getOriginalMessageNameId());
        $this->assertEquals('TXN-001', $document->getOriginalTransactionId());
        $this->assertEquals('E2E-001', $document->getOriginalEndToEndId());
        $this->assertEquals(5000.00, $document->getOriginalInterbankSettlementAmount());
    }

    #[Test]
    public function testAddModificationRequest(): void {
        $modRequest = new ModificationRequest(
            requestedSettlementAmount: 4500.00,
            requestedCurrency: CurrencyCode::Euro
        );

        $document = Camt087DocumentBuilder::create('ASSIGN-003')
            ->addModificationRequest($modRequest)
            ->build();

        $this->assertCount(1, $document->getModificationRequests());
        $this->assertEquals(4500.00, $document->getModificationRequests()[0]->getRequestedSettlementAmount());
    }

    #[Test]
    public function testRequestAmountChange(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-004')
            ->requestAmountChange(3000.00, CurrencyCode::Euro)
            ->build();

        $requests = $document->getModificationRequests();
        $this->assertCount(1, $requests);
        $this->assertTrue($requests[0]->hasAmountModification());
        $this->assertEquals(3000.00, $requests[0]->getRequestedSettlementAmount());
    }

    #[Test]
    public function testRequestCreditorChange(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-005')
            ->requestCreditorChange('Neue Firma GmbH', 'DE91100000000123456789')
            ->build();

        $requests = $document->getModificationRequests();
        $this->assertCount(1, $requests);
        $this->assertTrue($requests[0]->hasCreditorModification());
        $this->assertEquals('Neue Firma GmbH', $requests[0]->getCreditorName());
        $this->assertEquals('DE91100000000123456789', $requests[0]->getCreditorAccount());
    }

    #[Test]
    public function testRequestRemittanceChange(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-006')
            ->requestRemittanceChange('Neue Verwendungszweck-Information')
            ->build();

        $requests = $document->getModificationRequests();
        $this->assertCount(1, $requests);
        $this->assertEquals('Neue Verwendungszweck-Information', $requests[0]->getRemittanceInformation());
    }

    #[Test]
    public function testMultipleModificationRequests(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-007')
            ->requestAmountChange(2500.00, CurrencyCode::Euro)
            ->requestCreditorChange('Andere Firma', 'DE12345678901234567890')
            ->requestRemittanceChange('Korrigierter Verwendungszweck')
            ->build();

        $this->assertCount(3, $document->getModificationRequests());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt087DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt087DocumentBuilder::create('ASSIGN-008');
        $builder2 = $builder1->requestAmountChange(1000.00, CurrencyCode::Euro);

        $this->assertNotSame($builder1, $builder2);

        $doc1 = $builder1->build();
        $doc2 = $builder2->build();

        $this->assertEmpty($doc1->getModificationRequests());
        $this->assertCount(1, $doc2->getModificationRequests());
    }
}
