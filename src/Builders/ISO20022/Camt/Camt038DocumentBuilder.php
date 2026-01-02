<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt038DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type38\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.038 Documents (Case Status Report Request).
 * 
 * Erstellt Anfragen zum Fallstatus.
 * Wird verwendet um den aktuellen Status eines Untersuchungsfalles abzufragen.
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt038DocumentBuilder {
    private string $requestId;
    private DateTimeImmutable $creationDateTime;
    private ?string $requesterAgentBic = null;
    private ?string $requesterPartyName = null;
    private ?string $responderAgentBic = null;
    private ?string $responderPartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;

    private function __construct(string $requestId) {
        if (strlen($requestId) > 35) {
            throw new InvalidArgumentException('RequestId darf maximal 35 Zeichen lang sein');
        }
        $this->requestId = $requestId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    public static function create(string $requestId): self {
        return new self($requestId);
    }

    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    public function withRequesterAgent(string $bic): self {
        $clone = clone $this;
        $clone->requesterAgentBic = $bic;
        return $clone;
    }

    public function withRequesterPartyName(string $name): self {
        $clone = clone $this;
        $clone->requesterPartyName = $name;
        return $clone;
    }

    public function withResponderAgent(string $bic): self {
        $clone = clone $this;
        $clone->responderAgentBic = $bic;
        return $clone;
    }

    public function withResponderPartyName(string $name): self {
        $clone = clone $this;
        $clone->responderPartyName = $name;
        return $clone;
    }

    public function forCase(string $caseId, ?string $caseCreator = null): self {
        $clone = clone $this;
        $clone->caseId = $caseId;
        $clone->caseCreator = $caseCreator;
        return $clone;
    }

    public function build(): Document {
        return new Document(
            requestId: $this->requestId,
            creationDateTime: $this->creationDateTime,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            requesterAgentBic: $this->requesterAgentBic,
            requesterPartyName: $this->requesterPartyName,
            responderAgentBic: $this->responderAgentBic,
            responderPartyName: $this->responderPartyName
        );
    }
}
