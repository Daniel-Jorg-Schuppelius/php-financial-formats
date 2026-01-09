<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainVersionTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Enums\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainVersion;
use Tests\Contracts\BaseTestCase;

class PainVersionTest extends BaseTestCase {
    public function testAllVersionsExist(): void {
        $expectedVersions = [
            'V01',
            'V02',
            'V03',
            'V04',
            'V05',
            'V06',
            'V07',
            'V08',
            'V09',
            'V10',
            'V11',
            'V12',
            'V13',
            'V14',
        ];

        $actualVersions = array_map(fn($case) => $case->name, PainVersion::cases());

        foreach ($expectedVersions as $expected) {
            $this->assertContains($expected, $actualVersions, "Expected version $expected to exist");
        }
        $this->assertCount(14, PainVersion::cases());
    }

    public function testVersionValues(): void {
        $this->assertEquals('01', PainVersion::V01->value);
        $this->assertEquals('08', PainVersion::V08->value);
        $this->assertEquals('12', PainVersion::V12->value);
        $this->assertEquals('14', PainVersion::V14->value);
    }

    public function testGetNamespace(): void {
        $namespace = PainVersion::V12->getNamespace(PainType::PAIN_001);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:pain.001.001.12', $namespace);

        $namespace = PainVersion::V14->getNamespace(PainType::PAIN_002);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:pain.002.001.14', $namespace);

        $namespace = PainVersion::V11->getNamespace(PainType::PAIN_008);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:pain.008.001.11', $namespace);
    }

    public function testGetSchemaLocation(): void {
        $location = PainVersion::V12->getSchemaLocation(PainType::PAIN_001);
        $this->assertEquals('pain.001.001.12.xsd', $location);

        $location = PainVersion::V08->getSchemaLocation(PainType::PAIN_009);
        $this->assertEquals('pain.009.001.08.xsd', $location);
    }

    public function testGetShortVersion(): void {
        $this->assertEquals('12', PainVersion::V12->getShortVersion());
        $this->assertEquals('08', PainVersion::V08->getShortVersion());
        $this->assertEquals('04', PainVersion::V04->getShortVersion());
    }

    public function testFromNamespace(): void {
        $version = PainVersion::fromNamespace('urn:iso:std:iso:20022:tech:xsd:pain.001.001.12');
        $this->assertEquals(PainVersion::V12, $version);

        $version = PainVersion::fromNamespace('urn:iso:std:iso:20022:tech:xsd:pain.008.001.11');
        $this->assertEquals(PainVersion::V11, $version);

        $version = PainVersion::fromNamespace('urn:iso:std:iso:20022:tech:xsd:pain.017.001.04');
        $this->assertEquals(PainVersion::V04, $version);

        $version = PainVersion::fromNamespace('invalid-namespace');
        $this->assertNull($version);
    }

    public function testGetDefault(): void {
        $this->assertEquals(PainVersion::V12, PainVersion::getDefault(PainType::PAIN_001));
        $this->assertEquals(PainVersion::V14, PainVersion::getDefault(PainType::PAIN_002));
        $this->assertEquals(PainVersion::V12, PainVersion::getDefault(PainType::PAIN_007));
        $this->assertEquals(PainVersion::V11, PainVersion::getDefault(PainType::PAIN_008));
        $this->assertEquals(PainVersion::V08, PainVersion::getDefault(PainType::PAIN_009));
        $this->assertEquals(PainVersion::V08, PainVersion::getDefault(PainType::PAIN_010));
        $this->assertEquals(PainVersion::V08, PainVersion::getDefault(PainType::PAIN_011));
        $this->assertEquals(PainVersion::V08, PainVersion::getDefault(PainType::PAIN_012));
        $this->assertEquals(PainVersion::V11, PainVersion::getDefault(PainType::PAIN_013));
        $this->assertEquals(PainVersion::V11, PainVersion::getDefault(PainType::PAIN_014));
        $this->assertEquals(PainVersion::V04, PainVersion::getDefault(PainType::PAIN_017));
        $this->assertEquals(PainVersion::V04, PainVersion::getDefault(PainType::PAIN_018));
    }

    public function testGetSupportedVersions(): void {
        // Credit Transfer supports many versions
        $versions = PainVersion::getSupportedVersions(PainType::PAIN_001);
        $this->assertContains(PainVersion::V12, $versions);
        $this->assertContains(PainVersion::V08, $versions);
        $this->assertContains(PainVersion::V03, $versions);

        // Mandate formats typically V01-V08
        $versions = PainVersion::getSupportedVersions(PainType::PAIN_009);
        $this->assertContains(PainVersion::V08, $versions);
        $this->assertContains(PainVersion::V01, $versions);

        // Newer mandate copy format V01-V04
        $versions = PainVersion::getSupportedVersions(PainType::PAIN_017);
        $this->assertContains(PainVersion::V04, $versions);
        $this->assertContains(PainVersion::V01, $versions);
    }

    public function testIsSupported(): void {
        $this->assertTrue(PainVersion::V12->isSupported(PainType::PAIN_001));
        $this->assertTrue(PainVersion::V08->isSupported(PainType::PAIN_001));
        $this->assertTrue(PainVersion::V04->isSupported(PainType::PAIN_017));

        // V14 is not supported for PAIN_001 (only goes up to V12)
        $this->assertFalse(PainVersion::V14->isSupported(PainType::PAIN_001));
        // V14 is supported for PAIN_002
        $this->assertTrue(PainVersion::V14->isSupported(PainType::PAIN_002));
    }

    public function testVersionNamespaceRoundTrip(): void {
        foreach (PainType::cases() as $type) {
            $defaultVersion = PainVersion::getDefault($type);
            $namespace = $defaultVersion->getNamespace($type);

            $parsedVersion = PainVersion::fromNamespace($namespace);
            $this->assertEquals($defaultVersion, $parsedVersion, "Round-trip failed for {$type->value}");
        }
    }
}
