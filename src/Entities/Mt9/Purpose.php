<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Purpose.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt9;

use CommonToolkit\FinancialFormats\Enums\Mt\GvcCode;
use CommonToolkit\FinancialFormats\Enums\Mt\Mt940OutputFormat;
use CommonToolkit\FinancialFormats\Enums\Mt\PurposeCode;
use CommonToolkit\FinancialFormats\Enums\Mt\SepaKeyword;
use CommonToolkit\FinancialFormats\Enums\Mt\SwiftKeyword;
use CommonToolkit\FinancialFormats\Enums\Mt\TextKeyExtension;

/**
 * Represents the :86: information to account owner of an MT9xx transaction.
 *
 * Supports three formats:
 * 1. SWIFT MT940 narrative (6*65x) - unstructured text
 * 2. DATEV/DFÜ structured fields (?00, ?10, ?20-?29, ?30-?34, ?60-?63)
 * 3. SWIFT structured keywords (/EREF/, /REMI/, /ORDP/, /BENM/, etc.)
 *
 * @package CommonToolkit\Entities\Common\Banking\Mt9
 */
class Purpose {
    private const SWIFT_LINE_LENGTH = 65;
    private const SWIFT_MAX_LINES = 6;

    // =========================================================================
    // DATEV/DFÜ Structured Fields (?xx format)
    // =========================================================================
    private ?GvcCode $gvcCode;               // Geschäftsvorfall-Code (3 Stellen)
    private ?string $bookingText;            // ?00 Buchungstext
    private ?string $primanotenNr;           // ?10 Primanoten-Nr.
    private array $purposeLines;             // ?20-?29, ?60-?63 Verwendungszweck
    private ?string $payerBlz;               // ?30 BLZ/BIC
    private ?string $payerAccount;           // ?31 Kontonummer/IBAN
    private ?string $payerName1;             // ?32 Name Zeile 1
    private ?string $payerName2;             // ?33 Name Zeile 2
    private ?TextKeyExtension $textKeyExt;   // ?34 Textschlüssel-Ergänzung

    // =========================================================================
    // SWIFT Structured Keywords (/XXX/ format)
    // Per mt940-details.pdf and SWIFT standard
    // =========================================================================
    private ?string $endToEndReference;      // /EREF/ - End-to-End Reference (35x)
    private ?string $paymentInfoId;          // /PREF/ - Payment Information ID (35x)
    private ?string $instructionId;          // /IREF/ - Instruction ID (35x)
    private ?string $mandateReference;       // /MREF/ - Mandate Reference (35x)
    private ?string $creditorId;             // /CRED/ - Creditor Identifier (35x)
    private ?string $remittanceInfo;         // /REMI/ - Remittance Information (140x)
    private ?string $beneficiaryName;        // /BENM/NAME/ - Beneficiary Name (70x)
    private ?string $orderingPartyName;      // /ORDP/NAME/ - Ordering Party Name (70x)
    private ?string $ultimateDebtorName;     // /ULTD/NAME/ - Ultimate Debtor Name (70x)
    private ?string $ultimateCreditorName;   // /ULTC/NAME/ - Ultimate Creditor Name (70x)
    private ?string $beneficiaryAccount;     // /INFO/ - Beneficiary Account (34x)
    private ?string $beneficiaryBank;        // /BBK/ - Beneficiary Bank BIC (35x)
    private ?string $orderingBank;           // /OBK/ - Ordering Bank BIC (35x)
    private ?string $originalAmount;         // /OCMT/ - Original Currency/Amount (3!a15d)
    private ?string $charges;                // /CHGS/ - Charges (3!a15d)
    private ?string $exchangeRate;           // /EXCH/ - Exchange Rate (12d)
    private ?PurposeCode $purposeCode;       // /PURP/CD/ - Purpose Code (4a)
    private ?string $returnReason;           // /RTRN/ - Return Reason Code (4a)
    private ?string $transactionReference;   // /TR/ - Transaction Reference (17x)
    private ?string $virtualAccount;         // /VACC/ - Virtual Account (34x)
    private ?string $numberOfTransactions;   // /NBTR/ - Number of Transactions (10!n)
    private ?string $typeCode;               // /TYPE/ - Accounting Entry Type (3!n/10x)
    private ?string $localCode;              // /CODE/ - Local Operation Code (2a/3n)
    private ?string $urgencyPriority;        // /URGP/ - Urgency/Priority

