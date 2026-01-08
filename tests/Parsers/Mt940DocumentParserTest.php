<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940DocumentParserTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Parsers;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document;
use CommonToolkit\FinancialFormats\Parsers\Mt940DocumentParser;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use Tests\Contracts\BaseTestCase;

class Mt940DocumentParserTest extends BaseTestCase {
    private function getBasicMt940Statement(): string {
        // Hinweis: Opening und Closing Balance müssen das gleiche Datum haben,
        // da der Builder bei Vergleich das Opening-Datum für den berechneten Closing-Saldo verwendet
        return <<<'MT940'
:20:STMT-2025-001
:25:DE89370400440532013000
:28C:001/001
:60F:C250115EUR10000,00
:61:2501150115C500,00NTRFREF123//BANKREF456
:86:020?00Überweisung?20Test Verwendungszweck?21Zeile 2
:62F:C250115EUR10500,00
-}
MT940;
    }

    public function testParseBasicStatement(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);

        $this->assertInstanceOf(Document::class, $document);
    }

    public function testParseExtractsAccountId(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);

        $this->assertSame('DE89370400440532013000', $document->getAccountId());
    }

    public function testParseExtractsReferenceId(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);

        $this->assertSame('STMT-2025-001', $document->getReferenceId());
    }

    public function testParseExtractsStatementNumber(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);

        $this->assertSame('001/001', $document->getStatementNumber());
    }

    public function testParseExtractsOpeningBalance(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);
        $balance = $document->getOpeningBalance();

        $this->assertNotNull($balance);
        $this->assertSame(CreditDebit::CREDIT, $balance->getCreditDebit());
        $this->assertSame(10000.00, $balance->getAmount());
        $this->assertSame(CurrencyCode::Euro, $balance->getCurrency());
    }

    public function testParseExtractsClosingBalance(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);
        $balance = $document->getClosingBalance();

        $this->assertNotNull($balance);
        $this->assertSame(CreditDebit::CREDIT, $balance->getCreditDebit());
        $this->assertSame(10500.00, $balance->getAmount());
    }

    public function testParseExtractsTransactions(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);
        $transactions = $document->getTransactions();

        $this->assertCount(1, $transactions);
    }

    public function testParseTransactionHasCorrectAmount(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        $this->assertSame(500.00, $transaction->getAmount());
    }

    public function testParseTransactionHasCorrectCreditDebit(): void {
        $mt940 = $this->getBasicMt940Statement();

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        $this->assertSame(CreditDebit::CREDIT, $transaction->getCreditDebit());
    }

    public function testParseDebitTransaction(): void {
        $mt940 = <<<'MT940'
:20:STMT-2025-002
:25:DE89370400440532013000
:28C:002/001
:60F:C250115EUR10000,00
:61:2501150115D250,00NTRFREF789
:86:020?00Lastschrift
:62F:C250115EUR9750,00
-}
MT940;

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        $this->assertSame(CreditDebit::DEBIT, $transaction->getCreditDebit());
        $this->assertSame(250.00, $transaction->getAmount());
    }

    public function testParseMultipleTransactions(): void {
        $mt940 = <<<'MT940'
:20:STMT-2025-003
:25:DE89370400440532013000
:28C:003/001
:60F:C250115EUR10000,00
:61:2501150115C100,00NTRFREF001
:86:020?00Einzahlung 1
:61:2501150115C200,00NTRFREF002
:86:020?00Einzahlung 2
:61:2501150115D50,00NTRFREF003
:86:020?00Auszahlung
:62F:C250115EUR10250,00
-}
MT940;

        $document = Mt940DocumentParser::parse($mt940);

        // Der Builder prüft die Salden, daher können wir nur die Transaktionsanzahl testen
        // wenn die Salden korrekt sind (10000 + 100 + 200 - 50 = 10250)
        $this->assertCount(3, $document->getTransactions());
    }

    public function testParseWithReversalCreditDebit(): void {
        // Hinweis: In der aktuellen Implementierung wird RC (Reversal Credit) als CREDIT behandelt.
        // Das bedeutet: RC = Storno einer Gutschrift, aber im MT940-Parsing als "Gutschrift" gewertet.
        // Der Builder addiert daher den Betrag zum Saldo (10000 + 500 = 10500).
        // Dies entspricht der aktuellen CreditDebit::fromMt940Code() Implementierung.
        $mt940 = <<<'MT940'
:20:STMT-2025-004
:25:DE89370400440532013000
:28C:004/001
:60F:C250115EUR10000,00
:61:2501150115RC500,00NTRFSTORNO
:86:020?00Storno Gutschrift
:62F:C250115EUR10500,00
-}
MT940;

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        // Aktuelle Implementierung: RC wird als CREDIT interpretiert
        $this->assertSame(CreditDebit::CREDIT, $transaction->getCreditDebit());
    }

    public function testParseWithDebitReversalDR(): void {
        // DR (Debit Reversal) = Storno einer Lastschrift
        // Format häufig bei deutschen Banken: :61:JJMMTTMMDDR...
        $mt940 = <<<'MT940'
:20:STMT-2025-005
:25:DE89370400440532013000
:28C:005/001
:60F:C210701EUR1000,00
:61:2107010701DR52,50NMSCNONREF
:86:999?00Storno Lastschrift
:62F:C210701EUR947,50
-}
MT940;

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        // DR wird als DEBIT interpretiert (Lastschrift-Storno)
        $this->assertSame(CreditDebit::DEBIT, $transaction->getCreditDebit());
        $this->assertTrue($transaction->isReversal());
        $this->assertSame('RD', $transaction->getMt940DirectionCode());
        $this->assertEquals(52.50, $transaction->getAmount());
        $this->assertSame('MSC', $transaction->getReference()->getTransactionCode());
    }

    public function testParseWithCreditReversalCR(): void {
        // CR (Credit Reversal) = Storno einer Gutschrift
        // Alternative Notation zu RC
        $mt940 = <<<'MT940'
:20:STMT-2025-006
:25:DE89370400440532013000
:28C:006/001
:60F:C210801EUR2000,00
:61:2108010801CR150,00NTRF123456
:86:020?00Storno Eingang
:62F:C210801EUR2150,00
-}
MT940;

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        // CR wird als CREDIT interpretiert (Gutschrift-Storno)
        $this->assertSame(CreditDebit::CREDIT, $transaction->getCreditDebit());
        $this->assertTrue($transaction->isReversal());
        $this->assertSame('RC', $transaction->getMt940DirectionCode());
        $this->assertEquals(150.00, $transaction->getAmount());
    }

    public function testNonReversalTransactionHasIsReversalFalse(): void {
        $mt940 = <<<'MT940'
:20:STMT-2025-007
:25:DE89370400440532013000
:28C:007/001
:60F:C210901EUR5000,00
:61:2109010901C200,00NTRFEINGANG
:86:051?00Eingang
:62F:C210901EUR5200,00
-}
MT940;

        $document = Mt940DocumentParser::parse($mt940);
        $transaction = $document->getTransactions()[0];

        // Normale Transaktion ohne R-Prefix
        $this->assertSame(CreditDebit::CREDIT, $transaction->getCreditDebit());
        $this->assertFalse($transaction->isReversal());
        $this->assertSame('C', $transaction->getMt940DirectionCode());
    }
}
