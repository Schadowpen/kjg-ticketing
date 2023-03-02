<?php

namespace KjG_Ticketing;

use KjG_Ticketing\database\AbstractDatabaseConnection;
use KjG_Ticketing\database\DatabaseConnection;
use KjG_Ticketing\database\DatabaseOverview;
use KjG_Ticketing\database\TemplateDatabaseConnection;

class ApiHelper {

    public static function validate_and_get_process_id(): int {
        if ( ! isset( $_POST['process_id'] ) ) {
            wp_die( "Error: No process id defined in POST parameters", 400 );
        }
        if ( intval( $_POST['process_id'] ) < 0 ) {
            wp_die( "Error: Only positive numbers for process id are allowed \n", 400 );
        }

        return intval( $_POST["process_id"] );
    }

    /**
     * Checks, which databases are set in the AJAX-Request and if it is allowed to access those.
     *
     * It is also check, if no database is specified.
     * It is not checked, if multiple databases are specified.
     *
     * @param bool $currentDatabaseAllowed If it is allowed to access the current event
     * @param bool $archiveDatabaseAllowed If it is allowed to access any archived event
     * @param bool $templateDatabaseAllowed If it is allowed to access template events
     *
     * TODO check usages of this functions and if they can be simplified
     */
    public static function validateDatabaseUsageAllowed( bool $currentDatabaseAllowed, bool $archiveDatabaseAllowed, bool $templateDatabaseAllowed ): void {
        // check for archive
        if ( isset( $_POST['archive'] ) ) {
            if ( $archiveDatabaseAllowed ) {
                if ( ! is_string( $_POST['archive'] ) ) {
                    wp_die( "Error: Wrong data type for 'archive'", 400 );
                }
            } else {
                wp_die( "Error: It is not allowed to access archived events for this command", 400 );
            }
            $archiveGiven = true;
        } else {
            $archiveGiven = false;
        }

        // check for template
        if ( isset( $_POST['template'] ) ) {
            if ( $templateDatabaseAllowed ) {
                if ( ! is_string( $_POST['template'] ) ) {
                    wp_die( 'Error: Wrong data type for "template"', 400 );
                }
            } else {
                wp_die( "Error: It is not allowed to access template events for this command", 400 );
            }
            $templateGiven = true;
        } else {
            $templateGiven = false;
        }

        // check if any database is given
        if ( ! $currentDatabaseAllowed ) {
            if ( ! $archiveGiven && ! $templateGiven ) {
                wp_die( "Error: No Database given to the request", 400 );
            }
        }

        // All checks succeeded
    }

    public static function getAbstractDatabaseConnection( DatabaseOverview $dbo ): AbstractDatabaseConnection {
        if ( isset( $_POST["archive"] ) && isset( $_POST["template"] ) ) {
            wp_die( "Error: Both template and archive is given. Only one of them is allowed", 400 );
        }

        if ( isset( $_POST['archive'] ) ) {
            $dbc = $dbo->getArchiveDatabaseConnection( $_POST['archive'] );
        } elseif ( isset( $_POST["template"] ) ) {
            $dbc = $dbo->getTemplateDatabaseConnection( $_POST["template"] );
        } else {
            $dbc = $dbo->getCurrentDatabaseConnection();
        }

        if ( ! $dbc ) {
            wp_die();
        }

        return $dbc;
    }

    public static function getDatabaseConnection( DatabaseOverview $dbo ): DatabaseConnection {
        if ( isset( $_POST['archive'] ) ) {
            $dbc = $dbo->getArchiveDatabaseConnection( $_POST['archive'] );
        } else {
            $dbc = $dbo->getCurrentDatabaseConnection();
        }
        if ( ! $dbc ) {
            wp_die();
        }

        return $dbc;
    }

    public static function getTemplateDatabaseConnection( DatabaseOverview $dbo ): TemplateDatabaseConnection {
        $dbc = $dbo->getTemplateDatabaseConnection( $_POST["template"] );
        if ( ! $dbc ) {
            wp_die();
        }

        return $dbc;
    }
}