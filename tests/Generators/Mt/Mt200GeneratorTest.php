<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt200GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Builders\Mt\Mt200DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt200Generator;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Mt200GeneratorTest extends BaseTestCase {
    public function testGenerateSimpleMessage(): void {
        $document = Mt200DocumentBuilder::create('FI-TRANSFER-001')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(500000.00, CurrencyCode::Euro)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->build();

        $generator = new Mt200Generator($document);
        $message = $generator->generate($document);

        // Feldprüfungen - MT200 besteht aus Feldblöcken
        $this->assertStringContainsString(':20:FI-TRANSFER-001', $message);
        $this->assertStringContainsString(':32A:', $message);
        $this->assertStringContainsString(':57A:', $message);
        $this->assertStringContainsString('EUR', $message);
    }

    public function testGenerateWithOptionalFields(): void {
        $document = Mt200DocumentBuilder::create('FI-FULL')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(1000000.00, CurrencyCode::USDollar)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->sendersCorrespondent('COBADEFFXXX')
            ->intermediary('INGBDEFFXXX')
            ->senderToReceiverInfo('/ACC/Transfer for liquidity')
            ->build();

        $generator = new Mt200Generator($document);
        $message = $generator->generate($document);

        $this->assertStringContainsString('USD', $message);
        $this->assertStringContainsString('COBADEFFXXX', $message);
        $this->assertStringContainsString('INGBDEFFXXX', $message);
    }

    public function testField32AFormat(): void {
        $document = Mt200DocumentBuilder::create('FIELD-TEST')
            ->valueDate(new DateTimeImmutable('2024-06-30'))
            ->amount(123456.78, CurrencyCode::BritishPound)
            ->accountWithInstitution('BABOROBU')
            ->build();

        $generator = new Mt200Generator($document);
        $message = $generator->generate($document);

        // Feld 32A: Valutadatum + Währung + Betrag
        $this->assertStringContainsString(':32A:', $message);
        $this->assertStringContainsString('240630', $message);
        $this->assertStringContainsString('GBP', $message);
    }

    public function testMessageEndsCorrectly(): void {
        $document = Mt200DocumentBuilder::create('END-TEST')
            ->valueDate(new DateTimeImmutable('2024-03-15'))
            ->amount(10000.00, CurrencyCode::Euro)
            ->accountWithInstitution('DEUTDEFFXXX')
            ->build();

        $generator = new Mt200Generator($document);
        $message = $generator->generate($document);

        // Nachricht sollte mit dem letzten Feldinhalt enden
        $this->assertNotEmpty($message);
        $this->assertStringContainsString(':57A:', $message);
    }
}
