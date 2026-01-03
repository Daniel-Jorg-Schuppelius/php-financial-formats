<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt026DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt026DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\UnableToApplyReason;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt026DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');

        $document = Camt026DocumentBuilder::create('ASSIGN-002')
            ->withCreationDateTime($creationDateTime)
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withOriginalTransaction('MSG-001', 'E2E-001', 'TXN-001')
            ->withOriginalAmount(1000.00, CurrencyCode::Euro)
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-001', $document->getCaseId());
        $this->assertEquals('Bank AG', $document->getCaseCreator());
        $this->assertEquals('TXN-001', $document->getOriginalTransactionId());
        $this->assertEquals('E2E-001', $document->getOriginalEndToEndId());
        $this->assertEquals(1000.00, $document->getOriginalInterbankSettlementAmount());
        $this->assertEquals(CurrencyCode::Euro, $document->getOriginalCurrency());
    }

    #[Test]
    public function testAddUnableToApplyReason(): void {
        $reason1 = new UnableToApplyReason(reasonCode: 'AM09', additionalInformation: 'Falscher Betrag');
        $reason2 = new UnableToApplyReason(reasonCode: 'RC01', additionalInformation: 'Ungültige BIC');

        $document = Camt026DocumentBuilder::create('ASSIGN-003')
            ->addUnableToApplyReason($reason1)
            ->addUnableToApplyReason($reason2)
            ->build();

        $reasons = $document->getUnableToApplyReasons();
        $this->assertCount(2, $reasons);
        $this->assertEquals('AM09', $reasons[0]->getReasonCode());
        $this->assertEquals('Falscher Betrag', $reasons[0]->getAdditionalInformation());
    }

    #[Test]
    public function testWithAssignerPartyName(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-004')
            ->withAssignerPartyName('Deutsche Bank AG')
            ->build();

        $this->assertEquals('Deutsche Bank AG', $document->getAssignerPartyName());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AssignmentId must not exceed 35 characters');

        Camt026DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt026DocumentBuilder::create('ASSIGN-005');
        $builder2 = $builder1->withAssignerAgent('DEUTDEFFXXX');

        $this->assertNotSame($builder1, $builder2);

        $doc1 = $builder1->build();
        $doc2 = $builder2->build();

        $this->assertNull($doc1->getAssignerAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $doc2->getAssignerAgentBic());
    }
}
