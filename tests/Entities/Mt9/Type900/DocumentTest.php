<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt9\Type900;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type900\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertSame('TXN-12345', $doc->getTransactionReference());
        $this->assertSame('REL-001', $doc->getRelatedReference());
        $this->assertSame('DE89370400440532013000', $doc->getAccountId());
        $this->assertSame(1500.50, $doc->getAmount());
        $this->assertSame(CurrencyCode::Euro, $doc->getCurrency());
    }

    #[Test]
    public function getMtType(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertSame(MtType::MT900, $doc->getMtType());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $orderingInstitution = new Party(bic: 'DEUTDEFF');

        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50,
            dateTimeIndication: new DateTimeImmutable('2026-01-09 10:30:00'),
            orderingInstitution: $orderingInstitution,
            senderToReceiverInfo: 'Info to receiver'
        );

        $this->assertNotNull($doc->getDateTimeIndication());
        $this->assertSame($orderingInstitution, $doc->getOrderingInstitution());
        $this->assertSame('Info to receiver', $doc->getSenderToReceiverInfo());
    }

    #[Test]
    public function getValueDate(): void {
        $date = new DateTimeImmutable('2026-01-09');

        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: $date,
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertEquals($date, $doc->getValueDate());
    }

    #[Test]
    public function dateTimeIndicationIsOptional(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertNull($doc->getDateTimeIndication());
    }

    #[Test]
    public function orderingInstitutionIsOptional(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 1500.50
        );

        $this->assertNull($doc->getOrderingInstitution());
    }

    #[Test]
    public function differentCurrencies(): void {
        $usd = new Document(
            transactionReference: 'TXN-USD',
            relatedReference: 'REL-USD',
            accountId: 'US1234567890',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::USDollar,
            amount: 2500.00
        );

        $this->assertSame(CurrencyCode::USDollar, $usd->getCurrency());
        $this->assertSame(2500.00, $usd->getAmount());
    }
}
