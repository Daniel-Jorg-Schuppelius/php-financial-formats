<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainMandateFormatsTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Pain;

use Tests\Contracts\BaseTestCase;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain007Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain009Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain010Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain011Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain012Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain013Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain014Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain017Generator;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain018Generator;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\StatusReason;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\TransactionStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\Document as Pain007Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\GroupHeader as Pain007GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\OriginalGroupInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\ReversalReason;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\TransactionInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type9\Document as Pain009Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\AmendmentDetails;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\Document as Pain010Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\MandateAmendment;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\CancellationReason;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\Document as Pain011Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\MandateCancellation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12\Document as Pain012Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12\MandateAcceptance;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13\Document as Pain013Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13\PaymentActivationRequest;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14\Document as Pain014Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14\PaymentActivationStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17\Document as Pain017Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17\MandateCopyRequest;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18\Document as Pain018Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18\MandateSuspensionRequest;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\MandateStatus;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\SequenceType;
use CommonToolkit\FinancialFormats\Parsers\ISO20022\PainParser;
use DateTimeImmutable;

/**
 * Tests für alle pain-Mandatsformate (007, 009-018).
 */
class PainMandateFormatsTest extends BaseTestCase {

    // ==================== pain.007 Tests ====================

    public function testPain007ReversalReasonCreation(): void {
        $reason = ReversalReason::customerRequest('Kundenanfrage');

        $this->assertEquals('CUST', $reason->getCodeString());
        $this->assertNull($reason->getProprietary());
        $this->assertContains('Kundenanfrage', $reason->getAdditionalInfo());
    }

    public function testPain007TransactionInformationCreation(): void {
        $reason = ReversalReason::customerRequest();
        $tx = TransactionInformation::create('E2E-123', 100.00, $reason);

        $this->assertEquals('E2E-123', $tx->getOriginalEndToEndId());
        $this->assertEquals(100.00, $tx->getReversedAmount());
    }

    public function testPain007DocumentCreation(): void {
        $groupHeader = Pain007GroupHeader::create('MSG-007-001', new PartyIdentification(name: 'Test Company'));
        $orgGroupInfo = new OriginalGroupInformation('ORIG-MSG-001', 'pain.008.001.11');
        $orgPmtInfo = OriginalPaymentInformation::reverseAll('ORIG-PMTINF-001', ReversalReason::customerRequest());

        $document = new Pain007Document($groupHeader, $orgGroupInfo, [$orgPmtInfo]);

        $this->assertEquals(PainType::PAIN_007, $document->getType());
        $this->assertCount(1, $document->getOriginalPaymentInformations());
    }

    public function testPain007GeneratorOutput(): void {
        $groupHeader = Pain007GroupHeader::create('MSG-007-001', new PartyIdentification(name: 'Test Company'));
        $orgGroupInfo = new OriginalGroupInformation('ORIG-MSG-001', 'pain.008.001.11');
        $reason = ReversalReason::customerRequest();
        $tx = TransactionInformation::create('E2E-123', 150.00, $reason);
        $orgPmtInfo = OriginalPaymentInformation::create('ORIG-PMTINF-001', [$tx]);

        $document = new Pain007Document($groupHeader, $orgGroupInfo, [$orgPmtInfo]);

        $generator = new Pain007Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('CstmrPmtRvsl', $xml);
        $this->assertStringContainsString('MSG-007-001', $xml);
        $this->assertStringContainsString('pain.007.001.12', $xml);
    }

    public function testPain007ParserIsValid(): void {
        $xml = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.007.001.12"><CstmrPmtRvsl><GrpHdr><MsgId>TEST</MsgId><CreDtTm>2025-01-01T12:00:00</CreDtTm><NbOfTxs>1</NbOfTxs><InitgPty><Nm>Test</Nm></InitgPty></GrpHdr><OrgnlGrpInf><OrgnlMsgId>ORIG</OrgnlMsgId><OrgnlMsgNmId>pain.008.001.11</OrgnlMsgNmId></OrgnlGrpInf></CstmrPmtRvsl></Document>';

        $this->assertTrue(PainParser::isValid($xml, PainType::PAIN_007));
    }

    // ==================== pain.009 Tests ====================

