<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtGeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Iso20022GeneratorAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use DOMDocument;
use DOMElement;

/**
 * Abstrakte Basisklasse für CAMT-XML-Generatoren.
 * 
 * Erweitert Iso20022GeneratorAbstract und stellt CAMT-spezifische Funktionalität bereit:
 * - CAMT-Namespace und Version-Handling
 * - Gemeinsame Strukturen (Balance, Account, Entry, etc.)
 * 
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Contracts\Abstracts\ISO20022\Camt
 */
abstract class CamtGeneratorAbstract extends Iso20022GeneratorAbstract {
    /**
     * Gibt den CAMT-Typ dieses Generators zurück.
     */
    abstract public function getCamtType(): CamtType;

    /**
     * Initialisiert das CAMT-Dokument mit Root-Element.
     * 
     * @param string $rootChildElement Name des Kind-Elements unter Document (z.B. 'BkToCstmrStmt')
     * @param CamtVersion $version CAMT-Version
     */
    protected function initCamtDocument(string $rootChildElement, CamtVersion $version): void {
        $namespace = $version->getNamespace($this->getCamtType());
        $this->initDocument($rootChildElement, $namespace);
    }

    /**
     * Fügt den Group Header hinzu.
     */
    protected function addGroupHeader(CamtDocumentAbstract $document, string $messageIdPrefix): self {
        $this->builder->addElement('GrpHdr');

        $msgId = $document->getMessageId() ?? $messageIdPrefix . $document->getCreationDateTime()->format('YmdHis');
        $this->builder
            ->addChild('MsgId', $this->escape($msgId))
            ->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        $this->builder->end(); // GrpHdr

        return $this;
    }

    /**
     * Fügt eine Account-Struktur für CAMT hinzu.
     */
    protected function addCamtAccount(CamtDocumentAbstract $document, bool $includeCurrency = true): self {
        $this->addAccountIdentificationFromString('Acct', $document->getAccountIdentifier(), $includeCurrency, $document->getCurrency());

        // Innerhalb des Acct-Elements Owner und Servicer hinzufügen
        // Wir müssen hier manuell in das Acct-Element navigieren
        // Da addAccountIdentificationFromString das Element bereits schließt, müssen wir es neu öffnen
        // Besser: Eine spezifische CAMT-Account-Methode

        return $this;
    }

    /**
     * Fügt eine vollständige CAMT-Account-Struktur hinzu (mit Owner und Servicer).
     */
    protected function addCamtAccountFull(CamtDocumentAbstract $document, bool $includeCurrency = true): self {
        $this->builder->addElement('Acct');

        // Account ID
        $this->builder->addElement('Id');
        if (\CommonToolkit\Helper\Data\BankHelper::shouldFormatAsIBAN($document->getAccountIdentifier())) {
            $this->builder->addChild('IBAN', $this->escape($document->getAccountIdentifier()));
        } else {
            $this->builder
                ->addElement('Othr')
                ->addChild('Id', $this->escape($document->getAccountIdentifier()))
                ->end();
        }
        $this->builder->end(); // Id

        // Currency
        if ($includeCurrency) {
            $this->builder->addChild('Ccy', $document->getCurrency()->value);
        }

        // Owner
        if ($document->getAccountOwner() !== null) {
            $this->builder
                ->addElement('Ownr')
                ->addChild('Nm', $this->escape($document->getAccountOwner()))
                ->end();
        }

        // Servicer
        if ($document->getServicerBic() !== null) {
            $this->builder
                ->addElement('Svcr')
                ->addElement('FinInstnId')
                ->addChild('BICFI', $this->escape($document->getServicerBic()))
                ->end() // FinInstnId
                ->end(); // Svcr
        }

        $this->builder->end(); // Acct

        return $this;
    }

