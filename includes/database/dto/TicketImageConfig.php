<?php

namespace KjG_Ticketing\database\dto;

use KjG_Ticketing\pdf\graphics\Point;

/**
 * Subclass for TicketConfig
 */
class TicketImageConfig {
    // possible values for content enum
    public const CONTENT_QR_CODE = "qr_code";
    public const CONTENT_SEATING_PLAN = "seating_plan";

    public ?int $pdf_operator_number = null;
    public ?string $pdf_operator_name = null;
    public ?bool $pdf_resource_deletable = null;
    public ?int $pdf_content_stream_start_operator_index = null;
    public ?int $pdf_content_stream_num_operators = null;
    public Point $lower_left_corner;
    public Point $lower_right_corner;
    public Point $upper_left_corner;
    public ?string $font = null;
    public ?float $font_size = null;
    public ?float $line_width = null;

    public static function from_DB( \stdClass $db_row ): TicketImageConfig {
        $ticket_image_config = new TicketImageConfig();
        $ticket_image_config->fill_from_DB( $db_row );

        return $ticket_image_config;
    }

    protected function fill_from_DB( \stdClass $db_row ): void {
        if ( $db_row->pdf_operator_number != null ) {
            $this->pdf_operator_number = intval( $db_row->pdf_operator_number );
            $this->pdf_operator_name = (string) $db_row->pdf_operator_name;
            $this->pdf_resource_deletable = intval( $db_row->pdf_resource_deletable ) === 1;
            $this->pdf_content_stream_start_operator_index = intval( $db_row->pdf_content_stream_start_operator_index );
            $this->pdf_content_stream_num_operators = intval( $db_row->pdf_content_stream_num_operators );
        }
        $this->lower_left_corner = new Point(
            floatval( $db_row->lower_left_corner_x ),
            floatval( $db_row->lower_left_corner_y )
        );
        $this->lower_right_corner = new Point(
            floatval( $db_row->lower_right_corner_x ),
            floatval( $db_row->lower_right_corner_y )
        );
        $this->upper_left_corner = new Point(
            floatval( $db_row->upper_left_corner_x ),
            floatval( $db_row->upper_left_corner_y )
        );
        if ( $db_row->font != null ) {
            $this->font = (string) $db_row->font;
            $this->font_size = floatval( $db_row->font_size );
        }
        $this->line_width = $db_row->line_width != null ? floatval( $db_row->line_width ) : null;
    }
}