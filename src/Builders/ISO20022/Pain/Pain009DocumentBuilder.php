<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain009DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type9\Document;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\SequenceType;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for pain.009 Mandate Initiation Request.
 * 
 * Erstellt Anfragen zur Einrichtung von SEPA-Lastschrift-Mandaten.
 * 
 * Verwendung:
 * ```php
 * $document = Pain009DocumentBuilder::create('MSG-001', 'Firma GmbH')
 *     ->beginCoreMandate('MNDT-001', new DateTimeImmutable('2024-01-15'))
 *         ->creditor('Firma GmbH', 'DE89370400440532013000', 'COBADEFFXXX', 'DE98ZZZ09999999999')
 *         ->debtor('Max Mustermann', 'DE91100000000123456789', 'DEUTDEFF')
 *         ->done()
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Pain
 */
final class Pain009DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    /** @var Mandate[] */
    private array $mandates = [];

    private function __construct(string $messageId, PartyIdentification $initiatingParty) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId must not exceed 35 characters');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
        $this->initiatingParty = $initiatingParty;
    }

    /**
     * Erzeugt neuen Builder mit Message-ID und Initiator-Name.
     */
    public static function create(string $messageId, string $initiatingPartyName): self {
        return new self($messageId, new PartyIdentification(name: $initiatingPartyName));
    }

    /**
     * Creates new builder with complete PartyIdentification.
     */
    public static function createWithParty(string $messageId, PartyIdentification $initiatingParty): self {
        return new self($messageId, $initiatingParty);
    }

    /**
     * Setzt den Erstellungszeitpunkt (Standard: jetzt).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Beginnt ein neues SEPA Core Mandat.
     */
    public function beginCoreMandate(string $mandateId, DateTimeImmutable $dateOfSignature): MandateBuilder {
        return new MandateBuilder($this, $mandateId, $dateOfSignature, LocalInstrument::SEPA_CORE);
    }

    /**
     * Beginnt ein neues SEPA B2B Mandat.
     */
    public function beginB2BMandate(string $mandateId, DateTimeImmutable $dateOfSignature): MandateBuilder {
        return new MandateBuilder($this, $mandateId, $dateOfSignature, LocalInstrument::SEPA_B2B);
    }

    /**
     * Adds a pre-built mandate.
     */
    public function addMandate(Mandate $mandate): self {
        $clone = clone $this;
        $clone->mandates[] = $mandate;
        return $clone;
    }

    /**
     * Adds multiple mandates.
     * 
     * @param Mandate[] $mandates
     */
    public function addMandates(array $mandates): self {
        $clone = clone $this;
        $clone->mandates = array_merge($clone->mandates, $mandates);
        return $clone;
    }

    /**
     * Called by MandateBuilder to add the mandate.
     * @internal
     */
    public function pushMandate(Mandate $mandate): self {
        return $this->addMandate($mandate);
    }

    /**
     * Erstellt das pain.009 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Mandate vorhanden
     */
    public function build(): Document {
        if (empty($this->mandates)) {
            throw new InvalidArgumentException('Mindestens ein Mandat erforderlich');
        }

        return new Document(
            messageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            initiatingParty: $this->initiatingParty,
            mandates: $this->mandates
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt ein einfaches SEPA Core Mandat.
     */
    public static function createCoreMandate(
        string $messageId,
        string $mandateId,
        DateTimeImmutable $dateOfSignature,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        string $debtorName,
        string $debtorIban,
        string $debtorBic
    ): Document {
        return self::create($messageId, $creditorName)
            ->addMandate(Mandate::sepaCore(
                mandateId: $mandateId,
                dateOfSignature: $dateOfSignature,
                creditorName: $creditorName,
                creditorIban: $creditorIban,
                creditorBic: $creditorBic,
                creditorSchemeId: $creditorSchemeId,
                debtorName: $debtorName,
                debtorIban: $debtorIban,
                debtorBic: $debtorBic
            ))
            ->build();
    }

    /**
     * Erstellt ein einfaches SEPA B2B Mandat.
     */
    public static function createB2BMandate(
        string $messageId,
        string $mandateId,
        DateTimeImmutable $dateOfSignature,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        string $debtorName,
        string $debtorIban,
        string $debtorBic
    ): Document {
        return self::create($messageId, $creditorName)
            ->addMandate(Mandate::sepaB2B(
                mandateId: $mandateId,
                dateOfSignature: $dateOfSignature,
                creditorName: $creditorName,
                creditorIban: $creditorIban,
                creditorBic: $creditorBic,
                creditorSchemeId: $creditorSchemeId,
                debtorName: $debtorName,
                debtorIban: $debtorIban,
                debtorBic: $debtorBic
            ))
            ->build();
    }
}

/**
 * Helper builder for individual mandates.
 */
final class MandateBuilder {
    private string $mandateId;
    private DateTimeImmutable $dateOfSignature;
    private LocalInstrument $localInstrument;

    // Creditor
    private ?PartyIdentification $creditor = null;
    private ?AccountIdentification $creditorAccount = null;
    private ?FinancialInstitution $creditorAgent = null;
    private ?string $creditorSchemeId = null;

    // Debtor
    private ?PartyIdentification $debtor = null;
    private ?AccountIdentification $debtorAccount = null;
    private ?FinancialInstitution $debtorAgent = null;

    // Optional fields
    private ?SequenceType $sequenceType = null;
    private ?DateTimeImmutable $firstCollectionDate = null;
    private ?DateTimeImmutable $finalCollectionDate = null;
    private ?float $maxAmount = null;
    private ?string $electronicSignature = null;
    private ?string $mandateReason = null;

    public function __construct(
        private readonly Pain009DocumentBuilder $parent,
        string $mandateId,
        DateTimeImmutable $dateOfSignature,
        LocalInstrument $localInstrument
    ) {
        if (strlen($mandateId) > 35) {
            throw new InvalidArgumentException('MndtId must not exceed 35 characters');
        }
        $this->mandateId = $mandateId;
        $this->dateOfSignature = $dateOfSignature;
        $this->localInstrument = $localInstrument;
    }

    /**
     * Sets the creditor information.
     */
    public function creditor(
        string $name,
        string $iban,
        string $bic,
        string $schemeId
    ): self {
        $clone = clone $this;
        $clone->creditor = new PartyIdentification(name: $name);
        $clone->creditorAccount = new AccountIdentification(iban: $iban);
        $clone->creditorAgent = new FinancialInstitution(bic: $bic);
        $clone->creditorSchemeId = $schemeId;
        return $clone;
    }

    /**
     * Sets the creditor information with complete objects.
     */
    public function creditorFull(
        PartyIdentification $creditor,
        AccountIdentification $account,
        FinancialInstitution $agent,
        string $schemeId
    ): self {
        $clone = clone $this;
        $clone->creditor = $creditor;
        $clone->creditorAccount = $account;
        $clone->creditorAgent = $agent;
        $clone->creditorSchemeId = $schemeId;
        return $clone;
    }

    /**
     * Setzt die Schuldner-Informationen.
     */
    public function debtor(string $name, string $iban, string $bic): self {
        $clone = clone $this;
        $clone->debtor = new PartyIdentification(name: $name);
        $clone->debtorAccount = new AccountIdentification(iban: $iban);
        $clone->debtorAgent = new FinancialInstitution(bic: $bic);
        return $clone;
    }

    /**
     * Sets the debtor information with complete objects.
     */
    public function debtorFull(
        PartyIdentification $debtor,
        AccountIdentification $account,
        FinancialInstitution $agent
    ): self {
        $clone = clone $this;
        $clone->debtor = $debtor;
        $clone->debtorAccount = $account;
        $clone->debtorAgent = $agent;
        return $clone;
    }

    /**
     * Setzt die Sequenzart (FRST, RCUR, OOFF, FNAL).
     */
    public function sequenceType(SequenceType $sequenceType): self {
        $clone = clone $this;
        $clone->sequenceType = $sequenceType;
        return $clone;
    }

    /**
     * Setzt das Datum der ersten Einziehung.
     */
    public function firstCollectionDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->firstCollectionDate = $date;
        return $clone;
    }

    /**
     * Setzt das Datum der letzten Einziehung.
     */
    public function finalCollectionDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->finalCollectionDate = $date;
        return $clone;
    }

    /**
     * Setzt den maximalen Einziehungsbetrag.
     */
    public function maxAmount(float $amount): self {
        $clone = clone $this;
        $clone->maxAmount = $amount;
        return $clone;
    }

    /**
     * Setzt die elektronische Signatur.
     */
    public function electronicSignature(string $signature): self {
        $clone = clone $this;
        $clone->electronicSignature = $signature;
        return $clone;
    }

    /**
     * Setzt den Mandatsgrund.
     */
    public function mandateReason(string $reason): self {
        $clone = clone $this;
        $clone->mandateReason = $reason;
        return $clone;
    }

    /**
     * Ends the mandate and returns to the main builder.
     */
    public function done(): Pain009DocumentBuilder {
        if ($this->creditor === null || $this->creditorAccount === null || $this->creditorAgent === null) {
            throw new InvalidArgumentException('Gläubiger-Informationen erforderlich');
        }
        if ($this->creditorSchemeId === null) {
            throw new InvalidArgumentException('Gläubiger-ID (CreditorSchemeId) erforderlich');
        }
        if ($this->debtor === null || $this->debtorAccount === null || $this->debtorAgent === null) {
            throw new InvalidArgumentException('Schuldner-Informationen erforderlich');
        }

        $mandate = new Mandate(
            mandateId: $this->mandateId,
            dateOfSignature: $this->dateOfSignature,
            creditor: $this->creditor,
            creditorAccount: $this->creditorAccount,
            creditorAgent: $this->creditorAgent,
            debtor: $this->debtor,
            debtorAccount: $this->debtorAccount,
            debtorAgent: $this->debtorAgent,
            creditorSchemeId: $this->creditorSchemeId,
            localInstrument: $this->localInstrument,
            sequenceType: $this->sequenceType,
            finalCollectionDate: $this->finalCollectionDate,
            firstCollectionDate: $this->firstCollectionDate,
            maxAmount: $this->maxAmount,
            electronicSignature: $this->electronicSignature,
            mandateReason: $this->mandateReason
        );

        return $this->parent->pushMandate($mandate);
    }
}
