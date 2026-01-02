<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Iso20022GeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022;

use CommonToolkit\Builders\ExtendedDOMDocumentBuilder;
use CommonToolkit\Entities\XML\ExtendedDOMDocument;
use CommonToolkit\Enums\CountryCode;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PostalAddress;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;
use CommonToolkit\Helper\Data\BankHelper;
use DateTimeInterface;

/**
 * Abstrakte Basisklasse für ISO 20022 XML-Generatoren.
 * 
 * Nutzt ExtendedDOMDocumentBuilder für eine optimierte, fluent XML-Generierung.
 * Stellt gemeinsame Funktionalität für CAMT und Pain Generatoren bereit:
 * - Fluent Builder-API für DOM-Erstellung
 * - Gemeinsame Strukturen (Party, Account, Amount, etc.)
 * - Formatierungs-Hilfsmethoden
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022
 */
abstract class Iso20022GeneratorAbstract {
    protected const XSI_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';

    protected ExtendedDOMDocumentBuilder $builder;
    protected string $namespace;

    // =========================================================================
    // INITIALISIERUNG
    // =========================================================================

    /**
     * Initialisiert das Dokument mit Root-Element und Namespace.
     * 
     * @param string $rootChildElement Name des Kind-Elements unter Document (z.B. 'BkToCstmrStmt')
     * @param string $namespace ISO 20022 Namespace
     * @param string|null $schemaLocation Optionale Schema-Location
     * @return ExtendedDOMDocumentBuilder Der Builder zur weiteren Verwendung
     */
    protected function initDocument(string $rootChildElement, string $namespace, ?string $schemaLocation = null): ExtendedDOMDocumentBuilder {
        $this->namespace = $namespace;

        $this->builder = ExtendedDOMDocumentBuilder::create('Document')
            ->withNamespace($namespace);

        if ($schemaLocation !== null) {
            $this->builder->withAttributeNS(
                self::XSI_NAMESPACE,
                'xsi:schemaLocation',
                $namespace . ' ' . $schemaLocation
            );
        }

        // Haupt-Element hinzufügen und hineinnavigieren
        $this->builder->addElement($rootChildElement);

        return $this->builder;
    }

    /**
     * Gibt das generierte XML als String zurück.
     */
    protected function getXml(): string {
        return $this->builder->toString();
    }

    /**
     * Gibt das ExtendedDOMDocument zurück.
     */
    protected function getDocument(): ExtendedDOMDocument {
        return $this->builder->build();
    }

    // =========================================================================
    // FORMATIERUNGS-HILFSMETHODEN
    // =========================================================================

