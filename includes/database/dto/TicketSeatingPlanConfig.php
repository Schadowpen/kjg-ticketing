<?php

namespace KjG_Ticketing\database\dto;

/**
 * Subclass for TicketConfig
 */
class TicketSeatingPlanConfig extends TicketImageConfig {
    public bool $seat_numbers_visible;
    public bool $connect_entrance_arrows;

    protected function __construct() {
        parent::__construct();
        // use static functions instead of constructor
    }

    public static function from_DB( \stdClass $db_row ): TicketSeatingPlanConfig {
        $config = new TicketSeatingPlanConfig();
        $config->fill_from_DB( $db_row );
        $config->seat_numbers_visible    = intval( $db_row->seating_plan_seat_numbers_visible ) === 1;
        $config->connect_entrance_arrows = intval( $db_row->seating_plan_connect_arrows ) === 1;

        return $config;
    }

    public function to_DB_data(): array {
        $data = parent::to_DB_data();

        $data["seating_plan_seat_numbers_visible"] = $this->seat_numbers_visible;
        $data["seating_plan_connect_arrows"]       = $this->connect_entrance_arrows;

        return $data;
    }

    public function to_DB_format(): array {
        $format = parent::to_DB_format();

        $format["seating_plan_seat_numbers_visible"] = "%d";
        $format["seating_plan_connect_arrows"]       = "%d";

        return $format;
    }
}
