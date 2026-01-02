<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain012DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12\MandateAcceptance;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für pain.012 Documents (Mandate Acceptance Report).
 * 
 * Erstellt Bestätigungen/Ablehnungen für Mandatsanfragen.
 * Wird typischerweise von Banken als Antwort auf pain.009/010/011 generiert.
 * 
 * Verwendung:
 * ```php
 * $document = Pain012DocumentBuilder::forPain009('MSG-001', 'ORIG-MSG-001')
 *     ->addAccepted('MNDT-001')
 *     ->addRejected('MNDT-002', 'Invalid debtor account')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain012DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private string $originalMessageId;
    private string $originalMessageNameId;
    private ?PartyIdentification $initiatingParty = null;
    /** @var MandateAcceptance[] */
    private array $mandateAcceptances = [];

    private function __construct(string $messageId, string $originalMessageId, string $originalMessageNameId) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId darf maximal 35 Zeichen lang sein');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
        $this->originalMessageId = $originalMessageId;
        $this->originalMessageNameId = $originalMessageNameId;
    }

    /**
     * Erzeugt Builder für pain.009 Antwort (Mandate Initiation).
     */
    public static function forPain009(string $messageId, string $originalMessageId): self {
        return new self($messageId, $originalMessageId, 'pain.009.001.08');
    }

    /**
     * Erzeugt Builder für pain.010 Antwort (Mandate Amendment).
     */
    public static function forPain010(string $messageId, string $originalMessageId): self {
        return new self($messageId, $originalMessageId, 'pain.010.001.08');
    }

    /**
     * Erzeugt Builder für pain.011 Antwort (Mandate Cancellation).
     */
    public static function forPain011(string $messageId, string $originalMessageId): self {
        return new self($messageId, $originalMessageId, 'pain.011.001.08');
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
     * Setzt die initiierende Partei (optional).
     */
    public function withInitiatingParty(PartyIdentification $party): self {
        $clone = clone $this;
        $clone->initiatingParty = $party;
        return $clone;
    }

    /**
     * Fügt eine Mandat-Annahme hinzu.
     */
    public function addAccepted(string $mandateId, ?Mandate $mandate = null): self {
        $clone = clone $this;
        $clone->mandateAcceptances[] = MandateAcceptance::accepted($mandateId, $mandate);
        return $clone;
    }

    /**
     * Fügt eine Mandat-Ablehnung hinzu.
     */
    public function addRejected(string $mandateId, string $rejectReason): self {
        $clone = clone $this;
        $clone->mandateAcceptances[] = MandateAcceptance::rejected($mandateId, $rejectReason);
        return $clone;
    }

    /**
     * Fügt eine fertige MandateAcceptance hinzu.
     */
    public function addMandateAcceptance(MandateAcceptance $acceptance): self {
        $clone = clone $this;
        $clone->mandateAcceptances[] = $acceptance;
        return $clone;
    }

    /**
     * Fügt mehrere Mandate-Annahmen/Ablehnungen hinzu.
     * 
     * @param MandateAcceptance[] $acceptances
     */
    public function addMandateAcceptances(array $acceptances): self {
        $clone = clone $this;
        $clone->mandateAcceptances = array_merge($clone->mandateAcceptances, $acceptances);
        return $clone;
    }

    /**
     * Erstellt das pain.012 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Acceptances vorhanden
     */
    public function build(): Document {
        if (empty($this->mandateAcceptances)) {
            throw new InvalidArgumentException('Mindestens eine Mandatsannahme/-ablehnung erforderlich');
        }

        return Document::create(
            messageId: $this->messageId,
            originalMessageId: $this->originalMessageId,
            originalMessageNameId: $this->originalMessageNameId,
            mandateAcceptances: $this->mandateAcceptances,
            initiatingParty: $this->initiatingParty
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Annahme für ein Mandat.
     */
    public static function createSingleAcceptance(
        string $messageId,
        string $originalMessageId,
        string $originalMessageNameId,
        string $mandateId
    ): Document {
        $builder = match ($originalMessageNameId) {
            'pain.009.001.08' => self::forPain009($messageId, $originalMessageId),
            'pain.010.001.08' => self::forPain010($messageId, $originalMessageId),
            'pain.011.001.08' => self::forPain011($messageId, $originalMessageId),
            default => throw new InvalidArgumentException("Unbekannter Nachrichtentyp: $originalMessageNameId")
        };

        return $builder->addAccepted($mandateId)->build();
    }

    /**
     * Erstellt eine einfache Ablehnung für ein Mandat.
     */
    public static function createSingleRejection(
        string $messageId,
        string $originalMessageId,
        string $originalMessageNameId,
        string $mandateId,
        string $rejectReason
    ): Document {
        $builder = match ($originalMessageNameId) {
            'pain.009.001.08' => self::forPain009($messageId, $originalMessageId),
            'pain.010.001.08' => self::forPain010($messageId, $originalMessageId),
            'pain.011.001.08' => self::forPain011($messageId, $originalMessageId),
            default => throw new InvalidArgumentException("Unbekannter Nachrichtentyp: $originalMessageNameId")
        };

        return $builder->addRejected($mandateId, $rejectReason)->build();
    }

    /**
     * Akzeptiert alle Mandate in einer Liste.
     * 
     * @param string[] $mandateIds
     */
    public static function acceptAll(
        string $messageId,
        string $originalMessageId,
        string $originalMessageNameId,
        array $mandateIds
    ): Document {
        $builder = match ($originalMessageNameId) {
            'pain.009.001.08' => self::forPain009($messageId, $originalMessageId),
            'pain.010.001.08' => self::forPain010($messageId, $originalMessageId),
            'pain.011.001.08' => self::forPain011($messageId, $originalMessageId),
            default => throw new InvalidArgumentException("Unbekannter Nachrichtentyp: $originalMessageNameId")
        };

        foreach ($mandateIds as $mandateId) {
            $builder = $builder->addAccepted($mandateId);
        }

        return $builder->build();
    }
}
