<?php

namespace KjG_Ticketing\database\dto;

use KjG_Ticketing\pdf\graphics\Point;

/**
 * Subclass for TicketConfig
 */
class TicketTextConfig {
    public Point $position;
    public string $alignment;
    public string $font;
    public float $font_size;
    public Color $color;

    public static function from_DB( \stdClass $db_row ): TicketTextConfig {
        $ticket_text_config = new TicketTextConfig();
        $ticket_text_config->position = new Point(
            floatval( $db_row->position_x ),
            floatval( $db_row->position_y )
        );
        $ticket_text_config->alignment = $db_row->alignment;
        $ticket_text_config->font = $db_row->font;
        $ticket_text_config->font_size = $db_row->font_size;
        $ticket_text_config->color = new Color(
            intval( $db_row->color_red ),
            intval( $db_row->color_green ),
            intval( $db_row->color_blue )
        );

        return $ticket_text_config;
    }
}