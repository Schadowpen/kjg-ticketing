<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\DatabaseConnection;
use KjG_Ticketing\database\dto\Process;
use KjG_Ticketing\database\dto\SeatState;

class Overview {
    public string $event_name;
    public float $ticket_price;
    public float $shipping_price;
    public float $shipping_revenue;
    public float $paid_shipping_revenue;
    /**
     * @var OverviewPerShow[]
     */
    public array $per_show;

    public static function get( DatabaseConnection $dbc ): mixed {
        // read database
        $event       = $dbc->get_event();
        $seats       = $dbc->get_seats();
        $shows       = $dbc->get_shows();
        $seat_states = $dbc->get_seat_states();
        $processes   = $dbc->get_processes();

        // Connect data to output
        $seat_count             = count( $seats );
        $output                 = new Overview();
        $output->event_name     = $event->name;
        $output->ticket_price   = $event->ticket_price;
        $output->shipping_price = $event->shipping_price;

        // Calculate revenue by mail
        $output->shipping_revenue      = 0;
        $output->paid_shipping_revenue = 0;
        for ( $i = 0; $i < count( $processes ); $i ++ ) {
            if ( $processes[ $i ]->shipping == Process::SHIPPING_MAIL ) {
                $output->shipping_revenue += $event->shipping_price;
                if ( $processes[ $i ]->payment_state == Process::PAYMENT_STATE_PAID ) {
                    $output->paid_shipping_revenue += $event->shipping_price;
                }
            }
        }

        // Calculate for every show
        for ( $i = 0; $i < count( $shows ); $i ++ ) {
            $overview_per_show = new OverviewPerShow( $shows[ $i ], $seat_count );

            // go through all seat states for this show
            for ( $k = 0; $k < count( $seat_states ); $k ++ ) {
                if ( $seat_states[ $k ]->show_id == $overview_per_show->show_id ) {

                    // If this seat is blocked, reduce number of available seats
                    if ( $seat_states[ $k ]->state == SeatState::STATE_BLOCKED ) {
                        $overview_per_show->num_seats_available --;

                    } else if ( $seat_states[ $k ]->state == SeatState::STATE_AVAILABLE ) {
                        // all available seats are included by default

                    } else { // "reserved", "booked" oder "present"
                        // Get process belonging to seat state
                        $process = null;
                        for ( $j = 0; $j < count( $processes ); $j ++ ) {
                            if ( $processes[ $j ]->id == @$seat_states[ $k ]->process_id ) {
                                $process = $processes[ $j ];
                                break;
                            }
                        }
                        if ( $process == null ) {
                            wp_die( "Error: seat state without belonging process: " . json_encode( $seat_states[ $k ] ), 500 );
                        }

                        // It is irrelevant if visitors were present. We convert it into the information if they paid for it
                        if ( $seat_states[ $k ]->state == SeatState::STATE_PRESENT ) {
                            if ( $process->payment_state == Process::PAYMENT_STATE_OPEN ) {
                                // did not pay
                                $seat_states[ $k ]->state = SeatState::STATE_RESERVED;
                            } else { // payment_state === PAID || BOX_OFFICE
                                // did pay
                                $seat_states[ $k ]->state = SeatState::STATE_BOOKED;
                            }
                        }

                        // If a seat is reserved, it is not yet paid
                        if ( $seat_states[ $k ]->state == SeatState::STATE_RESERVED ) {
                            $overview_per_show->num_seats_reserved ++;
                            for ( $j = 0; $j < count( $processes ); $j ++ ) {
                                if ( $processes[ $j ]->id == @$seat_states[ $k ]->process_id ) {
                                    if ( $processes[ $j ]->payment_method != Process::PAYMENT_METHOD_VIP && $processes[ $j ]->payment_method != Process::PAYMENT_METHOD_TRIPLE_A ) {
                                        $overview_per_show->revenue += $processes[ $j ]->get_ticket_price( $event );
                                    }
                                    break;
                                }
                            }

                            // If a seat is booked, it is paid. VIP and TripleA get tickets for free
                        } else if ( $seat_states[ $k ]->state == SeatState::STATE_BOOKED ) {
                            for ( $j = 0; $j < count( $processes ); $j ++ ) {
                                if ( $processes[ $j ]->id == $seat_states[ $k ]->process_id ) {
                                    if ( $processes[ $j ]->payment_method == Process::PAYMENT_METHOD_VIP ) {
                                        $overview_per_show->num_seats_VIP ++;
                                    } else if ( $processes[ $j ]->payment_method == Process::PAYMENT_METHOD_TRIPLE_A ) {
                                        $overview_per_show->num_seats_TripleA ++;
                                    } else {
                                        $overview_per_show->num_seats_booked ++;
                                        $ticket_price                    = $processes[ $j ]->get_ticket_price( $event );
                                        $overview_per_show->revenue      += $ticket_price;
                                        $overview_per_show->paid_revenue += $ticket_price;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $output->per_show[ $i ] = $overview_per_show;
        }

        return $output;
    }
}