<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MandateAmendment.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;

/**
 * Mandate amendment for pain.010.
 * 
 * Kombiniert das neue Mandat mit den Original-Werten.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type10
 */
final readonly class MandateAmendment {
    public function __construct(
        private Mandate $mandate,
        private AmendmentDetails $amendmentDetails
    ) {
    }

    public static function create(Mandate $mandate, AmendmentDetails $amendmentDetails): self {
        return new self($mandate, $amendmentDetails);
    }

    public function getMandate(): Mandate {
        return $this->mandate;
    }

    public function getAmendmentDetails(): AmendmentDetails {
        return $this->amendmentDetails;
    }
}
