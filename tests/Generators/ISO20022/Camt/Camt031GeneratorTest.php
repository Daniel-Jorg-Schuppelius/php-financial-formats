<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt031GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt031DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt031Generator;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt031GeneratorTest extends BaseTestCase {
    private Camt031Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt031Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT031, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('RjctInvstgtn', $xml);
    }

    #[Test]
    public function testGenerateWithRejectionReason(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-002')
            ->withRejectionReason('NOOR')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Justfn>', $xml);
        $this->assertStringContainsString('NOOR', $xml);
    }

    #[Test]
    public function testGenerateWithProprietaryReason(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-003')
            ->withProprietaryRejectionReason('CUSTOM')
            ->withAdditionalInformation('Zusätzliche Info')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('CUSTOM', $xml);
        $this->assertStringContainsString('Zusätzliche Info', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-004')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt031DocumentBuilder::create('ASSIGN-005')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.031.001.06', $xml);
    }
}
