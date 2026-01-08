<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtReflectionParserTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Parsers;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type31\Document as Camt031Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type34\Document as Camt034Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type35\Document as Camt035Document;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Parsers\CamtReflectionParser;
use CommonToolkit\FinancialFormats\Registries\CamtParserRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests für den Reflection-basierten CAMT-Parser.
 */
class CamtReflectionParserTest extends TestCase {
    protected function setUp(): void {
        CamtParserRegistry::reset();
        CamtParserRegistry::initialize();
    }

    public function testParseCamt031ViaReflection(): void {
        $xmlContent = file_get_contents(__DIR__ . '/../../.samples/Banking/CAMT/sample_camt031.xml');

        $document = CamtReflectionParser::parse($xmlContent, CamtType::CAMT031);

        $this->assertInstanceOf(Camt031Document::class, $document);
        /** @var Camt031Document $document */
        $this->assertNotEmpty($document->getAssignmentId());
        $this->assertNotNull($document->getCreationDateTime());
    }

    public function testParseCamt034ViaReflection(): void {
        $xmlContent = file_get_contents(__DIR__ . '/../../.samples/Banking/CAMT/sample_camt034.xml');

        $document = CamtReflectionParser::parse($xmlContent, CamtType::CAMT034);

        $this->assertInstanceOf(Camt034Document::class, $document);
        /** @var Camt034Document $document */
        $this->assertNotEmpty($document->getAssignmentId());
    }

    public function testParseCamt035ViaReflection(): void {
        $xmlContent = file_get_contents(__DIR__ . '/../../.samples/Banking/CAMT/sample_camt035.xml');

        $document = CamtReflectionParser::parse($xmlContent, CamtType::CAMT035);

        $this->assertInstanceOf(Camt035Document::class, $document);
        /** @var Camt035Document $document */
        $this->assertNotEmpty($document->getAssignmentId());
    }

    public function testCompareReflectionWithOriginalParser(): void {
        $xmlContent = file_get_contents(__DIR__ . '/../../.samples/Banking/CAMT/sample_camt031.xml');

        // Parse mit Original-Parser (nutzt Reflection für CAMT031)
        $originalDoc = \CommonToolkit\FinancialFormats\Parsers\CamtParser::parse($xmlContent);

        // Parse mit Reflection-Parser direkt
        $reflectionDoc = CamtReflectionParser::parse($xmlContent, CamtType::CAMT031);
        $this->assertInstanceOf(Camt031Document::class, $reflectionDoc);

        /** @var Camt031Document $reflectionDoc */
        /** @var Camt031Document $originalDoc */
        // Vergleiche Ergebnisse
        $this->assertEquals($originalDoc->getAssignmentId(), $reflectionDoc->getAssignmentId());
        $this->assertEquals($originalDoc->getCreationDateTime(), $reflectionDoc->getCreationDateTime());
        $this->assertEquals($originalDoc->getCaseId(), $reflectionDoc->getCaseId());
        $this->assertEquals($originalDoc->getCaseCreator(), $reflectionDoc->getCaseCreator());
    }

    public function testRegistryInitialization(): void {
        CamtParserRegistry::reset();
        $this->assertFalse(CamtParserRegistry::isInitialized());

        CamtParserRegistry::initialize();
        $this->assertTrue(CamtParserRegistry::isInitialized());

        // Prüfe, dass Typen registriert sind
        $registeredTypes = CamtReflectionParser::getRegisteredTypes();
        $this->assertArrayHasKey('camt.031', $registeredTypes);
        $this->assertArrayHasKey('camt.034', $registeredTypes);
        $this->assertArrayHasKey('camt.035', $registeredTypes);
    }

    public function testUnregisteredTypeThrowsException(): void {
        CamtParserRegistry::reset();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Keine Konfiguration');

        CamtReflectionParser::parse('<xml/>', CamtType::CAMT052);
    }
}
