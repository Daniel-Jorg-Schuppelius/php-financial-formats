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
 * Represents a debit/credit notification (single transaction notification) according to
 * ISO 20022 camt.054.001.02/08 Standard.
 * 
 * Uses <BkToCstmrDbtCdtNtfctn> as root and <Ntfctn> for notifications.
 * 
 * Unlike CAMT.052/053, CAMT.054 normally contains no balances,
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
     * Returns the document as XML string.
     */
    public function __toString(): string {
        return $this->toXml();
    }
}
