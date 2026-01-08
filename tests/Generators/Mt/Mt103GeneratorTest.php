<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt103GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt103DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type103\Document;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt103Generator;
use CommonToolkit\FinancialFormats\Enums\Mt\BankOperationCode;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\Contracts\BaseTestCase;

class Mt103GeneratorTest extends BaseTestCase {
    private function createBasicDocument(): Document {
        return Mt103DocumentBuilder::create('REF-001')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->build();
    }

    public function testGenerateBasicDocument(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt103Generator();

        $output = $generator->generate($document);

        $this->assertNotEmpty($output);
        $this->assertIsString($output);
    }

    public function testGenerateContainsSendersReference(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt103Generator();

        $output = $generator->generate($document);

        $this->assertStringContainsString(':20:REF-001', $output);
    }

    public function testGenerateContainsBankOperationCode(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt103Generator();

        $output = $generator->generate($document);

        $this->assertStringContainsString(':23B:CRED', $output);
    }

    public function testGenerateContainsValueDateAndAmount(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt103Generator();

        $output = $generator->generate($document);

        // :32A: enthält Value Date, Currency, Amount
        $this->assertStringContainsString(':32A:', $output);
    }

    public function testGenerateContainsOrderingCustomer(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt103Generator();

        $output = $generator->generate($document);

        $this->assertStringContainsString(':50K:', $output);
        $this->assertStringContainsString('Firma GmbH', $output);
    }

    public function testGenerateContainsBeneficiary(): void {
        $document = $this->createBasicDocument();
        $generator = new Mt103Generator();

        $output = $generator->generate($document);

        $this->assertStringContainsString(':59:', $output);
        $this->assertStringContainsString('Max Mustermann', $output);
    }

    public function testGenerateWithChargesCode(): void {
        $document = Mt103DocumentBuilder::create('REF-002')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(500.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->chargesShared()
            ->build();

        $generator = new Mt103Generator();
        $output = $generator->generate($document);

        $this->assertStringContainsString(':71A:SHA', $output);
    }

    public function testGenerateWithRemittanceInfo(): void {
        $document = Mt103DocumentBuilder::create('REF-003')
            ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
            ->beneficiary('DE91100000000123456789', 'Max Mustermann')
            ->amount(750.00, CurrencyCode::Euro, new DateTimeImmutable('2025-03-15'))
            ->remittanceInfo('Rechnung Nr. 12345')
            ->build();

        $generator = new Mt103Generator();
        $output = $generator->generate($document);

        $this->assertStringContainsString(':70:', $output);
        $this->assertStringContainsString('Rechnung Nr. 12345', $output);
    }

    public function testGenerateThrowsExceptionForWrongDocumentType(): void {
        $generator = new Mt103Generator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Mt1\Type103\Document');

        $generator->generate(new \stdClass());
    }
}
