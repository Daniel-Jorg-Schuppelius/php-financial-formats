<?php
/*
 * Created on   : Thu May 08 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BalanceInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces;

use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

interface BalanceInterface {
    public function getCreditDebit(): CreditDebit;
    public function getDate(): DateTimeImmutable;
    public function getCurrency(): CurrencyCode;
    public function getAmount(): float;

    public function __toString(): string;
}
