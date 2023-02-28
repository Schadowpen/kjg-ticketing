<?php

namespace KjG_Ticketing\database\dto;

class SeatGroup {
    public int $id;
    public string $block;
    public string $row_front;
    public string $row_back;
    public float $row_distance;
    public int $seat_number_left;
    public int $seat_number_right;
    public float $seat_distance;
    public float $position_x;
    public float $position_y;
    public float $rotation;
    public float $seat_width;
    public float $seat_length;
    public ?int $entrance_id;

    public static function from_DB( \stdClass $db_row ): SeatGroup {
        $seat_group = new SeatGroup();
        $seat_group->id = intval( $db_row->id );
        $seat_group->block = (string) $db_row->block;
        $seat_group->row_front = (string) $db_row->row_front;
        $seat_group->row_back = (string) $db_row->row_back;
        $seat_group->row_distance = floatval( $db_row->row_distance );
        $seat_group->seat_number_left = intval( $db_row->seat_number_left );
        $seat_group->seat_number_right = intval( $db_row->seat_number_right );
        $seat_group->seat_distance = floatval( $db_row->seat_distance );
        $seat_group->position_x = floatval( $db_row->position_x );
        $seat_group->position_y = floatval( $db_row->position_y );
        $seat_group->rotation = floatval( $db_row->rotation );
        $seat_group->seat_width = floatval( $db_row->seat_width );
        $seat_group->seat_length = floatval( $db_row->seat_length );
        if ( $db_row->entrance_id != null ) {
            $seat_group->entrance_id = intval( $db_row->entrance_id );
        }

        return $seat_group;
    }

    /**
     * Splits this seat group into individual seats
     * @return Seat[]
     */
    public function split_into_seats(): array {
        $rows = array();
        $frontRowCharCode = ord( $this->row_front );
        $backRowCharCode = ord( $this->row_back );
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
        $groupLength = ( count( $rows ) - 1 ) * $this->row_distance;

        $columns = array();
        if ( $this->seat_number_left < $this->seat_number_right ) {
            for ( $p = $this->seat_number_left; $p <= $this->seat_number_right; $p ++ ) {
                $columns[] = $p;
            }
        } else {
            for ( $p = $this->seat_number_left; $p >= $this->seat_number_right; $p -- ) {
                $columns[] = $p;
            }
        }
        $groupWidth = ( count( $columns ) - 1 ) * $this->seat_distance;

        $seats = array();
        for ( $i = 0; $i < count( $rows ); $i ++ ) {
            for ( $k = 0; $k < count( $columns ); $k ++ ) {
                $internalX = - $groupWidth / 2 + $k * $this->seat_distance;
                $internalY = $groupLength / 2 - $i * $this->row_distance;
                $rotationRad = deg2rad( $this->rotation );

                $new_seat = new Seat();
                $new_seat->block = $this->block;
                $new_seat->row = $rows[ $i ];
                $new_seat->number = $columns[ $k ];
                $new_seat->position_x = cos( $rotationRad ) * $internalX - sin( $rotationRad ) * $internalY + $this->position_x;
                $new_seat->position_y = sin( $rotationRad ) * $internalX + cos( $rotationRad ) * $internalY + $this->position_y;
                $new_seat->rotation = $this->rotation;
                $new_seat->width = $this->seat_width;
                $new_seat->length = $this->seat_length;
                $new_seat->entrance_id = @$this->entrance_id;
                $seats[] = $new_seat;
            }
        }

        return $seats;
    }
}