<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain008Test.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Pain;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Pain\Pain008DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\DirectDebitTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\Document;
use CommonToolkit\FinancialFormats\Enums\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\PainType;
use CommonToolkit\FinancialFormats\Enums\SequenceType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für pain.008 Document Builder (SEPA Direct Debit).
 */
class Pain008Test extends BaseTestCase {

    #[Test]
    public function testStaticFactoryCreateSepaDirectDebit(): void {
        $document = Pain008DocumentBuilder::createSepaDirectDebit(
            messageId: 'MSG-001',
            creditorName: 'Firma GmbH',
            creditorIban: 'DE89370400440532013000',
            creditorBic: 'COBADEFFXXX',
            creditorSchemeId: 'DE98ZZZ09999999999',
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789',
            amount: 100.50,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            reference: 'Rechnung 2025-001',
            sequenceType: SequenceType::ONE_OFF
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('MSG-001', $document->getGroupHeader()->getMessageId());
        $this->assertSame(PainType::PAIN_008, $document->getType());
        $this->assertCount(1, $document->getPaymentInstructions());
    }

    #[Test]
    public function testCreateWithBuilder(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 250.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789',
            remittanceInfo: 'Rechnung 2025-001'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-002')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999',
                sequenceType: SequenceType::FIRST
            )
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('MSG-002', $document->getGroupHeader()->getMessageId());
        $this->assertCount(1, $document->getPaymentInstructions());
    }

    #[Test]
    public function testCreateB2BInstruction(): void {
        $initiatingParty = new PartyIdentification(name: 'Business GmbH');
        $creditor = new PartyIdentification(name: 'Business GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 5000.00,
            mandateId: 'MNDT-B2B-001',
            mandateDate: new DateTimeImmutable('2024-02-01'),
            debtorName: 'Partner AG',
            debtorIban: 'DE91100000000123456789',
            remittanceInfo: 'B2B Zahlung'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-003')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaB2BInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999'
            )
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertSame(LocalInstrument::SEPA_B2B, $paymentInstruction->getLocalInstrument());
    }

