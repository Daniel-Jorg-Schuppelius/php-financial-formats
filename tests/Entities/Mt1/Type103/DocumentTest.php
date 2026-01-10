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

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt1\Type103;

use CommonToolkit\FinancialFormats\Entities\Mt1\Type103\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Enums\Mt\BankOperationCode;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    private function createTransferDetails(): TransferDetails {
        return new TransferDetails(
            valueDate: new DateTimeImmutable('2026-01-15'),
            currency: CurrencyCode::Euro,
            amount: 5000.00
        );
    }

    #[Test]
    public function constructorWithMinimalParameters(): void {
        $orderingCustomer = new Party(name: 'Customer GmbH', account: 'DE89370400440532013000');
        $beneficiary = new Party(name: 'Beneficiary AG', account: 'DE12345678901234567890');
        $transferDetails = $this->createTransferDetails();

        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $transferDetails,
            orderingCustomer: $orderingCustomer,
            beneficiary: $beneficiary
        );

        $this->assertSame('REF-103', $doc->getSendersReference());
        $this->assertSame($orderingCustomer, $doc->getOrderingCustomer());
        $this->assertSame($beneficiary, $doc->getBeneficiary());
        $this->assertSame($transferDetails, $doc->getTransferDetails());
    }

    #[Test]
    public function getMtType(): void {
        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $this->createTransferDetails(),
            orderingCustomer: new Party(name: 'Customer'),
            beneficiary: new Party(name: 'Beneficiary')
        );

        $this->assertSame(MtType::MT103, $doc->getMtType());
    }

    #[Test]
    public function defaultBankOperationCode(): void {
        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $this->createTransferDetails(),
            orderingCustomer: new Party(name: 'Customer'),
            beneficiary: new Party(name: 'Beneficiary')
        );

        $this->assertSame(BankOperationCode::CRED, $doc->getBankOperationCode());
    }

    #[Test]
    public function customBankOperationCode(): void {
        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $this->createTransferDetails(),
            orderingCustomer: new Party(name: 'Customer'),
            beneficiary: new Party(name: 'Beneficiary'),
            bankOperationCode: BankOperationCode::SPRI
        );

        $this->assertSame(BankOperationCode::SPRI, $doc->getBankOperationCode());
    }

    #[Test]
    public function chargesCode(): void {
        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $this->createTransferDetails(),
            orderingCustomer: new Party(name: 'Customer'),
            beneficiary: new Party(name: 'Beneficiary'),
            chargesCode: ChargesCode::SHA
        );

        $this->assertSame(ChargesCode::SHA, $doc->getChargesCode());
    }

    #[Test]
    public function constructorWithAllOptionalParameters(): void {
        $orderingInstitution = new Party(bic: 'DEUTDEFF');
        $sendersCorrespondent = new Party(bic: 'COBADEFF');
        $intermediaryInstitution = new Party(bic: 'INGBDEFF');
        $accountWithInstitution = new Party(bic: 'SOLADEST');

        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $this->createTransferDetails(),
            orderingCustomer: new Party(name: 'Customer'),
            beneficiary: new Party(name: 'Beneficiary'),
            bankOperationCode: BankOperationCode::CRED,
            chargesCode: ChargesCode::OUR,
            remittanceInfo: 'Invoice 12345',
            orderingInstitution: $orderingInstitution,
            sendersCorrespondent: $sendersCorrespondent,
            intermediaryInstitution: $intermediaryInstitution,
            accountWithInstitution: $accountWithInstitution,
            senderToReceiverInfo: 'Sender info',
            regulatoryReporting: '/ORDERRES/DE/',
            transactionTypeCode: 'TTC001'
        );

        $this->assertSame($orderingInstitution, $doc->getOrderingInstitution());
        $this->assertSame($sendersCorrespondent, $doc->getSendersCorrespondent());
        $this->assertSame($intermediaryInstitution, $doc->getIntermediaryInstitution());
        $this->assertSame($accountWithInstitution, $doc->getAccountWithInstitution());
        $this->assertSame('Invoice 12345', $doc->getRemittanceInfo());
        $this->assertSame('Sender info', $doc->getSenderToReceiverInfo());
        $this->assertSame('/ORDERRES/DE/', $doc->getRegulatoryReporting());
        $this->assertSame('TTC001', $doc->getTransactionTypeCode());
    }

    #[Test]
    public function optionalFieldsAreNullByDefault(): void {
        $doc = new Document(
            sendersReference: 'REF-103',
            transferDetails: $this->createTransferDetails(),
            orderingCustomer: new Party(name: 'Customer'),
            beneficiary: new Party(name: 'Beneficiary')
        );

        $this->assertNull($doc->getOrderingInstitution());
        $this->assertNull($doc->getSendersCorrespondent());
        $this->assertNull($doc->getIntermediaryInstitution());
        $this->assertNull($doc->getAccountWithInstitution());
        $this->assertNull($doc->getSenderToReceiverInfo());
        $this->assertNull($doc->getRegulatoryReporting());
        $this->assertNull($doc->getTransactionTypeCode());
    }
}
