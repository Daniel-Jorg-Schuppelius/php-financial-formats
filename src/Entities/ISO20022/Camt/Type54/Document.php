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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt054Generator;

/**
 * CAMT.054 Document (Bank to Customer Debit Credit Notification).
 * 
 * Repräsentiert einen Soll/Haben-Avis (Einzelumsatzbenachrichtigung) gemäß
 * ISO 20022 camt.054.001.02/08 Standard.
 * 
 * Verwendet <BkToCstmrDbtCdtNtfctn> als Root und <Ntfctn> für Notifications.
 * 
 * Anders als CAMT.052/053 enthält CAMT.054 normalerweise keine Salden,
 * sondern nur einzelne Buchungsbenachrichtigungen.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt054
 */
class Document extends CamtDocumentAbstract {
    /** @var Transaction[] */
    protected array $entries = [];

    public function getCamtType(): CamtType {
        return CamtType::CAMT054;
    }

    public function addEntry(Transaction $entry): void {
        $this->entries[] = $entry;
    }

    /**
     * @return Transaction[]
     */
    public function getEntries(): array {
        return $this->entries;
    }

    public function toXml(CamtVersion $version = CamtVersion::V02): string {
        return (new Camt054Generator())->generate($this, $version);
    }

    /**
     * Gibt das Dokument als XML-String zurück.
     */
    public function __toString(): string {
        return $this->toXml();
    }
}