    #[Test]
    public function testCreateWithSequenceTypes(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 200.00,
            mandateId: 'MNDT-FIRST-001',
            mandateDate: new DateTimeImmutable('2025-03-15'),
            debtorName: 'Neukunde GmbH',
            debtorIban: 'DE91100000000123456789'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-004')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999',
                sequenceType: SequenceType::FIRST
            )
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertSame(SequenceType::FIRST, $paymentInstruction->getSequenceType());
    }

    #[Test]
    public function testMultipleTransactions(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction1 = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 100.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-01'),
            debtorName: 'Kunde 1',
            debtorIban: 'DE11111111111111111111'
        );

        $transaction2 = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-002',
            amount: 200.00,
            mandateId: 'MNDT-002',
            mandateDate: new DateTimeImmutable('2024-02-01'),
            debtorName: 'Kunde 2',
            debtorIban: 'DE22222222222222222222'
        );

        $transaction3 = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-003',
            amount: 300.00,
            mandateId: 'MNDT-003',
            mandateDate: new DateTimeImmutable('2024-03-01'),
            debtorName: 'Kunde 3',
            debtorIban: 'DE33333333333333333333'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-005')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999'
            )
            ->addTransaction($transaction1)
            ->addTransaction($transaction2)
            ->addTransaction($transaction3)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertSame(3, $paymentInstruction->countTransactions());
        $this->assertSame(600.00, $paymentInstruction->calculateControlSum());
    }

    #[Test]
    public function testBuildWithoutMessageIdThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MessageId muss angegeben werden');

        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');

        (new Pain008DocumentBuilder())
            ->setInitiatingParty($initiatingParty)
            ->build();
    }

    #[Test]
    public function testBuildWithoutInitiatingPartyThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('InitiatingParty muss angegeben werden');

        (new Pain008DocumentBuilder())
            ->setMessageId('MSG-001')
            ->build();
    }

    #[Test]
    public function testBuildWithoutPaymentInstructionsThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Mindestens eine PaymentInstruction ist erforderlich');

        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');

        (new Pain008DocumentBuilder())
            ->setMessageId('MSG-001')
            ->setInitiatingParty($initiatingParty)
            ->build();
    }

    #[Test]
    public function testImmutableBuilder(): void {
        $builder1 = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-001');

        $builder2 = $builder1->setCreationDateTime(new DateTimeImmutable('2025-01-01'));

        $this->assertNotSame($builder1, $builder2);
    }

    #[Test]
    public function testSetRequestedCollectionDate(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');
        $collectionDate = new DateTimeImmutable('2025-06-15');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 150.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-006')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999'
            )
            ->setRequestedCollectionDate($collectionDate)
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertEquals($collectionDate->format('Y-m-d'), $paymentInstruction->getRequestedCollectionDate()->format('Y-m-d'));
    }

    #[Test]
    public function testSetChargesCode(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 100.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-007')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999'
            )
            ->setChargesCode(ChargesCode::SLEV)
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertSame(ChargesCode::SLEV, $paymentInstruction->getChargeBearer());
    }

    #[Test]
    public function testSetBatchBooking(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 100.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-008')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999'
            )
            ->setBatchBooking(true)
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertTrue($paymentInstruction->getBatchBooking());
    }

    #[Test]
    public function testAddTransactionWithoutInstructionThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Keine PaymentInstruction aktiv');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 100.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789'
        );

        (new Pain008DocumentBuilder())
            ->setMessageId('MSG-001')
            ->addTransaction($transaction);
    }

    #[Test]
    public function testWithCreditorAgent(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');
        $creditorAgent = new FinancialInstitution(bic: 'COBADEFFXXX');

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 100.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-009')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999',
                creditorAgent: $creditorAgent
            )
            ->addTransaction($transaction)
            ->endPaymentInstruction()
            ->build();

        $paymentInstruction = $document->getPaymentInstructions()[0];
        $this->assertNotNull($paymentInstruction->getCreditorAgent());
        $this->assertSame('COBADEFFXXX', $paymentInstruction->getCreditorAgent()->getBic());
    }

    #[Test]
    public function testMultiplePaymentInstructions(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $creditor = new PartyIdentification(name: 'Firma GmbH');
        $creditorAccount = new AccountIdentification(iban: 'DE89370400440532013000');

        $transaction1 = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-001',
            amount: 100.00,
            mandateId: 'MNDT-001',
            mandateDate: new DateTimeImmutable('2024-01-15'),
            debtorName: 'Kunde 1',
            debtorIban: 'DE11111111111111111111'
        );

        $transaction2 = DirectDebitTransaction::sepa(
            endToEndId: 'E2E-002',
            amount: 200.00,
            mandateId: 'MNDT-002',
            mandateDate: new DateTimeImmutable('2024-02-15'),
            debtorName: 'Kunde 2',
            debtorIban: 'DE22222222222222222222'
        );

        $document = (new Pain008DocumentBuilder())
            ->setMessageId('MSG-010')
            ->setInitiatingParty($initiatingParty)
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-001',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999',
                sequenceType: SequenceType::FIRST
            )
            ->addTransaction($transaction1)
            ->endPaymentInstruction()
            ->beginSepaCorInstruction(
                paymentInstructionId: 'PMT-002',
                creditor: $creditor,
                creditorAccount: $creditorAccount,
                creditorSchemeId: 'DE98ZZZ09999999999',
                sequenceType: SequenceType::RECURRING
            )
            ->addTransaction($transaction2)
            ->endPaymentInstruction()
            ->build();

        $this->assertCount(2, $document->getPaymentInstructions());
        $this->assertSame(SequenceType::FIRST, $document->getPaymentInstructions()[0]->getSequenceType());
        $this->assertSame(SequenceType::RECURRING, $document->getPaymentInstructions()[1]->getSequenceType());
        $this->assertSame(2, $document->getGroupHeader()->getNumberOfTransactions());
        $this->assertSame(300.00, $document->getGroupHeader()->getControlSum());
    }
}