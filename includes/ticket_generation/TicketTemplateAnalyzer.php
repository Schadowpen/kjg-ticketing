<?php

namespace KjG_Ticketing\ticket_generation;

use KjG_Ticketing\database\AbstractDatabaseConnection;
use KjG_Ticketing\pdf\misc\StringReader;
use KjG_Ticketing\pdf\document\FontType0;
use KjG_Ticketing\pdf\document\Page;
use KjG_Ticketing\pdf\PdfDocument;
use KjG_Ticketing\pdf\PdfFile;

class TicketTemplateAnalyzer {
    /**
     * Ticket template
     * @var PdfDocument
     */
    protected PdfDocument $pdfDocument;

    /**
     * @param AbstractDatabaseConnection $databaseConnection Connection to database to get the ticket template
     *
     * @throws \Exception If the ticket template cannot be read or used as template.
     */
    public function __construct( AbstractDatabaseConnection $databaseConnection ) {
        $ticket_template = $databaseConnection->get_ticket_template();
        if ( $ticket_template === null ) {
            throw new \Exception( "Could not find ticket template in database" );
        }

        $this->pdfDocument = new PdfDocument( new PdfFile( new StringReader( $ticket_template ) ) );
        if ( $this->pdfDocument->getPageList()->getPageCount() !== 1 ) {
            throw new \Exception( "Only PDF Documents with one Page can be used as Template" );
        }
    }

    /**
     * Returns all fonts, that are usable during generation of a ticket without problems based on the template PDF.
     * @return string[] Names of the available fonts
     */
    public function getAvailableFonts(): array {
        // Those fonts out of the standard 14 fonts are always supported
        $fonts = [
            "Courier",
            "Courier-Bold",
            "Courier-Oblique",
            "Courier-BoldOblique",
            "Helvetica",
            "Helvetica-Bold",
            "Helvetica-Oblique",
            "Helvetica-BoldOblique",
            "Times-Roman",
            "Times-Bold",
            "Times-Italic",
            "Times-BoldItalic"
        ];

        // Get fonts out of the resource dictionaries which are supported
        $pageCount = $this->pdfDocument->getPageList()->getPageCount();
        for ( $i = 0; $i < $pageCount; ++ $i ) {
            foreach ( $this->pdfDocument->getPageList()->getPage( $i )->getResources()->getAllFonts() as $font ) {
                if ( $font instanceof FontType0 ) {
                    continue; // this font type is not supported
                }

                if ( ! in_array( $font->getBaseFontName(), $fonts ) ) {
                    $fonts[] = $font->getBaseFontName();
                }
            }
        }

        return $fonts;
    }

    /**
     * @return Page The single page of a PDF-file that is used as template for the tickets.
     */
    public function getTemplatePage(): Page {
        return $this->pdfDocument->getPageList()->getPage( 0 );
    }
}