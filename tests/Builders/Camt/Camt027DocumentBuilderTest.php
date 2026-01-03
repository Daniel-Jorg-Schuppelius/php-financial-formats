<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt027DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt027DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type27\Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt027DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt027DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');

        $document = Camt027DocumentBuilder::create('ASSIGN-002')
            ->withCreationDateTime($creationDateTime)
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withOriginalTransaction('MSG-001', 'E2E-001', 'TXN-001')
            ->withOriginalAmount(2500.00, CurrencyCode::Euro)
            ->withMissingCoverIndicator(true)
            ->withDebtorName('Max Mustermann')
            ->withCreditorName('Firma GmbH')
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-001', $document->getCaseId());
        $this->assertEquals('TXN-001', $document->getOriginalTransactionId());
        $this->assertEquals(2500.00, $document->getOriginalInterbankSettlementAmount());
        $this->assertTrue($document->getMissingCoverIndicator());
        $this->assertEquals('Max Mustermann', $document->getDebtorName());
        $this->assertEquals('Firma GmbH', $document->getCreditorName());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('AssignmentId must not exceed 35 characters');

        Camt027DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt027DocumentBuilder::create('ASSIGN-003');
        $builder2 = $builder1->withMissingCoverIndicator(true);

        $this->assertNotSame($builder1, $builder2);
    }
}
