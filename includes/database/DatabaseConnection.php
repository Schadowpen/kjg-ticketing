<?php

namespace KjG_Ticketing\database;

use KjG_Ticketing\database\dto\Process;
use KjG_Ticketing\database\dto\ProcessAdditionalEntry;
use KjG_Ticketing\database\dto\SeatState;

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

    // --------------------------------------------------

    protected static function get_table_name_processes(): string {
        return "kjg_ticketing_processes";
    }

    /**
     * @return Process[]
     */
    public function get_processes(): array {
        $additional_entries = $this->get_all_processes_additional_entries();

        return $this->get_table_contents(
            static::get_table_name_processes(),
            function ( $table_row ) use ( $additional_entries ) {
                return Process::from_DB( $table_row, $additional_entries );
            }
        );
    }

    // --------------------------------------------------

    protected static function get_table_name_process_additional_fields(): string {
        return "kjg_ticketing_process_additional_fields";
    }

    // --------------------------------------------------

    protected static function get_table_name_process_additional_entries(): string {
        return "kjg_ticketing_process_additional_entries";
    }

    /**
     * @return ProcessAdditionalEntry[]
     */
    protected function get_all_processes_additional_entries(): array {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM " . static::get_table_name_process_additional_entries() . " WHERE event_id = %d",
            $this->event_id
        );
        $table_rows = $wpdb->get_results( $sql );

        $fields = $this->get_process_additional_fields();
        $entries = array();
        foreach ( $table_rows as $row ) {
            foreach ( $fields as $field ) {
                if ( intval( $row->field_id ) === $field->id ) {
                    $entries[] = ProcessAdditionalEntry::from_DB( $row, $field->data_type );
                    break;
                }
            }
            // entries without matching fields are omitted.
        }

        return $entries;
    }

    // --------------------------------------------------

    protected static function get_table_name_shows(): string {
        return "kjg_ticketing_shows";
    }

    // --------------------------------------------------

    protected static function get_table_name_seat_state(): string {
        return "kjg_ticketing_seat_state";
    }

    /**
     * @return SeatState[]
     */
    public function get_seat_states(): array {
        return $this->get_table_contents(
            static::get_table_name_seat_state(),
            function ( $table_row ) {
                return SeatState::from_DB( $table_row );
            }
        );
    }
}