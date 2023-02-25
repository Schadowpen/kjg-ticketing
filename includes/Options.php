<?php

namespace KjG_Ticketing;

/**
 * Unifies WordPress Options handling to avoid misspelling and such
 */
class Options {

	public static function delete_all_options(): void {
		self::delete_db_version();
		self::delete_current_event_id();
	}

	// --------------------------------------------------
	private static string $db_version_option_name = "kjg_ticketing_db_version";

	public static function add_db_version( $db_version ): bool {
		return add_option( self::$db_version_option_name, $db_version );
	}

	public static function delete_db_version(): bool {
		return delete_option( self::$db_version_option_name );
	}

	// --------------------------------------------------

	private static string $current_event_id_option_name = "kjg_ticketing_current_event_id";

	public static function update_current_event_id( int $current_event_id ): bool {
		return update_option( self::$current_event_id_option_name, $current_event_id );
	}

	public static function get_current_event_id(): int|false {
		$current_event_id = get_option( self::$current_event_id_option_name );
		if ( ! $current_event_id ) {
			return false;
		}

		return (int) $current_event_id;
	}

	public static function delete_current_event_id(): bool {
		return delete_option( self::$current_event_id_option_name );
	}
}