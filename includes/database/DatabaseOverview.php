<?php

namespace KjG_Ticketing\database;

use KjG_Ticketing\database\dto\Event;
use KjG_Ticketing\Options;

/**
 * This class gives an overview over all events stored in the database.
 * Please only instantiate one element of this class.
 *
 * TODO This class is a relict of the old times, where each event was stored in its own database.
 * It should be refactored, at least when support for multiple active events at a time is implemented. Therefore
 * - Naming convention should completely go over from databases to events
 * - distinguishing between events should move over from names to their IDs.
 * - the kjg_ticketing_current_event option should not be used any more.
 * - the "archived" field in the kjg_ticketing_events page should be used.
 */
class DatabaseOverview {

    /**
     * @return Event[]
     */
    public function getEvents(): array {
        global $wpdb;

        $events = $wpdb->get_results(
            "SELECT id, name, archived, ticket_price, shipping_price, seating_plan_width, seating_plan_length, seating_plan_length_unit FROM "
            . DatabaseConnection::get_table_name_events(),
            OBJECT
        );
        for ( $i = 0; $i < count( $events ); $i ++ ) {
            $events[ $i ] = Event::from_DB( $events[ $i ] );
        }

        return $events;
    }

    /**
     * @return Event[]
     */
    public function getTemplateEvents(): array {
        global $wpdb;

        $events = $wpdb->get_results(
            "SELECT id, name, archived, ticket_price, shipping_price, seating_plan_width, seating_plan_length, seating_plan_length_unit FROM "
            . TemplateDatabaseConnection::get_table_name_events(),
            OBJECT
        );
        for ( $i = 0; $i < count( $events ); $i ++ ) {
            $events[ $i ] = Event::from_DB( $events[ $i ] );
        }

        return $events;
    }

    /**
     * @return string[]
     */
    public function getArchivedDatabaseNames(): array {
        global $wpdb;

        $current_event_id = Options::get_current_event_id();
        if ( $current_event_id ) {
            $sql = $wpdb->prepare( "SELECT name FROM " . DatabaseConnection::get_table_name_events()
                                   . " WHERE id IS NOT %d", $current_event_id );
            $all_event_names = $wpdb->get_col( $sql );
        } else {
            $all_event_names = $wpdb->get_col( "SELECT name FROM " . DatabaseConnection::get_table_name_events() );
        }

        return $all_event_names;
    }

    /**
     * @return string[]
     */
    public function getTemplateDatabaseNames(): array {
        global $wpdb;

        return $wpdb->get_col( "SELECT name FROM " . TemplateDatabaseConnection::get_table_name_events() );
    }

    public function archiveDatabaseExists( string $archiveName ): bool {
        return in_array( $archiveName, $this->getArchivedDatabaseNames() );
    }

    public function templateDatabaseExists( string $templateName ): bool {
        return in_array( $templateName, $this->getTemplateDatabaseNames() );
    }

    public function getCurrentDatabaseConnection( bool $echoErrors = true ): DatabaseConnection|false {
        $event_id = Options::get_current_event_id();
        if ( ! $event_id ) {
            if ( $echoErrors ) {
                echo "Error: Current event does not exist\n";
            }

            return false;
        }

        return new DatabaseConnection( $event_id );
    }

    public function getArchiveDatabaseConnection( string $archiveName, bool $echoErrors = true ): DatabaseConnection|false {
        $eventsWithName = array_filter( $this->getEvents(), function ( $event ) use ( $archiveName ) {
            return $event->name == $archiveName;
        } );

        $numEventsWithName = count( $eventsWithName );
        if ( $numEventsWithName < 1 ) {
            if ( $echoErrors ) {
                echo "Error: Event \"$archiveName\" not found in database\n";
            }

            return false;
        }

        if ( $numEventsWithName > 1 ) {
            if ( $echoErrors ) {
                echo "Error: Multiple Events named \"$archiveName\" found in database (Total: $numEventsWithName)\n";
            }

            return false;
        }

        return new DatabaseConnection( $eventsWithName[0]->id );
    }

    public function getTemplateDatabaseConnection( string $templateName, bool $echoErrors = true ): false|TemplateDatabaseConnection {
        $eventsWithName = array_filter( $this->getTemplateEvents(), function ( $event ) use ( $templateName ) {
            return $event->name == $templateName;
        } );

        $numEventsWithName = count( $eventsWithName );
        if ( $numEventsWithName < 1 ) {
            if ( $echoErrors ) {
                echo "Error: Template Event \"$templateName\" not found in database\n";
            }

            return false;
        }

        if ( $numEventsWithName > 1 ) {
            if ( $echoErrors ) {
                echo "Error: Multiple Template Events named \"$templateName\" found in database (Total: $numEventsWithName)\n";
            }

            return false;
        }

        return new TemplateDatabaseConnection( $eventsWithName[0]->id );
    }

