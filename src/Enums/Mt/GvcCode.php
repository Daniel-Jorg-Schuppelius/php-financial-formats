<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GvcCode.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionDomain;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionFamily;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionSubFamily;
use InvalidArgumentException;

/**
 * Geschäftsvorfall-Codes (GVC) for MT940 :86: field.
 * 
 * Complete implementation based on DFÜ-Abkommen Anlage 3 - Datenformate V3.4.
 * These codes identify the type of banking transaction.
 * 
 * Structure:
 * - 0XX/1XX: Zahlungsverkehr in Euro innerhalb der EU und des EWR
 * - 2XX: Auslandsgeschäft / Auslandszahlungsverkehr
 * - 3XX: Wertpapiergeschäft
 * - 4XX: Devisengeschäft
 * - 5XX: MAOBE (reserved)
 * - 6XX: Kreditgeschäft
 * - 7XX: Reserve
 * - 8XX: Sonstige
 * - 9XX: Unstrukturierte Belegung
 * 
 * @see https://www.ebics.de/de/datenformate
 */
enum GvcCode: string {
    // =========================================================================
    // 0XX: Zahlungsverkehr in Euro (Legacy/Misc)
    // =========================================================================
    case CREDIT_CARD_SETTLEMENT = '006';
    case BANK_TO_BANK_TRANSFER = '058';
    case BILL_OF_EXCHANGE_SUBMISSION = '072';
    case BILL_OF_EXCHANGE = '073';
    case TELEPHONE_ORDER = '076';
    case BATCH_TRANSFER = '079';
    case CASH_DEPOSIT = '082';
    case CASH_WITHDRAWAL = '083';
    case ONLINE_DIRECT_DEBIT = '084';
    case EXPRESS_TRANSFER = '087';
    case CREDIT_TRANSFER_FIXED_VALUE = '088';
    case DISCOUNT_BILL = '093';
    case GUARANTEE_DOMESTIC = '095';
    case GELDKARTE_MERCHANT = '098';

        // =========================================================================
        // 1XX: SEPA-Zahlungsverkehr
        // =========================================================================
    case CHECK_BEARER = '101';
    case CHECK_ORDER = '102';
    case CHECK_TRAVEL = '103';
    case SEPA_DD_SINGLE_B2B = '104';
    case SEPA_DD_SINGLE_CORE = '105';
    case SEPA_CARD_CLEARING_SINGLE = '106';
    case SEPA_DD_POS_GENERATED = '107';
    case SEPA_DD_RETURN_B2B = '108';
    case SEPA_DD_RETURN_CORE = '109';
    case SEPA_CARD_CLEARING_RETURN = '110';
    case CHECK_REVERSAL = '111';
    case PAYMENT_ORDER_CLEARING = '112';
    case SEPA_CT_SINGLE_DEBIT = '116';
    case SEPA_CT_STANDING_ORDER_DEBIT = '117';
    case SEPA_CT_INSTANT_SINGLE_DEBIT = '118';
    case SEPA_CT_SINGLE_DEBIT_DONATION = '119';
    case CURRENCY_CHECK_EUR = '122';
    case SEPA_STANDING_ORDER_CREDIT = '152';
    case SEPA_CT_SINGLE_SALARY = '153';
    case SEPA_CT_SINGLE_VWL = '154';
    case SEPA_CT_SINGLE_AVWL = '155';
    case SEPA_CT_SINGLE_PUBLIC = '156';
    case SEPA_CT_INSTANT_SALARY = '157';
    case SEPA_CT_RETURN = '159';
    case SEPA_CT_INSTANT_RETURN = '160';
    case SEPA_CT_INSTANT_VWL = '161';
    case SEPA_CT_INSTANT_AVWL = '162';
    case SEPA_CT_INSTANT_PUBLIC = '163';
    case SEPA_CT_INSTANT_RF = '164';
    case SEPA_CT_INSTANT_DONATION = '165';
    case SEPA_CT_SINGLE_CREDIT = '166';
    case SEPA_CT_SINGLE_RF = '167';
    case SEPA_CT_INSTANT_CREDIT = '168';
    case SEPA_CT_SINGLE_DONATION_CREDIT = '169';
    case CHECK_COLLECTION_CREDIT = '170';
    case SEPA_DD_COLLECTION_CORE = '171';
    case SEPA_DD_COLLECTION_B2B = '174';
    case SEPA_CT_ONLINE_DEBIT = '177';
    case SEPA_DD_REFUND_CORE = '181';
    case SEPA_CARD_CLEARING_REFUND = '182';
    case CHECK_RETURN_CREDIT = '183';
    case SEPA_DD_REFUND_B2B = '184';
    case CHECK_DEBIT_BATCH = '185';
    case SEPA_CT_INSTANT_BATCH_DEBIT_RESERVED = '188';
    case SEPA_CT_INSTANT_BATCH_CREDIT = '189';
    case SEPA_CARD_CLEARING_BATCH_DEBIT = '190';
    case SEPA_CT_BATCH_DEBIT = '191';
    case SEPA_DD_BATCH_CREDIT_CORE = '192';
    case SEPA_DD_REVERSAL = '193';
    case SEPA_CT_BATCH_CREDIT = '194';
    case SEPA_DD_BATCH_DEBIT_CORE = '195';
    case SEPA_DD_BATCH_CREDIT_B2B = '196';
    case SEPA_DD_BATCH_DEBIT_B2B = '197';
    case SEPA_CARD_CLEARING_BATCH_CREDIT = '198';
    case SEPA_CARD_CLEARING_REVERSAL = '199';

        // =========================================================================
        // 2XX: Auslandsgeschäft / Auslandszahlungsverkehr
        // =========================================================================
    case FOREIGN_PAYMENT_ORDER = '201';
    case FOREIGN_CREDIT = '202';
    case COLLECTION = '203';
    case LETTER_OF_CREDIT = '204';
    case GUARANTEE_FOREIGN = '205';
    case FOREIGN_TRANSFER = '206';
    case REIMBURSEMENT = '208';
    case PAYMENT_BY_CHECK = '209';
    case ELECTRONIC_PAYMENT = '210';
    case ELECTRONIC_PAYMENT_INCOMING = '211';
    case STANDING_ORDER_FOREIGN = '212';
    case DIRECT_DEBIT_FOREIGN = '213';
    case DOCUMENTARY_COLLECTION_IMPORT = '214';
    case DOCUMENTARY_COLLECTION_EXPORT = '215';
    case BILL_COLLECTION_IMPORT = '216';
    case BILL_COLLECTION_EXPORT = '217';
    case LETTER_OF_CREDIT_IMPORT = '218';
    case LETTER_OF_CREDIT_EXPORT = '219';
    case FOREIGN_CHECK_CREDIT_PROVISIONAL = '220';
    case FOREIGN_CHECK_COLLECTION_CREDIT = '221';
    case FOREIGN_CHECK_DEBIT = '222';
    case FOREIGN_EC_CHECK_DEBIT = '223';
    case CURRENCY_PURCHASE = '224';
    case CURRENCY_SALE = '225';

        // =========================================================================
        // 3XX: Wertpapiergeschäft (Securities)
        // =========================================================================
    case SECURITIES_COLLECTION = '301';
    case COUPON_DIVIDEND = '302';
    case SECURITIES = '303';
    case SECURITIES_TRANSFER = '304';
    case REGISTERED_BOND = '305';
    case PROMISSORY_NOTE = '306';
    case SECURITIES_SUBSCRIPTION = '307';
    case SUBSCRIPTION_RIGHTS_TRADING = '308';
    case BONUS_RIGHTS_TRADING = '309';
    case OPTIONS_TRADING = '310';
    case FUTURES_TRADING = '311';
    case SECURITIES_FEES = '320';
    case CUSTODY_FEES = '321';
    case SECURITIES_INCOME = '330';
    case MATURING_SECURITIES_CREDIT = '340';
    case SECURITIES_REVERSAL = '399';

        // =========================================================================
        // 4XX: Devisengeschäft (Foreign Exchange)
        // =========================================================================
    case FX_SPOT = '401';
    case FX_FORWARD = '402';
    case FX_TRAVEL = '403';
    case FX_CHECK = '404';
    case FINANCIAL_INNOVATIONS = '405';
    case FX_TRADING = '406';
    case MONEY_MARKET = '407';
    case MONEY_MARKET_INTEREST = '408';
    case CAPITAL_PLUS_INTEREST = '409';
    case FX_SPOT_PURCHASE = '411';
    case FX_SPOT_SALE = '412';
    case FX_FORWARD_PURCHASE = '413';
    case FX_FORWARD_SALE = '414';
    case FX_OVERNIGHT_ACTIVE = '415';
    case FX_OVERNIGHT_PASSIVE = '416';
    case FX_TERM_ACTIVE = '417';
    case FX_TERM_PASSIVE = '418';
    case CALL_MONEY_ACTIVE = '419';
    case CALL_MONEY_PASSIVE = '420';
    case FX_OPTIONS = '421';
    case FX_SWAP = '422';
    case PRECIOUS_METAL_PURCHASE = '423';
    case PRECIOUS_METAL_SALE = '424';

