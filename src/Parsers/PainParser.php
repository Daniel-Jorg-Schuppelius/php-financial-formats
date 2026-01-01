<?php
/*
 * Created on   : Thu Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainParser.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Parsers;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Iso20022ParserAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\PainDocumentInterface;
use CommonToolkit\FinancialFormats\Entities\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\Pain\Mandate\Mandate;
use CommonToolkit\FinancialFormats\Entities\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\Pain\RemittanceInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type001\CreditTransferTransaction;
use CommonToolkit\FinancialFormats\Entities\Pain\Type001\Document as Pain001Document;
use CommonToolkit\FinancialFormats\Entities\Pain\Type001\GroupHeader as Pain001GroupHeader;
use CommonToolkit\FinancialFormats\Entities\Pain\Type001\PaymentInstruction as Pain001PaymentInstruction;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\Document as Pain002Document;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\GroupHeader as Pain002GroupHeader;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\OriginalGroupInformation as Pain002OriginalGroupInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\OriginalPaymentInformation as Pain002OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\StatusReason;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\TransactionInformationAndStatus;
use CommonToolkit\FinancialFormats\Entities\Pain\Type002\TransactionStatus;
use CommonToolkit\FinancialFormats\Entities\Pain\Type007\Document as Pain007Document;
use CommonToolkit\FinancialFormats\Entities\Pain\Type007\GroupHeader as Pain007GroupHeader;
use CommonToolkit\FinancialFormats\Entities\Pain\Type007\OriginalGroupInformation as Pain007OriginalGroupInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type007\OriginalPaymentInformation as Pain007OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type007\ReversalReason;
use CommonToolkit\FinancialFormats\Entities\Pain\Type007\TransactionInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type008\DirectDebitTransaction;
use CommonToolkit\FinancialFormats\Entities\Pain\Type008\Document as Pain008Document;
use CommonToolkit\FinancialFormats\Entities\Pain\Type008\GroupHeader as Pain008GroupHeader;
use CommonToolkit\FinancialFormats\Entities\Pain\Type008\MandateInformation;
use CommonToolkit\FinancialFormats\Entities\Pain\Type008\PaymentInstruction as Pain008PaymentInstruction;
use CommonToolkit\FinancialFormats\Entities\Pain\Type009\Document as Pain009Document;
use CommonToolkit\FinancialFormats\Enums\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\PainType;
use CommonToolkit\FinancialFormats\Enums\PaymentMethod;
use CommonToolkit\FinancialFormats\Enums\SequenceType;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Helper\FileSystem\File;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

/**
 * Generischer Parser für Pain-Dokumente.
 * 
 * Erkennt automatisch den Pain-Typ und gibt das entsprechende
 * Document-Objekt zurück.
 * 
 * Unterstützte Formate:
 * - pain.001 (Customer Credit Transfer Initiation)
 * - pain.002 (Customer Payment Status Report)
 * - pain.007 (Customer Payment Reversal)
 * - pain.008 (Customer Direct Debit Initiation)
 * - pain.009 (Mandate Initiation Request)
 * 
 * @package CommonToolkit\FinancialFormats\Parsers
 */
class PainParser extends Iso20022ParserAbstract {

    // =========================================================================
    // ÖFFENTLICHE API
    // =========================================================================

    /**
     * Parst ein Pain-Dokument und erkennt den Typ automatisch.
     * 
     * @param string $xmlContent XML-Inhalt
     * @return PainDocumentInterface Geparstes Dokument
     * @throws RuntimeException Bei ungültigem XML oder unbekanntem Typ
     */
    public static function parse(string $xmlContent): PainDocumentInterface {
        $type = PainType::fromXml($xmlContent);

        if ($type === null) {
            throw new RuntimeException('Unbekannter Pain-Dokumenttyp');
        }

        return match ($type) {
            PainType::PAIN_001 => self::parsePain001($xmlContent),
            PainType::PAIN_002 => self::parsePain002($xmlContent),
            PainType::PAIN_007 => self::parsePain007($xmlContent),
            PainType::PAIN_008 => self::parsePain008($xmlContent),
            PainType::PAIN_009 => self::parsePain009($xmlContent),
            default => throw new RuntimeException("Pain-Typ {$type->value} wird noch nicht unterstützt"),
        };
    }

    /**
     * Parst eine Pain-XML-Datei.
     * 
     * @param string $filePath Pfad zur XML-Datei
     * @return PainDocumentInterface Geparstes Dokument
     * @throws RuntimeException Bei Datei- oder Parse-Fehlern
     */
    public static function parseFile(string $filePath): PainDocumentInterface {
        $content = File::getContents($filePath);
        if ($content === false) {
            throw new RuntimeException("Datei konnte nicht gelesen werden: {$filePath}");
        }
        return self::parse($content);
    }

