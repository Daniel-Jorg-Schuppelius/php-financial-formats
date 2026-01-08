<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain001GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\CreditTransferTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\PaymentInstruction;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain001Generator;
use CommonToolkit\FinancialFormats\Enums\Pain\PaymentMethod;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Pain001GeneratorTest extends BaseTestCase {
    private Pain001Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Pain001Generator();
    }

    private function createGroupHeader(): GroupHeader {
        return new GroupHeader(
            messageId: 'MSG-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            numberOfTransactions: 1,
            initiatingParty: PartyIdentification::fromName('Test GmbH')
        );
    }

    private function createPaymentInstruction(): PaymentInstruction {
        $debtor = PartyIdentification::fromName('Auftraggeber GmbH');
        $debtorAccount = AccountIdentification::fromIban('DE89370400440532013000');
        $debtorAgent = FinancialInstitution::fromBic('COBADEFFXXX');

        $creditor = PartyIdentification::fromName('Empfänger AG');
        $creditorAccount = AccountIdentification::fromIban('DE75512108001245126199');
        $creditorAgent = FinancialInstitution::fromBic('SOLADEST600');

        $paymentId = new PaymentIdentification(endToEndId: 'E2E-001');
        $transaction = new CreditTransferTransaction(
            paymentId: $paymentId,
            amount: 1500.00,
            currency: CurrencyCode::Euro,
            creditor: $creditor,
            creditorAccount: $creditorAccount,
            creditorAgent: $creditorAgent
        );

        return new PaymentInstruction(
            paymentInstructionId: 'PMT-001',
            paymentMethod: PaymentMethod::TRANSFER,
            requestedExecutionDate: new DateTimeImmutable('2025-01-16'),
            debtor: $debtor,
            debtorAccount: $debtorAccount,
            debtorAgent: $debtorAgent,
            transactions: [$transaction]
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
        $this->assertStringContainsString('CstmrCdtTrfInitn', $xml);
    }

    public function testGenerateContainsPainNamespace(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('pain.001', $xml);
    }

    public function testGenerateContainsGroupHeader(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<GrpHdr>', $xml);
        $this->assertStringContainsString('MSG-001', $xml);
        $this->assertStringContainsString('<NbOfTxs>1</NbOfTxs>', $xml);
    }

    public function testGenerateContainsPaymentInstruction(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<PmtInf>', $xml);
        $this->assertStringContainsString('PMT-001', $xml);
    }

    public function testGenerateContainsDebtorInfo(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('DE89370400440532013000', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
    }

    public function testGenerateContainsCreditorInfo(): void {
        $document = new Document(
            groupHeader: $this->createGroupHeader(),
            paymentInstructions: [$this->createPaymentInstruction()]
        );

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('DE75512108001245126199', $xml);
        $this->assertStringContainsString('SOLADEST600', $xml);
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
