<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt101DocumentBuilderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Mt;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt101DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Transaction;
use CommonToolkit\FinancialFormats\Enums\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für MT101 Document Builder (Request for Transfer).
 */
class Mt101DocumentBuilderTest extends BaseTestCase {

    #[Test]
    public function testCreateSimpleDocument(): void {
        $document = Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('REF-001', $document->getSendersReference());
        $this->assertSame(MtType::MT101, $document->getMtType());
        $this->assertCount(1, $document->getTransactions());
    }

    #[Test]
    public function testCreateWithMultipleTransactions(): void {
        $document = Mt101DocumentBuilder::create('REF-002')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-20'))
            ->beginTransaction('TXN-001')
            ->amount(100.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-20'))
            ->beneficiary('DE11111111111111111111', 'Empfänger 1')
            ->done()
            ->beginTransaction('TXN-002')
            ->amount(200.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-20'))
            ->beneficiary('DE22222222222222222222', 'Empfänger 2')
            ->done()
            ->beginTransaction('TXN-003')
            ->amount(300.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-20'))
            ->beneficiary('DE33333333333333333333', 'Empfänger 3')
            ->done()
            ->build();

        $this->assertCount(3, $document->getTransactions());
        $this->assertSame(600.00, $document->getTotalAmount());
    }

    #[Test]
    public function testCreateWithAllFields(): void {
        $document = Mt101DocumentBuilder::create('REF-003')
            ->customerReference('CUST-REF-001')
            ->messageIndex(1, 2)
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX', 'Musterstraße 123')
            ->orderingInstitution('COBADEFFXXX', 'Commerzbank AG')
            ->requestedExecutionDate(new DateTimeImmutable('2025-04-01'))
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2025-04-01'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann', 'DEUTDEFF')
            ->accountWithInstitution('DEUTDEFF')
            ->remittanceInfo('Rechnung 2025-001')
            ->chargesCode(ChargesCode::SHA)
            ->done()
            ->build();

        $this->assertSame('CUST-REF-001', $document->getCustomerReference());
        $this->assertSame('1/2', $document->getMessageIndex());
        $this->assertNotNull($document->getOrderingInstitution());
        $transaction = $document->getTransactions()[0];
        $this->assertSame('Rechnung 2025-001', $transaction->getRemittanceInfo());
        $this->assertSame(ChargesCode::SHA, $transaction->getChargesCode());
    }

    #[Test]
    public function testStaticFactoryCreateBatch(): void {
        $payments = [
            [
                'reference' => 'TXN-001',
                'amount' => 100.00,
                'currency' => CurrencyCode::Euro,
                'beneficiaryAccount' => 'DE11111111111111111111',
                'beneficiaryName' => 'Empfänger 1',
            ],
            [
                'reference' => 'TXN-002',
                'amount' => 200.00,
                'currency' => CurrencyCode::Euro,
                'beneficiaryAccount' => 'DE22222222222222222222',
                'beneficiaryName' => 'Empfänger 2',
                'remittanceInfo' => 'Zahlung 2',
            ],
        ];

        $document = Mt101DocumentBuilder::createBatch(
            sendersReference: 'BATCH-001',
            orderingAccount: 'DE89370400440532013000',
            orderingName: 'Firma GmbH',
            executionDate: new DateTimeImmutable('2025-05-01'),
            payments: $payments
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertCount(2, $document->getTransactions());
        $this->assertSame(300.00, $document->getTotalAmount());
    }

    #[Test]
    public function testWithPartyObjects(): void {
        $orderingCustomer = new Party(
            account: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
            name: 'Firma GmbH',
            addressLine1: 'Musterstraße 123',
            addressLine2: '12345 Musterstadt'
        );

        $beneficiary = new Party(
            account: 'DE91100000000123456789',
            bic: 'DEUTDEFF',
            name: 'Max Mustermann'
        );

        $document = Mt101DocumentBuilder::create('REF-004')
            ->orderingCustomerParty($orderingCustomer)
            ->requestedExecutionDate(new DateTimeImmutable('2025-06-01'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-06-01'))
            ->beneficiaryParty($beneficiary)
            ->done()
            ->build();

        $this->assertSame('Firma GmbH', $document->getOrderingCustomer()->getName());
        $this->assertSame('Musterstraße 123', $document->getOrderingCustomer()->getAddressLine1());
    }

    #[Test]
    public function testWithTransferDetails(): void {
        $transferDetails = new TransferDetails(
            valueDate: new DateTimeImmutable('2025-07-01'),
            currency: CurrencyCode::USDollar,
            amount: 1500.00
        );

        $document = Mt101DocumentBuilder::create('REF-005')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-07-01'))
            ->beginTransaction('TXN-001')
            ->transferDetails($transferDetails)
            ->beneficiary('US12345678901234567890', 'US Company Inc')
            ->done()
            ->build();

        $transaction = $document->getTransactions()[0];
        $this->assertSame(CurrencyCode::USDollar, $transaction->getTransferDetails()->getCurrency());
        $this->assertSame(1500.00, $transaction->getAmount());
    }

    #[Test]
    public function testGetCurrencies(): void {
        $document = Mt101DocumentBuilder::create('REF-006')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-08-01'))
            ->beginTransaction('TXN-001')
            ->amount(100.00, CurrencyCode::Euro, new DateTimeImmutable('2025-08-01'))
            ->beneficiary('DE11111111111111111111', 'Empfänger 1')
            ->done()
            ->beginTransaction('TXN-002')
            ->amount(200.00, CurrencyCode::USDollar, new DateTimeImmutable('2025-08-01'))
            ->beneficiary('US12345678901234567890', 'US Company')
            ->done()
            ->build();

        $currencies = $document->getCurrencies();
        $this->assertCount(2, $currencies);
        $this->assertContains(CurrencyCode::Euro, $currencies);
        $this->assertContains(CurrencyCode::USDollar, $currencies);
    }

    #[Test]
    public function testSendersReferenceMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender\'s Reference darf maximal 16 Zeichen lang sein');

        Mt101DocumentBuilder::create(str_repeat('X', 17));
    }

    #[Test]
    public function testTransactionReferenceMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction Reference darf maximal 16 Zeichen lang sein');

        Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction(str_repeat('X', 17));
    }

    #[Test]
    public function testBuildWithoutOrderingCustomerThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ordering Customer (Auftraggeber) ist erforderlich');

        Mt101DocumentBuilder::create('REF-001')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    #[Test]
    public function testBuildWithoutExecutionDateThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requested Execution Date ist erforderlich');

        Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    #[Test]
    public function testBuildWithoutTransactionsThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mindestens eine Transaktion erforderlich');

        Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->build();
    }

    #[Test]
    public function testTransactionWithoutTransferDetailsThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TransferDetails (Betrag/Währung/Datum) erforderlich');

        Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done();
    }

