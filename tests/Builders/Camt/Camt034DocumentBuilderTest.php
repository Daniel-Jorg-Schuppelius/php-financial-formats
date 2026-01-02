<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt034DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt034DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type34\Document;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt034DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt034DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithDuplicateContent(): void {
        $content = base64_encode('Dies ist der duplizierte Dokumentinhalt');

        $document = Camt034DocumentBuilder::create('ASSIGN-002')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withDuplicateContent($content)
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals($content, $document->getDuplicateContent());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt034DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt034DocumentBuilder::create('ASSIGN-003');
        $builder2 = $builder1->withDuplicateContent('content');

        $this->assertNotSame($builder1, $builder2);

        $doc1 = $builder1->build();
        $doc2 = $builder2->build();

        $this->assertNull($doc1->getDuplicateContent());
        $this->assertEquals('content', $doc2->getDuplicateContent());
    }
}
