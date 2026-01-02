<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt038DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt038DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type38\Document;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt038DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('REQUEST-001', $document->getRequestId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');

        $document = Camt038DocumentBuilder::create('REQUEST-002')
            ->withCreationDateTime($creationDateTime)
            ->withRequesterAgent('DEUTDEFFXXX')
            ->withResponderAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $this->assertEquals('REQUEST-002', $document->getRequestId());
        $this->assertEquals($creationDateTime, $document->getCreationDateTime());
        $this->assertEquals('DEUTDEFFXXX', $document->getRequesterAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getResponderAgentBic());
        $this->assertEquals('CASE-001', $document->getCaseId());
    }

    #[Test]
    public function testWithRequesterPartyName(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-003')
            ->withRequesterPartyName('Deutsche Bank AG')
            ->build();

        $this->assertEquals('Deutsche Bank AG', $document->getRequesterPartyName());
    }

    #[Test]
    public function testWithResponderPartyName(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-004')
            ->withResponderPartyName('Commerzbank AG')
            ->build();

        $this->assertEquals('Commerzbank AG', $document->getResponderPartyName());
    }

    #[Test]
    public function testRequestIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt038DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt038DocumentBuilder::create('REQUEST-005');
        $builder2 = $builder1->withRequesterAgent('DEUTDEFFXXX');

        $this->assertNotSame($builder1, $builder2);
    }
}
