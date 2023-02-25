<?php

namespace KjG_Ticketing\database;

/**
 * This class gives an overview over all events stored in the database.
 * Please only instantiate one element of this class.
 *
 * TODO This class is a relict of the old times, where each event was stored in its own database.
 * It should be refactored, at least when support for multiple active events at a time is implemented. Therefore
 * - Naming convention should completely go over from databases to events
 * - distinguishing between events should move over from names to their IDs.
 * - the kjg_ticketing_current_event option should not be used any more.
 * - the "archived" field in the kjg_ticketing_events page should be used.
 */
class DatabaseOverview {

	public function getEvents(): array {
		global $wpdb;

		return $wpdb->get_results( "SELECT * FROM kjg_ticketing_events", OBJECT );
	}

	public function getArchivedDatabaseNames(): array {
		global $wpdb;

		$current_event_id = \KjG_Ticketing\Options::get_current_event_id();
		if ( $current_event_id ) {
			$sql = $wpdb->prepare( "SELECT name FROM kjg_ticketing_events WHERE id IS NOT %d", $current_event_id );
			$all_event_names = $wpdb->get_col( $sql );
		} else {
			$all_event_names = $wpdb->get_col( "SELECT name FROM kjg_ticketing_events" );
		}

		return $all_event_names;
	}

	public function getTemplateDatabaseNames(): array {
		global $wpdb;

		return $wpdb->get_col( "SELECT name FROM kjg_ticketing_template_events" );
	}
}