<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\DatabaseConnection;

/**
 * @deprecated
 */
class SeatingPlan {

    /**
     * @return mixed A combination of event, seats and seating_plan_areas
     *
     * @deprecated TODO This can be split up into individual backend calls
     */
    public static function get( DatabaseConnection $dbc ): mixed {
        $event = $dbc->get_event();
        if ( ! $event ) {
            wp_die();
        }
        $seats = $dbc->get_seats();
        $seating_plan_areas = $dbc->get_seating_plan_areas();

        // concatenate to one object and send
        $event->seats = $seats;
        $event->seating_plan_areas = $seating_plan_areas;

        return $event;
    }
}