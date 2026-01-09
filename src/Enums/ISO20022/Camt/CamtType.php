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

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Camt;

/**
 * CAMT message types according to ISO 20022.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum CamtType: string {
    // =========================================================================
    // LIQUIDITY & CASH MANAGEMENT (003-025)
    // =========================================================================

    /**
     * CAMT.003 - Get Account
     * Request for account information
     */
    case CAMT003 = 'camt.003';

    /**
     * CAMT.004 - Return Account
     * Response to account information request
     */
    case CAMT004 = 'camt.004';

    /**
     * CAMT.005 - Get Transaction
     * Request for transaction details
     */
    case CAMT005 = 'camt.005';

    /**
     * CAMT.006 - Return Transaction
     * Response with transaction details
     */
    case CAMT006 = 'camt.006';

    /**
     * CAMT.007 - Modify Transaction
     * Request to modify a transaction
     */
    case CAMT007 = 'camt.007';

    /**
     * CAMT.008 - Cancel Transaction
     * Request to cancel a transaction
     */
    case CAMT008 = 'camt.008';

    /**
     * CAMT.009 - Get Limit
     * Request for limit information
     */
    case CAMT009 = 'camt.009';

    /**
     * CAMT.010 - Return Limit
     * Response with limit information
     */
    case CAMT010 = 'camt.010';

    /**
     * CAMT.011 - Modify Limit
     * Request to modify a limit
     */
    case CAMT011 = 'camt.011';

    /**
     * CAMT.012 - Delete Limit
     * Request to delete a limit
     */
    case CAMT012 = 'camt.012';

    /**
     * CAMT.013 - Get Member
     * Request for member information
     */
    case CAMT013 = 'camt.013';

    /**
     * CAMT.014 - Return Member
     * Response with member information
     */
    case CAMT014 = 'camt.014';

    /**
     * CAMT.015 - Modify Member
     * Request to modify member details
     */
    case CAMT015 = 'camt.015';

    /**
     * CAMT.016 - Get Currency Exchange Rate
     * Request for currency exchange rate
     */
    case CAMT016 = 'camt.016';

    /**
     * CAMT.017 - Return Currency Exchange Rate
     * Response with currency exchange rate
     */
    case CAMT017 = 'camt.017';

    /**
     * CAMT.018 - Get Business Day Information
     * Request for business day information
     */
    case CAMT018 = 'camt.018';

    /**
     * CAMT.019 - Return Business Day Information
     * Response with business day information
     */
    case CAMT019 = 'camt.019';

    /**
     * CAMT.020 - Get General Business Information
     * Request for general business information
     */
    case CAMT020 = 'camt.020';

    /**
     * CAMT.021 - Return General Business Information
     * Response with general business information
     */
    case CAMT021 = 'camt.021';

    /**
     * CAMT.023 - Backup Payment
     * Backup payment instruction
     */
    case CAMT023 = 'camt.023';

    /**
     * CAMT.024 - Modify Standing Order
     * Request to modify standing order
     */
    case CAMT024 = 'camt.024';

    /**
     * CAMT.025 - Receipt
     * Receipt acknowledgement
     */
    case CAMT025 = 'camt.025';

    // =========================================================================
    // INVESTIGATION & CLAIMS (026-039)
    // =========================================================================

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
     * CAMT.029 - Resolution of Investigation
     * Clarification response to claim
     */
    case CAMT029 = 'camt.029';

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
     * CAMT.032 - Cancel Case Assignment
     * Request to cancel a case assignment
     */
    case CAMT032 = 'camt.032';

    /**
     * CAMT.033 - Request for Duplicate
     * Request for duplicate
     */
    case CAMT033 = 'camt.033';

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

    // =========================================================================
    // FUND PROCESSING (040-051)
    // =========================================================================

    /**
     * CAMT.040 - Fund Estimated Cash Forecast Report
     * Estimated cash forecast for funds
     */
    case CAMT040 = 'camt.040';

    /**
     * CAMT.041 - Fund Confirmed Cash Forecast Report
     * Confirmed cash forecast for funds
     */
    case CAMT041 = 'camt.041';

    /**
     * CAMT.042 - Fund Detailed Estimated Cash Forecast Report
     * Detailed estimated cash forecast
     */
    case CAMT042 = 'camt.042';

    /**
     * CAMT.043 - Fund Detailed Confirmed Cash Forecast Report
     * Detailed confirmed cash forecast
     */
    case CAMT043 = 'camt.043';

    /**
     * CAMT.044 - Fund Confirmed Cash Forecast Report Cancellation
     * Cancellation of confirmed cash forecast
     */
    case CAMT044 = 'camt.044';

    /**
     * CAMT.045 - Fund Detailed Confirmed Cash Forecast Report Cancellation
     * Cancellation of detailed confirmed cash forecast
     */
    case CAMT045 = 'camt.045';

    /**
     * CAMT.046 - Get Reservation
     * Request for reservation information
     */
    case CAMT046 = 'camt.046';

    /**
     * CAMT.047 - Return Reservation
     * Response with reservation information
     */
    case CAMT047 = 'camt.047';

    /**
     * CAMT.048 - Modify Reservation
     * Request to modify a reservation
     */
    case CAMT048 = 'camt.048';

    /**
     * CAMT.049 - Delete Reservation
     * Request to delete a reservation
     */
    case CAMT049 = 'camt.049';

    /**
     * CAMT.050 - Liquidity Credit Transfer
     * Liquidity credit transfer instruction
     */
    case CAMT050 = 'camt.050';

    /**
     * CAMT.051 - Liquidity Debit Transfer
     * Liquidity debit transfer instruction
     */
    case CAMT051 = 'camt.051';

    // =========================================================================
    // BANK TO CUSTOMER STATEMENTS (052-054)
    // =========================================================================

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

    // =========================================================================
    // ACCOUNT REPORTING & BILLING (060-088)
    // =========================================================================

    /**
     * CAMT.060 - Account Reporting Request
     * Request for account reporting
     */
    case CAMT060 = 'camt.060';

    /**
     * CAMT.061 - Pay In Call
     * Pay-in call notification
     */
    case CAMT061 = 'camt.061';

    /**
     * CAMT.062 - Pay In Schedule
     * Pay-in schedule information
     */
    case CAMT062 = 'camt.062';

    /**
     * CAMT.063 - Pay In Event Acknowledgement
     * Acknowledgement of pay-in event
     */
    case CAMT063 = 'camt.063';

    /**
     * CAMT.064 - Limit Utilisation Journal Query
     * Query for limit utilisation journal
     */
    case CAMT064 = 'camt.064';

    /**
     * CAMT.065 - Limit Utilisation Journal Report
     * Report on limit utilisation journal
     */
    case CAMT065 = 'camt.065';

    /**
     * CAMT.066 - Intra Balance Movement Instruction
     * Instruction for intra-balance movement
     */
    case CAMT066 = 'camt.066';

    /**
     * CAMT.067 - Intra Balance Movement Status Advice
     * Status advice for intra-balance movement
     */
    case CAMT067 = 'camt.067';

    /**
     * CAMT.068 - Intra Balance Movement Confirmation
     * Confirmation of intra-balance movement
     */
    case CAMT068 = 'camt.068';

    /**
     * CAMT.069 - Get Standing Order
     * Request for standing order information
     */
    case CAMT069 = 'camt.069';

    /**
     * CAMT.070 - Return Standing Order
     * Response with standing order information
     */
    case CAMT070 = 'camt.070';

    /**
     * CAMT.071 - Delete Standing Order
     * Request to delete a standing order
     */
    case CAMT071 = 'camt.071';

    /**
     * CAMT.072 - Intra Balance Movement Modification Request
     * Request to modify intra-balance movement
     */
    case CAMT072 = 'camt.072';

    /**
     * CAMT.073 - Intra Balance Movement Modification Request Status Advice
     * Status advice for modification request
     */
    case CAMT073 = 'camt.073';

    /**
     * CAMT.074 - Intra Balance Movement Cancellation Request
     * Request to cancel intra-balance movement
     */
    case CAMT074 = 'camt.074';

    /**
     * CAMT.075 - Intra Balance Movement Cancellation Request Status Advice
     * Status advice for cancellation request
     */
    case CAMT075 = 'camt.075';

    /**
     * CAMT.076 - Billing Report Request
     * Request for billing report
     */
    case CAMT076 = 'camt.076';

    /**
     * CAMT.077 - Billing Report
     * Billing report response
     */
    case CAMT077 = 'camt.077';

    /**
     * CAMT.078 - Intra Balance Movement Query
     * Query for intra-balance movement
     */
    case CAMT078 = 'camt.078';

    /**
     * CAMT.079 - Intra Balance Movement Query Response
     * Response to intra-balance movement query
     */
    case CAMT079 = 'camt.079';

    /**
     * CAMT.080 - Intra Balance Movement Modification Query
     * Query for modification of intra-balance movement
     */
    case CAMT080 = 'camt.080';

    /**
     * CAMT.081 - Intra Balance Movement Modification Report
     * Report on modification of intra-balance movement
     */
    case CAMT081 = 'camt.081';

    /**
     * CAMT.082 - Intra Balance Movement Cancellation Query
     * Query for cancellation of intra-balance movement
     */
    case CAMT082 = 'camt.082';

    /**
     * CAMT.083 - Intra Balance Movement Cancellation Report
     * Report on cancellation of intra-balance movement
     */
    case CAMT083 = 'camt.083';

    /**
     * CAMT.084 - Intra Balance Movement Posting Report
     * Posting report for intra-balance movement
     */
    case CAMT084 = 'camt.084';

    /**
     * CAMT.085 - Intra Balance Movement Pending Report
     * Pending report for intra-balance movement
     */
    case CAMT085 = 'camt.085';

    /**
     * CAMT.086 - Bank Services Billing Statement
     * Statement for bank service charges
     */
    case CAMT086 = 'camt.086';

    /**
     * CAMT.087 - Request to Modify Payment
     * Payment modification request
     */
    case CAMT087 = 'camt.087';

    /**
     * CAMT.088 - Net Report
     * Net position report
     */
    case CAMT088 = 'camt.088';

    // =========================================================================
    // STANDING ORDERS & CHARGES (101-109)
    // =========================================================================

    /**
     * CAMT.101 - Create Limit
     * Request to create a limit
     */
    case CAMT101 = 'camt.101';

    /**
     * CAMT.102 - Create Standing Order
     * Request to create a standing order
     */
    case CAMT102 = 'camt.102';

    /**
     * CAMT.103 - Create Reservation
     * Request to create a reservation
     */
    case CAMT103 = 'camt.103';

    /**
     * CAMT.104 - Create Member
     * Request to create a member
     */
    case CAMT104 = 'camt.104';

    /**
     * CAMT.105 - Charges Payment Notification
     * Notification of charges payment
     */
    case CAMT105 = 'camt.105';

    /**
     * CAMT.106 - Charges Payment Request
     * Request for charges payment
     */
    case CAMT106 = 'camt.106';

    /**
     * CAMT.107 - Cheque Presentment Notification
     * Notification of cheque presentment
     */
    case CAMT107 = 'camt.107';

    /**
     * CAMT.108 - Cheque Cancellation or Stop Request
     * Request to cancel or stop a cheque
     */
    case CAMT108 = 'camt.108';

    /**
     * CAMT.109 - Cheque Cancellation or Stop Report
     * Report on cheque cancellation or stop
     */
    case CAMT109 = 'camt.109';

    /**
     * Returns the description text.
     */
    public function getDescription(): string {
        return match ($this) {
            // Liquidity & Cash Management (003-025)
            self::CAMT003 => 'Get account information request',
            self::CAMT004 => 'Return account information',
            self::CAMT005 => 'Get transaction details request',
            self::CAMT006 => 'Return transaction details',
            self::CAMT007 => 'Modify transaction request',
            self::CAMT008 => 'Cancel transaction request',
            self::CAMT009 => 'Get limit information request',
            self::CAMT010 => 'Return limit information',
            self::CAMT011 => 'Modify limit request',
            self::CAMT012 => 'Delete limit request',
            self::CAMT013 => 'Get member information request',
            self::CAMT014 => 'Return member information',
            self::CAMT015 => 'Modify member request',
            self::CAMT016 => 'Get currency exchange rate request',
            self::CAMT017 => 'Return currency exchange rate',
            self::CAMT018 => 'Get business day information request',
            self::CAMT019 => 'Return business day information',
            self::CAMT020 => 'Get general business information request',
            self::CAMT021 => 'Return general business information',
            self::CAMT023 => 'Backup payment instruction',
            self::CAMT024 => 'Modify standing order request',
            self::CAMT025 => 'Receipt acknowledgement',
            // Investigation & Claims (026-039)
            self::CAMT026 => 'Unable to apply payment',
            self::CAMT027 => 'Claim non-receipt of payment',
            self::CAMT028 => 'Additional payment information',
            self::CAMT029 => 'Resolution of investigation',
            self::CAMT030 => 'Case assignment notification',
            self::CAMT031 => 'Rejection of investigation',
            self::CAMT032 => 'Cancel case assignment request',
            self::CAMT033 => 'Request for duplicate',
            self::CAMT034 => 'Duplicate response',
            self::CAMT035 => 'Proprietary investigation request',
            self::CAMT036 => 'Debit authorization response',
            self::CAMT037 => 'Debit authorization request',
            self::CAMT038 => 'Case status query',
            self::CAMT039 => 'Case status report',
            // Fund Processing (040-051)
            self::CAMT040 => 'Fund estimated cash forecast report',
            self::CAMT041 => 'Fund confirmed cash forecast report',
            self::CAMT042 => 'Fund detailed estimated cash forecast report',
            self::CAMT043 => 'Fund detailed confirmed cash forecast report',
            self::CAMT044 => 'Fund confirmed cash forecast cancellation',
            self::CAMT045 => 'Fund detailed confirmed cash forecast cancellation',
            self::CAMT046 => 'Get reservation information request',
            self::CAMT047 => 'Return reservation information',
            self::CAMT048 => 'Modify reservation request',
            self::CAMT049 => 'Delete reservation request',
            self::CAMT050 => 'Liquidity credit transfer',
            self::CAMT051 => 'Liquidity debit transfer',
            // Bank to Customer Statements (052-054)
            self::CAMT052 => 'Intraday account movement information',
            self::CAMT053 => 'Daily account statement',
            self::CAMT054 => 'Debit/Credit Notification',
            // Payment Cancellation (055-056)
            self::CAMT055 => 'Customer payment cancellation request',
            self::CAMT056 => 'Bank-to-bank payment cancellation',
            // Notifications (057-059)
            self::CAMT057 => 'Notification about expected payment receipt',
            self::CAMT058 => 'Cancellation notice of receipt notification',
            self::CAMT059 => 'Status report of receipt notification',
            // Account Reporting & Billing (060-088)
            self::CAMT060 => 'Account reporting request',
            self::CAMT061 => 'Pay-in call notification',
            self::CAMT062 => 'Pay-in schedule information',
            self::CAMT063 => 'Pay-in event acknowledgement',
            self::CAMT064 => 'Limit utilisation journal query',
            self::CAMT065 => 'Limit utilisation journal report',
            self::CAMT066 => 'Intra-balance movement instruction',
            self::CAMT067 => 'Intra-balance movement status advice',
            self::CAMT068 => 'Intra-balance movement confirmation',
            self::CAMT069 => 'Get standing order request',
            self::CAMT070 => 'Return standing order information',
            self::CAMT071 => 'Delete standing order request',
            self::CAMT072 => 'Intra-balance movement modification request',
            self::CAMT073 => 'Intra-balance movement modification status advice',
            self::CAMT074 => 'Intra-balance movement cancellation request',
            self::CAMT075 => 'Intra-balance movement cancellation status advice',
            self::CAMT076 => 'Billing report request',
            self::CAMT077 => 'Billing report',
            self::CAMT078 => 'Intra-balance movement query',
            self::CAMT079 => 'Intra-balance movement query response',
            self::CAMT080 => 'Intra-balance movement modification query',
            self::CAMT081 => 'Intra-balance movement modification report',
            self::CAMT082 => 'Intra-balance movement cancellation query',
            self::CAMT083 => 'Intra-balance movement cancellation report',
            self::CAMT084 => 'Intra-balance movement posting report',
            self::CAMT085 => 'Intra-balance movement pending report',
            self::CAMT086 => 'Bank services billing statement',
            self::CAMT087 => 'Payment modification request',
            self::CAMT088 => 'Net position report',
            // Standing Orders & Charges (101-109)
            self::CAMT101 => 'Create limit request',
            self::CAMT102 => 'Create standing order request',
            self::CAMT103 => 'Create reservation request',
            self::CAMT104 => 'Create member request',
            self::CAMT105 => 'Charges payment notification',
            self::CAMT106 => 'Charges payment request',
            self::CAMT107 => 'Cheque presentment notification',
            self::CAMT108 => 'Cheque cancellation or stop request',
            self::CAMT109 => 'Cheque cancellation or stop report',
        };
    }

    /**
     * Returns the ISO 20022 message name.
     */
    public function getMessageName(): string {
        return match ($this) {
            // Liquidity & Cash Management (003-025)
            self::CAMT003 => 'GetAccount',
            self::CAMT004 => 'ReturnAccount',
            self::CAMT005 => 'GetTransaction',
            self::CAMT006 => 'ReturnTransaction',
            self::CAMT007 => 'ModifyTransaction',
            self::CAMT008 => 'CancelTransaction',
            self::CAMT009 => 'GetLimit',
            self::CAMT010 => 'ReturnLimit',
            self::CAMT011 => 'ModifyLimit',
            self::CAMT012 => 'DeleteLimit',
            self::CAMT013 => 'GetMember',
            self::CAMT014 => 'ReturnMember',
            self::CAMT015 => 'ModifyMember',
            self::CAMT016 => 'GetCurrencyExchangeRate',
            self::CAMT017 => 'ReturnCurrencyExchangeRate',
            self::CAMT018 => 'GetBusinessDayInformation',
            self::CAMT019 => 'ReturnBusinessDayInformation',
            self::CAMT020 => 'GetGeneralBusinessInformation',
            self::CAMT021 => 'ReturnGeneralBusinessInformation',
            self::CAMT023 => 'BackupPayment',
            self::CAMT024 => 'ModifyStandingOrder',
            self::CAMT025 => 'Receipt',
            // Investigation & Claims (026-039)
            self::CAMT026 => 'UnableToApply',
            self::CAMT027 => 'ClaimNonReceipt',
            self::CAMT028 => 'AdditionalPaymentInformation',
            self::CAMT029 => 'ResolutionOfInvestigation',
            self::CAMT030 => 'NotificationOfCaseAssignment',
            self::CAMT031 => 'RejectInvestigation',
            self::CAMT032 => 'CancelCaseAssignment',
            self::CAMT033 => 'RequestForDuplicate',
            self::CAMT034 => 'Duplicate',
            self::CAMT035 => 'ProprietaryFormatInvestigation',
            self::CAMT036 => 'DebitAuthorisationResponse',
            self::CAMT037 => 'DebitAuthorisationRequest',
            self::CAMT038 => 'CaseStatusReportRequest',
            self::CAMT039 => 'CaseStatusReport',
            // Fund Processing (040-051)
            self::CAMT040 => 'FundEstimatedCashForecastReport',
            self::CAMT041 => 'FundConfirmedCashForecastReport',
            self::CAMT042 => 'FundDetailedEstimatedCashForecastReport',
            self::CAMT043 => 'FundDetailedConfirmedCashForecastReport',
            self::CAMT044 => 'FundConfirmedCashForecastReportCancellation',
            self::CAMT045 => 'FundDetailedConfirmedCashForecastReportCancellation',
            self::CAMT046 => 'GetReservation',
            self::CAMT047 => 'ReturnReservation',
            self::CAMT048 => 'ModifyReservation',
            self::CAMT049 => 'DeleteReservation',
            self::CAMT050 => 'LiquidityCreditTransfer',
            self::CAMT051 => 'LiquidityDebitTransfer',
            // Bank to Customer Statements (052-054)
            self::CAMT052 => 'BankToCustomerAccountReport',
            self::CAMT053 => 'BankToCustomerStatement',
            self::CAMT054 => 'BankToCustomerDebitCreditNotification',
            // Payment Cancellation (055-056)
            self::CAMT055 => 'CustomerPaymentCancellationRequest',
            self::CAMT056 => 'FIToFIPaymentCancellationRequest',
            // Notifications (057-059)
            self::CAMT057 => 'NotificationToReceive',
            self::CAMT058 => 'NotificationToReceiveCancellationAdvice',
            self::CAMT059 => 'NotificationToReceiveStatusReport',
            // Account Reporting & Billing (060-088)
            self::CAMT060 => 'AccountReportingRequest',
            self::CAMT061 => 'PayInCall',
            self::CAMT062 => 'PayInSchedule',
            self::CAMT063 => 'PayInEventAcknowledgement',
            self::CAMT064 => 'LimitUtilisationJournalQuery',
            self::CAMT065 => 'LimitUtilisationJournalReport',
            self::CAMT066 => 'IntraBalanceMovementInstruction',
            self::CAMT067 => 'IntraBalanceMovementStatusAdvice',
            self::CAMT068 => 'IntraBalanceMovementConfirmation',
            self::CAMT069 => 'GetStandingOrder',
            self::CAMT070 => 'ReturnStandingOrder',
            self::CAMT071 => 'DeleteStandingOrder',
            self::CAMT072 => 'IntraBalanceMovementModificationRequest',
            self::CAMT073 => 'IntraBalanceMovementModificationRequestStatusAdvice',
            self::CAMT074 => 'IntraBalanceMovementCancellationRequest',
            self::CAMT075 => 'IntraBalanceMovementCancellationRequestStatusAdvice',
            self::CAMT076 => 'BillingReportRequest',
            self::CAMT077 => 'BillingReport',
            self::CAMT078 => 'IntraBalanceMovementQuery',
            self::CAMT079 => 'IntraBalanceMovementQueryResponse',
            self::CAMT080 => 'IntraBalanceMovementModificationQuery',
            self::CAMT081 => 'IntraBalanceMovementModificationReport',
            self::CAMT082 => 'IntraBalanceMovementCancellationQuery',
            self::CAMT083 => 'IntraBalanceMovementCancellationReport',
            self::CAMT084 => 'IntraBalanceMovementPostingReport',
            self::CAMT085 => 'IntraBalanceMovementPendingReport',
            self::CAMT086 => 'BankServicesBillingStatement',
            self::CAMT087 => 'RequestToModifyPayment',
            self::CAMT088 => 'NetReport',
            // Standing Orders & Charges (101-109)
            self::CAMT101 => 'CreateLimit',
            self::CAMT102 => 'CreateStandingOrder',
            self::CAMT103 => 'CreateReservation',
            self::CAMT104 => 'CreateMember',
            self::CAMT105 => 'ChargesPaymentNotification',
            self::CAMT106 => 'ChargesPaymentRequest',
            self::CAMT107 => 'ChequePresntmentNotification',
            self::CAMT108 => 'ChequeCancellationOrStopRequest',
            self::CAMT109 => 'ChequeCancellationOrStopReport',
        };
    }

    /**
     * Returns the root element in XML.
     */
    public function getRootElement(): string {
        return match ($this) {
            // Liquidity & Cash Management (003-025)
            self::CAMT003 => 'GetAcct',
            self::CAMT004 => 'RtrAcct',
            self::CAMT005 => 'GetTx',
            self::CAMT006 => 'RtrTx',
            self::CAMT007 => 'ModfyTx',
            self::CAMT008 => 'CclTx',
            self::CAMT009 => 'GetLmt',
            self::CAMT010 => 'RtrLmt',
            self::CAMT011 => 'ModfyLmt',
            self::CAMT012 => 'DelLmt',
            self::CAMT013 => 'GetMmb',
            self::CAMT014 => 'RtrMmb',
            self::CAMT015 => 'ModfyMmb',
            self::CAMT016 => 'GetCcyXchgRate',
            self::CAMT017 => 'RtrCcyXchgRate',
            self::CAMT018 => 'GetBizDayInf',
            self::CAMT019 => 'RtrBizDayInf',
            self::CAMT020 => 'GetGnlBizInf',
            self::CAMT021 => 'RtrGnlBizInf',
            self::CAMT023 => 'BckpPmt',
            self::CAMT024 => 'ModfyStgOrdr',
            self::CAMT025 => 'Rct',
            // Investigation & Claims (026-039)
            self::CAMT026 => 'UblToApply',
            self::CAMT027 => 'ClmNonRcpt',
            self::CAMT028 => 'AddtlPmtInf',
            self::CAMT029 => 'RsltnOfInvstgtn',
            self::CAMT030 => 'NtfctnOfCaseAssgnmt',
            self::CAMT031 => 'RjctInvstgtn',
            self::CAMT032 => 'CclCaseAssgnmt',
            self::CAMT033 => 'ReqForDplct',
            self::CAMT034 => 'Dplct',
            self::CAMT035 => 'PrtryFrmtInvstgtn',
            self::CAMT036 => 'DbtAuthstnRspn',
            self::CAMT037 => 'DbtAuthstnReq',
            self::CAMT038 => 'CaseStsRptReq',
            self::CAMT039 => 'CaseStsRpt',
            // Fund Processing (040-051)
            self::CAMT040 => 'FndEstmtdCshFcstRpt',
            self::CAMT041 => 'FndConfdCshFcstRpt',
            self::CAMT042 => 'FndDtldEstmtdCshFcstRpt',
            self::CAMT043 => 'FndDtldConfdCshFcstRpt',
            self::CAMT044 => 'FndConfdCshFcstRptCxl',
            self::CAMT045 => 'FndDtldConfdCshFcstRptCxl',
            self::CAMT046 => 'GetRsvatn',
            self::CAMT047 => 'RtrRsvatn',
            self::CAMT048 => 'ModfyRsvatn',
            self::CAMT049 => 'DelRsvatn',
            self::CAMT050 => 'LqdtyCdtTrf',
            self::CAMT051 => 'LqdtyDbtTrf',
            // Bank to Customer Statements (052-054)
            self::CAMT052 => 'BkToCstmrAcctRpt',
            self::CAMT053 => 'BkToCstmrStmt',
            self::CAMT054 => 'BkToCstmrDbtCdtNtfctn',
            // Payment Cancellation (055-056)
            self::CAMT055 => 'CstmrPmtCxlReq',
            self::CAMT056 => 'FIToFIPmtCxlReq',
            // Notifications (057-059)
            self::CAMT057 => 'NtfctnToRcv',
            self::CAMT058 => 'NtfctnToRcvCxlAdvc',
            self::CAMT059 => 'NtfctnToRcvStsRpt',
            // Account Reporting & Billing (060-088)
            self::CAMT060 => 'AcctRptgReq',
            self::CAMT061 => 'PayInCall',
            self::CAMT062 => 'PayInSchdl',
            self::CAMT063 => 'PayInEvtAck',
            self::CAMT064 => 'LmtUtlstnJrnlQry',
            self::CAMT065 => 'LmtUtlstnJrnlRpt',
            self::CAMT066 => 'IntraBalMvmntInstr',
            self::CAMT067 => 'IntraBalMvmntStsAdvc',
            self::CAMT068 => 'IntraBalMvmntConf',
            self::CAMT069 => 'GetStgOrdr',
            self::CAMT070 => 'RtrStgOrdr',
            self::CAMT071 => 'DelStgOrdr',
            self::CAMT072 => 'IntraBalMvmntModReq',
            self::CAMT073 => 'IntraBalMvmntModReqStsAdvc',
            self::CAMT074 => 'IntraBalMvmntCxlReq',
            self::CAMT075 => 'IntraBalMvmntCxlReqStsAdvc',
            self::CAMT076 => 'BllgRptReq',
            self::CAMT077 => 'BllgRpt',
            self::CAMT078 => 'IntraBalMvmntQry',
            self::CAMT079 => 'IntraBalMvmntQryRspn',
            self::CAMT080 => 'IntraBalMvmntModQry',
            self::CAMT081 => 'IntraBalMvmntModRpt',
            self::CAMT082 => 'IntraBalMvmntCxlQry',
            self::CAMT083 => 'IntraBalMvmntCxlRpt',
            self::CAMT084 => 'IntraBalMvmntPstngRpt',
            self::CAMT085 => 'IntraBalMvmntPdgRpt',
            self::CAMT086 => 'BkSvcsBllgStmt',
            self::CAMT087 => 'ReqToModfyPmt',
            self::CAMT088 => 'NetRpt',
            // Standing Orders & Charges (101-109)
            self::CAMT101 => 'CretLmt',
            self::CAMT102 => 'CretStgOrdr',
            self::CAMT103 => 'CretRsvatn',
            self::CAMT104 => 'CretMmb',
            self::CAMT105 => 'ChrgsPmtNtfctn',
            self::CAMT106 => 'ChrgsPmtReq',
            self::CAMT107 => 'ChqPresntmntNtfctn',
            self::CAMT108 => 'ChqCxlOrStopReq',
            self::CAMT109 => 'ChqCxlOrStopRpt',
        };
    }

    /**
     * Returns the Statement/Report/Notification element.
     * Returns null for types that don't have a statement-like structure.
     */
    public function getStatementElement(): ?string {
        return match ($this) {
            // Bank to Customer Statements
            self::CAMT052 => 'Rpt',
            self::CAMT053 => 'Stmt',
            self::CAMT054 => 'Ntfctn',
            // Payment Cancellation
            self::CAMT055 => 'Undrlyg',
            self::CAMT056 => 'Undrlyg',
            // Investigation
            self::CAMT026 => 'Undrlyg',
            self::CAMT027 => 'Undrlyg',
            self::CAMT028 => 'Undrlyg',
            self::CAMT029 => 'CxlDtls',
            self::CAMT030 => 'Case',
            self::CAMT031 => 'Case',
            self::CAMT032 => 'Case',
            self::CAMT033 => 'Case',
            self::CAMT034 => 'Case',
            self::CAMT035 => 'PrtryData',
            self::CAMT036 => 'Conf',
            self::CAMT037 => 'Dtl',
            self::CAMT038 => 'Case',
            self::CAMT039 => 'Sts',
            self::CAMT087 => 'Undrlyg',
            // Notifications
            self::CAMT057 => 'Ntfctn',
            self::CAMT058 => 'OrgnlNtfctn',
            self::CAMT059 => 'OrgnlNtfctnAndSts',
            // Fund Processing
            self::CAMT040 => 'EstmtdFndCshFcst',
            self::CAMT041 => 'FndCshFcst',
            self::CAMT042 => 'EstmtdFndCshFcstDtls',
            self::CAMT043 => 'FndCshFcstDtls',
            self::CAMT044 => 'FndCshFcstCxl',
            self::CAMT045 => 'FndCshFcstDtlsCxl',
            // Billing
            self::CAMT086 => 'BllgStmt',
            // Default: No specific statement element
            default => null,
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
     * Determines the CAMT type from an XML document.
     * First tries to match the namespace pattern in the Document element,
     * then falls back to root element matching.
     */
    public static function fromXml(string $xmlContent): ?self {
        // Try to find CAMT type in namespace declaration (e.g., xmlns="urn:iso:std:iso:20022:tech:xsd:camt.053.001.08")
        // This is more reliable than searching the entire content
        if (preg_match('/xmlns[^=]*=\s*["\']urn:iso:std:iso:20022:tech:xsd:(camt\.\d{3})\.001\.\d{2}["\']/', $xmlContent, $matches)) {
            return self::tryFrom($matches[1]);
        }

        // Fallback: Try to match root element
        // Sort cases by root element length descending to match longer patterns first
        $sortedCases = self::cases();
        usort($sortedCases, fn($a, $b) => strlen($b->getRootElement()) <=> strlen($a->getRootElement()));

        foreach ($sortedCases as $case) {
            $rootElement = $case->getRootElement();
            if (str_contains($xmlContent, "<{$rootElement}>") || str_contains($xmlContent, "<{$rootElement} ")) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Returns the supported versions for this CAMT type.
     * Based on XSD files available in data/xsd/camt/.
     * @return CamtVersion[]
     */
    public function getSupportedVersions(): array {
        return match ($this) {
            // Liquidity & Cash Management (003-025)
            self::CAMT003 => [CamtVersion::V08],
            self::CAMT004 => [CamtVersion::V10],
            self::CAMT005 => [CamtVersion::V11],
            self::CAMT006 => [CamtVersion::V11],
            self::CAMT007 => [CamtVersion::V10],
            self::CAMT008 => [CamtVersion::V11],
            self::CAMT009 => [CamtVersion::V08],
            self::CAMT010 => [CamtVersion::V09],
            self::CAMT011 => [CamtVersion::V08],
            self::CAMT012 => [CamtVersion::V08],
            self::CAMT013 => [CamtVersion::V04],
            self::CAMT014 => [CamtVersion::V05],
            self::CAMT015 => [CamtVersion::V04],
            self::CAMT016 => [CamtVersion::V04],
            self::CAMT017 => [CamtVersion::V05],
            self::CAMT018 => [CamtVersion::V05],
            self::CAMT019 => [CamtVersion::V07],
            self::CAMT020 => [CamtVersion::V04],
            self::CAMT021 => [CamtVersion::V06],
            self::CAMT023 => [CamtVersion::V07],
            self::CAMT024 => [CamtVersion::V08],
            self::CAMT025 => [CamtVersion::V09],
            // Investigation & Claims (026-039)
            self::CAMT026 => [CamtVersion::V10],
            self::CAMT027 => [CamtVersion::V10],
            self::CAMT028 => [CamtVersion::V12],
            self::CAMT029 => [CamtVersion::V13],
            self::CAMT030 => [CamtVersion::V06],
            self::CAMT031 => [CamtVersion::V07],
            self::CAMT032 => [CamtVersion::V05],
            self::CAMT033 => [CamtVersion::V07],
            self::CAMT034 => [CamtVersion::V07],
            self::CAMT035 => [CamtVersion::V06],
            self::CAMT036 => [CamtVersion::V06],
            self::CAMT037 => [CamtVersion::V10],
            self::CAMT038 => [CamtVersion::V05],
            self::CAMT039 => [CamtVersion::V06],
            // Fund Processing (040-051)
            self::CAMT040 => [CamtVersion::V04],
            self::CAMT041 => [CamtVersion::V04],
            self::CAMT042 => [CamtVersion::V04],
            self::CAMT043 => [CamtVersion::V04],
            self::CAMT044 => [CamtVersion::V03],
            self::CAMT045 => [CamtVersion::V03],
            self::CAMT046 => [CamtVersion::V08],
            self::CAMT047 => [CamtVersion::V08],
            self::CAMT048 => [CamtVersion::V07],
            self::CAMT049 => [CamtVersion::V07],
            self::CAMT050 => [CamtVersion::V07],
            self::CAMT051 => [CamtVersion::V07],
            // Bank to Customer Statements (052-054)
            self::CAMT052 => [CamtVersion::V02, CamtVersion::V06, CamtVersion::V08, CamtVersion::V10, CamtVersion::V12, CamtVersion::V13],
            self::CAMT053 => [CamtVersion::V02, CamtVersion::V04, CamtVersion::V08, CamtVersion::V10, CamtVersion::V12, CamtVersion::V13],
            self::CAMT054 => [CamtVersion::V02, CamtVersion::V08, CamtVersion::V13],
            // Payment Cancellation (055-056)
            self::CAMT055 => [CamtVersion::V12],
            self::CAMT056 => [CamtVersion::V11],
            // Notifications (057-059)
            self::CAMT057 => [CamtVersion::V08],
            self::CAMT058 => [CamtVersion::V09],
            self::CAMT059 => [CamtVersion::V08],
            // Account Reporting & Billing (060-088)
            self::CAMT060 => [CamtVersion::V07],
            self::CAMT061 => [CamtVersion::V02],
            self::CAMT062 => [CamtVersion::V03],
            self::CAMT063 => [CamtVersion::V02],
            self::CAMT064 => [CamtVersion::V01],
            self::CAMT065 => [CamtVersion::V01],
            self::CAMT066 => [CamtVersion::V02],
            self::CAMT067 => [CamtVersion::V02],
            self::CAMT068 => [CamtVersion::V02],
            self::CAMT069 => [CamtVersion::V05],
            self::CAMT070 => [CamtVersion::V06],
            self::CAMT071 => [CamtVersion::V05],
            self::CAMT072 => [CamtVersion::V02],
            self::CAMT073 => [CamtVersion::V02],
            self::CAMT074 => [CamtVersion::V02],
            self::CAMT075 => [CamtVersion::V02],
            self::CAMT076 => [CamtVersion::V01],
            self::CAMT077 => [CamtVersion::V01],
            self::CAMT078 => [CamtVersion::V02],
            self::CAMT079 => [CamtVersion::V02],
            self::CAMT080 => [CamtVersion::V02],
            self::CAMT081 => [CamtVersion::V02],
            self::CAMT082 => [CamtVersion::V02],
            self::CAMT083 => [CamtVersion::V02],
            self::CAMT084 => [CamtVersion::V02],
            self::CAMT085 => [CamtVersion::V02],
            self::CAMT086 => [CamtVersion::V05],
            self::CAMT087 => [CamtVersion::V09],
            self::CAMT088 => [CamtVersion::V03],
            // Standing Orders & Charges (101-109)
            self::CAMT101 => [CamtVersion::V02],
            self::CAMT102 => [CamtVersion::V03],
            self::CAMT103 => [CamtVersion::V03],
            self::CAMT104 => [CamtVersion::V01],
            self::CAMT105 => [CamtVersion::V03],
            self::CAMT106 => [CamtVersion::V03],
            self::CAMT107 => [CamtVersion::V02],
            self::CAMT108 => [CamtVersion::V02],
            self::CAMT109 => [CamtVersion::V02],
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