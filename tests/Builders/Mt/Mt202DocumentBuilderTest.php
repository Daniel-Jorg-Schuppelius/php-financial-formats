<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt202DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt202DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt2\Type202\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt202DocumentBuilderTest extends BaseTestCase {
    public function testCreateDocument(): void {
        $document = Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('REF-001', $document->getTransactionReference());
        $this->assertEquals('RELATED-001', $document->getRelatedReference());
        $this->assertEquals(MtType::MT202, $document->getMtType());
        $this->assertEquals(100000.00, $document->getAmount());
        $this->assertFalse($document->isCoverPayment());
    }

    public function testCreateCoverPayment(): void {
        $document = Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->asCoverPayment()
            ->build();

        $this->assertEquals(MtType::MT202COV, $document->getMtType());
        $this->assertTrue($document->isCoverPayment());
    }

    public function testWithAllOptionalFields(): void {
        $document = Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->timeIndication('/CLSTIME/0915+0100')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->orderingInstitution('COBADEFFXXX')
            ->sendersCorrespondent('INGBDEFFXXX')
            ->receiversCorrespondent('BYLADEM1001')
            ->intermediary('SOGEDEFFXXX')
            ->accountWithInstitution('PBNKDEFFXXX')
            ->senderToReceiverInfo('/INS/Additional info')
            ->build();

        $this->assertEquals('/CLSTIME/0915+0100', $document->getTimeIndication());
        $this->assertNotNull($document->getOrderingInstitution());
        $this->assertNotNull($document->getSendersCorrespondent());
        $this->assertNotNull($document->getReceiversCorrespondent());
        $this->assertNotNull($document->getIntermediary());
        $this->assertNotNull($document->getAccountWithInstitution());
        $this->assertEquals('/INS/Additional info', $document->getSenderToReceiverInfo());
    }

    public function testRequiresValueDate(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value Date is required');

        Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->amount(100000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();
    }

    public function testRequiresAmount(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount is required');

        Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();
    }

    public function testRequiresBeneficiaryInstitution(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beneficiary Institution is required');

        Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->build();
    }

    public function testToField32A(): void {
        $document = Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(12345.67, CurrencyCode::BritishPound)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();

        $field32A = $document->toField32A();
        $this->assertStringContainsString('240315', $field32A);
        // CurrencyCode::BritishPound has value 'GBP'
        $this->assertStringContainsString('GBP', $field32A);
    }
}
