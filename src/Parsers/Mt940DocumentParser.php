<?php
/*
 * Created on   : Thu May 08 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940DocumentParser.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Parsers;

use CommonToolkit\FinancialFormats\Builders\Mt\Mt940DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Transaction;
use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class Mt940DocumentParser {
    public static function parse(string $rawBlock): Document {
        $lines = preg_split('/\r\n|\n|\r/', trim($rawBlock));
        $transactions = [];
        $accountId = null;
        $referenceId = 'COMMON';
        $relatedReference = null;
        $statementNumber = '00000';
        $openingBalance = null;
        $closingBalance = null;
        $closingAvailableBalance = null;
        $forwardAvailableBalances = [];
        $statementInfo = null;

        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            if (str_starts_with($line, ':20:')) {
                $referenceId = trim(substr($line, 4));
            } elseif (str_starts_with($line, ':21:')) {
                // :21: Related Reference - optional, references the MT 920 request if applicable
                $relatedReference = trim(substr($line, 4));
            } elseif (str_starts_with($line, ':25P:')) {
                // :25P: Account Identification with Party Identifier (SWIFT 2020+)
                $accountId = trim(substr($line, 5));
            } elseif (str_starts_with($line, ':25:')) {
                $accountId = trim(substr($line, 4));
            } elseif (str_starts_with($line, ':28C:') || str_starts_with($line, ':28:')) {
                // :28C: (with sequence) or :28: (without sequence) - Statement Number
                $offset = str_starts_with($line, ':28C:') ? 5 : 4;
                $statementNumber = trim(substr($line, $offset));
            } elseif (str_starts_with($line, ':60F:') || str_starts_with($line, ':60M:')) {
                // :60F: = First Opening Balance, :60M: = Intermediate Opening Balance
                $openingBalance = self::parseBalance(trim(substr($line, 5)));
            } elseif (str_starts_with($line, ':62F:') || str_starts_with($line, ':62M:')) {
                // :62F: = Final Closing Balance, :62M: = Intermediate Closing Balance
                $closingBalance = self::parseBalance(trim(substr($line, 5)));

                // Check for statement-level :86: after closing balance (and optional :64:/:65:)
                // This :86: applies to the statement as a whole, not to a transaction
                $j = $i + 1;
                while ($j < count($lines)) {
                    $nextLine = $lines[$j];
                    if (str_starts_with($nextLine, ':64:') || str_starts_with($nextLine, ':65:')) {
                        $j++; // Skip these, they are parsed separately
                        continue;
                    }
                    if (str_starts_with($nextLine, ':86:')) {
                        $statementInfoLines = [trim(substr($nextLine, 4))];
                        $j++;
                        // Collect continuation lines
                        while ($j < count($lines)) {
                            $contLine = $lines[$j];
                            if (preg_match('/^:\d{2}[A-Z]?:/', $contLine) || $contLine === '-' || $contLine === '-}') {
                                break;
                            }
                            $statementInfoLines[] = trim($contLine);
                            $j++;
                        }
                        $statementInfo = implode(' ', $statementInfoLines);
                        break;
                    }
                    break; // No :86: found at statement level
                }
            } elseif (str_starts_with($line, ':64:')) {
                // :64: = Closing Available Balance (Available Funds) - optional
                $closingAvailableBalance = self::parseBalance(trim(substr($line, 4)));
            } elseif (str_starts_with($line, ':65:')) {
                // :65: = Forward Available Balance - optional, can be repeated
                $forwardAvailableBalances[] = self::parseBalance(trim(substr($line, 4)));
            } elseif (str_starts_with($line, ':61:')) {
                $bookingLine = $line;
                $i++;

                // Subfield 9: Supplementary Details [34x] - optional, on next line (not starting with :XX:)
                $supplementaryDetails = null;
                if (isset($lines[$i]) && !preg_match('/^:\d{2}[A-Z]?:/', $lines[$i]) && !str_starts_with($lines[$i], '-')) {
                    // This line is supplementary details, not a field tag
                    $supplementaryDetails = trim($lines[$i]);
                    if (strlen($supplementaryDetails) > 34) {
                        $supplementaryDetails = substr($supplementaryDetails, 0, 34);
                    }
                    $i++;
                }

                $purposeLines = [];
                if (isset($lines[$i]) && str_starts_with($lines[$i], ':86:')) {
                    $purposeLines[] = trim(substr($lines[$i], 4));
                    $i++;

                    // MT940 :86: Field can span multiple lines (up to 6*65 chars per SWIFT spec)
                    // Continuation lines may start with:
                    // - '?' followed by 2 digits (DATEV/DFÜ structured format: ?00, ?20-?29, ?30, etc.)
                    // - '/' followed by code (SWIFT format: /EREF/, /REMI/, /ORDP/, /BENM/, etc.)
                    // - Any other character that is not a field tag (doesn't match :XX:)
                    while ($i < count($lines)) {
                        $nextLine = $lines[$i];
                        // Stop if we hit another MT940 field tag (:XX: format) or end of block
                        if (preg_match('/^:\d{2}[A-Z]?:/', $nextLine) || $nextLine === '-' || $nextLine === '-}') {
                            break;
                        }
                        $purposeLines[] = trim($nextLine);
                        $i++;
                    }
                }

                $purpose = implode(' ', $purposeLines);

                try {
                    // MT940 :61: Format per SWIFT Standard (us9m_20190719.pdf):
                    // Format: 6!n[4!n]2a[1!a]15d1!a3!c16x[//16x][34x]
                    // Subfields:
                    // 1. Value Date: 6!n (YYMMDD) - mandatory
                    // 2. Entry Date: [4!n] (MMDD) - optional
                    // 3. Debit/Credit Mark: 2a (C, D, RC, RD) - mandatory
                    // 4. Funds Code: [1!a] - optional (3rd char of currency code)
                    // 5. Amount: 15d - with comma, decimal places optional per SWIFT spec
                    // 6. Transaction Type: 1!a (S, N, F) + Identification Code: 3!c
                    // 7. Reference for Account Owner: 16x
                    // 8. Reference of Account Servicing Institution: [//16x] - optional
                    // 9. Supplementary Details: [34x] on next line - optional
                    //
                    // Example: :61:2201010101C100,00NTRFNONREF//123456
                    // Example with Funds Code: :61:090528D1,2FCHG494935/DEV//67914

                    // Extended regex supporting all SWIFT-compliant formats
                    if (preg_match('/^:61:(\d{6})(\d{4})?(R?[CD])([A-Z])?([0-9]+,?\d*)([SNF])([A-Z0-9]{3})([^\/]*?)(?:\/\/(.*))?$/i', $bookingLine, $match)) {
                        // Valutadatum ist immer das erste Datum (JJMMTT)
                        $valutaDate = DateTimeImmutable::createFromFormat('ymd', $match[1]) ?: throw new RuntimeException("Ungültiges Valutadatum");

                        // Buchungsdatum ist optional (MMTT), wenn vorhanden mit Jahr von Valuta ergänzen
                        $bookingDate = !empty($match[2])
                            ? DateTimeImmutable::createFromFormat('Ymd', $valutaDate->format('Y') . $match[2])
                            : $valutaDate; // Falls kein Buchungsdatum, verwende Valuta

                        if ($bookingDate === false) {
                            $bookingDate = $valutaDate;
                        }

                        $creditDebit = CreditDebit::fromMt940Code($match[3]);
                        // $match[4] wäre der optionale Währungsbuchstabe (letzte Stelle ISO-Code)
                        $amount = (float) CurrencyHelper::deToUs($match[5]);
                        $bookingKey = $match[6];
                        $transactionCode = $match[7]; // 3-stelliger Code
                        $reference = trim($match[8] ?? '');
                        $bankReference = trim($match[9] ?? '');
                        if (strlen($reference) > 16) {
                            $reference = substr($reference, 0, 16);
                        }
                        if (strlen($bankReference) > 16) {
                            $bankReference = substr($bankReference, 0, 16);
                        }

                        $transactions[] = new Transaction(
                            bookingDate: $bookingDate,
                            valutaDate: $valutaDate,
                            amount: $amount,
                            creditDebit: $creditDebit,
                            currency: $openingBalance?->getCurrency() ?? CurrencyCode::Euro,
                            reference: new Reference($transactionCode, $reference ?: 'NONREF', $bankReference ?: null, $bookingKey),
                            purpose: $purpose,
                            supplementaryDetails: $supplementaryDetails
                        );
                    }
                } catch (Throwable $e) {
                    // Logging optional
                }

                continue;
            }

            $i++;
        }

        if (!$accountId || !$openingBalance || !$closingBalance) {
            throw new RuntimeException("Fehlende Pflichtinformationen im MT940-Block");
        }

        // When parsing real-world bank data, skip balance validation as there may be
        // rounding differences, fees, or intermediate transactions not visible in the statement
        $builder = (new Mt940DocumentBuilder())
            ->setAccountId($accountId)
            ->setReferenceId($referenceId)
            ->setStatementNumber($statementNumber)
            ->setOpeningBalance($openingBalance)
            ->setClosingBalance($closingBalance)
            ->addTransactions($transactions)
            ->skipBalanceValidation();

        // Optional: Related Reference (:21:)
        if ($relatedReference !== null) {
            $builder = $builder->setRelatedReference($relatedReference);
        }

        // Optional: Closing Available Balance (:64:)
        if ($closingAvailableBalance !== null) {
            $builder = $builder->setClosingAvailableBalance($closingAvailableBalance);
        }

        // Optional: Forward Available Balance (:65:) - can be multiple
        if (!empty($forwardAvailableBalances)) {
            $builder = $builder->setForwardAvailableBalances($forwardAvailableBalances);
        }

        // Optional: Statement-Level Information (:86: after :62:)
        if ($statementInfo !== null) {
            $builder = $builder->setStatementInfo($statementInfo);
        }

        return $builder->build();
    }

    private static function parseBalance(string $raw): Balance {
        // SWIFT MT940 Balance Format: 1!a6!n3!a15d
        // D/C Mark (1 char) + Date YYMMDD (6 digits) + Currency (3 chars) + Amount (up to 15 chars with comma)
        // Note: Decimal comma is mandatory per SWIFT spec, but decimal places are optional (e.g., "100," is valid)
        if (!preg_match('/^([CD])(\d{6})([A-Z]{3})([0-9]+,?\d*)$/', $raw, $matches)) {
            throw new RuntimeException("Balance-String ungültig: $raw");
        }

        return new Balance(
            creditDebit: CreditDebit::fromMt940Code($matches[1]),
            date: DateTimeImmutable::createFromFormat('ymd', $matches[2]) ?: throw new RuntimeException("Datum ungültig"),
            currency: CurrencyCode::tryFrom($matches[3]) ?? throw new RuntimeException("Währung ungültig: {$matches[3]}"),
            amount: (float) str_replace(',', '.', $matches[4])
        );
    }
}
