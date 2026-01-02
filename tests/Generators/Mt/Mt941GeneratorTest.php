<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt941GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type941\Document;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt941Generator;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt941GeneratorTest extends BaseTestCase {
    private Mt941Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Mt941Generator();
    }

    private function createOpeningBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-14'),
            currency: CurrencyCode::Euro,
            amount: 10000.00,
            type: 'F'
        );
    }

    private function createClosingBalance(): Balance {
        return new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00,
            type: 'F'
        );
    }

    public function testGenerateBasicDocument(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-941',
            statementNumber: '001',
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $output = $this->generator->generate($document);

        $this->assertNotEmpty($output);
        $this->assertStringContainsString(':20:', $output);
        $this->assertStringContainsString(':25:', $output);
    }

    public function testGenerateContainsAccountId(): void {
        $document = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-941',
            statementNumber: '001',
            openingBalance: $this->createOpeningBalance(),
            closingBalance: $this->createClosingBalance()
        );

        $output = $this->generator->generate($document);

        $this->assertStringContainsString('DE89370400440532013000', $output);
    }

    public function testGenerateThrowsExceptionForWrongDocumentType(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Mt9\Type941\Document');

        $wrongDocument = $this->createMock(\CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract::class);

        $this->generator->generate($wrongDocument);
    }
}
