<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt031DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt031DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type31\Document;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt031DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithRejectionReason(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-002')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withRejectionReason('NOOR')
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals('NOOR', $document->getRejectionReasonCode());
    }

    #[Test]
    public function testDocumentWithProprietaryRejectionReason(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-003')
            ->withProprietaryRejectionReason('CUSTOM_REASON')
            ->withAdditionalInformation('Benutzerdefinierte Ablehnung')
            ->build();

        $this->assertEquals('CUSTOM_REASON', $document->getRejectionReasonProprietary());
        $this->assertEquals('Benutzerdefinierte Ablehnung', $document->getAdditionalInformation());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt031DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt031DocumentBuilder::create('ASSIGN-004');
        $builder2 = $builder1->withRejectionReason('NOOR');

        $this->assertNotSame($builder1, $builder2);
    }
}
