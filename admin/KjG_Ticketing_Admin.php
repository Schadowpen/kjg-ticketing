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
    public function __construct(string $plugin_name, string $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles(): void {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/kjg-ticketing-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts(): void {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in KjG_Ticketing_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The KjG_Ticketing_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/kjg-ticketing-admin.js', array('jquery'), $this->version, false);

    }

    public function add_menu_pages(): void {
        require 'partials/kjg-ticketing-admin-display.php';
        $hookname = add_menu_page(
            "KjG Ticketing",
            "KjG Ticketing",
            "manage_options",
            'kjg-ticketing-admin-display', //plugin_dir_path(__FILE__) . 'partials/kjg-ticketing-admin-display.php',
            'kjg_ticketing_admin_display', //null
            'dashicons-tickets',
            100
        );
        add_action('load-' . $hookname, array($this, 'admin_display_submit_callback'));
    }

    public function admin_display_submit_callback(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        check_admin_referer('kjg-ticketing-admin-display');

        // here comes validation and sanitization
    }
}
