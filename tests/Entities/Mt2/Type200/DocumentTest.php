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

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt2\Type200;

use CommonToolkit\FinancialFormats\Entities\Mt2\Type200\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $accountWithInstitution = new Party(bic: 'DEUTDEFF');

        $doc = new Document(
            transactionReference: 'TXN-200',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 100000.00,
            accountWithInstitution: $accountWithInstitution
        );

        $this->assertSame('TXN-200', $doc->getTransactionReference());
        $this->assertSame(100000.00, $doc->getAmount());
        $this->assertSame(CurrencyCode::Euro, $doc->getCurrency());
        $this->assertSame($accountWithInstitution, $doc->getAccountWithInstitution());
    }

    #[Test]
    public function getMtType(): void {
        $doc = new Document(
            transactionReference: 'TXN-200',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 100000.00,
            accountWithInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertSame(MtType::MT200, $doc->getMtType());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $accountWithInstitution = new Party(bic: 'DEUTDEFF');
        $sendersCorrespondent = new Party(bic: 'COBADEFF');
        $intermediary = new Party(bic: 'INGBDEFF');

        $doc = new Document(
            transactionReference: 'TXN-200',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 100000.00,
            accountWithInstitution: $accountWithInstitution,
            sendersCorrespondent: $sendersCorrespondent,
            intermediary: $intermediary,
            senderToReceiverInfo: 'Internal transfer'
        );

        $this->assertSame($sendersCorrespondent, $doc->getSendersCorrespondent());
        $this->assertSame($intermediary, $doc->getIntermediary());
        $this->assertSame('Internal transfer', $doc->getSenderToReceiverInfo());
    }

    #[Test]
    public function getValueDate(): void {
        $date = new DateTimeImmutable('2026-01-15');

        $doc = new Document(
            transactionReference: 'TXN-200',
            valueDate: $date,
            currency: CurrencyCode::Euro,
            amount: 100000.00,
            accountWithInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertEquals($date, $doc->getValueDate());
    }

    #[Test]
    public function optionalFieldsAreNull(): void {
        $doc = new Document(
            transactionReference: 'TXN-200',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 100000.00,
            accountWithInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertNull($doc->getSendersCorrespondent());
        $this->assertNull($doc->getIntermediary());
        $this->assertNull($doc->getSenderToReceiverInfo());
    }

    #[Test]
    public function differentCurrencies(): void {
        $usd = new Document(
            transactionReference: 'TXN-USD',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::USDollar,
            amount: 150000.00,
            accountWithInstitution: new Party(bic: 'BOFAUS3N')
        );

        $this->assertSame(CurrencyCode::USDollar, $usd->getCurrency());
        $this->assertSame(150000.00, $usd->getAmount());
    }
}
