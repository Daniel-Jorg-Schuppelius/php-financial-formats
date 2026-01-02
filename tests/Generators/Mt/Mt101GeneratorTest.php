<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt101GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt101DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt101Generator;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt101GeneratorTest extends BaseTestCase {
    private function createBasicDocument(): Document {
        return Mt101DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();
    }

    public function testGenerateBasicDocument(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt101Generator();

        $output = $generator->generate($document);

        $this->assertNotEmpty($output);
        $this->assertIsString($output);
    }

    public function testGenerateContainsSendersReference(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt101Generator();

        $output = $generator->generate($document);

        $this->assertStringContainsString(':20:REF-001', $output);
    }

    public function testGenerateContainsMessageIndex(): void {
        $document = Mt101DocumentBuilder::create('REF-002')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->messageIndex(1, 2)
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-15'))
            ->beginTransaction('TXN-001')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->done()
            ->build();

        $generator = new Mt101Generator();
        $output = $generator->generate($document);

        $this->assertStringContainsString(':28D:', $output);
    }

    public function testGenerateContainsOrderingCustomer(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt101Generator();

        $output = $generator->generate($document);

        // :50H: oder :50K: für Ordering Customer
        $this->assertTrue(
            str_contains($output, ':50H:') || str_contains($output, ':50K:'),
            'Output should contain :50H: or :50K: for ordering customer'
        );
    }

    public function testGenerateContainsRequestedExecutionDate(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt101Generator();

        $output = $generator->generate($document);

        $this->assertStringContainsString(':30:', $output);
    }

    public function testGenerateWithMultipleTransactions(): void {
        $document = Mt101DocumentBuilder::create('REF-003')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->requestedExecutionDate(new DateTimeImmutable('2025-03-20'))
            ->beginTransaction('TXN-001')
            ->amount(100.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-20'))
            ->beneficiary('DE11111111111111111111', 'Empfänger 1')
            ->done()
            ->beginTransaction('TXN-002')
            ->amount(200.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-20'))
            ->beneficiary('DE22222222222222222222', 'Empfänger 2')
            ->done()
            ->build();

        $generator = new Mt101Generator();
        $output = $generator->generate($document);

        // Prüfe auf Transaction-Felder
        $this->assertStringContainsString('TXN-001', $output);
        $this->assertStringContainsString('TXN-002', $output);
    }

    public function testGenerateThrowsExceptionForWrongDocumentType(): void {
        $generator = new Mt101Generator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Mt1\Type101\Document');

        $generator->generate(new \stdClass());
    }
}
