<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt028GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt028DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type28\AdditionalPaymentInformation;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt028Generator;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt028GeneratorTest extends BaseTestCase {
    private Camt028Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt028Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT028, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt028DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('AddtlPmtInf', $xml);
    }

    #[Test]
    public function testGenerateWithAdditionalInformation(): void {
        $info = new AdditionalPaymentInformation(remittanceInformation: 'Zahlungsdetails hier');
        $document = Camt028DocumentBuilder::create('ASSIGN-002')
            ->addAdditionalInformation($info)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<AddtlPmtInf>', $xml);
    }

    #[Test]
    public function testGenerateWithOriginalTransaction(): void {
        $document = Camt028DocumentBuilder::create('ASSIGN-004')
            ->withOriginalTransaction('MSG-001', 'E2E-001', 'TXN-001')
            ->withOriginalAmount(2000.00, CurrencyCode::Euro)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('MSG-001', $xml);
        $this->assertStringContainsString('2000.00', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt028DocumentBuilder::create('ASSIGN-005')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.028.001.06', $xml);
    }
}