    /**
     * Fügt ein Balance-Element hinzu.
     */
    protected function addBalance(Balance $balance): self {
        $this->builder->addElement('Bal');

        // Type
        $this->builder
            ->addElement('Tp')
            ->addElement('CdOrPrtry')
            ->addChild('Cd', $balance->getType())
            ->end() // CdOrPrtry
            ->end(); // Tp

        // Amount
        $this->addAmount('Amt', $balance->getAmount(), $balance->getCurrency());

        // CreditDebit Indicator
        $this->builder->addChild('CdtDbtInd', $this->formatCreditDebit($balance->getCreditDebit()));

        // Date
        $this->builder
            ->addElement('Dt')
            ->addChild('Dt', $this->formatDate($balance->getDate()))
            ->end();

        $this->builder->end(); // Bal

        return $this;
    }

    /**
     * Erstellt die Basis-Entry-Struktur (öffnet Ntry-Element).
     */
    protected function beginEntry(CamtTransactionAbstract $entry): self {
        $this->builder->addElement('Ntry');

        // NtryRef
        $this->addChildIfNotEmpty('NtryRef', $entry->getEntryReference());

        // Amount
        $this->addAmount('Amt', $entry->getAmount(), $entry->getCurrency());

        // CreditDebit Indicator
        $this->builder->addChild('CdtDbtInd', $this->formatCreditDebit($entry->getCreditDebit()));

        // Reversal Indicator
        if ($entry->isReversal()) {
            $this->builder->addChild('RvslInd', 'true');
        }

        // Status
        $this->builder
            ->addElement('Sts')
            ->addChild('Cd', $entry->getStatus() ?? 'BOOK')
            ->end();

        return $this;
    }

    /**
     * Fügt Booking- und Value-Date zu einem Entry hinzu.
     */
    protected function addEntryDates(CamtTransactionAbstract $entry, bool $useDateTimeForBooking = false): self {
        // BookgDt
        $this->builder->addElement('BookgDt');
        if ($useDateTimeForBooking) {
            $this->builder->addChild('DtTm', $this->formatDateTime($entry->getBookingDate()));
        } else {
            $this->builder->addChild('Dt', $this->formatDate($entry->getBookingDate()));
        }
        $this->builder->end();

        // ValDt
        if ($entry->getValutaDate() !== null) {
            $this->builder
                ->addElement('ValDt')
                ->addChild('Dt', $this->formatDate($entry->getValutaDate()))
                ->end();
        }

        return $this;
    }

    /**
     * Fügt einen proprietären BankTransactionCode hinzu.
     */
    protected function addBankTxCodeProprietary(string $code): self {
        $this->builder
            ->addElement('BkTxCd')
            ->addElement('Prtry')
            ->addChild('Cd', $this->escape($code))
            ->end() // Prtry
            ->end(); // BkTxCd

        return $this;
    }

    /**
     * Schließt das aktuelle Ntry-Element.
     */
    protected function endEntry(): self {
        $this->builder->end(); // Ntry
        return $this;
    }

    /**
     * Gibt das interne DOM-Dokument zurück.
     * 
     * @deprecated Nutze getDocument() für ExtendedDOMDocument
     */
    public function getDomDocument(): DOMDocument {
        return $this->builder->build();
    }

    // =========================================================================
    // LEGACY-METHODEN FÜR ABWÄRTSKOMPATIBILITÄT
    // =========================================================================

    /** @deprecated Nutze initCamtDocument() */
    protected function initDocumentLegacy(string $rootChildElement, CamtVersion $version): DOMElement {
        $this->initCamtDocument($rootChildElement, $version);
        return $this->builder->getCurrent();
    }

    /** @deprecated Nutze addChildIfNotEmpty() */
    protected function appendElementIfNotNull(DOMElement $parent, string $name, ?string $value): void {
        if ($value !== null) {
            $this->builder->addChild($name, $this->escape($value));
        }
    }

    /** @deprecated Nutze builder->addChild() */
    protected function createElement(string $name, ?string $value = null): DOMElement {
        $doc = $this->builder->build();
        $element = $doc->createElement($name);
        if ($value !== null) {
            $element->appendChild($doc->createTextNode($value));
        }
        return $element;
    }
}
