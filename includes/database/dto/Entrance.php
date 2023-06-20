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
    public ?string $text = null;
    public ?float $text_position_x = null;
    public ?float $text_position_y = null;
    public ?int $entrance_id = null;

    private function __construct() {
        // use static functions instead of constructor
    }

    public static function from_DB( \stdClass $db_row ): Entrance {
        $entrance     = new Entrance();
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
            $entrance->text            = (string) $db_row->text;
            $entrance->text_position_x = floatval( $db_row->text_position_x );
            $entrance->text_position_y = floatval( $db_row->text_position_y );
        }
        $entrance->entrance_id = $db_row->entrance_id != null ? intval( $db_row->entrance_id ) : null;

        return $entrance;
    }

    public function to_DB_data(): array {
        return array(
            "x0"              => $this->x0,
            "y0"              => $this->y0,
            "x1"              => $this->x1,
            "y1"              => $this->y1,
            "x2"              => $this->x2,
            "y2"              => $this->y2,
            "x3"              => $this->x3,
            "y3"              => $this->y3,
            "text"            => $this->text,
            "text_position_x" => $this->text_position_x,
            "text_position_y" => $this->text_position_y,
            "entrance_id"     => $this->entrance_id,
        );
    }

    public static function to_DB_format(): array {
        return array(
            "x0"              => "%f",
            "y0"              => "%f",
            "x1"              => "%f",
            "y1"              => "%f",
            "x2"              => "%f",
            "y2"              => "%f",
            "x3"              => "%f",
            "y3"              => "%f",
            "text"            => "%s",
            "text_position_x" => "%f",
            "text_position_y" => "%f",
            "entrance_id"     => "%d",
        );
    }
}
