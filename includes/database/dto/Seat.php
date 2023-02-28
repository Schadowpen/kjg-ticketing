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

    public static function from_DB( \stdClass $db_row ): Seat {
        $seat = new Seat();
        $seat->block = (string) $db_row->seat_block;
        $seat->row = (string) $db_row->seat_row;
        $seat->number = intval( $db_row->seat_number );
        $seat->position_x = floatval( $db_row->position_x );
        $seat->position_y = floatval( $db_row->position_y );
        $seat->rotation = floatval( $db_row->rotation );
        $seat->width = floatval( $db_row->width );
        $seat->length = floatval( $db_row->length );
        if ( $db_row->entrance_id != null ) {
            $seat->entrance_id = intval( $db_row->entrance_id );
        }

        return $seat;
    }
}