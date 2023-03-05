<?php

namespace KjG_Ticketing\ticket_generation;

use KjG_Ticketing\database\dto\Color;
use KjG_Ticketing\database\dto\TicketImageConfig;
use KjG_Ticketing\database\dto\TicketSeatingPlanConfig;
use KjG_Ticketing\database\dto\TicketTextConfig;
use KjG_Ticketing\pdf\graphics\AnalyzedContentStream;
use KjG_Ticketing\pdf\graphics\operator\AbstractImageOperator;
use KjG_Ticketing\pdf\graphics\Point;
use KjG_Ticketing\pdf\graphics\state\GraphicsStateStack;
use KjG_Ticketing\pdf\graphics\TransformationMatrix;
use KjG_Ticketing\pdf\PdfDocument;

/**
 * This class provides an automatic configuration for the different objects in a ticket.
 */
class AutoConfig {
    /**
     * Calculated configuration for the QR-Code
     */
    public ?TicketImageConfig $qr_code_config = null;
    /**
     * Calculated configuration for the seating plan
     */
    public ?TicketSeatingPlanConfig $seating_plan_config = null;
    /**
     * Calculated configuration for the text with the date
     */
    public ?TicketTextConfig $date_text_config = null;
    /**
     * Calculated configuration for the text with the time
     */
    public ?TicketTextConfig $time_text_config = null;
    /**
     * Calculated configuration for the text with the seat block
     */
    public ?TicketTextConfig $seat_block_text_config = null;
    /**
     * Calculated configuration for the text with the seat row
     */
    public ?TicketTextConfig $seat_row_text_config = null;
    /**
     * Calculated configuration for the text with the seat number
     */
    public ?TicketTextConfig $seat_number_text_config = null;
    /**
     * Calculated configuration for the text with the price (for each ticket)
     */
    public ?TicketTextConfig $price_text_config = null;
    /**
     * Calculated configuration for the text with the payment state
     */
    public ?TicketTextConfig $payment_state_text_config = null;
    /**
     * Calculated configuration for the text with the process id
     */
    public ?TicketTextConfig $process_id_text_config = null;

