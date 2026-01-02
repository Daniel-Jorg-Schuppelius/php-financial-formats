<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt037DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt037DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type37\Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt037DocumentBuilderTest extends BaseTestCase {
    #[Test]
    public function testBasicDocumentCreation(): void {
        $document = Camt037DocumentBuilder::create('ASSIGN-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('ASSIGN-001', $document->getAssignmentId());
    }

    #[Test]
    public function testDocumentWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15 10:30:00');

        $document = Camt037DocumentBuilder::create('ASSIGN-002')
            ->withCreationDateTime($creationDateTime)
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->forCase('CASE-001', 'Bank AG')
            ->withUnderlyingTransaction('TXN-001', 'E2E-001')
            ->withInstructedAmount(1500.00, CurrencyCode::Euro)
            ->withDebtor('Max Mustermann', 'DE89370400440532013000')
            ->withReason('Autorisierung erforderlich')
            ->build();

        $this->assertEquals('ASSIGN-002', $document->getAssignmentId());
        $this->assertEquals('TXN-001', $document->getOriginalTransactionId());
        $this->assertEquals(1500.00, $document->getOriginalInterbankSettlementAmount());
        $this->assertEquals('Max Mustermann', $document->getDebtorName());
        $this->assertEquals('DE89370400440532013000', $document->getDebtorAccountIban());
        $this->assertEquals('Autorisierung erforderlich', $document->getReason());
    }

    #[Test]
    public function testAssignmentIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);

        Camt037DocumentBuilder::create(str_repeat('A', 36));
    }

    #[Test]
    public function testBuilderIsImmutable(): void {
        $builder1 = Camt037DocumentBuilder::create('ASSIGN-003');
        $builder2 = $builder1->withDebtor('Test', 'DE123');

        $this->assertNotSame($builder1, $builder2);
    }
}
