<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtParserRegistry.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Registries;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Camt\Type26\Document as Camt026Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type26\UnableToApplyReason;
use CommonToolkit\FinancialFormats\Entities\Camt\Type27\Document as Camt027Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type28\AdditionalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\Camt\Type28\Document as Camt028Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type29\Document as Camt029Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type30\Document as Camt030Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type31\Document as Camt031Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type33\Document as Camt033Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type34\Document as Camt034Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type35\Document as Camt035Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type36\Document as Camt036Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type37\Document as Camt037Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type38\Document as Camt038Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type39\Document as Camt039Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type55\Document as Camt055Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type56\Document as Camt056Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type57\Document as Camt057Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type57\NotificationItem;
use CommonToolkit\FinancialFormats\Entities\Camt\Type58\CancellationItem;
use CommonToolkit\FinancialFormats\Entities\Camt\Type58\Document as Camt058Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type59\Document as Camt059Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type59\StatusItem;
use CommonToolkit\FinancialFormats\Entities\Camt\Type87\Document as Camt087Document;
use CommonToolkit\FinancialFormats\Entities\Camt\Type87\ModificationRequest;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Parsers\CamtReflectionParser;
use DOMNode;
use DOMXPath;

/**
 * Registry für CAMT-Parser-Konfigurationen.
 * 
 * Registriert alle unterstützten CAMT-Typen mit ihren XPath-Mappings
 * für den Reflection-basierten Parser.
 * 
 * @package CommonToolkit\FinancialFormats\Registries
 */
class CamtParserRegistry {
    private static bool $initialized = false;

    /**
     * Initialisiert alle CAMT-Typ-Registrierungen.
     * Wird automatisch beim ersten Zugriff aufgerufen.
     */
    public static function initialize(): void {
        if (self::$initialized) {
            return;
        }

        self::registerInvestigationDocuments();
        self::$initialized = true;
    }

