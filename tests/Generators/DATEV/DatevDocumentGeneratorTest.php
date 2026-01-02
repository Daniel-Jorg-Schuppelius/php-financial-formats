<?php
/*
 * Created on   : Fri Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DatevDocumentGeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\DATEV;

use CommonToolkit\FinancialFormats\Builders\DATEV\V700\BookingDocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\DATEV\DatevDocumentGenerator;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für den DatevDocumentGenerator.
 */
class DatevDocumentGeneratorTest extends BaseTestCase {
    private DatevDocumentGenerator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new DatevDocumentGenerator();
    }

    public function testGenerateProducesValidOutput(): void {
        $builder = new BookingDocumentBuilder();
        $builder->setMetaHeader();
        $builder->setFieldHeader();
        $document = $builder->build();

        $output = $this->generator->generate($document);

        // MetaHeader sollte EXTF enthalten
        $this->assertStringContainsString('EXTF', $output);

        // Sollte Semikolon als Trennzeichen verwenden
        $this->assertStringContainsString(';', $output);
    }

    public function testGenerateWithCustomDelimiter(): void {
        $builder = new BookingDocumentBuilder();
        $builder->setMetaHeader();
        $builder->setFieldHeader();
        $document = $builder->build();

        $output = $this->generator->generate($document, '|');

        // Sollte Pipe als Trennzeichen verwenden
        $this->assertStringContainsString('|', $output);
    }

    public function testGeneratorMatchesToString(): void {
        $builder = new BookingDocumentBuilder();
        $builder->setMetaHeader();
        $builder->setFieldHeader();
        $document = $builder->build();

        // Generator output should match toString since toString uses generator
        $generatorOutput = $this->generator->generate($document);
        $toStringOutput = $document->toString();

        $this->assertEquals($generatorOutput, $toStringOutput);
    }

    public function testGenerateIncludesMetaHeader(): void {
        $builder = new BookingDocumentBuilder();
        $builder->setMetaHeader();
        $builder->setFieldHeader();
        $document = $builder->build();

        $output = $this->generator->generate($document);
        $lines = explode("\n", $output);

        // Erste Zeile sollte MetaHeader sein (beginnt mit "EXTF")
        $this->assertStringContainsString('EXTF', $lines[0]);
    }

    public function testGenerateIncludesHeader(): void {
        $builder = new BookingDocumentBuilder();
        $builder->setMetaHeader();
        $builder->setFieldHeader();
        $document = $builder->build();

        $output = $this->generator->generate($document);
        $lines = explode("\n", $output);

        // Zweite Zeile sollte Header sein (enthält Feldnamen)
        $this->assertGreaterThanOrEqual(2, count($lines));
        // BookingBatch Header enthält "Umsatz"
        $this->assertStringContainsString('Umsatz', $lines[1]);
    }
}
