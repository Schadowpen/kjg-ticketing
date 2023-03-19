<?php

namespace KjG_Ticketing\database;

use KjG_Ticketing\database\dto\Event;
use KjG_Ticketing\database\dto\SeatGroup;

/**
 * A connection to the template database for a single event
 */
class TemplateDatabaseConnection extends AbstractDatabaseConnection {

    public static function get_table_name_events(): string {
        return "kjg_ticketing_template_events";
    }

    public function get_event( bool $echoErrors = true ): Event|false {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT id, name, ticket_price, shipping_price, seating_plan_width, seating_plan_length, seating_plan_length_unit FROM "
            . static::get_table_name_events() . " WHERE id = %d",
            $this->event_id
        );
        $row = $wpdb->get_row( $sql, OBJECT );

        if ( ! $row ) {
            if ( $echoErrors ) {
                echo "Error: Could not read event from database\n";
            }

            return false;
        }

        return Event::from_DB( $row );
    }

    // --------------------------------------------------

    protected static function get_table_name_ticket_text_config(): string {
        return "kjg_ticketing_template_ticket_text_config";
    }

    protected static function get_table_name_ticket_image_config(): string {
        return "kjg_ticketing_template_ticket_image_config";
    }

    protected static function get_table_name_seating_plan_areas(): string {
        return "kjg_ticketing_template_seating_plan_areas";
    }

    protected static function get_table_name_entrances(): string {
        return "kjg_ticketing_template_entrances";
    }

    protected static function get_table_name_seats(): string {
        return "kjg_ticketing_template_seats";
    }

    // --------------------------------------------------

    protected static function get_table_name_seat_groups(): string {
        return "kjg_ticketing_template_seat_groups";
    }

    /**
     * @return SeatGroup[]
     */
    public function get_seat_groups(): array {
        return $this->get_table_contents(
            static::get_table_name_seat_groups(),
            function ( $table_row ) {
                return SeatGroup::from_DB( $table_row );
            }
        );
    }

    // --------------------------------------------------

    protected static function get_table_name_process_additional_fields(): string {
        return "kjg_ticketing_template_process_additional_fields";
    }

    protected static function get_table_name_shows(): string {
        return "kjg_ticketing_template_shows";
    }
}