<?php

namespace KjG_Ticketing\database\dto;

class SeatingPlanArea {
    public int $id;
    public float $position_x;
    public float $position_y;
    public float $width;
    public float $length;
    public string $color;
    public ?string $text = null;
    public ?float $text_position_x = null;
    public ?float $text_position_y = null;
    public ?string $text_color = null;

    private function __construct() {
        // use static functions instead of constructor
    }

    public static function from_DB( \stdClass $db_row ): SeatingPlanArea {
        $seating_plan_area             = new SeatingPlanArea();
        $seating_plan_area->id         = intval( $db_row->id );
        $seating_plan_area->position_x = floatval( $db_row->position_x );
        $seating_plan_area->position_y = floatval( $db_row->position_y );
        $seating_plan_area->width      = floatval( $db_row->width );
        $seating_plan_area->length     = floatval( $db_row->length );
        $seating_plan_area->color      = (string) $db_row->color;
        if ( $db_row->text != null ) {
            $seating_plan_area->text            = (string) $db_row->text;
            $seating_plan_area->text_position_x = floatval( $db_row->text_position_x );
            $seating_plan_area->text_position_y = floatval( $db_row->text_position_y );
            $seating_plan_area->text_color      = (string) $db_row->text_color;
        }

        return $seating_plan_area;
    }

    public function to_DB_data(): array {
        return array(
            "position_x"      => $this->position_x,
            "position_y"      => $this->position_y,
            "width"           => $this->width,
            "length"          => $this->length,
            "color"           => $this->color,
            "text"            => $this->text,
            "text_position_x" => $this->text_position_x,
            "text_position_y" => $this->text_position_y,
            "text_color"      => $this->text_color,
        );
    }

    public function to_DB_format() {
        return array(
            "position_x"      => "%f",
            "position_y"      => "%f",
            "width"           => "%f",
            "length"          => "%f",
            "color"           => "%s",
            "text"            => "%s",
            "text_position_x" => "%f",
            "text_position_y" => "%f",
            "text_color"      => "%s",
        );
    }
}
