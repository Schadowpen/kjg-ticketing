<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class KjG_Ticketing_Admin {

    /**
     * The ID of this plugin.
     *
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private string $plugin_name;

    /**
     * The version of this plugin.
     *
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private string $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( string $plugin_name, string $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles( string $hook ): void {

        // only enqueue styles on admin page of kjg-ticketing
        if ( $hook !== get_plugin_page_hookname( 'kjg-ticketing-admin-display', "" ) ) {
            return;
        }

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kjg-ticketing-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts( string $hook ): void {

        // only enqueue scripts on admin page of kjg-ticketing
        if ( $hook !== get_plugin_page_hookname( 'kjg-ticketing-admin-display', "" ) ) {
            return;
        }

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kjg-ticketing-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function add_menu_pages(): void {
        require 'partials/kjg-ticketing-admin-display.php';
        $hookname = add_menu_page(
            "KjG Ticketing",
            "KjG Ticketing",
            "manage_options",
            'kjg-ticketing-admin-display',
            'kjg_ticketing_admin_display',
            'dashicons-tickets',
            100
        );
        add_action( 'load-' . $hookname, array( $this, 'admin_display_submit_callback' ) );
    }

    public function admin_display_submit_callback(): void {
        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return;
        }
        check_admin_referer( 'kjg-ticketing-admin-display' );

        // validation and sanitization
        if ( ! ( isset( $_POST["current_event_id"] ) && ctype_digit( $_POST["current_event_id"] ) ) ) {
            return;
        }
        $current_event_id = (int) $_POST["current_event_id"];

        $is_http_allowed = isset( $_POST["http_allowed"] ) && $_POST["http_allowed"] === "on";

        // store new settings
        \KjG_Ticketing\Options::update_current_event_id( $current_event_id );
        \KjG_Ticketing\Options::update_is_http_allowed( $is_http_allowed );
    }
}
