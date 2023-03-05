<?php

namespace KjG_Ticketing\pdf\document;

use KjG_Ticketing\pdf\indirectObject\PdfStream;
use KjG_Ticketing\pdf\object\PdfDictionary;
use KjG_Ticketing\pdf\object\PdfIndirectReference;
use KjG_Ticketing\pdf\PdfFile;

/**
 * Kapselt einen einzelnen Content Stream.
 * @package KjG_Ticketing\pdf\document
 */
class ContentStream extends AbstractDocumentStream {
    /**
     * Ressourcen, die von dem ContentStream genutzt werden
     * @var ResourceDictionary
     */
    protected $resourceDictionary;

    public static function objectType(): ?string {
        return null;
    }

    public static function objectSubtype(): ?string {
        return null;
    }

    /**
     * ContentStream constructor.
     *
     * @param PdfIndirectReference|PdfStream $pdfObject Referenz auf oder Stream mit dem Content
     * @param PdfFile $pdfFile PdfFile, in welchem der ContentStream genutzt wird
     * @param ResourceDictionary $resourceDictionary Ressourcen, die im Content Stream genutzt werden
     *
     * @throws \Exception Wenn die übergebenen Parameter keinen ContentStream beschreiben
     */
    public function __construct( $pdfObject, PdfFile $pdfFile, ResourceDictionary $resourceDictionary ) {
        parent::__construct( $pdfObject, $pdfFile );
        $this->resourceDictionary = $resourceDictionary;
    }

    /**
     * @return ResourceDictionary
     */
    public function getResourceDictionary(): ResourceDictionary {
        return $this->resourceDictionary;
    }
}