<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\PainType;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain018Generator;
use DateTimeImmutable;

/**
 * pain.018 Document - Mandate Suspension Request.
 * 
 * Anfrage zur temporären Aussetzung eines Mandats.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type18
 */
final class Document {
    /** @var MandateSuspensionRequest[] */
    private array $suspensionRequests = [];

    public function __construct(
        private readonly string $messageId,
        private readonly DateTimeImmutable $creationDateTime,
        private readonly PartyIdentification $initiatingParty,
        array $suspensionRequests = []
    ) {
        $this->suspensionRequests = $suspensionRequests;
    }

    public static function create(
        string $messageId,
        PartyIdentification $initiatingParty,
        array $suspensionRequests = []
    ): self {
        return new self(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            initiatingParty: $initiatingParty,
            suspensionRequests: $suspensionRequests
        );
    }

    public function getType(): PainType {
        return PainType::PAIN_018;
    }

    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getInitiatingParty(): PartyIdentification {
        return $this->initiatingParty;
    }

    /**
     * @return MandateSuspensionRequest[]
     */
    public function getSuspensionRequests(): array {
        return $this->suspensionRequests;
    }

    public function addSuspensionRequest(MandateSuspensionRequest $request): self {
        $clone = clone $this;
        $clone->suspensionRequests[] = $request;
        return $clone;
    }

    public function countRequests(): int {
        return count($this->suspensionRequests);
    }

    /**
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        if (strlen($this->messageId) > 35) {
            $errors[] = 'MsgId darf maximal 35 Zeichen lang sein';
        }

        if (empty($this->suspensionRequests)) {
            $errors[] = 'Mindestens eine Mandatsaussetzungs-Anfrage erforderlich';
        }

        foreach ($this->suspensionRequests as $index => $request) {
            if ($request->getSuspensionStartDate() > $request->getSuspensionEndDate()) {
                $errors[] = "MndtSspnsnReq[$index] Startdatum liegt nach Enddatum";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generiert XML-Ausgabe für dieses Dokument.
     *
     * @param string|null $namespace Optionaler XML-Namespace
     * @return string Das generierte XML
     */
    public function toXml(?string $namespace = null): string {
        $generator = $namespace !== null
            ? new Pain018Generator($namespace)
            : new Pain018Generator();
        return $generator->generate($this);
    }
}
