<?php
namespace KjG_Ticketing;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */
class KjG_Ticketing_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain(): void {

		load_plugin_textdomain(
			'kjg-ticketing',
			false,
			dirname(plugin_basename(__FILE__), 2) . '/languages/'
		);

	}
}
