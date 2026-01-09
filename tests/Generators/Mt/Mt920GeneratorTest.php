<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt920GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt920DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt920Generator;
use Tests\Contracts\BaseTestCase;

class Mt920GeneratorTest extends BaseTestCase {
    public function testGenerateSimpleMessage(): void {
        $document = Mt920DocumentBuilder::create('REQ-001')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->build();

        $generator = new Mt920Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':20:REQ-001', $message);
        $this->assertStringContainsString(':12:940', $message);
        $this->assertStringContainsString(':25:DE89370400440532013000', $message);
    }

    public function testGenerateWithFloorLimit(): void {
        $document = Mt920DocumentBuilder::create('REQ-002')
            ->account('GB33GSLD04296852369741')
            ->requestMt942()
            ->floorLimit('GBP', 500.00, 'D')
            ->build();

        $generator = new Mt920Generator();
        $message = $generator->generate($document);

        $this->assertStringContainsString(':12:942', $message);
        $this->assertStringContainsString(':34F:GBPD500,00', $message);
    }

    public function testGenerateForDifferentMessageTypes(): void {
        $generator = new Mt920Generator();

        // MT941
        $doc941 = Mt920DocumentBuilder::create('REQ-941')
            ->account('DE89370400440532013000')
            ->requestMt941()
            ->build();
        $this->assertStringContainsString(':12:941', $generator->generate($doc941));

        // MT950
        $doc950 = Mt920DocumentBuilder::create('REQ-950')
            ->account('DE89370400440532013000')
            ->requestMt950()
            ->build();
        $this->assertStringContainsString(':12:950', $generator->generate($doc950));
    }

    public function testMessageEndsWithMarker(): void {
        $document = Mt920DocumentBuilder::create('END-TEST')
            ->account('DE89370400440532013000')
            ->requestMt940()
            ->build();

        $generator = new Mt920Generator();
        $message = $generator->generate($document);

        $this->assertStringEndsWith('-', trim($message));
    }
}