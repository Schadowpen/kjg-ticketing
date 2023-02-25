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

    public static function fromObject( mixed $obj ): Event {
        $event = new Event();
        $event->id = intval( $obj->id );
        $event->name = (string) $obj->name;
        if ( isset( $obj->archived ) ) {
            $event->archived = intval( $obj->archived ) === 1;
        }
        $event->ticket_price = floatval( $obj->ticket_price );
        $event->shipping_price = floatval( $obj->shipping_price );
        $event->seating_plan_width = floatval( $obj->seating_plan_width );
        $event->seating_plan_length = floatval( $obj->seating_plan_length );
        $event->seating_plan_length_unit = (string) $obj->seating_plan_length_unit;

        return $event;
    }
}