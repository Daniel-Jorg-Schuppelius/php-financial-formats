<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : StatusReasonCode.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 * 
 * Based on: ISO 20022 ExternalStatusReason1Code
 * @see https://www.iso20022.org/external_code_list.page
 * @see DFÜ-Abkommen Anlage 3 - Kapitel 2.2.4 (SEPA Payment Status Report)
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Pain;

/**
 * StatusReason - ISO 20022 External Status Reason Code
 * 
 * Used in pain.002 Payment Status Report for SEPA transactions.
 * Contains reason codes for transaction status (ACCP, ACWC, RJCT, etc.)
 * 
 * @see ExternalStatusReason1Code
 */
enum StatusReasonCode: string {
    /**
     * AB05 - Timeout Creditor Agent
     * Transaction stopped due to timeout at the Creditor Agent.
     */
    case AB05 = 'AB05';

    /**
     * AB06 - Timeout Instructed Agent
     * Transaction stopped due to timeout at the Instructed Agent.
     */
    case AB06 = 'AB06';

    /**
     * AB07 - Agent Offline
     * Agent of message is not online.
     */
    case AB07 = 'AB07';

    /**
     * AB08 - Error Creditor Agent
     * Creditor Agent is not able to process the transaction.
     */
    case AB08 = 'AB08';

    /**
     * AB09 - Error Instructed Agent
     * Instructed Agent is not able to process the transaction.
     */
    case AB09 = 'AB09';

    /**
     * AB10 - Timeout Debtor Agent
     * Transaction stopped due to timeout at the Debtor Agent.
     */
    case AB10 = 'AB10';

    /**
     * AB11 - Timeout by Debtor Agent
     * Debtor Agent timed out waiting for response from Debtor.
     */
    case AB11 = 'AB11';

    /**
     * AC01 - Incorrect Account Number
     * Format of the account number specified is not correct.
     */
    case AC01 = 'AC01';

    /**
     * AC02 - Invalid Debtor Account Number
     * Debtor account number invalid or missing.
     */
    case AC02 = 'AC02';

    /**
     * AC03 - Invalid Creditor Account Number
     * Wrong IBAN in SCT.
     */
    case AC03 = 'AC03';

    /**
     * AC04 - Closed Account Number
     * Account number specified has been closed on the bank of account's books.
     */
    case AC04 = 'AC04';

    /**
     * AC06 - Blocked Account
     * Account specified is blocked, prohibiting posting of transactions against it.
     */
    case AC06 = 'AC06';

    /**
     * AC13 - Invalid Debtor Account Type
     * Debtor account type is missing or invalid.
     */
    case AC13 = 'AC13';

    /**
     * AG01 - Transaction Forbidden
     * Transaction forbidden on this type of account.
     */
    case AG01 = 'AG01';

    /**
     * AG02 - Invalid Bank Operation Code
     * Bank operation code specified in the message is not valid for receiver.
     */
    case AG02 = 'AG02';

    /**
     * AG03 - Transaction Not Supported
     * Transaction type not supported/authorized on this account.
     */
    case AG03 = 'AG03';

    /**
     * AG10 - Agent Suspended
     * Agent in the payment workflow is suspended.
     */
    case AG10 = 'AG10';

    /**
     * AG11 - Creditor Agent Suspended
     * Creditor Agent is suspended from the payment system.
     */
    case AG11 = 'AG11';

    /**
     * AM01 - Zero Amount
     * Specified message amount is equal to zero.
     */
    case AM01 = 'AM01';

    /**
     * AM02 - Not Allowed Amount
     * Specific transaction/message amount is greater than allowed maximum.
     */
    case AM02 = 'AM02';

    /**
     * AM03 - Not Allowed Currency
     * Specified message amount is a non-processable currency.
     */
    case AM03 = 'AM03';

    /**
     * AM04 - Insufficient Funds
     * Amount of funds available to cover specified message amount is insufficient.
     */
    case AM04 = 'AM04';

    /**
     * AM05 - Duplication
     * Duplication.
     */
    case AM05 = 'AM05';

    /**
     * AM06 - Too Low Amount
     * Specified transaction amount is less than agreed minimum.
     */
    case AM06 = 'AM06';

    /**
     * AM07 - Blocked Amount
     * Amount is blocked.
     */
    case AM07 = 'AM07';

    /**
     * AM09 - Wrong Amount
     * Amount received is not the amount agreed or expected.
     */
    case AM09 = 'AM09';

    /**
     * AM10 - Invalid Control Sum
     * Sum of instructed amounts does not equal the control sum.
     */
    case AM10 = 'AM10';

    /**
     * AM18 - Number of Transactions Exceeded
     * Number of transactions exceeds the clearing system's limit.
     */
    case AM18 = 'AM18';

