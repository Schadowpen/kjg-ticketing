<?php

namespace KjG_Ticketing\database\dto;

class SeatState {
    // possible values for state enum
    public const STATE_AVAILABLE = "available";
    public const STATE_RESERVED = "reserved";
    public const STATE_BOOKED = "booked";
    public const STATE_BLOCKED = "blocked";
    public const STATE_PRESENT = "present";

    public string $seat_block;
    public string $seat_row;
    public int $seat_number;
    public int $show_id;
    public string $state;
    public ?int $process_id;

    public static function from_DB( \stdClass $db_row ): SeatState {
        $seat_state = new SeatState();
        $seat_state->seat_block = (string) $db_row->seat_block;
        $seat_state->seat_row = (string) $db_row->seat_row;
        $seat_state->seat_number = intval( $db_row->seat_number );
        $seat_state->show_id = intval( $db_row->show_id );
        $seat_state->state = (string) $db_row->state;
        $seat_state->process_id = $db_row->process_id != null ? intval( $db_row->process_id ) : null;

        return $seat_state;
    }
}