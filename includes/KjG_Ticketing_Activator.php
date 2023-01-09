<?php
require_once get_home_path() . 'wp-admin/includes/upgrade.php';

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
		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		dbDelta("CREATE TABLE kjg_ticketing_events (
			id int NOT NULL AUTO_INCREMENT,
			name char(100) UNIQUE NOT NULL,
			archived bit DEFAULT 0 NOT NULL,
			ticket_price DECIMAL(6,2) DEFAULT 5 NOT NULL,
			shipping_price DECIMAL(6,2) DEFAULT 2.5 NOT NULL,
			seating_plan_width FLOAT NOT NULL,
			seating_plan_length FLOAT NOT NULL,
			seat_width FLOAT NOT NULL,
			seat_length FLOAT NOT NULL,
			seating_plan_length_unit char(32) NOT NULL,
			PRIMARY KEY  (id)
			) $charset_collate;");
		
		dbDelta("CREATE TABLE kjg_ticketing_ticket_config (
			id int NOT NULL,
			template blob NOT NULL,
			seating_plan_seat_numbers_visible bit DEFAULT 0 NOT NULL,
			seating_plan_connect_arrows bit DEFAULT 1 NOT NULL,
			PRIMARY KEY  (id),
			FOREIGN KEY  (id) REFERENCES kjg_ticketing_events(id)
			) $charset_collate;");
	}
}
