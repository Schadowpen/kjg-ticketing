<?php

namespace KjG_Ticketing\database;

use KjG_Ticketing\database\dto\SeatGroup;

/**
 * A connection to the template database for a single event
 */
class TemplateDatabaseConnection extends AbstractDatabaseConnection {

    public static function get_table_name_events(): string {
        return "kjg_ticketing_template_events";
    }

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