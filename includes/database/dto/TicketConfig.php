<?php

namespace KjG_Ticketing\database\dto;

use stdClass;

class TicketConfig {
    public ?TicketImageConfig $qr_code_config;
    public ?TicketSeatingPlanConfig $seating_plan_config;
    public ?TicketTextConfig $date_text_config;
    public ?TicketTextConfig $time_text_config;
    public ?TicketTextConfig $seat_block_text_config;
    public ?TicketTextConfig $seat_row_text_config;
    public ?TicketTextConfig $seat_number_text_config;
    public ?TicketTextConfig $price_text_config;
    public ?TicketTextConfig $payment_state_text_config;
    public ?TicketTextConfig $process_id_text_config;

    /**
     * @param stdClass[] $text_config_db_rows All rows from the ticket_text_config table with the same event_id.
     * Please query database with "ORDER BY id" to get stable results when duplicate entries exist.
     * @param stdClass[] $image_config_db_rows All rows from the ticket_image_config table with the same event_id.
     * Please query database with "ORDER BY id" to get stable results when duplicate entries exist.
     *
     * @return TicketConfig
     */
    public static function from_DB(
        array $text_config_db_rows,
        array $image_config_db_rows
    ): TicketConfig {
        $ticket_config = new TicketConfig();

        foreach ( $text_config_db_rows as $text_config_row ) {
            $text_config = TicketTextConfig::from_DB( $text_config_row );
            $content = (string) $text_config_row->content;
            switch ( $content ) {
                case TicketTextConfig::CONTENT_DATE:
                    $ticket_config->date_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_TIME:
                    $ticket_config->time_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_SEAT_BLOCK:
                    $ticket_config->seat_block_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_SEAT_ROW:
                    $ticket_config->seat_row_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_SEAT_NUMBER:
                    $ticket_config->seat_number_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_PRICE:
                    $ticket_config->price_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_PAYMENT_STATE:
                    $ticket_config->payment_state_text_config = $text_config;
                    break;
                case TicketTextConfig::CONTENT_PROCESS_ID:
                    $ticket_config->process_id_text_config = $text_config;
                    break;
            }
        }

        foreach ( $image_config_db_rows as $image_config_row ) {
            $content = (string) $image_config_row->content;
            switch ( $content ) {
                case TicketImageConfig::CONTENT_QR_CODE:
                    $ticket_config->qr_code_config = TicketImageConfig::from_DB( $image_config_row );
                    break;
                case TicketImageConfig::CONTENT_SEATING_PLAN:
                    $ticket_config->seating_plan_config = TicketSeatingPlanConfig::from_DB( $image_config_row );
                    break;
            }
        }

        return $ticket_config;
    }
}