        // =========================================================================
        // 6XX: Kreditgeschäft (Loans)
        // =========================================================================
    case LOAN_INSTALLMENT_COLLECTION = '601';
    case LOAN_INSTALLMENT_TRANSFER = '602';
    case LOAN_REPAYMENT = '603';
    case LOAN_INTEREST = '604';
    case LOAN_INTEREST_WITH_FEES = '605';
    case LOAN_CAPITAL = '606';
    case LOAN_PAYMENT = '607';

        // =========================================================================
        // 8XX: Sonstige (Miscellaneous)
        // =========================================================================
    case CHECK_CARD = '801';
    case CHECK_BOOK = '802';
    case CUSTODY = '803';
    case STANDING_ORDER_FEE = '804';
    case ACCOUNT_CLOSING = '805';
    case POSTAGE_FEE = '806';
    case CHARGES = '807';
    case FEES = '808';
    case COMMISSION = '809';
    case REMINDER_FEE = '810';
    case CREDIT_COSTS = '811';
    case DEFERRAL_INTEREST = '812';
    case DISAGIO = '813';
    case INTEREST = '814';
    case CAPITALIZED_INTEREST = '815';
    case INTEREST_RATE_CHANGE = '816';
    case INTEREST_CORRECTION = '817';
    case DIRECT_DEBIT_INTERNAL = '818';
    case SALARY = '819';
    case INTERNAL_TRANSFER = '820';
    case TELEPHONE = '821';
    case WITHDRAWAL_PLAN = '822';
    case FIXED_DEPOSIT = '823';
    case BORROWED_MONEY = '824';
    case UNIVERSAL_LOAN = '825';
    case DYNAMIC_SAVINGS = '826';
    case SURPLUS_SAVINGS = '827';
    case SAVINGS_BOND = '828';
    case SAVINGS_PLAN = '829';
    case BONUS = '830';
    case OLD_ACCOUNT = '831';
    case MORTGAGE = '832';
    case CASH_CONCENTRATING_MAIN = '833';
    case CASH_CONCENTRATING_SUB = '834';
    case OTHER_UNDEFINED = '835';
    case COMPLAINT_BOOKING = '836';
    case VAT = '837';
    case EURO_CONVERSION = '888';
    case REVERSAL = '899';

        // =========================================================================
        // 9XX: Unstrukturierte Belegung
        // =========================================================================
    case CUSTODY_STATEMENT = '997';
    case UNSTRUCTURED = '999';

