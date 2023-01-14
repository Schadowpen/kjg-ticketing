<?php

/**
 * Plugin Name: Kjg Ticketing
 * Plugin URI: https://github.com/Schadowpen/kjgTicketing
 * Description: WordPress Plugin to provide a ticketing system for the KjG theater
 * Version: 0.1.1
 * Author: Philipp Horwat
 * Requires PHP: 7.4
 * License: GPL2
 * Text Domain: kjg-ticketing
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('KJG_TICKETING_VERSION', '0.1.1');

// TODO autoload.php

/**
 * The code that runs during plugin activation.
 */
function activate_kjg_ticketing() {
    require_once plugin_dir_path(__FILE__) . 'includes/KjG_Ticketing_Activator.php';
    KjG_Ticketing_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_kjg_ticketing() {
    require_once plugin_dir_path(__FILE__) . 'includes/KjG_Ticketing_Deactivator.php';
    KjG_Ticketing_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 * (This is done to have similar activate and uninstall code, despite placing this functionality into the uninstall.php file)
 */
function uninstall_kjg_ticketing() {
    require_once plugin_dir_path(__FILE__) . 'includes/KjG_Ticketing_Uninstaller.php';
    KjG_Ticketing_Uninstaller::uninstall();
}

register_activation_hook(__FILE__, 'activate_kjg_ticketing');
register_deactivation_hook(__FILE__, 'deactivate_kjg_ticketing');
register_uninstall_hook(__FILE__, 'uninstall_kjg_ticketing');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/KjG_Ticketing.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kjg_ticketing() {

    $plugin = new KjG_Ticketing();
    $plugin->run();

}

run_kjg_ticketing();
