<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt900DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt900DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type900\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt900DocumentBuilderTest extends BaseTestCase {
    public function testCreateBuilder(): void {
        $builder = Mt900DocumentBuilder::create('DEBIT-001', 'REL-001');
        $this->assertInstanceOf(Mt900DocumentBuilder::class, $builder);
    }

    public function testBuildMinimalDocument(): void {
        $document = Mt900DocumentBuilder::create('DEBIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(1500.00, CurrencyCode::Euro)
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('DEBIT-001', $document->getTransactionReference());
        $this->assertSame('REL-001', $document->getRelatedReference());
        $this->assertSame('DE89370400440532013000', $document->getAccountId());
        $this->assertEquals(1500.00, $document->getAmount());
        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
        $this->assertSame(MtType::MT900, $document->getMtType());
    }

    public function testBuildWithOptionalFields(): void {
        $document = Mt900DocumentBuilder::create('DEBIT-002', 'REL-002')
            ->account('GB33GSLD04296852369741')
            ->valueDate(new DateTimeImmutable('2024-03-20'))
            ->amount(2500.50, CurrencyCode::BritishPound)
            ->dateTimeIndication(new DateTimeImmutable('2024-03-20 08:14:00'))
            ->orderingInstitution('COBADEFFXXX')
            ->senderToReceiverInfo('/EREF/TEST-REF/REMI/Payment info')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertNotNull($document->getDateTimeIndication());
        $this->assertNotNull($document->getOrderingInstitution());
        $this->assertSame('/EREF/TEST-REF/REMI/Payment info', $document->getSenderToReceiverInfo());
    }

    public function testField32AFormat(): void {
        $document = Mt900DocumentBuilder::create('FORMAT-TEST', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-06-30'))
            ->amount(12345.67, CurrencyCode::Euro)
            ->build();

        $field32A = $document->toField32A();
        $this->assertStringContainsString('240630', $field32A);
        $this->assertStringContainsString('EUR', $field32A);
        $this->assertStringContainsString('12345,67', $field32A);
    }

    public function testThrowsOnMissingAccount(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt900DocumentBuilder::create('DEBIT-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(1500.00, CurrencyCode::Euro)
            ->build();
    }

    public function testThrowsOnMissingValueDate(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt900DocumentBuilder::create('DEBIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->amount(1500.00, CurrencyCode::Euro)
            ->build();
    }

    public function testThrowsOnMissingAmount(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt900DocumentBuilder::create('DEBIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->build();
    }
}
