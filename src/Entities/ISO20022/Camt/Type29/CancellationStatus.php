<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CancellationStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29;

/**
 * CAMT.029 Cancellation Status Information.
 * 
 * Enthält den Status einer Stornierungsanfrage.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type29
 */
class CancellationStatus {
    private ?string $statusCode;
    private ?string $statusProprietary;
    private ?string $additionalInformation;
    private ?string $originatorName;
    private ?string $originatorId;

    public function __construct(
        ?string $statusCode = null,
        ?string $statusProprietary = null,
        ?string $additionalInformation = null,
        ?string $originatorName = null,
        ?string $originatorId = null
    ) {
        $this->statusCode = $statusCode;
        $this->statusProprietary = $statusProprietary;
        $this->additionalInformation = $additionalInformation;
        $this->originatorName = $originatorName;
        $this->originatorId = $originatorId;
    }

    public function getStatusCode(): ?string {
        return $this->statusCode;
    }

    public function getStatusProprietary(): ?string {
        return $this->statusProprietary;
    }

    public function getStatus(): ?string {
        return $this->statusCode ?? $this->statusProprietary;
    }

    public function getAdditionalInformation(): ?string {
        return $this->additionalInformation;
    }

    public function getOriginatorName(): ?string {
        return $this->originatorName;
    }

    public function getOriginatorId(): ?string {
        return $this->originatorId;
    }

    /**
     * Prüft ob die Stornierung akzeptiert wurde.
     */
    public function isAccepted(): bool {
        return $this->statusCode === 'ACCP' || $this->statusCode === 'ACSC';
    }

    /**
     * Prüft ob die Stornierung abgelehnt wurde.
     */
    public function isRejected(): bool {
        return $this->statusCode === 'RJCR';
    }

    /**
     * Prüft ob die Stornierung noch aussteht.
     */
    public function isPending(): bool {
        return $this->statusCode === 'PDNG';
    }
}