    /**
     * Creates a new template event and returns the according database connection
     *
     * @param string $templateName
     * @param bool $echoErrors If errors should be directly printed to the output via echo, default true
     *
     * @return TemplateDatabaseConnection|false false on error
     */
    public function createTemplateDatabaseConnection( string $templateName, bool $echoErrors = true ): TemplateDatabaseConnection|false {
        global $wpdb;

        if ( $this->templateDatabaseExists( $templateName ) ) {
            if ( $echoErrors ) {
                echo "Error: Database \"$templateName\" should be created but already exists\n";
            }

            return false;
        }

        $result = $wpdb->insert( "kjg_ticketing_template_events", array(
            "name" => $templateName,
        ) );
        if ( $result === false ) {
            if ( $echoErrors ) {
                echo "Error: Database \"$templateName\" cannot be created\n";
            }

            return false;
        }

        return new TemplateDatabaseConnection( $wpdb->insert_id );
    }

    public function fillTemplateDatabase( TemplateDatabaseConnection $dbc, bool $echoErrors ): bool {
        /*
         * TODO implement or check if columns without default value support being NULL by default
         * Left-over columns that have no default value:
         * events->seating_plan_length_unit  because this is language dependent
         * events->ticket_template  because this is a file
         */
        return true;
    }

    public function copyDatabase( AbstractDatabaseConnection $from, AbstractDatabaseConnection $to, bool $echoErrors = true ): bool {
        // TODO implement database copy
        if ( $echoErrors ) {
            echo "Error: DatabaseOverview::copyDatabase is not yet implemented\n";
        }

        return false;
    }

    public function deleteDatabase( AbstractDatabaseConnection $dbc, bool $echoErrors = true ): bool {
        return $dbc->delete_event( $echoErrors );
    }

    /**
     * Distributes a seat group into its individual seats
     *
     * @param object $seatGroup
     *
     * @return array All seats this seat group describes
     */
    public static function getSeatsFromSeatGroup( object $seatGroup ): array {
        $rows = array();
        $frontRowCharCode = ord( $seatGroup->row_front );
        $backRowCharCode = ord( $seatGroup->row_back );
        $charCodeJ = ord( "J" );
        if ( $frontRowCharCode < $backRowCharCode ) {
            for ( $c = $frontRowCharCode; $c <= $backRowCharCode; $c ++ ) {
                if ( $c != $charCodeJ ) {
                    $rows[] = chr( $c );
                }
            }
        } else {
            for ( $c = $frontRowCharCode; $c >= $backRowCharCode; $c -- ) {
                if ( $c != $charCodeJ ) {
                    $rows[] = chr( $c );
                }
            }
        }
        $groupLength = ( count( $rows ) - 1 ) * $seatGroup->row_distance;

        $columns = array();
        if ( $seatGroup->seat_number_left < $seatGroup->seat_number_right ) {
            for ( $p = $seatGroup->seat_number_left; $p <= $seatGroup->seat_number_right; $p ++ ) {
                $columns[] = $p;
            }
        } else {
            for ( $p = $seatGroup->seat_number_left; $p >= $seatGroup->seat_number_right; $p -- ) {
                $columns[] = $p;
            }
        }
        $groupWidth = ( count( $columns ) - 1 ) * $seatGroup->seat_distance;

        $seats = array();
        for ( $i = 0; $i < count( $rows ); $i ++ ) {
            for ( $k = 0; $k < count( $columns ); $k ++ ) {
                $internalX = - $groupWidth / 2 + $k * $seatGroup->seat_distance;
                $internalY = $groupLength / 2 - $i * $seatGroup->row_distance;
                $rotationRad = deg2rad( $seatGroup->rotation );
                $seats[] = (object) [
                    "event_id"    => $seatGroup->event_id,
                    "seat_block"  => $seatGroup->block,
                    "seat_row"    => $rows[ $i ],
                    "seat_number" => $columns[ $k ],
                    "position_x"  => cos( $rotationRad ) * $internalX - sin( $rotationRad ) * $internalY + $seatGroup->position_x,
                    "position_y"  => sin( $rotationRad ) * $internalX + cos( $rotationRad ) * $internalY + $seatGroup->position_y,
                    "rotation"    => $seatGroup->rotation,
                    "width"       => $seatGroup->seat_width,
                    "length"      => $seatGroup->seat_length,
                    "entrance_id" => @$seatGroup->entrance_id,
                ];
            }
        }

        return $seats;
    }

    /**
     * Returns all seats for this template event, including individual seats and seat groups
     *
     * @param TemplateDatabaseConnection $dbc
     * @param bool $echoErrors If errors should be directly printed to the output via echo, default true
     *
     * @return array|null null on error, array of seats otherwise
     */
    public static function getSeatsIncludingSeatGroups( TemplateDatabaseConnection $dbc, bool $echoErrors = true ): ?array {
        $seats = $dbc->get_seats( $echoErrors );
        if ( $seats === null ) {
            return null;
        }
        $seat_groups = $dbc->get_seat_groups( $echoErrors );
        if ( $seat_groups === null ) {
            return null;
        }

        foreach ( $seat_groups as $seat_group ) {
            $seats_from_seat_group = self::getSeatsFromSeatGroup( $seat_group );
            $seats = array_merge( $seats, $seats_from_seat_group );
        }

        return $seats;
    }
}