    /**
     * AM21 - Limit Exceeded
     * Transaction amount exceeds limits set by clearing system.
     */
    case AM21 = 'AM21';

    /**
     * AM23 - Amount Exceeds Clearing System Limit
     * Instructed amount exceeds the maximum for clearing system.
     */
    case AM23 = 'AM23';

    /**
     * BE01 - Inconsistent With End Customer
     * Identification of end customer is not consistent with associated account.
     */
    case BE01 = 'BE01';

    /**
     * BE04 - Missing Creditor Address
     * Specification of creditor's address is missing/not correct.
     */
    case BE04 = 'BE04';

    /**
     * BE05 - Unrecognised Initiating Party
     * Party who initiated the message is not recognised by the end customer.
     */
    case BE05 = 'BE05';

    /**
     * BE06 - Unknown End Customer
     * End customer specified is not known.
     */
    case BE06 = 'BE06';

    /**
     * BE07 - Missing Debtor Address
     * Debtor address is missing.
     */
    case BE07 = 'BE07';

    /**
     * BE16 - Invalid Debtor Identification Code
     * Debtor identification code is invalid.
     */
    case BE16 = 'BE16';

    /**
     * CNOR - Creditor Bank Not Registered
     * Creditor bank is not registered under this BIC in the clearing system.
     */
    case CNOR = 'CNOR';

    /**
     * CUST - Requested By Customer
     * Cancellation requested by the Debtor.
     */
    case CUST = 'CUST';

    /**
     * DNOR - Debtor Bank Not Registered
     * Debtor bank is not registered under this BIC in the clearing system.
     */
    case DNOR = 'DNOR';

    /**
     * DS01 - Elapsed Settlement Due Date
     * Settlement date has elapsed.
     */
    case DS01 = 'DS01';

    /**
     * DS02 - Order Cancelled
     * The related mandate has been cancelled.
     */
    case DS02 = 'DS02';

    /**
     * DS24 - Settlement Failed
     * Settlement of the transaction has failed.
     */
    case DS24 = 'DS24';

    /**
     * DT01 - Invalid Date
     * Invalid date (eg, wrong or missing settlement date).
     */
    case DT01 = 'DT01';

    /**
     * DT04 - Future Date Not Supported
     * Future date not supported.
     */
    case DT04 = 'DT04';

    /**
     * DT06 - Execution Date Changed
     * Execution date has been modified.
     */
    case DT06 = 'DT06';

    /**
     * DUPL - Duplicate Payment
     * Payment is a duplicate of another payment.
     */
    case DUPL = 'DUPL';

    /**
     * ED01 - Corresponding Original File Non-Existent
     * Corresponds to original file that does not exist.
     */
    case ED01 = 'ED01';

    /**
     * ED03 - Balance Info Requested
     * Balance information was requested.
     */
    case ED03 = 'ED03';

    /**
     * ED05 - Settlement Failed
     * Settlement of the transaction failed.
     */
    case ED05 = 'ED05';

    /**
     * EMVL - EMV Liability Shift
     * EMV liability shift.
     */
    case EMVL = 'EMVL';

    /**
     * ERIN - ERI Option Not Supported
     * Extended Remittance Information is not supported.
     */
    case ERIN = 'ERIN';

    /**
     * FF01 - Invalid File Format
     * Operation/transaction code incorrect, file format incomplete or invalid.
     */
    case FF01 = 'FF01';

    /**
     * FF05 - Invalid Local Instrument Code
     * Local Instrument code is incorrect.
     */
    case FF05 = 'FF05';

    /**
     * FOCR - Following Cancellation Request
     * Return is following a cancellation request.
     */
    case FOCR = 'FOCR';

    /**
     * FRAD - Fraudulent Origin
     * Fraudulent origin.
     */
    case FRAD = 'FRAD';

    /**
     * MD01 - No Mandate
     * No mandate.
     */
    case MD01 = 'MD01';

    /**
     * MD02 - Missing Mandatory Information In Mandate
     * Mandate related information data required by clearing system is missing.
     */
    case MD02 = 'MD02';

    /**
     * MD06 - Refund Request By End Customer
     * Refund request by end customer.
     */
    case MD06 = 'MD06';

    /**
     * MD07 - End Customer Deceased
     * End customer is deceased.
     */
    case MD07 = 'MD07';

    /**
     * MS02 - Reason Not Specified by Customer
     * Reason has not been specified by end customer.
     */
    case MS02 = 'MS02';

    /**
     * MS03 - Reason Not Specified by Agent
     * Reason has not been specified by agent.
     */
    case MS03 = 'MS03';

    /**
     * NARR - Narrative
     * Reason is provided as narrative information in the additional reason information.
     */
    case NARR = 'NARR';

