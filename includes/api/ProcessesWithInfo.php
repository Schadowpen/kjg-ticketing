<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\DatabaseConnection;
use KjG_Ticketing\database\dto\Show;

class ProcessesWithInfo {

    /**
     * @param DatabaseConnection $dbc
     *
     * @return array An array of processes with additional info in ->num_seats and ->shows
     */
    public static function get( DatabaseConnection $dbc ): array {
        // read database
        $processes = $dbc->get_processes();
        $seat_states = $dbc->get_seat_states();
        $shows = $dbc->get_shows();

        // Add extra information for each process
        for ( $i = 0; $i < count( $processes ); $i ++ ) {
            $processes[ $i ]->num_seats = 0;
            $processes[ $i ]->shows = [];

            for ( $k = 0; $k < count( $seat_states ); $k ++ ) {
                if ( @$seat_states[ $k ]->process_id == $processes[ $i ]->id ) {
                    $processes[ $i ]->num_seats ++;

                    $show_exists = false;
                    for ( $j = 0; $j < count( $processes[ $i ]->shows ); $j ++ ) {
                        if ( $processes[ $i ]->shows[ $j ]->id == $seat_states[ $k ]->show_id ) {
                            $show_exists = true;
                        }
                    }
                    if ( ! $show_exists ) {
                        $show = self::get_show( $shows, $seat_states[ $k ]->show_id );
                        $processes[ $i ]->shows[] = $show;
                    }
                }
            }
        }

        return $processes;
    }

    /**
     * @param Show[] $shows
     * @param int $show_id
     *
     * @return Show
     */
    private static function get_show( array $shows, int $show_id ): Show {
        foreach ( $shows as $show ) {
            if ( $show->id === $show_id ) {
                return $show;
            }
        }
        wp_die( "Error: Show with ID $show_id not found in database", 500 );
    }
}