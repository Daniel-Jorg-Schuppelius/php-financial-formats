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

namespace CommonToolkit\FinancialFormats\Parsers\ISO20022;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Iso20022ParserAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance as CamtBalance;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\FinancialInstitutionIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\GenericIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\CancellationDetails as Camt029CancellationDetails;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\CancellationStatus as Camt029CancellationStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\Document as Camt029Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\OriginalGroupInformationAndStatus as Camt029GroupStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\TransactionInformationAndStatus as Camt029TxStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document as Camt052Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Transaction as Camt052Transaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document as Camt053Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Reference as Camt053Reference;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Transaction as Camt053Transaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document as Camt054Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Transaction as Camt054Transaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\Document as Camt055Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\PaymentCancellationRequest as Camt055CancellationRequest;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\UnderlyingTransaction as Camt055UnderlyingTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\Document as Camt056Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\PaymentCancellationRequest as Camt056CancellationRequest;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\UnderlyingTransaction as Camt056UnderlyingTransaction;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Helper\Data\CamtValidator;
use CommonToolkit\FinancialFormats\Registries\CamtParserRegistry;
use CommonToolkit\Helper\FileSystem\File;
use DateTimeImmutable;
use DOMNode;
use DOMXPath;
use RuntimeException;

/**
 * Generic parser for CAMT documents.
 * 
 * Erkennt automatisch den CAMT-Typ und gibt das entsprechende
 * Document object. Supports CAMT 052, 053, 054 directly
 * und delegiert andere Typen an den CamtReflectionParser.
 * 
 * Uses CamtParserHelper for common XML parsing functionality.
 * 
 * @package CommonToolkit\Parsers
 */
class CamtParser extends Iso20022ParserAbstract {

