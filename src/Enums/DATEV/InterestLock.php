<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : InterestLock.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use CommonToolkit\Traits\LockFlagTrait;

enum InterestLock: int {
    use LockFlagTrait;

    case NONE   = 0; // keine Zinssperre
    case LOCKED = 1; // gesperrt
}
