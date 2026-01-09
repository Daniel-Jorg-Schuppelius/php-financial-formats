<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt030GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt030DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt030Generator;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt030GeneratorTest extends BaseTestCase {
    private Camt030Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt030Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT030, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt030DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('NtfctnOfCaseAssgnmt', $xml);
    }

    #[Test]
    public function testGenerateContainsHeader(): void {
        $document = Camt030DocumentBuilder::create('ASSIGN-002')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Hdr>', $xml);
        $this->assertStringContainsString('ASSIGN-002', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt030DocumentBuilder::create('ASSIGN-003')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithNotification(): void {
        $document = Camt030DocumentBuilder::create('ASSIGN-004')
            ->withNotificationJustification('Fall zur Bearbeitung weitergeleitet')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Ntfctn>', $xml);
        $this->assertStringContainsString('Fall zur Bearbeitung weitergeleitet', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt030DocumentBuilder::create('ASSIGN-005')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.030.001.06', $xml);
    }
}
