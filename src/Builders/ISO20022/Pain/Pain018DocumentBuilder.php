<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain018DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18\MandateSuspensionRequest;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for pain.018 Documents (Mandate Suspension Request).
 * 
 * Creates requests for temporary suspension of a mandate.
 * 
 * Verwendung:
 * ```php
 * $document = Pain018DocumentBuilder::create('MSG-001', 'Firma GmbH')
 *     ->addSuspension(
 *         'MNDT-001',
 *         new DateTimeImmutable('2024-06-01'),
 *         new DateTimeImmutable('2024-08-31'),
 *         'Urlaubspause'
 *     )
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain018DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    /** @var MandateSuspensionRequest[] */
    private array $suspensionRequests = [];

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
     * Adds a mandate suspension.
     */
    public function addSuspension(
        string $mandateId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $reason = null
    ): self {
        $clone = clone $this;
        $clone->suspensionRequests[] = MandateSuspensionRequest::create(
            $mandateId,
            $startDate,
            $endDate,
            $reason
        );
        return $clone;
    }

    /**
     * Adds an unlimited mandate suspension.
     */
    public function addIndefiniteSuspension(
        string $mandateId,
        DateTimeImmutable $startDate,
        ?string $reason = null
    ): self {
        $clone = clone $this;
        $clone->suspensionRequests[] = MandateSuspensionRequest::indefinite(
            $mandateId,
            $startDate,
            $reason
        );
        return $clone;
    }

    /**
     * Adds a completed MandateSuspensionRequest.
     */
    public function addMandateSuspensionRequest(MandateSuspensionRequest $request): self {
        $clone = clone $this;
        $clone->suspensionRequests[] = $request;
        return $clone;
    }

    /**
     * Adds multiple mandate suspensions.
     * 
     * @param MandateSuspensionRequest[] $requests
     */
    public function addMandateSuspensionRequests(array $requests): self {
        $clone = clone $this;
        $clone->suspensionRequests = array_merge($clone->suspensionRequests, $requests);
        return $clone;
    }

    /**
     * Erstellt das pain.018 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Requests vorhanden
     */
    public function build(): Document {
        if (empty($this->suspensionRequests)) {
            throw new InvalidArgumentException('Mindestens eine Mandatsaussetzung erforderlich');
        }

        return Document::create(
            messageId: $this->messageId,
            initiatingParty: $this->initiatingParty,
            suspensionRequests: $this->suspensionRequests
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache befristete Aussetzung.
     */
    public static function createTemporarySuspension(
        string $messageId,
        string $initiatorName,
        string $mandateId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $reason = null
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addSuspension($mandateId, $startDate, $endDate, $reason)
            ->build();
    }

    /**
     * Erstellt eine unbegrenzte Aussetzung.
     */
    public static function createIndefiniteSuspension(
        string $messageId,
        string $initiatorName,
        string $mandateId,
        DateTimeImmutable $startDate,
        ?string $reason = null
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addIndefiniteSuspension($mandateId, $startDate, $reason)
            ->build();
    }

    /**
     * Creates suspensions for multiple mandates with the same period.
     * 
     * @param string[] $mandateIds
     */
    public static function createBulkSuspension(
        string $messageId,
        string $initiatorName,
        array $mandateIds,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $reason = null
    ): Document {
        $builder = self::create($messageId, $initiatorName);

        foreach ($mandateIds as $mandateId) {
            $builder = $builder->addSuspension($mandateId, $startDate, $endDate, $reason);
        }

        return $builder->build();
    }
}