    /**
     * Registriert Investigation-Dokumente (026, 027, 028, 030, 031, 033-039, 087).
     * Diese haben alle ähnliche Strukturen mit Assignment + Case.
     */
    private static function registerInvestigationDocuments(): void {
        // CAMT.026 - Unable to Apply
        CamtReflectionParser::registerType(
            type: CamtType::CAMT026,
            class: Camt026Document::class,
            root: 'UblToApply',
            includeAssignment: true,
            includeUnderlying: true,
            postProcessor: self::getCamt026PostProcessor()
        );

        // CAMT.027 - Claim Non Receipt
        CamtReflectionParser::registerType(
            type: CamtType::CAMT027,
            class: Camt027Document::class,
            root: 'ClmNonRcpt',
            mappings: [
                'missingCoverIndicator' => 'CoverDtls/MssngCoverInd',
                'coverDate' => 'CoverDtls/CoverDt',
            ],
            includeAssignment: true,
            includeUnderlying: true
        );

        // CAMT.028 - Additional Payment Information
        CamtReflectionParser::registerType(
            type: CamtType::CAMT028,
            class: Camt028Document::class,
            root: 'AddtlPmtInf',
            includeAssignment: true,
            includeUnderlying: true,
            postProcessor: self::getCamt028PostProcessor()
        );

        // CAMT.030 - Notification of Case Assignment
        CamtReflectionParser::registerType(
            type: CamtType::CAMT030,
            class: Camt030Document::class,
            root: 'NtfctnOfCaseAssgnmt',
            mappings: [
                'headerMessageId' => 'Hdr/Id',
                'creationDateTime' => 'Hdr/CreDtTm',
                'assignerAgentBic' => 'Assgnmt/Assgnr/Agt/FinInstnId/BICFI',
                'assignerPartyName' => 'Assgnmt/Assgnr/Pty/Nm',
                'assigneeAgentBic' => 'Assgnmt/Assgne/Agt/FinInstnId/BICFI',
                'assigneePartyName' => 'Assgnmt/Assgne/Pty/Nm',
                'caseId' => 'Case/Id',
                'caseCreator' => 'Case/Cretr/Pty/Nm',
                'notificationJustification' => 'Justfn/Rsn',
            ]
        );

        // CAMT.031 - Reject Investigation
        CamtReflectionParser::registerType(
            type: CamtType::CAMT031,
            class: Camt031Document::class,
            root: 'RjctInvstgtn',
            mappings: [
                'rejectionReasonCode' => 'Justfn/RjctnRsn/Cd',
                'rejectionReasonProprietary' => 'Justfn/RjctnRsn/Prtry',
                'additionalInformation' => 'Justfn/AddtlInf',
            ],
            includeAssignment: true
        );

        // CAMT.033 - Request for Duplicate
        CamtReflectionParser::registerType(
            type: CamtType::CAMT033,
            class: Camt033Document::class,
            root: 'ReqForDplct',
            includeAssignment: true,
            includeUnderlying: true
        );

        // CAMT.034 - Duplicate
        CamtReflectionParser::registerType(
            type: CamtType::CAMT034,
            class: Camt034Document::class,
            root: 'Dplct',
            mappings: [
                'duplicateContent' => 'Dplct/PrtryData/Data',
                'duplicateContentType' => 'Dplct/PrtryData/Tp',
            ],
            includeAssignment: true
        );

        // CAMT.035 - Proprietary Format Investigation
        CamtReflectionParser::registerType(
            type: CamtType::CAMT035,
            class: Camt035Document::class,
            root: 'PrtryFrmtInvstgtn',
            mappings: [
                'proprietaryData' => 'PrtryData/Data',
                'proprietaryType' => 'PrtryData/Tp',
            ],
            includeAssignment: true
        );

        // CAMT.036 - Debit Authorisation Response
        CamtReflectionParser::registerType(
            type: CamtType::CAMT036,
            class: Camt036Document::class,
            root: 'DbtAuthstnRspn',
            mappings: [
                'debitAuthorised' => 'Conf/DbtAuthstn',
                'authorisedAmount' => 'Conf/AmtToDbt',
                'authorisedCurrency' => 'Conf/AmtToDbt/@Ccy',
                'valueDate' => 'Conf/ValDtToDbt',
                'reason' => 'Conf/Rsn',
            ],
            includeAssignment: true
        );

        // CAMT.037 - Debit Authorisation Request
        CamtReflectionParser::registerType(
            type: CamtType::CAMT037,
            class: Camt037Document::class,
            root: 'DbtAuthstnReq',
            mappings: [
                'debtorName' => 'Dtl/Dbtr/Nm',
                'debtorAccountIban' => 'Dtl/DbtrAcct/Id/IBAN',
                'reason' => 'Dtl/Rsn/Prtry',
            ],
            includeAssignment: true,
            includeUnderlying: true
        );

        // CAMT.038 - Case Status Report Request
        CamtReflectionParser::registerType(
            type: CamtType::CAMT038,
            class: Camt038Document::class,
            root: 'CaseStsRptReq',
            mappings: [
                'requestId' => 'ReqHdr/Id',
                'creationDateTime' => 'ReqHdr/CreDtTm',
                'caseId' => 'Case/Id',
                'caseCreator' => 'Case/Cretr/Pty/Nm',
                'requesterAgentBic' => 'ReqHdr/Reqstr/Agt/FinInstnId/BICFI',
                'requesterPartyName' => 'ReqHdr/Reqstr/Pty/Nm',
                'responderAgentBic' => 'ReqHdr/Rspndr/Agt/FinInstnId/BICFI',
                'responderPartyName' => 'ReqHdr/Rspndr/Pty/Nm',
            ]
        );

        // CAMT.039 - Case Status Report
        CamtReflectionParser::registerType(
            type: CamtType::CAMT039,
            class: Camt039Document::class,
            root: 'CaseStsRpt',
            mappings: [
                'reportId' => 'Hdr/Id',
                'creationDateTime' => 'Hdr/CreDtTm',
                'statusCode' => 'Sts/Conf',
                'statusReason' => 'Sts/StsRsn/Rsn/Prtry',
                'caseId' => 'Case/Id',
                'caseCreator' => 'Case/Cretr/Pty/Nm',
                'reporterAgentBic' => 'Hdr/Fr/Agt/FinInstnId/BICFI',
                'reporterPartyName' => 'Hdr/Fr/Pty/Nm',
                'receiverAgentBic' => 'Hdr/To/Agt/FinInstnId/BICFI',
                'receiverPartyName' => 'Hdr/To/Pty/Nm',
                'additionalInformation' => 'Sts/StsRsn/AddtlInf',
            ]
        );

        // CAMT.087 - Request to Modify Payment
        CamtReflectionParser::registerType(
            type: CamtType::CAMT087,
            class: Camt087Document::class,
            root: 'ReqToModfyPmt',
            includeAssignment: true,
            includeUnderlying: true,
            postProcessor: self::getCamt087PostProcessor()
        );

        // CAMT.029 - Resolution of Investigation
        CamtReflectionParser::registerType(
            type: CamtType::CAMT029,
            class: Camt029Document::class,
            root: 'RsltnOfInvstgtn',
            mappings: [
                'investigationStatus' => 'Sts/Conf',
                'investigationStatusProprietary' => 'Sts/Prtry',
            ],
            includeAssignment: true
        );

        // CAMT.055 - Customer Payment Cancellation Request
        CamtReflectionParser::registerType(
            type: CamtType::CAMT055,
            class: Camt055Document::class,
            root: 'CstmrPmtCxlReq',
            mappings: [
                'messageId' => 'GrpHdr/MsgId',
                'creationDateTime' => 'GrpHdr/CreDtTm',
                'numberOfTransactions' => 'GrpHdr/NbOfTxs',
                'controlSum' => 'GrpHdr/CtrlSum',
                'initiatingPartyName' => 'GrpHdr/InitgPty/Nm',
                'initiatingPartyId' => 'GrpHdr/InitgPty/Id/OrgId/Othr/Id',
                'caseId' => 'Case/Id',
                'caseCreator' => 'Case/Cretr/Pty/Nm',
            ]
        );

        // CAMT.056 - FI To FI Payment Cancellation Request
        CamtReflectionParser::registerType(
            type: CamtType::CAMT056,
            class: Camt056Document::class,
            root: 'FIToFIPmtCxlReq',
            mappings: [
                'messageId' => 'GrpHdr/MsgId',
                'creationDateTime' => 'GrpHdr/CreDtTm',
                'numberOfTransactions' => 'GrpHdr/NbOfTxs',
                'controlSum' => 'GrpHdr/CtrlSum',
                'instructingAgentBic' => 'GrpHdr/InstgAgt/FinInstnId/BICFI',
                'instructedAgentBic' => 'GrpHdr/InstdAgt/FinInstnId/BICFI',
                'caseId' => 'Case/Id',
                'caseCreator' => 'Case/Cretr/Pty/Nm',
            ]
        );

        // CAMT.057 - Notification to Receive
        CamtReflectionParser::registerType(
            type: CamtType::CAMT057,
            class: Camt057Document::class,
            root: 'NtfctnToRcv',
            mappings: [
                'groupHeaderMessageId' => 'GrpHdr/MsgId',
                'creationDateTime' => 'GrpHdr/CreDtTm',
                'initiatingPartyName' => 'GrpHdr/InitgPty/Nm',
                'messageRecipientBic' => 'GrpHdr/MsgRcpt/FinInstnId/BICFI',
            ],
            postProcessor: self::getCamt057PostProcessor()
        );

        // CAMT.058 - Notification to Receive Cancellation Advice
        CamtReflectionParser::registerType(
            type: CamtType::CAMT058,
            class: Camt058Document::class,
            root: 'NtfctnToRcvCxlAdvc',
            mappings: [
                'groupHeaderMessageId' => 'GrpHdr/MsgId',
                'creationDateTime' => 'GrpHdr/CreDtTm',
                'initiatingPartyName' => 'GrpHdr/InitgPty/Nm',
                'messageRecipientBic' => 'GrpHdr/MsgRcpt/FinInstnId/BICFI',
                'originalMessageId' => 'OrgnlNtfctn/OrgnlMsgId',
                'originalMessageNameId' => 'OrgnlNtfctn/OrgnlMsgNmId',
                'originalCreationDateTime' => 'OrgnlNtfctn/OrgnlCreDtTm',
            ],
            postProcessor: self::getCamt058PostProcessor()
        );

        // CAMT.059 - Notification to Receive Status Report
        CamtReflectionParser::registerType(
            type: CamtType::CAMT059,
            class: Camt059Document::class,
            root: 'NtfctnToRcvStsRpt',
            mappings: [
                'groupHeaderMessageId' => 'GrpHdr/MsgId',
                'creationDateTime' => 'GrpHdr/CreDtTm',
                'initiatingPartyName' => 'GrpHdr/InitgPty/Nm',
                'messageRecipientBic' => 'GrpHdr/MsgRcpt/FinInstnId/BICFI',
                'originalMessageId' => 'OrgnlNtfctnAndSts/OrgnlMsgId',
                'originalMessageNameId' => 'OrgnlNtfctnAndSts/OrgnlMsgNmId',
                'originalCreationDateTime' => 'OrgnlNtfctnAndSts/OrgnlCreDtTm',
                'originalGroupStatusCode' => 'OrgnlNtfctnAndSts/OrgnlNtfctnSts',
            ],
            postProcessor: self::getCamt059PostProcessor()
        );
    }

