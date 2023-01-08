<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class KjG_Ticketing_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 */
	public static function activate() {
		self::create_database_tables();
	}

	private static function create_database_tables() {
		require_once get_home_path() . 'wp-admin/includes/upgrade.php';
		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta("CREATE TABLE kjg_ticketing_events (
			id int NOT NULL AUTO_INCREMENT,
			name char(100) UNIQUE NOT NULL,
			ticket_price DECIMAL(6,2) DEFAULT 5 NOT NULL,
			shipping_price DECIMAL(6,2) DEFAULT 2.5 NOT NULL,
			PRIMARY KEY  (id)
			) $charset_collate;");
	}
}
