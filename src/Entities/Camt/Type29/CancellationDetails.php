<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CancellationDetails.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type29;

/**
 * CAMT.029 Cancellation Details.
 * 
 * Enthält Details über die Stornierungsantwort mit Gruppen-, Zahlungs- und Transaktionsstatus.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type29
 */
class CancellationDetails {
    private ?OriginalGroupInformationAndStatus $originalGroupInformationAndStatus = null;

    /** @var OriginalPaymentInformationAndStatus[] */
    private array $originalPaymentInformationAndStatus = [];

    /** @var TransactionInformationAndStatus[] */
    private array $transactionInformationAndStatus = [];

    public function __construct(
        ?OriginalGroupInformationAndStatus $originalGroupInformationAndStatus = null
    ) {
        $this->originalGroupInformationAndStatus = $originalGroupInformationAndStatus;
    }

    public function getOriginalGroupInformationAndStatus(): ?OriginalGroupInformationAndStatus {
        return $this->originalGroupInformationAndStatus;
    }

    public function setOriginalGroupInformationAndStatus(OriginalGroupInformationAndStatus $status): void {
        $this->originalGroupInformationAndStatus = $status;
    }

    public function addOriginalPaymentInformationAndStatus(OriginalPaymentInformationAndStatus $pmtInfAndSts): void {
        $this->originalPaymentInformationAndStatus[] = $pmtInfAndSts;
    }

    /**
     * @return OriginalPaymentInformationAndStatus[]
     */
    public function getOriginalPaymentInformationAndStatus(): array {
        return $this->originalPaymentInformationAndStatus;
    }

    public function addTransactionInformationAndStatus(TransactionInformationAndStatus $txInfAndSts): void {
        $this->transactionInformationAndStatus[] = $txInfAndSts;
    }

    /**
     * @return TransactionInformationAndStatus[]
     */
    public function getTransactionInformationAndStatus(): array {
        return $this->transactionInformationAndStatus;
    }

    /**
     * Alle TransactionInformationAndStatus aus allen Quellen.
     * 
     * @return TransactionInformationAndStatus[]
     */
    public function getAllTransactionInformationAndStatus(): array {
        $all = $this->transactionInformationAndStatus;
        foreach ($this->originalPaymentInformationAndStatus as $pmtInf) {
            foreach ($pmtInf->getTransactionInformationAndStatus() as $txInfo) {
                $all[] = $txInfo;
            }
        }
        return $all;
    }

    public function getTotalTransactionCount(): int {
        $count = count($this->transactionInformationAndStatus);
        foreach ($this->originalPaymentInformationAndStatus as $pmtInf) {
            $count += $pmtInf->getTransactionCount();
        }
        return $count;
    }
}
