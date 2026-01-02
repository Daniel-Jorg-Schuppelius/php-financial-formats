<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.055 Document (Customer Payment Cancellation Request).
 * 
 * Repräsentiert eine Stornierungsanfrage vom Kunden an die Bank
 * gemäß ISO 20022 camt.055.001.xx Standard.
 * 
 * Verwendet <CstmrPmtCxlReq> als Root und <Undrlyg> für die zugrundeliegenden Transaktionen.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type55
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $messageId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $numberOfTransactions = null;
    protected ?float $controlSum = null;
    protected ?string $initiatingPartyName = null;
    protected ?string $initiatingPartyId = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;

    /** @var UnderlyingTransaction[] */
    protected array $underlyingTransactions = [];

    public function __construct(
        string $messageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $numberOfTransactions = null,
        float|string|null $controlSum = null,
        ?string $initiatingPartyName = null,
        ?string $initiatingPartyId = null,
        ?string $caseId = null,
        ?string $caseCreator = null
    ) {
        $this->messageId = $messageId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->numberOfTransactions = $numberOfTransactions;
        $this->controlSum = is_string($controlSum) ? (float) $controlSum : $controlSum;
        $this->initiatingPartyName = $initiatingPartyName;
        $this->initiatingPartyId = $initiatingPartyId;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT055;
    }

    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getNumberOfTransactions(): ?string {
        return $this->numberOfTransactions;
    }

    public function getControlSum(): ?float {
        return $this->controlSum;
    }

    public function getInitiatingPartyName(): ?string {
        return $this->initiatingPartyName;
    }

    public function getInitiatingPartyId(): ?string {
        return $this->initiatingPartyId;
    }

    public function getCaseId(): ?string {
        return $this->caseId;
    }

    public function getCaseCreator(): ?string {
        return $this->caseCreator;
    }

    public function addUnderlyingTransaction(UnderlyingTransaction $underlying): void {
        $this->underlyingTransactions[] = $underlying;
    }

    /**
     * @return UnderlyingTransaction[]
     */
    public function getUnderlyingTransactions(): array {
        return $this->underlyingTransactions;
    }

    /**
     * Alle PaymentCancellationRequests aus allen UnderlyingTransactions.
     * 
     * @return PaymentCancellationRequest[]
     */
    public function getAllCancellationRequests(): array {
        $requests = [];
        foreach ($this->underlyingTransactions as $underlying) {
            // Direkte TxInf
            foreach ($underlying->getTransactionInformation() as $txInfo) {
                $requests[] = $txInfo;
            }
            // Via OrgnlPmtInfAndCxl
            foreach ($underlying->getOriginalPaymentInformationAndCancellation() as $pmtInf) {
                foreach ($pmtInf->getTransactionInformation() as $txInfo) {
                    $requests[] = $txInfo;
                }
            }
        }
        return $requests;
    }

    public function getTotalTransactionCount(): int {
        $count = 0;
        foreach ($this->underlyingTransactions as $underlying) {
            $count += $underlying->getTotalTransactionCount();
        }
        return $count;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V12): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $cstmrPmtCxlReq = $dom->createElement('CstmrPmtCxlReq');
        $root->appendChild($cstmrPmtCxlReq);

        // Assgnmt (Assignment)
        $assgnmt = $dom->createElement('Assgnmt');
        $cstmrPmtCxlReq->appendChild($assgnmt);

        $assgnmt->appendChild($dom->createElement('Id', htmlspecialchars($this->messageId)));
        $assgnmt->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        // Assigner (Initiating Party)
        if ($this->initiatingPartyName !== null) {
            $assgnr = $dom->createElement('Assgnr');
            $assgnmt->appendChild($assgnr);
            $pty = $dom->createElement('Pty');
            $assgnr->appendChild($pty);
            $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->initiatingPartyName)));
            if ($this->initiatingPartyId !== null) {
                $id = $dom->createElement('Id');
                $pty->appendChild($id);
                $orgId = $dom->createElement('OrgId');
                $id->appendChild($orgId);
                $othr = $dom->createElement('Othr');
                $orgId->appendChild($othr);
                $othr->appendChild($dom->createElement('Id', htmlspecialchars($this->initiatingPartyId)));
            }
        }

        // Case (optional)
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $cstmrPmtCxlReq->appendChild($case);
            $case->appendChild($dom->createElement('Id', htmlspecialchars($this->caseId)));

            if ($this->caseCreator !== null) {
                $cretr = $dom->createElement('Cretr');
                $case->appendChild($cretr);
                $pty = $dom->createElement('Pty');
                $cretr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->caseCreator)));
            }
        }

        // CtrlData (optional)
        if ($this->numberOfTransactions !== null) {
            $ctrlData = $dom->createElement('CtrlData');
            $cstmrPmtCxlReq->appendChild($ctrlData);
            $ctrlData->appendChild($dom->createElement('NbOfTxs', htmlspecialchars($this->numberOfTransactions)));
            if ($this->controlSum !== null) {
                $ctrlData->appendChild($dom->createElement('CtrlSum', number_format($this->controlSum, 2, '.', '')));
            }
        }

        // Undrlyg (Underlying Transactions)
        foreach ($this->underlyingTransactions as $underlying) {
            $undrlyg = $dom->createElement('Undrlyg');
            $cstmrPmtCxlReq->appendChild($undrlyg);

            // OrgnlGrpInfAndCxl
            if ($underlying->getOriginalGroupInformationMessageId() !== null) {
                $orgnlGrpInfAndCxl = $dom->createElement('OrgnlGrpInfAndCxl');
                $undrlyg->appendChild($orgnlGrpInfAndCxl);
                $orgnlGrpInfAndCxl->appendChild(
                    $dom->createElement('OrgnlMsgId', htmlspecialchars($underlying->getOriginalGroupInformationMessageId()))
                );

                if ($underlying->getOriginalGroupInformationMessageNameId() !== null) {
                    $orgnlGrpInfAndCxl->appendChild(
                        $dom->createElement('OrgnlMsgNmId', htmlspecialchars($underlying->getOriginalGroupInformationMessageNameId()))
                    );
                }

                if ($underlying->getOriginalGroupInformationCreationDateTime() !== null) {
                    $orgnlGrpInfAndCxl->appendChild(
                        $dom->createElement('OrgnlCreDtTm', $underlying->getOriginalGroupInformationCreationDateTime()->format('Y-m-d\TH:i:s.vP'))
                    );
                }
            }

            // OrgnlPmtInfAndCxl
            foreach ($underlying->getOriginalPaymentInformationAndCancellation() as $pmtInf) {
                $orgnlPmtInfAndCxl = $dom->createElement('OrgnlPmtInfAndCxl');
                $undrlyg->appendChild($orgnlPmtInfAndCxl);

                if ($pmtInf->getOriginalPaymentInformationId() !== null) {
                    $orgnlPmtInfAndCxl->appendChild(
                        $dom->createElement('OrgnlPmtInfId', htmlspecialchars($pmtInf->getOriginalPaymentInformationId()))
                    );
                }

                if ($pmtInf->getOriginalNumberOfTransactions() !== null) {
                    $orgnlPmtInfAndCxl->appendChild(
                        $dom->createElement('OrgnlNbOfTxs', (string)$pmtInf->getOriginalNumberOfTransactions())
                    );
                }

                if ($pmtInf->getOriginalControlSum() !== null) {
                    $orgnlPmtInfAndCxl->appendChild(
                        $dom->createElement('OrgnlCtrlSum', number_format($pmtInf->getOriginalControlSum(), 2, '.', ''))
                    );
                }

                if ($pmtInf->isCancelAllTransactions()) {
                    $orgnlPmtInfAndCxl->appendChild($dom->createElement('PmtInfCxl', 'true'));
                }

                // TxInf within OrgnlPmtInfAndCxl
                foreach ($pmtInf->getTransactionInformation() as $txInfo) {
                    $this->appendTransactionInfo($dom, $orgnlPmtInfAndCxl, $txInfo);
                }
            }

            // Direct TxInf under Undrlyg
            foreach ($underlying->getTransactionInformation() as $txInfo) {
                $this->appendTransactionInfo($dom, $undrlyg, $txInfo);
            }
        }

        return $dom->saveXML() ?: '';
    }

    private function appendTransactionInfo(DOMDocument $dom, \DOMElement $parent, PaymentCancellationRequest $txInfo): void {
        $txInfElement = $dom->createElement('TxInf');
        $parent->appendChild($txInfElement);

        if ($txInfo->getCancellationId() !== null) {
            $txInfElement->appendChild($dom->createElement('CxlId', htmlspecialchars($txInfo->getCancellationId())));
        }

        if ($txInfo->getCancellationReasonCode() !== null || $txInfo->getCancellationReasonProprietary() !== null) {
            $cxlRsnInf = $dom->createElement('CxlRsnInf');
            $txInfElement->appendChild($cxlRsnInf);

            $rsn = $dom->createElement('Rsn');
            $cxlRsnInf->appendChild($rsn);

            if ($txInfo->getCancellationReasonCode() !== null) {
                $rsn->appendChild($dom->createElement('Cd', htmlspecialchars($txInfo->getCancellationReasonCode())));
            } else {
                $rsn->appendChild($dom->createElement('Prtry', htmlspecialchars($txInfo->getCancellationReasonProprietary())));
            }

            if ($txInfo->getCancellationReasonAdditionalInfo() !== null) {
                $cxlRsnInf->appendChild(
                    $dom->createElement('AddtlInf', htmlspecialchars($txInfo->getCancellationReasonAdditionalInfo()))
                );
            }
        }

        if ($txInfo->getOriginalInstructionId() !== null) {
            $txInfElement->appendChild(
                $dom->createElement('OrgnlInstrId', htmlspecialchars($txInfo->getOriginalInstructionId()))
            );
        }

        if ($txInfo->getOriginalEndToEndId() !== null) {
            $txInfElement->appendChild(
                $dom->createElement('OrgnlEndToEndId', htmlspecialchars($txInfo->getOriginalEndToEndId()))
            );
        }

        if ($txInfo->getOriginalAmount() !== null && $txInfo->getOriginalCurrency() !== null) {
            $amtElement = $dom->createElement('OrgnlInstdAmt', number_format($txInfo->getOriginalAmount(), 2, '.', ''));
            $amtElement->setAttribute('Ccy', $txInfo->getOriginalCurrency()->value);
            $txInfElement->appendChild($amtElement);
        }

        if ($txInfo->getRequestedExecutionDate() !== null) {
            $txInfElement->appendChild(
                $dom->createElement('OrgnlReqdExctnDt', $txInfo->getRequestedExecutionDate()->format('Y-m-d'))
            );
        }
    }
}