    #[Test]
    public function testTransactionWithoutBeneficiaryThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Beneficiary (Begünstigter) erforderlich');

        Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->done();
    }

    #[Test]
    public function testImmutableBuilder(): void {
        $builder1 = Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH');

        $builder2 = $builder1->customerReference('CUST-REF');

        $this->assertNotSame($builder1, $builder2);
    }

    #[Test]
    public function testWithCreationDateTime(): void {
        $creationDate = new DateTimeImmutable('2025-06-15T12:00:00+00:00');

        $document = Mt101DocumentBuilder::create('REF-010')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-06-15'))
            ->withCreationDateTime($creationDate)
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-06-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertSame(
            $creationDate->format('Y-m-d'),
            $document->getCreationDateTime()->format('Y-m-d')
        );
    }

    #[Test]
    public function testWithMessageIndex(): void {
        $document = Mt101DocumentBuilder::create('REF-011')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->messageIndex(2, 5)
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertSame('2/5', $document->getMessageIndex());
    }

    #[Test]
    public function testWithOrderingInstitution(): void {
        $document = Mt101DocumentBuilder::create('REF-012')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->orderingInstitution('COBADEFFXXX', 'Commerzbank')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $this->assertNotNull($document->getOrderingInstitution());
        $this->assertSame('COBADEFFXXX', $document->getOrderingInstitution()->getBic());
    }

    #[Test]
    public function testWithAccountWithInstitution(): void {
        $document = Mt101DocumentBuilder::create('REF-013')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->accountWithInstitution('DEUTDEFF')
            ->done()
            ->build();

        $transaction = $document->getTransactions()[0];
        $this->assertNotNull($transaction->getAccountWithInstitution());
    }

    #[Test]
    public function testWithChargesCode(): void {
        $document = Mt101DocumentBuilder::create('REF-014')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->chargesCode(ChargesCode::OUR)
            ->done()
            ->build();

        $transaction = $document->getTransactions()[0];
        $this->assertSame(ChargesCode::OUR, $transaction->getChargesCode());
    }
}
