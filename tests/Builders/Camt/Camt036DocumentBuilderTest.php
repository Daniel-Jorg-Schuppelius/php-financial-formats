<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt036DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt036DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type36\Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt036DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
        $this->assertFalse($document->isDebitAuthorised());
    }

    #[Test]
    public function testDocumentWithAuthorisation(): void {
        $valueDate = new DateTimeImmutable('2025-01-20');

        $document = Camt036DocumentBuilder::create('ASSIGN-002')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->authorised(true)
            ->withAuthorisedAmount(1000.00, CurrencyCode::Euro, $valueDate)
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertTrue($document->isDebitAuthorised());
        $this->assertEquals(1000.00, $document->getAuthorisedAmount());
        $this->assertEquals(CurrencyCode::Euro, $document->getAuthorisedCurrency());
        $this->assertEquals($valueDate, $document->getValueDate());
    }

    #[Test]
    public function testDocumentWithRejection(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-003')
            ->authorised(false)
            ->withReason('Nicht autorisiert durch Kontoinhaber')
            ->build();

        $this->assertFalse($document->isDebitAuthorised());
        $this->assertEquals('Nicht autorisiert durch Kontoinhaber', $document->getReason());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt036DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt036DocumentBuilder::create('ASSIGN-004');
        $builder2 = $builder1->authorised(true);

        $this->assertNotSame($builder1, $builder2);

        $doc1 = $builder1->build();
        $doc2 = $builder2->build();

        $this->assertFalse($doc1->isDebitAuthorised());
        $this->assertTrue($doc2->isDebitAuthorised());
    }
}