    /**
     * Returns the German description of the GVC code.
     */
    public function description(): string {
        return match ($this) {
            // 0XX
            self::CREDIT_CARD_SETTLEMENT => 'Kreditkartenabrechnung',
            self::BANK_TO_BANK_TRANSFER => 'Bank-an-Bank-Zahlung',
            self::BILL_OF_EXCHANGE_SUBMISSION => 'Wechseleinreichung',
            self::BILL_OF_EXCHANGE => 'Wechsel',
            self::TELEPHONE_ORDER => 'Telefonauftrag',
            self::BATCH_TRANSFER => 'Sammler',
            self::CASH_DEPOSIT => 'Einzahlung',
            self::CASH_WITHDRAWAL => 'Auszahlung',
            self::ONLINE_DIRECT_DEBIT => 'Online-Einzugsauftrag',
            self::EXPRESS_TRANSFER => 'Eilüberweisung',
            self::CREDIT_TRANSFER_FIXED_VALUE => 'Überweisungsgutschrift mit Festvaluta',
            self::DISCOUNT_BILL => 'Diskont-Wechsel',
            self::GUARANTEE_DOMESTIC => 'Aval (Inland)',
            self::GELDKARTE_MERCHANT => 'GeldKarte (Händler)',

            // 1XX - Checks
            self::CHECK_BEARER => 'Inhaberscheck',
            self::CHECK_ORDER => 'Orderscheck',
            self::CHECK_TRAVEL => 'Reisescheck',

            // 1XX - SEPA Direct Debit
            self::SEPA_DD_SINGLE_B2B => 'SEPA-Lastschrift Einzelbuchung B2B',
            self::SEPA_DD_SINGLE_CORE => 'SEPA-Lastschrift Einzelbuchung Core',
            self::SEPA_CARD_CLEARING_SINGLE => 'SEPA Card Clearing Einzelbuchung',
            self::SEPA_DD_POS_GENERATED => 'SEPA-Lastschrift POS-generiert',
            self::SEPA_DD_RETURN_B2B => 'SEPA-Lastschrift Rückbelastung B2B',
            self::SEPA_DD_RETURN_CORE => 'SEPA-Lastschrift Rückbelastung Core',
            self::SEPA_CARD_CLEARING_RETURN => 'SEPA Card Clearing Rückbelastung',
            self::CHECK_REVERSAL => 'Scheckrückrechnung',
            self::PAYMENT_ORDER_CLEARING => 'Zahlungsanweisung zur Verrechnung',

            // 1XX - SEPA Credit Transfer Debit
            self::SEPA_CT_SINGLE_DEBIT => 'SEPA-Überweisung Einzelbuchung Soll',
            self::SEPA_CT_STANDING_ORDER_DEBIT => 'SEPA-Dauerauftrag Soll',
            self::SEPA_CT_INSTANT_SINGLE_DEBIT => 'SEPA-Echtzeitüberweisung Einzelbuchung Soll',
            self::SEPA_CT_SINGLE_DEBIT_DONATION => 'SEPA-Überweisung Spende Soll',
            self::CURRENCY_CHECK_EUR => 'Währungsscheck auf Euro',

            // 1XX - SEPA Credit Transfer Credit
            self::SEPA_STANDING_ORDER_CREDIT => 'SEPA-Dauerauftragsgutschrift',
            self::SEPA_CT_SINGLE_SALARY => 'SEPA-Überweisung Lohn/Gehalt/Rente',
            self::SEPA_CT_SINGLE_VWL => 'SEPA-Überweisung VWL',
            self::SEPA_CT_SINGLE_AVWL => 'SEPA-Überweisung AVWL',
            self::SEPA_CT_SINGLE_PUBLIC => 'SEPA-Überweisung öffentliche Kassen',
            self::SEPA_CT_INSTANT_SALARY => 'SEPA-Echtzeitüberweisung Lohn/Gehalt/Rente',
            self::SEPA_CT_RETURN => 'SEPA-Überweisung Rückbuchung',
            self::SEPA_CT_INSTANT_RETURN => 'SEPA-Echtzeitüberweisung Rückbuchung',
            self::SEPA_CT_INSTANT_VWL => 'SEPA-Echtzeitüberweisung VWL',
            self::SEPA_CT_INSTANT_AVWL => 'SEPA-Echtzeitüberweisung AVWL',
            self::SEPA_CT_INSTANT_PUBLIC => 'SEPA-Echtzeitüberweisung öffentliche Kassen',
            self::SEPA_CT_INSTANT_RF => 'SEPA-Echtzeitüberweisung RF-Referenz',
            self::SEPA_CT_INSTANT_DONATION => 'SEPA-Echtzeitüberweisung Spende',
            self::SEPA_CT_SINGLE_CREDIT => 'SEPA-Überweisung Einzelbuchung Haben',
            self::SEPA_CT_SINGLE_RF => 'SEPA-Überweisung RF-Referenz',
            self::SEPA_CT_INSTANT_CREDIT => 'SEPA-Echtzeitüberweisung Haben',
            self::SEPA_CT_SINGLE_DONATION_CREDIT => 'SEPA-Überweisung Spende Haben',

            // 1XX - Collections & Batches
            self::CHECK_COLLECTION_CREDIT => 'Scheckeinreichung Gutschrift',
            self::SEPA_DD_COLLECTION_CORE => 'SEPA-Lastschrifteinzug Core',
            self::SEPA_DD_COLLECTION_B2B => 'SEPA-Lastschrifteinzug B2B',
            self::SEPA_CT_ONLINE_DEBIT => 'SEPA-Überweisung Online Soll',
            self::SEPA_DD_REFUND_CORE => 'SEPA-Lastschrift Wiedergutschrift Core',
            self::SEPA_CARD_CLEARING_REFUND => 'SEPA Card Clearing Wiedergutschrift',
            self::CHECK_RETURN_CREDIT => 'Scheckrückgabe Haben',
            self::SEPA_DD_REFUND_B2B => 'SEPA-Lastschrift Wiedergutschrift B2B',
            self::CHECK_DEBIT_BATCH => 'Scheckbelastung Sammler',
            self::SEPA_CT_INSTANT_BATCH_DEBIT_RESERVED => 'SEPA-Echtzeitüberweisung Sammler Soll (reserviert)',
            self::SEPA_CT_INSTANT_BATCH_CREDIT => 'SEPA-Echtzeitüberweisung Sammler Haben',
            self::SEPA_CARD_CLEARING_BATCH_DEBIT => 'SEPA Card Clearing Sammler Soll',
            self::SEPA_CT_BATCH_DEBIT => 'SEPA-Überweisung Sammler Soll',
            self::SEPA_DD_BATCH_CREDIT_CORE => 'SEPA-Lastschrift Sammler Haben Core',
            self::SEPA_DD_REVERSAL => 'SEPA-Lastschrift Reversal',
            self::SEPA_CT_BATCH_CREDIT => 'SEPA-Überweisung Sammler Haben',
            self::SEPA_DD_BATCH_DEBIT_CORE => 'SEPA-Lastschrift Sammler Soll Core',
            self::SEPA_DD_BATCH_CREDIT_B2B => 'SEPA-Lastschrift Sammler Haben B2B',
            self::SEPA_DD_BATCH_DEBIT_B2B => 'SEPA-Lastschrift Sammler Soll B2B',
            self::SEPA_CARD_CLEARING_BATCH_CREDIT => 'SEPA Card Clearing Sammler Haben',
            self::SEPA_CARD_CLEARING_REVERSAL => 'SEPA Card Clearing Reversal',

            // 2XX - Foreign
            self::FOREIGN_PAYMENT_ORDER => 'Zahlungsauftrag Ausland',
            self::FOREIGN_CREDIT => 'Auslandsvergütung',
            self::COLLECTION => 'Inkasso',
            self::LETTER_OF_CREDIT => 'Akkreditiv',
            self::GUARANTEE_FOREIGN => 'Aval Ausland',
            self::FOREIGN_TRANSFER => 'Auslandsüberweisung',
            self::REIMBURSEMENT => 'Rembourse',
            self::PAYMENT_BY_CHECK => 'Zahlung per Scheck',
            self::ELECTRONIC_PAYMENT => 'Zahlung über elektronische Medien',
            self::ELECTRONIC_PAYMENT_INCOMING => 'Zahlungseingang elektronisch',
            self::STANDING_ORDER_FOREIGN => 'Dauerauftrag Ausland',
            self::DIRECT_DEBIT_FOREIGN => 'Lastschrift Ausland',
            self::DOCUMENTARY_COLLECTION_IMPORT => 'Dokumenten-Inkasso Import',
            self::DOCUMENTARY_COLLECTION_EXPORT => 'Dokumenten-Inkasso Export',
            self::BILL_COLLECTION_IMPORT => 'Wechsel-Inkasso Import',
            self::BILL_COLLECTION_EXPORT => 'Wechsel-Inkasso Export',
            self::LETTER_OF_CREDIT_IMPORT => 'Import-Akkreditiv',
            self::LETTER_OF_CREDIT_EXPORT => 'Export-Akkreditiv',
            self::FOREIGN_CHECK_CREDIT_PROVISIONAL => 'Gutschrift E.v. Auslands-Scheck',
            self::FOREIGN_CHECK_COLLECTION_CREDIT => 'Gutschrift Auslands-Scheck-Inkasso',
            self::FOREIGN_CHECK_DEBIT => 'Belastung Auslands-Scheck',
            self::FOREIGN_EC_CHECK_DEBIT => 'Belastung Auslands-ec-Scheck',
            self::CURRENCY_PURCHASE => 'Sorten-Ankauf',
            self::CURRENCY_SALE => 'Sorten-Verkauf',

            // 3XX - Securities
            self::SECURITIES_COLLECTION => 'Wertpapier-Inkasso',
            self::COUPON_DIVIDEND => 'Kupon/Dividenden',
            self::SECURITIES => 'Effekten',
            self::SECURITIES_TRANSFER => 'Wertpapier-Übertrag',
            self::REGISTERED_BOND => 'Namensschuldverschreibung',
            self::PROMISSORY_NOTE => 'Schuldschein',
            self::SECURITIES_SUBSCRIPTION => 'Wertpapierzeichnung',
            self::SUBSCRIPTION_RIGHTS_TRADING => 'Bezugsrechte-Handel',
            self::BONUS_RIGHTS_TRADING => 'Bonusrechte-Handel',
            self::OPTIONS_TRADING => 'Optionen-Handel',
            self::FUTURES_TRADING => 'Termingeschäfte',
            self::SECURITIES_FEES => 'Wertpapiergebühren',
            self::CUSTODY_FEES => 'Depotgebühren',
            self::SECURITIES_INCOME => 'Erträge aus Wertpapieren',
            self::MATURING_SECURITIES_CREDIT => 'Gutschrift fälliger Wertpapiere',
            self::SECURITIES_REVERSAL => 'Wertpapier-Storno',

            // 4XX - FX
            self::FX_SPOT => 'Kassedevisen',
            self::FX_FORWARD => 'Termindevisen',
            self::FX_TRAVEL => 'Reisedevisen',
            self::FX_CHECK => 'Devisenscheck',
            self::FINANCIAL_INNOVATIONS => 'Finanzinnovationen',
            self::FX_TRADING => 'Devisenhandel',
            self::MONEY_MARKET => 'Geldhandel',
            self::MONEY_MARKET_INTEREST => 'Zinsen Geldhandel',
            self::CAPITAL_PLUS_INTEREST => 'Kapital plus Zinsen',
            self::FX_SPOT_PURCHASE => 'Devisenkassa-Kauf',
            self::FX_SPOT_SALE => 'Devisenkassa-Verkauf',
            self::FX_FORWARD_PURCHASE => 'Devisentermin-Kauf',
            self::FX_FORWARD_SALE => 'Devisentermin-Verkauf',
            self::FX_OVERNIGHT_ACTIVE => 'FW-Tagegeld-Aktiv',
            self::FX_OVERNIGHT_PASSIVE => 'FW-Tagegeld-Passiv',
            self::FX_TERM_ACTIVE => 'FW-Termingeld-Aktiv',
            self::FX_TERM_PASSIVE => 'FW-Termingeld-Passiv',
            self::CALL_MONEY_ACTIVE => 'Call-Geld-Aktiv',
            self::CALL_MONEY_PASSIVE => 'Call-Geld-Passiv',
            self::FX_OPTIONS => 'Devisenoptionen',
            self::FX_SWAP => 'Devisen-Swap',
            self::PRECIOUS_METAL_PURCHASE => 'Edelmetall-Ankauf',
            self::PRECIOUS_METAL_SALE => 'Edelmetall-Verkauf',

            // 6XX - Loans
            self::LOAN_INSTALLMENT_COLLECTION => 'Einzug Raten/Annuitäten',
            self::LOAN_INSTALLMENT_TRANSFER => 'Überweisung Raten/Annuitäten',
            self::LOAN_REPAYMENT => 'Tilgung',
            self::LOAN_INTEREST => 'Darlehenszinsen',
            self::LOAN_INTEREST_WITH_FEES => 'Darlehenszinsen mit Nebenleistungen',
            self::LOAN_CAPITAL => 'Kredit Kapital',
            self::LOAN_PAYMENT => 'Kredit-/Zinszahlung',

            // 8XX - Misc
            self::CHECK_CARD => 'Scheckkarte',
            self::CHECK_BOOK => 'Scheckheft',
            self::CUSTODY => 'Depotverwahrung',
            self::STANDING_ORDER_FEE => 'Dauerauftragsgebühren',
            self::ACCOUNT_CLOSING => 'Abschluss',
            self::POSTAGE_FEE => 'Porto/Zustellgebühren',
            self::CHARGES => 'Preise/Spesen',
            self::FEES => 'Gebühren',
            self::COMMISSION => 'Provisionen',
            self::REMINDER_FEE => 'Mahngebühren',
            self::CREDIT_COSTS => 'Kreditkosten',
            self::DEFERRAL_INTEREST => 'Stundungszinsen',
            self::DISAGIO => 'Disagio',
            self::INTEREST => 'Zinsen',
            self::CAPITALIZED_INTEREST => 'Kapitalisierte Zinsen',
            self::INTEREST_RATE_CHANGE => 'Zinssatzänderung',
            self::INTEREST_CORRECTION => 'Zinsberichtigung',
            self::DIRECT_DEBIT_INTERNAL => 'Abbuchung',
            self::SALARY => 'Bezüge',
            self::INTERNAL_TRANSFER => 'Übertrag',
            self::TELEPHONE => 'Telefon',
            self::WITHDRAWAL_PLAN => 'Auszahlplan',
            self::FIXED_DEPOSIT => 'Festgeld',
            self::BORROWED_MONEY => 'Leihgeld',
            self::UNIVERSAL_LOAN => 'Universaldarlehen',
            self::DYNAMIC_SAVINGS => 'Dynamisches Sparen',
            self::SURPLUS_SAVINGS => 'Überschusssparen',
            self::SAVINGS_BOND => 'Sparbrief',
            self::SAVINGS_PLAN => 'Sparplan',
            self::BONUS => 'Bonus',
            self::OLD_ACCOUNT => 'Alte Rechnung',
            self::MORTGAGE => 'Hypothek',
            self::CASH_CONCENTRATING_MAIN => 'Cash Concentrating Hauptkonto',
            self::CASH_CONCENTRATING_SUB => 'Cash Concentrating Nebenkonto',
            self::OTHER_UNDEFINED => 'Sonstige nicht definierte GV-Arten',
            self::COMPLAINT_BOOKING => 'Reklamationsbuchung',
            self::VAT => 'Umsatzsteuer',
            self::EURO_CONVERSION => 'Umbuchung Euro-Umstellung',
            self::REVERSAL => 'Storno',

            // 9XX - Unstructured
            self::CUSTODY_STATEMENT => 'Depotaufstellung',
            self::UNSTRUCTURED => 'Unstrukturierte Belegung',
        };
    }

