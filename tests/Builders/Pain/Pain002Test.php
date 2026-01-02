<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain002Test.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Pain;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Pain\Pain002DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\TransactionStatus;
use CommonToolkit\FinancialFormats\Enums\PainType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für pain.002 Document Builder (Payment Status Report).
 */
class Pain002Test extends BaseTestCase {

    #[Test]
    public function testCreateAllAcceptedStatusReport(): void {
        $document = Pain002DocumentBuilder::createAllAccepted(
            messageId: 'PSR-001',
            originalMessageId: 'MSG-001',
            originalMessageName: 'pain.001.001.12'
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('PSR-001', $document->getGroupHeader()->getMessageId());
        $this->assertSame(PainType::PAIN_002, $document->getType());
    }

    #[Test]
    public function testCreateRejectedStatusReport(): void {
        $document = Pain002DocumentBuilder::createRejected(
            messageId: 'PSR-002',
            originalMessageId: 'MSG-002',
            reasonCode: 'AC01',
            additionalInfo: 'Konto nicht gefunden',
            originalMessageName: 'pain.008.001.11'
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('PSR-002', $document->getGroupHeader()->getMessageId());
        $this->assertSame(TransactionStatus::REJECTED, $document->getOriginalGroupInformation()->getGroupStatus());
    }

    #[Test]
    public function testForPain001(): void {
        $document = (new Pain002DocumentBuilder())
            ->setMessageId('PSR-001')
            ->forPain001('MSG-001')
            ->addAcceptedPaymentInformation('PMT-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('pain.001.001.12', $document->getOriginalGroupInformation()->getOriginalMessageNameId());
    }

    #[Test]
    public function testForPain008(): void {
        $document = (new Pain002DocumentBuilder())
            ->setMessageId('PSR-002')
            ->forPain008('MSG-002')
            ->addAcceptedPaymentInformation('PMT-001')
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('pain.008.001.11', $document->getOriginalGroupInformation()->getOriginalMessageNameId());
    }

    #[Test]
    public function testAddAcceptedPaymentInformation(): void {
        $document = (new Pain002DocumentBuilder())
            ->setMessageId('PSR-003')
            ->forPain001('MSG-003')
            ->addAcceptedPaymentInformation('PMT-001')
            ->build();

        $this->assertCount(1, $document->getOriginalPaymentInformations());
        $paymentInfo = $document->getOriginalPaymentInformations()[0];
        $this->assertSame('PMT-001', $paymentInfo->getOriginalPaymentInformationId());
        $this->assertSame(TransactionStatus::ACCEPTED_SETTLEMENT_COMPLETED, $paymentInfo->getStatus());
    }

    #[Test]
    public function testAddRejectedPaymentInformation(): void {
        $document = (new Pain002DocumentBuilder())
            ->setMessageId('PSR-004')
            ->forPain001('MSG-004')
            ->addRejectedPaymentInformation(
                originalPaymentInformationId: 'PMT-001',
                status: TransactionStatus::REJECTED,
                reasonCode: 'AM04',
                additionalInfo: 'Betrag überschreitet Limit'
            )
            ->build();

        $paymentInfo = $document->getOriginalPaymentInformations()[0];
        $this->assertSame(TransactionStatus::REJECTED, $paymentInfo->getStatus());
        $this->assertCount(1, $paymentInfo->getStatusReasons());
    }

    #[Test]
    public function testMultiplePaymentInformations(): void {
        $document = (new Pain002DocumentBuilder())
            ->setMessageId('PSR-005')
            ->forPain001('MSG-005')
            ->addAcceptedPaymentInformation('PMT-001')
            ->addAcceptedPaymentInformation('PMT-002')
            ->addRejectedPaymentInformation('PMT-003', TransactionStatus::REJECTED, 'AC01')
            ->build();

        $this->assertCount(3, $document->getOriginalPaymentInformations());
    }

    #[Test]
    public function testBuildWithoutMessageIdThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MessageId muss angegeben werden');

        (new Pain002DocumentBuilder())
            ->forPain001('MSG-001')
            ->build();
    }

    #[Test]
    public function testBuildWithoutOriginalGroupInformationThrows(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OriginalGroupInformation muss angegeben werden');

        (new Pain002DocumentBuilder())
            ->setMessageId('PSR-001')
            ->build();
    }

    #[Test]
    public function testImmutableBuilder(): void {
        $builder1 = (new Pain002DocumentBuilder())
            ->setMessageId('PSR-001');

        $builder2 = $builder1->setCreationDateTime(new DateTimeImmutable('2025-01-01'));

        $this->assertNotSame($builder1, $builder2);
    }
}
