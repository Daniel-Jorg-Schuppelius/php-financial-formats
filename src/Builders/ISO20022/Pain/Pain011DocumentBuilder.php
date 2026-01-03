<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain011DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\CancellationReason;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\MandateCancellation;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for pain.011 Documents (Mandate Cancellation Request).
 * 
 * Creates requests for cancellation/termination of SEPA direct debit mandates.
 * 
 * Verwendung:
 * ```php
 * $document = Pain011DocumentBuilder::create('MSG-001', 'Firma GmbH')
 *     ->addCancellation('MNDT-001', CancellationReason::customerRequest())
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain011DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    /** @var MandateCancellation[] */
    private array $mandateCancellations = [];

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
     * Adds a mandate cancellation.
     */
    public function addCancellation(string $mandateId, CancellationReason $reason): self {
        $clone = clone $this;
        $clone->mandateCancellations[] = MandateCancellation::create($mandateId, $reason);
        return $clone;
    }

    /**
     * Adds a mandate cancellation with original mandate.
     */
    public function addCancellationWithMandate(Mandate $originalMandate, CancellationReason $reason): self {
        $clone = clone $this;
        $clone->mandateCancellations[] = MandateCancellation::withOriginal($originalMandate, $reason);
        return $clone;
    }

    /**
     * Adds a completed MandateCancellation.
     */
    public function addMandateCancellation(MandateCancellation $cancellation): self {
        $clone = clone $this;
        $clone->mandateCancellations[] = $cancellation;
        return $clone;
    }

    /**
     * Adds multiple mandate cancellations.
     * 
     * @param MandateCancellation[] $cancellations
     */
    public function addMandateCancellations(array $cancellations): self {
        $clone = clone $this;
        $clone->mandateCancellations = array_merge($clone->mandateCancellations, $cancellations);
        return $clone;
    }

    /**
     * Erstellt das pain.011 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Cancellations vorhanden
     */
    public function build(): Document {
        if (empty($this->mandateCancellations)) {
            throw new InvalidArgumentException('Mindestens eine Mandatsstornierung erforderlich');
        }

        return new Document(
            messageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            initiatingParty: $this->initiatingParty,
            mandateCancellations: $this->mandateCancellations
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Stornierung auf Kundenanfrage.
     */
    public static function createCustomerRequest(
        string $messageId,
        string $initiatorName,
        string $mandateId
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addCancellation($mandateId, CancellationReason::customerRequest())
            ->build();
    }

    /**
     * Erstellt eine Stornierung wegen geschlossenem Konto.
     */
    public static function createAccountClosed(
        string $messageId,
        string $initiatorName,
        string $mandateId
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addCancellation($mandateId, CancellationReason::accountClosed())
            ->build();
    }

    /**
     * Erstellt eine Stornierung wegen Versterbens des Schuldners.
     */
    public static function createDebtorDeceased(
        string $messageId,
        string $initiatorName,
        string $mandateId
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addCancellation($mandateId, CancellationReason::debtorDeceased())
            ->build();
    }

    /**
     * Erstellt eine Stornierung wegen Betrugsverdachts.
     */
    public static function createFraudulent(
        string $messageId,
        string $initiatorName,
        string $mandateId
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addCancellation($mandateId, CancellationReason::fraudulent())
            ->build();
    }

    /**
     * Erstellt mehrere Stornierungen mit dem gleichen Grund.
     * 
     * @param string[] $mandateIds
     */
    public static function createBulkCancellation(
        string $messageId,
        string $initiatorName,
        array $mandateIds,
        CancellationReason $reason
    ): Document {
        $builder = self::create($messageId, $initiatorName);
        foreach ($mandateIds as $mandateId) {
            $builder = $builder->addCancellation($mandateId, $reason);
        }
        return $builder->build();
    }
}