    /**
     * Parst ein beliebiges CAMT-Dokument.
     * 
     * @param string $xmlContent XML-Inhalt
     * @param bool $validate Optional: Perform XSD validation
     * @return CamtDocumentInterface Geparstes Dokument
     * @throws RuntimeException On invalid XML oder unbekanntem Typ
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
     * @param bool $validate Optional: Perform XSD validation
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
        ['doc' => $doc, 'prefix' => $p] = static::createIso20022Document($xmlContent, 'camt.053');
        $xpath = $doc->getXPath();

        // Statement-Block finden
        $stmtNode = $xpath->query("//{$p}Stmt")->item(0);
        if (!$stmtNode) {
            throw new RuntimeException("Kein <Stmt>-Block gefunden.");
        }

        // Basisdaten
        $currency = $doc->xpathString("{$p}Acct/{$p}Ccy", $stmtNode) ?? 'EUR';

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

        // Reporting Source
        $reportingSource = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}RptgSrc/{$p}Cd",
            "{$p}RptgSrc/{$p}Prtry"
        ], $stmtNode);

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
            reportingSource: $reportingSource,
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
        ['doc' => $doc, 'prefix' => $p] = static::createIso20022Document($xmlContent, 'camt.052');
        $xpath = $doc->getXPath();

        // Report-Block finden
        $rptNode = $xpath->query("//{$p}Rpt")->item(0);
        if (!$rptNode) {
            throw new RuntimeException("Kein <Rpt>-Block gefunden.");
        }

        // Basisdaten
        $currency = $doc->xpathString("{$p}Acct/{$p}Ccy", $rptNode) ?? 'EUR';

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

        // Reporting Source
        $reportingSource = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}RptgSrc/{$p}Cd",
            "{$p}RptgSrc/{$p}Prtry"
        ], $rptNode);

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
            reportingSource: $reportingSource,
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
        ['doc' => $doc, 'prefix' => $p] = static::createIso20022Document($xmlContent, 'camt.054');
        $xpath = $doc->getXPath();

        // Notification-Block finden
        $ntfctnNode = $xpath->query("//{$p}Ntfctn")->item(0);
        if (!$ntfctnNode) {
            throw new RuntimeException("Kein <Ntfctn>-Block gefunden.");
        }

        // Basisdaten
        $currency = $doc->xpathString("{$p}Acct/{$p}Ccy", $ntfctnNode) ?? 'EUR';

        // Reporting Source
        $reportingSource = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}RptgSrc/{$p}Cd",
            "{$p}RptgSrc/{$p}Prtry"
        ], $ntfctnNode);

        $document = new Camt054Document(
            id: $xpath->evaluate("string({$p}Id)", $ntfctnNode),
            creationDateTime: $xpath->evaluate("string(//{$p}GrpHdr/{$p}CreDtTm)") ?: 'now',
            accountIdentifier: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}IBAN)", $ntfctnNode)
                ?: $xpath->evaluate("string({$p}Acct/{$p}Id/{$p}Othr/{$p}Id)", $ntfctnNode),
            currency: $currency,
            accountOwner: $xpath->evaluate("string({$p}Acct/{$p}Ownr/{$p}Id/{$p}OrgId/{$p}AnyBIC)", $ntfctnNode) ?: null,
            messageId: $xpath->evaluate("string(//{$p}GrpHdr/{$p}MsgId)") ?: null,
            reportingSource: $reportingSource
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
     * Parses a balance block with dynamic prefix.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $balNode Der Balance-Node
     * @param string $defaultCurrency Default currency
     * @param string $p Namespace prefix
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
     * Parses common entry base data for CAMT 052/053/054.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $entry Entry-Node
     * @param string $defaultCurrency Default currency
     * @param string $p Namespace prefix
     * @return array{amount: float, currency: CurrencyCode, creditDebit: CreditDebit, bookingDate: ?DateTimeImmutable, valutaDate: ?DateTimeImmutable, status: string, isReversal: bool, entryRef: ?string, acctSvcrRef: ?string, bankTxCode: ?string, domainCode: ?string, familyCode: ?string, subFamilyCode: ?string, txDtls: ?DOMNode}|null
     */
    private static function parseEntryBasics(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?array {
        $amountStr = $xpath->evaluate("string({$p}Amt)", $entry);
        if (empty($amountStr)) {
            return null;
        }

        $amount = static::parseAmountValue($amountStr);
        $entryCcy = $xpath->evaluate("string({$p}Amt/@Ccy)", $entry) ?: $defaultCurrency;
        $creditDebitStr = $xpath->evaluate("string({$p}CdtDbtInd)", $entry);
        $reversalIndicator = $xpath->evaluate("string({$p}RvslInd)", $entry);
        $status = $xpath->evaluate("string({$p}Sts/{$p}Cd)", $entry) ?: 'BOOK';

        // Buchungs- und Valutadatum (Reihenfolge: DtTm vor Dt für genauere Zeitangaben)
        $bookingDateStr = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}BookgDt/{$p}DtTm",
            "{$p}BookgDt/{$p}Dt"
        ], $entry);
        $valutaDateStr = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}ValDt/{$p}DtTm",
            "{$p}ValDt/{$p}Dt"
        ], $entry);

        if (empty($bookingDateStr)) {
            return null;
        }

        $isReversal = static::isReversalIndicator($reversalIndicator);
        $creditDebit = static::parseCreditDebitIndicator($creditDebitStr);

        // Bei Storno wird CreditDebit invertiert
        if ($isReversal) {
            $creditDebit = $creditDebit === CreditDebit::CREDIT ? CreditDebit::DEBIT : CreditDebit::CREDIT;
        }

        $currency = CurrencyCode::tryFrom(strtoupper($entryCcy)) ?? CurrencyCode::Euro;

        // Referenzen
        $entryRef = static::xpathStringStatic($xpath, "{$p}NtryRef", $entry);
        $acctSvcrRef = static::xpathStringStatic($xpath, "{$p}AcctSvcrRef", $entry);

        // Bank Transaction Codes
        $bankTxCode = static::parseBankTxCode($xpath, $entry, $p);

        // TxDtls Block
        $txDtls = $xpath->query("{$p}NtryDtls/{$p}TxDtls", $entry)->item(0);

        // Technical Input Channel (on Entry level)
        $techInputChannel = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}TechInptChanl/{$p}Cd",
            "{$p}TechInptChanl/{$p}Prtry"
        ], $entry);

        return [
            'amount' => $amount,
            'currency' => $currency,
            'creditDebit' => $creditDebit,
            'bookingDate' => new DateTimeImmutable($bookingDateStr),
            'valutaDate' => !empty($valutaDateStr) ? new DateTimeImmutable($valutaDateStr) : null,
            'status' => $status,
            'isReversal' => $isReversal,
            'entryRef' => $entryRef,
            'acctSvcrRef' => $acctSvcrRef,
            'bankTxCode' => $bankTxCode['code'],
            'domainCode' => $bankTxCode['domain'],
            'familyCode' => $bankTxCode['family'],
            'subFamilyCode' => $bankTxCode['subFamily'],
            'technicalInputChannel' => $techInputChannel,
            'txDtls' => $txDtls,
        ];
    }

    /**
     * Parses an entry block for CAMT.053 with dynamic prefix.
     */
    private static function parseTransaction053WithPrefix(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?Camt053Transaction {
        $basics = self::parseEntryBasics($xpath, $entry, $defaultCurrency, $p);
        if ($basics === null) {
            return null;
        }

        // CAMT.053 spezifische Felder aus TxDtls
        $txDtls = $basics['txDtls'];
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
        $debtor = null;
        $creditor = null;
        $debtorAgent = null;
        $creditorAgent = null;

        if ($txDtls) {
            // Referenzen parsen
            $endToEndId = static::xpathStringStatic($xpath, "{$p}Refs/{$p}EndToEndId", $txDtls);
            $mandateId = static::xpathStringStatic($xpath, "{$p}Refs/{$p}MndtId", $txDtls);
            $paymentInformationId = static::xpathStringStatic($xpath, "{$p}Refs/{$p}PmtInfId", $txDtls);
            $instructionId = static::xpathStringStatic($xpath, "{$p}Refs/{$p}InstrId", $txDtls);

            // Gläubiger-ID kann im RmtInf/Strd/CdtrRefInf stehen
            $creditorId = static::xpathStringWithFallbackStatic($xpath, [
                "{$p}RltdPties/{$p}Cdtr/{$p}Id/{$p}PrvtId/{$p}Othr/{$p}Id",
                "{$p}RmtInf/{$p}Strd/{$p}CdtrRefInf/{$p}Ref"
            ], $txDtls);

            // Purpose
            $purpose = static::xpathStringStatic($xpath, "{$p}Purp/{$p}Prtry", $txDtls);
            $purposeCode = static::xpathStringStatic($xpath, "{$p}Purp/{$p}Cd", $txDtls);

            // Remittance Info (kann mehrere Ustrd-Elemente haben)
            $additionalInfo = static::xpathMultiStringStatic($xpath, "{$p}RmtInf/{$p}Ustrd", $txDtls);

            // Return Reason
            $returnReason = static::xpathStringStatic($xpath, "{$p}RtrInf/{$p}Rsn/{$p}Cd", $txDtls);

            // Counterparty - je nach Credit/Debit via Hilfsmethode
            $partyType = $basics['creditDebit'] === CreditDebit::CREDIT ? 'Dbtr' : 'Cdtr';
            $counterparty = static::parsePartyInfo($xpath, $txDtls, $partyType, $p);
            $counterpartyName = $counterparty['name'];
            $counterpartyIban = $counterparty['iban'];
            $counterpartyBic = $counterparty['bic'];

            // Full party identifications
            $debtor = self::parsePartyIdentificationFromPath($xpath, $txDtls, "RltdPties/{$p}Dbtr", $p);
            $creditor = self::parsePartyIdentificationFromPath($xpath, $txDtls, "RltdPties/{$p}Cdtr", $p);
            $debtorAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}DbtrAgt", $p);
            $creditorAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}CdtrAgt", $p);
        }

        // Fallback für Additional Info
        if ($additionalInfo === null) {
            $additionalInfo = static::xpathStringStatic($xpath, "{$p}AddtlNtryInf", $entry);
        }

        // Reference-Objekt erstellen
        $reference = new Camt053Reference(
            endToEndId: $endToEndId,
            mandateId: $mandateId,
            creditorId: $creditorId,
            entryReference: $basics['entryRef'],
            accountServicerReference: $basics['acctSvcrRef'],
            paymentInformationId: $paymentInformationId,
            instructionId: $instructionId
        );

        return new Camt053Transaction(
            bookingDate: $basics['bookingDate'],
            valutaDate: $basics['valutaDate'],
            amount: $basics['amount'],
            currency: $basics['currency'],
            creditDebit: $basics['creditDebit'],
            reference: $reference,
            entryReference: $basics['entryRef'],
            accountServicerReference: $basics['acctSvcrRef'],
            status: $basics['status'],
            isReversal: $basics['isReversal'],
            purpose: $purpose,
            purposeCode: $purposeCode,
            additionalInfo: $additionalInfo,
            transactionCode: $basics['bankTxCode'],
            domainCode: $basics['domainCode'],
            familyCode: $basics['familyCode'],
            subFamilyCode: $basics['subFamilyCode'],
            returnReason: $returnReason,
            technicalInputChannel: $basics['technicalInputChannel'],
            counterpartyName: $counterpartyName,
            counterpartyIban: $counterpartyIban,
            counterpartyBic: $counterpartyBic,
            debtor: $debtor ?? null,
            creditor: $creditor ?? null,
            debtorAgent: $debtorAgent ?? null,
            creditorAgent: $creditorAgent ?? null
        );
    }

    /**
     * Parses an entry block for CAMT.052 with dynamic prefix.
     */
    private static function parseTransaction052WithPrefix(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?Camt052Transaction {
        $basics = self::parseEntryBasics($xpath, $entry, $defaultCurrency, $p);
        if ($basics === null) {
            return null;
        }

        // CAMT.052 spezifische Felder aus TxDtls
        $txDtls = $basics['txDtls'];
        $purpose = null;
        $purposeCode = null;
        $additionalInfo = null;
        $remittanceInfo = null;
        $returnReason = null;
        $counterpartyName = null;
        $counterpartyIban = null;
        $counterpartyBic = null;
        $debtor = null;
        $creditor = null;
        $debtorAgent = null;
        $creditorAgent = null;

        if ($txDtls) {
            $purpose = static::xpathStringStatic($xpath, "{$p}Purp/{$p}Prtry", $txDtls);
            $purposeCode = static::xpathStringStatic($xpath, "{$p}Purp/{$p}Cd", $txDtls);
            // Remittance Info (kann mehrere Ustrd-Elemente haben)
            $remittanceInfo = static::xpathMultiStringStatic($xpath, "{$p}RmtInf/{$p}Ustrd", $txDtls);
            $additionalInfo = static::xpathStringStatic($xpath, "{$p}AddtlTxInf", $txDtls);
            $returnReason = static::xpathStringStatic($xpath, "{$p}RtrInf/{$p}Rsn/{$p}Cd", $txDtls);

            // Counterparty - je nach Credit/Debit
            $partyType = $basics['creditDebit'] === CreditDebit::CREDIT ? 'Dbtr' : 'Cdtr';
            $counterparty = static::parsePartyInfo($xpath, $txDtls, $partyType, $p);
            $counterpartyName = $counterparty['name'];
            $counterpartyIban = $counterparty['iban'];
            $counterpartyBic = $counterparty['bic'];

            // Full party identifications
            $debtor = self::parsePartyIdentificationFromPath($xpath, $txDtls, "RltdPties/{$p}Dbtr", $p);
            $creditor = self::parsePartyIdentificationFromPath($xpath, $txDtls, "RltdPties/{$p}Cdtr", $p);
            $debtorAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}DbtrAgt", $p);
            $creditorAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}CdtrAgt", $p);
        }

        // Fallback: Wenn keine remittanceInfo, verwende additionalInfo
        if ($remittanceInfo === null && $additionalInfo === null) {
            $additionalInfo = static::xpathStringStatic($xpath, "{$p}AddtlNtryInf", $entry);
        }

        // Kombiniere remittanceInfo und additionalInfo für das additionalInfo-Feld
        $combinedInfo = $remittanceInfo;
        if ($combinedInfo === null) {
            $combinedInfo = $additionalInfo;
        } elseif ($additionalInfo !== null && $additionalInfo !== $remittanceInfo) {
            $combinedInfo = $remittanceInfo . ' ' . $additionalInfo;
        }

        return new Camt052Transaction(
            bookingDate: $basics['bookingDate'],
            valutaDate: $basics['valutaDate'],
            amount: $basics['amount'],
            currency: $basics['currency'],
            creditDebit: $basics['creditDebit'],
            entryReference: $basics['entryRef'],
            accountServicerReference: $basics['acctSvcrRef'],
            status: $basics['status'],
            isReversal: $basics['isReversal'],
            purpose: $purpose,
            purposeCode: $purposeCode,
            additionalInfo: $combinedInfo,
            bankTransactionCode: $basics['bankTxCode'],
            domainCode: $basics['domainCode'],
            familyCode: $basics['familyCode'],
            subFamilyCode: $basics['subFamilyCode'],
            returnReason: $returnReason,
            technicalInputChannel: $basics['technicalInputChannel'],
            counterpartyName: $counterpartyName,
            counterpartyIban: $counterpartyIban,
            counterpartyBic: $counterpartyBic,
            debtor: $debtor,
            creditor: $creditor,
            debtorAgent: $debtorAgent,
            creditorAgent: $creditorAgent
        );
    }

    /**
     * Parses an entry block for CAMT.054 with dynamic prefix.
     */
    private static function parseTransaction054WithPrefix(DOMXPath $xpath, DOMNode $entry, string $defaultCurrency, string $p): ?Camt054Transaction {
        $basics = self::parseEntryBasics($xpath, $entry, $defaultCurrency, $p);
        if ($basics === null) {
            return null;
        }

        // CAMT.054 spezifische Felder aus TxDtls
        $txDtls = $basics['txDtls'];
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
        $debtor = null;
        $creditor = null;
        $debtorAgent = null;
        $creditorAgent = null;
        $instructingAgent = null;
        $instructedAgent = null;

        if ($txDtls) {
            $instructionId = static::xpathStringStatic($xpath, "{$p}Refs/{$p}InstrId", $txDtls);
            $endToEndId = static::xpathStringStatic($xpath, "{$p}Refs/{$p}EndToEndId", $txDtls);
            // Remittance Info (kann mehrere Ustrd-Elemente haben)
            $remittanceInfo = static::xpathMultiStringStatic($xpath, "{$p}RmtInf/{$p}Ustrd", $txDtls);
            $purposeCode = static::xpathStringStatic($xpath, "{$p}Purp/{$p}Cd", $txDtls);
            $returnReason = static::xpathStringStatic($xpath, "{$p}RtrInf/{$p}Rsn/{$p}Cd", $txDtls);
            $localInstrumentCode = static::xpathStringWithFallbackStatic($xpath, [
                "{$p}LclInstrm/{$p}Prtry",
                "{$p}LclInstrm/{$p}Cd"
            ], $txDtls);

            // Agent BICs via Hilfsmethode
            $instructingAgentBic = static::xpathStringStatic($xpath, "{$p}RltdAgts/{$p}InstgAgt/{$p}FinInstnId/{$p}BICFI", $txDtls);
            $instructedAgentBic = static::xpathStringStatic($xpath, "{$p}RltdAgts/{$p}InstdAgt/{$p}FinInstnId/{$p}BICFI", $txDtls);
            $debtorAgentBic = static::xpathStringStatic($xpath, "{$p}RltdAgts/{$p}DbtrAgt/{$p}FinInstnId/{$p}BICFI", $txDtls);
            $creditorAgentBic = static::xpathStringStatic($xpath, "{$p}RltdAgts/{$p}CdtrAgt/{$p}FinInstnId/{$p}BICFI", $txDtls);

            // Full party identifications
            $debtor = self::parsePartyIdentificationFromPath($xpath, $txDtls, "RltdPties/{$p}Dbtr", $p);
            $creditor = self::parsePartyIdentificationFromPath($xpath, $txDtls, "RltdPties/{$p}Cdtr", $p);
            $debtorAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}DbtrAgt", $p);
            $creditorAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}CdtrAgt", $p);
            $instructingAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}InstgAgt", $p);
            $instructedAgent = self::parseFinancialInstitutionIdentification($xpath, $txDtls, "RltdAgts/{$p}InstdAgt", $p);
        }

        return new Camt054Transaction(
            bookingDate: $basics['bookingDate'],
            valutaDate: $basics['valutaDate'],
            amount: $basics['amount'],
            currency: $basics['currency'],
            creditDebit: $basics['creditDebit'],
            entryReference: $basics['entryRef'],
            accountServicerReference: $basics['acctSvcrRef'],
            status: $basics['status'],
            isReversal: $basics['isReversal'],
            instructionId: $instructionId,
            endToEndId: $endToEndId,
            remittanceInfo: $remittanceInfo,
            purposeCode: $purposeCode,
            bankTransactionCode: $basics['bankTxCode'],
            domainCode: $basics['domainCode'],
            familyCode: $basics['familyCode'],
            subFamilyCode: $basics['subFamilyCode'],
            returnReason: $returnReason,
            technicalInputChannel: $basics['technicalInputChannel'],
            localInstrumentCode: $localInstrumentCode,
            instructingAgentBic: $instructingAgentBic,
            instructedAgentBic: $instructedAgentBic,
            debtorAgentBic: $debtorAgentBic,
            creditorAgentBic: $creditorAgentBic,
            debtor: $debtor,
            creditor: $creditor,
            debtorAgent: $debtorAgent,
            creditorAgent: $creditorAgent,
            instructingAgent: $instructingAgent,
            instructedAgent: $instructedAgent
        );
    }

    // =========================================================================
    // PARTY IDENTIFICATION PARSING METHODS
    // =========================================================================

    /**
     * Parses full PartyIdentification from XML node.
     * 
     * @param DOMXPath $xpath XPath object
     * @param DOMNode $context Context node
     * @param string $partyPath Path to party element (e.g., "RltdPties/Dbtr")
     * @param string $p Namespace prefix
     * @return PartyIdentification|null
     */
    private static function parsePartyIdentificationFromPath(DOMXPath $xpath, DOMNode $context, string $partyPath, string $p): ?PartyIdentification {
        $partyNode = $xpath->query("{$p}{$partyPath}", $context)->item(0);
        if (!$partyNode) {
            return null;
        }

        $name = static::xpathStringStatic($xpath, "{$p}Nm", $partyNode);
        $bicOrBei = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}Id/{$p}OrgId/{$p}AnyBIC",
            "{$p}Id/{$p}OrgId/{$p}LEI"
        ], $partyNode);

        // Organisation identification
        $orgId = null;
        $orgIdValue = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}Id/{$p}OrgId/{$p}Othr/{$p}Id",
        ], $partyNode);
        if ($orgIdValue) {
            $orgSchemeCode = static::xpathStringStatic($xpath, "{$p}Id/{$p}OrgId/{$p}Othr/{$p}SchmeNm/{$p}Cd", $partyNode);
            $orgSchemeProprietary = static::xpathStringStatic($xpath, "{$p}Id/{$p}OrgId/{$p}Othr/{$p}SchmeNm/{$p}Prtry", $partyNode);
            $orgIssuer = static::xpathStringStatic($xpath, "{$p}Id/{$p}OrgId/{$p}Othr/{$p}Issr", $partyNode);
            $orgId = new GenericIdentification($orgIdValue, $orgSchemeCode, $orgSchemeProprietary, $orgIssuer);
        }

        // Person identification
        $personId = null;
        $birthDate = null;
        $birthPlace = null;
        $personIdValue = static::xpathStringStatic($xpath, "{$p}Id/{$p}PrvtId/{$p}Othr/{$p}Id", $partyNode);
        if ($personIdValue) {
            $personSchemeCode = static::xpathStringStatic($xpath, "{$p}Id/{$p}PrvtId/{$p}Othr/{$p}SchmeNm/{$p}Cd", $partyNode);
            $personSchemeProprietary = static::xpathStringStatic($xpath, "{$p}Id/{$p}PrvtId/{$p}Othr/{$p}SchmeNm/{$p}Prtry", $partyNode);
            $personIssuer = static::xpathStringStatic($xpath, "{$p}Id/{$p}PrvtId/{$p}Othr/{$p}Issr", $partyNode);
            $personId = new GenericIdentification($personIdValue, $personSchemeCode, $personSchemeProprietary, $personIssuer);

            // Birth data
            $birthDateStr = static::xpathStringStatic($xpath, "{$p}Id/{$p}PrvtId/{$p}DtAndPlcOfBirth/{$p}BirthDt", $partyNode);
            $birthDate = $birthDateStr ? new \DateTimeImmutable($birthDateStr) : null;
            $birthPlace = static::xpathStringStatic($xpath, "{$p}Id/{$p}PrvtId/{$p}DtAndPlcOfBirth/{$p}CityOfBirth", $partyNode);
        }

        // Only return if we have at least a name or identification
        if ($name === null && $bicOrBei === null && $orgId === null && $personId === null) {
            return null;
        }

        return new PartyIdentification(
            name: $name,
            bicOrBei: $bicOrBei,
            organisationId: $orgId,
            birthDate: $birthDate,
            birthPlace: $birthPlace,
            personId: $personId
        );
    }

    /**
     * Parses full FinancialInstitutionIdentification from XML node.
     * 
     * @param DOMXPath $xpath XPath object
     * @param DOMNode $context Context node
     * @param string $agentPath Path to agent element (e.g., "RltdAgts/DbtrAgt")
     * @param string $p Namespace prefix
     * @return FinancialInstitutionIdentification|null
     */
    private static function parseFinancialInstitutionIdentification(DOMXPath $xpath, DOMNode $context, string $agentPath, string $p): ?FinancialInstitutionIdentification {
        $agentNode = $xpath->query("{$p}{$agentPath}/{$p}FinInstnId", $context)->item(0);
        if (!$agentNode) {
            return null;
        }

        $bic = static::xpathStringWithFallbackStatic($xpath, [
            "{$p}BICFI",
            "{$p}BIC"
        ], $agentNode);

        // Clearing system identification
        $clearingSystemCode = static::xpathStringStatic($xpath, "{$p}ClrSysMmbId/{$p}ClrSysId/{$p}Cd", $agentNode);
        $clearingMemberId = static::xpathStringStatic($xpath, "{$p}ClrSysMmbId/{$p}MmbId", $agentNode);

        // Only return if we have at least BIC or clearing info
        if ($bic === null && $clearingSystemCode === null && $clearingMemberId === null) {
            return null;
        }

        return new FinancialInstitutionIdentification(
            bic: $bic,
            clearingSystemCode: $clearingSystemCode,
            clearingMemberId: $clearingMemberId
        );
    }

    /**
     * Parst ein CAMT.029 Dokument (Resolution of Investigation).
     */
    public static function parseCamt029(string $xmlContent): Camt029Document {
        ['doc' => $doc, 'prefix' => $p] = static::createIso20022Document($xmlContent, 'camt.029');
        $xpath = $doc->getXPath();

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
        ['doc' => $doc, 'prefix' => $p] = static::createIso20022Document($xmlContent, 'camt.055');
        $xpath = $doc->getXPath();

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
                // Remittance Info (kann mehrere Ustrd-Elemente haben)
                $rmtInf = static::xpathMultiStringStatic($xpath, "{$p}OrgnlTxRef/{$p}RmtInf/{$p}Ustrd", $txInfNode);

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
        ['doc' => $doc, 'prefix' => $p] = static::createIso20022Document($xmlContent, 'camt.056');
        $xpath = $doc->getXPath();

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
     * Returns the detected CAMT type.
     */
    public static function detectType(string $xmlContent): ?CamtType {
        return CamtType::fromXml($xmlContent);
    }
}