<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\dto\Show;

class OverviewPerShow {
    public int $show_id;
    public string $show_date;
    public string $show_time;

    public int $num_seats_available;
    public int $num_seats_reserved;
    public int $num_seats_booked;
    public int $num_seats_VIP;
    public int $num_seats_TripleA;

    public float $revenue;
    public float $paid_revenue;

    public function __construct( Show $show, int $seat_count ) {
        $this->show_id = $show->id;
        $this->show_date = $show->date;
        $this->show_time = $show->time;
        $this->num_seats_available = $seat_count;
        $this->num_seats_reserved = 0;
        $this->num_seats_booked = 0;
        $this->num_seats_VIP = 0;
        $this->num_seats_TripleA = 0;
        $this->revenue = 0.0;
        $this->paid_revenue = 0.0;
    }
}