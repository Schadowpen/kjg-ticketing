<?php

namespace KjG_Ticketing\database;

class DatabaseUninstaller {

	public static function delete_database_tables(): void {
		self::delete_database_tables_active();
		self::delete_database_tables_template();
	}

	private static function delete_database_tables_active(): void {
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

	private static function delete_database_tables_template(): void {
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