<?php

use KjG_Ticketing\ApiHelper;
use KjG_Ticketing\database\DatabaseOverview;
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
    // |---------------------------|

    public function get_entrances(): void {
        ApiHelper::validateDatabaseUsageAllowed( true, true, true );
        $dbc = ApiHelper::getAbstractDatabaseConnection( new DatabaseOverview() );
        wp_send_json( $dbc->get_entrances() );
    }

}
