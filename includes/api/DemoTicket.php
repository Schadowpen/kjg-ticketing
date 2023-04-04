<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\AbstractDatabaseConnection;
use KjG_Ticketing\database\DatabaseOverview;
use KjG_Ticketing\database\dto\Process;
use KjG_Ticketing\database\dto\SeatState;
use KjG_Ticketing\database\TemplateDatabaseConnection;
use KjG_Ticketing\ticket_generation\TicketGenerator;

class DemoTicket {
    public static function get( AbstractDatabaseConnection $dbc ): void {
        if ( $dbc instanceof TemplateDatabaseConnection ) {
            $seats = DatabaseOverview::getSeatsIncludingSeatGroups( $dbc );
        } else {
            $seats = $dbc->get_seats();
        }
        if ( count( $seats ) === 0 ) {
            wp_die( "Error: Demo ticket cannot be generated, no seats exist", 500 );
        }

        $shows = $dbc->get_shows();
        if ( count( $shows ) === 0 ) {
            wp_die( "Error: Demo ticket cannot be generated, no shows exist", 500 );
        }

        $process = Process::create_demo_process();

        $seat_state  = SeatState::new(
            $seats[0],
            $shows[0],
            SeatState::STATE_BOOKED,
            $process
        );
        $seat_states = [ $seat_state ];

        try {
            // Pass data
            $ticketGenerator = new TicketGenerator( $dbc, $process );
            $ticketGenerator->setSeats( $seats );
            $ticketGenerator->setSeatstates( $seat_states );
            $ticketGenerator->loadData();

            // Generate ticket
            $ticketGenerator->generateTicket();
            $pdfContent = $ticketGenerator->getTicketContent();

            // output
            header( "Content-Type: application/pdf" );
            echo $pdfContent;

        } catch ( \Throwable $exception ) {
            wp_die( "Error: {$exception->getMessage()}\n{$exception->getTraceAsString()}", 500 );
        } finally {
            exit();
        }
    }
}
