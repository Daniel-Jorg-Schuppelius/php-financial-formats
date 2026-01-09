<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain007Test.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Pain;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Pain\Pain007DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\TransactionInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\ReversalReason;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für pain.007 Document Builder (Payment Reversal).
 */
class Pain007Test extends BaseTestCase {

    #[Test]
    public function testStaticFactoryCreateSingleReversal(): void {
        $document = Pain007DocumentBuilder::createSingleReversal(
            messageId: 'REV-001',
            initiatorName: 'Firma GmbH',
            originalMessageId: 'MSG-001',
            originalPaymentInformationId: 'PMT-001',
            originalEndToEndId: 'E2E-001',
            amount: 500.00,
            reasonCode: 'MD06'
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('REV-001', $document->getGroupHeader()->getMessageId());
        $this->assertSame(PainType::PAIN_007, $document->getType());
        $this->assertCount(1, $document->getOriginalPaymentInformations());
    }

    #[Test]
    public function testCreateWithBuilder(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $reason = ReversalReason::customerRequest('Kunde wünscht Stornierung');

        $document = (new Pain007DocumentBuilder())
            ->setMessageId('REV-002')
            ->setInitiatingParty($initiatingParty)
            ->forPain008('MSG-002')
            ->beginReversalInstruction('PMT-001')
            ->addTransactionReversal(TransactionInformation::create(
                originalEndToEndId: 'E2E-001',
                reversedAmount: 1000.00,
                reason: $reason
            ))
            ->setReversalReason($reason)
            ->endReversalInstruction()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('REV-002', $document->getGroupHeader()->getMessageId());
    }

    #[Test]
    public function testMultipleReversalInstructions(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $reason = ReversalReason::customerRequest();

        $document = (new Pain007DocumentBuilder())
            ->setMessageId('REV-003')
            ->setInitiatingParty($initiatingParty)
            ->forPain008('MSG-003')
            ->beginReversalInstruction('PMT-001')
            ->addTransactionReversal(TransactionInformation::create(
                originalEndToEndId: 'E2E-001',
                reversedAmount: 100.00,
                reason: $reason
            ))
            ->endReversalInstruction()
            ->beginReversalInstruction('PMT-002')
            ->addTransactionReversal(TransactionInformation::create(
                originalEndToEndId: 'E2E-002',
                reversedAmount: 200.00,
                reason: $reason
            ))
            ->endReversalInstruction()
            ->build();

        $this->assertCount(2, $document->getOriginalPaymentInformations());
    }

    #[Test]
    public function testWithReversalReason(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $reason = ReversalReason::fromCode('MD06', ['Kunde hat Lastschrift widerrufen']);

        $document = (new Pain007DocumentBuilder())
            ->setMessageId('REV-004')
            ->setInitiatingParty($initiatingParty)
            ->forPain008('MSG-004')
            ->beginReversalInstruction('PMT-001')
            ->addTransactionReversal(TransactionInformation::create(
                originalEndToEndId: 'E2E-001',
                reversedAmount: 500.00,
                reason: $reason
            ))
            ->setReversalReason($reason)
            ->endReversalInstruction()
            ->build();

        $paymentInfo = $document->getOriginalPaymentInformations()[0];
        $this->assertNotNull($paymentInfo->getReversalReason());
        $this->assertSame('MD06', $paymentInfo->getReversalReason()->getCodeString());
    }

    #[Test]
    public function testBuildWithoutMessageIdThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MessageId muss angegeben werden');

        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');

        (new Pain007DocumentBuilder())
            ->setInitiatingParty($initiatingParty)
            ->forPain008('MSG-001')
            ->build();
    }

    #[Test]
    public function testBuildWithoutInitiatingPartyThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('InitiatingParty muss angegeben werden');

        (new Pain007DocumentBuilder())
            ->setMessageId('REV-001')
            ->forPain008('MSG-001')
            ->build();
    }

    #[Test]
    public function testBuildWithoutOriginalGroupInformationThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OriginalGroupInformation muss angegeben werden');

        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');

        (new Pain007DocumentBuilder())
            ->setMessageId('REV-001')
            ->setInitiatingParty($initiatingParty)
            ->build();
    }

    #[Test]
    public function testImmutableBuilder(): void {
        $builder1 = (new Pain007DocumentBuilder())
            ->setMessageId('REV-001');

        $builder2 = $builder1->setCreationDateTime(new DateTimeImmutable('2025-01-01'));

        $this->assertNotSame($builder1, $builder2);
    }

    #[Test]
    public function testAddOriginalPaymentInformationDirectly(): void {
        $initiatingParty = new PartyIdentification(name: 'Firma GmbH');
        $reason = ReversalReason::customerRequest();

        $paymentInfo = OriginalPaymentInformation::create(
            originalPaymentInformationId: 'PMT-001',
            transactionInformations: [
                TransactionInformation::create(
                    originalEndToEndId: 'E2E-001',
                    reversedAmount: 750.00,
                    reason: $reason
                )
            ],
            reversalReason: $reason
        );

        $document = (new Pain007DocumentBuilder())
            ->setMessageId('REV-005')
            ->setInitiatingParty($initiatingParty)
            ->forPain008('MSG-005')
            ->addOriginalPaymentInformation($paymentInfo)
            ->build();

        $this->assertCount(1, $document->getOriginalPaymentInformations());
        $this->assertSame('PMT-001', $document->getOriginalPaymentInformations()[0]->getOriginalPaymentInformationId());
    }
}
