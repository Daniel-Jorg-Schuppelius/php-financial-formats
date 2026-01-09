<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt910DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt910DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type910\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt910DocumentBuilderTest extends BaseTestCase {
    public function testCreateBuilder(): void {
        $builder = Mt910DocumentBuilder::create('CREDIT-001', 'REL-001');
        $this->assertInstanceOf(Mt910DocumentBuilder::class, $builder);
    }

    public function testBuildMinimalDocument(): void {
        $document = Mt910DocumentBuilder::create('CREDIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(2500.00, CurrencyCode::Euro)
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('CREDIT-001', $document->getTransactionReference());
        $this->assertSame('REL-001', $document->getRelatedReference());
        $this->assertSame('DE89370400440532013000', $document->getAccountId());
        $this->assertEquals(2500.00, $document->getAmount());
        $this->assertSame(CurrencyCode::Euro, $document->getCurrency());
        $this->assertSame(MtType::MT910, $document->getMtType());
    }

    public function testBuildWithOrderingCustomer(): void {
        $document = Mt910DocumentBuilder::create('CREDIT-002', 'REL-002')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-20'))
            ->amount(5000.00, CurrencyCode::USDollar)
            ->orderingCustomer('DE11520513735120710131', 'Max Mustermann', 'Musterstraße 1')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertNotNull($document->getOrderingCustomer());
        $this->assertSame('Max Mustermann', $document->getOrderingCustomer()->getName());
    }

    public function testBuildWithAllOptionalFields(): void {
        $document = Mt910DocumentBuilder::create('CREDIT-003', 'REL-003')
            ->account('GB33GSLD04296852369741')
            ->valueDate(new DateTimeImmutable('2024-03-25'))
            ->amount(10000.00, CurrencyCode::BritishPound)
            ->dateTimeIndication(new DateTimeImmutable('2024-03-25 13:14:00'))
            ->orderingCustomer('107044863', 'Petrochem', '29955 Lake Rd')
            ->orderingInstitution('GSCRUS30')
            ->intermediary('INGBDEFFXXX')
            ->senderToReceiverInfo('/EREF/TEST/FCCY/USD/DACT/107044863')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertNotNull($document->getDateTimeIndication());
        $this->assertNotNull($document->getOrderingCustomer());
        $this->assertNotNull($document->getOrderingInstitution());
        $this->assertNotNull($document->getIntermediary());
        $this->assertNotNull($document->getSenderToReceiverInfo());
    }

    public function testField32AFormat(): void {
        $document = Mt910DocumentBuilder::create('FORMAT-TEST', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-12-31'))
            ->amount(99999.99, CurrencyCode::SwissFranc)
            ->build();

        $field32A = $document->toField32A();
        $this->assertStringContainsString('241231', $field32A);
        $this->assertStringContainsString('CHF', $field32A);
        $this->assertStringContainsString('99999,99', $field32A);
    }

    public function testThrowsOnMissingAccount(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt910DocumentBuilder::create('CREDIT-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(2500.00, CurrencyCode::Euro)
            ->build();
    }

    public function testThrowsOnMissingValueDate(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt910DocumentBuilder::create('CREDIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->amount(2500.00, CurrencyCode::Euro)
            ->build();
    }

    public function testThrowsOnMissingAmount(): void {
        $this->expectException(InvalidArgumentException::class);

        Mt910DocumentBuilder::create('CREDIT-001', 'REL-001')
            ->account('DE89370400440532013000')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->build();
    }
}
