<?php

namespace KjG_Ticketing\database;

use KjG_Ticketing\database\dto\Entrance;
use KjG_Ticketing\database\dto\Event;
use KjG_Ticketing\database\dto\ProcessAdditionalField;
use KjG_Ticketing\database\dto\Seat;
use KjG_Ticketing\database\dto\SeatingPlanArea;
use KjG_Ticketing\database\dto\Show;
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

    /**
     * Returns all rows in this table for $event_id
     *
     * @param string $table_name
     * @param callable $dto_mapper function that maps table row to one of the \Kjg_Ticketing\database\dto classes
     *
     * @return array An array of DTO objects. Type depends on $dto_mapper.
     */
    protected function get_table_contents( string $table_name, callable $dto_mapper ): array {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE event_id = %d",
            $this->event_id
        );
        $table_rows = $wpdb->get_results( $sql );

        return array_map( $dto_mapper, $table_rows );
    }

    // --------------------------------------------------

    public static abstract function get_table_name_events(): string;

    // This function is abstract because the table definitions differ between events and template events.
    // - The template events table does not have an "archived" column
    public abstract function get_event( bool $echoErrors = true ): Event|false;

    /**
     * Deletes the whole event associated with this databaseConnection.
     * Afterwards, this AbstractDatabaseConnection instance is no longer usable.
     *
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

    // --------------------------------------------------

    public function get_ticket_template(): string|null {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT ticket_template FROM " . static::get_table_name_events() . " WHERE id = %d",
            $this->event_id
        );

        return $wpdb->get_var( $sql );
    }

    // --------------------------------------------------

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

    public function get_ticket_config(): TicketConfig {
        $text_configs = $this->get_ticket_text_configs();
        $image_configs = $this->get_ticket_image_configs();

        return TicketConfig::from_DB( $text_configs, $image_configs );
    }

    // --------------------------------------------------

    protected static abstract function get_table_name_seating_plan_areas(): string;

    /**
     * @return SeatingPlanArea[]
     */
    public function get_seating_plan_areas(): array {
        return $this->get_table_contents(
            static::get_table_name_seating_plan_areas(),
            function ( $table_row ) {
                return SeatingPlanArea::from_DB( $table_row );
            }
        );
    }

    // --------------------------------------------------

    protected static abstract function get_table_name_entrances(): string;

    /**
     * @return Entrance[]
     */
    public function get_entrances(): array {
        return $this->get_table_contents(
            static::get_table_name_entrances(),
            function ( $table_row ) {
                return Entrance::from_DB( $table_row );
            }
        );
    }

    // --------------------------------------------------

    protected static abstract function get_table_name_seats(): string;

    /**
     * @return Seat[]
     */
    public function get_seats(): array {
        return $this->get_table_contents(
            static::get_table_name_seats(),
            function ( $table_row ) {
                return Seat::from_DB( $table_row );
            }
        );
    }

    // --------------------------------------------------

    protected static abstract function get_table_name_process_additional_fields(): string;

    /**
     * @return ProcessAdditionalField[]
     */
    public function get_process_additional_fields(): array {
        return $this->get_table_contents(
            static::get_table_name_process_additional_fields(),
            function ( $table_row ) {
                return ProcessAdditionalField::from_DB( $table_row );
            }
        );
    }

    // --------------------------------------------------

    protected static abstract function get_table_name_shows(): string;

    /**
     * @return Show[]
     */
    public function get_shows(): array {
        return $this->get_table_contents(
            static::get_table_name_shows(),
            function ( $table_row ) {
                return Show::from_DB( $table_row );
            }
        );
    }
}
