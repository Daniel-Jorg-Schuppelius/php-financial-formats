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

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt1\Type101;

use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $orderingCustomer = new Party(
            name: 'Customer GmbH',
            account: 'DE89370400440532013000'
        );

        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: $orderingCustomer,
            requestedExecutionDate: new DateTimeImmutable('2026-01-15')
        );

        $this->assertSame('REF-101', $doc->getSendersReference());
        $this->assertSame($orderingCustomer, $doc->getOrderingCustomer());
        $this->assertEquals(new DateTimeImmutable('2026-01-15'), $doc->getRequestedExecutionDate());
    }

    #[Test]
    public function getMtType(): void {
        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: new Party(name: 'Customer'),
            requestedExecutionDate: new DateTimeImmutable('2026-01-15')
        );

        $this->assertSame(MtType::MT101, $doc->getMtType());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $orderingCustomer = new Party(name: 'Customer GmbH');
        $orderingInstitution = new Party(bic: 'DEUTDEFF');

        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: $orderingCustomer,
            requestedExecutionDate: new DateTimeImmutable('2026-01-15'),
            transactions: [],
            orderingInstitution: $orderingInstitution,
            customerReference: 'CUST-REF-001',
            messageIndex: '2/3',
            creationDateTime: new DateTimeImmutable('2026-01-09')
        );

        $this->assertSame($orderingInstitution, $doc->getOrderingInstitution());
        $this->assertSame('CUST-REF-001', $doc->getCustomerReference());
        $this->assertSame('2/3', $doc->getMessageIndex());
    }

    #[Test]
    public function defaultMessageIndex(): void {
        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: new Party(name: 'Customer'),
            requestedExecutionDate: new DateTimeImmutable('2026-01-15')
        );

        $this->assertSame('1/1', $doc->getMessageIndex());
    }

    #[Test]
    public function emptyTransactions(): void {
        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: new Party(name: 'Customer'),
            requestedExecutionDate: new DateTimeImmutable('2026-01-15')
        );

        $this->assertSame([], $doc->getTransactions());
    }

    #[Test]
    public function customerReferenceIsOptional(): void {
        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: new Party(name: 'Customer'),
            requestedExecutionDate: new DateTimeImmutable('2026-01-15')
        );

        $this->assertNull($doc->getCustomerReference());
    }

    #[Test]
    public function orderingInstitutionIsOptional(): void {
        $doc = new Document(
            sendersReference: 'REF-101',
            orderingCustomer: new Party(name: 'Customer'),
            requestedExecutionDate: new DateTimeImmutable('2026-01-15')
        );

        $this->assertNull($doc->getOrderingInstitution());
    }
}