    public function testMandateCreation(): void {
        $mandate = Mandate::sepaCore(
            'MNDT-001',
            new DateTimeImmutable('2025-01-01'),
            'Creditor GmbH',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'Debtor AG',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $this->assertEquals('MNDT-001', $mandate->getMandateId());
        $this->assertTrue($mandate->isCore());
        $this->assertFalse($mandate->isB2B());
    }

    public function testMandateB2BCreation(): void {
        $mandate = Mandate::sepaB2B(
            'MNDT-B2B-001',
            new DateTimeImmutable('2025-01-01'),
            'Business Creditor',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'Business Debtor',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $this->assertEquals(LocalInstrument::SEPA_B2B, $mandate->getLocalInstrument());
        $this->assertTrue($mandate->isB2B());
    }

    public function testPain009DocumentCreation(): void {
        $mandate = Mandate::sepaCore(
            'MNDT-001',
            new DateTimeImmutable('2025-01-01'),
            'Creditor GmbH',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'Debtor AG',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $document = Pain009Document::create(
            'MSG-009-001',
            new PartyIdentification(name: 'Initiator'),
            [$mandate]
        );

        $this->assertEquals(PainType::PAIN_009, $document->getType());
        $this->assertEquals(1, $document->countMandates());
    }

    public function testPain009GeneratorOutput(): void {
        $mandate = Mandate::sepaCore(
            'MNDT-001',
            new DateTimeImmutable('2025-01-01'),
            'Creditor GmbH',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'Debtor AG',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $document = Pain009Document::create(
            'MSG-009-001',
            new PartyIdentification(name: 'Initiator'),
            [$mandate]
        );

        $generator = new Pain009Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('MndtInitnReq', $xml);
        $this->assertStringContainsString('MNDT-001', $xml);
        $this->assertStringContainsString('pain.009.001.08', $xml);
    }

    public function testPain009ParserIsValid(): void {
        $xml = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.009.001.08"><MndtInitnReq><GrpHdr><MsgId>TEST</MsgId><CreDtTm>2025-01-01T12:00:00</CreDtTm><InitgPty><Nm>Test</Nm></InitgPty></GrpHdr></MndtInitnReq></Document>';

        $this->assertTrue(PainParser::isValid($xml, PainType::PAIN_009));
    }

    // ==================== pain.010 Tests ====================

    public function testPain010AmendmentDetailsCreation(): void {
        $details = AmendmentDetails::mandateIdChange('OLD-MNDT-001');

        $this->assertEquals('OLD-MNDT-001', $details->getOriginalMandateId());
    }

    public function testPain010DebtorAccountChange(): void {
        $originalAccount = new AccountIdentification(iban: 'DE75512108001245126199');
        $details = AmendmentDetails::debtorAccountChange($originalAccount);

        $this->assertEquals('DE75512108001245126199', $details->getOriginalDebtorAccount()->getIban());
    }

    public function testPain010DocumentCreation(): void {
        $mandate = Mandate::sepaCore(
            'MNDT-002',
            new DateTimeImmutable('2025-01-01'),
            'Creditor GmbH',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'New Debtor Name',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $amendment = MandateAmendment::create(
            $mandate,
            AmendmentDetails::mandateIdChange('OLD-MNDT-001')
        );

        $document = Pain010Document::create(
            'MSG-010-001',
            new PartyIdentification(name: 'Initiator'),
            [$amendment]
        );

        $this->assertEquals(PainType::PAIN_010, $document->getType());
        $this->assertEquals(1, $document->countAmendments());
    }

    public function testPain010GeneratorOutput(): void {
        $mandate = Mandate::sepaCore(
            'MNDT-002',
            new DateTimeImmutable('2025-01-01'),
            'Creditor GmbH',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'Debtor AG',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $amendment = MandateAmendment::create(
            $mandate,
            AmendmentDetails::mandateIdChange('OLD-MNDT-001')
        );

        $document = Pain010Document::create(
            'MSG-010-001',
            new PartyIdentification(name: 'Initiator'),
            [$amendment]
        );

        $generator = new Pain010Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('MndtAmdmntReq', $xml);
        $this->assertStringContainsString('OLD-MNDT-001', $xml);
        $this->assertStringContainsString('pain.010.001.08', $xml);
    }

    // ==================== pain.011 Tests ====================

    public function testPain011CancellationReasonCreation(): void {
        $reason = CancellationReason::customerRequest();
        $this->assertEquals('CUST', $reason->getCodeString());

        $reason = CancellationReason::accountClosed();
        $this->assertEquals('AC01', $reason->getCodeString());

        $reason = CancellationReason::fraudulent();
        $this->assertEquals('FRAD', $reason->getCodeString());
    }

    public function testPain011DocumentCreation(): void {
        $cancellation = MandateCancellation::create('MNDT-001', CancellationReason::customerRequest());

        $document = Pain011Document::create(
            'MSG-011-001',
            new PartyIdentification(name: 'Initiator'),
            [$cancellation]
        );

        $this->assertEquals(PainType::PAIN_011, $document->getType());
        $this->assertEquals(1, $document->countCancellations());
    }

    public function testPain011GeneratorOutput(): void {
        $cancellation = MandateCancellation::create('MNDT-001', CancellationReason::customerRequest());

        $document = Pain011Document::create(
            'MSG-011-001',
            new PartyIdentification(name: 'Initiator'),
            [$cancellation]
        );

        $generator = new Pain011Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('MndtCxlReq', $xml);
        $this->assertStringContainsString('CUST', $xml);
        $this->assertStringContainsString('pain.011.001.08', $xml);
    }

    // ==================== pain.012 Tests ====================

    public function testPain012AcceptanceCreation(): void {
        $acceptance = MandateAcceptance::accepted('MNDT-001');

        $this->assertTrue($acceptance->isAccepted());
        $this->assertFalse($acceptance->isRejected());
        $this->assertEquals(MandateStatus::ACCEPTED, $acceptance->getStatus());
    }

    public function testPain012RejectionCreation(): void {
        $rejection = MandateAcceptance::rejected('MNDT-001', 'Ungültige Kontodaten');

        $this->assertFalse($rejection->isAccepted());
        $this->assertTrue($rejection->isRejected());
        $this->assertEquals('Ungültige Kontodaten', $rejection->getRejectReason());
    }

    public function testPain012DocumentCreation(): void {
        $acceptance = MandateAcceptance::accepted('MNDT-001');

        $document = Pain012Document::forPain009('MSG-012-001', 'ORIG-MSG-009-001', [$acceptance]);

        $this->assertEquals(PainType::PAIN_012, $document->getType());
        $this->assertEquals('pain.009.001.08', $document->getOriginalMessageNameId());
        $this->assertTrue($document->isFullyAccepted());
    }

    public function testPain012GeneratorOutput(): void {
        $acceptance = MandateAcceptance::accepted('MNDT-001');

        $document = Pain012Document::forPain009('MSG-012-001', 'ORIG-MSG-009-001', [$acceptance]);

        $generator = new Pain012Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('MndtAccptncRpt', $xml);
        $this->assertStringContainsString('pain.012.001.08', $xml);
    }

    // ==================== pain.013 Tests ====================

    public function testPain013PaymentRequestCreation(): void {
        $request = PaymentActivationRequest::create(
            'E2E-013-001',
            250.00,
            'Debtor Name',
            'DE75512108001245126199',
            'INGDDEFFXXX',
            'Creditor Name',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'Rechnung 12345'
        );

        $this->assertEquals('E2E-013-001', $request->getEndToEndId());
        $this->assertEquals(250.00, $request->getAmount());
        $this->assertEquals('Rechnung 12345', $request->getRemittanceInformation());
    }

    public function testPain013DocumentCreation(): void {
        $request = PaymentActivationRequest::create(
            'E2E-013-001',
            250.00,
            'Debtor Name',
            'DE75512108001245126199',
            'INGDDEFFXXX',
            'Creditor Name',
            'DE89370400440532013000',
            'COBADEFFXXX'
        );

        $document = Pain013Document::create(
            'MSG-013-001',
            new PartyIdentification(name: 'Creditor Company'),
            [$request]
        );

        $this->assertEquals(PainType::PAIN_013, $document->getType());
        $this->assertEquals(1, $document->countRequests());
        $this->assertEquals(250.00, $document->getControlSum());
    }

    public function testPain013GeneratorOutput(): void {
        $request = PaymentActivationRequest::create(
            'E2E-013-001',
            250.00,
            'Debtor Name',
            'DE75512108001245126199',
            'INGDDEFFXXX',
            'Creditor Name',
            'DE89370400440532013000',
            'COBADEFFXXX'
        );

        $document = Pain013Document::create(
            'MSG-013-001',
            new PartyIdentification(name: 'Creditor Company'),
            [$request]
        );

        $generator = new Pain013Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('CdtrPmtActvtnReq', $xml);
        $this->assertStringContainsString('pain.013.001.11', $xml);
    }

    // ==================== pain.014 Tests ====================

    public function testPain014StatusCreation(): void {
        $status = PaymentActivationStatus::accepted('INST-001', 'E2E-001');
        $this->assertTrue($status->isAccepted());

        $status = PaymentActivationStatus::pending('INST-001', 'E2E-001');
        $this->assertTrue($status->isPending());
    }

    public function testPain014DocumentCreation(): void {
        $status = PaymentActivationStatus::accepted('INST-001', 'E2E-001');

        $document = Pain014Document::create(
            'MSG-014-001',
            'ORIG-MSG-013-001',
            [$status]
        );

        $this->assertEquals(PainType::PAIN_014, $document->getType());
        $this->assertTrue($document->isFullyAccepted());
    }

    public function testPain014GeneratorOutput(): void {
        $status = PaymentActivationStatus::accepted('INST-001', 'E2E-001');

        $document = Pain014Document::create(
            'MSG-014-001',
            'ORIG-MSG-013-001',
            [$status]
        );

        $generator = new Pain014Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('CdtrPmtActvtnReqStsRpt', $xml);
        $this->assertStringContainsString('pain.014.001.11', $xml);
    }

    // ==================== pain.017 Tests ====================

    public function testPain017CopyRequestCreation(): void {
        $request = MandateCopyRequest::create('MNDT-001', 'DE98ZZZ09999999999');

        $this->assertEquals('MNDT-001', $request->getMandateId());
        $this->assertEquals('DE98ZZZ09999999999', $request->getCreditorSchemeId());
    }

    public function testPain017DocumentCreation(): void {
        $request = MandateCopyRequest::create('MNDT-001');

        $document = Pain017Document::create(
            'MSG-017-001',
            new PartyIdentification(name: 'Requester'),
            [$request]
        );

        $this->assertEquals(PainType::PAIN_017, $document->getType());
        $this->assertEquals(1, $document->countRequests());
    }

    public function testPain017GeneratorOutput(): void {
        $request = MandateCopyRequest::create('MNDT-001');

        $document = Pain017Document::create(
            'MSG-017-001',
            new PartyIdentification(name: 'Requester'),
            [$request]
        );

        $generator = new Pain017Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('MndtCpyReq', $xml);
        $this->assertStringContainsString('pain.017.001.04', $xml);
    }

    // ==================== pain.018 Tests ====================

    public function testPain018SuspensionRequestCreation(): void {
        $request = MandateSuspensionRequest::create(
            'MNDT-001',
            new DateTimeImmutable('2025-06-01'),
            new DateTimeImmutable('2025-08-31'),
            'Urlaubszeit'
        );

        $this->assertEquals('MNDT-001', $request->getMandateId());
        $this->assertEquals('Urlaubszeit', $request->getSuspensionReason());
        $this->assertGreaterThan(0, $request->getDurationDays());
    }

    public function testPain018IndefiniteSuspension(): void {
        $request = MandateSuspensionRequest::indefinite(
            'MNDT-001',
            new DateTimeImmutable('2025-06-01'),
            'Konto gesperrt'
        );

        $this->assertEquals('2099-12-31', $request->getSuspensionEndDate()->format('Y-m-d'));
    }

    public function testPain018DocumentCreation(): void {
        $request = MandateSuspensionRequest::create(
            'MNDT-001',
            new DateTimeImmutable('2025-06-01'),
            new DateTimeImmutable('2025-08-31')
        );

        $document = Pain018Document::create(
            'MSG-018-001',
            new PartyIdentification(name: 'Requester'),
            [$request]
        );

        $this->assertEquals(PainType::PAIN_018, $document->getType());
        $this->assertEquals(1, $document->countRequests());
    }

    public function testPain018GeneratorOutput(): void {
        $request = MandateSuspensionRequest::create(
            'MNDT-001',
            new DateTimeImmutable('2025-06-01'),
            new DateTimeImmutable('2025-08-31'),
            'Urlaubszeit'
        );

        $document = Pain018Document::create(
            'MSG-018-001',
            new PartyIdentification(name: 'Requester'),
            [$request]
        );

        $generator = new Pain018Generator();
        $xml = $generator->generate($document);

        $this->assertStringContainsString('MndtSspnsnReq', $xml);
        $this->assertStringContainsString('pain.018.001.04', $xml);
        $this->assertStringContainsString('SspnsnPrd', $xml);
    }

    // ==================== MandateStatus Tests ====================

    public function testMandateStatusEnum(): void {
        $this->assertTrue(MandateStatus::ACCEPTED->isActive());
        $this->assertTrue(MandateStatus::AMENDED->isActive());
        $this->assertFalse(MandateStatus::PENDING->isActive());

        $this->assertTrue(MandateStatus::REJECTED->isTerminal());
        $this->assertTrue(MandateStatus::CANCELLED->isTerminal());
        $this->assertTrue(MandateStatus::EXPIRED->isTerminal());
        $this->assertFalse(MandateStatus::SUSPENDED->isTerminal());
    }

    // ==================== Validation Tests ====================

    public function testPain009Validation(): void {
        $document = Pain009Document::create(
            str_repeat('X', 40), // Zu lang
            new PartyIdentification(name: 'Initiator'),
            []
        );

        $validation = $document->validate();
        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    public function testPain011Validation(): void {
        $document = Pain011Document::create(
            'MSG-011-001',
            new PartyIdentification(name: 'Initiator'),
            [] // Keine Cancellations
        );

        $validation = $document->validate();
        $this->assertFalse($validation['valid']);
        $this->assertContains('Mindestens eine Mandatskündigung erforderlich', $validation['errors']);
    }

    public function testPain018ValidationInvalidDates(): void {
        $request = MandateSuspensionRequest::create(
            'MNDT-001',
            new DateTimeImmutable('2025-08-31'), // Start nach Ende
            new DateTimeImmutable('2025-06-01')
        );

        $document = Pain018Document::create(
            'MSG-018-001',
            new PartyIdentification(name: 'Requester'),
            [$request]
        );

        $validation = $document->validate();
        $this->assertFalse($validation['valid']);
    }

    // ==================== Generator Roundtrip Tests ====================

    public function testPain007GeneratorRoundtrip(): void {
        $groupHeader = Pain007GroupHeader::create('ROUND-007', new PartyIdentification(name: 'Company'));
        $orgGroupInfo = new OriginalGroupInformation('ORIG-007', 'pain.008.001.11');
        $reason = ReversalReason::customerRequest('Test');
        $tx = TransactionInformation::create('E2E-ROUND', 123.45, $reason);
        $orgPmtInfo = OriginalPaymentInformation::create('PMTINF-ROUND', [$tx]);
        $document = new Pain007Document($groupHeader, $orgGroupInfo, [$orgPmtInfo]);

        $generator = new Pain007Generator();
        $xml = $generator->generate($document);

        $this->assertTrue(PainParser::isValid($xml, PainType::PAIN_007));
        $parsed = PainParser::parsePain007($xml);
        $this->assertEquals('ROUND-007', $parsed->getGroupHeader()->getMessageId());
    }

    public function testPain009GeneratorRoundtrip(): void {
        $mandate = Mandate::sepaCore(
            'MNDT-ROUND-009',
            new DateTimeImmutable('2025-03-15'),
            'Creditor Test',
            'DE89370400440532013000',
            'COBADEFFXXX',
            'DE98ZZZ09999999999',
            'Debtor Test',
            'DE75512108001245126199',
            'INGDDEFFXXX'
        );

        $document = Pain009Document::create('ROUND-009', new PartyIdentification(name: 'Initiator'), [$mandate]);

        $generator = new Pain009Generator();
        $xml = $generator->generate($document);

        $this->assertTrue(PainParser::isValid($xml, PainType::PAIN_009));
        $parsed = PainParser::parsePain009($xml);
        $this->assertEquals('ROUND-009', $parsed->getMessageId());
    }

    // ==================== Edge Case Tests ====================

    public function testMandateWithAllOptionalFields(): void {
        $mandate = new Mandate(
            mandateId: 'MNDT-FULL-001',
            dateOfSignature: new DateTimeImmutable('2025-01-01'),
            creditor: new PartyIdentification(name: 'Full Creditor'),
            creditorAccount: new AccountIdentification(iban: 'DE89370400440532013000'),
            creditorAgent: new FinancialInstitution(bic: 'COBADEFFXXX'),
            debtor: new PartyIdentification(name: 'Full Debtor'),
            debtorAccount: new AccountIdentification(iban: 'DE75512108001245126199'),
            debtorAgent: new FinancialInstitution(bic: 'INGDDEFFXXX'),
            creditorSchemeId: 'DE98ZZZ09999999999',
            localInstrument: LocalInstrument::SEPA_CORE,
            sequenceType: SequenceType::FIRST,
            finalCollectionDate: new DateTimeImmutable('2030-12-31'),
            firstCollectionDate: new DateTimeImmutable('2025-02-01'),
            maxAmount: 10000.00,
            electronicSignature: 'SIG123456',
            mandateReason: 'Monatsgebühr Streaming'
        );

        $this->assertEquals('MNDT-FULL-001', $mandate->getMandateId());
        $this->assertEquals(SequenceType::FIRST, $mandate->getSequenceType());
        $this->assertEquals(10000.00, $mandate->getMaxAmount());
        $this->assertEquals('SIG123456', $mandate->getElectronicSignature());
        $this->assertEquals('Monatsgebühr Streaming', $mandate->getMandateReason());
    }

    public function testPain012RejectionWithMultipleMandates(): void {
        $acceptance1 = MandateAcceptance::accepted('MNDT-001');
        $rejection1 = MandateAcceptance::rejected('MNDT-002', 'Ungültige IBAN');
        $acceptance2 = MandateAcceptance::accepted('MNDT-003');

        $document = Pain012Document::forPain009('MSG-012-MIXED', 'ORIG-009', [$acceptance1, $rejection1, $acceptance2]);

        $this->assertFalse($document->isFullyAccepted());
        $this->assertEquals(3, count($document->getMandateAcceptances()));
    }

    public function testPain011MultipleReasonTypes(): void {
        $reason1 = CancellationReason::customerRequest();
        $reason2 = CancellationReason::accountClosed();
        $reason3 = CancellationReason::fraudulent();
        $reason4 = CancellationReason::fromCode('FF05', 'Falsches Format');

        $this->assertEquals('CUST', $reason1->getCodeString());
        $this->assertEquals('AC01', $reason2->getCodeString());
        $this->assertEquals('FRAD', $reason3->getCodeString());
        $this->assertEquals('FF05', $reason4->getCodeString());
        $this->assertEquals('Falsches Format', $reason4->getAdditionalInfo()[0]);
    }

    public function testPain010CreditorSchemeIdChange(): void {
        $details = AmendmentDetails::creditorSchemeIdChange('OLD-SCHEME-ID');

        $this->assertEquals('OLD-SCHEME-ID', $details->getOriginalCreditorSchemeId());
    }

    public function testPain014RejectedStatus(): void {
        $reason = StatusReason::fromCode('AC04', ['Konto gesperrt']);
        $status = PaymentActivationStatus::rejected('INST-REJ', 'E2E-REJ', $reason);

        $this->assertFalse($status->isAccepted());
        $this->assertFalse($status->isPending());
        $this->assertEquals(TransactionStatus::REJECTED, $status->getStatus());
        $this->assertEquals('AC04', $status->getStatusReason()->getCodeString());
    }

    public function testPain013MultiplePaymentRequests(): void {
        $request1 = PaymentActivationRequest::create('E2E-001', 100.00, 'D1', 'DE89370400440532013000', 'COBADEFFXXX', 'C1', 'DE75512108001245126199', 'INGDDEFFXXX');
        $request2 = PaymentActivationRequest::create('E2E-002', 200.00, 'D2', 'DE89370400440532013000', 'COBADEFFXXX', 'C2', 'DE75512108001245126199', 'INGDDEFFXXX');
        $request3 = PaymentActivationRequest::create('E2E-003', 150.00, 'D3', 'DE89370400440532013000', 'COBADEFFXXX', 'C3', 'DE75512108001245126199', 'INGDDEFFXXX');

        $document = Pain013Document::create('MSG-013-MULTI', new PartyIdentification(name: 'Multi'), [$request1, $request2, $request3]);

        $this->assertEquals(3, $document->countRequests());
        $this->assertEquals(450.00, $document->getControlSum());
    }

    public function testPain018SuspensionDuration(): void {
        $request = MandateSuspensionRequest::create(
            'MNDT-DUR',
            new DateTimeImmutable('2025-06-01'),
            new DateTimeImmutable('2025-06-30')
        );

        $this->assertEquals(29, $request->getDurationDays());
    }
}
