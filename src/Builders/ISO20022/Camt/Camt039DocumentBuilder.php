<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt039DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type39\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for CAMT.039 Documents (Case Status Report).
 * 
 * Creates case status reports in response to CAMT.038 requests.
 * Contains the current status of an investigation case.
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt039DocumentBuilder {
    private string $reportId;
    private DateTimeImmutable $creationDateTime;
    private ?string $reporterAgentBic = null;
    private ?string $reporterPartyName = null;
    private ?string $receiverAgentBic = null;
    private ?string $receiverPartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $statusCode = null;
    private ?string $statusReason = null;
    private ?string $additionalInformation = null;

    private function __construct(string $reportId) {
        if (strlen($reportId) > 35) {
            throw new InvalidArgumentException('ReportId must not exceed 35 characters');
        }
        $this->reportId = $reportId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    public static function create(string $reportId): self {
        return new self($reportId);
    }

    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    public function withReporterAgent(string $bic): self {
        $clone = clone $this;
        $clone->reporterAgentBic = $bic;
        return $clone;
    }

    public function withReporterPartyName(string $name): self {
        $clone = clone $this;
        $clone->reporterPartyName = $name;
        return $clone;
    }

    public function withReceiverAgent(string $bic): self {
        $clone = clone $this;
        $clone->receiverAgentBic = $bic;
        return $clone;
    }

    public function withReceiverPartyName(string $name): self {
        $clone = clone $this;
        $clone->receiverPartyName = $name;
        return $clone;
    }

    public function forCase(string $caseId, ?string $caseCreator = null): self {
        $clone = clone $this;
        $clone->caseId = $caseId;
        $clone->caseCreator = $caseCreator;
        return $clone;
    }

    public function withStatus(string $code, ?string $reason = null): self {
        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->statusReason = $reason;
        return $clone;
    }

    public function withAdditionalInformation(string $info): self {
        $clone = clone $this;
        $clone->additionalInformation = $info;
        return $clone;
    }

    public function build(): Document {
        return new Document(
            reportId: $this->reportId,
            creationDateTime: $this->creationDateTime,
            statusCode: $this->statusCode,
            statusReason: $this->statusReason,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            reporterAgentBic: $this->reporterAgentBic,
            reporterPartyName: $this->reporterPartyName,
            receiverAgentBic: $this->receiverAgentBic,
            receiverPartyName: $this->receiverPartyName,
            additionalInformation: $this->additionalInformation
        );
    }
}
