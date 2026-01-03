<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums;

/**
 * CAMT message types according to ISO 20022.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum CamtType: string {
    /**
     * CAMT.052 - Bank to Customer Account Report (Intraday)
     * Intraday account movement information
     */
    case CAMT052 = 'camt.052';

    /**
     * CAMT.053 - Bank to Customer Statement (End of Day)
     * Daily account statement
     */
    case CAMT053 = 'camt.053';

    /**
     * CAMT.054 - Bank to Customer Debit Credit Notification
     * Debit/Credit notification (individual transaction notification)
     */
    case CAMT054 = 'camt.054';

    /**
     * CAMT.055 - Customer Payment Cancellation Request
     * Kundenseitige Stornoanforderung
     */
    case CAMT055 = 'camt.055';

    /**
     * CAMT.056 - FI to FI Payment Cancellation Request
     * Bank-zu-Bank Zahlungsstornierung
     */
    case CAMT056 = 'camt.056';

    /**
     * CAMT.029 - Resolution of Investigation
     * Clarification response to claim
     */
    case CAMT029 = 'camt.029';

    /**
     * CAMT.026 - Unable to Apply
     * Nicht zuordenbare Zahlung
     */
    case CAMT026 = 'camt.026';

    /**
     * CAMT.027 - Claim Non Receipt
     * Einfordern einer nicht erhaltenen Zahlung
     */
    case CAMT027 = 'camt.027';

    /**
     * CAMT.028 - Additional Payment Information
     * Additional payment information
     */
    case CAMT028 = 'camt.028';

    /**
     * CAMT.087 - Request to Modify Payment
     * Payment modification request
     */
    case CAMT087 = 'camt.087';

    /**
     * CAMT.030 - Notification of Case Assignment
     * Case assignment notification
     */
    case CAMT030 = 'camt.030';

    /**
     * CAMT.031 - Reject Investigation
     * Ablehnung einer Untersuchung
     */
    case CAMT031 = 'camt.031';

    /**
     * CAMT.033 - Request for Duplicate
     * Request for duplicate
     */
    case CAMT033 = 'camt.033';

    /**
     * CAMT.057 - Notification to Receive
     * Notification about expected payment receipt
     */
    case CAMT057 = 'camt.057';

    /**
     * CAMT.058 - Notification to Receive Cancellation Advice
     * Stornierungshinweis einer Empfangsbenachrichtigung
     */
    case CAMT058 = 'camt.058';

    /**
     * CAMT.059 - Notification to Receive Status Report
     * Statusbericht einer Empfangsbenachrichtigung
     */
    case CAMT059 = 'camt.059';

    /**
     * CAMT.034 - Duplicate
     * Duplikatantwort auf Anfrage (CAMT.033)
     */
    case CAMT034 = 'camt.034';

    /**
     * CAMT.035 - Proprietary Format Investigation
     * Proprietary investigation request
     */
    case CAMT035 = 'camt.035';

    /**
     * CAMT.036 - Debit Authorisation Response
     * Belastungsautorisierungsantwort
     */
    case CAMT036 = 'camt.036';

    /**
     * CAMT.037 - Debit Authorisation Request
     * Belastungsautorisierungsanfrage
     */
    case CAMT037 = 'camt.037';

    /**
     * CAMT.038 - Case Status Report Request
     * Fallstatusabfrage
     */
    case CAMT038 = 'camt.038';

    /**
     * CAMT.039 - Case Status Report
     * Fallstatusbericht
     */
    case CAMT039 = 'camt.039';

    /**
     * Returns the German description text.
     */
    public function getDescription(): string {
        return match ($this) {
            self::CAMT052 => 'Intraday account movement information',
            self::CAMT053 => 'Daily account statement',
            self::CAMT054 => 'Debit/Credit Notification',
            self::CAMT055 => 'Customer payment cancellation request',
            self::CAMT056 => 'Bank-to-bank payment cancellation',
            self::CAMT029 => 'Clarification response to claim',
            self::CAMT026 => 'Unable to apply payment',
            self::CAMT027 => 'Claim non-receipt of payment',
            self::CAMT028 => 'Additional payment information',
            self::CAMT087 => 'Payment modification request',
            self::CAMT030 => 'Case assignment notification',
            self::CAMT031 => 'Rejection of investigation',
            self::CAMT033 => 'Request for duplicate',
            self::CAMT057 => 'Notification about expected payment receipt',
            self::CAMT058 => 'Cancellation notice of receipt notification',
            self::CAMT059 => 'Status report of receipt notification',
            self::CAMT034 => 'Duplicate response',
            self::CAMT035 => 'Proprietary investigation request',
            self::CAMT036 => 'Debit authorization response',
            self::CAMT037 => 'Debit authorization request',
            self::CAMT038 => 'Case status query',
            self::CAMT039 => 'Case status report',
        };
    }

    /**
     * Returns the ISO 20022 message name.
     */
    public function getMessageName(): string {
        return match ($this) {
            self::CAMT052 => 'BankToCustomerAccountReport',
            self::CAMT053 => 'BankToCustomerStatement',
            self::CAMT054 => 'BankToCustomerDebitCreditNotification',
            self::CAMT055 => 'CustomerPaymentCancellationRequest',
            self::CAMT056 => 'FIToFIPaymentCancellationRequest',
            self::CAMT029 => 'ResolutionOfInvestigation',
            self::CAMT026 => 'UnableToApply',
            self::CAMT027 => 'ClaimNonReceipt',
            self::CAMT028 => 'AdditionalPaymentInformation',
            self::CAMT087 => 'RequestToModifyPayment',
            self::CAMT030 => 'NotificationOfCaseAssignment',
            self::CAMT031 => 'RejectInvestigation',
            self::CAMT033 => 'RequestForDuplicate',
            self::CAMT057 => 'NotificationToReceive',
            self::CAMT058 => 'NotificationToReceiveCancellationAdvice',
            self::CAMT059 => 'NotificationToReceiveStatusReport',
            self::CAMT034 => 'Duplicate',
            self::CAMT035 => 'ProprietaryFormatInvestigation',
            self::CAMT036 => 'DebitAuthorisationResponse',
            self::CAMT037 => 'DebitAuthorisationRequest',
            self::CAMT038 => 'CaseStatusReportRequest',
            self::CAMT039 => 'CaseStatusReport',
        };
    }

    /**
     * Returns the root element in XML.
     */
    public function getRootElement(): string {
        return match ($this) {
            self::CAMT052 => 'BkToCstmrAcctRpt',
            self::CAMT053 => 'BkToCstmrStmt',
            self::CAMT054 => 'BkToCstmrDbtCdtNtfctn',
            self::CAMT055 => 'CstmrPmtCxlReq',
            self::CAMT056 => 'FIToFIPmtCxlReq',
            self::CAMT029 => 'RsltnOfInvstgtn',
            self::CAMT026 => 'UblToApply',
            self::CAMT027 => 'ClmNonRcpt',
            self::CAMT028 => 'AddtlPmtInf',
            self::CAMT087 => 'ReqToModfyPmt',
            self::CAMT030 => 'NtfctnOfCaseAssgnmt',
            self::CAMT031 => 'RjctInvstgtn',
            self::CAMT033 => 'ReqForDplct',
            self::CAMT057 => 'NtfctnToRcv',
            self::CAMT058 => 'NtfctnToRcvCxlAdvc',
            self::CAMT059 => 'NtfctnToRcvStsRpt',
            self::CAMT034 => 'Dplct',
            self::CAMT035 => 'PrtryFrmtInvstgtn',
            self::CAMT036 => 'DbtAuthstnRspn',
            self::CAMT037 => 'DbtAuthstnReq',
            self::CAMT038 => 'CaseStsRptReq',
            self::CAMT039 => 'CaseStsRpt',
        };
    }

    /**
     * Returns the Statement/Report/Notification element.
     */
    public function getStatementElement(): string {
        return match ($this) {
            self::CAMT052 => 'Rpt',
            self::CAMT053 => 'Stmt',
            self::CAMT054 => 'Ntfctn',
            self::CAMT055 => 'Undrlyg',
            self::CAMT056 => 'Undrlyg',
            self::CAMT029 => 'CxlDtls',
            self::CAMT026 => 'Undrlyg',
            self::CAMT027 => 'Undrlyg',
            self::CAMT028 => 'Undrlyg',
            self::CAMT087 => 'Undrlyg',
            self::CAMT030 => 'Case',
            self::CAMT031 => 'Case',
            self::CAMT033 => 'Case',
            self::CAMT057 => 'Ntfctn',
            self::CAMT058 => 'OrgnlNtfctn',
            self::CAMT059 => 'OrgnlNtfctnAndSts',
            self::CAMT034 => 'Case',
            self::CAMT035 => 'PrtryData',
            self::CAMT036 => 'Conf',
            self::CAMT037 => 'Dtl',
            self::CAMT038 => 'Case',
            self::CAMT039 => 'Sts',
        };
    }

    /**
     * Checks if this is a statement type (052, 053, 054).
     */
    public function isStatementType(): bool {
        return match ($this) {
            self::CAMT052, self::CAMT053, self::CAMT054 => true,
            default => false,
        };
    }

    /**
     * Checks if this is a cancellation type (055, 056, 029).
     */
    public function isCancellationType(): bool {
        return match ($this) {
            self::CAMT055, self::CAMT056, self::CAMT029 => true,
            default => false,
        };
    }

    /**
     * Checks if this is an investigation/claim type (026, 027, 028, 030, 031, 033, 034, 035, 036, 037, 038, 039, 087).
     */
    public function isInvestigationType(): bool {
        return match ($this) {
            self::CAMT026, self::CAMT027, self::CAMT028, self::CAMT030, self::CAMT031, self::CAMT033,
            self::CAMT034, self::CAMT035, self::CAMT036, self::CAMT037, self::CAMT038, self::CAMT039, self::CAMT087 => true,
            default => false,
        };
    }

    /**
     * Checks if this is a notification type (057, 058, 059).
     */
    public function isNotificationType(): bool {
        return match ($this) {
            self::CAMT057, self::CAMT058, self::CAMT059 => true,
            default => false,
        };
    }

    /**
     * Ermittelt den CAMT-Typ aus einem XML-Dokument.
     */
    public static function fromXml(string $xmlContent): ?self {
        if (str_contains($xmlContent, 'camt.052')) {
            return self::CAMT052;
        }
        if (str_contains($xmlContent, 'camt.053')) {
            return self::CAMT053;
        }
        if (str_contains($xmlContent, 'camt.054')) {
            return self::CAMT054;
        }
        if (str_contains($xmlContent, 'camt.055')) {
            return self::CAMT055;
        }
        if (str_contains($xmlContent, 'camt.056')) {
            return self::CAMT056;
        }
        if (str_contains($xmlContent, 'camt.029')) {
            return self::CAMT029;
        }
        if (str_contains($xmlContent, 'camt.026')) {
            return self::CAMT026;
        }
        if (str_contains($xmlContent, 'camt.027')) {
            return self::CAMT027;
        }
        if (str_contains($xmlContent, 'camt.028')) {
            return self::CAMT028;
        }
        if (str_contains($xmlContent, 'camt.087')) {
            return self::CAMT087;
        }
        if (str_contains($xmlContent, 'camt.030')) {
            return self::CAMT030;
        }
        if (str_contains($xmlContent, 'camt.031')) {
            return self::CAMT031;
        }
        if (str_contains($xmlContent, 'camt.033')) {
            return self::CAMT033;
        }
        if (str_contains($xmlContent, 'camt.058')) {
            return self::CAMT058;
        }
        if (str_contains($xmlContent, 'camt.059')) {
            return self::CAMT059;
        }
        if (str_contains($xmlContent, 'camt.057')) {
            return self::CAMT057;
        }
        if (str_contains($xmlContent, 'camt.034')) {
            return self::CAMT034;
        }
        if (str_contains($xmlContent, 'camt.035')) {
            return self::CAMT035;
        }
        if (str_contains($xmlContent, 'camt.036')) {
            return self::CAMT036;
        }
        if (str_contains($xmlContent, 'camt.037')) {
            return self::CAMT037;
        }
        if (str_contains($xmlContent, 'camt.038')) {
            return self::CAMT038;
        }
        if (str_contains($xmlContent, 'camt.039')) {
            return self::CAMT039;
        }

        // Fallback: Nach Root-Element suchen
        if (str_contains($xmlContent, '<BkToCstmrAcctRpt>') || str_contains($xmlContent, '<BkToCstmrAcctRpt ')) {
            return self::CAMT052;
        }
        if (str_contains($xmlContent, '<BkToCstmrStmt>') || str_contains($xmlContent, '<BkToCstmrStmt ')) {
            return self::CAMT053;
        }
        if (str_contains($xmlContent, '<BkToCstmrDbtCdtNtfctn>') || str_contains($xmlContent, '<BkToCstmrDbtCdtNtfctn ')) {
            return self::CAMT054;
        }
        if (str_contains($xmlContent, '<CstmrPmtCxlReq>') || str_contains($xmlContent, '<CstmrPmtCxlReq ')) {
            return self::CAMT055;
        }
        if (str_contains($xmlContent, '<FIToFIPmtCxlReq>') || str_contains($xmlContent, '<FIToFIPmtCxlReq ')) {
            return self::CAMT056;
        }
        if (str_contains($xmlContent, '<RsltnOfInvstgtn>') || str_contains($xmlContent, '<RsltnOfInvstgtn ')) {
            return self::CAMT029;
        }
        if (str_contains($xmlContent, '<UblToApply>') || str_contains($xmlContent, '<UblToApply ')) {
            return self::CAMT026;
        }
        if (str_contains($xmlContent, '<ClmNonRcpt>') || str_contains($xmlContent, '<ClmNonRcpt ')) {
            return self::CAMT027;
        }
        if (str_contains($xmlContent, '<AddtlPmtInf>') || str_contains($xmlContent, '<AddtlPmtInf ')) {
            return self::CAMT028;
        }
        if (str_contains($xmlContent, '<ReqToModfyPmt>') || str_contains($xmlContent, '<ReqToModfyPmt ')) {
            return self::CAMT087;
        }
        if (str_contains($xmlContent, '<NtfctnOfCaseAssgnmt>') || str_contains($xmlContent, '<NtfctnOfCaseAssgnmt ')) {
            return self::CAMT030;
        }
        if (str_contains($xmlContent, '<RjctInvstgtn>') || str_contains($xmlContent, '<RjctInvstgtn ')) {
            return self::CAMT031;
        }
        if (str_contains($xmlContent, '<ReqForDplct>') || str_contains($xmlContent, '<ReqForDplct ')) {
            return self::CAMT033;
        }
        if (str_contains($xmlContent, '<NtfctnToRcv>') || str_contains($xmlContent, '<NtfctnToRcv ')) {
            return self::CAMT057;
        }
        if (str_contains($xmlContent, '<NtfctnToRcvCxlAdvc>') || str_contains($xmlContent, '<NtfctnToRcvCxlAdvc ')) {
            return self::CAMT058;
        }
        if (str_contains($xmlContent, '<NtfctnToRcvStsRpt>') || str_contains($xmlContent, '<NtfctnToRcvStsRpt ')) {
            return self::CAMT059;
        }
        if (str_contains($xmlContent, '<Dplct>') || str_contains($xmlContent, '<Dplct ')) {
            return self::CAMT034;
        }
        if (str_contains($xmlContent, '<PrtryFrmtInvstgtn>') || str_contains($xmlContent, '<PrtryFrmtInvstgtn ')) {
            return self::CAMT035;
        }
        if (str_contains($xmlContent, '<DbtAuthstnRspn>') || str_contains($xmlContent, '<DbtAuthstnRspn ')) {
            return self::CAMT036;
        }
        if (str_contains($xmlContent, '<DbtAuthstnReq>') || str_contains($xmlContent, '<DbtAuthstnReq ')) {
            return self::CAMT037;
        }
        if (str_contains($xmlContent, '<CaseStsRptReq>') || str_contains($xmlContent, '<CaseStsRptReq ')) {
            return self::CAMT038;
        }
        if (str_contains($xmlContent, '<CaseStsRpt>') || str_contains($xmlContent, '<CaseStsRpt ')) {
            return self::CAMT039;
        }

        return null;
    }

    /**
     * Returns the supported versions for this CAMT type.
     * @return CamtVersion[]
     */
    public function getSupportedVersions(): array {
        return match ($this) {
            self::CAMT052 => [
                CamtVersion::V02,
                CamtVersion::V06,
                CamtVersion::V08,
                CamtVersion::V10,
                CamtVersion::V12,
                CamtVersion::V13,
            ],
            self::CAMT053 => [
                CamtVersion::V02,
                CamtVersion::V04,
                CamtVersion::V08,
                CamtVersion::V10,
                CamtVersion::V12,
                CamtVersion::V13,
            ],
            self::CAMT054 => [
                CamtVersion::V02,
                CamtVersion::V08,
                CamtVersion::V13,
            ],
            self::CAMT055 => [
                CamtVersion::V12,
            ],
            self::CAMT056 => [
                CamtVersion::V11,
            ],
            self::CAMT029 => [
                CamtVersion::V13,
            ],
            self::CAMT026 => [
                CamtVersion::V10,
            ],
            self::CAMT027 => [
                CamtVersion::V10,
            ],
            self::CAMT028 => [
                CamtVersion::V12,
            ],
            self::CAMT087 => [
                CamtVersion::V09,
            ],
            self::CAMT030 => [
                CamtVersion::V06,
            ],
            self::CAMT031 => [
                CamtVersion::V07,
            ],
            self::CAMT033 => [
                CamtVersion::V07,
            ],
            self::CAMT057 => [
                CamtVersion::V08,
            ],
            self::CAMT058 => [
                CamtVersion::V09,
            ],
            self::CAMT059 => [
                CamtVersion::V08,
            ],
            self::CAMT034 => [
                CamtVersion::V07,
            ],
            self::CAMT035 => [
                CamtVersion::V06,
            ],
            self::CAMT036 => [
                CamtVersion::V06,
            ],
            self::CAMT037 => [
                CamtVersion::V10,
            ],
            self::CAMT038 => [
                CamtVersion::V05,
            ],
            self::CAMT039 => [
                CamtVersion::V06,
            ],
        };
    }

    /**
     * Returns the namespace for a specific version.
     */
    public function getNamespace(CamtVersion $version): string {
        return $version->getNamespace($this);
    }

    /**
     * Checks if a version is supported for this CAMT type.
     */
    public function supportsVersion(CamtVersion $version): bool {
        return in_array($version, $this->getSupportedVersions(), true);
    }

    /**
     * Returns the supported namespace URIs.
     * @return array<string, string> Version => Namespace-URI
     * @deprecated Verwende getSupportedVersions() und getNamespace() stattdessen
     */
    public function getNamespaces(): array {
        $namespaces = [];
        foreach ($this->getSupportedVersions() as $version) {
            $namespaces[$version->value] = $version->getNamespace($this);
        }
        return $namespaces;
    }
}
