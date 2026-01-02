<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain017DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17\MandateCopyRequest;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für pain.017 Documents (Mandate Copy Request).
 * 
 * Erstellt Anfragen zur Erstellung einer Kopie eines bestehenden Mandats.
 * 
 * Verwendung:
 * ```php
 * $document = Pain017DocumentBuilder::create('MSG-001', 'Firma GmbH')
 *     ->addCopyRequest('MNDT-001', 'DE98ZZZ09999999999')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain017DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    /** @var MandateCopyRequest[] */
    private array $copyRequests = [];

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
     * Fügt eine Mandatskopie-Anfrage hinzu.
     */
    public function addCopyRequest(string $mandateId, ?string $creditorSchemeId = null): self {
        $clone = clone $this;
        $clone->copyRequests[] = MandateCopyRequest::create($mandateId, $creditorSchemeId);
        return $clone;
    }

    /**
     * Fügt eine fertige MandateCopyRequest hinzu.
     */
    public function addMandateCopyRequest(MandateCopyRequest $request): self {
        $clone = clone $this;
        $clone->copyRequests[] = $request;
        return $clone;
    }

    /**
     * Fügt mehrere Mandatskopie-Anfragen hinzu.
     * 
     * @param MandateCopyRequest[] $requests
     */
    public function addMandateCopyRequests(array $requests): self {
        $clone = clone $this;
        $clone->copyRequests = array_merge($clone->copyRequests, $requests);
        return $clone;
    }

    /**
     * Erstellt das pain.017 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Requests vorhanden
     */
    public function build(): Document {
        if (empty($this->copyRequests)) {
            throw new InvalidArgumentException('Mindestens eine Mandatskopie-Anfrage erforderlich');
        }

        return Document::create(
            messageId: $this->messageId,
            initiatingParty: $this->initiatingParty,
            copyRequests: $this->copyRequests
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Mandatskopie-Anfrage.
     */
    public static function createSingleRequest(
        string $messageId,
        string $initiatorName,
        string $mandateId,
        ?string $creditorSchemeId = null
    ): Document {
        return self::create($messageId, $initiatorName)
            ->addCopyRequest($mandateId, $creditorSchemeId)
            ->build();
    }

    /**
     * Erstellt Kopie-Anfragen für mehrere Mandate.
     * 
     * @param string[] $mandateIds
     */
    public static function createBulkRequest(
        string $messageId,
        string $initiatorName,
        array $mandateIds,
        ?string $creditorSchemeId = null
    ): Document {
        $builder = self::create($messageId, $initiatorName);

        foreach ($mandateIds as $mandateId) {
            $builder = $builder->addCopyRequest($mandateId, $creditorSchemeId);
        }

        return $builder->build();
    }
}
