<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtParser.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Parsers;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Iso20022ParserAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Entities\Camt\Balance as CamtBalance;
use CommonToolkit\FinancialFormats\Entities\Camt\Type29\CancellationDetails as Camt029CancellationDetails;
use CommonToolkit\FinancialFormats\Entities\Camt\Type29\CancellationStatus as Camt029CancellationStatus;
use CommonToolkit\FinancialFormats\Entities\Camt\Type29\Document as Camt029Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type29\OriginalGroupInformationAndStatus as Camt029GroupStatus;
use CommonToolkit\FinancialFormats\Entities\Camt\Type29\TransactionInformationAndStatus as Camt029TxStatus;
use CommonToolkit\FinancialFormats\Entities\Camt\Type52\Document as Camt052Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type52\Transaction as Camt052Transaction;
use CommonToolkit\FinancialFormats\Entities\Camt\Type53\Document as Camt053Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type53\Reference as Camt053Reference;
use CommonToolkit\FinancialFormats\Entities\Camt\Type53\Transaction as Camt053Transaction;
use CommonToolkit\FinancialFormats\Entities\Camt\Type54\Document as Camt054Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type54\Transaction as Camt054Transaction;
use CommonToolkit\FinancialFormats\Entities\Camt\Type55\Document as Camt055Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type55\PaymentCancellationRequest as Camt055CancellationRequest;
use CommonToolkit\FinancialFormats\Entities\Camt\Type55\UnderlyingTransaction as Camt055UnderlyingTransaction;
use CommonToolkit\FinancialFormats\Entities\Camt\Type56\Document as Camt056Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type56\PaymentCancellationRequest as Camt056CancellationRequest;
use CommonToolkit\FinancialFormats\Entities\Camt\Type56\UnderlyingTransaction as Camt056UnderlyingTransaction;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Helper\Data\CamtValidator;
use CommonToolkit\FinancialFormats\Registries\CamtParserRegistry;
use CommonToolkit\Helper\FileSystem\File;
use DateTimeImmutable;
use DOMDocument;
use DOMNode;
use DOMXPath;
use RuntimeException;

/**
 * Generischer Parser für CAMT-Dokumente.
 * 
 * Erkennt automatisch den CAMT-Typ und gibt das entsprechende
 * Document-Objekt zurück. Unterstützt CAMT 052, 053, 054 direkt
 * und delegiert andere Typen an den CamtReflectionParser.
 * 
 * Nutzt CamtParserHelper für gemeinsame XML-Parsing-Funktionalität.
 * 
 * @package CommonToolkit\Parsers
 */
class CamtParser extends Iso20022ParserAbstract {

    /**
     * Parst ein beliebiges CAMT-Dokument.
     * 
     * @param string $xmlContent XML-Inhalt
     * @param bool $validate Optional: XSD-Validierung durchführen
     * @return CamtDocumentInterface Geparstes Dokument
     * @throws RuntimeException Bei ungültigem XML oder unbekanntem Typ
     */
    public static function parse(string $xmlContent, bool $validate = false): CamtDocumentInterface {
        $type = CamtType::fromXml($xmlContent);

        if ($type === null) {
            throw new RuntimeException('Unbekannter CAMT-Dokumenttyp');
        }

        // Optional: XSD-Validierung
        if ($validate) {
            $validationResult = CamtValidator::validate($xmlContent, $type);
            if (!$validationResult->isValid()) {
                throw new RuntimeException(
                    "XSD-Validierung fehlgeschlagen: " . $validationResult->getFirstError()
                );
            }
        }

        // Registry für Reflection-Parser initialisieren
        CamtParserRegistry::initialize();

        return match ($type) {
            CamtType::CAMT029 => self::parseCamt029($xmlContent),
            CamtType::CAMT052 => self::parseCamt052($xmlContent),
            CamtType::CAMT053 => self::parseCamt053($xmlContent),
            CamtType::CAMT054 => self::parseCamt054($xmlContent),
            CamtType::CAMT055 => self::parseCamt055($xmlContent),
            CamtType::CAMT056 => self::parseCamt056($xmlContent),
            // CAMT.057, 058, 059 sowie alle anderen Typen über Reflection-Parser
            default => CamtReflectionParser::parse($xmlContent, $type),
        };
    }

    /**
     * Parst eine CAMT-Datei.
     * 
     * @param string $filePath Pfad zur Datei
     * @param bool $validate Optional: XSD-Validierung durchführen
     * @return CamtDocumentInterface Geparstes Dokument
     * @throws RuntimeException Bei Lesefehlern
     */
    public static function parseFile(string $filePath, bool $validate = false): CamtDocumentInterface {
        $content = File::read($filePath);
        return self::parse($content, $validate);
    }

