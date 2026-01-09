<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MtType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;

/**
 * MT message types according to SWIFT specification.
 * 
 * SWIFT-Nachrichten verschiedener Kategorien:
 * - Kategorie 1: Customer Payments & Cheques
 * - Kategorie 2: Financial Institution Transfers
 * - Kategorie 5: Securities Markets
 * - Kategorie 7: Documentary Credits & Guarantees
 * - Kategorie 9: Cash Management & Customer Status
 * - Kategorie n: Common Group Messages (n9x)
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum MtType: string {
    // ========================================
    // Kategorie 1: Customer Payments & Cheques
    // ========================================

    /**
     * MT101 - Request for Transfer
     * Zahlungsauftrag des Kunden an die Bank
     */
    case MT101 = 'MT101';

    /**
     * MT102 - Multiple Customer Credit Transfer
     * Sammelüberweisung mit mehreren Einzelaufträgen
     */
    case MT102 = 'MT102';

    /**
     * MT103 - Single Customer Credit Transfer
     * Single customer credit transfer (SEPA/International)
     */
    case MT103 = 'MT103';

    /**
     * MT103STP - Single Customer Credit Transfer (STP)
     * Straight Through Processing variant
     */
    case MT103STP = 'MT103STP';

    /**
     * MT104 - Customer Direct Debit
     * Kundenlastschrift (Direct Debit)
     */
    case MT104 = 'MT104';

    /**
     * MT105 - EDIFACT Envelope
     * EDIFACT message envelope
     */
    case MT105 = 'MT105';

    /**
     * MT107 - General Direct Debit Message
     * General direct debit instruction
     */
    case MT107 = 'MT107';

    /**
     * MT110 - Advice of Cheque(s)
     * Scheckankündigung
     */
    case MT110 = 'MT110';

    /**
     * MT111 - Request for Stop Payment of a Cheque
     * Schecksperre
     */
    case MT111 = 'MT111';

    /**
     * MT112 - Status of a Request for Stop Payment of a Cheque
     * Status einer Schecksperre
     */
    case MT112 = 'MT112';

    /**
     * MT190 - Advice of Charges, Interest and Other Adjustments
     * Kosten- und Zinsmitteilung (Category 1)
     */
    case MT190 = 'MT190';

    /**
     * MT191 - Request for Payment of Charges, Interest and Other Expenses
     * Anforderung Kosten/Zinsen (Category 1)
     */
    case MT191 = 'MT191';

    /**
     * MT192 - Request for Cancellation
     * Stornierungsanforderung (Category 1)
     */
    case MT192 = 'MT192';

    /**
     * MT195 - Queries
     * Rückfrage (Category 1)
     */
    case MT195 = 'MT195';

    /**
     * MT196 - Answers
     * Antwort auf Rückfrage (Category 1)
     */
    case MT196 = 'MT196';

    /**
     * MT199 - Free Format Message
     * Freitextnachricht (Category 1)
     */
    case MT199 = 'MT199';

    // ========================================
    // Kategorie 2: Financial Institution Transfers
    // ========================================

    /**
     * MT200 - Financial Institution Transfer for its Own Account
     * Eigengeschäft der Bank
     */
    case MT200 = 'MT200';

    /**
     * MT201 - Multiple Financial Institution Transfer for its Own Account
     * Sammel-Eigengeschäft der Bank
     */
    case MT201 = 'MT201';

    /**
     * MT202 - General Financial Institution Transfer
     * General financial institution transfer
     */
    case MT202 = 'MT202';

    /**
     * MT202COV - Cover Payment
     * Cover payment for underlying customer credit transfer
     */
    case MT202COV = 'MT202COV';

    /**
     * MT203 - Multiple General Financial Institution Transfer
     * Sammelüberweisung zwischen Finanzinstituten
     */
    case MT203 = 'MT203';

    /**
     * MT204 - Financial Markets Direct Debit Message
     * Lastschrift zwischen Finanzinstituten
     */
    case MT204 = 'MT204';

    /**
     * MT205 - Financial Institution Transfer Execution
     * Ausführung einer FI-Überweisung
     */
    case MT205 = 'MT205';

    /**
     * MT205COV - Financial Institution Transfer Execution (Cover)
     * Cover-Ausführung einer FI-Überweisung
     */
    case MT205COV = 'MT205COV';

    /**
     * MT210 - Notice to Receive
     * Eingangsanzeige
     */
    case MT210 = 'MT210';

    /**
     * MT290 - Advice of Charges, Interest and Other Adjustments
     * Kosten- und Zinsmitteilung (Category 2)
     */
    case MT290 = 'MT290';

    /**
     * MT291 - Request for Payment of Charges, Interest and Other Expenses
     * Anforderung Kosten/Zinsen (Category 2)
     */
    case MT291 = 'MT291';

    /**
     * MT292 - Request for Cancellation
     * Stornierungsanforderung (Category 2)
     */
    case MT292 = 'MT292';

    /**
     * MT295 - Queries
     * Rückfrage (Category 2)
     */
    case MT295 = 'MT295';

    /**
     * MT296 - Answers
     * Antwort auf Rückfrage (Category 2)
     */
    case MT296 = 'MT296';

    /**
     * MT299 - Free Format Message
     * Freitextnachricht (Category 2)
     */
    case MT299 = 'MT299';

    // ========================================
    // Kategorie 5: Securities Markets
    // ========================================

    /**
     * MT502 - Order to Buy or Sell
     * Wertpapierkauf-/Verkaufsauftrag
     */
    case MT502 = 'MT502';

    /**
     * MT509 - Trade Status Message
     * Handelsstatus
     */
    case MT509 = 'MT509';

    /**
     * MT513 - Client Advice of Execution
     * Kundenbenachrichtigung Ausführung
     */
    case MT513 = 'MT513';

    /**
     * MT515 - Client Confirmation of Purchase or Sale
     * Kundenbestätigung Kauf/Verkauf
     */
    case MT515 = 'MT515';

    /**
     * MT518 - Market-Side Securities Trade Confirmation
     * Marktbestätigung Wertpapierhandel
     */
    case MT518 = 'MT518';

    /**
     * MT535 - Statement of Holdings
     * Depotauszug
     */
    case MT535 = 'MT535';

    /**
     * MT536 - Statement of Transactions
     * Depottransaktionsauszug
     */
    case MT536 = 'MT536';

    /**
     * MT537 - Statement of Pending Transactions
     * Auszug offener Transaktionen
     */
    case MT537 = 'MT537';

    /**
     * MT540 - Receive Free
     * Wertpapiereingang frei
     */
    case MT540 = 'MT540';

    /**
     * MT541 - Receive Against Payment
     * Wertpapiereingang gegen Zahlung
     */
    case MT541 = 'MT541';

    /**
     * MT542 - Deliver Free
     * Wertpapierausgang frei
     */
    case MT542 = 'MT542';

    /**
     * MT543 - Deliver Against Payment
     * Wertpapierausgang gegen Zahlung
     */
    case MT543 = 'MT543';

    /**
     * MT544 - Receive Free Confirmation
     * Bestätigung Wertpapiereingang frei
     */
    case MT544 = 'MT544';

    /**
     * MT545 - Receive Against Payment Confirmation
     * Bestätigung Wertpapiereingang gegen Zahlung
     */
    case MT545 = 'MT545';

    /**
     * MT546 - Deliver Free Confirmation
     * Bestätigung Wertpapierausgang frei
     */
    case MT546 = 'MT546';

    /**
     * MT547 - Deliver Against Payment Confirmation
     * Bestätigung Wertpapierausgang gegen Zahlung
     */
    case MT547 = 'MT547';

    /**
     * MT548 - Settlement Status and Processing Advice
     * Abwicklungsstatus
     */
    case MT548 = 'MT548';

    /**
     * MT564 - Corporate Action Notification
     * Kapitalmaßnahme Benachrichtigung
     */
    case MT564 = 'MT564';

    /**
     * MT565 - Corporate Action Instruction
     * Kapitalmaßnahme Weisung
     */
    case MT565 = 'MT565';

    /**
     * MT566 - Corporate Action Confirmation
     * Kapitalmaßnahme Bestätigung
     */
    case MT566 = 'MT566';

    /**
     * MT567 - Corporate Action Status and Processing Advice
     * Kapitalmaßnahme Status
     */
    case MT567 = 'MT567';

    /**
     * MT568 - Corporate Action Narrative
     * Kapitalmaßnahme Freitext
     */
    case MT568 = 'MT568';

    /**
     * MT569 - Triparty Collateral Status and Processing Advice
     * Triparty-Sicherheitenstatus
     */
    case MT569 = 'MT569';

    /**
     * MT574 - Irrevocable Payment Confirmation
     * Unwiderrufliche Zahlungsbestätigung
     */
    case MT574 = 'MT574';

    /**
     * MT575 - Report of Combined Activity
     * Bericht kombinierte Aktivität
     */
    case MT575 = 'MT575';

    /**
     * MT576 - Statement of Open Orders
     * Auszug offener Orders
     */
    case MT576 = 'MT576';

    /**
     * MT578 - Settlement Allegement
     * Abwicklungsvorwurf
     */
    case MT578 = 'MT578';

    /**
     * MT586 - Statement of Settlement Allegements
     * Auszug Abwicklungsvorwürfe
     */
    case MT586 = 'MT586';

    /**
     * MT592 - Request for Cancellation
     * Stornierungsanforderung (Category 5)
     */
    case MT592 = 'MT592';

    /**
     * MT595 - Queries
     * Rückfrage (Category 5)
     */
    case MT595 = 'MT595';

    /**
     * MT596 - Answers
     * Antwort auf Rückfrage (Category 5)
     */
    case MT596 = 'MT596';

    /**
     * MT599 - Free Format Message
     * Freitextnachricht (Category 5)
     */
    case MT599 = 'MT599';

    // ========================================
    // Kategorie 7: Documentary Credits & Guarantees
    // ========================================

    /**
     * MT700 - Issue of a Documentary Credit
     * Akkreditiveröffnung
     */
    case MT700 = 'MT700';

    /**
     * MT701 - Issue of a Documentary Credit (Continuation)
     * Akkreditiveröffnung (Fortsetzung)
     */
    case MT701 = 'MT701';

    /**
     * MT705 - Pre-Advice of a Documentary Credit
     * Voranzeige Akkreditiv
     */
    case MT705 = 'MT705';

    /**
     * MT707 - Amendment to a Documentary Credit
     * Akkreditivänderung
     */
    case MT707 = 'MT707';

    /**
     * MT710 - Advice of a Third Bank's or Non-Bank's Documentary Credit
     * Avisierung Drittbank-Akkreditiv
     */
    case MT710 = 'MT710';

    /**
     * MT711 - Advice of a Third Bank's Documentary Credit (Continuation)
     * Avisierung Drittbank-Akkreditiv (Fortsetzung)
     */
    case MT711 = 'MT711';

    /**
     * MT720 - Transfer of a Documentary Credit
     * Akkreditivübertragung
     */
    case MT720 = 'MT720';

    /**
     * MT721 - Transfer of a Documentary Credit (Continuation)
     * Akkreditivübertragung (Fortsetzung)
     */
    case MT721 = 'MT721';

    /**
     * MT730 - Acknowledgement
     * Akkreditivbestätigung
     */
    case MT730 = 'MT730';

    /**
     * MT732 - Advice of Discharge
     * Entlastungsanzeige
     */
    case MT732 = 'MT732';

    /**
     * MT734 - Advice of Refusal
     * Ablehnungsanzeige
     */
    case MT734 = 'MT734';

    /**
     * MT740 - Authorisation to Reimburse
     * Remboursermächtigung
     */
    case MT740 = 'MT740';

    /**
     * MT742 - Reimbursement Claim
     * Remboursanspruch
     */
    case MT742 = 'MT742';

    /**
     * MT747 - Amendment to an Authorisation to Reimburse
     * Änderung Remboursermächtigung
     */
    case MT747 = 'MT747';

    /**
     * MT750 - Advice of Discrepancy
     * Unstimmigkeitsanzeige
     */
    case MT750 = 'MT750';

    /**
     * MT752 - Authorisation to Pay, Accept or Negotiate
     * Zahlungs-/Akzept-/Negoziierungsermächtigung
     */
    case MT752 = 'MT752';

    /**
     * MT754 - Advice of Payment/Acceptance/Negotiation
     * Zahlungs-/Akzept-/Negoziierungsanzeige
     */
    case MT754 = 'MT754';

    /**
     * MT756 - Advice of Reimbursement or Payment
     * Rembours-/Zahlungsanzeige
     */
    case MT756 = 'MT756';

    /**
     * MT760 - Guarantee/Standby Letter of Credit
     * Garantie/Standby-Akkreditiv
     */
    case MT760 = 'MT760';

    /**
     * MT765 - Guarantee/Standby Letter of Credit Amendment
     * Garantie-/Standby-Akkreditiv-Änderung
     */
    case MT765 = 'MT765';

    /**
     * MT767 - Guarantee/Standby Letter of Credit Amendment
     * Garantie-/Standby-Akkreditiv-Änderung
     */
    case MT767 = 'MT767';

    /**
     * MT768 - Acknowledgement of a Guarantee/Standby Letter of Credit
     * Bestätigung Garantie/Standby-Akkreditiv
     */
    case MT768 = 'MT768';

    /**
     * MT769 - Advice of Reduction or Release
     * Anzeige Reduzierung/Freigabe
     */
    case MT769 = 'MT769';

    /**
     * MT790 - Advice of Charges, Interest and Other Adjustments
     * Kosten- und Zinsmitteilung (Category 7)
     */
    case MT790 = 'MT790';

    /**
     * MT791 - Request for Payment of Charges, Interest and Other Expenses
     * Anforderung Kosten/Zinsen (Category 7)
     */
    case MT791 = 'MT791';

    /**
     * MT792 - Request for Cancellation
     * Stornierungsanforderung (Category 7)
     */
    case MT792 = 'MT792';

    /**
     * MT795 - Queries
     * Rückfrage (Category 7)
     */
    case MT795 = 'MT795';

    /**
     * MT796 - Answers
     * Antwort auf Rückfrage (Category 7)
     */
    case MT796 = 'MT796';

    /**
     * MT799 - Free Format Message
     * Freitextnachricht (Category 7)
     */
    case MT799 = 'MT799';

    // ========================================
    // Kategorie 9: Cash Management & Customer Status
    // ========================================

    /**
     * MT900 - Confirmation of Debit
     * Debit confirmation (Debit Advice)
     */
    case MT900 = 'MT900';

    /**
     * MT910 - Confirmation of Credit
     * Credit confirmation (Credit Advice)
     */
    case MT910 = 'MT910';

    /**
     * MT920 - Customer Statement Message Request
     * Anforderung von Umsatz- und Saldeninformationen
     */
    case MT920 = 'MT920';

    /**
     * MT940 - Customer Statement Message
     * Tagesendeauszug (End of Day Statement)
     * Equivalent to CAMT.053
     */
    case MT940 = 'MT940';

    /**
     * MT941 - Balance Report
     * Saldeninformation ohne Umsatzdetails
     */
    case MT941 = 'MT941';

    /**
     * MT942 - Interim Transaction Report
     * Intraday transaction information (Intraday)
     * Equivalent to CAMT.052
     */
    case MT942 = 'MT942';

    /**
     * MT950 - Statement Message
     * Statement message (international format, used by FI)
     */
    case MT950 = 'MT950';

    /**
     * MT970 - Netting Statement
     * Netting-Auszug
     */
    case MT970 = 'MT970';

    /**
     * MT971 - Netting Balance Report
     * Netting-Saldobericht
     */
    case MT971 = 'MT971';

    /**
     * MT972 - Netting Interim Statement
     * Netting-Zwischenauszug
     */
    case MT972 = 'MT972';

    /**
     * MT973 - Netting Request Message
     * Netting-Anforderung
     */
    case MT973 = 'MT973';

    /**
     * MT985 - Status Inquiry
     * Statusanfrage
     */
    case MT985 = 'MT985';

    /**
     * MT986 - Status Report
     * Statusbericht
     */
    case MT986 = 'MT986';

    /**
     * MT990 - Advice of Charges, Interest and Other Adjustments
     * Kosten- und Zinsmitteilung (Category 9)
     */
    case MT990 = 'MT990';

    /**
     * MT991 - Request for Payment of Charges, Interest and Other Expenses
     * Anforderung Kosten/Zinsen (Category 9)
     */
    case MT991 = 'MT991';

    /**
     * MT992 - Request for Cancellation
     * Stornierungsanforderung (Category 9)
     */
    case MT992 = 'MT992';

    /**
     * MT995 - Queries
     * Rückfrage (Category 9)
     */
    case MT995 = 'MT995';

    /**
     * MT996 - Answers
     * Antwort auf Rückfrage (Category 9)
     */
    case MT996 = 'MT996';

    /**
     * MT999 - Free Format Message
     * Freitextnachricht (Category 9)
     */
    case MT999 = 'MT999';

    /**
     * Returns the German description text.
     */
    public function getDescription(): string {
        return match ($this) {
            // Category 1: Customer Payments & Cheques
            self::MT101 => 'Zahlungsauftrag (Request for Transfer)',
            self::MT102 => 'Sammelüberweisung (Multiple Credit Transfer)',
            self::MT103 => 'Einzelüberweisung (Single Credit Transfer)',
            self::MT103STP => 'Einzelüberweisung STP (Single Credit Transfer STP)',
            self::MT104 => 'Kundenlastschrift (Direct Debit)',
            self::MT105 => 'EDIFACT-Umschlag (EDIFACT Envelope)',
            self::MT107 => 'Sammellastschrift (General Direct Debit)',
            self::MT110 => 'Scheckankündigung (Advice of Cheque)',
            self::MT111 => 'Schecksperre (Stop Payment Request)',
            self::MT112 => 'Status Schecksperre (Stop Payment Status)',
            self::MT190 => 'Kosten-/Zinsmitteilung (Charges/Interest Advice)',
            self::MT191 => 'Kostenanforderung (Charges Request)',
            self::MT192 => 'Stornierungsanforderung (Cancellation Request)',
            self::MT195 => 'Rückfrage (Queries)',
            self::MT196 => 'Antwort (Answers)',
            self::MT199 => 'Freitext (Free Format)',
            // Category 2: Financial Institution Transfers
            self::MT200 => 'Eigengeschäft (FI Transfer Own Account)',
            self::MT201 => 'Sammel-Eigengeschäft (Multiple FI Own Account)',
            self::MT202 => 'Institutsüberweisung (FI Transfer)',
            self::MT202COV => 'Cover-Zahlung (Cover Payment)',
            self::MT203 => 'Sammel-Institutsüberweisung (Multiple FI Transfer)',
            self::MT204 => 'Institutslastschrift (FI Direct Debit)',
            self::MT205 => 'FI-Überweisungsausführung (FI Transfer Execution)',
            self::MT205COV => 'FI-Cover-Ausführung (FI Transfer Execution Cover)',
            self::MT210 => 'Eingangsanzeige (Notice to Receive)',
            self::MT290 => 'Kosten-/Zinsmitteilung (Charges/Interest Advice)',
            self::MT291 => 'Kostenanforderung (Charges Request)',
            self::MT292 => 'Stornierungsanforderung (Cancellation Request)',
            self::MT295 => 'Rückfrage (Queries)',
            self::MT296 => 'Antwort (Answers)',
            self::MT299 => 'Freitext (Free Format)',
            // Category 5: Securities Markets
            self::MT502 => 'Wertpapierauftrag (Order to Buy or Sell)',
            self::MT509 => 'Handelsstatus (Trade Status)',
            self::MT513 => 'Ausführungsanzeige (Advice of Execution)',
            self::MT515 => 'Handelsbestätigung (Trade Confirmation)',
            self::MT518 => 'Marktbestätigung (Market Confirmation)',
            self::MT535 => 'Depotauszug (Statement of Holdings)',
            self::MT536 => 'Depotumsatzauszug (Statement of Transactions)',
            self::MT537 => 'Offene Transaktionen (Pending Transactions)',
            self::MT540 => 'Wertpapiereingang frei (Receive Free)',
            self::MT541 => 'Wertpapiereingang DVP (Receive Against Payment)',
            self::MT542 => 'Wertpapierausgang frei (Deliver Free)',
            self::MT543 => 'Wertpapierausgang DVP (Deliver Against Payment)',
            self::MT544 => 'Bestätigung Eingang frei (Receive Free Confirmation)',
            self::MT545 => 'Bestätigung Eingang DVP (Receive Against Payment Confirmation)',
            self::MT546 => 'Bestätigung Ausgang frei (Deliver Free Confirmation)',
            self::MT547 => 'Bestätigung Ausgang DVP (Deliver Against Payment Confirmation)',
            self::MT548 => 'Abwicklungsstatus (Settlement Status)',
            self::MT564 => 'Kapitalmaßnahme (Corporate Action Notification)',
            self::MT565 => 'Kapitalmaßnahme-Weisung (Corporate Action Instruction)',
            self::MT566 => 'Kapitalmaßnahme-Bestätigung (Corporate Action Confirmation)',
            self::MT567 => 'Kapitalmaßnahme-Status (Corporate Action Status)',
            self::MT568 => 'Kapitalmaßnahme-Freitext (Corporate Action Narrative)',
            self::MT569 => 'Triparty-Status (Triparty Collateral Status)',
            self::MT574 => 'Unwiderrufliche Zahlungsbestätigung (Irrevocable Payment)',
            self::MT575 => 'Kombinierter Aktivitätsbericht (Combined Activity Report)',
            self::MT576 => 'Offene Orders (Statement of Open Orders)',
            self::MT578 => 'Abwicklungsvorwurf (Settlement Allegement)',
            self::MT586 => 'Abwicklungsvorwürfe-Auszug (Statement of Allegements)',
            self::MT592 => 'Stornierungsanforderung (Cancellation Request)',
            self::MT595 => 'Rückfrage (Queries)',
            self::MT596 => 'Antwort (Answers)',
            self::MT599 => 'Freitext (Free Format)',
            // Category 7: Documentary Credits & Guarantees
            self::MT700 => 'Akkreditiveröffnung (Issue of Documentary Credit)',
            self::MT701 => 'Akkreditiv-Fortsetzung (Documentary Credit Continuation)',
            self::MT705 => 'Akkreditiv-Voranzeige (Pre-Advice of Documentary Credit)',
            self::MT707 => 'Akkreditivänderung (Documentary Credit Amendment)',
            self::MT710 => 'Drittbank-Akkreditiv (Third Bank Documentary Credit)',
            self::MT711 => 'Drittbank-Akkreditiv-Fortsetzung (Third Bank DC Continuation)',
            self::MT720 => 'Akkreditivübertragung (Transfer of Documentary Credit)',
            self::MT721 => 'Akkreditivübertragung-Fortsetzung (Transfer DC Continuation)',
            self::MT730 => 'Akkreditivbestätigung (Acknowledgement)',
            self::MT732 => 'Entlastungsanzeige (Advice of Discharge)',
            self::MT734 => 'Ablehnungsanzeige (Advice of Refusal)',
            self::MT740 => 'Remboursermächtigung (Authorisation to Reimburse)',
            self::MT742 => 'Remboursanspruch (Reimbursement Claim)',
            self::MT747 => 'Rembours-Änderung (Amendment to Authorisation)',
            self::MT750 => 'Unstimmigkeitsanzeige (Advice of Discrepancy)',
            self::MT752 => 'Zahlungsermächtigung (Authorisation to Pay/Accept)',
            self::MT754 => 'Zahlungsanzeige (Advice of Payment/Acceptance)',
            self::MT756 => 'Rembours-/Zahlungsanzeige (Advice of Reimbursement)',
            self::MT760 => 'Garantie/Standby-Akkreditiv (Guarantee/SBLC)',
            self::MT765 => 'Garantie-Änderung (Guarantee Amendment)',
            self::MT767 => 'Garantie-Änderung (Guarantee Amendment)',
            self::MT768 => 'Garantie-Bestätigung (Guarantee Acknowledgement)',
            self::MT769 => 'Reduzierung/Freigabe (Reduction or Release)',
            self::MT790 => 'Kosten-/Zinsmitteilung (Charges/Interest Advice)',
            self::MT791 => 'Kostenanforderung (Charges Request)',
            self::MT792 => 'Stornierungsanforderung (Cancellation Request)',
            self::MT795 => 'Rückfrage (Queries)',
            self::MT796 => 'Antwort (Answers)',
            self::MT799 => 'Freitext (Free Format)',
            // Category 9: Cash Management & Customer Status
            self::MT900 => 'Belastungsbestätigung (Confirmation of Debit)',
            self::MT910 => 'Gutschriftsbestätigung (Confirmation of Credit)',
            self::MT920 => 'Anforderung Umsatz-/Saldeninformationen',
            self::MT940 => 'Tagesendeauszug (Customer Statement)',
            self::MT941 => 'Saldeninformation (Balance Report)',
            self::MT942 => 'Untertägige Umsatzinformation (Interim Report)',
            self::MT950 => 'Kontoauszug (Statement Message)',
            self::MT970 => 'Netting-Auszug (Netting Statement)',
            self::MT971 => 'Netting-Saldobericht (Netting Balance Report)',
            self::MT972 => 'Netting-Zwischenauszug (Netting Interim Statement)',
            self::MT973 => 'Netting-Anforderung (Netting Request)',
            self::MT985 => 'Statusanfrage (Status Inquiry)',
            self::MT986 => 'Statusbericht (Status Report)',
            self::MT990 => 'Kosten-/Zinsmitteilung (Charges/Interest Advice)',
            self::MT991 => 'Kostenanforderung (Charges Request)',
            self::MT992 => 'Stornierungsanforderung (Cancellation Request)',
            self::MT995 => 'Rückfrage (Queries)',
            self::MT996 => 'Antwort (Answers)',
            self::MT999 => 'Freitext (Free Format)',
        };
    }

    /**
     * Returns the SWIFT message name.
     */
    public function getMessageName(): string {
        return match ($this) {
            // Category 1: Customer Payments & Cheques
            self::MT101 => 'Request for Transfer',
            self::MT102 => 'Multiple Customer Credit Transfer',
            self::MT103 => 'Single Customer Credit Transfer',
            self::MT103STP => 'Single Customer Credit Transfer (STP)',
            self::MT104 => 'Customer Direct Debit',
            self::MT105 => 'EDIFACT Envelope',
            self::MT107 => 'General Direct Debit Message',
            self::MT110 => 'Advice of Cheque(s)',
            self::MT111 => 'Request for Stop Payment of a Cheque',
            self::MT112 => 'Status of a Request for Stop Payment',
            self::MT190 => 'Advice of Charges, Interest and Other Adjustments',
            self::MT191 => 'Request for Payment of Charges',
            self::MT192 => 'Request for Cancellation',
            self::MT195 => 'Queries',
            self::MT196 => 'Answers',
            self::MT199 => 'Free Format Message',
            // Category 2: Financial Institution Transfers
            self::MT200 => 'Financial Institution Transfer for its Own Account',
            self::MT201 => 'Multiple Financial Institution Transfer for its Own Account',
            self::MT202 => 'General Financial Institution Transfer',
            self::MT202COV => 'Cover Payment',
            self::MT203 => 'Multiple General Financial Institution Transfer',
            self::MT204 => 'Financial Markets Direct Debit Message',
            self::MT205 => 'Financial Institution Transfer Execution',
            self::MT205COV => 'Financial Institution Transfer Execution (Cover)',
            self::MT210 => 'Notice to Receive',
            self::MT290 => 'Advice of Charges, Interest and Other Adjustments',
            self::MT291 => 'Request for Payment of Charges',
            self::MT292 => 'Request for Cancellation',
            self::MT295 => 'Queries',
            self::MT296 => 'Answers',
            self::MT299 => 'Free Format Message',
            // Category 5: Securities Markets
            self::MT502 => 'Order to Buy or Sell',
            self::MT509 => 'Trade Status Message',
            self::MT513 => 'Client Advice of Execution',
            self::MT515 => 'Client Confirmation of Purchase or Sale',
            self::MT518 => 'Market-Side Securities Trade Confirmation',
            self::MT535 => 'Statement of Holdings',
            self::MT536 => 'Statement of Transactions',
            self::MT537 => 'Statement of Pending Transactions',
            self::MT540 => 'Receive Free',
            self::MT541 => 'Receive Against Payment',
            self::MT542 => 'Deliver Free',
            self::MT543 => 'Deliver Against Payment',
            self::MT544 => 'Receive Free Confirmation',
            self::MT545 => 'Receive Against Payment Confirmation',
            self::MT546 => 'Deliver Free Confirmation',
            self::MT547 => 'Deliver Against Payment Confirmation',
            self::MT548 => 'Settlement Status and Processing Advice',
            self::MT564 => 'Corporate Action Notification',
            self::MT565 => 'Corporate Action Instruction',
            self::MT566 => 'Corporate Action Confirmation',
            self::MT567 => 'Corporate Action Status and Processing Advice',
            self::MT568 => 'Corporate Action Narrative',
            self::MT569 => 'Triparty Collateral Status and Processing Advice',
            self::MT574 => 'Irrevocable Payment Confirmation',
            self::MT575 => 'Report of Combined Activity',
            self::MT576 => 'Statement of Open Orders',
            self::MT578 => 'Settlement Allegement',
            self::MT586 => 'Statement of Settlement Allegements',
            self::MT592 => 'Request for Cancellation',
            self::MT595 => 'Queries',
            self::MT596 => 'Answers',
            self::MT599 => 'Free Format Message',
            // Category 7: Documentary Credits & Guarantees
            self::MT700 => 'Issue of a Documentary Credit',
            self::MT701 => 'Issue of a Documentary Credit (Continuation)',
            self::MT705 => 'Pre-Advice of a Documentary Credit',
            self::MT707 => 'Amendment to a Documentary Credit',
            self::MT710 => 'Advice of a Third Bank\'s Documentary Credit',
            self::MT711 => 'Advice of a Third Bank\'s Documentary Credit (Continuation)',
            self::MT720 => 'Transfer of a Documentary Credit',
            self::MT721 => 'Transfer of a Documentary Credit (Continuation)',
            self::MT730 => 'Acknowledgement',
            self::MT732 => 'Advice of Discharge',
            self::MT734 => 'Advice of Refusal',
            self::MT740 => 'Authorisation to Reimburse',
            self::MT742 => 'Reimbursement Claim',
            self::MT747 => 'Amendment to an Authorisation to Reimburse',
            self::MT750 => 'Advice of Discrepancy',
            self::MT752 => 'Authorisation to Pay, Accept or Negotiate',
            self::MT754 => 'Advice of Payment/Acceptance/Negotiation',
            self::MT756 => 'Advice of Reimbursement or Payment',
            self::MT760 => 'Guarantee/Standby Letter of Credit',
            self::MT765 => 'Guarantee/Standby Letter of Credit Amendment',
            self::MT767 => 'Guarantee/Standby Letter of Credit Amendment',
            self::MT768 => 'Acknowledgement of a Guarantee/Standby Letter of Credit',
            self::MT769 => 'Advice of Reduction or Release',
            self::MT790 => 'Advice of Charges, Interest and Other Adjustments',
            self::MT791 => 'Request for Payment of Charges',
            self::MT792 => 'Request for Cancellation',
            self::MT795 => 'Queries',
            self::MT796 => 'Answers',
            self::MT799 => 'Free Format Message',
            // Category 9: Cash Management & Customer Status
            self::MT900 => 'Confirmation of Debit',
            self::MT910 => 'Confirmation of Credit',
            self::MT920 => 'Customer Statement Message Request',
            self::MT940 => 'Customer Statement Message',
            self::MT941 => 'Balance Report',
            self::MT942 => 'Interim Transaction Report',
            self::MT950 => 'Statement Message',
            self::MT970 => 'Netting Statement',
            self::MT971 => 'Netting Balance Report',
            self::MT972 => 'Netting Interim Statement',
            self::MT973 => 'Netting Request Message',
            self::MT985 => 'Status Inquiry',
            self::MT986 => 'Status Report',
            self::MT990 => 'Advice of Charges, Interest and Other Adjustments',
            self::MT991 => 'Request for Payment of Charges',
            self::MT992 => 'Request for Cancellation',
            self::MT995 => 'Queries',
            self::MT996 => 'Answers',
            self::MT999 => 'Free Format Message',
        };
    }

    /**
     * Returns the numeric message type.
     */
    public function getNumericType(): int {
        return match ($this) {
            // Category 1
            self::MT101 => 101,
            self::MT102 => 102,
            self::MT103, self::MT103STP => 103,
            self::MT104 => 104,
            self::MT105 => 105,
            self::MT107 => 107,
            self::MT110 => 110,
            self::MT111 => 111,
            self::MT112 => 112,
            self::MT190 => 190,
            self::MT191 => 191,
            self::MT192 => 192,
            self::MT195 => 195,
            self::MT196 => 196,
            self::MT199 => 199,
            // Category 2
            self::MT200 => 200,
            self::MT201 => 201,
            self::MT202, self::MT202COV => 202,
            self::MT203 => 203,
            self::MT204 => 204,
            self::MT205, self::MT205COV => 205,
            self::MT210 => 210,
            self::MT290 => 290,
            self::MT291 => 291,
            self::MT292 => 292,
            self::MT295 => 295,
            self::MT296 => 296,
            self::MT299 => 299,
            // Category 5
            self::MT502 => 502,
            self::MT509 => 509,
            self::MT513 => 513,
            self::MT515 => 515,
            self::MT518 => 518,
            self::MT535 => 535,
            self::MT536 => 536,
            self::MT537 => 537,
            self::MT540 => 540,
            self::MT541 => 541,
            self::MT542 => 542,
            self::MT543 => 543,
            self::MT544 => 544,
            self::MT545 => 545,
            self::MT546 => 546,
            self::MT547 => 547,
            self::MT548 => 548,
            self::MT564 => 564,
            self::MT565 => 565,
            self::MT566 => 566,
            self::MT567 => 567,
            self::MT568 => 568,
            self::MT569 => 569,
            self::MT574 => 574,
            self::MT575 => 575,
            self::MT576 => 576,
            self::MT578 => 578,
            self::MT586 => 586,
            self::MT592 => 592,
            self::MT595 => 595,
            self::MT596 => 596,
            self::MT599 => 599,
            // Category 7
            self::MT700 => 700,
            self::MT701 => 701,
            self::MT705 => 705,
            self::MT707 => 707,
            self::MT710 => 710,
            self::MT711 => 711,
            self::MT720 => 720,
            self::MT721 => 721,
            self::MT730 => 730,
            self::MT732 => 732,
            self::MT734 => 734,
            self::MT740 => 740,
            self::MT742 => 742,
            self::MT747 => 747,
            self::MT750 => 750,
            self::MT752 => 752,
            self::MT754 => 754,
            self::MT756 => 756,
            self::MT760 => 760,
            self::MT765 => 765,
            self::MT767 => 767,
            self::MT768 => 768,
            self::MT769 => 769,
            self::MT790 => 790,
            self::MT791 => 791,
            self::MT792 => 792,
            self::MT795 => 795,
            self::MT796 => 796,
            self::MT799 => 799,
            // Category 9
            self::MT900 => 900,
            self::MT910 => 910,
            self::MT920 => 920,
            self::MT940 => 940,
            self::MT941 => 941,
            self::MT942 => 942,
            self::MT950 => 950,
            self::MT970 => 970,
            self::MT971 => 971,
            self::MT972 => 972,
            self::MT973 => 973,
            self::MT985 => 985,
            self::MT986 => 986,
            self::MT990 => 990,
            self::MT991 => 991,
            self::MT992 => 992,
            self::MT995 => 995,
            self::MT996 => 996,
            self::MT999 => 999,
        };
    }

    /**
     * Returns the SWIFT category.
     */
    public function getCategory(): int {
        $numericType = $this->getNumericType();
        return (int) floor($numericType / 100);
    }

    /**
     * Returns the category description.
     */
    public function getCategoryDescription(): string {
        return match ($this->getCategory()) {
            1 => 'Customer Payments and Cheques',
            2 => 'Financial Institution Transfers',
            5 => 'Securities Markets',
            7 => 'Documentary Credits and Guarantees',
            9 => 'Cash Management and Customer Status',
            default => 'Unknown',
        };
    }

    /**
     * Checks if this type is a payment order.
     */
    public function isPaymentInitiation(): bool {
        return match ($this) {
            self::MT101, self::MT102, self::MT103, self::MT103STP, self::MT104, self::MT107 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is a confirmation.
     */
    public function isConfirmation(): bool {
        return match ($this) {
            self::MT900, self::MT910,
            self::MT544, self::MT545, self::MT546, self::MT547,
            self::MT566, self::MT574 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is a statement/report.
     */
    public function isStatement(): bool {
        return match ($this) {
            self::MT535, self::MT536, self::MT537, self::MT576, self::MT586,
            self::MT940, self::MT941, self::MT942, self::MT950,
            self::MT970, self::MT971, self::MT972,
            self::MT986 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is a common group message (n9x pattern).
     */
    public function isCommonMessage(): bool {
        $numericType = $this->getNumericType();
        $lastTwoDigits = $numericType % 100;
        return $lastTwoDigits >= 90 && $lastTwoDigits <= 99;
    }

    /**
     * Checks if this type is a cancellation request (n92).
     */
    public function isCancellationRequest(): bool {
        return match ($this) {
            self::MT192, self::MT292, self::MT592, self::MT792, self::MT992 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is a query message (n95).
     */
    public function isQuery(): bool {
        return match ($this) {
            self::MT195, self::MT295, self::MT595, self::MT795, self::MT995 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is an answer message (n96).
     */
    public function isAnswer(): bool {
        return match ($this) {
            self::MT196, self::MT296, self::MT596, self::MT796, self::MT996 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is a free format message (n99).
     */
    public function isFreeFormat(): bool {
        return match ($this) {
            self::MT199, self::MT299, self::MT599, self::MT799, self::MT999 => true,
            default => false,
        };
    }

    /**
     * Checks if this type is a securities message (MT5xx).
     */
    public function isSecuritiesMessage(): bool {
        return $this->getCategory() === 5;
    }

    /**
     * Checks if this type is a documentary credit message (MT7xx).
     */
    public function isDocumentaryCredit(): bool {
        return $this->getCategory() === 7;
    }

    /**
     * Checks if this type contains transactions.
     */
    public function hasTransactions(): bool {
        return match ($this) {
            // Category 1: Customer Payments
            self::MT101, self::MT102, self::MT103, self::MT103STP, self::MT104, self::MT107 => true,
            // Category 2: FI Transfers
            self::MT200, self::MT201, self::MT202, self::MT202COV, self::MT203, self::MT204,
            self::MT205, self::MT205COV => true,
            // Category 5: Securities
            self::MT502, self::MT536, self::MT540, self::MT541, self::MT542, self::MT543,
            self::MT544, self::MT545, self::MT546, self::MT547 => true,
            // Category 9: Cash Management
            self::MT940, self::MT942, self::MT950, self::MT970, self::MT972 => true,
            default => false,
        };
    }

    /**
     * Checks if this type contains balances.
     */
    public function hasBalances(): bool {
        return match ($this) {
            self::MT535,  // Holdings
            self::MT940, self::MT941, self::MT942, self::MT950,
            self::MT970, self::MT971, self::MT972 => true,
            default => false,
        };
    }

    /**
     * Returns the corresponding CAMT format (if available).
     */
    public function getCamtEquivalent(): ?CamtType {
        return match ($this) {
            self::MT900 => CamtType::CAMT054,  // Debit Notification
            self::MT910 => CamtType::CAMT054,  // Credit Notification
            self::MT940, self::MT941, self::MT950 => CamtType::CAMT053,  // End of Day
            self::MT942 => CamtType::CAMT052,  // Intraday
            default => null,
        };
    }

    /**
     * Erstellt einen MtType aus einem numerischen Wert.
     */
    public static function fromNumeric(int $type): ?self {
        foreach (self::cases() as $case) {
            if ($case->getNumericType() === $type) {
                return $case;
            }
        }
        return null;
    }

    /**
     * Ermittelt den MT-Typ aus einer SWIFT-Nachricht.
     */
    public static function fromSwiftMessage(string $content): ?self {
        // Prüfe auf Application Header Block {2:...} mit Nachrichtentyp
        if (preg_match('/\{2:[OI]\s*(\d{3})/', $content, $matches)) {
            return self::fromNumeric((int) $matches[1]);
        }

        // Fallback: Nach typischen Feldkombinationen suchen
        // MT101: Request for Transfer
        if (str_contains($content, ':28D:') && str_contains($content, ':50H:')) {
            return self::MT101;
        }
        // MT103: Single Customer Credit Transfer
        if (str_contains($content, ':23B:') && str_contains($content, ':32A:')) {
            return self::MT103;
        }
        // MT900/MT910: Confirmation messages mit :32A: aber ohne :23B:
        if (str_contains($content, ':32A:') && str_contains($content, ':21:') && !str_contains($content, ':23B:')) {
            return str_contains($content, ':25:') ? self::MT910 : self::MT900;
        }
        // MT940: End of Day Statement
        if (str_contains($content, ':60F:') && str_contains($content, ':62F:')) {
            return self::MT940;
        }
        // MT942: Interim Transaction Report
        if (str_contains($content, ':62M:') || str_contains($content, ':60M:')) {
            return self::MT942;
        }
        // MT941: Balance Report (nur Salden)
        if (str_contains($content, ':62F:') && !str_contains($content, ':61:')) {
            return self::MT941;
        }
        // MT950: Statement Message (FI to FI)
        if (str_contains($content, ':60F:') && str_contains($content, ':62F:') && str_contains($content, ':64:')) {
            return self::MT950;
        }
        // MT920: Statement Request
        if (str_contains($content, ':20:') && str_contains($content, ':12:')) {
            return self::MT920;
        }

        return null;
    }

    /**
     * Returns all statement types (MT94x, MT95x, MT97x).
     * 
     * @return self[]
     */
    public static function getStatementTypes(): array {
        return [
            self::MT940,
            self::MT941,
            self::MT942,
            self::MT950,
            self::MT970,
            self::MT971,
            self::MT972,
        ];
    }

    /**
     * Returns all customer payment types (MT1xx).
     * 
     * @return self[]
     */
    public static function getPaymentTypes(): array {
        return [
            self::MT101,
            self::MT102,
            self::MT103,
            self::MT103STP,
            self::MT104,
            self::MT105,
            self::MT107,
        ];
    }

    /**
     * Returns all financial institution transfer types (MT2xx).
     * 
     * @return self[]
     */
    public static function getFITransferTypes(): array {
        return [
            self::MT200,
            self::MT201,
            self::MT202,
            self::MT202COV,
            self::MT203,
            self::MT204,
            self::MT205,
            self::MT205COV,
            self::MT210,
        ];
    }

    /**
     * Returns all securities types (MT5xx).
     * 
     * @return self[]
     */
    public static function getSecuritiesTypes(): array {
        return array_filter(self::cases(), fn($case) => $case->getCategory() === 5);
    }

    /**
     * Returns all documentary credit types (MT7xx).
     * 
     * @return self[]
     */
    public static function getDocumentaryCreditTypes(): array {
        return array_filter(self::cases(), fn($case) => $case->getCategory() === 7);
    }

    /**
     * Returns all confirmation types (MT9xx without statements).
     * 
     * @return self[]
     */
    public static function getConfirmationTypes(): array {
        return [self::MT900, self::MT910];
    }

    /**
     * Returns all cheque-related types.
     * 
     * @return self[]
     */
    public static function getChequeTypes(): array {
        return [self::MT110, self::MT111, self::MT112];
    }
}