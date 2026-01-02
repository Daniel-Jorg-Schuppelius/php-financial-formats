<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AccountIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\Enums\CurrencyCode;
use Tests\Contracts\BaseTestCase;

class AccountIdentificationTest extends BaseTestCase {
    public function testConstructorWithIban(): void {
        $account = new AccountIdentification(
            iban: 'DE89370400440532013000',
            currency: CurrencyCode::Euro,
            name: 'Hauptkonto'
        );

        $this->assertSame('DE89370400440532013000', $account->getIban());
        $this->assertNull($account->getOther());
        $this->assertSame(CurrencyCode::Euro, $account->getCurrency());
        $this->assertSame('Hauptkonto', $account->getName());
        $this->assertTrue($account->isIban());
        $this->assertSame('DE89370400440532013000', $account->getIdentification());
    }

    public function testConstructorWithOther(): void {
        $account = new AccountIdentification(
            other: '1234567890',
            currency: CurrencyCode::Euro
        );

        $this->assertNull($account->getIban());
        $this->assertSame('1234567890', $account->getOther());
        $this->assertFalse($account->isIban());
        $this->assertSame('1234567890', $account->getIdentification());
    }

    public function testFromIban(): void {
        $account = AccountIdentification::fromIban('DE89370400440532013000', CurrencyCode::Euro);

        $this->assertSame('DE89370400440532013000', $account->getIban());
        $this->assertSame(CurrencyCode::Euro, $account->getCurrency());
        $this->assertTrue($account->isIban());
    }

    public function testFromIbanWithoutCurrency(): void {
        $account = AccountIdentification::fromIban('DE89370400440532013000');

        $this->assertSame('DE89370400440532013000', $account->getIban());
        $this->assertNull($account->getCurrency());
    }

    public function testFromOther(): void {
        $account = AccountIdentification::fromOther('9876543210', CurrencyCode::USDollar);

        $this->assertSame('9876543210', $account->getOther());
        $this->assertSame(CurrencyCode::USDollar, $account->getCurrency());
        $this->assertFalse($account->isIban());
    }

    public function testGetIdentificationPrefersIban(): void {
        $account = new AccountIdentification(
            iban: 'DE89370400440532013000',
            other: '1234567890'
        );

        $this->assertSame('DE89370400440532013000', $account->getIdentification());
    }

    public function testEmptyAccount(): void {
        $account = new AccountIdentification();

        $this->assertNull($account->getIban());
        $this->assertNull($account->getOther());
        $this->assertNull($account->getIdentification());
        $this->assertFalse($account->isIban());
    }

    public function testReadonlyClass(): void {
        $account = new AccountIdentification(iban: 'DE89370400440532013000');
        $reflection = new \ReflectionClass($account);
        $this->assertTrue($reflection->isReadOnly());
    }
}