    /**
     * Erstellt Post-Processor für CAMT.026 (Unable to Apply Reasons).
     */
    private static function getCamt026PostProcessor(): callable {
        return function (Camt026Document $document, DOMXPath $xpath, DOMNode $rootNode, string $prefix): void {
            // Parse MssngOrIncrrctInf elements unter Justfn
            $reasonNodes = $xpath->query("{$prefix}Justfn/{$prefix}MssngOrIncrrctInf", $rootNode);

            foreach ($reasonNodes as $reasonNode) {
                $missingType = $xpath->evaluate("string({$prefix}MssngInf/{$prefix}Tp)", $reasonNode) ?: null;
                $incorrectType = $xpath->evaluate("string({$prefix}IncrrctInf/{$prefix}Tp)", $reasonNode) ?: null;
                $additionalInfo = $xpath->evaluate("string({$prefix}AddtlInf)", $reasonNode) ?: null;

                $reason = new UnableToApplyReason(
                    reasonCode: null,
                    reasonProprietary: null,
                    additionalInformation: $additionalInfo,
                    missingInformationType: $missingType,
                    incorrectInformationType: $incorrectType
                );

                $document->addUnableToApplyReason($reason);
            }
        };
    }

    /**
     * Erstellt Post-Processor für CAMT.028 (Additional Payment Information).
     */
    private static function getCamt028PostProcessor(): callable {
        return function (Camt028Document $document, DOMXPath $xpath, DOMNode $rootNode, string $prefix): void {
            // Parse InfReqd/RmtInf elements
            $infoNodes = $xpath->query("{$prefix}InfReqd/{$prefix}RmtInf", $rootNode);

            foreach ($infoNodes as $infoNode) {
                $remittanceInfo = $xpath->evaluate("string({$prefix}Ustrd)", $infoNode) ?: null;

                $additionalInfo = new AdditionalPaymentInformation(
                    instructionIdentification: null,
                    endToEndIdentification: null,
                    paymentInformationIdentification: null,
                    remittanceInformation: $remittanceInfo,
                    purpose: null
                );

                $document->addAdditionalInformation($additionalInfo);
            }
        };
    }

