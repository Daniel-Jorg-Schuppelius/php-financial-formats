<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt030DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt030DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type30\Document;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt030DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt030DocumentBuilder::create('MSG-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('MSG-001', $document->getHeaderMessageId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');

        $document = Camt030DocumentBuilder::create('MSG-002')
            ->withCreationDateTime($creationDateTime)
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withNotificationJustification('Fall wurde weitergeleitet')
            ->build();

        $this->assertEquals('MSG-002', $document->getHeaderMessageId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-001', $document->getCaseId());
        $this->assertEquals('Fall wurde weitergeleitet', $document->getNotificationJustification());
    }

    #[Test]
    public function testWithAssignerPartyName(): void {
        $document = Camt030DocumentBuilder::create('ASSIGN-003')
            ->withAssignerPartyName('Deutsche Bank AG')
            ->build();

        $this->assertEquals('Deutsche Bank AG', $document->getAssignerPartyName());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt030DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt030DocumentBuilder::create('ASSIGN-004');
        $builder2 = $builder1->withNotificationJustification('Test');

        $this->assertNotSame($builder1, $builder2);
    }
}
