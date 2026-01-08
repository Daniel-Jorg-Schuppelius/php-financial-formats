<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain009Test.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Builders\Pain;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Pain\Pain009DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type9\Document;
use CommonToolkit\FinancialFormats\Enums\Pain\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\Pain\PainType;
use CommonToolkit\FinancialFormats\Enums\Pain\SequenceType;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für pain.009 Document Builder (Mandate Initiation Request).
 */
class Pain009Test extends BaseTestCase {

    #[Test]
    public function testCreateSimpleCoreMandateDocument(): void {
        $document = Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH')
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Max Mustermann', 'DE91100000000123456789', 'DEUTDEFF')
            ->done()
            ->build();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('MSG-001', $document->getMessageId());
        $this->assertSame(PainType::PAIN_009, $document->getType());
        $this->assertCount(1, $document->getMandates());
        $this->assertTrue($document->getMandates()[0]->isCore());
    }

    #[Test]
    public function testCreateB2BMandateDocument(): void {
        $document = Pain009DocumentBuilder::create('MSG-002', 'Business GmbH')
            ->beginB2BMandate('MNDT-001', new DateTimeImmutable('2025-02-01'))
            ->creditor('Business GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Partner AG', 'DE91100000000123456789', 'DEUTDEFF')
            ->done()
            ->build();

        $mandate = $document->getMandates()[0];
        $this->assertTrue($mandate->isB2B());
        $this->assertSame(LocalInstrument::SEPA_B2B, $mandate->getLocalInstrument());
    }