    /**
     * Prüft, ob ein XML ein gültiges Pain-Dokument ist.
     * 
     * @param string $xmlContent XML-Inhalt
     * @param PainType|null $expectedType Optional: Erwarteter Pain-Typ
     * @return bool True wenn gültig
     */
    public static function isValid(string $xmlContent, ?PainType $expectedType = null): bool {
        try {
            $dom = new DOMDocument();
            if (!@$dom->loadXML($xmlContent)) {
                return false;
            }

            $detectedType = PainType::fromXml($xmlContent);
            if ($detectedType === null) {
                return false;
            }

            if ($expectedType !== null && $expectedType !== $detectedType) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Ermittelt den Pain-Typ eines XML-Dokuments.
     * 
     * @param string $xmlContent XML-Inhalt
     * @return PainType|null Pain-Typ oder null
     */
    public static function detectType(string $xmlContent): ?PainType {
        return PainType::fromXml($xmlContent);
    }

    // =========================================================================
    // ALIAS-METHODEN FÜR ABWÄRTSKOMPATIBILITÄT
    // =========================================================================

    /**
     * Alias für parsePain001() - für Abwärtskompatibilität.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain001Document Das geparste Dokument
     */
    public static function fromXml001(string $xmlContent): Pain001Document {
        return self::parsePain001($xmlContent);
    }

    /**
     * Alias für parsePain002() - für Abwärtskompatibilität.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain002Document Das geparste Dokument
     */
    public static function fromXml002(string $xmlContent): Pain002Document {
        return self::parsePain002($xmlContent);
    }

    /**
     * Alias für parsePain007() - für Abwärtskompatibilität.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain007Document Das geparste Dokument
     */
    public static function fromXml007(string $xmlContent): Pain007Document {
        return self::parsePain007($xmlContent);
    }

    /**
     * Alias für parsePain008() - für Abwärtskompatibilität.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain008Document Das geparste Dokument
     */
    public static function fromXml008(string $xmlContent): Pain008Document {
        return self::parsePain008($xmlContent);
    }

    /**
     * Alias für parsePain009() - für Abwärtskompatibilität.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain009Document Das geparste Dokument
     */
    public static function fromXml009(string $xmlContent): Pain009Document {
        return self::parsePain009($xmlContent);
    }

    // =========================================================================
    // PAIN.001 - CUSTOMER CREDIT TRANSFER INITIATION
    // =========================================================================

    private const PAIN001_NAMESPACES = [
        'pain001v03' => 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03',
        'pain001v09' => 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.09',
        'pain001v12' => 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.12',
    ];

    /**
     * Parst ein pain.001 XML-Dokument.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain001Document Das geparste Dokument
     * @throws RuntimeException Bei ungültigem XML oder fehlendem Content
     */
    public static function parsePain001(string $xmlContent): Pain001Document {
        ['xpath' => $xpath, 'prefix' => $prefix] = self::createIso20022XPath($xmlContent, 'pain.001', self::PAIN001_NAMESPACES);

        // Prefix-Variante für Namespace-Suche
        $nsPrefix = !empty($prefix) ? 'ns:' : '';
        if (!empty($prefix)) {
            // Re-registrieren mit 'ns' für den speziellen Lookup
            $namespace = self::detectIso20022Namespace($xpath->document, 'pain.001', self::PAIN001_NAMESPACES);
            $xpath->registerNamespace('ns', $namespace ?? '');
        }

        // Customer Credit Transfer Initiation Block finden
        $cstmrCdtTrfInitnNode = $xpath->query("//{$nsPrefix}CstmrCdtTrfInitn")->item(0);
        if (!$cstmrCdtTrfInitnNode) {
            // Fallback ohne Namespace
            $cstmrCdtTrfInitnNode = $xpath->query('//CstmrCdtTrfInitn')->item(0);
            $prefix = '';
        }
        if (!$cstmrCdtTrfInitnNode) {
            throw new RuntimeException("Kein <CstmrCdtTrfInitn>-Block gefunden.");
        }

        // GroupHeader parsen
        $grpHdrNode = $xpath->query("{$prefix}GrpHdr", $cstmrCdtTrfInitnNode)->item(0);
        if (!$grpHdrNode) {
            throw new RuntimeException("Kein <GrpHdr>-Block gefunden.");
        }

        $groupHeader = self::parsePain001GroupHeader($xpath, $grpHdrNode, $prefix);

        // PaymentInstructions parsen
        $pmtInfNodes = $xpath->query("{$prefix}PmtInf", $cstmrCdtTrfInitnNode);
        $paymentInstructions = [];

        foreach ($pmtInfNodes as $pmtInfNode) {
            $paymentInstructions[] = self::parsePain001PaymentInstruction($xpath, $pmtInfNode, $prefix);
        }

        return new Pain001Document($groupHeader, $paymentInstructions);
    }

    private static function parsePain001GroupHeader(DOMXPath $xpath, DOMNode $node, string $prefix): Pain001GroupHeader {
        $messageId = $xpath->evaluate("string({$prefix}MsgId)", $node);
        $creationDateTimeStr = $xpath->evaluate("string({$prefix}CreDtTm)", $node);
        $numberOfTransactions = (int) $xpath->evaluate("string({$prefix}NbOfTxs)", $node);
        $controlSumStr = $xpath->evaluate("string({$prefix}CtrlSum)", $node);

        $creationDateTime = !empty($creationDateTimeStr)
            ? new DateTimeImmutable($creationDateTimeStr)
            : new DateTimeImmutable();

        $controlSum = !empty($controlSumStr) ? (float) $controlSumStr : null;

        // InitiatingParty parsen
        $initgPtyNode = $xpath->query("{$prefix}InitgPty", $node)->item(0);
        $initiatingParty = $initgPtyNode
            ? self::parseParty($xpath, $initgPtyNode, $prefix)
            : new PartyIdentification(name: 'Unknown');

        // ForwardingAgent parsen (optional)
        $fwdgAgtNode = $xpath->query("{$prefix}FwdgAgt", $node)->item(0);
        $forwardingAgent = $fwdgAgtNode
            ? self::parseFinancialInst($xpath, $fwdgAgtNode, $prefix)
            : null;

        return new Pain001GroupHeader(
            messageId: $messageId,
            creationDateTime: $creationDateTime,
            numberOfTransactions: $numberOfTransactions,
            initiatingParty: $initiatingParty,
            controlSum: $controlSum,
            forwardingAgent: $forwardingAgent
        );
    }

    private static function parsePain001PaymentInstruction(DOMXPath $xpath, DOMNode $node, string $prefix): Pain001PaymentInstruction {
        $pmtInfId = $xpath->evaluate("string({$prefix}PmtInfId)", $node);
        $pmtMtdStr = $xpath->evaluate("string({$prefix}PmtMtd)", $node);
        $reqdExctnDtStr = $xpath->evaluate("string({$prefix}ReqdExctnDt/{$prefix}Dt)", $node)
            ?: $xpath->evaluate("string({$prefix}ReqdExctnDt)", $node);
        $chrgBrStr = $xpath->evaluate("string({$prefix}ChrgBr)", $node);

        $paymentMethod = PaymentMethod::fromString($pmtMtdStr ?: 'TRF');
        $requestedExecutionDate = !empty($reqdExctnDtStr)
            ? new DateTimeImmutable($reqdExctnDtStr)
            : new DateTimeImmutable();
        $chargesCode = ChargesCode::fromString($chrgBrStr ?: 'SLEV');

        // Debtor parsen
        $dbtrNode = $xpath->query("{$prefix}Dbtr", $node)->item(0);
        $debtor = $dbtrNode
            ? self::parseParty($xpath, $dbtrNode, $prefix)
            : new PartyIdentification(name: 'Unknown');

        // DebtorAccount parsen
        $dbtrAcctNode = $xpath->query("{$prefix}DbtrAcct", $node)->item(0);
        $debtorAccount = $dbtrAcctNode
            ? self::parseAccount($xpath, $dbtrAcctNode, $prefix)
            : new AccountIdentification(iban: '');

        // DebtorAgent parsen (optional)
        $dbtrAgtNode = $xpath->query("{$prefix}DbtrAgt", $node)->item(0);
        $debtorAgent = $dbtrAgtNode
            ? self::parseFinancialInst($xpath, $dbtrAgtNode, $prefix)
            : null;

        // CreditTransferTransactions parsen
        $cdtTrfTxInfNodes = $xpath->query("{$prefix}CdtTrfTxInf", $node);
        $transactions = [];

        foreach ($cdtTrfTxInfNodes as $txnNode) {
            $transactions[] = self::parsePain001CreditTransferTransaction($xpath, $txnNode, $prefix);
        }

        return new Pain001PaymentInstruction(
            paymentInstructionId: $pmtInfId,
            paymentMethod: $paymentMethod,
            requestedExecutionDate: $requestedExecutionDate,
            debtor: $debtor,
            debtorAccount: $debtorAccount,
            debtorAgent: $debtorAgent ?? new FinancialInstitution(),
            transactions: $transactions,
            chargeBearer: $chargesCode
        );
    }

    private static function parsePain001CreditTransferTransaction(DOMXPath $xpath, DOMNode $node, string $prefix): CreditTransferTransaction {
        // PaymentIdentification parsen
        $pmtIdNode = $xpath->query("{$prefix}PmtId", $node)->item(0);
        $paymentIdentification = $pmtIdNode
            ? self::parsePaymentId($xpath, $pmtIdNode, $prefix)
            : PaymentIdentification::create('unknown');

        // Betrag und Währung parsen
        $amtData = static::parseAmountWithCcy($xpath, "{$prefix}Amt/{$prefix}InstdAmt", $node);
        if ($amtData['amount'] === 0.0) {
            // Fallback auf direktes Amt-Element
            $amtData = static::parseAmountWithCcy($xpath, "{$prefix}Amt", $node);
        }

        // Creditor parsen
        $cdtrNode = $xpath->query("{$prefix}Cdtr", $node)->item(0);
        $creditor = $cdtrNode
            ? self::parseParty($xpath, $cdtrNode, $prefix)
            : new PartyIdentification(name: 'Unknown');

        // CreditorAccount parsen
        $cdtrAcctNode = $xpath->query("{$prefix}CdtrAcct", $node)->item(0);
        $creditorAccount = $cdtrAcctNode
            ? self::parseAccount($xpath, $cdtrAcctNode, $prefix)
            : null;

        // CreditorAgent parsen (optional)
        $cdtrAgtNode = $xpath->query("{$prefix}CdtrAgt", $node)->item(0);
        $creditorAgent = $cdtrAgtNode
            ? self::parseFinancialInst($xpath, $cdtrAgtNode, $prefix)
            : null;

        // RemittanceInformation parsen (optional)
        $rmtInfNode = $xpath->query("{$prefix}RmtInf", $node)->item(0);
        $remittanceInformation = self::parseRemittance($xpath, $rmtInfNode, $prefix);

        return new CreditTransferTransaction(
            paymentId: $paymentIdentification,
            amount: $amtData['amount'],
            currency: $amtData['currency'],
            creditor: $creditor,
            creditorAccount: $creditorAccount,
            creditorAgent: $creditorAgent,
            remittanceInformation: $remittanceInformation
        );
    }

    // =========================================================================
    // PAIN.002 - CUSTOMER PAYMENT STATUS REPORT
    // =========================================================================

    /**
     * Parst ein pain.002 XML-Dokument.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain002Document Das geparste Dokument
     * @throws RuntimeException Bei ungültigem XML
     */
    public static function parsePain002(string $xmlContent): Pain002Document {
        ['xpath' => $xpath, 'prefix' => $prefix] = self::createIso20022XPath($xmlContent, 'pain.002');

        // GroupHeader parsen
        $grpHdrNode = $xpath->query("//{$prefix}GrpHdr")->item(0);
        $groupHeader = self::parsePain002GroupHeader($xpath, $grpHdrNode, $prefix);

        // OriginalGroupInformation parsen
        $orgnlGrpNode = $xpath->query("//{$prefix}OrgnlGrpInfAndSts")->item(0);
        $originalGroupInfo = self::parsePain002OriginalGroupInformation($xpath, $orgnlGrpNode, $prefix);

        // OriginalPaymentInformations parsen
        $pmtInfoNodes = $xpath->query("//{$prefix}OrgnlPmtInfAndSts");
        $paymentInfos = [];

        foreach ($pmtInfoNodes as $pmtInfoNode) {
            $paymentInfos[] = self::parsePain002OriginalPaymentInformation($xpath, $pmtInfoNode, $prefix);
        }

        return new Pain002Document($groupHeader, $originalGroupInfo, $paymentInfos);
    }

    private static function parsePain002GroupHeader(DOMXPath $xpath, ?DOMNode $node, string $prefix): Pain002GroupHeader {
        if (!$node) {
            return Pain002GroupHeader::create('UNKNOWN');
        }

        $msgId = $xpath->query("{$prefix}MsgId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $creDtTmStr = $xpath->query("{$prefix}CreDtTm", $node)->item(0)?->textContent;
        $creDtTm = $creDtTmStr ? new DateTimeImmutable($creDtTmStr) : new DateTimeImmutable();

        // InitgPty parsen via Trait
        $initgPtyNode = $xpath->query("{$prefix}InitgPty", $node)->item(0);
        $initiatingParty = $initgPtyNode ? self::parseParty($xpath, $initgPtyNode, $prefix) : null;
        // Falls nur Name in PartyIdentification relevant
        if ($initiatingParty?->getName() === null) {
            $initiatingParty = null;
        }

        return new Pain002GroupHeader($msgId, $creDtTm, $initiatingParty);
    }

    private static function parsePain002OriginalGroupInformation(DOMXPath $xpath, ?DOMNode $node, string $prefix): Pain002OriginalGroupInformation {
        if (!$node) {
            return new Pain002OriginalGroupInformation('UNKNOWN', 'pain.001.001.12');
        }

        $orgnlMsgId = $xpath->query("{$prefix}OrgnlMsgId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $orgnlMsgNmId = $xpath->query("{$prefix}OrgnlMsgNmId", $node)->item(0)?->textContent ?? 'pain.001.001.12';

        $orgnlCreDtTmStr = $xpath->query("{$prefix}OrgnlCreDtTm", $node)->item(0)?->textContent;
        $orgnlCreDtTm = $orgnlCreDtTmStr ? new DateTimeImmutable($orgnlCreDtTmStr) : null;

        $orgnlNbOfTxs = $xpath->query("{$prefix}OrgnlNbOfTxs", $node)->item(0)?->textContent;
        $orgnlCtrlSum = $xpath->query("{$prefix}OrgnlCtrlSum", $node)->item(0)?->textContent;

        $grpStsStr = $xpath->query("{$prefix}GrpSts", $node)->item(0)?->textContent;
        $grpSts = $grpStsStr ? TransactionStatus::tryFrom($grpStsStr) : null;

        // StsRsnInf parsen
        $statusReasons = self::parsePain002StatusReasons($xpath, $node, $prefix);

        return new Pain002OriginalGroupInformation(
            originalMessageId: $orgnlMsgId,
            originalMessageNameId: $orgnlMsgNmId,
            originalCreationDateTime: $orgnlCreDtTm,
            originalNumberOfTransactions: $orgnlNbOfTxs !== null ? (int) $orgnlNbOfTxs : null,
            originalControlSum: $orgnlCtrlSum !== null ? (float) $orgnlCtrlSum : null,
            groupStatus: $grpSts,
            statusReasons: $statusReasons
        );
    }

    private static function parsePain002OriginalPaymentInformation(DOMXPath $xpath, DOMNode $node, string $prefix): Pain002OriginalPaymentInformation {
        $orgnlPmtInfId = $xpath->query("{$prefix}OrgnlPmtInfId", $node)->item(0)?->textContent ?? 'UNKNOWN';

        $pmtInfStsStr = $xpath->query("{$prefix}PmtInfSts", $node)->item(0)?->textContent;
        $pmtInfSts = $pmtInfStsStr ? TransactionStatus::tryFrom($pmtInfStsStr) : null;

        $statusReasons = self::parsePain002StatusReasons($xpath, $node, $prefix);

        // TxInfAndSts parsen
        $txNodes = $xpath->query("{$prefix}TxInfAndSts", $node);
        $txStatuses = [];

        foreach ($txNodes as $txNode) {
            $txStatuses[] = self::parsePain002TransactionStatus($xpath, $txNode, $prefix);
        }

        return new Pain002OriginalPaymentInformation(
            originalPaymentInformationId: $orgnlPmtInfId,
            status: $pmtInfSts,
            statusReasons: $statusReasons,
            transactionStatuses: $txStatuses
        );
    }

    private static function parsePain002TransactionStatus(DOMXPath $xpath, DOMNode $node, string $prefix): TransactionInformationAndStatus {
        $stsId = $xpath->query("{$prefix}StsId", $node)->item(0)?->textContent;
        $orgnlInstrId = $xpath->query("{$prefix}OrgnlInstrId", $node)->item(0)?->textContent;
        $orgnlEndToEndId = $xpath->query("{$prefix}OrgnlEndToEndId", $node)->item(0)?->textContent;
        $orgnlUetr = $xpath->query("{$prefix}OrgnlUETR", $node)->item(0)?->textContent;

        $txStsStr = $xpath->query("{$prefix}TxSts", $node)->item(0)?->textContent;
        $txSts = $txStsStr ? TransactionStatus::tryFrom($txStsStr) : null;

        $statusReasons = self::parsePain002StatusReasons($xpath, $node, $prefix);

        // AccptncDtTm
        $accptncDtTmStr = $xpath->query("{$prefix}AccptncDtTm", $node)->item(0)?->textContent;
        $accptncDtTm = static::parseDateTimeStatic($accptncDtTmStr);

        // OrgnlTxRef/Amt
        $amtData = static::parseAmountWithCcy($xpath, "{$prefix}OrgnlTxRef/{$prefix}Amt/{$prefix}InstdAmt", $node);
        $amount = $amtData['amount'] > 0 ? $amtData['amount'] : null;
        $currency = $amount !== null ? $amtData['currency'] : null;

        return new TransactionInformationAndStatus(
            statusId: $stsId,
            originalInstructionId: $orgnlInstrId,
            originalEndToEndId: $orgnlEndToEndId,
            originalUetr: $orgnlUetr,
            status: $txSts,
            statusReasons: $statusReasons,
            originalAmount: $amount,
            originalCurrency: $currency,
            acceptanceDateTime: $accptncDtTm
        );
    }

    /**
     * @return StatusReason[]
     */
    private static function parsePain002StatusReasons(DOMXPath $xpath, DOMNode $node, string $prefix): array {
        $reasons = [];
        $stsRsnNodes = $xpath->query("{$prefix}StsRsnInf", $node);

        foreach ($stsRsnNodes as $rsnNode) {
            $code = $xpath->query("{$prefix}Rsn/{$prefix}Cd", $rsnNode)->item(0)?->textContent;
            $prtry = $xpath->query("{$prefix}Rsn/{$prefix}Prtry", $rsnNode)->item(0)?->textContent;

            $addtlInf = [];
            $addtlInfNodes = $xpath->query("{$prefix}AddtlInf", $rsnNode);
            foreach ($addtlInfNodes as $infoNode) {
                $addtlInf[] = $infoNode->textContent;
            }

            $reasons[] = new StatusReason($code, $prtry, $addtlInf);
        }

        return $reasons;
    }

    // =========================================================================
    // PAIN.007 - CUSTOMER PAYMENT REVERSAL
    // =========================================================================

    /**
     * Parst ein pain.007 XML-Dokument.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain007Document Das geparste Dokument
     * @throws RuntimeException Bei ungültigem XML
     */
    public static function parsePain007(string $xmlContent): Pain007Document {
        ['xpath' => $xpath, 'prefix' => $prefix] = self::createIso20022XPath($xmlContent, 'pain.007');

        // GroupHeader parsen
        $grpHdrNode = $xpath->query("//{$prefix}GrpHdr")->item(0);
        $groupHeader = self::parsePain007GroupHeader($xpath, $grpHdrNode, $prefix);

        // OriginalGroupInformation parsen
        $orgnlGrpInfNode = $xpath->query("//{$prefix}OrgnlGrpInf")->item(0);
        $originalGroupInfo = self::parsePain007OriginalGroupInformation($xpath, $orgnlGrpInfNode, $prefix);

        // OriginalPaymentInformation parsen
        $orgnlPmtInfAndRvslNodes = $xpath->query("//{$prefix}OrgnlPmtInfAndRvsl");
        $originalPaymentInfos = [];

        foreach ($orgnlPmtInfAndRvslNodes as $node) {
            $originalPaymentInfos[] = self::parsePain007OriginalPaymentInformation($xpath, $node, $prefix);
        }

        return new Pain007Document($groupHeader, $originalGroupInfo, $originalPaymentInfos);
    }

    private static function parsePain007GroupHeader(DOMXPath $xpath, ?DOMNode $node, string $prefix): Pain007GroupHeader {
        if (!$node) {
            return Pain007GroupHeader::create('UNKNOWN', new PartyIdentification(name: 'Unknown'));
        }

        $msgId = $xpath->query("{$prefix}MsgId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $creDtTmStr = $xpath->query("{$prefix}CreDtTm", $node)->item(0)?->textContent;
        $creDtTm = $creDtTmStr ? new DateTimeImmutable($creDtTmStr) : new DateTimeImmutable();

        $nbOfTxs = (int) ($xpath->query("{$prefix}NbOfTxs", $node)->item(0)?->textContent ?? '0');
        $ctrlSumStr = $xpath->query("{$prefix}CtrlSum", $node)->item(0)?->textContent;
        $ctrlSum = $ctrlSumStr !== null ? (float) $ctrlSumStr : null;

        $grpRvsl = $xpath->query("{$prefix}GrpRvsl", $node)->item(0)?->textContent;
        $groupReversal = $grpRvsl === 'true';

        // InitgPty parsen
        $initgPtyNode = $xpath->query("{$prefix}InitgPty", $node)->item(0);
        $initiatingParty = self::parseParty($xpath, $initgPtyNode, $prefix);

        return new Pain007GroupHeader($msgId, $creDtTm, $nbOfTxs, $ctrlSum, $initiatingParty, $groupReversal);
    }

    private static function parsePain007OriginalGroupInformation(DOMXPath $xpath, ?DOMNode $node, string $prefix): ?Pain007OriginalGroupInformation {
        if (!$node) {
            return null;
        }

        $orgnlMsgId = $xpath->query("{$prefix}OrgnlMsgId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $orgnlMsgNmId = $xpath->query("{$prefix}OrgnlMsgNmId", $node)->item(0)?->textContent ?? 'pain.008.001.11';
        $orgnlCreDtTmStr = $xpath->query("{$prefix}OrgnlCreDtTm", $node)->item(0)?->textContent;
        $orgnlCreDtTm = $orgnlCreDtTmStr ? new DateTimeImmutable($orgnlCreDtTmStr) : null;

        $orgnlNbOfTxs = (int) ($xpath->query("{$prefix}OrgnlNbOfTxs", $node)->item(0)?->textContent ?? '0');
        $orgnlCtrlSumStr = $xpath->query("{$prefix}OrgnlCtrlSum", $node)->item(0)?->textContent;
        $orgnlCtrlSum = $orgnlCtrlSumStr !== null ? (float) $orgnlCtrlSumStr : null;

        // ReversalReason parsen
        $rvslRsnInfNode = $xpath->query("{$prefix}RvslRsnInf", $node)->item(0);
        $reversalReason = $rvslRsnInfNode ? self::parsePain007ReversalReason($xpath, $rvslRsnInfNode, $prefix) : null;

        return new Pain007OriginalGroupInformation(
            $orgnlMsgId,
            $orgnlMsgNmId,
            $orgnlCreDtTm,
            $orgnlNbOfTxs > 0 ? $orgnlNbOfTxs : null,
            $orgnlCtrlSum,
            $reversalReason
        );
    }

    private static function parsePain007OriginalPaymentInformation(DOMXPath $xpath, DOMNode $node, string $prefix): Pain007OriginalPaymentInformation {
        $orgnlPmtInfId = $xpath->query("{$prefix}OrgnlPmtInfId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $orgnlNbOfTxs = (int) ($xpath->query("{$prefix}OrgnlNbOfTxs", $node)->item(0)?->textContent ?? '0');
        $orgnlCtrlSumStr = $xpath->query("{$prefix}OrgnlCtrlSum", $node)->item(0)?->textContent;
        $orgnlCtrlSum = $orgnlCtrlSumStr !== null ? (float) $orgnlCtrlSumStr : null;

        $pmtInfRvsl = $xpath->query("{$prefix}PmtInfRvsl", $node)->item(0)?->textContent;
        $paymentInfoReversal = $pmtInfRvsl === 'true';

        // ReversalReason parsen
        $rvslRsnInfNode = $xpath->query("{$prefix}RvslRsnInf", $node)->item(0);
        $reversalReason = $rvslRsnInfNode ? self::parsePain007ReversalReason($xpath, $rvslRsnInfNode, $prefix) : null;

        // Transactions parsen
        $txInfNodes = $xpath->query("{$prefix}TxInf", $node);
        $transactions = [];

        foreach ($txInfNodes as $txNode) {
            $transactions[] = self::parsePain007TransactionInformation($xpath, $txNode, $prefix);
        }

        return new Pain007OriginalPaymentInformation(
            originalPaymentInformationId: $orgnlPmtInfId,
            originalNumberOfTransactions: $orgnlNbOfTxs > 0 ? $orgnlNbOfTxs : null,
            originalControlSum: $orgnlCtrlSum,
            paymentInformationReversal: $paymentInfoReversal ?: null,
            reversalReason: $reversalReason,
            transactionInformations: $transactions
        );
    }

    private static function parsePain007TransactionInformation(DOMXPath $xpath, DOMNode $node, string $prefix): TransactionInformation {
        $rvslId = $xpath->query("{$prefix}RvslId", $node)->item(0)?->textContent;
        $orgnlInstrId = $xpath->query("{$prefix}OrgnlInstrId", $node)->item(0)?->textContent;
        $orgnlEndToEndId = $xpath->query("{$prefix}OrgnlEndToEndId", $node)->item(0)?->textContent ?? 'NOTPROVIDED';

        $rvsdInstdAmtStr = $xpath->query("{$prefix}RvsdInstdAmt", $node)->item(0)?->textContent;
        $rvsdInstdAmt = $rvsdInstdAmtStr !== null ? (float) $rvsdInstdAmtStr : null;

        $ccyNode = $xpath->query("{$prefix}RvsdInstdAmt", $node)->item(0);
        $currency = null;
        if ($ccyNode instanceof DOMElement && $ccyNode->hasAttribute('Ccy')) {
            $currency = CurrencyCode::tryFrom($ccyNode->getAttribute('Ccy'));
        }

        // ReversalReason parsen
        $rvslRsnInfNode = $xpath->query("{$prefix}RvslRsnInf", $node)->item(0);
        $reversalReason = $rvslRsnInfNode ? self::parsePain007ReversalReason($xpath, $rvslRsnInfNode, $prefix) : null;

        return new TransactionInformation(
            reversalId: $rvslId,
            originalInstructionId: $orgnlInstrId,
            originalEndToEndId: $orgnlEndToEndId,
            reversedAmount: $rvsdInstdAmt,
            currency: $currency,
            reversalReason: $reversalReason
        );
    }

    private static function parsePain007ReversalReason(DOMXPath $xpath, DOMNode $node, string $prefix): ReversalReason {
        $rsnCd = $xpath->query("{$prefix}Rsn/{$prefix}Cd", $node)->item(0)?->textContent;
        $rsnPrtry = $xpath->query("{$prefix}Rsn/{$prefix}Prtry", $node)->item(0)?->textContent;

        $addtlInf = [];
        $addtlInfNodes = $xpath->query("{$prefix}AddtlInf", $node);
        foreach ($addtlInfNodes as $addtlInfNode) {
            $addtlInf[] = $addtlInfNode->textContent;
        }

        return new ReversalReason($rsnCd, $rsnPrtry, $addtlInf);
    }

    // =========================================================================
    // PAIN.008 - CUSTOMER DIRECT DEBIT INITIATION
    // =========================================================================

    /**
     * Parst ein pain.008 XML-Dokument.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain008Document Das geparste Dokument
     * @throws RuntimeException Bei ungültigem XML
     */
    public static function parsePain008(string $xmlContent): Pain008Document {
        ['xpath' => $xpath, 'prefix' => $prefix] = self::createIso20022XPath($xmlContent, 'pain.008');

        // GroupHeader parsen
        $grpHdrNode = $xpath->query("//{$prefix}GrpHdr")->item(0);
        $groupHeader = self::parsePain008GroupHeader($xpath, $grpHdrNode, $prefix);

        // PaymentInstructions parsen
        $pmtInfNodes = $xpath->query("//{$prefix}PmtInf");
        $paymentInstructions = [];

        foreach ($pmtInfNodes as $pmtInfNode) {
            $paymentInstructions[] = self::parsePain008PaymentInstruction($xpath, $pmtInfNode, $prefix);
        }

        return new Pain008Document($groupHeader, $paymentInstructions);
    }

    private static function parsePain008GroupHeader(DOMXPath $xpath, ?DOMNode $node, string $prefix): Pain008GroupHeader {
        if (!$node) {
            return Pain008GroupHeader::create('UNKNOWN', new PartyIdentification(name: 'Unknown'));
        }

        $msgId = $xpath->query("{$prefix}MsgId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $creDtTmStr = $xpath->query("{$prefix}CreDtTm", $node)->item(0)?->textContent;
        $creDtTm = $creDtTmStr ? new DateTimeImmutable($creDtTmStr) : new DateTimeImmutable();

        $nbOfTxs = (int) ($xpath->query("{$prefix}NbOfTxs", $node)->item(0)?->textContent ?? '0');
        $ctrlSumStr = $xpath->query("{$prefix}CtrlSum", $node)->item(0)?->textContent;
        $ctrlSum = $ctrlSumStr !== null ? (float) $ctrlSumStr : null;

        // InitgPty parsen (via Trait)
        $initgPtyNode = $xpath->query("{$prefix}InitgPty", $node)->item(0);
        $initiatingParty = self::parseParty($xpath, $initgPtyNode, $prefix);

        return new Pain008GroupHeader($msgId, $creDtTm, $nbOfTxs, $ctrlSum, $initiatingParty);
    }

    private static function parsePain008PaymentInstruction(DOMXPath $xpath, DOMNode $node, string $prefix): Pain008PaymentInstruction {
        $pmtInfId = $xpath->query("{$prefix}PmtInfId", $node)->item(0)?->textContent ?? 'UNKNOWN';

        $pmtMtdStr = $xpath->query("{$prefix}PmtMtd", $node)->item(0)?->textContent ?? 'DD';
        $pmtMtd = PaymentMethod::tryFrom($pmtMtdStr) ?? PaymentMethod::DIRECT_DEBIT;

        $reqdColltnDtStr = $xpath->query("{$prefix}ReqdColltnDt", $node)->item(0)?->textContent;
        $reqdColltnDt = $reqdColltnDtStr ? new DateTimeImmutable($reqdColltnDtStr) : new DateTimeImmutable();

        // Creditor parsen
        $cdtrNode = $xpath->query("{$prefix}Cdtr", $node)->item(0);
        $creditor = self::parseParty($xpath, $cdtrNode, $prefix);

        // CreditorAccount parsen
        $cdtrAcctNode = $xpath->query("{$prefix}CdtrAcct", $node)->item(0);
        $creditorAccount = self::parseAccount($xpath, $cdtrAcctNode, $prefix);

        // CreditorAgent parsen
        $cdtrAgtNode = $xpath->query("{$prefix}CdtrAgt", $node)->item(0);
        $creditorAgent = self::parseFinancialInst($xpath, $cdtrAgtNode, $prefix);

        // CreditorSchemeId parsen
        $cdtrSchmeId = $xpath->query("{$prefix}CdtrSchmeId/{$prefix}Id/{$prefix}PrvtId/{$prefix}Othr/{$prefix}Id", $node)->item(0)?->textContent;

        // ChargeBearer
        $chrgBrStr = $xpath->query("{$prefix}ChrgBr", $node)->item(0)?->textContent;
        $chrgBr = $chrgBrStr ? ChargesCode::tryFrom($chrgBrStr) : null;

        // SequenceType
        $seqTpStr = $xpath->query("{$prefix}PmtTpInf/{$prefix}SeqTp", $node)->item(0)?->textContent;
        $seqTp = $seqTpStr ? SequenceType::tryFrom($seqTpStr) : null;

        // LocalInstrument
        $lclInstrmStr = $xpath->query("{$prefix}PmtTpInf/{$prefix}LclInstrm/{$prefix}Cd", $node)->item(0)?->textContent;
        $lclInstrm = $lclInstrmStr ? LocalInstrument::tryFrom($lclInstrmStr) : null;

        // ServiceLevel
        $svcLvl = $xpath->query("{$prefix}PmtTpInf/{$prefix}SvcLvl/{$prefix}Cd", $node)->item(0)?->textContent;

        // DrctDbtTxInf parsen
        $txNodes = $xpath->query("{$prefix}DrctDbtTxInf", $node);
        $transactions = [];

        foreach ($txNodes as $txNode) {
            $transactions[] = self::parsePain008DirectDebitTransaction($xpath, $txNode, $prefix);
        }

        return new Pain008PaymentInstruction(
            paymentInstructionId: $pmtInfId,
            paymentMethod: $pmtMtd,
            requestedCollectionDate: $reqdColltnDt,
            creditor: $creditor,
            creditorAccount: $creditorAccount,
            creditorAgent: $creditorAgent,
            transactions: $transactions,
            creditorSchemeId: $cdtrSchmeId,
            chargeBearer: $chrgBr,
            sequenceType: $seqTp,
            localInstrument: $lclInstrm,
            serviceLevel: $svcLvl
        );
    }

    private static function parsePain008DirectDebitTransaction(DOMXPath $xpath, DOMNode $node, string $prefix): DirectDebitTransaction {
        // PaymentIdentification
        $endToEndId = $xpath->query("{$prefix}PmtId/{$prefix}EndToEndId", $node)->item(0)?->textContent ?? 'NOTPROVIDED';
        $instrId = $xpath->query("{$prefix}PmtId/{$prefix}InstrId", $node)->item(0)?->textContent;
        $uetr = $xpath->query("{$prefix}PmtId/{$prefix}UETR", $node)->item(0)?->textContent;

        $paymentId = new PaymentIdentification($endToEndId, $instrId, $uetr);

        // Betrag und Währung
        $amtData = static::parseAmountWithCcy($xpath, "{$prefix}InstdAmt", $node);

        // MandateInformation
        $mndtNode = $xpath->query("{$prefix}DrctDbtTx/{$prefix}MndtRltdInf", $node)->item(0);
        $mandateInfo = self::parsePain008MandateInformation($xpath, $mndtNode, $prefix);

        // Debtor
        $dbtrNode = $xpath->query("{$prefix}Dbtr", $node)->item(0);
        $debtor = self::parseParty($xpath, $dbtrNode, $prefix);

        // DebtorAccount
        $dbtrAcctNode = $xpath->query("{$prefix}DbtrAcct", $node)->item(0);
        $debtorAccount = self::parseAccount($xpath, $dbtrAcctNode, $prefix);

        // DebtorAgent
        $dbtrAgtNode = $xpath->query("{$prefix}DbtrAgt", $node)->item(0);
        $debtorAgent = $dbtrAgtNode ? self::parseFinancialInst($xpath, $dbtrAgtNode, $prefix) : null;

        // RemittanceInformation
        $rmtInfNode = $xpath->query("{$prefix}RmtInf", $node)->item(0);
        $remittanceInfo = null;
        if ($rmtInfNode) {
            $ustrdNodes = $xpath->query("{$prefix}Ustrd", $rmtInfNode);
            $ustrd = [];
            foreach ($ustrdNodes as $ustrdNode) {
                $ustrd[] = $ustrdNode->textContent;
            }
            if (!empty($ustrd)) {
                $remittanceInfo = new RemittanceInformation($ustrd);
            }
        }

        return new DirectDebitTransaction(
            paymentId: $paymentId,
            amount: $amtData['amount'],
            currency: $amtData['currency'],
            mandateInfo: $mandateInfo,
            debtor: $debtor,
            debtorAccount: $debtorAccount,
            debtorAgent: $debtorAgent,
            remittanceInformation: $remittanceInfo
        );
    }

    private static function parsePain008MandateInformation(DOMXPath $xpath, ?DOMNode $node, string $prefix): MandateInformation {
        if (!$node) {
            return MandateInformation::create('UNKNOWN', new DateTimeImmutable());
        }

        $mndtId = $xpath->query("{$prefix}MndtId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $dtOfSgntrStr = $xpath->query("{$prefix}DtOfSgntr", $node)->item(0)?->textContent;
        $dtOfSgntr = $dtOfSgntrStr ? new DateTimeImmutable($dtOfSgntrStr) : new DateTimeImmutable();

        $amdmntIndStr = $xpath->query("{$prefix}AmdmntInd", $node)->item(0)?->textContent;
        $amdmntInd = $amdmntIndStr !== null ? ($amdmntIndStr === 'true') : null;

        $orgnlMndtId = $xpath->query("{$prefix}AmdmntInfDtls/{$prefix}OrgnlMndtId", $node)->item(0)?->textContent;
        $orgnlCdtrSchmeId = $xpath->query("{$prefix}AmdmntInfDtls/{$prefix}OrgnlCdtrSchmeId/{$prefix}Id/{$prefix}PrvtId/{$prefix}Othr/{$prefix}Id", $node)->item(0)?->textContent;

        return new MandateInformation(
            mandateId: $mndtId,
            dateOfSignature: $dtOfSgntr,
            amendmentIndicator: $amdmntInd,
            originalMandateId: $orgnlMndtId,
            originalCreditorSchemeId: $orgnlCdtrSchmeId
        );
    }

    // =========================================================================
    // PAIN.009 - MANDATE INITIATION REQUEST
    // =========================================================================

    /**
     * Parst ein pain.009 XML-Dokument.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @return Pain009Document Das geparste Dokument
     * @throws RuntimeException Bei ungültigem XML
     */
    public static function parsePain009(string $xmlContent): Pain009Document {
        ['xpath' => $xpath, 'prefix' => $prefix] = self::createIso20022XPath($xmlContent, 'pain.009');

        // Header parsen
        $msgId = $xpath->query("//{$prefix}GrpHdr/{$prefix}MsgId")->item(0)?->textContent ?? 'UNKNOWN';
        $creDtTmStr = $xpath->query("//{$prefix}GrpHdr/{$prefix}CreDtTm")->item(0)?->textContent;
        $creDtTm = $creDtTmStr ? new DateTimeImmutable($creDtTmStr) : new DateTimeImmutable();

        // InitgPty parsen
        $initgPtyNode = $xpath->query("//{$prefix}GrpHdr/{$prefix}InitgPty")->item(0);
        $initiatingParty = self::parseParty($xpath, $initgPtyNode, $prefix);

        // Mandate parsen
        $mndtNodes = $xpath->query("//{$prefix}Mndt");
        $mandates = [];

        foreach ($mndtNodes as $mndtNode) {
            $mandates[] = self::parsePain009Mandate($xpath, $mndtNode, $prefix);
        }

        return new Pain009Document($msgId, $creDtTm, $initiatingParty, $mandates);
    }

    private static function parsePain009Mandate(DOMXPath $xpath, DOMNode $node, string $prefix): Mandate {
        $mndtId = $xpath->query("{$prefix}MndtId", $node)->item(0)?->textContent ?? 'UNKNOWN';
        $dtOfSgntrStr = $xpath->query("{$prefix}DtOfSgntr", $node)->item(0)?->textContent;
        $dtOfSgntr = $dtOfSgntrStr ? new DateTimeImmutable($dtOfSgntrStr) : new DateTimeImmutable();

        // Creditor (via Trait)
        $cdtrNode = $xpath->query("{$prefix}Cdtr", $node)->item(0);
        $creditor = self::parseParty($xpath, $cdtrNode, $prefix);

        // CreditorAccount (via Trait)
        $cdtrAcctNode = $xpath->query("{$prefix}CdtrAcct", $node)->item(0);
        $creditorAccount = self::parseAccount($xpath, $cdtrAcctNode, $prefix);

        // CreditorAgent (via Trait)
        $cdtrAgtNode = $xpath->query("{$prefix}CdtrAgt", $node)->item(0);
        $creditorAgent = self::parseFinancialInst($xpath, $cdtrAgtNode, $prefix);

        // Debtor (via Trait)
        $dbtrNode = $xpath->query("{$prefix}Dbtr", $node)->item(0);
        $debtor = self::parseParty($xpath, $dbtrNode, $prefix);

        // DebtorAccount (via Trait)
        $dbtrAcctNode = $xpath->query("{$prefix}DbtrAcct", $node)->item(0);
        $debtorAccount = self::parseAccount($xpath, $dbtrAcctNode, $prefix);

        // DebtorAgent (via Trait)
        $dbtrAgtNode = $xpath->query("{$prefix}DbtrAgt", $node)->item(0);
        $debtorAgent = self::parseFinancialInst($xpath, $dbtrAgtNode, $prefix);

        // CreditorSchemeId
        $cdtrSchmeId = $xpath->query("{$prefix}CdtrSchmeId/{$prefix}Id/{$prefix}PrvtId/{$prefix}Othr/{$prefix}Id", $node)->item(0)?->textContent;

        // LocalInstrument
        $lclInstrmCd = $xpath->query("{$prefix}MndtTpInf/{$prefix}LclInstrm/{$prefix}Cd", $node)->item(0)?->textContent;
        $localInstrument = $lclInstrmCd ? LocalInstrument::tryFrom($lclInstrmCd) : null;

        // SequenceType
        $seqTp = $xpath->query("{$prefix}MndtTpInf/{$prefix}SeqTp", $node)->item(0)?->textContent;
        $sequenceType = $seqTp ? SequenceType::tryFrom($seqTp) : null;

        return new Mandate(
            mandateId: $mndtId,
            dateOfSignature: $dtOfSgntr,
            creditor: $creditor,
            creditorAccount: $creditorAccount,
            creditorAgent: $creditorAgent,
            debtor: $debtor,
            debtorAccount: $debtorAccount,
            debtorAgent: $debtorAgent,
            creditorSchemeId: $cdtrSchmeId,
            localInstrument: $localInstrument,
            sequenceType: $sequenceType
        );
    }
}
