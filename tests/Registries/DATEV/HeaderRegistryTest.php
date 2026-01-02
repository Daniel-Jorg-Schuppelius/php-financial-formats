<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : HeaderRegistryTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Registries\DATEV;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\MetaHeaderDefinitionInterface;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Registries\DATEV\HeaderRegistry;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

class HeaderRegistryTest extends BaseTestCase {
    public function testGetReturnsMetaHeaderDefinitionForV700(): void {
        $definition = HeaderRegistry::get(700);

        $this->assertInstanceOf(MetaHeaderDefinitionInterface::class, $definition);
    }

    public function testGetReturnsSameInstanceForSameVersion(): void {
        $definition1 = HeaderRegistry::get(700);
        $definition2 = HeaderRegistry::get(700);

        $this->assertSame($definition1, $definition2);
    }

    public function testGetThrowsExceptionForUnsupportedVersion(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('wird nicht unterstützt');

        HeaderRegistry::get(999);
    }

    public function testGetFormatEnumReturnsBuchungsstapelEnum(): void {
        $enumClass = HeaderRegistry::getFormatEnum(Category::Buchungsstapel, 700);

        $this->assertNotEmpty($enumClass);
        $this->assertTrue(enum_exists($enumClass));
    }

    public function testGetFormatEnumThrowsExceptionForUnsupportedFormat(): void {
        $this->expectException(RuntimeException::class);

        // Version 999 existiert nicht
        HeaderRegistry::getFormatEnum(Category::Buchungsstapel, 999);
    }

    public function testGetSupportedVersionsContainsV700(): void {
        $versions = HeaderRegistry::getSupportedVersions();

        $this->assertContains(700, $versions);
    }

    public function testGetAvailableVersionsContainsV700(): void {
        $versions = HeaderRegistry::getAvailableVersions();

        $this->assertContains(700, $versions);
    }
}
