<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentInfoTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\DATEV;

use CommonToolkit\FinancialFormats\Entities\DATEV\DocumentInfo;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentInfoTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700);

        $this->assertSame(Category::Buchungsstapel, $info->getCategory());
        $this->assertSame(700, $info->getVersion());
        $this->assertNull($info->getDefinitionClass());
    }

    #[Test]
    public function constructorWithDefinitionClass(): void {
        $info = new DocumentInfo(
            Category::Buchungsstapel,
            700,
            'SomeDefinitionClass'
        );

        $this->assertSame('SomeDefinitionClass', $info->getDefinitionClass());
    }

    #[Test]
    public function getType(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700);

        $this->assertSame('Buchungsstapel', $info->getType());
    }

    #[Test]
    public function getCategoryNumber(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700);

        $this->assertSame(21, $info->getCategoryNumber());
    }

    #[Test]
    public function isSupportedWithDefinitionClass(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700, 'SomeClass');

        $this->assertTrue($info->isSupported());
    }

    #[Test]
    public function isSupportedWithoutDefinitionClass(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700);

        $this->assertFalse($info->isSupported());
    }

    #[Test]
    public function toStringSupported(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700, 'SomeClass');

        $string = (string) $info;

        $this->assertStringContainsString('Buchungsstapel', $string);
        $this->assertStringContainsString('700', $string);
        $this->assertStringContainsString('unterstützt', $string);
    }

    #[Test]
    public function toStringNotSupported(): void {
        $info = new DocumentInfo(Category::Buchungsstapel, 700);

        $string = (string) $info;

        $this->assertStringContainsString('nicht unterstützt', $string);
    }

    #[Test]
    public function differentCategories(): void {
        $debitorsCreditors = new DocumentInfo(Category::DebitorenKreditoren, 700);
        $glAccount = new DocumentInfo(Category::Sachkontenbeschriftungen, 700);

        $this->assertSame('Debitoren/Kreditoren', $debitorsCreditors->getType());
        $this->assertSame('Kontenbeschriftungen', $glAccount->getType());
    }
}
