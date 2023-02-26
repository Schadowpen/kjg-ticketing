<?php

namespace KjG_Ticketing\database\dto;

class Event {
    public int $id;
    public string $name;
    public ?bool $archived;
    public float $ticket_price;
    public float $shipping_price;
    public float $seating_plan_width;
    public float $seating_plan_length;
    public string $seating_plan_length_unit;

    // $ticket_template etc. are part of the TicketConfig DTO

    public static function from_DB( \stdClass $db_row ): Event {
        $event = new Event();
        $event->id = intval( $db_row->id );
        $event->name = (string) $db_row->name;
        if ( $db_row->archived != null ) {
            $event->archived = intval( $db_row->archived ) === 1;
        }
        $event->ticket_price = floatval( $db_row->ticket_price );
        $event->shipping_price = floatval( $db_row->shipping_price );
        $event->seating_plan_width = floatval( $db_row->seating_plan_width );
        $event->seating_plan_length = floatval( $db_row->seating_plan_length );
        $event->seating_plan_length_unit = (string) $db_row->seating_plan_length_unit;

        return $event;
    }
}