    // Raw text fallback
    private ?string $rawText;

    public function __construct(
        ?GvcCode $gvcCode = null,
        ?string $bookingText = null,
        ?string $primanotenNr = null,
        array $purposeLines = [],
        ?string $payerBlz = null,
        ?string $payerAccount = null,
        ?string $payerName1 = null,
        ?string $payerName2 = null,
        ?TextKeyExtension $textKeyExt = null,
        ?string $rawText = null,
        // SWIFT Keywords
        ?string $endToEndReference = null,
        ?string $paymentInfoId = null,
        ?string $instructionId = null,
        ?string $mandateReference = null,
        ?string $creditorId = null,
        ?string $remittanceInfo = null,
        ?string $beneficiaryName = null,
        ?string $orderingPartyName = null,
        ?string $ultimateDebtorName = null,
        ?string $ultimateCreditorName = null,
        ?string $beneficiaryAccount = null,
        ?string $beneficiaryBank = null,
        ?string $orderingBank = null,
        ?string $originalAmount = null,
        ?string $charges = null,
        ?string $exchangeRate = null,
        ?PurposeCode $purposeCode = null,
        ?string $returnReason = null,
        ?string $transactionReference = null,
        ?string $virtualAccount = null,
        ?string $numberOfTransactions = null,
        ?string $typeCode = null,
        ?string $localCode = null,
        ?string $urgencyPriority = null
    ) {
        // DATEV fields
        $this->gvcCode = $gvcCode;
        $this->bookingText = $bookingText;
        $this->primanotenNr = $primanotenNr;
        $this->purposeLines = $purposeLines;
        $this->payerBlz = $payerBlz;
        $this->payerAccount = $payerAccount;
        $this->payerName1 = $payerName1;
        $this->payerName2 = $payerName2;
        $this->textKeyExt = $textKeyExt;
        $this->rawText = $rawText;

        // SWIFT Keywords
        $this->endToEndReference = $endToEndReference;
        $this->paymentInfoId = $paymentInfoId;
        $this->instructionId = $instructionId;
        $this->mandateReference = $mandateReference;
        $this->creditorId = $creditorId;
        $this->remittanceInfo = $remittanceInfo;
        $this->beneficiaryName = $beneficiaryName;
        $this->orderingPartyName = $orderingPartyName;
        $this->ultimateDebtorName = $ultimateDebtorName;
        $this->ultimateCreditorName = $ultimateCreditorName;
        $this->beneficiaryAccount = $beneficiaryAccount;
        $this->beneficiaryBank = $beneficiaryBank;
        $this->orderingBank = $orderingBank;
        $this->originalAmount = $originalAmount;
        $this->charges = $charges;
        $this->exchangeRate = $exchangeRate;
        $this->purposeCode = $purposeCode;
        $this->returnReason = $returnReason;
        $this->transactionReference = $transactionReference;
        $this->virtualAccount = $virtualAccount;
        $this->numberOfTransactions = $numberOfTransactions;
        $this->typeCode = $typeCode;
        $this->localCode = $localCode;
        $this->urgencyPriority = $urgencyPriority;
    }

