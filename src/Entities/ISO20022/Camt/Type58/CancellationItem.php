<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CancellationItem.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58;

/**
 * Cancellation item for CAMT.058.
 *
 * Represents a single element of a cancellation notification.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type58
 */
class CancellationItem {
    protected string $originalItemId;
    protected ?string $cancellationReasonCode = null;
    protected ?string $cancellationReasonProprietary = null;
    protected ?string $cancellationAdditionalInfo = null;

    public function __construct(
        string $originalItemId,
        ?string $cancellationReasonCode = null,
        ?string $cancellationReasonProprietary = null,
        ?string $cancellationAdditionalInfo = null
    ) {
        $this->originalItemId = $originalItemId;
        $this->cancellationReasonCode = $cancellationReasonCode;
        $this->cancellationReasonProprietary = $cancellationReasonProprietary;
        $this->cancellationAdditionalInfo = $cancellationAdditionalInfo;
    }

    public function getOriginalItemId(): string {
        return $this->originalItemId;
    }

    public function getCancellationReasonCode(): ?string {
        return $this->cancellationReasonCode;
    }

    public function getCancellationReasonProprietary(): ?string {
        return $this->cancellationReasonProprietary;
    }

    public function getCancellationReason(): ?string {
        return $this->cancellationReasonCode ?? $this->cancellationReasonProprietary;
    }

    public function getCancellationAdditionalInfo(): ?string {
        return $this->cancellationAdditionalInfo;
    }
}
