<?php

namespace KjG_Ticketing\database;

use Exception;
use KjG_Ticketing\database\dto\Entrance;
use KjG_Ticketing\database\dto\Event;
use KjG_Ticketing\database\dto\ProcessAdditionalField;
use KjG_Ticketing\database\dto\Seat;
use KjG_Ticketing\database\dto\SeatingPlanArea;
use KjG_Ticketing\database\dto\Show;
use KjG_Ticketing\database\dto\TicketConfig;
use KjG_Ticketing\database\dto\TicketImageConfig;
use KjG_Ticketing\database\dto\TicketTextConfig;

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
        $sql        = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE event_id = %d",
            $this->event_id
        );
        $table_rows = $wpdb->get_results( $sql );

        return array_map( $dto_mapper, $table_rows );
    }

    /**
     * Inserts a new row into a table
     *
     * @param string $table_name
     * @param array $data see {@see \wpdb::insert()}, should not contain unique ID
     * @param ?array $format see {@see \wpdb::insert()}, should not contain unique ID
     *
     * @return int The new ID for the inserted row from the AUTO_INCREMENT column
     * @throws Exception
     */
    protected function insert_table_row( string $table_name, array $data, array $format = null ): int {
        global $wpdb;

        $data["event_id"] = $this->event_id;
        if ( $format != null ) {
            $format["event_id"] = "%d";
        }

        $rowsInserted = $wpdb->insert(
            $table_name,
            $data,
            $format,
        );
        if ( $rowsInserted === false ) {
            throw new Exception( "Could not insert new row into $table_name table" );
        }
        if ( $rowsInserted !== 1 ) {
            throw new Exception( "Tried to insert 1 row into $table_name table, but $rowsInserted rows were inserted" );
        }

        return $wpdb->insert_id;
    }

    /**
     * Deletes a single row in this table.
     *
     * @param string $table_name
     * @param array $where for documentation see {@see \wpdb::delete()}
     * @param ?array $where_format for documentation see {@see \wpdb::delete()}
     *
     * @throws Exception
     */
    protected function delete_table_row( string $table_name, array $where, array $where_format = null ): void {
        global $wpdb;

        $where["event_id"] = $this->event_id;
        if ( $where_format != null ) {
            $where_format["event_id"] = "%d";
        }

        $rowsDeleted = $wpdb->delete(
            $table_name,
            $where,
            $where_format
        );

        if ( ! $rowsDeleted ) {
            throw new Exception( "Could not delete entry from database table $table_name" );
        }
        if ( $rowsDeleted > 1 ) {
            throw new Exception( "During attempt of deleting 1 row from database table $table_name, $rowsDeleted rows were deleted" );
        }
    }

    // --------------------------------------------------

    public static abstract function get_table_name_events(): string;

    // This function is abstract because the table definitions differ between events and template events.
    // - The template events table does not have an "archived" column
    public abstract function get_event( bool $echoErrors = true ): Event|false;

    /**
     * @throws Exception
     */
    public function set_event( Event $event ): void {
        global $wpdb;

        $is_template = $this instanceof TemplateDatabaseConnection;
        $result      = $wpdb->update(
            static::get_table_name_events(),
            $event->to_DB_data( $is_template ),
            array( "id" => $this->event_id ),
            Event::to_DB_format( $is_template ),
            array( "id" => "%d" )
        );

        if ( $result === false ) {
            throw new Exception( "Could not write event with ID $this->event_id to database" );
        }
    }

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
            static::get_table_name_events(),
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

    /**
     * @throws Exception
     */
    public function set_ticket_template( string|null $ticket_template ): void {
        global $wpdb;
        $result = $wpdb->update(
            static::get_table_name_events(),
            array( "ticket_template" => $ticket_template ),
            array( "id" => $this->event_id )
        );

        if ( $result === false ) {
            throw new Exception( "Could not write ticket template to database" );
        }
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

    /**
     * @throws Exception
     */
    protected function set_ticket_text_config( ?TicketTextConfig $ticket_text_config, string $content ): void {
        global $wpdb;
        if ( $ticket_text_config == null ) {
            $this->delete_table_row(
                static::get_table_name_ticket_text_config(),
                array( "content" => $content, ),
                array( "content" => "%s" )
            );

            return;
        }

        $data   = $ticket_text_config->to_DB_data();
        $format = TicketTextConfig::to_DB_format();
        $result = $wpdb->update(
            static::get_table_name_ticket_text_config(),
            $data,
            array(
                "event_id" => $this->event_id,
                "content"  => $content,
            ),
            $format,
            array(
                "event_id" => "%d",
                "content"  => "%s",
            )
        );

        if ( $result === false ) {
            $data["content"]   = $content;
            $format["content"] = "%s";
            $this->insert_table_row(
                static::get_table_name_ticket_text_config(),
                $data,
                $format
            );
        }
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

    /**
     * @throws Exception
     */
    protected function set_ticket_image_config( ?TicketImageConfig $ticket_image_config, string $content ): void {
        global $wpdb;
        if ( $ticket_image_config == null ) {
            $this->delete_table_row(
                static::get_table_name_ticket_image_config(),
                array( "content" => $content, ),
                array( "content" => "%s" )
            );

            return;
        }

        $data   = $ticket_image_config->to_DB_data();
        $format = $ticket_image_config->to_DB_format();
        $result = $wpdb->update(
            static::get_table_name_ticket_image_config(),
            $data,
            array(
                "event_id" => $this->event_id,
                "content"  => $content,
            ),
            $format,
            array(
                "event_id" => "%d",
                "content"  => "%s",
            )
        );

        if ( $result === false ) {
            $data["content"]   = $content;
            $format["content"] = "%s";
            $this->insert_table_row(
                static::get_table_name_ticket_image_config(),
                $data,
                $format
            );
        }
    }

    public function get_ticket_config(): TicketConfig {
        $text_configs  = $this->get_ticket_text_configs();
        $image_configs = $this->get_ticket_image_configs();

        return TicketConfig::from_DB( $text_configs, $image_configs );
    }

    /**
     * @throws Exception
     */
    public function set_ticket_config( TicketConfig $ticket_config ): void {
        $this->set_ticket_image_config( $ticket_config->qr_code_config, TicketImageConfig::CONTENT_QR_CODE );
        $this->set_ticket_image_config( $ticket_config->seating_plan_config, TicketImageConfig::CONTENT_SEATING_PLAN );

        $this->set_ticket_text_config( $ticket_config->date_text_config, TicketTextConfig::CONTENT_DATE );
        $this->set_ticket_text_config( $ticket_config->time_text_config, TicketTextConfig::CONTENT_TIME );
        $this->set_ticket_text_config( $ticket_config->seat_block_text_config, TicketTextConfig::CONTENT_SEAT_BLOCK );
        $this->set_ticket_text_config( $ticket_config->seat_row_text_config, TicketTextConfig::CONTENT_SEAT_ROW );
        $this->set_ticket_text_config( $ticket_config->seat_number_text_config, TicketTextConfig::CONTENT_SEAT_NUMBER );
        $this->set_ticket_text_config( $ticket_config->price_text_config, TicketTextConfig::CONTENT_PRICE );
        $this->set_ticket_text_config( $ticket_config->payment_state_text_config, TicketTextConfig::CONTENT_PAYMENT_STATE );
        $this->set_ticket_text_config( $ticket_config->process_id_text_config, TicketTextConfig::CONTENT_PROCESS_ID );
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

    /**
     * @return int The new ID of the SeatingPlanArea (old ID is preserved in input SeatingPlanArea)
     * @throws Exception
     */
    public function insert_seating_plan_area( SeatingPlanArea $seating_plan_area ): int {
        return $this->insert_table_row(
            static::get_table_name_seating_plan_areas(),
            $seating_plan_area->to_DB_data(),
            $seating_plan_area->to_DB_format(),
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

    /**
     * @return int The new ID of the Entrance (old ID is preserved in input Entrance)
     * @throws Exception
     */
    public function insert_entrance( Entrance $entrance ): int {
        return $this->insert_table_row(
            static::get_table_name_entrances(),
            $entrance->to_DB_data(),
            $entrance->to_DB_format()
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
