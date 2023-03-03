<?php

namespace KjG_Ticketing;

class KjG_Ticketing_Security {

    public static function create_AJAX_nonce(): string {
        return wp_create_nonce( "kjg_ticketing" );
    }

    public static function is_site_access_allowed(): bool {
        // TODO restrict access to sites
        if ( ! current_user_can( "kjg_ticketing_read" ) ) {
            return false;
        }

        if ( ! is_ssl() && ! Options::is_http_allowed() ) {
            return false;
        }

        return true;
    }

    public static function validate_AJAX_read_permission(): void {
        self::validate_nonce();

        // check user capabilities
        if ( ! current_user_can( "kjg_ticketing_read" ) ) {
            wp_die( "Error: User is not allowed to use the KjG_Ticketing read API", 403 );
        }

        self::validate_HTTPS();
    }

    public static function validate_AJAX_write_permission(): void {
        self::validate_nonce();

        // check user capabilities
        if ( ! current_user_can( "kjg_ticketing_write" ) ) {
            wp_die( "Error: User is not allowed to use the KjG_Ticketing write API", 403 );
        }

        self::validate_HTTPS();
    }

    public static function validate_AJAX_no_permission(): void {
        self::validate_nonce();
        self::validate_HTTPS();
    }

    public static function validate_download_permission(): void {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'kjg_ticketing' ) ) {
            wp_die( "Error: Authentication error", 403 );
        }

        // check user capabilities
        if ( ! current_user_can( "kjg_ticketing_read" ) ) {
            wp_die( "Error: Authentication error", 403 );
        }

        self::validate_HTTPS();
    }

    private static function validate_nonce(): void {
        check_ajax_referer( 'kjg_ticketing' );
        // dies with 403 if nonce is invalid
    }

    private static function validate_HTTPS(): void {
        if ( ! is_ssl() && ! Options::is_http_allowed() ) {
            wp_die( "Error: Access is only allowed over HTTPS", 403 );
        }
    }

    public static function create_user_roles(): void {
        add_role(
            "kjg_ticketing_event_manager",
            "Event Manager",
            array(
                "kjg_ticketing_read",
                "kjg_ticketing_write"
            )
        );

        $admin_role = get_role( "administrator" );
        $admin_role->add_cap( "kjg_ticketing_read" );
        $admin_role->add_cap( "kjg_ticketing_write" );
    }

    public static function delete_user_roles(): void {
        remove_role( "kjg_ticketing_event_manager" );
    }
}