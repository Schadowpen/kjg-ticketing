<?php

namespace KjG_Ticketing\api;

use KjG_Ticketing\database\DatabaseConnection;
use KjG_Ticketing\database\dto\Process;
use KjG_Ticketing\database\dto\ProcessAdditionalEntry;
use KjG_Ticketing\database\dto\Show;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VisitorsXlsx {
    /**
     * Creates an Excel file that contains all visitors for an event.
     * This function will output the created file and never return.
     *
     * @param DatabaseConnection $dbc
     * @param int|null $show_id If set, the result XLSX file will only contain entries for the specified show
     *
     * @return void
     */
    public static function get( DatabaseConnection $dbc, ?int $show_id ): void {
        $for_single_show = $show_id !== null;

        // read database
        $event                     = $dbc->get_event();
        $process_additional_fields = $dbc->get_process_additional_fields();
        $shows                     = $dbc->get_shows();
        $seat_states               = $dbc->get_seat_states();
        $processes                 = $dbc->get_processes();

        // print as XLSX file
        try {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            if ( $for_single_show ) {
                $sheet->setCellValue( 'A1', __( "First name", "kjg-ticketing" ) )
                      ->setCellValue( 'B1', __( "Last name", "kjg-ticketing" ) )
                      ->setCellValue( 'C1', __( "Address", "kjg-ticketing" ) )
                      ->setCellValue( 'D1', __( "E-Mail", "kjg-ticketing" ) )
                      ->setCellValue( 'E1', __( "Phone", "kjg-ticketing" ) )
                      ->setCellValue( 'F1', __( "Block", "kjg-ticketing" ) )
                      ->setCellValue( 'G1', __( "Seat", "kjg-ticketing" ) )
                      ->setCellValue( 'H1', __( "Comment", "kjg-ticketing" ) );
                $lastColumn = 'H';
            } else {
                $sheet->setCellValue( 'A1', __( "Date", "kjg-ticketing" ) )
                      ->setCellValue( 'B1', __( "Time", "kjg-ticketing" ) )
                      ->setCellValue( 'C1', __( "First name", "kjg-ticketing" ) )
                      ->setCellValue( 'D1', __( "Last name", "kjg-ticketing" ) )
                      ->setCellValue( 'E1', __( "Address", "kjg-ticketing" ) )
                      ->setCellValue( 'F1', __( "E-Mail", "kjg-ticketing" ) )
                      ->setCellValue( 'G1', __( "Phone", "kjg-ticketing" ) )
                      ->setCellValue( 'H1', __( "Block", "kjg-ticketing" ) )
                      ->setCellValue( 'I1', __( "Seat", "kjg-ticketing" ) )
                      ->setCellValue( 'J1', __( "Comment", "kjg-ticketing" ) );
                $lastColumn = 'J';
            }
            for ( $i = 0; $i < count( $process_additional_fields ); $i ++ ) {
                $lastColumn ++;
                $sheet->setCellValue( $lastColumn . '1', $process_additional_fields[ $i ]->description );
                $process_additional_fields[ $i ]->column = $lastColumn;
                if ( $lastColumn == 'Z' ) {
                    break;
                }
            }
            $sheet->getStyle( 'A1:' . $lastColumn . '1' )->applyFromArray( [ 'font' => [ 'bold' => true ] ] );

            // print every visitor
            $sheetRow = 2;
            for ( $i = 0; $i < count( $seat_states ); $i ++ ) {
                if ( ( ! $for_single_show || $seat_states[ $i ]->show_id === $show_id )
                     && isset( $seat_states[ $i ]->process_id ) ) {
                    for ( $j = 0; $j < count( $processes ); $j ++ ) {
                        if ( $processes[ $j ]->id == $seat_states[ $i ]->process_id ) {
                            $process = $processes[ $j ];
                            if ( $for_single_show ) {
                                $sheet->fromArray( array(
                                    $process->first_name,
                                    $process->last_name,
                                    $process->address,
                                    $process->email,
                                    $process->phone,
                                    $seat_states[ $i ]->seat_block,
                                    $seat_states[ $i ]->seat_row . $seat_states[ $i ]->seat_number,
                                    $process->comment
                                ),
                                    null, 'A' . $sheetRow );
                            } else {
                                $show = self::get_show( $shows, $seat_states[ $i ]->show_id );
                                $sheet->fromArray( array(
                                    $show->date,
                                    $show->time,
                                    $process->first_name,
                                    $process->last_name,
                                    $process->address,
                                    $process->email,
                                    $process->phone,
                                    $seat_states[ $i ]->seat_block,
                                    $seat_states[ $i ]->seat_row . $seat_states[ $i ]->seat_number,
                                    $process->comment
                                ),
                                    null, 'A' . $sheetRow );
                            }
                            for ( $k = 0; $k < count( $process_additional_fields ); $k ++ ) {
                                $entry = self::get_process_additional_entry( $process, $process_additional_fields[ $k ]->id );
                                $sheet->setCellValue( $process_additional_fields[ $k ]->column . $sheetRow, $entry?->get_value() );
                            }
                            $sheetRow ++;
                            break;
                        }
                    }
                }
            }

            // setup column widths
            for ( $column = 'A'; $column <= $lastColumn; $column ++ ) {
                $sheet->getColumnDimension( $column )->setAutoSize( true );
            }
            $sheet->calculateColumnWidths();
            $sheet->freezePane( $for_single_show ? 'C2' : 'E2' );

            $writer = new Xlsx( $spreadsheet );
            header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
            if ( $for_single_show ) {
                $show     = self::get_show( $shows, $show_id );
                $fileName = __( "Visitor", "kjg-ticketing" ) . ' ' . $event->name . ' ' . $show->date . ' ' . $show->time . '.xlsx';
            } else {
                $fileName = __( "Visitor", "kjg-ticketing" ) . ' ' . $event->name . '.xlsx';
            }
            header( 'Content-Disposition: attachment; filename="' . $fileName . '"' );
            $writer->save( "php://output" );
        } catch ( \PhpOffice\PhpSpreadsheet\Writer\Exception|\PhpOffice\PhpSpreadsheet\Exception $e ) {
            wp_die( "Error: {$e->getMessage()}\n{$e->getTraceAsString()}", 500 );
        } finally {
            exit();
        }
    }

    /**
     * @param Show[] $shows
     * @param int $show_id
     *
     * @return Show
     */
    private static function get_show( array $shows, int $show_id ): Show {
        foreach ( $shows as $show ) {
            if ( $show->id === $show_id ) {
                return $show;
            }
        }
        wp_die( "Error: Show with ID $show_id not found in database", 500 );
    }

    private static function get_process_additional_entry( Process $process, int $additional_field_id ): ProcessAdditionalEntry|null {
        foreach ( $process->additional_entries as $additional_entry ) {
            if ( $additional_entry->field_id === $additional_field_id ) {
                return $additional_entry;
            }
        }

        return null;
    }
}
