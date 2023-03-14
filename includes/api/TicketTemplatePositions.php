<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\AbstractDatabaseConnection;
use KjG_Ticketing\pdf\document\PdfRectangle;
use KjG_Ticketing\pdf\graphics\AnalyzedContentStream;
use KjG_Ticketing\pdf\graphics\operator\AbstractImageOperator;
use KjG_Ticketing\pdf\graphics\operator\AbstractTextOperator;
use KjG_Ticketing\pdf\graphics\state\GraphicsStateStack;
use KjG_Ticketing\pdf\graphics\TransformationMatrix;
use KjG_Ticketing\pdf\misc\StringReader;
use KjG_Ticketing\pdf\PdfDocument;
use KjG_Ticketing\pdf\PdfFile;

class TicketTemplatePositions {
    public PdfRectangle $cropBox;
    /**
     * @var AbstractImageOperator[]
     */
    public array $imageOperators;
    /**
     * @var AbstractTextOperator[]
     */
    public array $textOperators;

    /**
     * @throws \Exception
     */
    public static function get( AbstractDatabaseConnection $dbc ): TicketTemplatePositions {
        $ticket_template_string = $dbc->get_ticket_template();
        if ( $ticket_template_string == null ) {
            throw new \Exception( "Error: Could not read kartenVorlage from database" );
        }

        $pdfFile = new PdfFile( new StringReader( $ticket_template_string ) );
        $pdfDocument = new PdfDocument( $pdfFile );
        if ( $pdfDocument->getPageList()->getPageCount() !== 1 ) {
            throw new \Exception( "Error: PDF file does not have exactly one page" );
        }

        $defaultPage = $pdfDocument->getPageList()->getPage( 0 );
        $contentStream = $defaultPage->getContents();
        $cropBox = $defaultPage->getCropBox();
        $analyzedContentStream = new AnalyzedContentStream(
            new GraphicsStateStack( new TransformationMatrix(), $cropBox ),
            $contentStream
        );

        $ticket_template_positions = new TicketTemplatePositions();
        $ticket_template_positions->cropBox = $cropBox;
        foreach ( $analyzedContentStream->getImageOperators() as $imageOperator ) {
            $ticket_template_positions->imageOperators[] = $imageOperator;
        }
        foreach ( $analyzedContentStream->getTextOperators() as $text_operator ) {
            $ticket_template_positions->textOperators[] = $text_operator;
        }

        return $ticket_template_positions;
    }
}
