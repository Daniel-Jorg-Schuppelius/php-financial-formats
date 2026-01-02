<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt103DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Mt;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt103DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type103\Document;
use CommonToolkit\FinancialFormats\Enums\BankOperationCode;
use CommonToolkit\FinancialFormats\Enums\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für MT103 Document Builder (Single Customer Credit Transfer).
 */
class Mt103DocumentBuilderTest extends BaseTestCase {

    #[Test]
    public function testCreateSimpleDocument(): void {
        $document = Mt103DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('REF-001', $document->getSendersReference());
        $this->assertSame(MtType::MT103, $document->getMtType());
        $this->assertSame(BankOperationCode::CRED, $document->getBankOperationCode());
    }

    #[Test]
    public function testCreateWithChargesCodes(): void {
        $documentSHA = Mt103DocumentBuilder::create('REF-SHA')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->chargesShared()
            ->build();

        $documentOUR = Mt103DocumentBuilder::create('REF-OUR')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->chargesOur()
            ->build();

        $documentBEN = Mt103DocumentBuilder::create('REF-BEN')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->chargesBen()
            ->build();

        $this->assertSame(ChargesCode::SHA, $documentSHA->getChargesCode());
        $this->assertSame(ChargesCode::OUR, $documentOUR->getChargesCode());
        $this->assertSame(ChargesCode::BEN, $documentBEN->getChargesCode());
    }

    #[Test]
    public function testCreateWithAllFields(): void {
        $document = Mt103DocumentBuilder::create('REF-002')
            ->bankOperationCode(BankOperationCode::CRED)
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX', 'Musterstraße 123')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann', 'DEUTDEFF')
            ->amount(2500.00, CurrencyCode::Euro, new DateTimeImmutable('2025-04-01'))
            ->chargesShared()
            ->remittanceInfo('Rechnung 2025-001')
            ->orderingInstitution('COBADEFFXXX', 'Commerzbank AG')
            ->sendersCorrespondent('COBADEFFXXX')
            ->intermediaryInstitution('GENODEF1')
            ->accountWithInstitution('DEUTDEFF')
            ->senderToReceiverInfo('/ACC/URGENT')
            ->regulatoryReporting('/ORDERRES/DE/123456')
            ->transactionTypeCode('K90')
            ->build();

