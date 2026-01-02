<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : VersionDiscoveryTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Registries\DATEV;

use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Registries\DATEV\VersionDiscovery;
use Tests\Contracts\BaseTestCase;

class VersionDiscoveryTest extends BaseTestCase {
    public function testDiscoverFindsVersions(): void {
        VersionDiscovery::discover();

        $versions = VersionDiscovery::getAvailableVersions();

        $this->assertNotEmpty($versions);
        $this->assertContains(700, $versions);
    }

    public function testIsVersionSupportedReturnsTrueForV700(): void {
        VersionDiscovery::discover();

        $this->assertTrue(VersionDiscovery::isVersionSupported(700));
    }

    public function testIsVersionSupportedReturnsFalseForUnknown(): void {
        VersionDiscovery::discover();

        $this->assertFalse(VersionDiscovery::isVersionSupported(999));
    }

    public function testGetMetaHeaderClassReturnsClassForV700(): void {
        VersionDiscovery::discover();

        $class = VersionDiscovery::getMetaHeaderClass(700);

        $this->assertNotNull($class);
        $this->assertTrue(class_exists($class));
    }

    public function testIsFormatSupportedForBuchungsstapel(): void {
        VersionDiscovery::discover();

        $this->assertTrue(VersionDiscovery::isFormatSupported(Category::Buchungsstapel, 700));
    }

    public function testGetFormatEnumReturnsBuchungsstapelEnum(): void {
        VersionDiscovery::discover();

        $enum = VersionDiscovery::getFormatEnum(Category::Buchungsstapel, 700);

        $this->assertNotNull($enum);
    }

    public function testGetSupportedFormatsReturnsFormatsForV700(): void {
        VersionDiscovery::discover();

        $formats = VersionDiscovery::getSupportedFormats(700);

        $this->assertNotEmpty($formats);
    }
}
