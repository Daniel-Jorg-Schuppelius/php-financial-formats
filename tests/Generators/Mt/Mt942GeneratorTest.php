<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt942GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type942\Document;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt942Generator;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt942GeneratorTest extends BaseTestCase {
    private Mt942Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Mt942Generator();
    }

    private function createInterimOpeningBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10000.00,
            type: 'M' // Interim
        );
    }

    private function createInterimClosingBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10250.00,
            type: 'M' // Interim
        );
    }

    public function testGenerateBasicDocument(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-942',
            statementNumber: '001',
            openingBalance: $this->createInterimOpeningBalance(),
            closingBalance: $this->createInterimClosingBalance()
        );

        $output = $this->generator->generate($document);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString(':20:', $output);
        $this->assertStringContainsString(':25:', $output);
    }

    public function testGenerateContainsInterimBalanceFields(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-942',
            statementNumber: '001',
            openingBalance: $this->createInterimOpeningBalance(),
            closingBalance: $this->createInterimClosingBalance()
        );

        $output = $this->generator->generate($document);

        // MT942 nutzt :60M: und :62M: für Interim Balances
        $this->assertStringContainsString(':60M:', $output);
        $this->assertStringContainsString(':62M:', $output);
    }

    public function testGenerateThrowsExceptionForWrongDocumentType(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Mt9\Type942\Document');

        $wrongDocument = $this->createMock(\CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract::class);

        $this->generator->generate($wrongDocument);
    }
}
