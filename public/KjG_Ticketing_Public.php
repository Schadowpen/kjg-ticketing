<?php

use KjG_Ticketing\api\Overview;
use KjG_Ticketing\api\ProcessesWithInfo;
use KjG_Ticketing\api\SeatingPlan;
use KjG_Ticketing\api\ShowsWithStates;
use KjG_Ticketing\api\VisitorsXlsx;
use KjG_Ticketing\ApiHelper;
use KjG_Ticketing\database\DatabaseOverview;
use KjG_Ticketing\DownloadHelper;
use KjG_Ticketing\KjG_Ticketing_Security;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 */
class KjG_Ticketing_Public {

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
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( string $plugin_name, string $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles(): void {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in KjG_Ticketing_Loader as all the hooks are defined
         * in that particular class.
         *
         * The KjG_Ticketing_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kjg-ticketing-public.css', array(), $this->version, 'all' );

        // TODO only enqueue styles for certain hooks
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts(): void {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in KjG_Ticketing_Loader as all the hooks are defined
         * in that particular class.
         *
         * The KjG_Ticketing_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kjg-ticketing-public.js', array( 'jquery' ), $this->version, false );

        wp_localize_script(
            $this->plugin_name,
            'my_ajax_obj',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => KjG_Ticketing_Security::create_AJAX_nonce()
            )
        );

        // TODO only enqueue scripts for certain hooks
    }

    // |---------------------------|
    // |  Start of AJAX Endpoints  |
    // |    AJAX get-Endpoints     |
    // |---------------------------|

    public function get_archived_databases(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        $dbo = new DatabaseOverview();
        wp_send_json( $dbo->getArchivedDatabaseNames() );
    }

    public function get_available_fonts(): void {
        // TODO implement
        wp_die( "Error: not yet implemented", 501 );
    }

    public function get_entrances(): void {
        KjG_Ticketing_Security::validate_AJAX_no_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_entrances() );
    }

    public function get_event(): void {
        KjG_Ticketing_Security::validate_AJAX_no_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        $event = $dbc->get_event();
        if ( ! $event ) {
            wp_die();
        }
        wp_send_json( $event );
    }

    public function get_overview(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        wp_send_json( Overview::get( $dbc ) );
    }

    public function get_process(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        $process_id = ApiHelper::validate_and_get_process_id();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_process( $process_id ) );
    }

    public function get_processes_with_info(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        wp_send_json( ProcessesWithInfo::get( $dbc ) );
    }

    public function get_seating_plan(): void {
        KjG_Ticketing_Security::validate_AJAX_no_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        wp_send_json( SeatingPlan::get( $dbc ) );
    }

    public function get_seating_plan_areas(): void {
        KjG_Ticketing_Security::validate_AJAX_no_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_seating_plan_areas() );
    }

    public function get_seats(): void {
        KjG_Ticketing_Security::validate_AJAX_no_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_seats() );
    }

    public function get_seat_groups(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        ApiHelper::validateDatabaseUsageAllowed( false, false, true );
        $dbc = ApiHelper::getTemplateDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_seat_groups() );
    }

    public function get_seat_states(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_seat_states() );
    }

    public function get_shows(): void {
        KjG_Ticketing_Security::validate_AJAX_no_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_shows() );
    }

    public function get_shows_with_states(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        wp_send_json( ShowsWithStates::get( $dbc ) );
    }

    public function get_template_databases(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        $dbo = new DatabaseOverview();
        wp_send_json( $dbo->getTemplateDatabaseNames() );
    }

    public function get_ticket_config(): void {
        KjG_Ticketing_Security::validate_AJAX_read_permission();
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_ticket_config() );
    }

    public function get_ticket_template_positions(): void {
        // TODO implement
        wp_die( "Error: not yet implemented", 501 );
    }

    // |----------------------|
    // |  Download endpoints  |
    // |----------------------|

    public function download_visitors_xlsx(): void {
        if ( $_GET['action'] !== "kjg_ticketing_download_visitors_xlsx" ) {
            return; // don't execute download action by accident
        }

        KjG_Ticketing_Security::validate_download_permission();
        $show_id = DownloadHelper::validate_and_get_show_id_if_present();
        ApiHelper::validateDatabaseUsageAllowed( true, true, false );
        $dbc = ApiHelper::getDatabaseConnection( new DatabaseOverview() );
        VisitorsXlsx::get( $dbc, $show_id );
    }

}
