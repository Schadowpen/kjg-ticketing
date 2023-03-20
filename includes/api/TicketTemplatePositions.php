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
    public \stdClass $cropBox;
    public array $imageOperators;
    public array $textOperators;

    /**
     * @throws \Exception
     */
    public static function get( AbstractDatabaseConnection $dbc ): TicketTemplatePositions {
        $ticket_template_string = $dbc->get_ticket_template();
        if ( $ticket_template_string == null ) {
            throw new \Exception( "Error: Could not read ticket template from database" );
        }

        $pdfFile     = new PdfFile( new StringReader( $ticket_template_string ) );
        $pdfDocument = new PdfDocument( $pdfFile );
        if ( $pdfDocument->getPageList()->getPageCount() !== 1 ) {
            throw new \Exception( "Error: PDF file does not have exactly one page" );
        }

        $defaultPage           = $pdfDocument->getPageList()->getPage( 0 );
        $contentStream         = $defaultPage->getContents();
        $cropBox               = $defaultPage->getCropBox();
        $analyzedContentStream = new AnalyzedContentStream(
            new GraphicsStateStack( new TransformationMatrix(), $cropBox ),
            $contentStream
        );

        $ticket_template_positions          = new TicketTemplatePositions();
        $ticket_template_positions->cropBox = (object) [
            "lowerLeftX"  => $cropBox->getLowerLeftX(),
            "lowerLeftY"  => $cropBox->getLowerLeftY(),
            "upperRightX" => $cropBox->getUpperRightX(),
            "upperRightY" => $cropBox->getUpperRightY()
        ];
        foreach ( $analyzedContentStream->getImageOperators() as $imageOperator ) {
            $ticket_template_positions->imageOperators[] = [
                "operatorNumber"   => $imageOperator->getOperatorNumber(),
                "lowerLeftCorner"  => $imageOperator->getLowerLeftCorner(),
                "lowerRightCorner" => $imageOperator->getLowerRightCorner(),
                "upperLeftCorner"  => $imageOperator->getUpperLeftCorner(),
                "upperRightCorner" => $imageOperator->getUpperRightCorner(),
                "name"             => $imageOperator->getName()
            ];
        }
        foreach ( $analyzedContentStream->getTextOperators() as $textOperator ) {
            $ticket_template_positions->textOperators[] = [
                "operatorNumber" => $textOperator->getOperatorNumber(),
                "text"           => $textOperator->getTextUTF8(),
                "font"           => $textOperator->getFont(),
                "fontSize"       => $textOperator->getFontSize(),
                "startPoint"     => $textOperator->getStartPos(),
                "endPoint"       => $textOperator->getEndPos()
            ];
        }

        return $ticket_template_positions;
    }
}