    /**
     * NOAS - No Answer from Customer
     * No response from Beneficiary.
     */
    case NOAS = 'NOAS';

    /**
     * NOOR - No Original Transaction Received
     * Original SCT never received.
     */
    case NOOR = 'NOOR';

    /**
     * PINL - PIN Limit Exceeded
     * Number of PIN tries exceeded.
     */
    case PINL = 'PINL';

    /**
     * RC01 - Bank Identifier Incorrect
     * Bank identifier code specified in the message has an incorrect format.
     */
    case RC01 = 'RC01';

    /**
     * RF01 - Not Unique Transaction Reference
     * Transaction reference is not unique within the message.
     */
    case RF01 = 'RF01';

    /**
     * RR01 - Missing Debtor Account or Identification
     * Regulatory reason - missing debtor account or identification.
     */
    case RR01 = 'RR01';

    /**
     * RR02 - Missing Debtor Name or Address
     * Regulatory reason - missing debtor's name or address.
     */
    case RR02 = 'RR02';

    /**
     * RR03 - Missing Creditor Name or Address
     * Regulatory reason - missing creditor's name or address.
     */
    case RR03 = 'RR03';

    /**
     * RR04 - Regulatory Reason
     * Regulatory reason.
     */
    case RR04 = 'RR04';

    /**
     * RR10 - Originator Identification Missing
     * Regulatory reason - originator identification is missing.
     */
    case RR10 = 'RR10';

    /**
     * SL01 - Specific Service Offered by Debtor Agent
     * Specific service offered by the debtor's agent.
     */
    case SL01 = 'SL01';

    /**
     * SL02 - Specific Service Offered by Creditor Agent
     * Specific service offered by the creditor's agent.
     */
    case SL02 = 'SL02';

    /**
     * SL11 - Creditor Not on Whitelist
     * Creditor is not on the whitelist of the debtor.
     */
    case SL11 = 'SL11';

    /**
     * SL12 - Creditor on Blacklist
     * Creditor is on the blacklist of the debtor.
     */
    case SL12 = 'SL12';

    /**
     * SL13 - Maximum Number of Transactions Exceeded
     * The maximum number of allowed transactions has been exceeded.
     */
    case SL13 = 'SL13';

    /**
     * SL14 - Maximum Amount Exceeded
     * The maximum allowed amount has been exceeded.
     */
    case SL14 = 'SL14';

    /**
     * SVNR - Service Not Rendered
     * No service rendered.
     */
    case SVNR = 'SVNR';

    /**
     * TM01 - Cut Off Time
     * Associated message was received after the cut-off time.
     */
    case TM01 = 'TM01';

    /**
     * TECH - Technical Problem
     * Technical problem.
     */
    case TECH = 'TECH';

    /**
     * UPAY - Underpayment Due to Insufficient Funds
     * The debtor's account was unable to pay the full amount.
     */
    case UPAY = 'UPAY';

    /**
     * Try to create from string value, returns null if not found.
     */
    public static function tryFromString(string $value): ?self {
        return self::tryFrom($value);
    }

