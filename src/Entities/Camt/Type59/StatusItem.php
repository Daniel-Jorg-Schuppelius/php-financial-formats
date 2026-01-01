<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : StatusItem.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type59;

/**
 * Status Item für CAMT.059.
 *
 * Repräsentiert den Status eines einzelnen Elements
 * einer Benachrichtigung über einen erwarteten Zahlungseingang.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type59
 */
class StatusItem {
    protected string $originalItemId;
    protected ?string $itemStatus = null;
    protected ?string $reasonCode = null;
    protected ?string $reasonProprietary = null;
    protected ?string $additionalInformation = null;

    public function __construct(
        string $originalItemId,
        ?string $itemStatus = null,
        ?string $reasonCode = null,
        ?string $reasonProprietary = null,
        ?string $additionalInformation = null
    ) {
        $this->originalItemId = $originalItemId;
        $this->itemStatus = $itemStatus;
        $this->reasonCode = $reasonCode;
        $this->reasonProprietary = $reasonProprietary;
        $this->additionalInformation = $additionalInformation;
    }

    public function getOriginalItemId(): string {
        return $this->originalItemId;
    }

    public function getItemStatus(): ?string {
        return $this->itemStatus;
    }

    public function getReasonCode(): ?string {
        return $this->reasonCode;
    }

    public function getReasonProprietary(): ?string {
        return $this->reasonProprietary;
    }

    public function getReason(): ?string {
        return $this->reasonCode ?? $this->reasonProprietary;
    }

    public function getAdditionalInformation(): ?string {
        return $this->additionalInformation;
    }
}