    /**
     * Returns the English description of the GVC code.
     */
    public function descriptionEn(): string {
        return match ($this) {
            // 0XX
            self::CREDIT_CARD_SETTLEMENT => 'Credit Card Settlement',
            self::BANK_TO_BANK_TRANSFER => 'Bank to Bank Transfer',
            self::BILL_OF_EXCHANGE_SUBMISSION => 'Bill of Exchange Submission',
            self::BILL_OF_EXCHANGE => 'Bill of Exchange',
            self::TELEPHONE_ORDER => 'Telephone Order',
            self::BATCH_TRANSFER => 'Batch Transfer',
            self::CASH_DEPOSIT => 'Cash Deposit',
            self::CASH_WITHDRAWAL => 'Cash Withdrawal',
            self::ONLINE_DIRECT_DEBIT => 'Online Direct Debit',
            self::EXPRESS_TRANSFER => 'Express Transfer',
            self::CREDIT_TRANSFER_FIXED_VALUE => 'Credit Transfer Fixed Value Date',
            self::DISCOUNT_BILL => 'Discount Bill',
            self::GUARANTEE_DOMESTIC => 'Guarantee (Domestic)',
            self::GELDKARTE_MERCHANT => 'GeldKarte Merchant Credit',

            // 1XX - Checks
            self::CHECK_BEARER => 'Bearer Check',
            self::CHECK_ORDER => 'Order Check',
            self::CHECK_TRAVEL => 'Travel Check',

            // 1XX - SEPA Direct Debit
            self::SEPA_DD_SINGLE_B2B => 'SEPA Direct Debit Single B2B',
            self::SEPA_DD_SINGLE_CORE => 'SEPA Direct Debit Single Core',
            self::SEPA_CARD_CLEARING_SINGLE => 'SEPA Card Clearing Single',
            self::SEPA_DD_POS_GENERATED => 'SEPA Direct Debit POS Generated',
            self::SEPA_DD_RETURN_B2B => 'SEPA Direct Debit Return B2B',
            self::SEPA_DD_RETURN_CORE => 'SEPA Direct Debit Return Core',
            self::SEPA_CARD_CLEARING_RETURN => 'SEPA Card Clearing Return',
            self::CHECK_REVERSAL => 'Check Reversal',
            self::PAYMENT_ORDER_CLEARING => 'Payment Order Clearing',

            // 1XX - SEPA Credit Transfer Debit
            self::SEPA_CT_SINGLE_DEBIT => 'SEPA Credit Transfer Single Debit',
            self::SEPA_CT_STANDING_ORDER_DEBIT => 'SEPA Standing Order Debit',
            self::SEPA_CT_INSTANT_SINGLE_DEBIT => 'SEPA Instant Credit Transfer Debit',
            self::SEPA_CT_SINGLE_DEBIT_DONATION => 'SEPA Credit Transfer Donation Debit',
            self::CURRENCY_CHECK_EUR => 'Currency Check EUR',

            // 1XX - SEPA Credit Transfer Credit
            self::SEPA_STANDING_ORDER_CREDIT => 'SEPA Standing Order Credit',
            self::SEPA_CT_SINGLE_SALARY => 'SEPA Credit Transfer Salary',
            self::SEPA_CT_SINGLE_VWL => 'SEPA Credit Transfer Capital Formation',
            self::SEPA_CT_SINGLE_AVWL => 'SEPA Credit Transfer Pension Savings',
            self::SEPA_CT_SINGLE_PUBLIC => 'SEPA Credit Transfer Public Authority',
            self::SEPA_CT_INSTANT_SALARY => 'SEPA Instant Credit Transfer Salary',
            self::SEPA_CT_RETURN => 'SEPA Credit Transfer Return',
            self::SEPA_CT_INSTANT_RETURN => 'SEPA Instant Credit Transfer Return',
            self::SEPA_CT_INSTANT_VWL => 'SEPA Instant Credit Transfer Capital Formation',
            self::SEPA_CT_INSTANT_AVWL => 'SEPA Instant Credit Transfer Pension Savings',
            self::SEPA_CT_INSTANT_PUBLIC => 'SEPA Instant Credit Transfer Public Authority',
            self::SEPA_CT_INSTANT_RF => 'SEPA Instant Credit Transfer RF Reference',
            self::SEPA_CT_INSTANT_DONATION => 'SEPA Instant Credit Transfer Donation',
            self::SEPA_CT_SINGLE_CREDIT => 'SEPA Credit Transfer Single Credit',
            self::SEPA_CT_SINGLE_RF => 'SEPA Credit Transfer RF Reference',
            self::SEPA_CT_INSTANT_CREDIT => 'SEPA Instant Credit Transfer Credit',
            self::SEPA_CT_SINGLE_DONATION_CREDIT => 'SEPA Credit Transfer Donation Credit',

            // 1XX - Collections & Batches
            self::CHECK_COLLECTION_CREDIT => 'Check Collection Credit',
            self::SEPA_DD_COLLECTION_CORE => 'SEPA Direct Debit Collection Core',
            self::SEPA_DD_COLLECTION_B2B => 'SEPA Direct Debit Collection B2B',
            self::SEPA_CT_ONLINE_DEBIT => 'SEPA Credit Transfer Online Debit',
            self::SEPA_DD_REFUND_CORE => 'SEPA Direct Debit Refund Core',
            self::SEPA_CARD_CLEARING_REFUND => 'SEPA Card Clearing Refund',
            self::CHECK_RETURN_CREDIT => 'Check Return Credit',
            self::SEPA_DD_REFUND_B2B => 'SEPA Direct Debit Refund B2B',
            self::CHECK_DEBIT_BATCH => 'Check Debit Batch',
            self::SEPA_CT_INSTANT_BATCH_DEBIT_RESERVED => 'SEPA Instant Credit Transfer Batch Debit (Reserved)',
            self::SEPA_CT_INSTANT_BATCH_CREDIT => 'SEPA Instant Credit Transfer Batch Credit',
            self::SEPA_CARD_CLEARING_BATCH_DEBIT => 'SEPA Card Clearing Batch Debit',
            self::SEPA_CT_BATCH_DEBIT => 'SEPA Credit Transfer Batch Debit',
            self::SEPA_DD_BATCH_CREDIT_CORE => 'SEPA Direct Debit Batch Credit Core',
            self::SEPA_DD_REVERSAL => 'SEPA Direct Debit Reversal',
            self::SEPA_CT_BATCH_CREDIT => 'SEPA Credit Transfer Batch Credit',
            self::SEPA_DD_BATCH_DEBIT_CORE => 'SEPA Direct Debit Batch Debit Core',
            self::SEPA_DD_BATCH_CREDIT_B2B => 'SEPA Direct Debit Batch Credit B2B',
            self::SEPA_DD_BATCH_DEBIT_B2B => 'SEPA Direct Debit Batch Debit B2B',
            self::SEPA_CARD_CLEARING_BATCH_CREDIT => 'SEPA Card Clearing Batch Credit',
            self::SEPA_CARD_CLEARING_REVERSAL => 'SEPA Card Clearing Reversal',

            // 2XX - Foreign
            self::FOREIGN_PAYMENT_ORDER => 'Foreign Payment Order',
            self::FOREIGN_CREDIT => 'Foreign Credit',
            self::COLLECTION => 'Collection',
            self::LETTER_OF_CREDIT => 'Letter of Credit',
            self::GUARANTEE_FOREIGN => 'Guarantee (Foreign)',
            self::FOREIGN_TRANSFER => 'Foreign Transfer',
            self::REIMBURSEMENT => 'Reimbursement',
            self::PAYMENT_BY_CHECK => 'Payment by Check',
            self::ELECTRONIC_PAYMENT => 'Electronic Payment',
            self::ELECTRONIC_PAYMENT_INCOMING => 'Electronic Payment Incoming',
            self::STANDING_ORDER_FOREIGN => 'Standing Order Foreign',
            self::DIRECT_DEBIT_FOREIGN => 'Direct Debit Foreign',
            self::DOCUMENTARY_COLLECTION_IMPORT => 'Documentary Collection Import',
            self::DOCUMENTARY_COLLECTION_EXPORT => 'Documentary Collection Export',
            self::BILL_COLLECTION_IMPORT => 'Bill Collection Import',
            self::BILL_COLLECTION_EXPORT => 'Bill Collection Export',
            self::LETTER_OF_CREDIT_IMPORT => 'Letter of Credit Import',
            self::LETTER_OF_CREDIT_EXPORT => 'Letter of Credit Export',
            self::FOREIGN_CHECK_CREDIT_PROVISIONAL => 'Foreign Check Credit Provisional',
            self::FOREIGN_CHECK_COLLECTION_CREDIT => 'Foreign Check Collection Credit',
            self::FOREIGN_CHECK_DEBIT => 'Foreign Check Debit',
            self::FOREIGN_EC_CHECK_DEBIT => 'Foreign EC Check Debit',
            self::CURRENCY_PURCHASE => 'Currency Purchase',
            self::CURRENCY_SALE => 'Currency Sale',

            // 3XX - Securities
            self::SECURITIES_COLLECTION => 'Securities Collection',
            self::COUPON_DIVIDEND => 'Coupon/Dividend',
            self::SECURITIES => 'Securities',
            self::SECURITIES_TRANSFER => 'Securities Transfer',
            self::REGISTERED_BOND => 'Registered Bond',
            self::PROMISSORY_NOTE => 'Promissory Note',
            self::SECURITIES_SUBSCRIPTION => 'Securities Subscription',
            self::SUBSCRIPTION_RIGHTS_TRADING => 'Subscription Rights Trading',
            self::BONUS_RIGHTS_TRADING => 'Bonus Rights Trading',
            self::OPTIONS_TRADING => 'Options Trading',
            self::FUTURES_TRADING => 'Futures Trading',
            self::SECURITIES_FEES => 'Securities Fees',
            self::CUSTODY_FEES => 'Custody Fees',
            self::SECURITIES_INCOME => 'Securities Income',
            self::MATURING_SECURITIES_CREDIT => 'Maturing Securities Credit',
            self::SECURITIES_REVERSAL => 'Securities Reversal',

            // 4XX - FX
            self::FX_SPOT => 'FX Spot',
            self::FX_FORWARD => 'FX Forward',
            self::FX_TRAVEL => 'Travel Currency',
            self::FX_CHECK => 'FX Check',
            self::FINANCIAL_INNOVATIONS => 'Financial Innovations',
            self::FX_TRADING => 'FX Trading',
            self::MONEY_MARKET => 'Money Market',
            self::MONEY_MARKET_INTEREST => 'Money Market Interest',
            self::CAPITAL_PLUS_INTEREST => 'Capital Plus Interest',
            self::FX_SPOT_PURCHASE => 'FX Spot Purchase',
            self::FX_SPOT_SALE => 'FX Spot Sale',
            self::FX_FORWARD_PURCHASE => 'FX Forward Purchase',
            self::FX_FORWARD_SALE => 'FX Forward Sale',
            self::FX_OVERNIGHT_ACTIVE => 'FX Overnight Active',
            self::FX_OVERNIGHT_PASSIVE => 'FX Overnight Passive',
            self::FX_TERM_ACTIVE => 'FX Term Active',
            self::FX_TERM_PASSIVE => 'FX Term Passive',
            self::CALL_MONEY_ACTIVE => 'Call Money Active',
            self::CALL_MONEY_PASSIVE => 'Call Money Passive',
            self::FX_OPTIONS => 'FX Options',
            self::FX_SWAP => 'FX Swap',
            self::PRECIOUS_METAL_PURCHASE => 'Precious Metal Purchase',
            self::PRECIOUS_METAL_SALE => 'Precious Metal Sale',

            // 6XX - Loans
            self::LOAN_INSTALLMENT_COLLECTION => 'Loan Installment Collection',
            self::LOAN_INSTALLMENT_TRANSFER => 'Loan Installment Transfer',
            self::LOAN_REPAYMENT => 'Loan Repayment',
            self::LOAN_INTEREST => 'Loan Interest',
            self::LOAN_INTEREST_WITH_FEES => 'Loan Interest with Fees',
            self::LOAN_CAPITAL => 'Loan Capital',
            self::LOAN_PAYMENT => 'Loan Payment',

            // 8XX - Misc
            self::CHECK_CARD => 'Check Card',
            self::CHECK_BOOK => 'Check Book',
            self::CUSTODY => 'Custody',
            self::STANDING_ORDER_FEE => 'Standing Order Fee',
            self::ACCOUNT_CLOSING => 'Account Closing',
            self::POSTAGE_FEE => 'Postage Fee',
            self::CHARGES => 'Charges',
            self::FEES => 'Fees',
            self::COMMISSION => 'Commission',
            self::REMINDER_FEE => 'Reminder Fee',
            self::CREDIT_COSTS => 'Credit Costs',
            self::DEFERRAL_INTEREST => 'Deferral Interest',
            self::DISAGIO => 'Disagio',
            self::INTEREST => 'Interest',
            self::CAPITALIZED_INTEREST => 'Capitalized Interest',
            self::INTEREST_RATE_CHANGE => 'Interest Rate Change',
            self::INTEREST_CORRECTION => 'Interest Correction',
            self::DIRECT_DEBIT_INTERNAL => 'Internal Direct Debit',
            self::SALARY => 'Salary',
            self::INTERNAL_TRANSFER => 'Internal Transfer',
            self::TELEPHONE => 'Telephone',
            self::WITHDRAWAL_PLAN => 'Withdrawal Plan',
            self::FIXED_DEPOSIT => 'Fixed Deposit',
            self::BORROWED_MONEY => 'Borrowed Money',
            self::UNIVERSAL_LOAN => 'Universal Loan',
            self::DYNAMIC_SAVINGS => 'Dynamic Savings',
            self::SURPLUS_SAVINGS => 'Surplus Savings',
            self::SAVINGS_BOND => 'Savings Bond',
            self::SAVINGS_PLAN => 'Savings Plan',
            self::BONUS => 'Bonus',
            self::OLD_ACCOUNT => 'Old Account',
            self::MORTGAGE => 'Mortgage',
            self::CASH_CONCENTRATING_MAIN => 'Cash Concentrating Main Account',
            self::CASH_CONCENTRATING_SUB => 'Cash Concentrating Sub Account',
            self::OTHER_UNDEFINED => 'Other Undefined',
            self::COMPLAINT_BOOKING => 'Complaint Booking',
            self::VAT => 'VAT',
            self::EURO_CONVERSION => 'Euro Conversion',
            self::REVERSAL => 'Reversal',

            // 9XX - Unstructured
            self::CUSTODY_STATEMENT => 'Custody Statement',
            self::UNSTRUCTURED => 'Unstructured',
        };
    }

