<?php

namespace KjG_Ticketing;

/**
 * Unifies WordPress Options handling to avoid misspelling and such
 */
class Options {

    public static function add_default_options(): void {
        self::add_is_http_allowed( is_ssl() );
    }

    public static function delete_all_options(): void {
        self::delete_db_version();
        self::delete_current_event_id();
        self::delete_is_http_allowed();
    }

    // --------------------------------------------------

    private const DB_VERSION_OPTION_NAME = "kjg_ticketing_db_version";

    public static function add_db_version( $db_version ): bool {
        return add_option( self::DB_VERSION_OPTION_NAME, $db_version );
    }

    public static function delete_db_version(): bool {
        return delete_option( self::DB_VERSION_OPTION_NAME );
    }

    // --------------------------------------------------

    private const CURRENT_EVENT_ID_OPTION_NAME = "kjg_ticketing_current_event_id";

    public static function update_current_event_id( int $current_event_id ): bool {
        return update_option( self::CURRENT_EVENT_ID_OPTION_NAME, $current_event_id );
    }

    public static function get_current_event_id(): int|false {
        $current_event_id = get_option( self::CURRENT_EVENT_ID_OPTION_NAME );
        if ( ! $current_event_id ) {
            return false;
        }

        return (int) $current_event_id;
    }

    public static function delete_current_event_id(): bool {
        return delete_option( self::CURRENT_EVENT_ID_OPTION_NAME );
    }

    // --------------------------------------------------

    private const IS_HTTP_ALLOWED_OPTION_NAME = "kjg_ticketing_is_http_allowed";

    public static function add_is_http_allowed( bool $http_allowed ): bool {
        return add_option( self::IS_HTTP_ALLOWED_OPTION_NAME, $http_allowed );
    }

    public static function update_is_http_allowed( bool $http_allowed ): bool {
        return update_option( self::IS_HTTP_ALLOWED_OPTION_NAME, $http_allowed );
    }

    public static function is_http_allowed(): bool {
        $is_http_allowed = get_option( self::IS_HTTP_ALLOWED_OPTION_NAME );
        if ( ! $is_http_allowed ) {
            return false;
        }

        return $is_http_allowed == 1;
    }

    public static function delete_is_http_allowed(): bool {
        return delete_option( self::IS_HTTP_ALLOWED_OPTION_NAME );
    }
}