<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt057Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57\NotificationItem;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Generator für CAMT.057 XML (Notification to Receive).
 * 
 * Generiert Benachrichtigungen über erwartete Zahlungseingänge
 * gemäß ISO 20022 camt.057.001.xx Standard.
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt057Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT057;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V08): string {

        $this->initCamtDocument('NtfctnToRcv', $version);

        // GrpHdr (Group Header)
        $this->addGroupHeaderWithParties($document);

        // Ntfctn (Notification Items)
        foreach ($document->getItems() as $item) {
            $this->addNotificationItem($item);
        }

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
     * Fügt ein Notification Item hinzu.
     */
    private function addNotificationItem(NotificationItem $item): void {
        $this->builder->addElement('Ntfctn');

        $this->builder->addChild('Id', $this->escape($item->getId()));

        // XpctdValDt (Expected Value Date)
        if ($item->getExpectedValueDate() !== null) {
            $this->builder->addChild('XpctdValDt', $this->formatDate($item->getExpectedValueDate()));
        }

        // Amt (Amount)
        if ($item->getAmount() !== null && $item->getCurrency() !== null) {
            $this->addAmount('Amt', $item->getAmount(), $item->getCurrency());
        }

        // Dbtr (Debtor)
        if ($item->getDebtorName() !== null) {
            $this->builder
                ->addElement('Dbtr')
                ->addChild('Nm', $this->escape($item->getDebtorName()))
                ->end();
        }

        // DbtrAgt (Debtor Agent)
        if ($item->getDebtorAgentBic() !== null) {
            $this->builder
                ->addElement('DbtrAgt')
                ->addElement('FinInstnId')
                ->addChild('BICFI', $this->escape($item->getDebtorAgentBic()))
                ->end() // FinInstnId
                ->end(); // DbtrAgt
        }

        // RmtInf (Remittance Information)
        if ($item->getRemittanceInformation() !== null) {
            $this->builder
                ->addElement('RmtInf')
                ->addChild('Ustrd', $this->escape($item->getRemittanceInformation()))
                ->end();
        }

        $this->builder->end(); // Ntfctn
    }
}
