<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt103DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type103\Document;
use CommonToolkit\FinancialFormats\Enums\BankOperationCode;
use CommonToolkit\FinancialFormats\Enums\ChargesCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für MT103 Single Customer Credit Transfer.
 * 
 * Erstellt Einzelüberweisungen gemäß SWIFT-Standard. Der häufigste
 * Nachrichtentyp für Kundenzahlungen im internationalen Zahlungsverkehr.
 * 
 * Verwendung:
 * ```php
 * $document = Mt103DocumentBuilder::create('REF-001')
 *     ->orderingCustomer('DE89370400440532013000', 'Firma GmbH')
 *     ->beneficiary('DE91100000000123456789', 'Max Mustermann')
 *     ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
 *     ->remittanceInfo('Rechnung 2024-001')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Mt
 */
final class Mt103DocumentBuilder {
    private string $sendersReference;
    private BankOperationCode $bankOperationCode;
    private ?TransferDetails $transferDetails = null;
    private ?Party $orderingCustomer = null;
    private ?Party $beneficiary = null;
    private ?ChargesCode $chargesCode = null;
    private ?string $remittanceInfo = null;
    private ?Party $orderingInstitution = null;
    private ?Party $sendersCorrespondent = null;
    private ?Party $intermediaryInstitution = null;
    private ?Party $accountWithInstitution = null;
    private ?string $senderToReceiverInfo = null;
    private ?string $regulatoryReporting = null;
    private ?string $transactionTypeCode = null;
    private ?DateTimeImmutable $creationDateTime = null;

    private function __construct(string $sendersReference) {
        if (strlen($sendersReference) > 16) {
            throw new InvalidArgumentException('Sender\'s Reference darf maximal 16 Zeichen lang sein');
        }
        $this->sendersReference = $sendersReference;
        $this->bankOperationCode = BankOperationCode::CRED;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Erzeugt neuen Builder mit Sender's Reference.
     */
    public static function create(string $sendersReference): self {
        return new self($sendersReference);
    }

    /**
     * Setzt den Bank Operation Code (Feld :23B:).
     */
    public function bankOperationCode(BankOperationCode $code): self {
        $clone = clone $this;
        $clone->bankOperationCode = $code;
        return $clone;
    }

    /**
     * Setzt Betrag, Währung und Valutadatum (Feld :32A:).
     */
    public function amount(float $amount, CurrencyCode $currency, DateTimeImmutable $valueDate): self {
        $clone = clone $this;
        $clone->transferDetails = new TransferDetails(
            valueDate: $valueDate,
            currency: $currency,
            amount: $amount
        );
        return $clone;
    }

    /**
     * Setzt Betrag mit Währungsumrechnung (Felder :32A:, :33B:, :36:).
     */
    public function amountWithConversion(
        float $amount,
        CurrencyCode $currency,
        DateTimeImmutable $valueDate,
        float $originalAmount,
        CurrencyCode $originalCurrency,
        float $exchangeRate
    ): self {
        $clone = clone $this;
        $clone->transferDetails = new TransferDetails(
            valueDate: $valueDate,
            currency: $currency,
            amount: $amount,
            originalCurrency: $originalCurrency,
            originalAmount: $originalAmount,
            exchangeRate: $exchangeRate
        );
        return $clone;
    }

    /**
     * Setzt die TransferDetails mit vollständigem Objekt.
     */
    public function transferDetails(TransferDetails $details): self {
        $clone = clone $this;
        $clone->transferDetails = $details;
        return $clone;
    }

    /**
     * Setzt den Auftraggeber (Feld :50a:).
     */
    public function orderingCustomer(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $clone = clone $this;
        $clone->orderingCustomer = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $clone;
    }

    /**
     * Setzt den Auftraggeber mit vollständiger Party.
     */
    public function orderingCustomerParty(Party $party): self {
        $clone = clone $this;
        $clone->orderingCustomer = $party;
        return $clone;
    }

    /**
     * Setzt den Begünstigten (Feld :59a:).
     */
    public function beneficiary(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $clone = clone $this;
        $clone->beneficiary = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $clone;
    }

    /**
     * Setzt den Begünstigten mit vollständiger Party.
     */
    public function beneficiaryParty(Party $party): self {
        $clone = clone $this;
        $clone->beneficiary = $party;
        return $clone;
    }

    /**
     * Setzt den Gebührencode (Feld :71A:).
     */
    public function chargesCode(ChargesCode $code): self {
        $clone = clone $this;
        $clone->chargesCode = $code;
        return $clone;
    }

    /**
     * Setzt Gebühren auf SHA (geteilt).
     */
    public function chargesShared(): self {
        return $this->chargesCode(ChargesCode::SHA);
    }

    /**
     * Setzt Gebühren auf OUR (Auftraggeber trägt alle).
     */
    public function chargesOur(): self {
        return $this->chargesCode(ChargesCode::OUR);
    }

    /**
     * Setzt Gebühren auf BEN (Begünstigter trägt alle).
     */
    public function chargesBen(): self {
        return $this->chargesCode(ChargesCode::BEN);
    }

    /**
     * Setzt den Verwendungszweck (Feld :70:).
     */
    public function remittanceInfo(?string $info): self {
        $clone = clone $this;
        $clone->remittanceInfo = $info;
        return $clone;
    }

    /**
     * Setzt die Ordering Institution (Feld :52a:).
     */
    public function orderingInstitution(string $bic, ?string $name = null): self {
        $clone = clone $this;
        $clone->orderingInstitution = new Party(bic: $bic, name: $name);
        return $clone;
    }

    /**
     * Setzt die Ordering Institution mit vollständiger Party.
     */
    public function orderingInstitutionParty(Party $party): self {
        $clone = clone $this;
        $clone->orderingInstitution = $party;
        return $clone;
    }

    /**
     * Setzt den Sender's Correspondent (Feld :53a:).
     */
    public function sendersCorrespondent(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->sendersCorrespondent = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Setzt die Intermediary Institution (Feld :56a:).
     */
    public function intermediaryInstitution(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->intermediaryInstitution = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Setzt die Account With Institution (Feld :57a:).
     */
    public function accountWithInstitution(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->accountWithInstitution = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Setzt die Sender to Receiver Information (Feld :72:).
     */
    public function senderToReceiverInfo(string $info): self {
        $clone = clone $this;
        $clone->senderToReceiverInfo = $info;
        return $clone;
    }

    /**
     * Setzt die Regulatory Reporting Information (Feld :77B:).
     */
    public function regulatoryReporting(string $info): self {
        $clone = clone $this;
        $clone->regulatoryReporting = $info;
        return $clone;
    }

    /**
     * Setzt den Transaction Type Code (Feld :26T:).
     */
    public function transactionTypeCode(string $code): self {
        if (strlen($code) !== 3) {
            throw new InvalidArgumentException('Transaction Type Code muss genau 3 Zeichen haben');
        }
        $clone = clone $this;
        $clone->transactionTypeCode = $code;
        return $clone;
    }

    /**
     * Setzt den Erstellungszeitpunkt.
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Erstellt das MT103 Dokument.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if ($this->transferDetails === null) {
            throw new InvalidArgumentException('TransferDetails (Betrag/Währung/Datum) erforderlich');
        }
        if ($this->orderingCustomer === null) {
            throw new InvalidArgumentException('Ordering Customer (Auftraggeber) ist erforderlich');
        }
        if ($this->beneficiary === null) {
            throw new InvalidArgumentException('Beneficiary (Begünstigter) ist erforderlich');
        }

        return new Document(
            sendersReference: $this->sendersReference,
            transferDetails: $this->transferDetails,
            orderingCustomer: $this->orderingCustomer,
            beneficiary: $this->beneficiary,
            bankOperationCode: $this->bankOperationCode,
            chargesCode: $this->chargesCode,
            remittanceInfo: $this->remittanceInfo,
            orderingInstitution: $this->orderingInstitution,
            sendersCorrespondent: $this->sendersCorrespondent,
            intermediaryInstitution: $this->intermediaryInstitution,
            accountWithInstitution: $this->accountWithInstitution,
            senderToReceiverInfo: $this->senderToReceiverInfo,
            regulatoryReporting: $this->regulatoryReporting,
            transactionTypeCode: $this->transactionTypeCode,
            creationDateTime: $this->creationDateTime
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache EUR-Überweisung.
     */
    public static function createSimple(
        string $sendersReference,
        string $orderingAccount,
        string $orderingName,
        string $beneficiaryAccount,
        string $beneficiaryName,
        float $amount,
        DateTimeImmutable $valueDate,
        ?string $remittanceInfo = null
    ): Document {
        return self::create($sendersReference)
            ->orderingCustomer($orderingAccount, $orderingName)
            ->beneficiary($beneficiaryAccount, $beneficiaryName)
            ->amount($amount, CurrencyCode::Euro, $valueDate)
            ->chargesShared()
            ->remittanceInfo($remittanceInfo)
            ->build();
    }

    /**
     * Erstellt eine internationale Überweisung.
     */
    public static function createInternational(
        string $sendersReference,
        Party $orderingCustomer,
        Party $beneficiary,
        float $amount,
        CurrencyCode $currency,
        DateTimeImmutable $valueDate,
        ChargesCode $charges,
        ?Party $intermediaryInstitution = null,
        ?string $remittanceInfo = null
    ): Document {
        $builder = self::create($sendersReference)
            ->orderingCustomerParty($orderingCustomer)
            ->beneficiaryParty($beneficiary)
            ->amount($amount, $currency, $valueDate)
            ->chargesCode($charges)
            ->remittanceInfo($remittanceInfo);

        if ($intermediaryInstitution !== null) {
            $builder = $builder->intermediaryInstitution(
                $intermediaryInstitution->getBic() ?? '',
                $intermediaryInstitution->getAccount()
            );
        }

        return $builder->build();
    }
}
