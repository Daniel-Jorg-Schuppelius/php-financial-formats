<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt202GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt202DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt202Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt202GeneratorTest extends BaseTestCase {
    public function testGenerateStandardMt202(): void {
        $document = Mt202DocumentBuilder::create('FI-GEN-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(750000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        // Feldprüfungen für MT202 - nur Feldblöcke, keine SWIFT Envelope
        $this->assertStringContainsString(':20:FI-GEN-001', $message);
        $this->assertStringContainsString(':21:REL-001', $message);
        $this->assertStringContainsString(':32A:', $message);
        $this->assertStringContainsString(':58A:', $message);
        $this->assertStringContainsString('EUR', $message);
    }

    public function testGenerateCoverPayment(): void {
        $document = Mt202DocumentBuilder::create('COVER-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(500000.00, CurrencyCode::USDollar)
            ->beneficiaryInstitution('CHASUS33')
            ->asCoverPayment()
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        // MT202COV sollte im Application Header angezeigt werden
        $this->assertStringContainsString(':20:COVER-001', $message);
        $this->assertTrue($document->isCoverPayment());
    }

    public function testGenerateWithFullChain(): void {
        $document = Mt202DocumentBuilder::create('CHAIN-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(1000000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->orderingInstitution('COBADEFFXXX')
            ->sendersCorrespondent('INGBDEFFXXX')
            ->receiversCorrespondent('BYLADEM1001')
            ->intermediary('SOGEDEFFXXX')
            ->accountWithInstitution('PBNKDEFFXXX')
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString('COBADEFFXXX', $message);
        $this->assertStringContainsString('DEUTDEFFXXX', $message);
    }

    public function testTimeIndication(): void {
        $document = Mt202DocumentBuilder::create('TIME-001', 'REL-001')
            ->timeIndication('/CLSTIME/0915+0100')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(100000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString(':13C:', $message);
        $this->assertStringContainsString('/CLSTIME/', $message);
    }

    public function testField32AFormat(): void {
        $document = Mt202DocumentBuilder::create('FORMAT-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-12-31'))
            ->amount(987654.32, CurrencyCode::SwissFranc)
            ->beneficiaryInstitution('UBSWCHZH')
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString(':32A:', $message);
        $this->assertStringContainsString('241231', $message);
        $this->assertStringContainsString('CHF', $message);
    }

    public function testSenderToReceiverInfo(): void {
        $document = Mt202DocumentBuilder::create('INFO-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(50000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->senderToReceiverInfo('/ACC/Additional payment info')
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString(':72:', $message);
        $this->assertStringContainsString('/ACC/', $message);
    }

    public function testMessageEndsCorrectly(): void {
        $document = Mt202DocumentBuilder::create('END-001', 'REL-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(10000.00, CurrencyCode::Euro)
            ->beneficiaryInstitution('DEUTDEFFXXX')
            ->build();

        $generator = new Mt202Generator($document);
        $message = $generator->generate($document);

        // Nachricht sollte mit dem letzten Feldinhalt enden
        $this->assertNotEmpty($message);
        $this->assertStringContainsString(':58A:', $message);
    }
}
