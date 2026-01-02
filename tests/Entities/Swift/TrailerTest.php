<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TrailerTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\Swift;

use CommonToolkit\FinancialFormats\Entities\Swift\Trailer;
use Tests\Contracts\BaseTestCase;

class TrailerTest extends BaseTestCase {
    public function testConstructorWithEmptyFields(): void {
        $trailer = new Trailer();

        $this->assertEmpty($trailer->getFields());
    }

    public function testConstructorWithFields(): void {
        $fields = [
            'CHK' => '123456789ABC',
            'TNG' => ''
        ];

        $trailer = new Trailer($fields);

        $this->assertSame($fields, $trailer->getFields());
    }

    public function testGetField(): void {
        $trailer = new Trailer([
            'CHK' => '123456789ABC',
            'PDE' => '1348120811BANKFRPPAXXX2222123456'
        ]);

        $this->assertSame('123456789ABC', $trailer->getField('CHK'));
        $this->assertSame('1348120811BANKFRPPAXXX2222123456', $trailer->getField('PDE'));
        $this->assertNull($trailer->getField('TNG'));
    }

    public function testGetChecksum(): void {
        $trailer = new Trailer(['CHK' => 'ABCDEF123456']);

        $this->assertSame('ABCDEF123456', $trailer->getChecksum());
    }

    public function testGetChecksumReturnsNullWhenMissing(): void {
        $trailer = new Trailer();

        $this->assertNull($trailer->getChecksum());
    }

    public function testIsTraining(): void {
        $trainingTrailer = new Trailer(['TNG' => '']);
        $normalTrailer = new Trailer();

        $this->assertTrue($trainingTrailer->isTraining());
        $this->assertFalse($normalTrailer->isTraining());
    }

    public function testIsPossibleDuplicateEmission(): void {
        $pdeTrailer = new Trailer(['PDE' => '1348120811BANKFRPP']);
        $normalTrailer = new Trailer();

        $this->assertTrue($pdeTrailer->isPossibleDuplicateEmission());
        $this->assertFalse($normalTrailer->isPossibleDuplicateEmission());
    }

    public function testIsPossibleDuplicateMessage(): void {
        $pdmTrailer = new Trailer(['PDM' => '']);
        $normalTrailer = new Trailer();

        $this->assertTrue($pdmTrailer->isPossibleDuplicateMessage());
        $this->assertFalse($normalTrailer->isPossibleDuplicateMessage());
    }
}