    /**
     * The constructor directly executes the auto configuration algorithm.
     *
     * @param PdfDocument $pdfDocument Pass in the Ticket template
     *
     * @throws \Exception When the ticket template cannot get analyzed correctly.
     */
    public function __construct( PdfDocument $pdfDocument ) {
        // Start analysis
        if ( $pdfDocument->getPageList()->getPageCount() !== 1 ) {
            throw new \Exception( "Only PDF Documents with one Page can be used as Template" );
        }
        $page = $pdfDocument->getPageList()->getPage( 0 );
        $contentStream = $page->getContents();
        $analyzedContentStream = new AnalyzedContentStream(
            new GraphicsStateStack( new TransformationMatrix(), $page->getCropBox() ),
            $contentStream
        );


        // Detection of images
        $imageOperators = $analyzedContentStream->getImageOperators();
        $imageOperatorCount = count( $imageOperators );
        if ( $imageOperatorCount > 0 ) {

            // Find Positions for the seating plan
            $seating_plan_index = null;
            $seating_plan_value = INF;
            for ( $i = 0; $i < $imageOperatorCount; ++ $i ) {
                $imageOperator = $imageOperators[ $i ];
                $value = $this->calcDiagonalValue( $imageOperator->getLowerLeftCorner() );
                if ( $value < $seating_plan_value ) {
                    $seating_plan_value = $value;
                    $seating_plan_index = $i;
                }
            }

            // Save seating plan config
            if ( $seating_plan_index !== null ) {
                $seating_plan_operator = $imageOperators[ $seating_plan_index ];
                $deletion_range = $analyzedContentStream->getDeletableRangeForOperator( $seating_plan_operator->getOperatorNumber() );

                $this->seating_plan_config = new TicketSeatingPlanConfig();
                // operator number is calculated over ALL operators, not only image operators
                $this->seating_plan_config->pdf_operator_number = $seating_plan_operator->getOperatorNumber();
                $this->seating_plan_config->pdf_operator_name = $seating_plan_operator->getName();
                $this->seating_plan_config->pdf_resource_deletable = $seating_plan_operator->getName() !== "Inline Image"
                                                                     && ( $this->countImageOperatorsWithName( $imageOperators, $imageOperatorCount, $seating_plan_operator->getName() ) === 1 );
                $this->seating_plan_config->pdf_content_stream_start_operator_index = $deletion_range->getStartIndex();
                $this->seating_plan_config->pdf_content_stream_num_operators = $deletion_range->getLength();
                $this->seating_plan_config->lower_left_corner = $seating_plan_operator->getLowerLeftCorner();
                $this->seating_plan_config->lower_right_corner = $seating_plan_operator->getLowerRightCorner();
                $this->seating_plan_config->upper_left_corner = $seating_plan_operator->getUpperLeftCorner();
                $this->seating_plan_config->font = "Times-Roman";
                $this->seating_plan_config->font_size = 12;
                $this->seating_plan_config->seat_numbers_visible = false;
                $this->seating_plan_config->line_width = 3;
                $this->seating_plan_config->connect_entrance_arrows = true;
            }


            // Find positions for the QR-Code
            $cropBoxXCenter = ( $page->getCropBox()->getLowerLeftX() + $page->getCropBox()->getUpperRightX() ) / 2.0;
            $qrCodeIndex = null;
            $qrCodeValue = - INF;
            for ( $i = 0; $i < $imageOperatorCount; ++ $i ) {
                $imageOperator = $imageOperators[ $i ];
                $lowerLeftCorner = $imageOperator->getLowerLeftCorner();
                $lowerRightCorner = $imageOperator->getLowerRightCorner();
                $upperLeftCorner = $imageOperator->getUpperLeftCorner();
                $upperRightCorner = $imageOperator->getUpperRightCorner();
                if ( $lowerLeftCorner->x < $cropBoxXCenter
                     || $lowerRightCorner->x < $cropBoxXCenter
                     || $upperLeftCorner->x < $cropBoxXCenter
                     || $upperRightCorner->x < $cropBoxXCenter ) {
                    continue;
                } // Only images that are completely on the right side of the PDF page

                // Allow any twisting of the QR code
                $value = max(
                    $this->calcDiagonalValue( $lowerLeftCorner ),
                    $this->calcDiagonalValue( $lowerRightCorner ),
                    $this->calcDiagonalValue( $upperLeftCorner ),
                    $this->calcDiagonalValue( $upperRightCorner )
                );
                if ( $value > $qrCodeValue ) {
                    $qrCodeValue = $value;
                    $qrCodeIndex = $i;
                }
            }

            // If seating plan and QR code want to use the same imageObject
            if ( $qrCodeIndex === $seating_plan_index ) {
                $qrCodeIndex = null;
            }

            // save in QRCodeConfig
            if ( $qrCodeIndex !== null ) {
                $qrCodeOperator = $imageOperators[ $qrCodeIndex ];
                $deletion_range = $analyzedContentStream->getDeletableRangeForOperator( $qrCodeOperator->getOperatorNumber() );
                $this->qr_code_config = new TicketImageConfig();
                // operator number is calculated over ALL operators, not only image operators
                $this->qr_code_config->pdf_operator_number = $qrCodeOperator->getOperatorNumber();
                $this->qr_code_config->pdf_operator_name = $qrCodeOperator->getName();
                $this->qr_code_config->pdf_resource_deletable = $qrCodeOperator->getName() !== "Inline Image"
                                                                && ( $this->countImageOperatorsWithName( $imageOperators, $imageOperatorCount, $qrCodeOperator->getName() ) === 1 );
                $this->qr_code_config->pdf_content_stream_start_operator_index = $deletion_range->getStartIndex();
                $this->qr_code_config->pdf_content_stream_num_operators = $deletion_range->getLength();
                $this->qr_code_config->lower_left_corner = $qrCodeOperator->getLowerLeftCorner();
                $this->qr_code_config->lower_right_corner = $qrCodeOperator->getLowerRightCorner();
                $this->qr_code_config->upper_left_corner = $qrCodeOperator->getUpperLeftCorner();
            }
        }
        // Tidy up for debugging and performance
        unset( $seating_plan_value );
        unset( $seating_plan_index );
        unset( $seating_plan_operator );
        unset( $qrCodeValue );
        unset( $qrCodeIndex );
        unset( $qrCodeOperator );
        unset( $lowerLeftCorner );
        unset( $lowerRightCorner );
        unset( $upperLeftCorner );
        unset( $upperRightCorner );
        unset( $imageOperator );
        unset( $imageOperatorCount );
        unset( $imageOperators );
        unset( $deletion_range );


        // Detection of text
        $textOperators = $analyzedContentStream->getTextOperators();
        $textOperatorCount = count( $textOperators );
        /** @var array $textFindData Daten zu den einzelnen, gefundenen Texten. Der Inhalt des Textes ist gleichzeitig der Schlüssel in dem Array zu den Daten */
        $textFindData = [
            "date_text_config"        => [ "text" => "Datum" ],
            "time_text_config"        => [ "text" => "Uhrzeit" ],
            "seat_block_text_config"  => [ "text" => "Block" ],
            "seat_row_text_config"    => [ "text" => "Reihe" ],
            "seat_number_text_config" => [ "text" => "Platz" ],
            "price_text_config"       => [ "text" => "Beitrag" ],
            "process_id_text_config"  => [ "text" => "Buchungsnummer" ]
        ];
        $textFindDataCount = 0;
        $avgTextStartX = 0;
        $avgTextCenterX = 0;
        $avgTextEndX = 0;

        // find the textOperator for each text
        foreach ( $textFindData as $find_data_key => &$find_data_value ) {
            for ( $i = 0; $i < $textOperatorCount; ++ $i ) {
                $textOperatorText = $textOperators[ $i ]->getText();
                // Only TextOperators, which texts are at maximum 2 characters longer than the search text and where the search text is fully included
                if ( strlen( $textOperatorText ) > strlen( $find_data_value["text"] ) + 2 ) {
                    continue;
                }
                $strPos = strpos( $textOperatorText, $find_data_value["text"] );
                if ( $strPos === false || $strPos > 2 ) {
                    continue;
                }

                // If there are two or more TextOperators found with the same text, use the one that is nearer to the upper right corner.
                $startPos = $textOperators[ $i ]->getStartPos();
                $endPos = $textOperators[ $i ]->getEndPos();
                $centerPos = new Point( ( $startPos->x + $endPos->x ) / 2, ( $startPos->y + $endPos->y ) / 2 );
                if ( @$find_data_value["centerPos"] !== null && $this->calcDiagonalValue( $find_data_value["centerPos"] ) > $this->calcDiagonalValue( $centerPos ) ) {
                    continue; // already saved operator is nearer to the upper right corner
                }

                // save relevant data
                $find_data_value["startPos"] = $startPos;
                $find_data_value["endPos"] = $endPos;
                $find_data_value["centerPos"] = $centerPos;
                $find_data_value["fontSize"] = $textOperators[ $i ]->getFontSize();
            }

            // Check if data was found
            if ( @$find_data_value["centerPos"] !== null ) {
                ++ $textFindDataCount;
                $avgTextStartX += $find_data_value["startPos"]->x;
                $avgTextCenterX += $find_data_value["centerPos"]->x;
                $avgTextEndX += $find_data_value["endPos"]->x;
            } else {
                unset( $textFindData[ $find_data_key ] );
            }
        }

        // If no texts can be configured, we can already stop here
        if ( $textFindDataCount == 0 ) {
            return;
        }

        // Calculate mean value
        $avgTextStartX /= $textFindDataCount;
        $avgTextCenterX /= $textFindDataCount;
        $avgTextEndX /= $textFindDataCount;
        // Calculate varianz
        $varTextStartX = 0;
        $varTextCenterX = 0;
        $varTextEndX = 0;
        foreach ( $textFindData as &$find_data_value ) {
            $varTextStartX += ( $find_data_value["startPos"]->x - $avgTextStartX ) * ( $find_data_value["startPos"]->x - $avgTextStartX );
            $varTextCenterX += ( $find_data_value["centerPos"]->x - $avgTextCenterX ) * ( $find_data_value["centerPos"]->x - $avgTextCenterX );
            $varTextEndX += ( $find_data_value["endPos"]->x - $avgTextEndX ) * ( $find_data_value["endPos"]->x - $avgTextEndX );
        }
        if ( $varTextStartX < $varTextCenterX && $varTextStartX < $varTextEndX ) {
            // Align text to the left
            if ( @$textFindData["preisTextConfig"] !== null ) {
                $this->payment_state_text_config = $this->getDefaultTextConfig(
                    new Point( $avgTextStartX, $textFindData["preisTextConfig"]["startPos"]->y - $textFindData["preisTextConfig"]["fontSize"] * 3.0 ),
                    TicketTextConfig::ALIGNMENT_LEFT,
                    $textFindData["preisTextConfig"]["fontSize"]
                );
            }
            foreach ( $textFindData as $key => &$find_data_value ) {
                $this->$key = $this->getDefaultTextConfig(
                    new Point( $avgTextStartX, $find_data_value["startPos"]->y - $find_data_value["fontSize"] * 1.5 ),
                    TicketTextConfig::ALIGNMENT_LEFT,
                    $find_data_value["fontSize"]
                );
            }

        } else if ( $varTextCenterX < $varTextEndX ) {
            // Align text to the center
            if ( @$textFindData["preisTextConfig"] !== null ) {
                $this->payment_state_text_config = $this->getDefaultTextConfig(
                    new Point( $avgTextCenterX, $textFindData["preisTextConfig"]["centerPos"]->y - $textFindData["preisTextConfig"]["fontSize"] * 3.0 ),
                    TicketTextConfig::ALIGNMENT_CENTER,
                    $textFindData["preisTextConfig"]["fontSize"]
                );
            }
            foreach ( $textFindData as $key => &$find_data_value ) {
                $this->$key = $this->getDefaultTextConfig(
                    new Point( $avgTextCenterX, $find_data_value["centerPos"]->y - $find_data_value["fontSize"] * 1.5 ),
                    TicketTextConfig::ALIGNMENT_CENTER,
                    $find_data_value["fontSize"]
                );
            }

        } else {
            // Align text to the right
            if ( @$textFindData["preisTextConfig"] !== null ) {
                $this->payment_state_text_config = $this->getDefaultTextConfig(
                    new Point( $avgTextEndX, $textFindData["preisTextConfig"]["endPos"]->y - $textFindData["preisTextConfig"]["fontSize"] * 3.0 ),
                    TicketTextConfig::ALIGNMENT_RIGHT,
                    $textFindData["preisTextConfig"]["fontSize"]
                );
            }
            foreach ( $textFindData as $key => &$find_data_value ) {
                $this->$key = $this->getDefaultTextConfig(
                    new Point( $avgTextEndX, $find_data_value["endPos"]->y - $find_data_value["fontSize"] * 1.5 ),
                    TicketTextConfig::ALIGNMENT_RIGHT,
                    $find_data_value["fontSize"]
                );
            }
        }
    }

