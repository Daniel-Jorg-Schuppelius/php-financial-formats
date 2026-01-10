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

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt2\Type202;

use CommonToolkit\FinancialFormats\Entities\Mt2\Type202\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $beneficiaryInstitution = new Party(bic: 'DEUTDEFF');

        $doc = new Document(
            transactionReference: 'TXN-202',
            relatedReference: 'REL-202',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: $beneficiaryInstitution
        );

        $this->assertSame('TXN-202', $doc->getTransactionReference());
        $this->assertSame('REL-202', $doc->getRelatedReference());
        $this->assertSame(500000.00, $doc->getAmount());
        $this->assertSame(CurrencyCode::Euro, $doc->getCurrency());
        $this->assertSame($beneficiaryInstitution, $doc->getBeneficiaryInstitution());
    }

    #[Test]
    public function getMtTypeForNormalTransfer(): void {
        $doc = new Document(
            transactionReference: 'TXN-202',
            relatedReference: 'REL-202',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertSame(MtType::MT202, $doc->getMtType());
    }

    #[Test]
    public function getMtTypeForCoverPayment(): void {
        $doc = new Document(
            transactionReference: 'TXN-202COV',
            relatedReference: 'REL-202COV',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: new Party(bic: 'DEUTDEFF'),
            isCoverPayment: true
        );

        $this->assertSame(MtType::MT202COV, $doc->getMtType());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $beneficiaryInstitution = new Party(bic: 'DEUTDEFF');
        $orderingInstitution = new Party(bic: 'COBADEFF');
        $sendersCorrespondent = new Party(bic: 'INGBDEFF');
        $receiversCorrespondent = new Party(bic: 'SOLADEST');
        $intermediary = new Party(bic: 'BOFAUS3N');
        $accountWithInstitution = new Party(bic: 'CITIUS33');

        $doc = new Document(
            transactionReference: 'TXN-202',
            relatedReference: 'REL-202',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: $beneficiaryInstitution,
            orderingInstitution: $orderingInstitution,
            sendersCorrespondent: $sendersCorrespondent,
            receiversCorrespondent: $receiversCorrespondent,
            intermediary: $intermediary,
            accountWithInstitution: $accountWithInstitution,
            senderToReceiverInfo: 'FI transfer',
            timeIndication: '/CLSTIME/0800+0100',
            isCoverPayment: false
        );

        $this->assertSame($orderingInstitution, $doc->getOrderingInstitution());
        $this->assertSame($sendersCorrespondent, $doc->getSendersCorrespondent());
        $this->assertSame($receiversCorrespondent, $doc->getReceiversCorrespondent());
        $this->assertSame($intermediary, $doc->getIntermediary());
        $this->assertSame($accountWithInstitution, $doc->getAccountWithInstitution());
        $this->assertSame('FI transfer', $doc->getSenderToReceiverInfo());
        $this->assertSame('/CLSTIME/0800+0100', $doc->getTimeIndication());
    }

    #[Test]
    public function getValueDate(): void {
        $date = new DateTimeImmutable('2026-01-15');

        $doc = new Document(
            transactionReference: 'TXN-202',
            relatedReference: 'REL-202',
            valueDate: $date,
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertEquals($date, $doc->getValueDate());
    }

    #[Test]
    public function optionalFieldsAreNull(): void {
        $doc = new Document(
            transactionReference: 'TXN-202',
            relatedReference: 'REL-202',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertNull($doc->getOrderingInstitution());
        $this->assertNull($doc->getSendersCorrespondent());
        $this->assertNull($doc->getReceiversCorrespondent());
        $this->assertNull($doc->getIntermediary());
        $this->assertNull($doc->getAccountWithInstitution());
        $this->assertNull($doc->getSenderToReceiverInfo());
        $this->assertNull($doc->getTimeIndication());
    }

    #[Test]
    public function isCoverPaymentDefaultFalse(): void {
        $doc = new Document(
            transactionReference: 'TXN-202',
            relatedReference: 'REL-202',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 500000.00,
            beneficiaryInstitution: new Party(bic: 'DEUTDEFF')
        );

        $this->assertFalse($doc->isCoverPayment());
    }

    #[Test]
    public function differentCurrencies(): void {
        $usd = new Document(
            transactionReference: 'TXN-USD',
            relatedReference: 'REL-USD',
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::USDollar,
            amount: 750000.00,
            beneficiaryInstitution: new Party(bic: 'BOFAUS3N')
        );

        $this->assertSame(CurrencyCode::USDollar, $usd->getCurrency());
    }
}