    #[Test]
    public function testMultipleMandates(): void {
        $document = Pain009DocumentBuilder::create('MSG-003', 'Firma GmbH')
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Kunde 1', 'DE11111111111111111111', 'DEUTDEFF')
            ->done()
            ->beginCoreMandate('MNDT-002', new DateTimeImmutable('2025-01-20'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Kunde 2', 'DE22222222222222222222', 'COBADEFF')
            ->done()
            ->beginCoreMandate('MNDT-003', new DateTimeImmutable('2025-01-25'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Kunde 3', 'DE33333333333333333333', 'GENODEF1')
            ->done()
            ->build();

        $this->assertCount(3, $document->getMandates());
        $this->assertSame(3, $document->countMandates());
    }

    #[Test]
    public function testMandateWithOptionalFields(): void {
        $document = Pain009DocumentBuilder::create('MSG-004', 'Firma GmbH')
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Max Mustermann', 'DE91100000000123456789', 'DEUTDEFF')
            ->sequenceType(SequenceType::RECURRING)
            ->firstCollectionDate(new DateTimeImmutable('2025-02-01'))
            ->finalCollectionDate(new DateTimeImmutable('2026-01-31'))
            ->maxAmount(1000.00)
            ->mandateReason('Monatliche Mitgliedsbeiträge')
            ->done()
            ->build();

        $mandate = $document->getMandates()[0];
        $this->assertSame(SequenceType::RECURRING, $mandate->getSequenceType());
        $this->assertNotNull($mandate->getFirstCollectionDate());
        $this->assertNotNull($mandate->getFinalCollectionDate());
        $this->assertSame(1000.00, $mandate->getMaxAmount());
        $this->assertSame('Monatliche Mitgliedsbeiträge', $mandate->getMandateReason());
    }

    #[Test]
    public function testStaticFactoryCreateCoreMandate(): void {
        $document = Pain009DocumentBuilder::createCoreMandate(
            messageId: 'MSG-005',
            mandateId: 'MNDT-001',
            dateOfSignature: new DateTimeImmutable('2025-01-15'),
            creditorName: 'Firma GmbH',
            creditorIban: 'DE89370400440532013000',
            creditorBic: 'COBADEFFXXX',
            creditorSchemeId: 'DE98ZZZ09999999999',
            debtorName: 'Max Mustermann',
            debtorIban: 'DE91100000000123456789',
            debtorBic: 'DEUTDEFF'
        );

        $this->assertInstanceOf(Document::class, $document);
        $this->assertSame('MSG-005', $document->getMessageId());
        $mandate = $document->getMandates()[0];
        $this->assertSame('MNDT-001', $mandate->getMandateId());
        $this->assertTrue($mandate->isCore());
    }

    #[Test]
    public function testStaticFactoryCreateB2BMandate(): void {
        $document = Pain009DocumentBuilder::createB2BMandate(
            messageId: 'MSG-006',
            mandateId: 'MNDT-B2B-001',
            dateOfSignature: new DateTimeImmutable('2025-02-01'),
            creditorName: 'Business GmbH',
            creditorIban: 'DE89370400440532013000',
            creditorBic: 'COBADEFFXXX',
            creditorSchemeId: 'DE98ZZZ09999999999',
            debtorName: 'Partner AG',
            debtorIban: 'DE91100000000123456789',
            debtorBic: 'DEUTDEFF'
        );

        $this->assertInstanceOf(Document::class, $document);
        $mandate = $document->getMandates()[0];
        $this->assertSame('MNDT-B2B-001', $mandate->getMandateId());
        $this->assertTrue($mandate->isB2B());
    }

    #[Test]
    public function testAddPrebuiltMandate(): void {
        $prebuiltMandate = Mandate::sepaCore(
            mandateId: 'MNDT-PRE-001',
            dateOfSignature: new DateTimeImmutable('2025-03-01'),
            creditorName: 'Firma GmbH',
            creditorIban: 'DE89370400440532013000',
            creditorBic: 'COBADEFFXXX',
            creditorSchemeId: 'DE98ZZZ09999999999',
            debtorName: 'Vorbereiteter Kunde',
            debtorIban: 'DE55555555555555555555',
            debtorBic: 'DEUTDEFF'
        );

        $document = Pain009DocumentBuilder::create('MSG-007', 'Firma GmbH')
            ->addMandate($prebuiltMandate)
            ->build();

        $this->assertCount(1, $document->getMandates());
        $this->assertSame('MNDT-PRE-001', $document->getMandates()[0]->getMandateId());
    }

    #[Test]
    public function testMessageIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MsgId must not exceed 35 characters');

        Pain009DocumentBuilder::create(str_repeat('X', 36), 'Firma GmbH');
    }

    #[Test]
    public function testMandateIdMaxLength(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MndtId must not exceed 35 characters');

        Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH')
            ->beginCoreMandate(str_repeat('X', 36), new DateTimeImmutable('2025-01-15'));
    }

    #[Test]
    public function testBuildWithoutMandatesThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mindestens ein Mandat erforderlich');

        Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH')->build();
    }

    #[Test]
    public function testBuildWithoutCreditorThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gläubiger-Informationen erforderlich');

        Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH')
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->debtor('Max Mustermann', 'DE91100000000123456789', 'DEUTDEFF')
            ->done();
    }

    #[Test]
    public function testBuildWithoutDebtorThrows(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Schuldner-Informationen erforderlich');

        Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH')
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->done();
    }

    #[Test]
    public function testImmutableBuilder(): void {
        $builder1 = Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH');
        $builder2 = $builder1->withCreationDateTime(new DateTimeImmutable('2025-01-01'));

        $this->assertNotSame($builder1, $builder2);
    }

    #[Test]
    public function testWithCreationDateTime(): void {
        $creationDate = new DateTimeImmutable('2025-06-15 10:30:00');

        $document = Pain009DocumentBuilder::create('MSG-008', 'Firma GmbH')
            ->withCreationDateTime($creationDate)
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Max Mustermann', 'DE91100000000123456789', 'DEUTDEFF')
            ->done()
            ->build();

        $this->assertEquals($creationDate, $document->getCreationDateTime());
    }

    #[Test]
    public function testDocumentValidation(): void {
        $document = Pain009DocumentBuilder::create('MSG-009', 'Firma GmbH')
            ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2025-01-15'))
            ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
            ->debtor('Max Mustermann', 'DE91100000000123456789', 'DEUTDEFF')
            ->done()
            ->build();

        $validationResult = $document->validate();
        $this->assertTrue($validationResult['valid']);
        $this->assertEmpty($validationResult['errors']);
    }
}