    /**
     * Parses a structured :86: field.
     * Supports both DATEV (?xx) and SWIFT (/XXX/) formats.
     */
    public static function fromRawLines(array $lines): self {
        // DATEV fields
        $gvcCode = null;
        $bookingText = null;
        $primanotenNr = null;
        $purposeLines = [];
        $payerBlz = null;
        $payerAccount = null;
        $payerName1 = null;
        $payerName2 = null;
        $textKeyExt = null;
        $rawParts = [];

        // SWIFT Keywords
        $endToEndReference = null;
        $paymentInfoId = null;
        $instructionId = null;
        $mandateReference = null;
        $creditorId = null;
        $remittanceInfo = null;
        $beneficiaryName = null;
        $orderingPartyName = null;
        $ultimateDebtorName = null;
        $ultimateCreditorName = null;
        $beneficiaryAccount = null;
        $beneficiaryBank = null;
        $orderingBank = null;
        $originalAmount = null;
        $charges = null;
        $exchangeRate = null;
        $purposeCode = null;
        $returnReason = null;
        $transactionReference = null;
        $virtualAccount = null;
        $numberOfTransactions = null;
        $typeCode = null;
        $localCode = null;
        $urgencyPriority = null;

        // Join all lines for SWIFT keyword parsing
        $fullText = implode('', $lines);

        // Check if this is SWIFT format (contains /XXX/ patterns)
        $isSwiftFormat = preg_match('/\/[A-Z]{2,}\//', $fullText);

        if ($isSwiftFormat) {
            // Parse SWIFT Keywords
            $endToEndReference = self::extractSwiftKeyword($fullText, 'EREF');
            $paymentInfoId = self::extractSwiftKeyword($fullText, 'PREF');
            $instructionId = self::extractSwiftKeyword($fullText, 'IREF');
            $mandateReference = self::extractSwiftKeyword($fullText, 'MREF');
            $creditorId = self::extractSwiftKeyword($fullText, 'CRED');
            $remittanceInfo = self::extractSwiftKeyword($fullText, 'REMI');
            $beneficiaryName = self::extractSwiftKeyword($fullText, 'BENM', 'NAME');
            $orderingPartyName = self::extractSwiftKeyword($fullText, 'ORDP', 'NAME');
            $ultimateDebtorName = self::extractSwiftKeyword($fullText, 'ULTD', 'NAME');
            $ultimateCreditorName = self::extractSwiftKeyword($fullText, 'ULTC', 'NAME');
            $beneficiaryAccount = self::extractSwiftKeyword($fullText, 'INFO');
            $beneficiaryBank = self::extractSwiftKeyword($fullText, 'BBK');
            $orderingBank = self::extractSwiftKeyword($fullText, 'OBK');
            $originalAmount = self::extractSwiftKeyword($fullText, 'OCMT');
            $charges = self::extractSwiftKeyword($fullText, 'CHGS');
            $exchangeRate = self::extractSwiftKeyword($fullText, 'EXCH');
            $purposeCode = self::extractSwiftKeyword($fullText, 'PURP', 'CD');
            $returnReason = self::extractSwiftKeyword($fullText, 'RTRN');
            $transactionReference = self::extractSwiftKeyword($fullText, 'TR');
            $virtualAccount = self::extractSwiftKeyword($fullText, 'VACC');
            $numberOfTransactions = self::extractSwiftKeyword($fullText, 'NBTR');
            $typeCode = self::extractSwiftKeyword($fullText, 'TYPE');
            $localCode = self::extractSwiftKeyword($fullText, 'CODE');
            $urgencyPriority = self::extractSwiftKeyword($fullText, 'URGP');

            // Store remaining text as raw
            $rawParts[] = $fullText;
        } else {
            // Parse DATEV format (?xx fields)
            foreach ($lines as $line) {
                if (preg_match('/^\?(\d{2})(.*)$/', $line, $match)) {
                    $fieldKey = $match[1];
                    $fieldValue = $match[2];

                    switch ($fieldKey) {
                        case '00':
                            $bookingText = $fieldValue;
                            break;
                        case '10':
                            $primanotenNr = $fieldValue;
                            break;
                        case '20':
                        case '21':
                        case '22':
                        case '23':
                        case '24':
                        case '25':
                        case '26':
                        case '27':
                        case '28':
                        case '29':
                        case '60':
                        case '61':
                        case '62':
                        case '63':
                            $purposeLines[] = $fieldValue;
                            break;
                        case '30':
                            $payerBlz = $fieldValue;
                            break;
                        case '31':
                            $payerAccount = $fieldValue;
                            break;
                        case '32':
                            $payerName1 = $fieldValue;
                            break;
                        case '33':
                            $payerName2 = $fieldValue;
                            break;
                        case '34':
                            $textKeyExt = $fieldValue;
                            break;
                        default:
                            $rawParts[] = $line;
                    }
                } else {
                    // First line may contain GVC-Code + Text
                    if ($gvcCode === null && preg_match('/^(\d{3})(.*)$/', $line, $match)) {
                        $gvcCode = $match[1];
                        if (!empty($match[2])) {
                            $rawParts[] = $match[2];
                        }
                    } else {
                        $rawParts[] = $line;
                    }
                }
            }

            // Also check DATEV purpose lines for SEPA keywords (EREF+, KREF+, SVWZ+, etc.)
            // Keywords are separated by the next keyword pattern (e.g., EREF+...MREF+)
            $combinedPurpose = implode('', $purposeLines);

            // Pattern to match until next keyword or end of string
            // Keywords: EREF+, KREF+, MREF+, CRED+, SVWZ+, ABWA+, DEBT+, IBAN+, BIC+
            $keywordPattern = '(?:EREF|KREF|MREF|CRED|SVWZ|ABWA|DEBT|IBAN|BIC)\+';

            if (preg_match('/EREF\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                $endToEndReference = trim($m[1]);
            }
            if (preg_match('/KREF\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                $paymentInfoId = trim($m[1]);
            }
            if (preg_match('/MREF\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                $mandateReference = trim($m[1]);
            }
            if (preg_match('/CRED\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                $creditorId = trim($m[1]);
            }
            if (preg_match('/SVWZ\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                $remittanceInfo = trim($m[1]);
            }
            if (preg_match('/ABWA\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                $ultimateDebtorName = trim($m[1]);
            }
            if (preg_match('/DEBT\+(.*?)(?=' . $keywordPattern . '|$)/', $combinedPurpose, $m)) {
                // DEBT+ contains debtor identification
                $debtorId = trim($m[1]);
                // Store as ordering party for now
                if ($orderingPartyName === null) {
                    $orderingPartyName = $debtorId;
                }
            }
        }

        $rawText = !empty($rawParts) ? implode('', $rawParts) : null;

        // Convert string values to enums
        $gvcCodeEnum = $gvcCode !== null ? GvcCode::tryFromString($gvcCode) : null;
        $textKeyExtEnum = $textKeyExt !== null ? TextKeyExtension::tryFromString($textKeyExt) : null;
        $purposeCodeEnum = $purposeCode !== null ? PurposeCode::tryFromString($purposeCode) : null;

        return new self(
            $gvcCodeEnum,
            $bookingText,
            $primanotenNr,
            $purposeLines,
            $payerBlz,
            $payerAccount,
            $payerName1,
            $payerName2,
            $textKeyExtEnum,
            $rawText,
            // SWIFT Keywords
            $endToEndReference,
            $paymentInfoId,
            $instructionId,
            $mandateReference,
            $creditorId,
            $remittanceInfo,
            $beneficiaryName,
            $orderingPartyName,
            $ultimateDebtorName,
            $ultimateCreditorName,
            $beneficiaryAccount,
            $beneficiaryBank,
            $orderingBank,
            $originalAmount,
            $charges,
            $exchangeRate,
            $purposeCodeEnum,
            $returnReason,
            $transactionReference,
            $virtualAccount,
            $numberOfTransactions,
            $typeCode,
            $localCode,
            $urgencyPriority
        );
    }

    /**
     * Extracts a SWIFT keyword value from text.
     * Format: /KEYWORD/value/ or /KEYWORD/SUBKEY/value/
     */
    private static function extractSwiftKeyword(string $text, string $keyword, ?string $subKey = null): ?string {
        if ($subKey !== null) {
            // Pattern: /KEYWORD/SUBKEY/value/
            $pattern = '/\/' . preg_quote($keyword, '/') . '\/' . preg_quote($subKey, '/') . '\/([^\/]*)\//';
        } else {
            // Pattern: /KEYWORD/value/
            $pattern = '/\/' . preg_quote($keyword, '/') . '\/([^\/]*)\//';
        }

        if (preg_match($pattern, $text, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    /**
     * Creates a Purpose object from a string.
     * 
     * The string may contain:
     * - DATEV format: "166?00SEPA?20EREF+...?30BIC?31IBAN..."
     * - SWIFT format: "/EREF/xxx//REMI/xxx/"
     * - Plain text narrative
     * 
     * For DATEV format, splits at ?xx markers to create virtual lines.
     */
    public static function fromString(string $text): self {
        // Check if this is DATEV format (contains ?xx markers)
        if (preg_match('/\?\d{2}/', $text)) {
            // Split DATEV format at ?xx markers into virtual lines
            // Keep the ?xx prefix with each segment
            $lines = preg_split('/(?=\?\d{2})/', $text, -1, PREG_SPLIT_NO_EMPTY);

            // The first segment may contain GVC code before the first ?xx
            if (!empty($lines) && !preg_match('/^\?\d{2}/', $lines[0])) {
                // First segment is GVC code (e.g., "166")
                // We need to handle it specially - keep it as is
            }

            return self::fromRawLines($lines);
        }

        // Check if this is SWIFT format (contains /XXX/ patterns)
        if (preg_match('/\/[A-Z]{2,}\//', $text)) {
            return self::fromRawLines([$text]);
        }

        // Plain text - store as raw
        return new self(rawText: $text);
    }

    /**
     * Returns the GVC code enum.
     */
    public function getGvcCode(): ?GvcCode {
        return $this->gvcCode;
    }

    public function getBookingText(): ?string {
        return $this->bookingText;
    }

    public function getPrimanotenNr(): ?string {
        return $this->primanotenNr;
    }

    public function getPurposeLines(): array {
        return $this->purposeLines;
    }

    public function getPayerBlz(): ?string {
        return $this->payerBlz;
    }

    public function getPayerAccount(): ?string {
        return $this->payerAccount;
    }

    public function getPayerName(): string {
        return trim(($this->payerName1 ?? '') . ' ' . ($this->payerName2 ?? ''));
    }

    public function getPayerName1(): ?string {
        return $this->payerName1;
    }

    public function getPayerName2(): ?string {
        return $this->payerName2;
    }

    /**
     * Returns the text key extension enum.
     */
    public function getTextKeyExt(): ?TextKeyExtension {
        return $this->textKeyExt;
    }

    public function getRawText(): ?string {
        return $this->rawText;
    }

    // =========================================================================
    // SWIFT Keyword Getters
    // =========================================================================

    /**
     * Returns the End-to-End Reference.
     * SWIFT: /EREF/, DATEV: EREF+
     */
    public function getEndToEndReference(): ?string {
        return $this->endToEndReference;
    }

    /**
     * Returns the Payment Information ID.
     * SWIFT: /PREF/, DATEV: KREF+
     */
    public function getPaymentInfoId(): ?string {
        return $this->paymentInfoId;
    }

    /**
     * Returns the Instruction ID.
     * SWIFT: /IREF/
     */
    public function getInstructionId(): ?string {
        return $this->instructionId;
    }

    /**
     * Returns the Mandate Reference.
     * SWIFT: /MREF/, DATEV: MREF+
     */
    public function getMandateReference(): ?string {
        return $this->mandateReference;
    }

    /**
     * Returns the Creditor Identifier.
     * SWIFT: /CRED/, DATEV: CRED+
     */
    public function getCreditorId(): ?string {
        return $this->creditorId;
    }

    /**
     * Returns the Remittance Information.
     * SWIFT: /REMI/, DATEV: SVWZ+
     */
    public function getRemittanceInfo(): ?string {
        return $this->remittanceInfo;
    }

    /**
     * Returns the Beneficiary Name.
     * SWIFT: /BENM/NAME/
     */
    public function getBeneficiaryName(): ?string {
        return $this->beneficiaryName;
    }

    /**
     * Returns the Ordering Party Name.
     * SWIFT: /ORDP/NAME/
     */
    public function getOrderingPartyName(): ?string {
        return $this->orderingPartyName;
    }

    /**
     * Returns the Ultimate Debtor Name.
     * SWIFT: /ULTD/NAME/
     */
    public function getUltimateDebtorName(): ?string {
        return $this->ultimateDebtorName;
    }

    /**
     * Returns the Ultimate Creditor Name.
     * SWIFT: /ULTC/NAME/
     */
    public function getUltimateCreditorName(): ?string {
        return $this->ultimateCreditorName;
    }

    /**
     * Returns the Beneficiary Account.
     * SWIFT: /INFO/
     */
    public function getBeneficiaryAccount(): ?string {
        return $this->beneficiaryAccount;
    }

    /**
     * Returns the Beneficiary Bank BIC.
     * SWIFT: /BBK/
     */
    public function getBeneficiaryBank(): ?string {
        return $this->beneficiaryBank;
    }

    /**
     * Returns the Ordering Bank BIC.
     * SWIFT: /OBK/
     */
    public function getOrderingBank(): ?string {
        return $this->orderingBank;
    }

    /**
     * Returns the Original Currency and Amount.
     * SWIFT: /OCMT/ - Format: 3!a15d (e.g., EUR1234,56)
     */
    public function getOriginalAmount(): ?string {
        return $this->originalAmount;
    }

    /**
     * Returns the Charges.
     * SWIFT: /CHGS/ - Format: 3!a15d
     */
    public function getCharges(): ?string {
        return $this->charges;
    }

    /**
     * Returns the Exchange Rate.
     * SWIFT: /EXCH/ - Format: 12d
     */
    public function getExchangeRate(): ?string {
        return $this->exchangeRate;
    }

    /**
     * Returns the Purpose Code enum.
     * SWIFT: /PURP/CD/ - Format: 4a (e.g., SALA, INTC, TAXS)
     */
    public function getPurposeCode(): ?PurposeCode {
        return $this->purposeCode;
    }

    /**
     * Returns the Return Reason Code.
     * SWIFT: /RTRN/ - Format: 4a
     */
    public function getReturnReason(): ?string {
        return $this->returnReason;
    }

    /**
     * Returns the Transaction Reference.
     * SWIFT: /TR/
     */
    public function getTransactionReference(): ?string {
        return $this->transactionReference;
    }

    /**
     * Returns the Virtual Account.
     * SWIFT: /VACC/
     */
    public function getVirtualAccount(): ?string {
        return $this->virtualAccount;
    }

    /**
     * Returns the Number of Transactions (for batch bookings).
     * SWIFT: /NBTR/
     */
    public function getNumberOfTransactions(): ?string {
        return $this->numberOfTransactions;
    }

    /**
     * Returns the Type Code.
     * SWIFT: /TYPE/ - Accounting entry type
     */
    public function getTypeCode(): ?string {
        return $this->typeCode;
    }

    /**
     * Returns the Local Operation Code.
     * SWIFT: /CODE/
     */
    public function getLocalCode(): ?string {
        return $this->localCode;
    }

    /**
     * Returns the Urgency/Priority flag.
     * SWIFT: /URGP/
     */
    public function getUrgencyPriority(): ?string {
        return $this->urgencyPriority;
    }

    /**
     * Checks if this purpose contains SWIFT structured keywords.
     */
    public function hasSwiftKeywords(): bool {
        return $this->endToEndReference !== null
            || $this->paymentInfoId !== null
            || $this->remittanceInfo !== null
            || $this->beneficiaryName !== null
            || $this->orderingPartyName !== null
            || $this->originalAmount !== null
            || $this->typeCode !== null;
    }

    /**
     * Checks if this purpose contains DATEV structured fields.
     */
    public function hasDatevFields(): bool {
        return $this->gvcCode !== null
            || $this->bookingText !== null
            || !empty($this->purposeLines);
    }

    /**
     * Checks if this is a SEPA transaction based on GVC code.
     */
    public function isSepaTransaction(): bool {
        return $this->gvcCode !== null && $this->gvcCode->isSepa();
    }

    /**
     * Checks if this is an instant (real-time) transaction based on GVC code.
     */
    public function isInstantTransaction(): bool {
        return $this->gvcCode !== null && $this->gvcCode->isInstant();
    }

    /**
     * Checks if this is a B2B transaction based on GVC code.
     */
    public function isB2BTransaction(): bool {
        return $this->gvcCode !== null && $this->gvcCode->isB2B();
    }

    /**
     * Checks if this is a return/reversal transaction based on GVC code.
     */
    public function isReturnTransaction(): bool {
        return $this->gvcCode !== null && $this->gvcCode->isReturn();
    }

    /**
     * Returns all SWIFT keywords as formatted string.
     * Format: /EREF/value//MREF/value/...
     */
    public function toSwiftKeywordString(): string {
        $parts = [];

        if ($this->endToEndReference !== null) {
            $parts[] = SwiftKeyword::EREF->format($this->endToEndReference);
        }
        if ($this->paymentInfoId !== null) {
            $parts[] = SwiftKeyword::PREF->format($this->paymentInfoId);
        }
        if ($this->mandateReference !== null) {
            $parts[] = SwiftKeyword::MREF->format($this->mandateReference);
        }
        if ($this->creditorId !== null) {
            $parts[] = SwiftKeyword::CRED->format($this->creditorId);
        }
        if ($this->remittanceInfo !== null) {
            $parts[] = SwiftKeyword::REMI->format($this->remittanceInfo);
        }
        if ($this->beneficiaryName !== null) {
            $parts[] = SwiftKeyword::BENM->format($this->beneficiaryName, 'NAME');
        }
        if ($this->orderingPartyName !== null) {
            $parts[] = SwiftKeyword::ORDP->format($this->orderingPartyName, 'NAME');
        }
        if ($this->originalAmount !== null) {
            $parts[] = SwiftKeyword::OCMT->format($this->originalAmount);
        }
        $purposeCodeStr = $this->purposeCode?->value;
        if ($purposeCodeStr !== null) {
            $parts[] = SwiftKeyword::PURP->format($purposeCodeStr, 'CD');
        }

        return implode('', $parts);
    }

    /**
     * Returns all SEPA keywords as formatted string.
     * Format: EREF+value MREF+value...
     */
    public function toSepaKeywordString(): string {
        $parts = [];

        if ($this->endToEndReference !== null) {
            $parts[] = SepaKeyword::EREF->format($this->endToEndReference);
        }
        if ($this->paymentInfoId !== null) {
            $parts[] = SepaKeyword::KREF->format($this->paymentInfoId);
        }
        if ($this->mandateReference !== null) {
            $parts[] = SepaKeyword::MREF->format($this->mandateReference);
        }
        if ($this->creditorId !== null) {
            $parts[] = SepaKeyword::CRED->format($this->creditorId);
        }
        if ($this->remittanceInfo !== null) {
            $parts[] = SepaKeyword::SVWZ->format($this->remittanceInfo);
        }

        return implode('', $parts);
    }

    /**
     * Returns the complete purpose of payment as string.
     * 
     * Note: purposeLines may have been split at 27-character boundaries during
     * MT940 parsing, potentially breaking words. For export, consider using
     * getContinuousPurposeText() which doesn't add spaces.
     */
    public function getPurposeText(): string {
        $parts = $this->purposeLines;
        if (!empty($this->rawText)) {
            $parts[] = $this->rawText;
        }
        return trim(implode(' ', $parts));
    }

    /**
     * Returns the continuous purpose text without adding spaces between segments.
     * Use this for MT940 export where line breaks are arbitrary (not word boundaries).
     */
    public function getContinuousPurposeText(): string {
        $parts = $this->purposeLines;
        if (!empty($this->rawText)) {
            $parts[] = $this->rawText;
        }
        return trim(implode('', $parts));
    }

    /**
     * Returns all information as readable text.
     */
    public function getFullText(): string {
        $parts = [];

        if (!empty($this->bookingText)) {
            $parts[] = $this->bookingText;
        }

        $purpose = $this->getPurposeText();
        if (!empty($purpose)) {
            $parts[] = $purpose;
        }

        $payerName = $this->getPayerName();
        if (!empty($payerName)) {
            $parts[] = $payerName;
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Returns all information as continuous text without artificial spaces.
     * Use this for MT940 export where line breaks are arbitrary.
     */
    public function getContinuousFullText(): string {
        $parts = [];

        if (!empty($this->bookingText)) {
            $parts[] = $this->bookingText;
        }

        $purpose = $this->getContinuousPurposeText();
        if (!empty($purpose)) {
            // Add space after booking text, but not between purpose segments
            $parts[] = $purpose;
        }

        $payerName = $this->getPayerName();
        if (!empty($payerName)) {
            $parts[] = $payerName;
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Konvertiert das Purpose-Objekt in MT940 :86: Format-Zeilen.
     *
     * @param Mt940OutputFormat $format Output format (SWIFT or DATEV)
     */
    public function toMt940Lines(Mt940OutputFormat $format = Mt940OutputFormat::SWIFT): array {
        if ($format === Mt940OutputFormat::DATEV) {
            return $this->toDatevLines();
        }

        // Use rawText if available, otherwise build continuous text without artificial spaces
        $text = $this->rawText ?? $this->getContinuousFullText();
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $chunks = str_split($text, self::SWIFT_LINE_LENGTH);
        $chunks = array_slice($chunks, 0, self::SWIFT_MAX_LINES);

        $lines = [':86:' . ($chunks[0] ?? '')];
        foreach (array_slice($chunks, 1) as $chunk) {
            $lines[] = $chunk;
        }

        return $lines;
    }

    /**
     * Konvertiert in DATEV-strukturierte :86: Zeilen.
     * 
     * Format: :86:GVC?00Buchungstext?10Primanoten?20VWZ1?21VWZ2...?30BLZ?31Kto?32Name1?33Name2?34TKE
     */
    public function toDatevLines(): array {
        $lines = [];

        // Erste Zeile: GVC-Code + ?00 + Buchungstext
        $gvcCodeStr = $this->gvcCode?->value ?? '';
        $firstLine = $gvcCodeStr;

        if (!empty($this->bookingText)) {
            $firstLine .= '?00' . $this->bookingText;
        } elseif (!empty($this->rawText)) {
            // Raw-Text: ?00 + erstes Segment, Rest als ?20-?29, ?60-?63
            $segments = str_split($this->rawText, 27);
            $firstLine .= '?00' . (array_shift($segments) ?? '');
            $lines[] = ':86:' . $firstLine;

            // Rest als Verwendungszweck-Zeilen
            $fieldKey = 20;
            foreach ($segments as $segment) {
                if ($fieldKey > 29 && $fieldKey < 60) {
                    $fieldKey = 60;
                }
                if ($fieldKey > 63) {
                    break;
                }
                $lines[] = sprintf('?%02d%s', $fieldKey++, $segment);
            }
            return $lines;
        }

        $lines[] = ':86:' . $firstLine;

        // Primanoten-Nr.
        if (!empty($this->primanotenNr)) {
            $lines[] = '?10' . $this->primanotenNr;
        }

        // Verwendungszweck-Zeilen
        $fieldKey = 20;
        foreach ($this->purposeLines as $purposeLine) {
            if ($fieldKey > 29 && $fieldKey < 60) {
                $fieldKey = 60;
            }
            if ($fieldKey > 63) {
                break;
            }
            $lines[] = sprintf('?%02d%s', $fieldKey++, $purposeLine);
        }

        // Zahlungspartner-Daten
        if (!empty($this->payerBlz)) {
            $lines[] = '?30' . $this->payerBlz;
        }
        if (!empty($this->payerAccount)) {
            $lines[] = '?31' . $this->payerAccount;
        }
        if (!empty($this->payerName1)) {
            $lines[] = '?32' . $this->payerName1;
        }
        if (!empty($this->payerName2)) {
            $lines[] = '?33' . $this->payerName2;
        }
        $textKeyExtStr = $this->textKeyExt?->value;
        if (!empty($textKeyExtStr)) {
            $lines[] = '?34' . $textKeyExtStr;
        }

        return $lines;
    }

    public function __toString(): string {
        return $this->getFullText();
    }
}