    /**
     * Checks if this is a SEPA transaction.
     */
    public function isSepa(): bool {
        return in_array($this, [
            // SEPA Direct Debit
            self::SEPA_DD_SINGLE_B2B,
            self::SEPA_DD_SINGLE_CORE,
            self::SEPA_CARD_CLEARING_SINGLE,
            self::SEPA_DD_POS_GENERATED,
            self::SEPA_DD_RETURN_B2B,
            self::SEPA_DD_RETURN_CORE,
            self::SEPA_CARD_CLEARING_RETURN,
            // SEPA Credit Transfer
            self::SEPA_CT_SINGLE_DEBIT,
            self::SEPA_CT_STANDING_ORDER_DEBIT,
            self::SEPA_CT_INSTANT_SINGLE_DEBIT,
            self::SEPA_CT_SINGLE_DEBIT_DONATION,
            self::SEPA_STANDING_ORDER_CREDIT,
            self::SEPA_CT_SINGLE_SALARY,
            self::SEPA_CT_SINGLE_VWL,
            self::SEPA_CT_SINGLE_AVWL,
            self::SEPA_CT_SINGLE_PUBLIC,
            self::SEPA_CT_INSTANT_SALARY,
            self::SEPA_CT_RETURN,
            self::SEPA_CT_INSTANT_RETURN,
            self::SEPA_CT_INSTANT_VWL,
            self::SEPA_CT_INSTANT_AVWL,
            self::SEPA_CT_INSTANT_PUBLIC,
            self::SEPA_CT_INSTANT_RF,
            self::SEPA_CT_INSTANT_DONATION,
            self::SEPA_CT_SINGLE_CREDIT,
            self::SEPA_CT_SINGLE_RF,
            self::SEPA_CT_INSTANT_CREDIT,
            self::SEPA_CT_SINGLE_DONATION_CREDIT,
            // SEPA Collections
            self::SEPA_DD_COLLECTION_CORE,
            self::SEPA_DD_COLLECTION_B2B,
            self::SEPA_CT_ONLINE_DEBIT,
            self::SEPA_DD_REFUND_CORE,
            self::SEPA_CARD_CLEARING_REFUND,
            self::SEPA_DD_REFUND_B2B,
            // SEPA Batches
            self::SEPA_CT_INSTANT_BATCH_CREDIT,
            self::SEPA_CARD_CLEARING_BATCH_DEBIT,
            self::SEPA_CT_BATCH_DEBIT,
            self::SEPA_DD_BATCH_CREDIT_CORE,
            self::SEPA_DD_REVERSAL,
            self::SEPA_CT_BATCH_CREDIT,
            self::SEPA_DD_BATCH_DEBIT_CORE,
            self::SEPA_DD_BATCH_CREDIT_B2B,
            self::SEPA_DD_BATCH_DEBIT_B2B,
            self::SEPA_CARD_CLEARING_BATCH_CREDIT,
            self::SEPA_CARD_CLEARING_REVERSAL,
        ], true);
    }

