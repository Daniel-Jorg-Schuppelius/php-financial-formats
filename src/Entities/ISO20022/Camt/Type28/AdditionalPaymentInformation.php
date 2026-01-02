<?php
/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AdditionalPaymentInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type28;

/**
 * CAMT.028 Additional Payment Information Entry.
 * 
 * Enthält zusätzliche Informationen zu einer Zahlung.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type28
 */
class AdditionalPaymentInformation {
    private ?string $instructionIdentification;
    private ?string $endToEndIdentification;
    private ?string $paymentInformationIdentification;
    private ?string $remittanceInformation;
    private ?string $purpose;

    public function __construct(
        ?string $instructionIdentification = null,
        ?string $endToEndIdentification = null,
        ?string $paymentInformationIdentification = null,
        ?string $remittanceInformation = null,
        ?string $purpose = null
    ) {
        $this->instructionIdentification = $instructionIdentification;
        $this->endToEndIdentification = $endToEndIdentification;
        $this->paymentInformationIdentification = $paymentInformationIdentification;
        $this->remittanceInformation = $remittanceInformation;
        $this->purpose = $purpose;
    }

    public function getInstructionIdentification(): ?string {
        return $this->instructionIdentification;
    }

    public function getEndToEndIdentification(): ?string {
        return $this->endToEndIdentification;
    }

    public function getPaymentInformationIdentification(): ?string {
        return $this->paymentInformationIdentification;
    }

    public function getRemittanceInformation(): ?string {
        return $this->remittanceInformation;
    }

    public function getPurpose(): ?string {
        return $this->purpose;
    }
}
