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

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt9\Type910;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type910\Document;
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
            amount: 2500.00
        );

        $this->assertSame('TXN-12345', $doc->getTransactionReference());
        $this->assertSame('REL-001', $doc->getRelatedReference());
        $this->assertSame('DE89370400440532013000', $doc->getAccountId());
        $this->assertSame(2500.00, $doc->getAmount());
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
            amount: 2500.00
        );

        $this->assertSame(MtType::MT910, $doc->getMtType());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $orderingCustomer = new Party(name: 'Customer GmbH', account: 'DE1234567890');
        $orderingInstitution = new Party(bic: 'DEUTDEFF');
        $intermediary = new Party(bic: 'COBADEFF');

        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 2500.00,
            dateTimeIndication: new DateTimeImmutable('2026-01-09 14:30:00'),
            orderingCustomer: $orderingCustomer,
            orderingInstitution: $orderingInstitution,
            intermediary: $intermediary,
            senderToReceiverInfo: 'Credit notification'
        );

        $this->assertNotNull($doc->getDateTimeIndication());
        $this->assertSame($orderingCustomer, $doc->getOrderingCustomer());
        $this->assertSame($orderingInstitution, $doc->getOrderingInstitution());
        $this->assertSame($intermediary, $doc->getIntermediary());
        $this->assertSame('Credit notification', $doc->getSenderToReceiverInfo());
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
            amount: 2500.00
        );

        $this->assertEquals($date, $doc->getValueDate());
    }

    #[Test]
    public function optionalFieldsAreNull(): void {
        $doc = new Document(
            transactionReference: 'TXN-12345',
            relatedReference: 'REL-001',
            accountId: 'DE89370400440532013000',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::Euro,
            amount: 2500.00
        );

        $this->assertNull($doc->getDateTimeIndication());
        $this->assertNull($doc->getOrderingCustomer());
        $this->assertNull($doc->getOrderingInstitution());
        $this->assertNull($doc->getIntermediary());
        $this->assertNull($doc->getSenderToReceiverInfo());
    }

    #[Test]
    public function differentCurrencies(): void {
        $gbp = new Document(
            transactionReference: 'TXN-GBP',
            relatedReference: 'REL-GBP',
            accountId: 'GB1234567890',
            valueDate: new DateTimeImmutable('2026-01-09'),
            currency: CurrencyCode::BritishPound,
            amount: 1200.00
        );

        $this->assertSame(CurrencyCode::BritishPound, $gbp->getCurrency());
    }
}
