<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MessageTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\Common\Banking\Swift;

use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document as Mt101Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type103\Document as Mt103Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document as Mt940Document;
use CommonToolkit\FinancialFormats\Entities\Swift\ApplicationHeader;
use CommonToolkit\FinancialFormats\Entities\Swift\BasicHeader;
use CommonToolkit\FinancialFormats\Entities\Swift\Message;
use CommonToolkit\FinancialFormats\Entities\Swift\Trailer;
use CommonToolkit\FinancialFormats\Entities\Swift\UserHeader;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

final class MessageTest extends BaseTestCase {
    public function testParseDocumentMt103(): void {
        $textBlock = <<<MT103
:20:TESTREF001
:23B:CRED
:32A:250512EUR1000,00
:50K:/DE89370400440532013000
Max Mustermann
:59:/DE89370400440532013001
Firma ABC
:71A:SHA
MT103;

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT103, 'COBADEFFXXX'),
            textBlock: $textBlock
        );

        $this->assertTrue($message->isPaymentOrder());
        $this->assertFalse($message->isStatement());

        /** @var Mt103Document $document */
        $document = $message->parseDocument();

        $this->assertInstanceOf(Mt103Document::class, $document);
        $this->assertEquals('TESTREF001', $document->getSendersReference());
        $this->assertEquals(1000.00, $document->getTransferDetails()->getAmount());
    }

    public function testParseDocumentMt101(): void {
        $textBlock = <<<MT101
:20:BATCHREF001
:28D:1/1
:50K:/123456789
Test Company
:30:250512
:21:TXN001
:32B:EUR500,00
:59:/987654321
Beneficiary
:71A:OUR
MT101;

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT101, 'COBADEFFXXX'),
            textBlock: $textBlock
        );

        $this->assertTrue($message->isPaymentOrder());
        $this->assertFalse($message->isStatement());

        /** @var Mt101Document $document */
        $document = $message->parseDocument();

        $this->assertInstanceOf(Mt101Document::class, $document);
        $this->assertEquals('BATCHREF001', $document->getSendersReference());
        $this->assertCount(1, $document->getTransactions());
    }

    public function testParseDocumentMt940(): void {
        $textBlock = <<<MT940
:20:STMT001
:25:DE89370400440532013000
:28C:001/001
:60F:C250501EUR10000,00
:61:2505010501C1000,00NTRFNONREF
:86:020?00Gutschrift?20Verwendungszweck
:62F:C250501EUR11000,00
MT940;

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(true, MtType::MT940),
            textBlock: $textBlock
        );

        $this->assertFalse($message->isPaymentOrder());
        $this->assertTrue($message->isStatement());

        /** @var Mt940Document $document */
        $document = $message->parseDocument();

        $this->assertInstanceOf(Mt940Document::class, $document);
    }

    public function testParseDocumentUnsupportedType(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT900),
            textBlock: ':20:REF'
        );

        $this->assertFalse($message->isPaymentOrder());
        $this->assertFalse($message->isStatement());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Nicht unterstützter MT-Typ');

        $message->parseDocument();
    }

    public function testIsPaymentOrder(): void {
        $mt103 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT103),
            textBlock: ''
        );

        $mt101 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT101),
            textBlock: ''
        );

        $mt940 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(true, MtType::MT940),
            textBlock: ''
        );

        $this->assertTrue($mt103->isPaymentOrder());
        $this->assertTrue($mt101->isPaymentOrder());
        $this->assertFalse($mt940->isPaymentOrder());
    }

    public function testIsStatement(): void {
        $mt940 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(true, MtType::MT940),
            textBlock: ''
        );

        $mt941 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(true, MtType::MT941),
            textBlock: ''
        );

        $mt942 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(true, MtType::MT942),
            textBlock: ''
        );

        $mt103 = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT103),
            textBlock: ''
        );

        $this->assertTrue($mt940->isStatement());
        $this->assertTrue($mt941->isStatement());
        $this->assertTrue($mt942->isStatement());
        $this->assertFalse($mt103->isStatement());
    }

    public function testGetBasicHeader(): void {
        $basicHeader = new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001');
        $applicationHeader = new ApplicationHeader(false, MtType::MT940);

        $message = new Message(
            basicHeader: $basicHeader,
            applicationHeader: $applicationHeader,
            textBlock: ''
        );

        $this->assertSame($basicHeader, $message->getBasicHeader());
    }

    public function testGetApplicationHeader(): void {
        $basicHeader = new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001');
        $applicationHeader = new ApplicationHeader(false, MtType::MT940);

        $message = new Message(
            basicHeader: $basicHeader,
            applicationHeader: $applicationHeader,
            textBlock: ''
        );

        $this->assertSame($applicationHeader, $message->getApplicationHeader());
    }

    public function testGetUserHeader(): void {
        $basicHeader = new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001');
        $applicationHeader = new ApplicationHeader(false, MtType::MT940);
        $userHeader = new UserHeader(['108' => 'MUR12345']);

        $message = new Message(
            basicHeader: $basicHeader,
            applicationHeader: $applicationHeader,
            textBlock: '',
            userHeader: $userHeader
        );

        $this->assertSame($userHeader, $message->getUserHeader());
    }

    public function testGetTrailer(): void {
        $basicHeader = new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001');
        $applicationHeader = new ApplicationHeader(false, MtType::MT940);
        $trailer = new Trailer(['CHK' => '123456789ABC']);

        $message = new Message(
            basicHeader: $basicHeader,
            applicationHeader: $applicationHeader,
            textBlock: '',
            trailer: $trailer
        );

        $this->assertSame($trailer, $message->getTrailer());
    }

    public function testGetTextBlock(): void {
        $textBlock = ':20:REF001';
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: $textBlock
        );

        $this->assertSame($textBlock, $message->getTextBlock());
    }

    public function testGetMessageType(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT103),
            textBlock: ''
        );

        $this->assertSame(MtType::MT103, $message->getMessageType());
    }

    public function testGetSenderBic(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: ''
        );

        $this->assertSame('DEUTDEFF', $message->getSenderBic());
    }

    public function testGetReceiverBic(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'COBADEFFXXX'),
            textBlock: ''
        );

        $this->assertSame('COBADEFFXXX', $message->getReceiverBic());
    }

    public function testIsOutputAndIsInput(): void {
        $outputMessage = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(true, MtType::MT940),
            textBlock: ''
        );

        $inputMessage = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: ''
        );

        $this->assertTrue($outputMessage->isOutput());
        $this->assertFalse($outputMessage->isInput());
        $this->assertFalse($inputMessage->isOutput());
        $this->assertTrue($inputMessage->isInput());
    }

    public function testIsTraining(): void {
        $trainingMessage = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: '',
            trailer: new Trailer(['TNG' => ''])
        );

        $normalMessage = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: ''
        );

        $this->assertTrue($trainingMessage->isTraining());
        $this->assertFalse($normalMessage->isTraining());
    }

    public function testGetChecksum(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: '',
            trailer: new Trailer(['CHK' => 'ABCDEF123456'])
        );

        $this->assertSame('ABCDEF123456', $message->getChecksum());
    }

    public function testGetMur(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: '',
            userHeader: new UserHeader(['108' => 'MUR12345678901234'])
        );

        $this->assertSame('MUR12345678901234', $message->getMur());
    }

    public function testGetUetr(): void {
        $uetr = '123e4567-e89b-12d3-a456-426614174000';
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940),
            textBlock: '',
            userHeader: new UserHeader(['121' => $uetr])
        );

        $this->assertSame($uetr, $message->getUetr());
    }

    public function testIsStp(): void {
        $stpMessage = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT103),
            textBlock: '',
            userHeader: new UserHeader(['119' => 'STP'])
        );

        $normalMessage = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT103),
            textBlock: ''
        );

        $this->assertTrue($stpMessage->isStp());
        $this->assertFalse($normalMessage->isStp());
    }

    public function testToString(): void {
        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFAXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'COBADEFFXXX'),
            textBlock: ':20:REF001'
        );

        $result = (string)$message;
        $this->assertStringContainsString('{1:', $result);
        $this->assertStringContainsString('{2:', $result);
        $this->assertStringContainsString('{4:', $result);
    }
}
