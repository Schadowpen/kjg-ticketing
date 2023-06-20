<?php

namespace KjG_Ticketing\database\dto;

use KjG_Ticketing\pdf\graphics\Point;

/**
 * Subclass for TicketConfig
 */
class TicketTextConfig {
    // possible values for content enum
    public const CONTENT_DATE = "date";
    public const CONTENT_TIME = "time";
    public const CONTENT_SEAT_BLOCK = "seat_block";
    public const CONTENT_SEAT_ROW = "seat_row";
    public const CONTENT_SEAT_NUMBER = "seat_number";
    public const CONTENT_PRICE = "price";
    public const CONTENT_PAYMENT_STATE = "payment_state";
    public const CONTENT_PROCESS_ID = "process_id";

    // possible values for alignment enum
    public const ALIGNMENT_LEFT = "left";
    public const ALIGNMENT_CENTER = "center";
    public const ALIGNMENT_RIGHT = "right";

    public Point $position;
    public string $alignment;
    public string $font;
    public float $font_size;
    public Color $color;

    private function __construct() {
        // use static functions instead of constructor
    }

    public static function from_DB( \stdClass $db_row ): TicketTextConfig {
        $ticket_text_config            = new TicketTextConfig();
        $ticket_text_config->position  = new Point(
            floatval( $db_row->position_x ),
            floatval( $db_row->position_y )
        );
        $ticket_text_config->alignment = $db_row->alignment;
        $ticket_text_config->font      = $db_row->font;
        $ticket_text_config->font_size = $db_row->font_size;
        $ticket_text_config->color     = new Color(
            intval( $db_row->color_red ),
            intval( $db_row->color_green ),
            intval( $db_row->color_blue )
        );

        return $ticket_text_config;
    }

    public function to_DB_data(): array {
        return array(
            "position_x"  => $this->position->x,
            "position_y"  => $this->position->y,
            "alignment"   => $this->alignment,
            "font"        => $this->font,
            "font_size"   => $this->font_size,
            "color_red"   => $this->color->red,
            "color_green" => $this->color->green,
            "color_blue"  => $this->color->blue,
        );
    }

    public static function to_DB_format(): array {
        return array(
            "position_x"  => "%f",
            "position_y"  => "%f",
            "alignment"   => "%s",
            "font"        => "%s",
            "font_size"   => "%f",
            "color_red"   => "%d",
            "color_green" => "%d",
            "color_blue"  => "%d",
        );
    }
}