    /**
     * Checks if this is a return/reversal transaction.
     */
    public function isReturn(): bool {
        return in_array($this, [
            self::SEPA_DD_RETURN_B2B,
            self::SEPA_DD_RETURN_CORE,
            self::SEPA_CARD_CLEARING_RETURN,
            self::CHECK_REVERSAL,
            self::SEPA_CT_RETURN,
            self::SEPA_CT_INSTANT_RETURN,
            self::SEPA_DD_REFUND_CORE,
            self::SEPA_CARD_CLEARING_REFUND,
            self::CHECK_RETURN_CREDIT,
            self::SEPA_DD_REFUND_B2B,
            self::SEPA_DD_REVERSAL,
            self::SEPA_CARD_CLEARING_REVERSAL,
            self::SECURITIES_REVERSAL,
            self::REVERSAL,
        ], true);
    }

    /**
     * Checks if this is an instant (real-time) transaction.
     */
    public function isInstant(): bool {
        return in_array($this, [
            self::SEPA_CT_INSTANT_SINGLE_DEBIT,
            self::SEPA_CT_INSTANT_SALARY,
            self::SEPA_CT_INSTANT_RETURN,
            self::SEPA_CT_INSTANT_VWL,
            self::SEPA_CT_INSTANT_AVWL,
            self::SEPA_CT_INSTANT_PUBLIC,
            self::SEPA_CT_INSTANT_RF,
            self::SEPA_CT_INSTANT_DONATION,
            self::SEPA_CT_INSTANT_CREDIT,
            self::SEPA_CT_INSTANT_BATCH_DEBIT_RESERVED,
            self::SEPA_CT_INSTANT_BATCH_CREDIT,
        ], true);
    }

    /**
     * Checks if this is a B2B (business-to-business) transaction.
     */
    public function isB2B(): bool {
        return in_array($this, [
            self::SEPA_DD_SINGLE_B2B,
            self::SEPA_DD_RETURN_B2B,
            self::SEPA_DD_COLLECTION_B2B,
            self::SEPA_DD_REFUND_B2B,
            self::SEPA_DD_BATCH_CREDIT_B2B,
            self::SEPA_DD_BATCH_DEBIT_B2B,
        ], true);
    }

    /**
     * Returns the business category (first digit).
     * 
     * 0/1 = Zahlungsverkehr EU/EWR
     * 2 = Auslandsgeschäft
     * 3 = Wertpapiergeschäft
     * 4 = Devisengeschäft
     * 5 = MAOBE
     * 6 = Kreditgeschäft
     * 7 = Reserve
     * 8 = Sonstige
     * 9 = Unstrukturiert
     */
    public function getCategory(): int {
        return (int) substr($this->value, 0, 1);
    }

    /**
     * Returns the category name.
     */
    public function getCategoryName(): string {
        return match ($this->getCategory()) {
            0, 1 => 'Zahlungsverkehr EU/EWR',
            2 => 'Auslandsgeschäft',
            3 => 'Wertpapiergeschäft',
            4 => 'Devisengeschäft',
            5 => 'MAOBE',
            6 => 'Kreditgeschäft',
            7 => 'Reserve',
            8 => 'Sonstige',
            9 => 'Unstrukturiert',
            default => 'Unbekannt',
        };
    }

    /**
     * Creates a GvcCode from a string value.
     * 
     * @throws InvalidArgumentException If the code is not valid
     */
    public static function fromValue(string $code): self {
        $code = str_pad(trim($code), 3, '0', STR_PAD_LEFT);

        foreach (self::cases() as $case) {
            if ($case->value === $code) {
                return $case;
            }
        }

        throw new InvalidArgumentException("Unknown GVC code: $code");
    }

