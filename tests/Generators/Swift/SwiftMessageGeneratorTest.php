<?php
/*
 * Created on   : Mon Jan 06 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SwiftMessageGeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Swift;

use CommonToolkit\FinancialFormats\Entities\Swift\ApplicationHeader;
use CommonToolkit\FinancialFormats\Entities\Swift\BasicHeader;
use CommonToolkit\FinancialFormats\Entities\Swift\Message;
use CommonToolkit\FinancialFormats\Entities\Swift\Trailer;
use CommonToolkit\FinancialFormats\Entities\Swift\UserHeader;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\FinancialFormats\Generators\Swift\SwiftMessageGenerator;
use Tests\Contracts\BaseTestCase;

class SwiftMessageGeneratorTest extends BaseTestCase {
    private SwiftMessageGenerator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new SwiftMessageGenerator();
    }

    public function testGenerateBasicMessage(): void {
        $textBlock = <<<MT940
:20:TESTREF001
:25:DE89370400440532013000
:28C:001/001
MT940;

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'COBADEFFXXX'),
            textBlock: $textBlock
        );

        $output = $this->generator->generate($message);

        // Block 1 - Basic Header
        $this->assertStringContainsString('{1:F01DEUTDEFFXXX0000000001}', $output);

        // Block 2 - Application Header
        $this->assertStringContainsString('{2:I940COBADEFFXXXN}', $output);

        // Block 4 - Text Block
        $this->assertStringContainsString('{4:', $output);
        $this->assertStringContainsString(':20:TESTREF001', $output);
        $this->assertStringContainsString('-}', $output);
    }

    public function testGenerateWithUserHeader(): void {
        $textBlock = <<<MT940
:20:TESTREF002
:25:DE89370400440532013000
MT940;

        $userHeader = new UserHeader([
            '108' => 'MUR12345678901234',
            '119' => 'STP'
        ]);

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000002'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'COBADEFFXXX'),
            textBlock: $textBlock,
            userHeader: $userHeader
        );

        $output = $this->generator->generate($message);

        // Block 3 - User Header
        $this->assertStringContainsString('{3:', $output);
        $this->assertStringContainsString('{108:MUR12345678901234}', $output);
        $this->assertStringContainsString('{119:STP}', $output);
    }

    public function testGenerateWithTrailer(): void {
        $textBlock = <<<MT940
:20:TESTREF003
:25:DE89370400440532013000
MT940;

        $trailer = new Trailer([
            'CHK' => '123456789ABC',
            'TNG' => ''
        ]);

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000003'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'COBADEFFXXX'),
            textBlock: $textBlock,
            trailer: $trailer
        );

        $output = $this->generator->generate($message);

        // Block 5 - Trailer
        $this->assertStringContainsString('{5:', $output);
        $this->assertStringContainsString('{CHK:123456789ABC}', $output);
        $this->assertStringContainsString('{TNG:}', $output);
    }

    public function testGenerateFullMessage(): void {
        $textBlock = <<<MT940
:20:FULLTEST001
:25:DE89370400440532013000
:28C:001/001
MT940;

        $userHeader = new UserHeader([
            '108' => 'MUR00000000000001'
        ]);

        $trailer = new Trailer([
            'CHK' => 'ABCDEF123456'
        ]);

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'BANKDEFFXXX', '1234', '567890'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'DEUTDEFFXXX'),
            textBlock: $textBlock,
            userHeader: $userHeader,
            trailer: $trailer
        );

        $output = $this->generator->generate($message);

        // Verify all blocks present in correct order
        $block1Pos = strpos($output, '{1:');
        $block2Pos = strpos($output, '{2:');
        $block3Pos = strpos($output, '{3:');
        $block4Pos = strpos($output, '{4:');
        $block5Pos = strpos($output, '{5:');

        $this->assertNotFalse($block1Pos);
        $this->assertNotFalse($block2Pos);
        $this->assertNotFalse($block3Pos);
        $this->assertNotFalse($block4Pos);
        $this->assertNotFalse($block5Pos);

        $this->assertLessThan($block2Pos, $block1Pos);
        $this->assertLessThan($block3Pos, $block2Pos);
        $this->assertLessThan($block4Pos, $block3Pos);
        $this->assertLessThan($block5Pos, $block4Pos);
    }

    public function testGeneratorMatchesToString(): void {
        $textBlock = <<<MT940
:20:COMPARE001
:25:DE89370400440532013000
MT940;

        $message = new Message(
            basicHeader: new BasicHeader('F', '01', 'DEUTDEFFXXX', '0000', '000001'),
            applicationHeader: new ApplicationHeader(false, MtType::MT940, 'COBADEFFXXX'),
            textBlock: $textBlock
        );

        // Generator output should match __toString output since __toString uses the generator
        $generatorOutput = $this->generator->generate($message);
        $toStringOutput = (string) $message;

        $this->assertEquals($generatorOutput, $toStringOutput);
    }
}
