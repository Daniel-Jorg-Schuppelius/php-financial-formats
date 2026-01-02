<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtParserRegistryTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Registries;

use CommonToolkit\FinancialFormats\Registries\CamtParserRegistry;
use Tests\Contracts\BaseTestCase;
use ReflectionClass;

class CamtParserRegistryTest extends BaseTestCase {
    public function testInitializeDoesNotThrow(): void {
        // Initialisiere Registry - sollte ohne Fehler durchlaufen
        CamtParserRegistry::initialize();

        $this->assertTrue(true);
    }

    public function testInitializeIsIdempotent(): void {
        // Mehrfaches Initialisieren sollte keine Fehler verursachen
        CamtParserRegistry::initialize();
        CamtParserRegistry::initialize();
        CamtParserRegistry::initialize();

        $this->assertTrue(true);
    }

    public function testInitializeSetsInitializedFlag(): void {
        CamtParserRegistry::initialize();

        // Prüfe über Reflection, dass die Initialisierung stattfand
        $reflection = new ReflectionClass(CamtParserRegistry::class);
        $property = $reflection->getProperty('initialized');

        $this->assertTrue($property->getValue());
    }
}