    /**
     * Get description for the reason code.
     */
    public function getDescription(): string {
        return match ($this) {
            self::AB05 => 'Timeout Creditor Agent',
            self::AB06 => 'Timeout Instructed Agent',
            self::AB07 => 'Agent Offline',
            self::AB08 => 'Error Creditor Agent',
            self::AB09 => 'Error Instructed Agent',
            self::AB10 => 'Timeout Debtor Agent',
            self::AB11 => 'Timeout by Debtor Agent',
            self::AC01 => 'Incorrect Account Number',
            self::AC02 => 'Invalid Debtor Account Number',
            self::AC03 => 'Invalid Creditor Account Number',
            self::AC04 => 'Closed Account Number',
            self::AC06 => 'Blocked Account',
            self::AC13 => 'Invalid Debtor Account Type',
            self::AG01 => 'Transaction Forbidden',
            self::AG02 => 'Invalid Bank Operation Code',
            self::AG03 => 'Transaction Not Supported',
            self::AG10 => 'Agent Suspended',
            self::AG11 => 'Creditor Agent Suspended',
            self::AM01 => 'Zero Amount',
            self::AM02 => 'Not Allowed Amount',
            self::AM03 => 'Not Allowed Currency',
            self::AM04 => 'Insufficient Funds',
            self::AM05 => 'Duplication',
            self::AM06 => 'Too Low Amount',
            self::AM07 => 'Blocked Amount',
            self::AM09 => 'Wrong Amount',
            self::AM10 => 'Invalid Control Sum',
            self::AM18 => 'Number of Transactions Exceeded',
            self::AM21 => 'Limit Exceeded',
            self::AM23 => 'Amount Exceeds Clearing System Limit',
            self::BE01 => 'Inconsistent With End Customer',
            self::BE04 => 'Missing Creditor Address',
            self::BE05 => 'Unrecognised Initiating Party',
            self::BE06 => 'Unknown End Customer',
            self::BE07 => 'Missing Debtor Address',
            self::BE16 => 'Invalid Debtor Identification Code',
            self::CNOR => 'Creditor Bank Not Registered',
            self::CUST => 'Requested By Customer',
            self::DNOR => 'Debtor Bank Not Registered',
            self::DS01 => 'Elapsed Settlement Due Date',
            self::DS02 => 'Order Cancelled',
            self::DS24 => 'Settlement Failed',
            self::DT01 => 'Invalid Date',
            self::DT04 => 'Future Date Not Supported',
            self::DT06 => 'Execution Date Changed',
            self::DUPL => 'Duplicate Payment',
            self::ED01 => 'Corresponding Original File Non-Existent',
            self::ED03 => 'Balance Info Requested',
            self::ED05 => 'Settlement Failed',
            self::EMVL => 'EMV Liability Shift',
            self::ERIN => 'ERI Option Not Supported',
            self::FF01 => 'Invalid File Format',
            self::FF05 => 'Invalid Local Instrument Code',
            self::FOCR => 'Following Cancellation Request',
            self::FRAD => 'Fraudulent Origin',
            self::MD01 => 'No Mandate',
            self::MD02 => 'Missing Mandatory Information In Mandate',
            self::MD06 => 'Refund Request By End Customer',
            self::MD07 => 'End Customer Deceased',
            self::MS02 => 'Reason Not Specified by Customer',
            self::MS03 => 'Reason Not Specified by Agent',
            self::NARR => 'Narrative',
            self::NOAS => 'No Answer from Customer',
            self::NOOR => 'No Original Transaction Received',
            self::PINL => 'PIN Limit Exceeded',
            self::RC01 => 'Bank Identifier Incorrect',
            self::RF01 => 'Not Unique Transaction Reference',
            self::RR01 => 'Missing Debtor Account or Identification',
            self::RR02 => 'Missing Debtor Name or Address',
            self::RR03 => 'Missing Creditor Name or Address',
            self::RR04 => 'Regulatory Reason',
            self::RR10 => 'Originator Identification Missing',
            self::SL01 => 'Specific Service Offered by Debtor Agent',
            self::SL02 => 'Specific Service Offered by Creditor Agent',
            self::SL11 => 'Creditor Not on Whitelist',
            self::SL12 => 'Creditor on Blacklist',
            self::SL13 => 'Maximum Number of Transactions Exceeded',
            self::SL14 => 'Maximum Amount Exceeded',
            self::SVNR => 'Service Not Rendered',
            self::TM01 => 'Cut Off Time',
            self::TECH => 'Technical Problem',
            self::UPAY => 'Underpayment Due to Insufficient Funds',
        };
    }

    /**
     * Check if this is a rejection reason (transaction was rejected).
     */
    public function isRejection(): bool {
        return in_array($this, [
            self::AC01,
            self::AC02,
            self::AC03,
            self::AC04,
            self::AC06,
            self::AC13,
            self::AG01,
            self::AG02,
            self::AG03,
            self::AG10,
            self::AG11,
            self::AM01,
            self::AM02,
            self::AM03,
            self::AM04,
            self::AM06,
            self::AM07,
            self::AM09,
            self::AM10,
            self::AM18,
            self::AM21,
            self::AM23,
            self::BE01,
            self::BE04,
            self::BE05,
            self::BE06,
            self::BE07,
            self::BE16,
            self::CNOR,
            self::DNOR,
            self::DS01,
            self::DS24,
            self::DT01,
            self::DT04,
            self::ED01,
            self::ED05,
            self::FF01,
            self::FF05,
            self::MD01,
            self::MD02,
            self::RC01,
            self::RF01,
            self::RR01,
            self::RR02,
            self::RR03,
            self::RR04,
            self::RR10,
            self::SL11,
            self::SL12,
            self::SL13,
            self::SL14,
            self::TM01,
            self::TECH,
        ], true);
    }

    /**
     * Check if this is a cancellation/return reason.
     */
    public function isCancellation(): bool {
        return in_array($this, [
            self::CUST,
            self::DS02,
            self::FOCR,
            self::FRAD,
            self::MD06,
            self::MD07,
            self::MS02,
            self::MS03,
        ], true);
    }

    /**
     * Check if this is a timeout-related reason.
     */
    public function isTimeout(): bool {
        return in_array($this, [
            self::AB05,
            self::AB06,
            self::AB07,
            self::AB08,
            self::AB09,
            self::AB10,
            self::AB11,
            self::TM01,
        ], true);
    }
}