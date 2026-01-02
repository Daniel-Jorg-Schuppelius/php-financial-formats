<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain008GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\DirectDebitTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\MandateInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\PaymentInstruction;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain008Generator;
use CommonToolkit\FinancialFormats\Enums\PaymentMethod;
use CommonToolkit\FinancialFormats\Enums\SequenceType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Pain008GeneratorTest extends BaseTestCase {
    private Pain008Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Pain008Generator();
    }

    private function createGroupHeader(): GroupHeader {
        return GroupHeader::create(
            messageId: 'DD-MSG-001',
            initiatingParty: PartyIdentification::fromName('Creditor GmbH'),
            numberOfTransactions: 1,
            controlSum: 99.99
        );
    }

    private function createPaymentInstruction(): PaymentInstruction {
        $creditor = PartyIdentification::fromName('Creditor GmbH');
        $creditorAccount = AccountIdentification::fromIban('DE89370400440532013000');
        $creditorAgent = FinancialInstitution::fromBic('COBADEFFXXX');

        $debtor = PartyIdentification::fromName('Debtor AG');
        $debtorAccount = AccountIdentification::fromIban('DE75512108001245126199');
        $debtorAgent = FinancialInstitution::fromBic('SOLADEST600');

        $mandateInfo = MandateInformation::create(
            mandateId: 'MANDATE-001',
            dateOfSignature: new DateTimeImmutable('2024-01-01')
        );

        $paymentId = new PaymentIdentification(endToEndId: 'E2E-DD-001');
        $transaction = new DirectDebitTransaction(
            paymentId: $paymentId,
            amount: 99.99,
            currency: CurrencyCode::Euro,
            mandateInfo: $mandateInfo,
            debtor: $debtor,
            debtorAccount: $debtorAccount,
            debtorAgent: $debtorAgent
        );

        return new PaymentInstruction(
            paymentInstructionId: 'PMT-DD-001',
            paymentMethod: PaymentMethod::DIRECT_DEBIT,
            requestedCollectionDate: new DateTimeImmutable('2025-01-20'),
            creditor: $creditor,
            creditorAccount: $creditorAccount,
            creditorAgent: $creditorAgent,
            transactions: [$transaction],
            sequenceType: SequenceType::RECURRING
        );
    }

    public function testGenerateBasicDocument(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('CstmrDrctDbtInitn', $xml);
    }

    public function testGenerateContainsPainNamespace(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('pain.008', $xml);
    }

    public function testGenerateContainsGroupHeader(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<GrpHdr>', $xml);
        $this->assertStringContainsString('DD-MSG-001', $xml);
    }

    public function testGenerateContainsPaymentInstruction(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<PmtInf>', $xml);
        $this->assertStringContainsString('PMT-DD-001', $xml);
    }

    public function testGenerateContainsCreditorInfo(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('DE89370400440532013000', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
    }

    public function testGenerateContainsMandateInfo(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('MANDATE-001', $xml);
    }

    public function testGenerateIsValidXml(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $dom = new \DOMDocument();
        $result = @$dom->loadXML($xml);

        $this->assertTrue($result, 'Generated XML should be valid');
    }
}
