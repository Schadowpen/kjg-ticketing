<?php

namespace KjG_Ticketing\database;

use KjG_Ticketing\database\dto\Event;
use KjG_Ticketing\database\dto\TicketConfig;

/**
 * An abstract definition of a database connection that can be either a connection to a specific event
 * or a specific event template
 */
abstract class AbstractDatabaseConnection {

    /**
     * Id of the event that this DatabaseConnection manages
     * @var int
     */
    protected int $event_id;

    public function __construct( int $event_id ) {
        $this->event_id = $event_id;
    }

    public static abstract function get_table_name_events(): string;

    public function get_event( bool $echoErrors = true ): Event|false {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT id, name, archived, ticket_price, shipping_price, seating_plan_width, seating_plan_length, seating_plan_length_unit FROM "
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

    /**
     * Deletes the whole event associated with this databaseConnection.
     * Handle with care!
     *
     * @param bool $echoErrors If errors should be directly printed to the output via echo, default true
     *
     * @return bool if the deletion was successful
     */
    public function delete_event( bool $echoErrors = true ): bool {
        global $wpdb;
        $rowsDeleted = $wpdb->delete(
            self::get_table_name_events(),
            array(
                "id" => $this->event_id
            )
        );

        if ( ! $rowsDeleted ) {
            if ( $echoErrors ) {
                echo "Error: Could not delete event from database\n";
            }

            return false;
        }
        if ( $rowsDeleted > 1 && $echoErrors ) {
            echo "Error: During attempt of deleting 1 event, $rowsDeleted events were deleted";

            return false;
        }

        return true;
    }

    public function get_ticket_template(): string|null {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT ticket_template FROM " . static::get_table_name_events() . " WHERE id = %d",
            $this->event_id
        );

        return $wpdb->get_var( $sql );
    }

    protected static abstract function get_table_name_ticket_text_config(): string;

    protected function get_ticket_text_configs(): array {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM " . static::get_table_name_ticket_text_config() . " WHERE event_id = %d ORDER BY id",
            $this->event_id
        );

        return $wpdb->get_results( $sql );
    }

    protected static abstract function get_table_name_ticket_image_config(): string;

    protected function get_ticket_image_configs(): array {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM " . static::get_table_name_ticket_image_config() . " WHERE event_id = %d ORDER BY id",
            $this->event_id
        );

        return $wpdb->get_results( $sql );
    }

    public function get_ticket_config(): TicketConfig|false {
        $text_configs = $this->get_ticket_text_configs();
        $image_configs = $this->get_ticket_image_configs();

        return TicketConfig::from_DB( $text_configs, $image_configs );
    }

    protected static abstract function get_table_name_seating_plan_areas(): string;

    protected static abstract function get_table_name_entrances(): string;

    protected static abstract function get_table_name_seats(): string;

    protected static abstract function get_table_name_process_additional_fields(): string;

    protected static abstract function get_table_name_shows(): string;
}