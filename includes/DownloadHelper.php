<?php

namespace KjG_Ticketing;

/**
 * Tools especially for download links.
 *
 * @see ApiHelper
 */
class DownloadHelper {

    public static function validate_and_get_show_id_if_present(): int|null {
        if ( ! isset( $_GET['show_id'] ) ) {
            return null;
        }
        if ( intval( $_GET["show_id"] ) === 0 ) {
            wp_die( "Error: Only integers are allowed for show_id", 400 );
        }

        return intval( $_GET["show_id"] );
    }
}