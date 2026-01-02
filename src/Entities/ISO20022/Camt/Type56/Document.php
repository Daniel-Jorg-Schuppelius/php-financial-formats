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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.056 Document (FI To FI Payment Cancellation Request).
 * 
 * Repräsentiert eine Stornierungsanfrage von Bank zu Bank
 * gemäß ISO 20022 camt.056.001.xx Standard.
 * 
 * Verwendet <FIToFIPmtCxlReq> als Root und <Undrlyg> für die zugrundeliegenden Transaktionen.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type56
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $messageId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $numberOfTransactions = null;
    protected ?string $controlSum = null;
    protected ?string $instructingAgentBic = null;
    protected ?string $instructedAgentBic = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;

    /** @var UnderlyingTransaction[] */
    protected array $underlyingTransactions = [];

    public function __construct(
        string $messageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $numberOfTransactions = null,
        ?string $controlSum = null,
        ?string $instructingAgentBic = null,
        ?string $instructedAgentBic = null,
        ?string $caseId = null,
        ?string $caseCreator = null
    ) {
        $this->messageId = $messageId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->numberOfTransactions = $numberOfTransactions;
        $this->controlSum = $controlSum;
        $this->instructingAgentBic = $instructingAgentBic;
        $this->instructedAgentBic = $instructedAgentBic;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT056;
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

    public function getControlSum(): ?string {
        return $this->controlSum;
    }

    public function getInstructingAgentBic(): ?string {
        return $this->instructingAgentBic;
    }

    public function getInstructedAgentBic(): ?string {
        return $this->instructedAgentBic;
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
            foreach ($underlying->getTransactionInformation() as $txInfo) {
                $requests[] = $txInfo;
            }
        }
        return $requests;
    }

    public function getTotalTransactionCount(): int {
        $count = 0;
        foreach ($this->underlyingTransactions as $underlying) {
            $count += $underlying->getTransactionCount();
        }
        return $count;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V11): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $fiToFIPmtCxlReq = $dom->createElement('FIToFIPmtCxlReq');
        $root->appendChild($fiToFIPmtCxlReq);

        // Assgnmt (Assignment)
        $assgnmt = $dom->createElement('Assgnmt');
        $fiToFIPmtCxlReq->appendChild($assgnmt);

        $assgnmt->appendChild($dom->createElement('Id', htmlspecialchars($this->messageId)));
        $assgnmt->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        // Assigner (Instructing Agent)
        if ($this->instructingAgentBic !== null) {
            $assgnr = $dom->createElement('Assgnr');
            $assgnmt->appendChild($assgnr);
            $agt = $dom->createElement('Agt');
            $assgnr->appendChild($agt);
            $finInstnId = $dom->createElement('FinInstnId');
            $agt->appendChild($finInstnId);
            $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->instructingAgentBic)));
        }

        // Assignee (Instructed Agent)
        if ($this->instructedAgentBic !== null) {
            $assgne = $dom->createElement('Assgne');
            $assgnmt->appendChild($assgne);
            $agt = $dom->createElement('Agt');
            $assgne->appendChild($agt);
            $finInstnId = $dom->createElement('FinInstnId');
            $agt->appendChild($finInstnId);
            $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->instructedAgentBic)));
        }

        // Case (optional)
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $fiToFIPmtCxlReq->appendChild($case);
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
            $fiToFIPmtCxlReq->appendChild($ctrlData);
            $ctrlData->appendChild($dom->createElement('NbOfTxs', htmlspecialchars($this->numberOfTransactions)));
            if ($this->controlSum !== null) {
                $ctrlData->appendChild($dom->createElement('CtrlSum', htmlspecialchars($this->controlSum)));
            }
        }

        // Undrlyg (Underlying Transactions)
        foreach ($this->underlyingTransactions as $underlying) {
            $undrlyg = $dom->createElement('Undrlyg');
            $fiToFIPmtCxlReq->appendChild($undrlyg);

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

                if ($underlying->getOriginalNumberOfTransactions() !== null) {
                    $orgnlGrpInfAndCxl->appendChild(
                        $dom->createElement('OrgnlNbOfTxs', (string)$underlying->getOriginalNumberOfTransactions())
                    );
                }

                if ($underlying->getOriginalControlSum() !== null) {
                    $orgnlGrpInfAndCxl->appendChild(
                        $dom->createElement('OrgnlCtrlSum', htmlspecialchars($underlying->getOriginalControlSum()))
                    );
                }
            }

            // TxInf (Transaction Information)
            foreach ($underlying->getTransactionInformation() as $txInfo) {
                $txInfElement = $dom->createElement('TxInf');
                $undrlyg->appendChild($txInfElement);

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

                // OrgnlInstrId
                if ($txInfo->getOriginalInstructionId() !== null) {
                    $txInfElement->appendChild(
                        $dom->createElement('OrgnlInstrId', htmlspecialchars($txInfo->getOriginalInstructionId()))
                    );
                }

                // OrgnlEndToEndId
                if ($txInfo->getOriginalEndToEndId() !== null) {
                    $txInfElement->appendChild(
                        $dom->createElement('OrgnlEndToEndId', htmlspecialchars($txInfo->getOriginalEndToEndId()))
                    );
                }

                // OrgnlTxId
                if ($txInfo->getOriginalTransactionId() !== null) {
                    $txInfElement->appendChild(
                        $dom->createElement('OrgnlTxId', htmlspecialchars($txInfo->getOriginalTransactionId()))
                    );
                }

                // OrgnlIntrBkSttlmAmt
                if ($txInfo->getOriginalInterbankSettlementAmount() !== null && $txInfo->getOriginalCurrency() !== null) {
                    $amtElement = $dom->createElement('OrgnlIntrBkSttlmAmt', htmlspecialchars($txInfo->getOriginalInterbankSettlementAmount()));
                    $amtElement->setAttribute('Ccy', $txInfo->getOriginalCurrency()->value);
                    $txInfElement->appendChild($amtElement);
                }

                // OrgnlIntrBkSttlmDt
                if ($txInfo->getOriginalInterbankSettlementDate() !== null) {
                    $txInfElement->appendChild(
                        $dom->createElement('OrgnlIntrBkSttlmDt', $txInfo->getOriginalInterbankSettlementDate()->format('Y-m-d'))
                    );
                }
            }
        }

        return $dom->saveXML() ?: '';
    }
}