    /**
     * Parst ein CAMT.053 Dokument.
     */
    public static function parseCamt053(string $xmlContent): Camt053Document {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException("Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler'));
        }

        [$xpath, $p] = static::initializeXPath($dom, 'camt.053');

        // Statement-Block finden
        $stmtNode = $xpath->query("//{$p}Stmt")->item(0);
        if (!$stmtNode) {
            throw new RuntimeException("Kein <Stmt>-Block gefunden.");
        }

        // Basisdaten
        $currency = $xpath->evaluate("string({$p}Acct/{$p}Ccy)", $stmtNode) ?: 'EUR';

        // Salden parsen
        $openingBalance = null;
        $closingBalance = null;

        foreach ($xpath->query("{$p}Bal", $stmtNode) as $balNode) {
            $balance = self::parseBalanceWithPrefix($xpath, $balNode, $currency, $p);
            if ($balance === null) continue;

            if ($balance->isOpeningBalance()) {
                $openingBalance = $balance;
            } elseif ($balance->isClosingBalance()) {
                $closingBalance = $balance;
            }
        }

        $document = new Camt053Document(
            id: $xpath->evaluate("string({$p}Id)", $stmtNode),
            creationDateTime: $xpath->evaluate("string({$p}CreDtTm)", $stmtNode),
            accountIdentifier: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}IBAN)", $stmtNode)
                ?: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}Othr/{$p}Id)", $stmtNode),
            currency: $currency,
            accountOwner: $xpath->evaluate("string({$p}Acct/{$p}Ownr/{$p}Nm)", $stmtNode) ?: null,
            servicerBic: $xpath->evaluate("string({$p}Acct/{$p}Svcr/{$p}FinInstnId/{$p}BICFI)", $stmtNode)
                ?: $xpath->evaluate("string({$p}Acct/{$p}Svcr/{$p}FinInstnId/{$p}BIC)", $stmtNode) ?: null,
            messageId: $xpath->evaluate("string(//{$p}GrpHdr/{$p}MsgId)") ?: null,
            sequenceNumber: $xpath->evaluate("string({$p}ElctrncSeqNb)", $stmtNode)
                ?: $xpath->evaluate("string({$p}LglSeqNb)", $stmtNode) ?: null,
            openingBalance: $openingBalance,
            closingBalance: $closingBalance
        );

        // Transaktionen parsen
        foreach ($xpath->query("{$p}Ntry", $stmtNode) as $entry) {
            $transaction = self::parseTransaction053WithPrefix($xpath, $entry, $currency, $p);
            if ($transaction !== null) {
                $document->addEntry($transaction);
            }
        }

        libxml_clear_errors();
        return $document;
    }

    /**
     * Parst ein CAMT.052 Dokument.
     */
    public static function parseCamt052(string $xmlContent): Camt052Document {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException("Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler'));
        }

        [$xpath, $p] = static::initializeXPath($dom, 'camt.052');

        // Report-Block finden
        $rptNode = $xpath->query("//{$p}Rpt")->item(0);
        if (!$rptNode) {
            throw new RuntimeException("Kein <Rpt>-Block gefunden.");
        }

        // Basisdaten
        $currency = $xpath->evaluate("string({$p}Acct/{$p}Ccy)", $rptNode) ?: 'EUR';

        // Salden parsen
        $openingBalance = null;
        $closingBalance = null;

        foreach ($xpath->query("{$p}Bal", $rptNode) as $balNode) {
            $balance = self::parseBalanceWithPrefix($xpath, $balNode, $currency, $p);
            if ($balance === null) continue;

            if ($balance->isOpeningBalance()) {
                $openingBalance = $balance;
            } elseif ($balance->isClosingBalance()) {
                $closingBalance = $balance;
            }
        }

        $document = new Camt052Document(
            id: $xpath->evaluate("string({$p}Id)", $rptNode),
            creationDateTime: $xpath->evaluate("string({$p}CreDtTm)", $rptNode),
            accountIdentifier: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}IBAN)", $rptNode)
                ?: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}Othr/{$p}Id)", $rptNode),
            currency: $currency,
            accountOwner: $xpath->evaluate("string({$p}Acct/{$p}Ownr/{$p}Nm)", $rptNode) ?: null,
            servicerBic: $xpath->evaluate("string({$p}Acct/{$p}Svcr/{$p}FinInstnId/{$p}BICFI)", $rptNode)
                ?: $xpath->evaluate("string({$p}Acct/{$p}Svcr/{$p}FinInstnId/{$p}BIC)", $rptNode) ?: null,
            messageId: $xpath->evaluate("string(//{$p}GrpHdr/{$p}MsgId)") ?: null,
            sequenceNumber: $xpath->evaluate("string({$p}ElctrncSeqNb)", $rptNode) ?: null,
            openingBalance: $openingBalance,
            closingBalance: $closingBalance
        );

        // Transaktionen parsen
        foreach ($xpath->query("{$p}Ntry", $rptNode) as $entry) {
            $transaction = self::parseTransaction052WithPrefix($xpath, $entry, $currency, $p);
            if ($transaction !== null) {
                $document->addEntry($transaction);
            }
        }

        libxml_clear_errors();
        return $document;
    }

    /**
     * Parst ein CAMT.054 Dokument.
     */
    public static function parseCamt054(string $xmlContent): Camt054Document {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException("Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler'));
        }

        [$xpath, $p] = static::initializeXPath($dom, 'camt.054');

        // Notification-Block finden
        $ntfctnNode = $xpath->query("//{$p}Ntfctn")->item(0);
        if (!$ntfctnNode) {
            throw new RuntimeException("Kein <Ntfctn>-Block gefunden.");
        }

        // Basisdaten
        $currency = $xpath->evaluate("string({$p}Acct/{$p}Ccy)", $ntfctnNode) ?: 'EUR';

        $document = new Camt054Document(
            id: $xpath->evaluate("string({$p}Id)", $ntfctnNode),
            creationDateTime: $xpath->evaluate("string(//{$p}GrpHdr/{$p}CreDtTm)") ?: 'now',
            accountIdentifier: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}IBAN)", $ntfctnNode)
                ?: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}Othr/{$p}Id)", $ntfctnNode),
            currency: $currency,
            accountOwner: $xpath->evaluate("string({$p}Acct/{$p}Ownr/{$p}Id/{$p}OrgId/{$p}AnyBIC)", $ntfctnNode) ?: null,
            messageId: $xpath->evaluate("string(//{$p}GrpHdr/{$p}MsgId)") ?: null
        );

        // Transaktionen parsen
        foreach ($xpath->query("{$p}Ntry", $ntfctnNode) as $entry) {
            $transaction = self::parseTransaction054WithPrefix($xpath, $entry, $currency, $p);
            if ($transaction !== null) {
                $document->addEntry($transaction);
            }
        }

        libxml_clear_errors();
        return $document;
    }

    // =========================================================================
    // CAMT-SPEZIFISCHE PARSING-METHODEN
    // =========================================================================

    /**
     * Parst einen Balance-Block mit dynamischem Präfix.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $balNode Der Balance-Node
     * @param string $defaultCurrency Standard-Währung
     * @param string $p Namespace-Präfix
     * @return CamtBalance|null Der geparste Saldo oder null
     */
    private static function parseBalanceWithPrefix(DOMXPath $xpath, DOMNode $balNode, string $defaultCurrency, string $p): ?CamtBalance {
        $amountStr = $xpath->evaluate("string({$p}Amt)", $balNode);
        if (empty($amountStr)) {
            return null;
        }

        $amount = static::parseAmountValue($amountStr);
        $currency = static::xpathStringStatic($xpath, "{$p}Amt/@Ccy", $balNode) ?? $defaultCurrency;
        $creditDebitStr = static::xpathStringStatic($xpath, "{$p}CdtDbtInd", $balNode);
        $dateStr = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}Dt/{$p}Dt",
            "{$p}Dt/{$p}DtTm"
        ], $balNode);
        $balanceType = static::xpathStringStatic($xpath, "{$p}Tp/{$p}CdOrPrtry/{$p}Cd", $balNode);
        $balanceSubType = static::xpathStringStatic($xpath, "{$p}Tp/{$p}SubTp/{$p}Cd", $balNode);

        if (empty($dateStr)) {
            return null;
        }

        return new CamtBalance(
            creditDebit: static::parseCreditDebitIndicator($creditDebitStr),
            date: $dateStr,
            currency: $currency,
            amount: $amount,
            type: $balanceType ?? 'CLBD',
            subType: $balanceSubType
        );
    }

    /**
     * Parst einen Entry-Block für CAMT.053 mit dynamischem Präfix.
     */
    private static function parseTransaction053WithPrefix(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?Camt053Transaction {
        $amountStr = $xpath->evaluate("string({$p}Amt)", $entry);
        if (empty($amountStr)) {
            return null;
        }

        $amount = (float) str_replace(',', '.', $amountStr);
        $entryCcy = $xpath->evaluate("string({$p}Amt/@Ccy)", $entry) ?: $defaultCurrency;
        $creditDebitStr = $xpath->evaluate("string({$p}CdtDbtInd)", $entry);
        $reversalIndicator = $xpath->evaluate("string({$p}RvslInd)", $entry);
        $status = $xpath->evaluate("string({$p}Sts/{$p}Cd)", $entry) ?: 'BOOK';

        $bookingDateStr = $xpath->evaluate("string({$p}BookgDt/{$p}Dt)", $entry)
            ?: $xpath->evaluate("string({$p}BookgDt/{$p}DtTm)", $entry);
        $valutaDateStr = $xpath->evaluate("string({$p}ValDt/{$p}Dt)", $entry)
            ?: $xpath->evaluate("string({$p}ValDt/{$p}DtTm)", $entry);

        if (empty($bookingDateStr)) {
            return null;
        }

        $isReversal = strtolower($reversalIndicator) === 'true';
        $creditDebit = match (strtoupper($creditDebitStr)) {
            'CRDT' => $isReversal ? CreditDebit::DEBIT : CreditDebit::CREDIT,
            'DBIT' => $isReversal ? CreditDebit::CREDIT : CreditDebit::DEBIT,
            default => CreditDebit::CREDIT
        };

        $currency = CurrencyCode::tryFrom(strtoupper($entryCcy)) ?? CurrencyCode::Euro;

        $entryRef = $xpath->evaluate("string({$p}NtryRef)", $entry) ?: null;
        $acctSvcrRef = $xpath->evaluate("string({$p}AcctSvcrRef)", $entry) ?: null;

        // Bank Transaction Code
        $bankTxCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Prtry/{$p}Cd)", $entry) ?: null;
        $domainCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Cd)", $entry) ?: null;
        $familyCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Fmly/{$p}Cd)", $entry) ?: null;
        $subFamilyCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Fmly/{$p}SubFmlyCd)", $entry) ?: null;

        // TxDtls Block - CAMT.053 spezifisch
        $txDtls = $xpath->query("{$p}NtryDtls/{$p}TxDtls", $entry)->item(0);
        $purpose = null;
        $purposeCode = null;
        $additionalInfo = null;
        $returnReason = null;
        $endToEndId = null;
        $mandateId = null;
        $creditorId = null;
        $paymentInformationId = null;
        $instructionId = null;
        $counterpartyName = null;
        $counterpartyIban = null;
        $counterpartyBic = null;

        if ($txDtls) {
            // Referenzen parsen
            $endToEndId = $xpath->evaluate("string({$p}Refs/{$p}EndToEndId)", $txDtls) ?: null;
            $mandateId = $xpath->evaluate("string({$p}Refs/{$p}MndtId)", $txDtls) ?: null;
            $paymentInformationId = $xpath->evaluate("string({$p}Refs/{$p}PmtInfId)", $txDtls) ?: null;
            $instructionId = $xpath->evaluate("string({$p}Refs/{$p}InstrId)", $txDtls) ?: null;

            // Gläubiger-ID kann im RmtInf/Strd/CdtrRefInf stehen
            $creditorId = $xpath->evaluate("string({$p}RltdPties/{$p}Cdtr/{$p}Id/{$p}PrvtId/{$p}Othr/{$p}Id)", $txDtls) ?: null;
            if (empty($creditorId)) {
                $creditorId = $xpath->evaluate("string({$p}RmtInf/{$p}Strd/{$p}CdtrRefInf/{$p}Ref)", $txDtls) ?: null;
            }

            // Purpose
            $purpose = $xpath->evaluate("string({$p}Purp/{$p}Prtry)", $txDtls) ?: null;
            $purposeCode = $xpath->evaluate("string({$p}Purp/{$p}Cd)", $txDtls) ?: null;

            // Remittance Info
            $additionalInfo = $xpath->evaluate("string({$p}RmtInf/{$p}Ustrd)", $txDtls) ?: null;

            // Return Reason
            $returnReason = $xpath->evaluate("string({$p}RtrInf/{$p}Rsn/{$p}Cd)", $txDtls) ?: null;

            // Counterparty - je nach Credit/Debit
            if ($creditDebit === CreditDebit::CREDIT) {
                // Bei Gutschrift ist die Gegenseite der Debtor
                $counterpartyName = $xpath->evaluate("string({$p}RltdPties/{$p}Dbtr/{$p}Nm)", $txDtls) ?: null;
                $counterpartyIban = $xpath->evaluate("string({$p}RltdPties/{$p}DbtrAcct/{$p}Id/{$p}IBAN)", $txDtls) ?: null;
                $counterpartyBic = $xpath->evaluate("string({$p}RltdAgts/{$p}DbtrAgt/{$p}FinInstnId/{$p}BICFI)", $txDtls)
                    ?: $xpath->evaluate("string({$p}RltdAgts/{$p}DbtrAgt/{$p}FinInstnId/{$p}BIC)", $txDtls) ?: null;
            } else {
                // Bei Lastschrift ist die Gegenseite der Creditor
                $counterpartyName = $xpath->evaluate("string({$p}RltdPties/{$p}Cdtr/{$p}Nm)", $txDtls) ?: null;
                $counterpartyIban = $xpath->evaluate("string({$p}RltdPties/{$p}CdtrAcct/{$p}Id/{$p}IBAN)", $txDtls) ?: null;
                $counterpartyBic = $xpath->evaluate("string({$p}RltdAgts/{$p}CdtrAgt/{$p}FinInstnId/{$p}BICFI)", $txDtls)
                    ?: $xpath->evaluate("string({$p}RltdAgts/{$p}CdtrAgt/{$p}FinInstnId/{$p}BIC)", $txDtls) ?: null;
            }
        }

        // Fallback für Additional Info
        if ($additionalInfo === null) {
            $additionalInfo = $xpath->evaluate("string({$p}AddtlNtryInf)", $entry) ?: null;
        }

        // Reference-Objekt erstellen
        $reference = new Camt053Reference(
            endToEndId: $endToEndId,
            mandateId: $mandateId,
            creditorId: $creditorId,
            entryReference: $entryRef,
            accountServicerReference: $acctSvcrRef,
            paymentInformationId: $paymentInformationId,
            instructionId: $instructionId
        );

        return new Camt053Transaction(
            bookingDate: new DateTimeImmutable($bookingDateStr),
            valutaDate: !empty($valutaDateStr) ? new DateTimeImmutable($valutaDateStr) : null,
            amount: $amount,
            currency: $currency,
            creditDebit: $creditDebit,
            reference: $reference,
            entryReference: $entryRef,
            accountServicerReference: $acctSvcrRef,
            status: $status,
            isReversal: $isReversal,
            purpose: $purpose,
            purposeCode: $purposeCode,
            additionalInfo: $additionalInfo,
            transactionCode: $bankTxCode,
            domainCode: $domainCode,
            familyCode: $familyCode,
            subFamilyCode: $subFamilyCode,
            returnReason: $returnReason,
            counterpartyName: $counterpartyName,
            counterpartyIban: $counterpartyIban,
            counterpartyBic: $counterpartyBic
        );
    }

    /**
     * Parst einen Entry-Block für CAMT.052 mit dynamischem Präfix.
     */
    private static function parseTransaction052WithPrefix(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?Camt052Transaction {
        $amountStr = $xpath->evaluate("string({$p}Amt)", $entry);
        if (empty($amountStr)) {
            return null;
        }

        $amount = (float) str_replace(',', '.', $amountStr);
        $entryCcy = $xpath->evaluate("string({$p}Amt/@Ccy)", $entry) ?: $defaultCurrency;
        $creditDebitStr = $xpath->evaluate("string({$p}CdtDbtInd)", $entry);
        $reversalIndicator = $xpath->evaluate("string({$p}RvslInd)", $entry);
        $status = $xpath->evaluate("string({$p}Sts/{$p}Cd)", $entry) ?: 'BOOK';

        $bookingDateStr = $xpath->evaluate("string({$p}BookgDt/{$p}Dt)", $entry)
            ?: $xpath->evaluate("string({$p}BookgDt/{$p}DtTm)", $entry);
        $valutaDateStr = $xpath->evaluate("string({$p}ValDt/{$p}Dt)", $entry)
            ?: $xpath->evaluate("string({$p}ValDt/{$p}DtTm)", $entry);

        if (empty($bookingDateStr)) {
            return null;
        }

        $isReversal = strtolower($reversalIndicator) === 'true';
        $creditDebit = match (strtoupper($creditDebitStr)) {
            'CRDT' => $isReversal ? CreditDebit::DEBIT : CreditDebit::CREDIT,
            'DBIT' => $isReversal ? CreditDebit::CREDIT : CreditDebit::DEBIT,
            default => CreditDebit::CREDIT
        };

        $currency = CurrencyCode::tryFrom(strtoupper($entryCcy)) ?? CurrencyCode::Euro;

        $entryRef = $xpath->evaluate("string({$p}NtryRef)", $entry) ?: null;
        $acctSvcrRef = $xpath->evaluate("string({$p}AcctSvcrRef)", $entry) ?: null;

        // Bank Transaction Code
        $bankTxCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Prtry/{$p}Cd)", $entry) ?: null;
        $domainCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Cd)", $entry) ?: null;
        $familyCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Fmly/{$p}Cd)", $entry) ?: null;
        $subFamilyCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Fmly/{$p}SubFmlyCd)", $entry) ?: null;

        // Purpose und Additional Info
        $txDtls = $xpath->query("{$p}NtryDtls/{$p}TxDtls", $entry)->item(0);
        $purpose = null;
        $purposeCode = null;
        $additionalInfo = null;
        $returnReason = null;

        if ($txDtls) {
            $purpose = $xpath->evaluate("string({$p}Purp/{$p}Prtry)", $txDtls) ?: null;
            $purposeCode = $xpath->evaluate("string({$p}Purp/{$p}Cd)", $txDtls) ?: null;
            $additionalInfo = $xpath->evaluate("string({$p}AddtlTxInf)", $txDtls) ?: null;
            $returnReason = $xpath->evaluate("string({$p}RtrInf/{$p}Rsn/{$p}Cd)", $txDtls) ?: null;
        }

        if ($additionalInfo === null) {
            $additionalInfo = $xpath->evaluate("string({$p}AddtlNtryInf)", $entry) ?: null;
        }

        return new Camt052Transaction(
            bookingDate: new DateTimeImmutable($bookingDateStr),
            valutaDate: !empty($valutaDateStr) ? new DateTimeImmutable($valutaDateStr) : null,
            amount: $amount,
            currency: $currency,
            creditDebit: $creditDebit,
            entryReference: $entryRef,
            accountServicerReference: $acctSvcrRef,
            status: $status,
            isReversal: $isReversal,
            purpose: $purpose,
            purposeCode: $purposeCode,
            additionalInfo: $additionalInfo,
            bankTransactionCode: $bankTxCode,
            domainCode: $domainCode,
            familyCode: $familyCode,
            subFamilyCode: $subFamilyCode,
            returnReason: $returnReason
        );
    }

    /**
     * Parst einen Entry-Block für CAMT.054 mit dynamischem Präfix.
     */
    private static function parseTransaction054WithPrefix(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?Camt054Transaction {
        $amountStr = $xpath->evaluate("string({$p}Amt)", $entry);
        if (empty($amountStr)) {
            return null;
        }

        $amount = (float) str_replace(',', '.', $amountStr);
        $entryCcy = $xpath->evaluate("string({$p}Amt/@Ccy)", $entry) ?: $defaultCurrency;
        $creditDebitStr = $xpath->evaluate("string({$p}CdtDbtInd)", $entry);
        $reversalIndicator = $xpath->evaluate("string({$p}RvslInd)", $entry);
        $status = $xpath->evaluate("string({$p}Sts/{$p}Cd)", $entry) ?: 'BOOK';

        $bookingDateStr = $xpath->evaluate("string({$p}BookgDt/{$p}DtTm)", $entry)
            ?: $xpath->evaluate("string({$p}BookgDt/{$p}Dt)", $entry);
        $valutaDateStr = $xpath->evaluate("string({$p}ValDt/{$p}Dt)", $entry)
            ?: $xpath->evaluate("string({$p}ValDt/{$p}DtTm)", $entry);

        if (empty($bookingDateStr)) {
            return null;
        }

        $isReversal = strtolower($reversalIndicator) === 'true';
        $creditDebit = match (strtoupper($creditDebitStr)) {
            'CRDT' => $isReversal ? CreditDebit::DEBIT : CreditDebit::CREDIT,
            'DBIT' => $isReversal ? CreditDebit::CREDIT : CreditDebit::DEBIT,
            default => CreditDebit::CREDIT
        };

        $currency = CurrencyCode::tryFrom(strtoupper($entryCcy)) ?? CurrencyCode::Euro;

        $entryRef = $xpath->evaluate("string({$p}NtryRef)", $entry) ?: null;
        $acctSvcrRef = $xpath->evaluate("string({$p}AcctSvcrRef)", $entry) ?: null;

        // Bank Transaction Code (proprietär)
        $bankTxCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Prtry/{$p}Cd)", $entry) ?: null;

        // ISO 20022 Domain/Family/SubFamily Codes
        $domainCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Cd)", $entry) ?: null;
        $familyCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Fmly/{$p}Cd)", $entry) ?: null;
        $subFamilyCode = $xpath->evaluate("string({$p}BkTxCd/{$p}Domn/{$p}Fmly/{$p}SubFmlyCd)", $entry) ?: null;

        // TxDtls
        $txDtls = $xpath->query("{$p}NtryDtls/{$p}TxDtls", $entry)->item(0);
        $instructionId = null;
        $endToEndId = null;
        $remittanceInfo = null;
        $purposeCode = null;
        $returnReason = null;
        $localInstrumentCode = null;
        $instructingAgentBic = null;
        $instructedAgentBic = null;
        $debtorAgentBic = null;
        $creditorAgentBic = null;

        if ($txDtls) {
            $instructionId = $xpath->evaluate("string({$p}Refs/{$p}InstrId)", $txDtls) ?: null;
            $endToEndId = $xpath->evaluate("string({$p}Refs/{$p}EndToEndId)", $txDtls) ?: null;
            $remittanceInfo = $xpath->evaluate("string({$p}RmtInf/{$p}Ustrd)", $txDtls) ?: null;
            $purposeCode = $xpath->evaluate("string({$p}Purp/{$p}Cd)", $txDtls) ?: null;
            $returnReason = $xpath->evaluate("string({$p}RtrInf/{$p}Rsn/{$p}Cd)", $txDtls) ?: null;
            $localInstrumentCode = $xpath->evaluate("string({$p}LclInstrm/{$p}Prtry)", $txDtls)
                ?: $xpath->evaluate("string({$p}LclInstrm/{$p}Cd)", $txDtls) ?: null;

            $instructingAgentBic = $xpath->evaluate("string({$p}RltdAgts/{$p}InstgAgt/{$p}FinInstnId/{$p}BICFI)", $txDtls) ?: null;
            $instructedAgentBic = $xpath->evaluate("string({$p}RltdAgts/{$p}InstdAgt/{$p}FinInstnId/{$p}BICFI)", $txDtls) ?: null;
            $debtorAgentBic = $xpath->evaluate("string({$p}RltdAgts/{$p}DbtrAgt/{$p}FinInstnId/{$p}BICFI)", $txDtls) ?: null;
            $creditorAgentBic = $xpath->evaluate("string({$p}RltdAgts/{$p}CdtrAgt/{$p}FinInstnId/{$p}BICFI)", $txDtls) ?: null;
        }

        return new Camt054Transaction(
            bookingDate: new DateTimeImmutable($bookingDateStr),
            valutaDate: !empty($valutaDateStr) ? new DateTimeImmutable($valutaDateStr) : null,
            amount: $amount,
            currency: $currency,
            creditDebit: $creditDebit,
            entryReference: $entryRef,
            accountServicerReference: $acctSvcrRef,
            status: $status,
            isReversal: $isReversal,
            instructionId: $instructionId,
            endToEndId: $endToEndId,
            remittanceInfo: $remittanceInfo,
            purposeCode: $purposeCode,
            bankTransactionCode: $bankTxCode,
            domainCode: $domainCode,
            familyCode: $familyCode,
            subFamilyCode: $subFamilyCode,
            returnReason: $returnReason,
            localInstrumentCode: $localInstrumentCode,
            instructingAgentBic: $instructingAgentBic,
            instructedAgentBic: $instructedAgentBic,
            debtorAgentBic: $debtorAgentBic,
            creditorAgentBic: $creditorAgentBic
        );
    }
    /**
     * Parst ein CAMT.029 Dokument (Resolution of Investigation).
     */
    public static function parseCamt029(string $xmlContent): Camt029Document {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException("Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler'));
        }

        [$xpath, $p] = static::initializeXPath($dom, 'camt.029');

        // Assignment parsen
        $assignmentId = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Id)");
        $creDtTm = $xpath->evaluate("string(//{$p}Assgnmt/{$p}CreDtTm)");
        $assgnrAgtBic = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgnr/{$p}Agt/{$p}FinInstnId/{$p}BICFI)") ?: null;
        $assgnrPtyNm = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgnr/{$p}Pty/{$p}Nm)") ?: null;
        $assgneAgtBic = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgne/{$p}Agt/{$p}FinInstnId/{$p}BICFI)") ?: null;
        $assgnePtyNm = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgne/{$p}Pty/{$p}Nm)") ?: null;

        // Case parsen
        $caseId = $xpath->evaluate("string(//{$p}Case/{$p}Id)") ?: null;
        $caseCreator = $xpath->evaluate("string(//{$p}Case/{$p}Cretr/{$p}Pty/{$p}Nm)") ?: null;

        // Status parsen
        $invSts = $xpath->evaluate("string(//{$p}Sts/{$p}Conf)") ?: null;
        $invStsPrtry = $xpath->evaluate("string(//{$p}Sts/{$p}Prtry)") ?: null;

        $document = new Camt029Document(
            assignmentId: $assignmentId,
            creationDateTime: $creDtTm,
            assignerAgentBic: $assgnrAgtBic,
            assignerPartyName: $assgnrPtyNm,
            assigneeAgentBic: $assgneAgtBic,
            assigneePartyName: $assgnePtyNm,
            caseId: $caseId,
            caseCreator: $caseCreator,
            investigationStatus: $invSts,
            investigationStatusProprietary: $invStsPrtry
        );

        // CxlDtls parsen
        foreach ($xpath->query("//{$p}CxlDtls") as $cxlDtlsNode) {
            // OrgnlGrpInfAndSts parsen
            $orgnlMsgId = $xpath->evaluate("string({$p}OrgnlGrpInfAndSts/{$p}OrgnlMsgId)", $cxlDtlsNode) ?: null;
            $orgnlMsgNmId = $xpath->evaluate("string({$p}OrgnlGrpInfAndSts/{$p}OrgnlMsgNmId)", $cxlDtlsNode) ?: null;
            $orgnlCreDtTm = $xpath->evaluate("string({$p}OrgnlGrpInfAndSts/{$p}OrgnlCreDtTm)", $cxlDtlsNode) ?: null;
            $grpCxlSts = $xpath->evaluate("string({$p}OrgnlGrpInfAndSts/{$p}GrpCxlSts)", $cxlDtlsNode) ?: null;

            $groupStatus = null;
            if ($orgnlMsgId !== null) {
                $groupStatus = new Camt029GroupStatus(
                    originalMessageId: $orgnlMsgId,
                    originalMessageNameId: $orgnlMsgNmId,
                    originalCreationDateTime: $orgnlCreDtTm,
                    groupCancellationStatus: $grpCxlSts
                );
            }

            $cancellationDetails = new Camt029CancellationDetails($groupStatus);

            // TxInfAndSts parsen
            foreach ($xpath->query("{$p}TxInfAndSts", $cxlDtlsNode) as $txInfAndStsNode) {
                $cxlStsId = $xpath->evaluate("string({$p}CxlStsId)", $txInfAndStsNode) ?: null;
                $orgnlInstrId = $xpath->evaluate("string({$p}OrgnlInstrId)", $txInfAndStsNode) ?: null;
                $orgnlEndToEndId = $xpath->evaluate("string({$p}OrgnlEndToEndId)", $txInfAndStsNode) ?: null;
                $orgnlTxId = $xpath->evaluate("string({$p}OrgnlTxId)", $txInfAndStsNode) ?: null;
                $txCxlSts = $xpath->evaluate("string({$p}TxCxlSts)", $txInfAndStsNode) ?: null;
                $orgnlAmt = $xpath->evaluate("string({$p}OrgnlIntrBkSttlmAmt)", $txInfAndStsNode) ?: null;
                $orgnlCcy = $xpath->evaluate("string({$p}OrgnlIntrBkSttlmAmt/@Ccy)", $txInfAndStsNode) ?: null;
                $orgnlSttlmDt = $xpath->evaluate("string({$p}OrgnlIntrBkSttlmDt)", $txInfAndStsNode) ?: null;
                $dbtrNm = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}Dbtr/{$p}Nm)", $txInfAndStsNode) ?: null;
                $dbtrIban = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}DbtrAcct/{$p}Id/{$p}IBAN)", $txInfAndStsNode) ?: null;
                $cdtrNm = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}Cdtr/{$p}Nm)", $txInfAndStsNode) ?: null;
                $cdtrIban = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}CdtrAcct/{$p}Id/{$p}IBAN)", $txInfAndStsNode) ?: null;

                $txStatus = new Camt029TxStatus(
                    cancellationStatusId: $cxlStsId,
                    originalInstructionId: $orgnlInstrId,
                    originalEndToEndId: $orgnlEndToEndId,
                    originalTransactionId: $orgnlTxId,
                    transactionCancellationStatus: $txCxlSts,
                    originalAmount: $orgnlAmt,
                    originalCurrency: $orgnlCcy,
                    originalInterbankSettlementDate: $orgnlSttlmDt,
                    debtorName: $dbtrNm,
                    debtorIban: $dbtrIban,
                    creditorName: $cdtrNm,
                    creditorIban: $cdtrIban
                );

                // CxlStsRsnInf parsen
                foreach ($xpath->query("{$p}CxlStsRsnInf", $txInfAndStsNode) as $cxlStsRsnInfNode) {
                    $rsnCd = $xpath->evaluate("string({$p}Rsn/{$p}Cd)", $cxlStsRsnInfNode) ?: null;
                    $rsnPrtry = $xpath->evaluate("string({$p}Rsn/{$p}Prtry)", $cxlStsRsnInfNode) ?: null;
                    $addtlInf = $xpath->evaluate("string({$p}AddtlInf)", $cxlStsRsnInfNode) ?: null;
                    $orgnNm = $xpath->evaluate("string({$p}Orgtr/{$p}Nm)", $cxlStsRsnInfNode) ?: null;
                    $orgnId = $xpath->evaluate("string({$p}Orgtr/{$p}Id/{$p}OrgId/{$p}Othr/{$p}Id)", $cxlStsRsnInfNode) ?: null;

                    $txStatus->addCancellationStatusReasonInformation(new Camt029CancellationStatus(
                        statusCode: $rsnCd,
                        statusProprietary: $rsnPrtry,
                        additionalInformation: $addtlInf,
                        originatorName: $orgnNm,
                        originatorId: $orgnId
                    ));
                }

                $cancellationDetails->addTransactionInformationAndStatus($txStatus);
            }

            $document->addCancellationDetails($cancellationDetails);
        }

        libxml_clear_errors();
        return $document;
    }

    /**
     * Parst ein CAMT.055 Dokument (Customer Payment Cancellation Request).
     */
    public static function parseCamt055(string $xmlContent): Camt055Document {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException("Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler'));
        }

        [$xpath, $p] = static::initializeXPath($dom, 'camt.055');

        // Assignment (enthält messageId und creationDateTime)
        $msgId = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Id)");
        $creDtTm = $xpath->evaluate("string(//{$p}Assgnmt/{$p}CreDtTm)");
        $initgPtyNm = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgnr/{$p}Pty/{$p}Nm)") ?: null;
        $initgPtyId = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgnr/{$p}Pty/{$p}Id/{$p}OrgId/{$p}Othr/{$p}Id)") ?: null;

        // Case
        $caseId = $xpath->evaluate("string(//{$p}Case/{$p}Id)") ?: null;
        $caseCreator = $xpath->evaluate("string(//{$p}Case/{$p}Cretr/{$p}Pty/{$p}Nm)") ?: null;

        // Control Data
        $nbOfTxs = $xpath->evaluate("string(//{$p}CtrlData/{$p}NbOfTxs)") ?: null;
        $ctrlSum = $xpath->evaluate("string(//{$p}CtrlData/{$p}CtrlSum)") ?: null;

        $document = new Camt055Document(
            messageId: $msgId,
            creationDateTime: $creDtTm,
            numberOfTransactions: $nbOfTxs,
            controlSum: $ctrlSum,
            initiatingPartyName: $initgPtyNm,
            initiatingPartyId: $initgPtyId,
            caseId: $caseId,
            caseCreator: $caseCreator
        );

        // Underlying Transactions parsen
        foreach ($xpath->query("//{$p}Undrlyg") as $undrlygNode) {
            $orgnlGrpMsgId = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlMsgId)", $undrlygNode) ?: null;
            $orgnlGrpMsgNmId = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlMsgNmId)", $undrlygNode) ?: null;
            $orgnlGrpCreDtTm = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlCreDtTm)", $undrlygNode) ?: null;
            $orgnlNbOfTxs = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlNbOfTxs)", $undrlygNode) ?: null;
            $orgnlCtrlSum = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlCtrlSum)", $undrlygNode) ?: null;

            $underlying = new Camt055UnderlyingTransaction(
                originalGroupInformationMessageId: $orgnlGrpMsgId,
                originalGroupInformationMessageNameId: $orgnlGrpMsgNmId,
                originalGroupInformationCreationDateTime: $orgnlGrpCreDtTm,
                originalNumberOfTransactions: $orgnlNbOfTxs !== null ? (int)$orgnlNbOfTxs : null,
                originalControlSum: $orgnlCtrlSum
            );

            // TxInf aus OrgnlPmtInfAndCxl parsen
            foreach ($xpath->query("{$p}OrgnlPmtInfAndCxl/{$p}TxInf", $undrlygNode) as $txInfNode) {
                $cxlId = $xpath->evaluate("string({$p}CxlId)", $txInfNode) ?: null;
                $orgnlInstrId = $xpath->evaluate("string({$p}OrgnlInstrId)", $txInfNode) ?: null;
                $orgnlEndToEndId = $xpath->evaluate("string({$p}OrgnlEndToEndId)", $txInfNode) ?: null;
                $orgnlTxId = $xpath->evaluate("string({$p}OrgnlTxId)", $txInfNode) ?: null;
                $orgnlAmt = $xpath->evaluate("string({$p}OrgnlInstdAmt)", $txInfNode) ?: null;
                $orgnlCcy = $xpath->evaluate("string({$p}OrgnlInstdAmt/@Ccy)", $txInfNode) ?: null;
                $reqExecDt = $xpath->evaluate("string({$p}OrgnlReqdExctnDt/{$p}Dt)", $txInfNode) ?: null;
                $cxlRsnCd = $xpath->evaluate("string({$p}CxlRsnInf/{$p}Rsn/{$p}Cd)", $txInfNode) ?: null;
                $cxlRsnPrtry = $xpath->evaluate("string({$p}CxlRsnInf/{$p}Rsn/{$p}Prtry)", $txInfNode) ?: null;
                $cxlRsnAddtl = $xpath->evaluate("string({$p}CxlRsnInf/{$p}AddtlInf)", $txInfNode) ?: null;
                $dbtrNm = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}Dbtr/{$p}Nm)", $txInfNode) ?: null;
                $dbtrIban = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}DbtrAcct/{$p}Id/{$p}IBAN)", $txInfNode) ?: null;
                $dbtrBic = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}DbtrAgt/{$p}FinInstnId/{$p}BICFI)", $txInfNode) ?: null;
                $cdtrNm = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}Cdtr/{$p}Nm)", $txInfNode) ?: null;
                $cdtrIban = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}CdtrAcct/{$p}Id/{$p}IBAN)", $txInfNode) ?: null;
                $cdtrBic = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}CdtrAgt/{$p}FinInstnId/{$p}BICFI)", $txInfNode) ?: null;
                $rmtInf = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}RmtInf/{$p}Ustrd)", $txInfNode) ?: null;

                $underlying->addTransactionInformation(new Camt055CancellationRequest(
                    cancellationId: $cxlId,
                    originalInstructionId: $orgnlInstrId,
                    originalEndToEndId: $orgnlEndToEndId,
                    originalTransactionId: $orgnlTxId,
                    originalAmount: $orgnlAmt,
                    originalCurrency: $orgnlCcy,
                    requestedExecutionDate: $reqExecDt,
                    cancellationReasonCode: $cxlRsnCd,
                    cancellationReasonProprietary: $cxlRsnPrtry,
                    cancellationReasonAdditionalInfo: $cxlRsnAddtl,
                    debtorName: $dbtrNm,
                    debtorIban: $dbtrIban,
                    debtorBic: $dbtrBic,
                    creditorName: $cdtrNm,
                    creditorIban: $cdtrIban,
                    creditorBic: $cdtrBic,
                    remittanceInformation: $rmtInf
                ));
            }

            $document->addUnderlyingTransaction($underlying);
        }

        libxml_clear_errors();
        return $document;
    }

    /**
     * Parst ein CAMT.056 Dokument (FI To FI Payment Cancellation Request).
     */
    public static function parseCamt056(string $xmlContent): Camt056Document {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException("Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler'));
        }

        [$xpath, $p] = static::initializeXPath($dom, 'camt.056');

        // Assignment (enthält messageId und creationDateTime)
        $msgId = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Id)");
        $creDtTm = $xpath->evaluate("string(//{$p}Assgnmt/{$p}CreDtTm)");
        $instgAgtBic = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgnr/{$p}Agt/{$p}FinInstnId/{$p}BICFI)") ?: null;
        $instdAgtBic = $xpath->evaluate("string(//{$p}Assgnmt/{$p}Assgne/{$p}Agt/{$p}FinInstnId/{$p}BICFI)") ?: null;

        // Case
        $caseId = $xpath->evaluate("string(//{$p}Case/{$p}Id)") ?: null;
        $caseCreator = $xpath->evaluate("string(//{$p}Case/{$p}Cretr/{$p}Pty/{$p}Nm)") ?: null;

        // Control Data
        $nbOfTxs = $xpath->evaluate("string(//{$p}CtrlData/{$p}NbOfTxs)") ?: null;
        $ctrlSum = $xpath->evaluate("string(//{$p}CtrlData/{$p}CtrlSum)") ?: null;

        $document = new Camt056Document(
            messageId: $msgId,
            creationDateTime: $creDtTm,
            numberOfTransactions: $nbOfTxs,
            controlSum: $ctrlSum,
            instructingAgentBic: $instgAgtBic,
            instructedAgentBic: $instdAgtBic,
            caseId: $caseId,
            caseCreator: $caseCreator
        );

        // Underlying Transactions parsen
        foreach ($xpath->query("//{$p}Undrlyg") as $undrlygNode) {
            $orgnlGrpMsgId = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlMsgId)", $undrlygNode) ?: null;
            $orgnlGrpMsgNmId = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlMsgNmId)", $undrlygNode) ?: null;
            $orgnlGrpCreDtTm = $xpath->evaluate("string({$p}OrgnlGrpInfAndCxl/{$p}OrgnlCreDtTm)", $undrlygNode) ?: null;

            $underlying = new Camt056UnderlyingTransaction(
                originalGroupInformationMessageId: $orgnlGrpMsgId,
                originalGroupInformationMessageNameId: $orgnlGrpMsgNmId,
                originalGroupInformationCreationDateTime: $orgnlGrpCreDtTm
            );

            // TxInf parsen
            foreach ($xpath->query("{$p}TxInf", $undrlygNode) as $txInfNode) {
                $cxlId = $xpath->evaluate("string({$p}CxlId)", $txInfNode) ?: null;
                $orgnlInstrId = $xpath->evaluate("string({$p}OrgnlInstrId)", $txInfNode) ?: null;
                $orgnlEndToEndId = $xpath->evaluate("string({$p}OrgnlEndToEndId)", $txInfNode) ?: null;
                $orgnlTxId = $xpath->evaluate("string({$p}OrgnlTxId)", $txInfNode) ?: null;
                $orgnlSttlmAmt = $xpath->evaluate("string({$p}OrgnlIntrBkSttlmAmt)", $txInfNode) ?: null;
                $orgnlCcy = $xpath->evaluate("string({$p}OrgnlIntrBkSttlmAmt/@Ccy)", $txInfNode) ?: null;
                $orgnlSttlmDt = $xpath->evaluate("string({$p}OrgnlIntrBkSttlmDt)", $txInfNode) ?: null;
                $cxlRsnCd = $xpath->evaluate("string({$p}CxlRsnInf/{$p}Rsn/{$p}Cd)", $txInfNode) ?: null;
                $cxlRsnPrtry = $xpath->evaluate("string({$p}CxlRsnInf/{$p}Rsn/{$p}Prtry)", $txInfNode) ?: null;
                $cxlRsnAddtl = $xpath->evaluate("string({$p}CxlRsnInf/{$p}AddtlInf)", $txInfNode) ?: null;
                $dbtrNm = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}Dbtr/{$p}Nm)", $txInfNode) ?: null;
                $dbtrIban = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}DbtrAcct/{$p}Id/{$p}IBAN)", $txInfNode) ?: null;
                $dbtrBic = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}DbtrAgt/{$p}FinInstnId/{$p}BICFI)", $txInfNode) ?: null;
                $cdtrNm = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}Cdtr/{$p}Nm)", $txInfNode) ?: null;
                $cdtrIban = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}CdtrAcct/{$p}Id/{$p}IBAN)", $txInfNode) ?: null;
                $cdtrBic = $xpath->evaluate("string({$p}OrgnlTxRef/{$p}CdtrAgt/{$p}FinInstnId/{$p}BICFI)", $txInfNode) ?: null;

                $underlying->addTransactionInformation(new Camt056CancellationRequest(
                    originalEndToEndId: $orgnlEndToEndId,
                    originalInstructionId: $orgnlInstrId,
                    originalTransactionId: $orgnlTxId,
                    originalInterbankSettlementAmount: $orgnlSttlmAmt,
                    originalCurrency: $orgnlCcy,
                    originalInterbankSettlementDate: $orgnlSttlmDt,
                    cancellationReasonCode: $cxlRsnCd,
                    cancellationReasonProprietary: $cxlRsnPrtry,
                    cancellationReasonAdditionalInfo: $cxlRsnAddtl,
                    debtorName: $dbtrNm,
                    debtorIban: $dbtrIban,
                    debtorBic: $dbtrBic,
                    creditorName: $cdtrNm,
                    creditorIban: $cdtrIban,
                    creditorBic: $cdtrBic
                ));
            }

            $document->addUnderlyingTransaction($underlying);
        }

        libxml_clear_errors();
        return $document;
    }

    /**
     * Gibt den erkannten CAMT-Typ zurück.
     */
    public static function detectType(string $xmlContent): ?CamtType {
        return CamtType::fromXml($xmlContent);
    }
}