<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt033DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt033DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type33\Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt033DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithOriginalTransaction(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-002')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withOriginalTransaction('MSG-001', 'E2E-001', 'TXN-001')
            ->withOriginalAmount(5000.00, CurrencyCode::Euro)
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals('TXN-001', $document->getOriginalTransactionId());
        $this->assertEquals('E2E-001', $document->getOriginalEndToEndId());
        $this->assertEquals(5000.00, $document->getOriginalInterbankSettlementAmount());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt033DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt033DocumentBuilder::create('ASSIGN-003');
        $builder2 = $builder1->withOriginalTransaction('MSG-001');

        $this->assertNotSame($builder1, $builder2);
    }
}
