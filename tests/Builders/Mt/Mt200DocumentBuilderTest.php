<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt200DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt200DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt2\Type200\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt200DocumentBuilderTest extends BaseTestCase {
    public function testCreateDocument(): void {
        $document = Mt200DocumentBuilder::create('REF-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertEquals('REF-001', $document->getTransactionReference());
        $this->assertEquals(MtType::MT200, $document->getMtType());
        $this->assertEquals(100000.00, $document->getAmount());
        $this->assertEquals(CurrencyCode::Euro, $document->getCurrency());
    }

    public function testWithAllOptionalFields(): void {
        $document = Mt200DocumentBuilder::create('REF-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->sendersCorrespondent('COBADEFFXXX')
            ->intermediary('INGBDEFFXXX')
            ->senderToReceiverInfo('/INS/Additional info')
            ->build();

        $this->assertNotNull($document->getSendersCorrespondent());
        $this->assertNotNull($document->getIntermediary());
        $this->assertEquals('/INS/Additional info', $document->getSenderToReceiverInfo());
    }

    public function testRequiresValueDate(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value Date is required');

        Mt200DocumentBuilder::create('REF-001')
            ->amount(100000.00, CurrencyCode::Euro)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->build();
    }

    public function testRequiresAmount(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount is required');

        Mt200DocumentBuilder::create('REF-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->accountWithInstitution('DEUTDEFFXXX')
            ->build();
    }

    public function testRequiresAccountWithInstitution(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account With Institution is required');

        Mt200DocumentBuilder::create('REF-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->build();
    }

    public function testToField32A(): void {
        $document = Mt200DocumentBuilder::create('REF-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(12345.67, CurrencyCode::USDollar)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->build();

        $field32A = $document->toField32A();
        $this->assertStringContainsString('240315', $field32A);
        // Note: CurrencyCode value is 'USD'
        $this->assertStringContainsString('USD', $field32A);
    }
}