        $this->assertNotNull($document->getOrderingInstitution());
        $this->assertNotNull($document->getSendersCorrespondent());
        $this->assertNotNull($document->getIntermediaryInstitution());
        $this->assertNotNull($document->getAccountWithInstitution());
        $this->assertSame('/ACC/URGENT', $document->getSenderToReceiverInfo());
        $this->assertSame('/ORDERRES/DE/123456', $document->getRegulatoryReporting());
        $this->assertSame('K90', $document->getTransactionTypeCode());
    }

    #[Test]
    public function testCreateWithCurrencyConversion(): void {
        $document = Mt103DocumentBuilder::create('REF-003')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('US12345678901234567890', 'US Company Inc')
            ->amountWithConversion(
                amount: 850.00,
                currency: CurrencyCode::USDollar,
                valueDate: new DateTimeImmutable('2025-05-01'),
                originalAmount: 1000.00,
                originalCurrency: CurrencyCode::Euro,
                exchangeRate: 0.85
            )
            ->build();

        $transferDetails = $document->getTransferDetails();
        $this->assertTrue($transferDetails->hasCurrencyConversion());
        $this->assertSame(CurrencyCode::USDollar, $transferDetails->getCurrency());
        $this->assertSame(850.00, $transferDetails->getAmount());
        $this->assertSame(CurrencyCode::Euro, $transferDetails->getOriginalCurrency());
        $this->assertSame(1000.00, $transferDetails->getOriginalAmount());
        $this->assertSame(0.85, $transferDetails->getExchangeRate());
    }

    #[Test]
    public function testStaticFactoryCreateSimple(): void {
        $document = Mt103DocumentBuilder::createSimple(
            sendersReference: 'SIMPLE-001',
            orderingAccount: 'DE89370400440532013000',
            orderingName: 'Firma GmbH',
            beneficiaryAccount: 'DE91100000000123456789',
            beneficiaryName: 'Max Mustermann',
            amount: 500.00,
            valueDate: new DateTimeImmutable('2025-06-01'),
            remittanceInfo: 'Einfache Zahlung'
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('SIMPLE-001', $document->getSendersReference());
        $this->assertSame(500.00, $document->getTransferDetails()->getAmount());
        $this->assertSame(CurrencyCode::Euro, $document->getTransferDetails()->getCurrency());
        $this->assertSame(ChargesCode::SHA, $document->getChargesCode());
        $this->assertSame('Einfache Zahlung', $document->getRemittanceInfo());
    }

    #[Test]
    public function testStaticFactoryCreateInternational(): void {
        $orderingCustomer = new Party(
            account: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            name: 'Firma GmbH'
        );

        $beneficiary = new Party(
            account: 'GB82WEST12345698765432',
            bic: 'WESTGB2L',
            name: 'UK Company Ltd'
        );

        $intermediary = new Party(
            bic: 'CHASUS33'
        );

        $document = Mt103DocumentBuilder::createInternational(
            sendersReference: 'INTL-001',
            orderingCustomer: $orderingCustomer,
            beneficiary: $beneficiary,
            amount: 10000.00,
            currency: CurrencyCode::BritishPound,
            valueDate: new DateTimeImmutable('2025-07-01'),
            charges: ChargesCode::OUR,
            intermediaryInstitution: $intermediary,
            remittanceInfo: 'International Payment'
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame(ChargesCode::OUR, $document->getChargesCode());
        $this->assertSame(CurrencyCode::BritishPound, $document->getTransferDetails()->getCurrency());
        $this->assertNotNull($document->getIntermediaryInstitution());
    }

    #[Test]
    public function testWithPartyObjects(): void {
        $orderingCustomer = new Party(
            account: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            name: 'Firma GmbH',
            addressLine1: 'Musterstraße 123',
            addressLine2: '12345 Musterstadt',
            addressLine3: 'Germany'
        );

        $beneficiary = new Party(
            account: 'DE91100000000123456789',
            bic: 'DEUTDEFF',
            name: 'Max Mustermann'
        );

        $document = Mt103DocumentBuilder::create('REF-004')
            ->orderingCustomerParty($orderingCustomer)
            ->beneficiaryParty($beneficiary)
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-08-01'))
            ->build();

        $this->assertSame('Firma GmbH', $document->getOrderingCustomer()->getName());
        $this->assertCount(3, $document->getOrderingCustomer()->getAddressLines());
    }

    #[Test]
    public function testWithTransferDetails(): void {
        $transferDetails = new TransferDetails(
            valueDate: new DateTimeImmutable('2025-09-01'),
            currency: CurrencyCode::SwissFranc,
            amount: 5000.00
        );

        $document = Mt103DocumentBuilder::create('REF-005')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('CH9300762011623852957', 'Swiss Company AG')
            ->transferDetails($transferDetails)
            ->build();

        $this->assertSame(CurrencyCode::SwissFranc, $document->getTransferDetails()->getCurrency());
        $this->assertSame(5000.00, $document->getTransferDetails()->getAmount());
    }

    #[Test]
    public function testIsStpCapable(): void {
        $document = Mt103DocumentBuilder::create('REF-STP')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-10-01'))
            ->build();

        $this->assertTrue($document->isStpCapable());
    }

    #[Test]
    public function testSendersReferenceMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender\'s Reference darf maximal 16 Zeichen lang sein');

        Mt103DocumentBuilder::create(str_repeat('X', 17));
    }

    #[Test]
    public function testTransactionTypeCodeLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction Type Code muss genau 3 Zeichen haben');

        Mt103DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->transactionTypeCode('AB');
    }

    #[Test]
    public function testBuildWithoutTransferDetailsThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TransferDetails (Betrag/Währung/Datum) erforderlich');

        Mt103DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->build();
    }

    #[Test]
    public function testBuildWithoutOrderingCustomerThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ordering Customer (Auftraggeber) ist erforderlich');

        Mt103DocumentBuilder::create('REF-001')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->build();
    }

    #[Test]
    public function testBuildWithoutBeneficiaryThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beneficiary (Begünstigter) ist erforderlich');

        Mt103DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->build();
    }

    #[Test]
    public function testImmutableBuilder(): void {
        $builder1 = Mt103DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH');

        $builder2 = $builder1->chargesShared();

        $this->assertNotSame($builder1, $builder2);
    }

    #[Test]
    public function testWithCreationDateTime(): void {
        $creationDate = new DateTimeImmutable('2025-06-15 10:30:00');

        $document = Mt103DocumentBuilder::create('REF-006')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-06-15'))
            ->withCreationDateTime($creationDate)
            ->build();

        $this->assertEquals($creationDate, $document->getCreationDateTime());
    }
}
