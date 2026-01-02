<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt039DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt039DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type39\Document;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt039DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt039DocumentBuilder::create('REPORT-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('REPORT-001', $document->getReportId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');

        $document = Camt039DocumentBuilder::create('REPORT-002')
            ->withCreationDateTime($creationDateTime)
            ->withReporterAgent('DEUTDEFFXXX')
            ->withReceiverAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withStatus('ACCP', 'Fall wird bearbeitet')
            ->withAdditionalInformation('Bearbeitung in Kürze abgeschlossen')
            ->build();

        $this->assertEquals('REPORT-002', $document->getReportId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertEquals('DEUTDEFFXXX', $document->getReporterAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getReceiverAgentBic());
        $this->assertEquals('CASE-001', $document->getCaseId());
        $this->assertEquals('ACCP', $document->getStatusCode());
        $this->assertEquals('Fall wird bearbeitet', $document->getStatusReason());
        $this->assertEquals('Bearbeitung in Kürze abgeschlossen', $document->getAdditionalInformation());
    }

    #[Test]
    public function testWithReporterPartyName(): void {
        $document = Camt039DocumentBuilder::create('REPORT-003')
            ->withReporterPartyName('Deutsche Bank AG')
            ->build();

        $this->assertEquals('Deutsche Bank AG', $document->getReporterPartyName());
    }

    #[Test]
    public function testReportIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt039DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt039DocumentBuilder::create('REPORT-004');
        $builder2 = $builder1->withStatus('PNDG');

        $this->assertNotSame($builder1, $builder2);

        $doc1 = $builder1->build();
        $doc2 = $builder2->build();

        $this->assertNull($doc1->getStatusCode());
        $this->assertEquals('PNDG', $doc2->getStatusCode());
    }
}