    /**
     * Creates a GvcCode from a string value, returns null if not found.
     */
    public static function tryFromValue(string $code): ?self {
        $code = str_pad(trim($code), 3, '0', STR_PAD_LEFT);

        foreach (self::cases() as $case) {
            if ($case->value === $code) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Returns all codes for a given category.
     * 
     * @return self[]
     */
    public static function forCategory(int $category): array {
        return array_filter(
            self::cases(),
            fn(self $code) => $code->getCategory() === $category
        );
    }

    /**
     * Creates a GvcCode from a string.
     * Returns null if the code is not recognized.
     * 
     * @deprecated Use tryFromValue() instead
     */
    public static function tryFromString(string $code): ?self {
        return self::tryFromValue($code);
    }

    /**
     * Creates a GvcCode from a string.
     * Throws an exception if the code is not recognized.
     * 
     * @deprecated Use fromValue() instead
     */
    public static function fromString(string $code): self {
        return self::fromValue($code);
    }

    /**
     * Tries to derive the GVC code from a booking text (Buchungstext).
     * 
     * This is useful when DATEV ASCII files don't contain an explicit
     * GVC code but have descriptive booking text.
     * 
     * Supports German and English terms:
     * - Lastschrift / Direct Debit (DD) → 104/105 (Debit) or 171/174 (Credit)
     * - Überweisung / Credit Transfer (CT) → 116 (Debit) or 166 (Credit)
     * 
     * @param string $bookingText The booking text to analyze
     * @param bool $isDebit True if the transaction is a debit (money goes out), false for credit
     * @return self|null The matching GVC code or null
     */
    public static function tryFromBookingText(string $bookingText, bool $isDebit = true): ?self {
        $text = strtoupper(trim($bookingText));

        // =====================================================================
        // Retoure/Rückgabe/Return/Storno - VOR Lastschrift prüfen!
        // Da "Retoure Lastschrift" sonst als Lastschrift erkannt wird
        // =====================================================================
        if (self::containsReturnKeyword($text)) {
            return $isDebit ? self::SEPA_DD_RETURN_CORE : self::SEPA_CT_RETURN;
        }

        // =====================================================================
        // SEPA Lastschrift / Direct Debit (DD)
        // Debit (Zahlungspflichtiger): Geld wird vom Konto abgebucht
        // Credit (Gläubiger/Einzieher): Lastschrifteinzug kommt auf das Konto
        // =====================================================================

        // B2B Lastschrift (104/174)
        if (self::containsDirectDebitKeyword($text) && self::containsB2BKeyword($text)) {
            return $isDebit ? self::SEPA_DD_SINGLE_B2B : self::SEPA_DD_COLLECTION_B2B;
        }

        // Core Lastschrift (105/171)
        if (self::containsDirectDebitKeyword($text)) {
            return $isDebit ? self::SEPA_DD_SINGLE_CORE : self::SEPA_DD_COLLECTION_CORE;
        }

        // =====================================================================
        // SEPA Überweisung / Credit Transfer (CT)
        // Debit: 116 - Überweisung vom Konto
        // Credit: 166 - Überweisung auf das Konto
        // =====================================================================
        if (self::containsCreditTransferKeyword($text)) {
            // Instant/Echtzeit-Überweisung (118/168)
            if (self::containsInstantKeyword($text)) {
                return $isDebit ? self::SEPA_CT_INSTANT_SINGLE_DEBIT : self::SEPA_CT_INSTANT_CREDIT;
            }
            return $isDebit ? self::SEPA_CT_SINGLE_DEBIT : self::SEPA_CT_SINGLE_CREDIT;
        }

        // =====================================================================
        // Gutschrift / Credit (immer Credit)
        // =====================================================================
        if (str_contains($text, 'GUTSCHRIFT') || str_contains($text, 'CREDIT NOTE') || str_contains($text, 'HABEN')) {
            return self::SEPA_CT_SINGLE_CREDIT;
        }

        // =====================================================================
        // Dauerauftrag / Standing Order
        // Debit: 117 - Dauerauftrag vom Konto
        // Credit: 152 - Dauerauftrag auf das Konto
        // =====================================================================
        if (self::containsStandingOrderKeyword($text)) {
            return $isDebit ? self::SEPA_CT_STANDING_ORDER_DEBIT : self::SEPA_STANDING_ORDER_CREDIT;
        }

        // =====================================================================
        // Echtzeit-/Instant-Überweisung (ohne explizite Überweisung)
        // =====================================================================
        if (self::containsInstantKeyword($text)) {
            return $isDebit ? self::SEPA_CT_INSTANT_SINGLE_DEBIT : self::SEPA_CT_INSTANT_CREDIT;
        }

        // =====================================================================
        // Gehalt/Lohn/Salary - immer Credit
        // =====================================================================
        if (
            str_contains($text, 'GEHALT') || str_contains($text, 'LOHN') ||
            str_contains($text, 'SALARY') || str_contains($text, 'WAGES')
        ) {
            return self::SEPA_CT_SINGLE_SALARY;
        }

        // =====================================================================
        // VWL (Vermögenswirksame Leistungen)
        // =====================================================================
        if (str_contains($text, 'VWL') || str_contains($text, 'VERMÖGENSW') || str_contains($text, 'VERMOEGENSW')) {
            return self::SEPA_CT_SINGLE_VWL;
        }

        // =====================================================================
        // Kartenzahlung/Card Payment
        // =====================================================================
        if (self::containsCardPaymentKeyword($text)) {
            return self::SEPA_CARD_CLEARING_SINGLE;
        }

        // =====================================================================
        // Bargeld / Cash
        // =====================================================================
        if (
            str_contains($text, 'EINZAHLUNG') || str_contains($text, 'BAREINZAHLUNG') ||
            str_contains($text, 'CASH DEPOSIT') || str_contains($text, 'DEPOSIT')
        ) {
            return self::CASH_DEPOSIT;
        }
        if (
            str_contains($text, 'AUSZAHLUNG') || str_contains($text, 'BARAUSZAHLUNG') ||
            str_contains($text, 'GELDAUTOMAT') || str_contains($text, 'ATM') ||
            str_contains($text, 'CASH WITHDRAWAL') || str_contains($text, 'WITHDRAWAL')
        ) {
            return self::CASH_WITHDRAWAL;
        }

        return null;
    }

    /**
     * Checks if text contains Direct Debit keywords (DE/EN).
     */
    private static function containsDirectDebitKeyword(string $text): bool {
        return str_contains($text, 'LASTSCHRIFT') ||
            str_contains($text, 'DIRECT DEBIT') ||
            str_contains($text, 'DIRECTDEBIT') ||
            str_contains($text, 'EINZUG') ||
            str_contains($text, 'ABBUCHUNG');
    }

    /**
     * Checks if text contains Credit Transfer / Überweisung keywords (DE/EN).
     */
    private static function containsCreditTransferKeyword(string $text): bool {
        return str_contains($text, 'ÜBERWEISUNG') ||
            str_contains($text, 'UEBERWEISUNG') ||
            str_contains($text, 'UBERWEISUNG') ||  // ü→u ohne e
            str_contains($text, 'CREDIT TRANSFER') ||
            str_contains($text, 'CREDITTRANSFER') ||
            str_contains($text, 'BANK TRANSFER') ||
            str_contains($text, 'BANKTRANSFER') ||
            str_contains($text, 'WIRE TRANSFER');
    }

    /**
     * Checks if text contains B2B keywords.
     */
    private static function containsB2BKeyword(string $text): bool {
        return str_contains($text, 'B2B') ||
            str_contains($text, 'BUSINESS') ||
            str_contains($text, 'FIRMEN');
    }

    /**
     * Checks if text contains Instant/Echtzeit keywords.
     */
    private static function containsInstantKeyword(string $text): bool {
        return str_contains($text, 'ECHTZEIT') ||
            str_contains($text, 'INSTANT') ||
            str_contains($text, 'REAL-TIME') ||
            str_contains($text, 'REALTIME') ||
            str_contains($text, 'SCT INST');
    }

    /**
     * Checks if text contains Standing Order keywords.
     */
    private static function containsStandingOrderKeyword(string $text): bool {
        return str_contains($text, 'DAUERAUFTRAG') ||
            str_contains($text, 'DAUER-AUFTRAG') ||
            str_contains($text, 'STANDING ORDER') ||
            str_contains($text, 'STANDINGORDER') ||
            str_contains($text, 'RECURRING');
    }

    /**
     * Checks if text contains Return/Retoure keywords.
     */
    private static function containsReturnKeyword(string $text): bool {
        return str_contains($text, 'RETOURE') ||
            str_contains($text, 'RÜCKGABE') ||
            str_contains($text, 'RUECKGABE') ||
            str_contains($text, 'RUCKGABE') ||  // ü→u ohne e
            str_contains($text, 'RETURN') ||
            str_contains($text, 'REVERSAL') ||
            str_contains($text, 'STORNO') ||
            str_contains($text, 'RÜCKLAST') ||
            str_contains($text, 'RUECKLAST') ||
            str_contains($text, 'RUCKLAST');  // Rücklastschrift
    }

    /**
     * Checks if text contains Card Payment keywords.
     */
    private static function containsCardPaymentKeyword(string $text): bool {
        return str_contains($text, 'KARTENZAHLUNG') ||
            str_contains($text, 'KARTEN-ZAHLUNG') ||
            str_contains($text, 'EC-ZAHLUNG') ||
            str_contains($text, 'CARD PAYMENT') ||
            str_contains($text, 'CARDPAYMENT') ||
            str_contains($text, 'CARD CLEARING') ||
            str_contains($text, 'DEBIT CARD') ||
            str_contains($text, 'CREDIT CARD') ||
            str_contains($text, 'POS ') ||
            str_contains($text, ' POS');
    }

    /**
     * Tries to derive the GVC code from ISO 20022 CAMT transaction codes.
     *
     * Maps the ISO 20022 BankTransactionCode structure (Domain/Family/SubFamily)
     * to the German GVC codes used in MT940.
     *
     * Common mappings:
     * - PMNT/RCDT/ESCT (SEPA Credit Transfer received) → 166 (Credit)
     * - PMNT/ICDT/ESCT (SEPA Credit Transfer issued) → 116 (Debit)
     * - PMNT/RDDT/ESDD (SEPA Direct Debit received) → 105 (Debit)
     * - PMNT/IDDT/ESDD (SEPA Direct Debit issued) → 171 (Credit)
     * - PMNT/RCDT/STDO (Standing Order received) → 152 (Credit)
     * - PMNT/ICDT/STDO (Standing Order issued) → 117 (Debit)
     *
     * @param TransactionDomain|string|null $domain ISO 20022 Domain (e.g., PMNT, CAMT)
     * @param TransactionFamily|string|null $family ISO 20022 Family (e.g., RCDT, ICDT, RDDT, IDDT)
     * @param TransactionSubFamily|string|null $subFamily ISO 20022 SubFamily (e.g., ESCT, ESDD, BBDD)
     * @param CreditDebit $creditDebit The credit/debit indicator
     * @param bool $isReturn True if this is a return/reversal transaction
     * @return self|null The matching GVC code or null
     */
    public static function tryFromCamtCodes(
        TransactionDomain|string|null $domain,
        TransactionFamily|string|null $family,
        TransactionSubFamily|string|null $subFamily,
        CreditDebit $creditDebit,
        bool $isReturn = false
    ): ?self {
        // Convert to string values
        $domainStr = $domain instanceof TransactionDomain ? $domain->value : ($domain ?? '');
        $familyStr = $family instanceof TransactionFamily ? $family->value : ($family ?? '');
        $subFamilyStr = $subFamily instanceof TransactionSubFamily ? $subFamily->value : ($subFamily ?? '');

        $isDebit = $creditDebit === CreditDebit::DEBIT;

        // Only process PMNT (Payments) domain
        if ($domainStr !== 'PMNT') {
            return null;
        }

        // =====================================================================
        // Return/Reversal transactions
        // =====================================================================
        if ($isReturn || in_array($subFamilyStr, ['RRTN', 'PRDD', 'UPDD', 'ARET', 'AREV'], true)) {
            // RRTN = Return of Credit Transfer, PRDD/UPDD = Return of Direct Debit
            if (in_array($subFamilyStr, ['PRDD', 'UPDD'], true)) {
                // Direct Debit Return
                return $isDebit ? self::SEPA_DD_RETURN_CORE : self::SEPA_DD_RETURN_CORE;
            }
            // Credit Transfer Return
            return $isDebit ? self::SEPA_DD_RETURN_CORE : self::SEPA_CT_RETURN;
        }

        // =====================================================================
        // SEPA Credit Transfer (ESCT)
        // =====================================================================
        if ($subFamilyStr === 'ESCT') {
            // RCDT = Received Credit Transfer → Credit (Geld kommt)
            // ICDT = Issued Credit Transfer → Debit (Geld geht)
            return $isDebit ? self::SEPA_CT_SINGLE_DEBIT : self::SEPA_CT_SINGLE_CREDIT;
        }

        // =====================================================================
        // SEPA Instant Credit Transfer (INST)
        // =====================================================================
        if (in_array($subFamilyStr, ['INST', 'ESCI'], true)) {
            return $isDebit ? self::SEPA_CT_INSTANT_SINGLE_DEBIT : self::SEPA_CT_INSTANT_CREDIT;
        }

        // =====================================================================
        // SEPA Core Direct Debit (ESDD)
        // =====================================================================
        if ($subFamilyStr === 'ESDD') {
            // RDDT = Received Direct Debit → Debit (Geld geht durch Lastschrift)
            // IDDT = Issued Direct Debit → Credit (Lastschrifteinzug kommt)
            return $isDebit ? self::SEPA_DD_SINGLE_CORE : self::SEPA_DD_COLLECTION_CORE;
        }

        // =====================================================================
        // SEPA B2B Direct Debit (BBDD)
        // =====================================================================
        if ($subFamilyStr === 'BBDD') {
            return $isDebit ? self::SEPA_DD_SINGLE_B2B : self::SEPA_DD_COLLECTION_B2B;
        }

        // =====================================================================
        // Standing Order (STDO)
        // =====================================================================
        if ($subFamilyStr === 'STDO') {
            return $isDebit ? self::SEPA_CT_STANDING_ORDER_DEBIT : self::SEPA_STANDING_ORDER_CREDIT;
        }

        // =====================================================================
        // Salary/Wages (SALA)
        // =====================================================================
        if ($subFamilyStr === 'SALA') {
            return self::SEPA_CT_SINGLE_SALARY;
        }

        // =====================================================================
        // Card Payments (CCRD family)
        // =====================================================================
        if ($familyStr === 'CCRD') {
            return self::SEPA_CARD_CLEARING_SINGLE;
        }

        // =====================================================================
        // Counter/Cash transactions (CNTR family)
        // =====================================================================
        if ($familyStr === 'CNTR') {
            if (in_array($subFamilyStr, ['BCDP', 'CDPT'], true)) {
                return self::CASH_DEPOSIT;
            }
            if (in_array($subFamilyStr, ['BCWD', 'CWDL', 'ATMW'], true)) {
                return self::CASH_WITHDRAWAL;
            }
        }

        // =====================================================================
        // Domestic Credit Transfer (DMCT)
        // =====================================================================
        if ($subFamilyStr === 'DMCT') {
            return $isDebit ? self::SEPA_CT_SINGLE_DEBIT : self::SEPA_CT_SINGLE_CREDIT;
        }

        // =====================================================================
        // Financial Institution transfers (FICT/FIDD)
        // =====================================================================
        if ($subFamilyStr === 'FICT') {
            return $isDebit ? self::SEPA_CT_SINGLE_DEBIT : self::SEPA_CT_SINGLE_CREDIT;
        }
        if ($subFamilyStr === 'FIDD') {
            return $isDebit ? self::SEPA_DD_SINGLE_CORE : self::SEPA_DD_COLLECTION_CORE;
        }

        // =====================================================================
        // Automatic Transfer (AUTT)
        // =====================================================================
        if ($subFamilyStr === 'AUTT') {
            return $isDebit ? self::SEPA_CT_STANDING_ORDER_DEBIT : self::SEPA_STANDING_ORDER_CREDIT;
        }

        // =====================================================================
        // Generic mapping based on Family only
        // =====================================================================
        return match ($familyStr) {
            'RCDT', 'ICDT' => $isDebit ? self::SEPA_CT_SINGLE_DEBIT : self::SEPA_CT_SINGLE_CREDIT,
            'RDDT', 'IDDT' => $isDebit ? self::SEPA_DD_SINGLE_CORE : self::SEPA_DD_COLLECTION_CORE,
            default => null,
        };
    }

    /**
     * Returns the ISO 20022 BankTransactionCode components for this GVC code.
     *
     * This is the reverse mapping of tryFromCamtCodes().
     * Used when converting from MT940/DATEV to CAMT format.
     *
     * @param CreditDebit $creditDebit The credit/debit indicator
     * @return array{domain: TransactionDomain|null, family: TransactionFamily|null, subFamily: TransactionSubFamily|null}
     */
    public function toCamtCodes(CreditDebit $creditDebit): array {
        $isDebit = $creditDebit === CreditDebit::DEBIT;

        // Default: Payments domain
        $domain = TransactionDomain::PMNT;

        // Determine Family based on credit/debit and transaction type
        $family = match (true) {
            $this->isSepa() && $this->isDirectDebit() => $isDebit ? TransactionFamily::RDDT : TransactionFamily::IDDT,
            $this->isSepa() => $isDebit ? TransactionFamily::ICDT : TransactionFamily::RCDT,
            default => $isDebit ? TransactionFamily::ICDT : TransactionFamily::RCDT,
        };

        // Determine SubFamily based on specific GVC code
        $subFamily = match ($this) {
            // SEPA Credit Transfer
            self::SEPA_CT_SINGLE_CREDIT,
            self::SEPA_CT_SINGLE_DEBIT,
            self::SEPA_CT_SINGLE_SALARY,
            self::SEPA_CT_SINGLE_VWL,
            self::SEPA_CT_SINGLE_PUBLIC => TransactionSubFamily::ESCT,

            // SEPA Instant
            self::SEPA_CT_INSTANT_CREDIT,
            self::SEPA_CT_INSTANT_SINGLE_DEBIT => TransactionSubFamily::tryFrom('INST') ?? TransactionSubFamily::ESCT,

            // SEPA Standing Order
            self::SEPA_CT_STANDING_ORDER_DEBIT,
            self::SEPA_STANDING_ORDER_CREDIT => TransactionSubFamily::STDO,

            // SEPA Direct Debit Core
            self::SEPA_DD_SINGLE_CORE,
            self::SEPA_DD_COLLECTION_CORE => TransactionSubFamily::ESDD,

            // SEPA Direct Debit B2B
            self::SEPA_DD_SINGLE_B2B,
            self::SEPA_DD_COLLECTION_B2B => TransactionSubFamily::BBDD,

            // Returns
            self::SEPA_CT_RETURN => TransactionSubFamily::RRTN,
            self::SEPA_DD_RETURN_CORE,
            self::SEPA_DD_RETURN_B2B => TransactionSubFamily::UPDD,

            // Card payments (various types)
            self::SEPA_CARD_CLEARING_SINGLE,
            self::SEPA_CARD_CLEARING_RETURN,
            self::SEPA_CARD_CLEARING_REFUND,
            self::SEPA_CARD_CLEARING_BATCH_DEBIT,
            self::SEPA_CARD_CLEARING_BATCH_CREDIT,
            self::SEPA_CARD_CLEARING_REVERSAL => null, // Will set family to CCRD below

            // Cash transactions
            self::CASH_DEPOSIT => TransactionSubFamily::tryFrom('BCDP') ?? TransactionSubFamily::tryFrom('CDPT'),
            self::CASH_WITHDRAWAL => TransactionSubFamily::tryFrom('BCWD') ?? TransactionSubFamily::tryFrom('CWDL'),

            // Default
            default => TransactionSubFamily::tryFrom('OTHR'),
        };

        // Special handling for card payments
        if (in_array($this, [
            self::SEPA_CARD_CLEARING_SINGLE,
            self::SEPA_CARD_CLEARING_RETURN,
            self::SEPA_CARD_CLEARING_REFUND,
            self::SEPA_CARD_CLEARING_BATCH_DEBIT,
            self::SEPA_CARD_CLEARING_BATCH_CREDIT,
            self::SEPA_CARD_CLEARING_REVERSAL,
        ], true)) {
            $family = TransactionFamily::CCRD;
            $subFamily = TransactionSubFamily::tryFrom('POSD') ?? TransactionSubFamily::tryFrom('CWDL');
        }

        // Special handling for cash transactions
        if (in_array($this, [self::CASH_DEPOSIT, self::CASH_WITHDRAWAL], true)) {
            $family = TransactionFamily::CNTR;
        }

        return [
            'domain' => $domain,
            'family' => $family,
            'subFamily' => $subFamily,
        ];
    }

    /**
     * Checks if this GVC code represents a Direct Debit transaction.
     */
    public function isDirectDebit(): bool {
        $code = (int) $this->value;
        // GVC codes 104-109 (DD received) and 171-179 (DD collection) are Direct Debit
        return ($code >= 104 && $code <= 109) || ($code >= 171 && $code <= 179);
    }
}
