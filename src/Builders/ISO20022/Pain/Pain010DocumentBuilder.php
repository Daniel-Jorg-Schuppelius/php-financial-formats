<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain010DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\AmendmentDetails;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\MandateAmendment;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für pain.010 Documents (Mandate Amendment Request).
 * 
 * Erstellt Anfragen zur Änderung bestehender SEPA-Lastschrift-Mandate.
 * 
 * Verwendung:
 * ```php
 * $document = Pain010DocumentBuilder::create('MSG-001', 'Firma GmbH')
 *     ->addAmendment(
 *         Mandate::sepaCore(...),
 *         AmendmentDetails::mandateIdChange('OLD-MNDT-001')
 *     )
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain010DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    /** @var MandateAmendment[] */
    private array $mandateAmendments = [];

    private function __construct(string $messageId, PartyIdentification $initiatingParty) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId darf maximal 35 Zeichen lang sein');
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
     * Erzeugt neuen Builder mit vollständiger PartyIdentification.
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
     * Fügt eine Mandatsänderung hinzu.
     */
    public function addAmendment(Mandate $mandate, AmendmentDetails $details): self {
        $clone = clone $this;
        $clone->mandateAmendments[] = MandateAmendment::create($mandate, $details);
        return $clone;
    }

    /**
     * Fügt eine fertige MandateAmendment hinzu.
     */
    public function addMandateAmendment(MandateAmendment $amendment): self {
        $clone = clone $this;
        $clone->mandateAmendments[] = $amendment;
        return $clone;
    }

    /**
     * Fügt mehrere Mandatsänderungen hinzu.
     * 
     * @param MandateAmendment[] $amendments
     */
    public function addMandateAmendments(array $amendments): self {
        $clone = clone $this;
        $clone->mandateAmendments = array_merge($clone->mandateAmendments, $amendments);
        return $clone;
    }

    /**
     * Erstellt das pain.010 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Amendments vorhanden
     */
    public function build(): Document {
        if (empty($this->mandateAmendments)) {
            throw new InvalidArgumentException('Mindestens eine Mandatsänderung erforderlich');
        }

        return new Document(
            messageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            initiatingParty: $this->initiatingParty,
            mandateAmendments: $this->mandateAmendments
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Mandats-ID-Änderung.
     */
    public static function createMandateIdChange(
        string $messageId,
        string $initiatorName,
        Mandate $newMandate,
        string $originalMandateId
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addAmendment($newMandate, AmendmentDetails::mandateIdChange($originalMandateId))
            ->build();
    }

    /**
     * Erstellt eine Gläubiger-ID-Änderung.
     */
    public static function createCreditorSchemeIdChange(
        string $messageId,
        string $initiatorName,
        Mandate $newMandate,
        string $originalCreditorSchemeId
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addAmendment($newMandate, AmendmentDetails::creditorSchemeIdChange($originalCreditorSchemeId))
            ->build();
    }

    /**
     * Erstellt eine Schuldner-Kontoänderung.
     */
    public static function createDebtorAccountChange(
        string $messageId,
        string $initiatorName,
        Mandate $newMandate,
        string $originalDebtorIban,
        ?string $originalDebtorBic = null
    ): Document {
        $originalAccount = new AccountIdentification(iban: $originalDebtorIban);
        $originalAgent = $originalDebtorBic !== null
            ? new FinancialInstitution(bic: $originalDebtorBic)
            : null;

        return self::create($messageId, $initiatorName)
            ->addAmendment(
                $newMandate,
                AmendmentDetails::debtorAccountChange($originalAccount, $originalAgent)
            )
            ->build();
    }
}