<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt9\Type950;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Transaction;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase {
    private function createBalance(CreditDebit $creditDebit, float $amount): Balance {
        return new Balance(
            $creditDebit,
            new DateTimeImmutable('2026-01-09'),
            CurrencyCode::Euro,
            $amount
        );
    }

    #[Test]
    public function constructorWithMinimalParameters(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertSame('DE89370400440532013000', $doc->getAccountId());
        $this->assertSame('REF-950', $doc->getReferenceId());
        $this->assertSame('001/001', $doc->getStatementNumber());
    }

    #[Test]
    public function getMtType(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertSame(MtType::MT950, $doc->getMtType());
    }

    #[Test]
    public function getOpeningBalance(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertSame($opening, $doc->getOpeningBalance());
    }

    #[Test]
    public function getClosingBalance(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertSame($closing, $doc->getClosingBalance());
    }

    #[Test]
    public function closingAvailableBalanceIsOptional(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertNull($doc->getClosingAvailableBalance());
    }

    #[Test]
    public function closingAvailableBalanceWhenProvided(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);
        $available = $this->createBalance(CreditDebit::CREDIT, 11000.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing,
            closingAvailableBalance: $available
        );

        $this->assertSame($available, $doc->getClosingAvailableBalance());
    }

    #[Test]
    public function getTransactionsEmpty(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertSame([], $doc->getTransactions());
    }

    #[Test]
    public function getCurrency(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertSame(CurrencyCode::Euro, $doc->getCurrency());
    }

    #[Test]
    public function relatedReferenceIsOptional(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing
        );

        $this->assertNull($doc->getRelatedReference());
    }

    #[Test]
    public function relatedReferenceWhenProvided(): void {
        $opening = $this->createBalance(CreditDebit::CREDIT, 10000.00);
        $closing = $this->createBalance(CreditDebit::CREDIT, 11500.00);

        $doc = new Document(
            accountId: 'DE89370400440532013000',
            referenceId: 'REF-950',
            statementNumber: '001/001',
            openingBalance: $opening,
            closingBalance: $closing,
            relatedReference: 'REL-REF-001'
        );

        $this->assertSame('REL-REF-001', $doc->getRelatedReference());
    }
}