    /**
     * Erstellt Post-Processor für CAMT.087 (Modification Requests).
     */
    private static function getCamt087PostProcessor(): callable {
        return function (Camt087Document $document, DOMXPath $xpath, DOMNode $rootNode, string $prefix): void {
            // Parse Mod elements
            $modNodes = $xpath->query("{$prefix}Mod", $rootNode);

            foreach ($modNodes as $modNode) {
                $requestedAmount = $xpath->evaluate("string({$prefix}PmtModDtls/{$prefix}ReqdAmt)", $modNode) ?: null;
                $requestedCurrency = $xpath->evaluate("string({$prefix}PmtModDtls/{$prefix}ReqdAmt/@Ccy)", $modNode) ?: null;
                $creditorName = $xpath->evaluate("string({$prefix}CdtrDtls/{$prefix}Cdtr/{$prefix}Nm)", $modNode) ?: null;
                $creditorAccount = $xpath->evaluate("string({$prefix}CdtrDtls/{$prefix}CdtrAcct/{$prefix}Id/{$prefix}IBAN)", $modNode) ?: null;
                $remittanceInfo = $xpath->evaluate("string({$prefix}RmtInf/{$prefix}Ustrd)", $modNode) ?: null;

                $modRequest = new ModificationRequest(
                    requestedExecutionDate: null,
                    requestedSettlementAmount: $requestedAmount,
                    requestedCurrency: $requestedCurrency ? CurrencyCode::tryFrom($requestedCurrency) : null,
                    debtorName: null,
                    debtorAccount: null,
                    creditorName: $creditorName,
                    creditorAccount: $creditorAccount,
                    remittanceInformation: $remittanceInfo,
                    purpose: null
                );

                $document->addModificationRequest($modRequest);
            }
        };
    }

