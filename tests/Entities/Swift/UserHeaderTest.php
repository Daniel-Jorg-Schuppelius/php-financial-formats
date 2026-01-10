<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : UserHeaderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Swift;

use CommonToolkit\FinancialFormats\Entities\Swift\UserHeader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserHeaderTest extends TestCase {
    #[Test]
    public function constructorWithEmptyFields(): void {
        $header = new UserHeader();

        $this->assertSame([], $header->getFields());
    }

    #[Test]
    public function constructorWithFields(): void {
        $fields = [
            '103' => 'GPA',
            '108' => 'MUR12345678901234',
            '119' => 'STP'
        ];

        $header = new UserHeader($fields);

        $this->assertSame($fields, $header->getFields());
    }

    #[Test]
    public function getFieldReturnsValueIfExists(): void {
        $header = new UserHeader(['108' => 'MUR123']);

        $this->assertSame('MUR123', $header->getField('108'));
    }

    #[Test]
    public function getFieldReturnsNullIfNotExists(): void {
        $header = new UserHeader();

        $this->assertNull($header->getField('999'));
    }

    #[Test]
    public function getServiceTypeId(): void {
        $header = new UserHeader(['103' => 'GPA']);

        $this->assertSame('GPA', $header->getServiceTypeId());
    }

    #[Test]
    public function getMur(): void {
        $header = new UserHeader(['108' => 'MUR12345678901234']);

        $this->assertSame('MUR12345678901234', $header->getMur());
    }

    #[Test]
    public function getBankingPriority(): void {
        $header = new UserHeader(['113' => 'URGP']);

        $this->assertSame('URGP', $header->getBankingPriority());
    }

    #[Test]
    public function getValidationFlag(): void {
        $header = new UserHeader(['119' => 'STP']);

        $this->assertSame('STP', $header->getValidationFlag());
    }

    #[Test]
    public function getUetr(): void {
        $uetr = '123e4567-e89b-12d3-a456-426614174000';
        $header = new UserHeader(['121' => $uetr]);

        $this->assertSame($uetr, $header->getUetr());
    }

    #[Test]
    public function isStpReturnsTrueForStp(): void {
        $header = new UserHeader(['119' => 'STP']);

        $this->assertTrue($header->isStp());
    }

    #[Test]
    public function isStpReturnsFalseForNonStp(): void {
        $header = new UserHeader(['119' => 'OTHER']);

        $this->assertFalse($header->isStp());
    }

    #[Test]
    public function isStpReturnsFalseWhenNoValidationFlag(): void {
        $header = new UserHeader();

        $this->assertFalse($header->isStp());
    }

    #[Test]
    public function hasFieldReturnsTrueIfExists(): void {
        $header = new UserHeader(['108' => 'MUR123']);

        $this->assertTrue($header->hasField('108'));
    }

    #[Test]
    public function hasFieldReturnsFalseIfNotExists(): void {
        $header = new UserHeader();

        $this->assertFalse($header->hasField('108'));
    }

    #[Test]
    public function allCommonFields(): void {
        $header = new UserHeader([
            '103' => 'GPA',
            '108' => 'MUR123',
            '113' => 'URGP',
            '119' => 'STP',
            '121' => 'uetr-value',
            '165' => 'payment-release',
            '433' => 'sanctions-info',
            '434' => 'payment-controls'
        ]);

        $this->assertSame('GPA', $header->getServiceTypeId());
        $this->assertSame('MUR123', $header->getMur());
        $this->assertSame('URGP', $header->getBankingPriority());
        $this->assertSame('STP', $header->getValidationFlag());
        $this->assertSame('uetr-value', $header->getUetr());
        $this->assertSame('payment-release', $header->getField('165'));
        $this->assertSame('sanctions-info', $header->getField('433'));
        $this->assertSame('payment-controls', $header->getField('434'));
    }

    #[Test]
    public function toStringWithEmptyFields(): void {
        $header = new UserHeader();

        $this->assertSame('', (string)$header);
    }

    #[Test]
    public function toStringWithFields(): void {
        $header = new UserHeader([
            '108' => 'MUR12345678901234',
            '119' => 'STP'
        ]);

        $result = (string)$header;
        $this->assertStringStartsWith('{3:', $result);
        $this->assertStringContainsString('{108:MUR12345678901234}', $result);
        $this->assertStringContainsString('{119:STP}', $result);
        $this->assertStringEndsWith('}', $result);
    }

    #[Test]
    public function parseWithMultipleFields(): void {
        $raw = '{103:GPA}{108:MUR12345678901234}{119:STP}{121:uetr-value}';
        $header = UserHeader::parse($raw);

        $this->assertSame('GPA', $header->getServiceTypeId());
        $this->assertSame('MUR12345678901234', $header->getMur());
        $this->assertTrue($header->isStp());
        $this->assertSame('uetr-value', $header->getUetr());
    }

    #[Test]
    public function parseEmptyString(): void {
        $header = UserHeader::parse('');

        $this->assertEmpty($header->getFields());
        $this->assertNull($header->getMur());
    }
}