    /**
     * Calculates for a Point with its coordinates x and y a value
     * that indicates how far this Point is from the lower left corner of the PDF page.
     *
     * @param Point $point Point in Device Space
     *
     * @return float x + y
     */
    private function calcDiagonalValue( Point $point ): float {
        return $point->x + $point->y;
    }

    /**
     * Counts the ImageOperators with the given Name
     *
     * @param AbstractImageOperator[] $imageOperators All ImageOperators in the ContentStream
     * @param int $imageOperatorCount count($imageOperators)
     * @param string $name Name of an ImageOperator
     *
     * @return int
     */
    public static function countImageOperatorsWithName( array $imageOperators, int $imageOperatorCount, string $name ): int {
        $count = 0;
        for ( $i = 0; $i < $imageOperatorCount; ++ $i ) {
            if ( $imageOperators[ $i ]->getName() === $name ) {
                ++ $count;
            }
        }

        return $count;
    }

    /**
     * Returns the standard configuration for a text to be inserted into a ticket
     *
     * @param Point $position
     * @param string $alignment The alignment of the text can be "left", "center" or "right"
     * @param float $fontSize
     *
     * @return TicketTextConfig
     */
    private function getDefaultTextConfig( Point $position, string $alignment, float $fontSize ): TicketTextConfig {
        $text_config = new TicketTextConfig();
        $text_config->position = $position;
        $text_config->alignment = $alignment;
        $text_config->font = "Courier";
        $text_config->font_size = $fontSize;
        $text_config->color = new Color( 0, 0, 0 );

        return $text_config;
    }
}