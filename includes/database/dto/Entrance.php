<?php

namespace KjG_Ticketing\database\dto;

class Entrance {
    public int $id;
    public float $x0;
    public float $y0;
    public float $x1;
    public float $y1;
    public float $x2;
    public float $y2;
    public float $x3;
    public float $y3;
    public ?string $text;
    public ?float $text_position_x;
    public ?float $text_position_y;
    public ?int $entrance_id;

    public static function from_DB( \stdClass $db_row ): Entrance {
        $entrance = new Entrance();
        $entrance->id = intval( $db_row->id );
        $entrance->x0 = floatval( $db_row->x0 );
        $entrance->y0 = floatval( $db_row->y0 );
        $entrance->x1 = floatval( $db_row->x1 );
        $entrance->y1 = floatval( $db_row->y1 );
        $entrance->x2 = floatval( $db_row->x2 );
        $entrance->y2 = floatval( $db_row->y2 );
        $entrance->x3 = floatval( $db_row->x3 );
        $entrance->y3 = floatval( $db_row->y3 );
        if ( $db_row->text != null ) {
            $entrance->text = (string) $db_row->text;
            $entrance->text_position_x = floatval( $db_row->text_position_x );
            $entrance->text_position_y = floatval( $db_row->text_position_y );
        }
        if ( $db_row->entrance_id != null ) {
            $entrance->entrance_id = intval( $db_row->entrance_id );
        }

        return $entrance;
    }
}