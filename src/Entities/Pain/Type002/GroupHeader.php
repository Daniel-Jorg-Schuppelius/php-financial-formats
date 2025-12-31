<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GroupHeader.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Pain\Type002;

use CommonToolkit\FinancialFormats\Entities\Pain\PartyIdentification;
use DateTimeImmutable;

/**
 * Group Header für pain.002 Payment Status Report (GrpHdr).
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type002
 */
final readonly class GroupHeader {
    public function __construct(
        private string $messageId,
        private DateTimeImmutable $creationDateTime,
        private ?PartyIdentification $initiatingParty = null,
        private ?PartyIdentification $forwardingAgent = null,
        private ?PartyIdentification $debtorAgent = null,
        private ?PartyIdentification $creditorAgent = null
    ) {
    }

    /**
     * Factory-Methode.
     */
    public static function create(
        string $messageId,
        ?PartyIdentification $initiatingParty = null
    ): self {
        return new self(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            initiatingParty: $initiatingParty
        );
    }

    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getInitiatingParty(): ?PartyIdentification {
        return $this->initiatingParty;
    }

    public function getForwardingAgent(): ?PartyIdentification {
        return $this->forwardingAgent;
    }

    public function getDebtorAgent(): ?PartyIdentification {
        return $this->debtorAgent;
    }

    public function getCreditorAgent(): ?PartyIdentification {
        return $this->creditorAgent;
    }
}