    /**
     * Erstellt Post-Processor für CAMT.057 (Notification Items).
     */
    private static function getCamt057PostProcessor(): callable {
        return function (Camt057Document $document, DOMXPath $xpath, DOMNode $rootNode, string $prefix): void {
            // Parse Ntfctn elements
            $ntfctnNodes = $xpath->query("{$prefix}Ntfctn", $rootNode);

            foreach ($ntfctnNodes as $ntfctn) {
                $id = $xpath->evaluate("string({$prefix}Id)", $ntfctn) ?: '';
                $xpctdValDt = $xpath->evaluate("string({$prefix}XpctdValDt)", $ntfctn) ?: null;
                $amt = $xpath->evaluate("string({$prefix}Amt)", $ntfctn) ?: null;
                $ccy = $xpath->evaluate("string({$prefix}Amt/@Ccy)", $ntfctn) ?: null;
                $dbtrNm = $xpath->evaluate("string({$prefix}Dbtr/{$prefix}Nm)", $ntfctn) ?: null;
                $dbtrAcctIban = $xpath->evaluate("string({$prefix}DbtrAcct/{$prefix}Id/{$prefix}IBAN)", $ntfctn) ?: null;
                $dbtrAgtBic = $xpath->evaluate("string({$prefix}DbtrAgt/{$prefix}FinInstnId/{$prefix}BICFI)", $ntfctn) ?: null;
                $rmtInf = $xpath->evaluate("string({$prefix}RmtInf/{$prefix}Ustrd)", $ntfctn) ?: null;

                $item = new NotificationItem(
                    id: $id,
                    expectedValueDate: $xpctdValDt,
                    amount: $amt,
                    currency: $ccy ? CurrencyCode::tryFrom($ccy) : null,
                    debtorName: $dbtrNm,
                    debtorAccountIban: $dbtrAcctIban,
                    debtorAgentBic: $dbtrAgtBic,
                    remittanceInformation: $rmtInf
                );

                $document->addItem($item);
            }
        };
    }

    /**
     * Erstellt Post-Processor für CAMT.058 (Cancellation Items).
     */
    private static function getCamt058PostProcessor(): callable {
        return function (Camt058Document $document, DOMXPath $xpath, DOMNode $rootNode, string $prefix): void {
            // Parse OrgnlNtfctn/OrgnlItm elements
            $orgnlItmNodes = $xpath->query("{$prefix}OrgnlNtfctn/{$prefix}OrgnlItm", $rootNode);

            foreach ($orgnlItmNodes as $orgnlItm) {
                $orgnlItmId = $xpath->evaluate("string({$prefix}OrgnlItmId)", $orgnlItm) ?: '';
                $cxlRsnCd = $xpath->evaluate("string({$prefix}CxlRsnInf/{$prefix}Rsn/{$prefix}Cd)", $orgnlItm) ?: null;
                $cxlRsnPrtry = $xpath->evaluate("string({$prefix}CxlRsnInf/{$prefix}Rsn/{$prefix}Prtry)", $orgnlItm) ?: null;
                $addtlInf = $xpath->evaluate("string({$prefix}CxlRsnInf/{$prefix}AddtlInf)", $orgnlItm) ?: null;

                $item = new CancellationItem(
                    originalItemId: $orgnlItmId,
                    cancellationReasonCode: $cxlRsnCd,
                    cancellationReasonProprietary: $cxlRsnPrtry,
                    cancellationAdditionalInfo: $addtlInf
                );

                $document->addItem($item);
            }
        };
    }

    /**
     * Erstellt Post-Processor für CAMT.059 (Status Items).
     */
    private static function getCamt059PostProcessor(): callable {
        return function (Camt059Document $document, DOMXPath $xpath, DOMNode $rootNode, string $prefix): void {
            // Parse OrgnlNtfctnAndSts/OrgnlItmAndSts elements
            $orgnlItmNodes = $xpath->query("{$prefix}OrgnlNtfctnAndSts/{$prefix}OrgnlItmAndSts", $rootNode);

            foreach ($orgnlItmNodes as $orgnlItmAndSts) {
                $orgnlItmId = $xpath->evaluate("string({$prefix}OrgnlItmId)", $orgnlItmAndSts) ?: '';
                $itmSts = $xpath->evaluate("string({$prefix}ItmSts)", $orgnlItmAndSts) ?: null;
                $rsnCd = $xpath->evaluate("string({$prefix}StsRsnInf/{$prefix}Rsn/{$prefix}Cd)", $orgnlItmAndSts) ?: null;
                $rsnPrtry = $xpath->evaluate("string({$prefix}StsRsnInf/{$prefix}Rsn/{$prefix}Prtry)", $orgnlItmAndSts) ?: null;
                $addtlInf = $xpath->evaluate("string({$prefix}StsRsnInf/{$prefix}AddtlInf)", $orgnlItmAndSts) ?: null;

                $item = new StatusItem(
                    originalItemId: $orgnlItmId,
                    itemStatus: $itmSts,
                    reasonCode: $rsnCd,
                    reasonProprietary: $rsnPrtry,
                    additionalInformation: $addtlInf
                );

                $document->addItem($item);
            }
        };
    }

    /**
     * Prüft ob die Registry initialisiert ist.
     */
    public static function isInitialized(): bool {
        return self::$initialized;
    }

    /**
     * Setzt die Registry zurück (für Tests).
     */
    public static function reset(): void {
        CamtReflectionParser::clearRegistrations();
        self::$initialized = false;
    }
}
