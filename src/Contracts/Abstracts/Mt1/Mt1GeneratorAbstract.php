<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt1GeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;

/**
 * Abstrakte Basisklasse für MT1xx-Generatoren.
 * 
 * Gemeinsame Methoden für die Generierung von SWIFT MT1-Nachrichten
 * (Zahlungsaufträge wie MT101, MT103).
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt1
 */
abstract class Mt1GeneratorAbstract {
    /**
     * Zeilentrennzeichen für SWIFT-Nachrichten.
     */
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generiert das Dokument als SWIFT MT-String.
     */
    abstract public function generate(object $document): string;

    /**
     * Formatiert eine Party für Option A (nur BIC).
     * 
     * @param string $tag Tag-Präfix (z.B. ':52A:', ':57A:')
     * @param Party $party Die Party
     */
    protected function formatPartyOptionA(string $tag, Party $party): string {
        return $tag . $party->toOptionA();
    }

    /**
     * Formatiert eine Party für Option K (Name + Adresse).
     * 
     * @param string $tag Tag-Präfix (z.B. ':50K:', ':59:')
     * @param Party $party Die Party
     */
    protected function formatPartyOptionK(string $tag, Party $party): string {
        return $tag . $party->toOptionK();
    }

    /**
     * Fügt eine optionale Party hinzu, wenn vorhanden.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param Party|null $party Die Party (optional)
     * @param string $bicTag Tag für BIC-only Format
     * @param string $fullTag Tag für vollständiges Format
     */
    protected function appendOptionalParty(
        array &$lines,
        ?Party $party,
        string $bicTag,
        string $fullTag
    ): void {
        if ($party === null) {
            return;
        }

        if ($party->isBicOnly()) {
            $lines[] = $this->formatPartyOptionA($bicTag, $party);
        } else {
            $lines[] = $this->formatPartyOptionK($fullTag, $party);
        }
    }

    /**
     * Verbindet die Zeilen zu einer SWIFT-Nachricht.
     * 
     * @param string[] $lines Die Zeilen
     * @return string Die formatierte SWIFT-Nachricht
     */
    protected function joinLines(array $lines): string {
        return implode(self::LINE_SEPARATOR, $lines);
    }
}
