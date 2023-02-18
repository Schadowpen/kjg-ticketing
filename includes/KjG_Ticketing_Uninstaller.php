<?php

/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 */
class KjG_Ticketing_Uninstaller {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 */
	public static function uninstall() {
		self::delete_database_tables();
	}

	private static function delete_database_tables() {
		self::delete_database_tables_active();
		self::delete_database_tables_template();

		delete_option( 'kjg_ticketing_db_version' );
	}

	private static function delete_database_tables_active() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_seat_state" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_shows" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_process_additional_entries" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_process_additional_fields" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_processes" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_seats" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_entrances" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_seating_plan_areas" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_ticket_image_config" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_ticket_text_config" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_events" );
	}

	private static function delete_database_tables_template() {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_shows" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_process_additional_fields" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_seat_groups" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_seats" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_entrances" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_seating_plan_areas" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_ticket_image_config" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_ticket_text_config" );
		$wpdb->query( "DROP TABLE IF EXISTS kjg_ticketing_template_events" );
	}

}