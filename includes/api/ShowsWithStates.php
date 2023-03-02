<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\DatabaseConnection;

class ShowsWithStates {

    public static function get( DatabaseConnection $dbc ): array {
        // read database
        $shows = $dbc->get_shows();
        $seat_states = $dbc->get_seat_states();

        // Add an object to the according show for each seat state
        for ( $i = 0; $i < count( $seat_states ); $i ++ ) {
            for ( $k = 0; $k < count( $shows ); $k ++ ) {
                if ( $seat_states[ $i ]->show_id === $shows[ $k ]->id ) {
                    /** @var string */
                    $id = $seat_states[ $i ]->seat_block . "," . $seat_states[ $i ]->seat_row . $seat_states[ $i ]->seat_number;
                    $temp = (object) [
                        "state"      => $seat_states[ $i ]->state,
                        "process_id" => @$seat_states[ $i ]->process_id != null ? $seat_states[ $i ]->process_id : null
                    ];
                    $shows[ $k ]->$id = $temp;
                    break;
                }
            }
        }

        return $shows;
    }
}