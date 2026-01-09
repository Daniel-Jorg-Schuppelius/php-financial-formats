<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt102GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt102DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt102Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt102GeneratorTest extends BaseTestCase {
    public function testGenerateSimpleMessage(): void {
        $document = Mt102DocumentBuilder::create('BATCH-001')
            ->bankOperationCode('CRED')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->orderingCustomer('DE89370400440532013000', 'Test Company GmbH', 'COBADEFFXXX')
            ->beginTransaction('TX-001')
            ->amount(5000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE89370400440532013001', 'Max Mustermann')
            ->orderingCustomer('DE89370400440532013000', 'Test Company GmbH')
            ->remittanceInfo('Payment for Invoice 12345')
            ->done()
            ->build();

        $generator = new Mt102Generator($document);
        $message = $generator->generate($document);

        // Feldprüfungen - MT102 besteht aus Feldblöcken, keine SWIFT Envelope
        // Felder aus Sequence A (General Information)
        $this->assertStringContainsString(':20:BATCH-001', $message);
        $this->assertStringContainsString(':23:', $message);

        // Prüfen auf Transaktion (Sequence B)
        $this->assertStringContainsString(':21:TX-001', $message);
        $this->assertStringContainsString('EUR', $message);
    }

    public function testGenerateMultipleTransactions(): void {
        $document = Mt102DocumentBuilder::create('MULTI-BATCH')
            ->bankOperationCode('CRED')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->orderingCustomer('DE89370400440532013000', 'Auftraggeber', 'COBADEFFXXX')
            ->beginTransaction('TX-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE11520513735120710131', 'Empfänger 1')
            ->orderingCustomer('DE89370400440532013000', 'Auftraggeber')
            ->done()
            ->beginTransaction('TX-002')
            ->amount(2000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE21520513730231016700', 'Empfänger 2')
            ->orderingCustomer('DE89370400440532013000', 'Auftraggeber')
            ->done()
            ->build();

        $generator = new Mt102Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString(':21:TX-001', $message);
        $this->assertStringContainsString(':21:TX-002', $message);
    }

    public function testContainsSequenceCSummary(): void {
        $document = Mt102DocumentBuilder::create('SUM-TEST')
            ->bankOperationCode('CRED')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->orderingCustomer('DE89370400440532013000', 'Sender', 'COBADEFFXXX')
            ->beginTransaction('TX-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE11520513735120710131', 'Test')
            ->orderingCustomer('DE89370400440532013000', 'Sender')
            ->done()
            ->beginTransaction('TX-002')
            ->amount(2000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE21520513730231016700', 'Test 2')
            ->orderingCustomer('DE89370400440532013000', 'Sender')
            ->done()
            ->build();

        $generator = new Mt102Generator($document);
        $message = $generator->generate($document);

        // Prüfen auf Summary - Feld 32A enthält die Gesamtsumme (Sequence C)
        $this->assertStringContainsString(':32A:', $message);
    }

    public function testMessageBlockStructure(): void {
        $document = Mt102DocumentBuilder::create('BLOCK-TEST')
            ->bankOperationCode('CRED')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->currency(CurrencyCode::Euro)
            ->orderingCustomer('DE89370400440532013001', 'Sender', 'COBADEFFXXX')
            ->beginTransaction('TX-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
            ->beneficiary('DE89370400440532013000', 'Test')
            ->orderingCustomer('DE89370400440532013001', 'Sender')
            ->done()
            ->build();

        $generator = new Mt102Generator($document);
        $message = $generator->generate($document);

        // Nachricht sollte nicht leer sein und grundlegende Felder enthalten
        $this->assertNotEmpty($message);
        $this->assertStringContainsString(':20:', $message);
        $this->assertStringContainsString(':32A:', $message);
    }
}