    /**
     * Formatiert einen Betrag für XML-Ausgabe.
     */
    protected function formatAmount(float $amount): string {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Formatiert ein Datum für XML-Ausgabe (ISO 8601 Date).
     */
    protected function formatDate(DateTimeInterface $date): string {
        return $date->format('Y-m-d');
    }

    /**
     * Formatiert ein DateTime für XML-Ausgabe (ISO 8601 DateTime mit Millisekunden und Timezone).
     */
    protected function formatDateTime(DateTimeInterface $dateTime): string {
        return $dateTime->format('Y-m-d\TH:i:s.vP');
    }

    /**
     * Formatiert einen CreditDebit-Indikator.
     */
    protected function formatCreditDebit(CreditDebit $creditDebit): string {
        return $creditDebit === CreditDebit::CREDIT ? 'CRDT' : 'DBIT';
    }

    /**
     * Escapt HTML-Sonderzeichen für XML.
     */
    protected function escape(string $value): string {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    // =========================================================================
    // GEMEINSAME STRUKTUREN
    // =========================================================================

    /**
     * Fügt ein Amount-Element mit Currency-Attribut hinzu.
     * 
     * Beispiel: <Amt Ccy="EUR">1234.56</Amt>
     */
    protected function addAmount(string $elementName, float $amount, CurrencyCode $currency): self {
        $this->builder
            ->addElement($elementName, $this->formatAmount($amount))
            ->withAttribute('Ccy', $currency->value)
            ->end();

        return $this;
    }

    /**
     * Fügt ein Element nur hinzu wenn der Wert nicht null/leer ist.
     */
    protected function addChildIfNotEmpty(string $name, ?string $value): self {
        $this->builder->addChildIfNotEmpty($name, $value !== null ? $this->escape($value) : null);
        return $this;
    }

    /**
     * Fügt eine Account-Identifikation hinzu (IBAN oder Other).
     */
    protected function addAccountIdentification(string $elementName, AccountIdentification $account): self {
        $this->builder->addElement($elementName);

        $this->builder->addElement('Id');

        if ($account->getIban() !== null) {
            $this->builder->addChild('IBAN', $account->getIban());
        } elseif ($account->getOther() !== null) {
            $this->builder
                ->addElement('Othr')
                ->addChild('Id', $account->getOther())
                ->end();
        }

        $this->builder->end(); // Id

        if ($account->getCurrency() !== null) {
            $this->builder->addChild('Ccy', $account->getCurrency()->value);
        }

        $this->builder->end(); // elementName

        return $this;
    }

    /**
     * Fügt eine Account-Identifikation mit automatischer IBAN-Erkennung hinzu.
     */
    protected function addAccountIdentificationFromString(string $elementName, string $identifier, bool $includeCurrency = false, ?CurrencyCode $currency = null): self {
        $this->builder
            ->addElement($elementName)
            ->addElement('Id');

        if (BankHelper::shouldFormatAsIBAN($identifier)) {
            $this->builder->addChild('IBAN', $this->escape($identifier));
        } else {
            $this->builder
                ->addElement('Othr')
                ->addChild('Id', $this->escape($identifier))
                ->end();
        }

        $this->builder->end(); // Id

        if ($includeCurrency && $currency !== null) {
            $this->builder->addChild('Ccy', $currency->value);
        }

        $this->builder->end(); // elementName

        return $this;
    }

    /**
     * Fügt eine PartyIdentification hinzu.
     */
    protected function addPartyIdentification(string $elementName, PartyIdentification $party): self {
        $this->builder->addElement($elementName);

        // Name
        $this->addChildIfNotEmpty('Nm', $party->getName());

        // PostalAddress
        if ($party->getPostalAddress() !== null) {
            $this->addPostalAddress($party->getPostalAddress());
        }

        // Id (BIC/LEI/OrganisationId)
        if ($party->getBic() !== null || $party->getLei() !== null || $party->getOrganisationId() !== null) {
            $this->builder
                ->addElement('Id')
                ->addElement('OrgId');

            $this->addChildIfNotEmpty('AnyBIC', $party->getBic());
            $this->addChildIfNotEmpty('LEI', $party->getLei());

            if ($party->getOrganisationId() !== null) {
                $this->builder
                    ->addElement('Othr')
                    ->addChild('Id', $this->escape($party->getOrganisationId()))
                    ->end();
            }

            $this->builder
                ->end() // OrgId
                ->end(); // Id
        }

        // CountryOfResidence
        if ($party->getCountryOfResidence() !== null) {
            $this->builder->addChild('CtryOfRes', $party->getCountryOfResidence()->value);
        }

        $this->builder->end(); // elementName

        return $this;
    }

    /**
     * Fügt eine PostalAddress hinzu.
     */
    protected function addPostalAddress(PostalAddress $address): self {
        $this->builder->addElement('PstlAdr');

        $this->addChildIfNotEmpty('StrtNm', $address->getStreetName());
        $this->addChildIfNotEmpty('BldgNb', $address->getBuildingNumber());
        $this->addChildIfNotEmpty('PstCd', $address->getPostCode());
        $this->addChildIfNotEmpty('TwnNm', $address->getTownName());

        if ($address->getCountry() !== null) {
            $this->builder->addChild('Ctry', $address->getCountry()->value);
        }

        foreach ($address->getAddressLines() as $line) {
            $this->builder->addChild('AdrLine', $this->escape($line));
        }

        $this->builder->end(); // PstlAdr

        return $this;
    }

    /**
     * Fügt eine FinancialInstitution hinzu.
     */
    protected function addFinancialInstitution(string $elementName, FinancialInstitution $institution): self {
        $this->builder
            ->addElement($elementName)
            ->addElement('FinInstnId');

        $this->addChildIfNotEmpty('BICFI', $institution->getBic());
        $this->addChildIfNotEmpty('Nm', $institution->getName());
        $this->addChildIfNotEmpty('LEI', $institution->getLei());

        if ($institution->getMemberId() !== null) {
            $this->builder
                ->addElement('ClrSysMmbId')
                ->addChild('MmbId', $institution->getMemberId())
                ->end();
        }

        $this->builder
            ->end() // FinInstnId
            ->end(); // elementName

        return $this;
    }

    /**
     * Fügt eine PaymentIdentification hinzu.
     */
    protected function addPaymentIdentification(PaymentIdentification $paymentId): self {
        $this->builder->addElement('PmtId');

        $this->addChildIfNotEmpty('InstrId', $paymentId->getInstructionId());
        $this->builder->addChild('EndToEndId', $paymentId->getEndToEndId());
        $this->addChildIfNotEmpty('UETR', $paymentId->getUetr());

        $this->builder->end(); // PmtId

        return $this;
    }

    /**
     * Fügt RemittanceInformation hinzu.
     */
    protected function addRemittanceInformation(RemittanceInformation $remittance): self {
        $this->builder->addElement('RmtInf');

        // Unstructured
        foreach ($remittance->getUnstructured() as $line) {
            $this->builder->addChild('Ustrd', $this->escape($line));
        }

        // Structured (Creditor Reference)
        if ($remittance->getCreditorReference() !== null) {
            $this->builder
                ->addElement('Strd')
                ->addElement('CdtrRefInf')
                ->addChild('Ref', $remittance->getCreditorReference())
                ->end() // CdtrRefInf
                ->end(); // Strd
        }

        $this->builder->end(); // RmtInf

        return $this;
    }

    /**
     * Fügt einen BIC-basierten Agent hinzu.
     */
    protected function addAgentByBic(string $elementName, string $bic): self {
        $this->builder
            ->addElement($elementName)
            ->addElement('FinInstnId')
            ->addChild('BICFI', $this->escape($bic))
            ->end() // FinInstnId
            ->end(); // elementName

        return $this;
    }
}
