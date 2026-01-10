<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\GeneratorAbstract as ISO20022GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;

/**
 * Abstract base class for Pain XML generators.
 * 
 * Provides common functionality for all pain.xxx generators:
 * - DOM-Dokument Initialisierung via ExtendedDOMDocumentBuilder
 * - Gemeinsame Strukturen (PartyIdentification, PostalAddress, etc.)
 * 
 * Extends GeneratorAbstract for common ISO 20022 functionality.
 * 
 * @package CommonToolkit\Contracts\Abstracts\ISO20022\Pain
 */
abstract class GeneratorAbstract extends ISO20022GeneratorAbstract {
    protected string $painNamespace;

    public function __construct(string $namespace) {
        $this->painNamespace = $namespace;
    }

    /**
     * Derives the XSD filename from the namespace.
     * e.g. 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.12' → 'pain.001.001.12.xsd'
     */
    protected function deriveSchemaFilename(): string {
        // Extract the last part after the last colon
        $parts = explode(':', $this->painNamespace);
        return end($parts) . '.xsd';
    }

    /**
     * Initialisiert das Pain-Dokument mit dem ExtendedDOMDocumentBuilder.
     * 
     * @param string $rootChildElement Name des Kind-Elements unter Document (z.B. 'CstmrCdtTrfInitn')
     */
    protected function initPainDocument(string $rootChildElement): void {
        $schemaLocation = $this->includeSchemaLocation ? $this->deriveSchemaFilename() : null;
        $this->initDocument($rootChildElement, $this->painNamespace, $schemaLocation);
    }

    /**
     * Adds a PartyIdentification (Pain-specific with PostalAddress).
     */
    protected function addPainPartyIdentification(string $elementName, PartyIdentification $party): void {
        $this->builder->addElement($elementName);

        // Nm (Name)
        $this->addChildIfNotEmpty('Nm', $party->getName());

        // PstlAdr (optional)
        if ($party->getPostalAddress() !== null) {
            $this->addPostalAddress($party->getPostalAddress());
        }

        // Id (BIC/LEI)
        if ($party->getBic() !== null || $party->getLei() !== null) {
            $this->builder
                ->addElement('Id')
                ->addElement('OrgId');

            $this->addChildIfNotEmpty('AnyBIC', $party->getBic());
            $this->addChildIfNotEmpty('LEI', $party->getLei());

            $this->builder
                ->end() // OrgId
                ->end(); // Id
        }

        // CtryOfRes
        if ($party->getCountryOfResidence() !== null) {
            $this->builder->addChild('CtryOfRes', $party->getCountryOfResidence()->value);
        }

        $this->builder->end(); // elementName
    }

    /**
     * Adds an AccountIdentification (Pain-specific).
     */
    protected function addPainAccountIdentification(string $elementName, AccountIdentification $account): void {
        $this->builder
            ->addElement($elementName)
            ->addElement('Id');

        if ($account->getIban() !== null) {
            $this->builder->addChild('IBAN', $this->escape($account->getIban()));
        } elseif ($account->getOther() !== null) {
            $this->builder
                ->addElement('Othr')
                ->addChild('Id', $this->escape($account->getOther()))
                ->end();
        }

        $this->builder->end(); // Id

        if ($account->getCurrency() !== null) {
            $this->builder->addChild('Ccy', $account->getCurrency()->value);
        }

        $this->builder->end(); // elementName
    }

    /**
     * Adds a FinancialInstitution (Pain-specific with LEI/MemberId).
     */
    protected function addPainFinancialInstitution(string $elementName, FinancialInstitution $institution): void {
        $this->builder
            ->addElement($elementName)
            ->addElement('FinInstnId');

        $this->addChildIfNotEmpty('BICFI', $institution->getBic());
        $this->addChildIfNotEmpty('Nm', $institution->getName());
        $this->addChildIfNotEmpty('LEI', $institution->getLei());

        if ($institution->getMemberId() !== null) {
            $this->builder
                ->addElement('ClrSysMmbId')
                ->addChild('MmbId', $this->escape($institution->getMemberId()))
                ->end();
        }

        $this->builder
            ->end() // FinInstnId
            ->end(); // elementName
    }

    /**
     * Adds RemittanceInformation (Pain-specific with structured reference).
     */
    protected function addPainRemittanceInformation(RemittanceInformation $info): void {
        $this->builder->addElement('RmtInf');

        // Unstructured (mehrere Zeilen möglich)
        foreach ($info->getUnstructured() as $line) {
            $this->builder->addChild('Ustrd', $this->escape($line));
        }

        if ($info->getCreditorReference() !== null) {
            $this->builder
                ->addElement('Strd')
                ->addElement('CdtrRefInf')
                ->addChild('Ref', $this->escape($info->getCreditorReference()))
                ->end() // CdtrRefInf
                ->end(); // Strd
        }

        $this->builder->end(); // RmtInf
    }

    /**
     * Adds an amount with currency attribute.
     */
    protected function addInstructedAmount(float $amount, string $currency): void {
        $this->builder
            ->addElement('InstdAmt', $this->formatAmount($amount))
            ->withAttribute('Ccy', $currency)
            ->end();
    }
}