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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain017Generator;
use DateTimeImmutable;

/**
 * pain.017 Document - Mandate Copy Request.
 * 
 * Anfrage zur Erstellung einer Kopie eines bestehenden Mandats.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type17
 */
final class Document {
    /** @var MandateCopyRequest[] */
    private array $copyRequests = [];

    public function __construct(
        private readonly string $messageId,
        private readonly DateTimeImmutable $creationDateTime,
        private readonly PartyIdentification $initiatingParty,
        array $copyRequests = []
    ) {
        $this->copyRequests = $copyRequests;
    }

    public static function create(
        string $messageId,
        PartyIdentification $initiatingParty,
        array $copyRequests = []
    ): self {
        return new self(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            initiatingParty: $initiatingParty,
            copyRequests: $copyRequests
        );
    }

    public function getType(): PainType {
        return PainType::PAIN_017;
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
     * @return MandateCopyRequest[]
     */
    public function getCopyRequests(): array {
        return $this->copyRequests;
    }

    public function addCopyRequest(MandateCopyRequest $request): self {
        $clone = clone $this;
        $clone->copyRequests[] = $request;
        return $clone;
    }

    public function countRequests(): int {
        return count($this->copyRequests);
    }

    /**
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        if (strlen($this->messageId) > 35) {
            $errors[] = 'MsgId must not exceed 35 characters';
        }

        if (empty($this->copyRequests)) {
            $errors[] = 'Mindestens eine Mandatskopie-Anfrage erforderlich';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generates XML output for this document.
     *
     * @param string|null $namespace Optionaler XML-Namespace
     * @return string Das generierte XML
     */
    public function toXml(?string $namespace = null): string {
        $generator = $namespace !== null
            ? new Pain017Generator($namespace)
            : new Pain017Generator();
        return $generator->generate($this);
    }
}
