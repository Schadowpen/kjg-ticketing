<?php

namespace KjG_Ticketing\database;

/**
 * A connection to the database for a single event
 */
class DatabaseConnection extends AbstractDatabaseConnection {

    public static function get_table_name_events(): string {
        return "kjg_ticketing_events";
    }

    protected static function get_table_name_ticket_text_config(): string {
        return "kjg_ticketing_ticket_text_config";
    }

    protected static function get_table_name_ticket_image_config(): string {
        return "kjg_ticketing_ticket_image_config";
    }

    protected static function get_table_name_seating_plan_areas(): string {
        return "kjg_ticketing_seating_plan_areas";
    }

    protected static function get_table_name_entrances(): string {
        return "kjg_ticketing_entrances";
    }

    protected static function get_table_name_seats(): string {
        return "kjg_ticketing_seats";
    }

    protected static function get_table_name_processes(): string {
        return "kjg_ticketing_processes";
    }

    protected static function get_table_name_process_additional_fields(): string {
        return "kjg_ticketing_process_additional_fields";
    }

    protected static function get_table_name_process_additional_entries(): string {
        return "kjg_ticketing_process_additional_entries";
    }

    protected static function get_table_name_shows(): string {
        return "kjg_ticketing_shows";
    }

    protected static function get_table_name_seat_state(): string {
        return "kjg_ticketing_seat_state";
    }
}