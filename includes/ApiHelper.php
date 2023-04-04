<?php

namespace KjG_Ticketing;

use KjG_Ticketing\database\AbstractDatabaseConnection;
use KjG_Ticketing\database\DatabaseConnection;
use KjG_Ticketing\database\DatabaseOverview;
use KjG_Ticketing\database\TemplateDatabaseConnection;

/**
 * Tools to simplify (AJAX) API Code
 *
 * @see DownloadHelper for download calls of the API
 */
class ApiHelper {

    /**
     * Either $_GET or $_POST, depending on constructor parameters
     */
    private array $_PARAMS;

    private ?DatabaseOverview $dbo = null;

    /**
     * @param $is_download_request bool If this request is a download endpoint or an AJAX endpoint
     * Leads to assumptions which HTTP method is used
     */
    public function __construct( bool $is_download_request = false ) {
        if ( $is_download_request ) {
            if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
                wp_die( 'Error: only GET Requests are supported for this endpoint', 400 );
            }
            $this->_PARAMS = $_GET;

        } else {
            if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
                wp_die( 'Error: only POST Requests are supported for this endpoint', 400 );
            }
            $this->_PARAMS = $_POST;
        }
    }

    public function get_database_overview(): DatabaseOverview {
        if ( $this->dbo == null ) {
            $this->dbo = new DatabaseOverview();
        }

        return $this->dbo;
    }

    public function validate_and_get_process_id(): int {
        if ( ! isset( $this->_PARAMS['process_id'] ) ) {
            wp_die( "Error: No process id defined in POST parameters", 400 );
        }
        if ( intval( $this->_PARAMS['process_id'] ) < 0 ) {
            wp_die( "Error: Only positive numbers for process id are allowed \n", 400 );
        }

        return intval( $this->_PARAMS["process_id"] );
    }

    public function validate_and_get_show_id_if_present(): int|null {
        if ( ! isset( $this->_PARAMS['show_id'] ) ) {
            return null;
        }
        if ( intval( $this->_PARAMS["show_id"] ) === 0 ) {
            wp_die( "Error: Only integers are allowed for show_id", 400 );
        }

        return intval( $this->_PARAMS["show_id"] );
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
    public function validateDatabaseUsageAllowed( bool $currentDatabaseAllowed, bool $archiveDatabaseAllowed, bool $templateDatabaseAllowed ): void {
        // check for archive
        if ( isset( $this->_PARAMS['archive'] ) ) {
            if ( $archiveDatabaseAllowed ) {
                if ( ! is_string( $this->_PARAMS['archive'] ) ) {
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
        if ( isset( $this->_PARAMS['template'] ) ) {
            if ( $templateDatabaseAllowed ) {
                if ( ! is_string( $this->_PARAMS['template'] ) ) {
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

    public function getAbstractDatabaseConnection(): AbstractDatabaseConnection {
        if ( isset( $this->_PARAMS["archive"] ) && isset( $this->_PARAMS["template"] ) ) {
            wp_die( "Error: Both template and archive is given. Only one of them is allowed", 400 );
        }

        $dbo = $this->get_database_overview();
        if ( isset( $this->_PARAMS['archive'] ) ) {
            $dbc = $dbo->getArchiveDatabaseConnection( $this->_PARAMS['archive'] );
        } elseif ( isset( $this->_PARAMS["template"] ) ) {
            $dbc = $dbo->getTemplateDatabaseConnection( $this->_PARAMS["template"] );
        } else {
            $dbc = $dbo->getCurrentDatabaseConnection();
        }

        if ( ! $dbc ) {
            wp_die();
        }

        return $dbc;
    }

    public function getDatabaseConnection(): DatabaseConnection {
        $dbo = $this->get_database_overview();
        if ( isset( $this->_PARAMS['archive'] ) ) {
            $dbc = $dbo->getArchiveDatabaseConnection( $this->_PARAMS['archive'] );
        } else {
            $dbc = $dbo->getCurrentDatabaseConnection();
        }
        if ( ! $dbc ) {
            wp_die();
        }

        return $dbc;
    }

    public function getTemplateDatabaseConnection(): TemplateDatabaseConnection {
        $dbo = $this->get_database_overview();
        $dbc = $dbo->getTemplateDatabaseConnection( $this->_PARAMS["template"] );
        if ( ! $dbc ) {
            wp_die();
        }

        return $dbc;
    }
}
