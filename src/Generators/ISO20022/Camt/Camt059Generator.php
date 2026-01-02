<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt059Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59\StatusItem;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use InvalidArgumentException;

/**
 * Generator für CAMT.059 XML (Notification to Receive Status Report).
 * 
 * Generiert Statusberichte zu Benachrichtigungen über erwartete Zahlungseingänge
 * gemäß ISO 20022 camt.059.001.xx Standard.
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt059Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT059;
    }

    /**
     * @param Document $document
     */
    public function generate(CamtDocumentAbstract $document, CamtVersion $version = CamtVersion::V08): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Camt059Generator erwartet ein Camt.059 Document.');
        }

        $this->initCamtDocument('NtfctnToRcvStsRpt', $version);

        // GrpHdr (Group Header)
        $this->addGroupHeaderWithParties($document);

        // OrgnlNtfctnAndSts (Original Notification and Status)
        $this->addOriginalNotificationAndStatus($document);

        return $this->getXml();
    }

    /**
     * Fügt den Group Header mit optionalen Party-Informationen hinzu.
     */
    private function addGroupHeaderWithParties(Document $document): void {
        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $this->escape($document->getGroupHeaderMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // InitgPty (Initiating Party)
        if ($document->getInitiatingPartyName() !== null) {
            $this->builder
                ->addElement('InitgPty')
                ->addChild('Nm', $this->escape($document->getInitiatingPartyName()))
                ->end();
        }

        // MsgRcpt (Message Recipient)
        if ($document->getMessageRecipientBic() !== null) {
            $this->builder
                ->addElement('MsgRcpt')
                ->addElement('FinInstnId')
                ->addChild('BICFI', $this->escape($document->getMessageRecipientBic()))
                ->end() // FinInstnId
                ->end(); // MsgRcpt
        }

        $this->builder->end(); // GrpHdr
    }

    /**
     * Fügt die Original Notification and Status hinzu.
     */
    private function addOriginalNotificationAndStatus(Document $document): void {
        $this->builder->addElement('OrgnlNtfctnAndSts');

        $this->addChildIfNotEmpty('OrgnlMsgId', $document->getOriginalMessageId());
        $this->addChildIfNotEmpty('OrgnlMsgNmId', $document->getOriginalMessageNameId());

        if ($document->getOriginalCreationDateTime() !== null) {
            $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($document->getOriginalCreationDateTime()));
        }

        $this->addChildIfNotEmpty('OrgnlNtfctnSts', $document->getOriginalGroupStatusCode());

        // Status Items
        foreach ($document->getItems() as $item) {
            $this->addStatusItem($item);
        }

        $this->builder->end(); // OrgnlNtfctnAndSts
    }

    /**
     * Fügt ein Status Item hinzu.
     */
    private function addStatusItem(StatusItem $item): void {
        $this->builder->addElement('OrgnlItmAndSts');

        $this->builder->addChild('OrgnlItmId', $this->escape($item->getOriginalItemId()));

        $this->addChildIfNotEmpty('ItmSts', $item->getItemStatus());

        // StsRsnInf (Status Reason Information)
        if ($item->getReasonCode() !== null || $item->getReasonProprietary() !== null) {
            $this->builder->addElement('StsRsnInf');
            $this->builder->addElement('Rsn');

            if ($item->getReasonCode() !== null) {
                $this->builder->addChild('Cd', $this->escape($item->getReasonCode()));
            } else {
                $this->builder->addChild('Prtry', $this->escape($item->getReasonProprietary()));
            }

            $this->builder->end(); // Rsn

            $this->addChildIfNotEmpty('AddtlInf', $item->getAdditionalInformation());

            $this->builder->end(); // StsRsnInf
        }

        $this->builder->end(); // OrgnlItmAndSts
    }
}
