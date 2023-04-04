<?php

namespace KjG_Ticketing\database\dto;

class Seat {
    /**
     * Since "I" and "J" look very similar, it is not allowed to have a row called "J".
     * The row "I" should be followed by a row "K".
     */
    public const NOT_ALLOWED_ROW_NAME = "J";

    public string $block;
    public string $row;
    public int $number;
    public float $position_x;
    public float $position_y;
    public float $rotation;
    public float $width;
    public float $length;
    public ?int $entrance_id;

    private function __construct() {
        // use static functions instead of constructor
    }

    public static function from_DB( \stdClass $db_row ): Seat {
        $seat              = new Seat();
        $seat->block       = (string) $db_row->seat_block;
        $seat->row         = (string) $db_row->seat_row;
        $seat->number      = intval( $db_row->seat_number );
        $seat->position_x  = floatval( $db_row->position_x );
        $seat->position_y  = floatval( $db_row->position_y );
        $seat->rotation    = floatval( $db_row->rotation );
        $seat->width       = floatval( $db_row->width );
        $seat->length      = floatval( $db_row->length );
        $seat->entrance_id = $db_row->entrance_id != null ? intval( $db_row->entrance_id ) : null;

        return $seat;
    }

    public static function from_seat_group(
        SeatGroup $seat_group,
        string $seat_row,
        int $seat_number,
        float $position_x,
        float $position_y
    ): Seat {
        $seat              = new Seat();
        $seat->block       = $seat_group->block;
        $seat->row         = $seat_row;
        $seat->number      = $seat_number;
        $seat->position_x  = $position_x;
        $seat->position_y  = $position_y;
        $seat->rotation    = $seat_group->rotation;
        $seat->width       = $seat_group->seat_width;
        $seat->length      = $seat_group->seat_length;
        $seat->entrance_id = $seat_group->entrance_id;

        return $seat;
    }
}
