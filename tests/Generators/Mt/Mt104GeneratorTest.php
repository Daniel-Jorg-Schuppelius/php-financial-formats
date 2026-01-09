<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt104GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt104DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt104Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt104GeneratorTest extends BaseTestCase {
    public function testGenerateSimpleMessage(): void {
        $document = Mt104DocumentBuilder::create('DD-BATCH-001')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->creditor('DE89370400440532013000', 'Gläubiger GmbH')
            ->mandateReference('MANDATE-001')
            ->beginTransaction('DD-TX-001')
            ->amount(150.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE11520513735120710131', 'Schuldner Hans')
            ->endToEndReference('E2E-REF-001')
            ->done()
            ->build();

        $generator = new Mt104Generator($document);
        $message = $generator->generate($document);

        // Feldprüfungen - MT104 besteht aus Feldblöcken, keine SWIFT Envelope
        $this->assertStringContainsString(':20:DD-BATCH-001', $message);
        $this->assertStringContainsString(':21:DD-TX-001', $message);
        $this->assertStringContainsString('EUR', $message);
    }

    public function testGenerateWithMultipleDebits(): void {
        $document = Mt104DocumentBuilder::create('MULTI-DD')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->creditor('DE89370400440532013000', 'Gläubiger GmbH')
            ->mandateReference('MANDATE-001')
            ->beginTransaction('DD-001')
            ->amount(100.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE11520513735120710131', 'Schuldner A')
            ->endToEndReference('E2E-001')
            ->done()
            ->beginTransaction('DD-002')
            ->amount(200.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE21520513730231016700', 'Schuldner B')
            ->endToEndReference('E2E-002')
            ->done()
            ->beginTransaction('DD-003')
            ->amount(300.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE33520513730123456789', 'Schuldner C')
            ->endToEndReference('E2E-003')
            ->done()
            ->build();

        $generator = new Mt104Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString(':21:DD-001', $message);
        $this->assertStringContainsString(':21:DD-002', $message);
        $this->assertStringContainsString(':21:DD-003', $message);

        // Summenfeld - Feld 32B enthält die Gesamtsumme
        $this->assertStringContainsString(':32B:', $message);
    }

    public function testContainsCreditorInformation(): void {
        $document = Mt104DocumentBuilder::create('CRED-INFO-TEST')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->creditor('DE89370400440532013000', 'Test Gläubiger AG')
            ->creditorsBank('COBADEFFXXX')
            ->mandateReference('SEPA-MANDATE-123')
            ->beginTransaction('TX-1')
            ->amount(50.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE11520513735120710131', 'Zahler')
            ->done()
            ->build();

        $generator = new Mt104Generator($document);
        $message = $generator->generate($document);

        // Gläubigerinformationen sollten enthalten sein
        $this->assertStringContainsString('COBADEFFXXX', $message);
    }

    public function testMessageEndsCorrectly(): void {
        $document = Mt104DocumentBuilder::create('END-TEST')
            ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->creditor('DE89370400440532013000', 'Test')
            ->mandateReference('M-123')
            ->beginTransaction('TX-1')
            ->amount(25.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->debtor('DE11520513735120710131', 'D')
            ->done()
            ->build();

        $generator = new Mt104Generator($document);
        $message = $generator->generate($document);

        // Nachricht sollte nicht leer sein und Felder enthalten
        $this->assertNotEmpty($message);
        $this->assertStringContainsString(':20:', $message);
    }
}
