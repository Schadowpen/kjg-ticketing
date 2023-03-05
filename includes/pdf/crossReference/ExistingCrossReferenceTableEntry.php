<?php

namespace KjG_Ticketing\pdf\crossReference;

use KjG_Ticketing\pdf\indirectObject\IndirectObjectParser;
use KjG_Ticketing\pdf\indirectObject\PdfIndirectObject;
use KjG_Ticketing\pdf\indirectObject\PdfStream;
use KjG_Ticketing\pdf\PdfFile;

/**
 * Bereits existierender Eintrag in der CrossReferenceTable, verweist auf ein Indirect Objekt in der PdfFile
 * @package KjG_Ticketing\pdf\crossReference
 */
class ExistingCrossReferenceTableEntry extends CrossReferenceTableEntry {
    /**
     * PDF-Datei, aus welcher dieser Eintrag stammt
     * @var PdfFile
     */
    private $pdfFile;

    /**
     * Erzeugt einen neuen Eintrag einer existierenden PDF-Datei.
     * @inheritdoc
     *
     * @param $pdfFile PdfFile PDF-Datei, in der dieser Eintrag zu finden ist.
     *
     */
    public function __construct( int $objNumber, int $generationNumber, bool $inUse, int $byteOffset, PdfFile $pdfFile ) {
        parent::__construct( $objNumber, $generationNumber, $inUse, $byteOffset );
        $this->pdfFile = $pdfFile;
    }

    /**
     * Gibt das in dem Eintrag referenzierte Objekt zurück.
     * Sollte das Objekt noch nicht bekannt sein, wird es aus der PDF-Datei extrahiert.
     * Sollte dieser Eintrag als frei markiert sein, wird nichts zurückgegeben.
     * @return PdfIndirectObject|PdfStream|null
     * @throws \Exception Wenn das Referenzierte Objekt nicht geliefert werden kann
     */
    public function getReferencedObject() {
        if ( $this->referencedObject == null && $this->inUse ) {
            $this->referencedObject = IndirectObjectParser::parse( $this->pdfFile, $this->byteOffset );
        }

        return $this->referencedObject;
    }

    /**
     * Gibt die PDF-Datei zurück, aus der dieser Eintrag stammt
     * @return PdfFile
     */
    public function getPdfFile(): PdfFile {
        return $this->pdfFile;
    }


}