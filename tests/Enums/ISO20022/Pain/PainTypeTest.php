<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainTypeTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Enums\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainVersion;
use Tests\Contracts\BaseTestCase;

class PainTypeTest extends BaseTestCase {
    public function testAllCasesExist(): void {
        $expectedCases = [
            'PAIN_001',
            'PAIN_002',
            'PAIN_007',
            'PAIN_008',
            'PAIN_009',
            'PAIN_010',
            'PAIN_011',
            'PAIN_012',
            'PAIN_013',
            'PAIN_014',
            'PAIN_017',
            'PAIN_018',
        ];

        $actualCases = array_map(fn($case) => $case->name, PainType::cases());

        foreach ($expectedCases as $expected) {
            $this->assertContains($expected, $actualCases, "Expected case $expected to exist");
        }
        $this->assertCount(12, PainType::cases());
    }

    public function testNamespacePrefixReturnsDefaultNamespace(): void {
        $pain001 = PainType::PAIN_001;
        $namespace = $pain001->namespacePrefix();

        $this->assertStringContainsString('urn:iso:std:iso:20022:tech:xsd:pain.001.001', $namespace);
    }

    public function testGetNamespaceWithVersion(): void {
        $pain001 = PainType::PAIN_001;

        $nsV12 = $pain001->getNamespace(PainVersion::V12);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:pain.001.001.12', $nsV12);

        $nsV08 = $pain001->getNamespace(PainVersion::V08);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:pain.001.001.08', $nsV08);
    }

    public function testGetDefaultVersion(): void {
        $this->assertEquals(PainVersion::V12, PainType::PAIN_001->getDefaultVersion());
        $this->assertEquals(PainVersion::V14, PainType::PAIN_002->getDefaultVersion());
        $this->assertEquals(PainVersion::V11, PainType::PAIN_008->getDefaultVersion());
        $this->assertEquals(PainVersion::V08, PainType::PAIN_009->getDefaultVersion());
        $this->assertEquals(PainVersion::V04, PainType::PAIN_017->getDefaultVersion());
    }

    public function testGetSupportedVersions(): void {
        $versions = PainType::PAIN_001->getSupportedVersions();

        $this->assertIsArray($versions);
        $this->assertContains(PainVersion::V12, $versions);
        $this->assertContains(PainVersion::V08, $versions);
    }

    public function testDescriptionReturnsGermanText(): void {
        $this->assertEquals('Überweisungsauftrag', PainType::PAIN_001->description());
        $this->assertEquals('Zahlungsstatusbericht', PainType::PAIN_002->description());
        $this->assertEquals('Lastschriftauftrag', PainType::PAIN_008->description());
    }

    public function testGetMessageNameReturnsEnglishText(): void {
        $this->assertEquals('Customer Credit Transfer Initiation', PainType::PAIN_001->getMessageName());
        $this->assertEquals('Customer Payment Status Report', PainType::PAIN_002->getMessageName());
        $this->assertEquals('Customer Direct Debit Initiation', PainType::PAIN_008->getMessageName());
        $this->assertEquals('Mandate Initiation Request', PainType::PAIN_009->getMessageName());
    }

    public function testRootElement(): void {
        $this->assertEquals('CstmrCdtTrfInitn', PainType::PAIN_001->rootElement());
        $this->assertEquals('CstmrPmtStsRpt', PainType::PAIN_002->rootElement());
        $this->assertEquals('CstmrDrctDbtInitn', PainType::PAIN_008->rootElement());
        $this->assertEquals('MndtInitnReq', PainType::PAIN_009->rootElement());
    }

    public function testIsCreditTransfer(): void {
        $this->assertTrue(PainType::PAIN_001->isCreditTransfer());
        $this->assertFalse(PainType::PAIN_002->isCreditTransfer());
        $this->assertFalse(PainType::PAIN_008->isCreditTransfer());
    }

    public function testIsDirectDebit(): void {
        $this->assertTrue(PainType::PAIN_008->isDirectDebit());
        $this->assertFalse(PainType::PAIN_001->isDirectDebit());
        $this->assertFalse(PainType::PAIN_002->isDirectDebit());
    }

    public function testIsMandate(): void {
        $this->assertTrue(PainType::PAIN_009->isMandate());
        $this->assertTrue(PainType::PAIN_010->isMandate());
        $this->assertTrue(PainType::PAIN_011->isMandate());
        $this->assertTrue(PainType::PAIN_012->isMandate());
        $this->assertTrue(PainType::PAIN_017->isMandate());
        $this->assertTrue(PainType::PAIN_018->isMandate());

        $this->assertFalse(PainType::PAIN_001->isMandate());
        $this->assertFalse(PainType::PAIN_002->isMandate());
        $this->assertFalse(PainType::PAIN_008->isMandate());
    }

    public function testIsStatusReport(): void {
        $this->assertTrue(PainType::PAIN_002->isStatusReport());
        $this->assertTrue(PainType::PAIN_014->isStatusReport());

        $this->assertFalse(PainType::PAIN_001->isStatusReport());
        $this->assertFalse(PainType::PAIN_008->isStatusReport());
    }

    public function testFromNamespace(): void {
        $type = PainType::fromNamespace('urn:iso:std:iso:20022:tech:xsd:pain.001.001.12');
        $this->assertEquals(PainType::PAIN_001, $type);

        $type = PainType::fromNamespace('urn:iso:std:iso:20022:tech:xsd:pain.008.001.11');
        $this->assertEquals(PainType::PAIN_008, $type);

        $type = PainType::fromNamespace('invalid-namespace');
        $this->assertNull($type);
    }

    public function testFromRootElement(): void {
        $type = PainType::fromRootElement('CstmrCdtTrfInitn');
        $this->assertEquals(PainType::PAIN_001, $type);

        $type = PainType::fromRootElement('CstmrDrctDbtInitn');
        $this->assertEquals(PainType::PAIN_008, $type);

        $type = PainType::fromRootElement('UnknownElement');
        $this->assertNull($type);
    }

    public function testFromXml(): void {
        $xml = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"><CstmrCdtTrfInitn/></Document>';
        $type = PainType::fromXml($xml);
        $this->assertEquals(PainType::PAIN_001, $type);
    }

    public function testFromXmlWithPrefix(): void {
        $xml = '<?xml version="1.0"?><ns:Document xmlns:ns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.11"><ns:CstmrDrctDbtInitn/></ns:Document>';
        $type = PainType::fromXml($xml);
        $this->assertEquals(PainType::PAIN_008, $type);
    }

    public function testFromXmlDoesNotMatchOrgnlMsgNmId(): void {
        // This XML has pain.002 as namespace but references pain.001 in OrgnlMsgNmId
        $xml = '<?xml version="1.0"?>
            <Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.002.001.14">
                <CstmrPmtStsRpt>
                    <OrgnlMsgNmId>pain.001.001.12</OrgnlMsgNmId>
                </CstmrPmtStsRpt>
            </Document>';

        $type = PainType::fromXml($xml);
        $this->assertEquals(PainType::PAIN_002, $type, 'Should match namespace, not OrgnlMsgNmId');
    }

    public function testFromXmlReturnsNullForInvalidXml(): void {
        $type = PainType::fromXml('<invalid>content</invalid>');
        $this->assertNull($type);
    }
}
