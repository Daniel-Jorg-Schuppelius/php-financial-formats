<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt058Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58\CancellationItem;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58\Document;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Generator für CAMT.058 XML (Notification to Receive Cancellation Advice).
 * 
 * Generiert Stornierungshinweise zu Benachrichtigungen über erwartete Zahlungseingänge
 * gemäß ISO 20022 camt.058.001.xx Standard.
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt058Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT058;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V09): string {

        $this->initCamtDocument('NtfctnToRcvCxlAdvc', $version);

        // GrpHdr (Group Header)
        $this->addGroupHeaderWithParties($document);

        // OrgnlNtfctn (Original Notification Reference)
        $this->addOriginalNotification($document);

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
     * Fügt die Original Notification Reference hinzu.
     */
    private function addOriginalNotification(Document $document): void {
        $this->builder->addElement('OrgnlNtfctn');

        $this->addChildIfNotEmpty('OrgnlMsgId', $document->getOriginalMessageId());
        $this->addChildIfNotEmpty('OrgnlMsgNmId', $document->getOriginalMessageNameId());

        if ($document->getOriginalCreationDateTime() !== null) {
            $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($document->getOriginalCreationDateTime()));
        }

        // Cancellation Items
        foreach ($document->getItems() as $item) {
            $this->addCancellationItem($item);
        }

        $this->builder->end(); // OrgnlNtfctn
    }

    /**
     * Fügt ein Cancellation Item hinzu.
     */
    private function addCancellationItem(CancellationItem $item): void {
        $this->builder->addElement('OrgnlItm');

        $this->builder->addChild('OrgnlItmId', $this->escape($item->getOriginalItemId()));

        // CxlRsnInf (Cancellation Reason Information)
        if ($item->getCancellationReasonCode() !== null || $item->getCancellationReasonProprietary() !== null) {
            $this->builder->addElement('CxlRsnInf');
            $this->builder->addElement('Rsn');

            if ($item->getCancellationReasonCode() !== null) {
                $this->builder->addChild('Cd', $this->escape($item->getCancellationReasonCode()));
            } else {
                $this->builder->addChild('Prtry', $this->escape($item->getCancellationReasonProprietary()));
            }

            $this->builder->end(); // Rsn

            $this->addChildIfNotEmpty('AddtlInf', $item->getCancellationAdditionalInfo());

            $this->builder->end(); // CxlRsnInf
        }

        $this->builder->end(); // OrgnlItm
    }
}
