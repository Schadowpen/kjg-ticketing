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
			name varchar(255) UNIQUE NOT NULL,
			archived bit DEFAULT 0 NOT NULL,
			ticket_price decimal(6,2) DEFAULT 5 NOT NULL,
			shipping_price decimal(6,2) DEFAULT 2.5 NOT NULL,
			seating_plan_width float NOT NULL,
			seating_plan_length float NOT NULL,
			seating_plan_length_unit char(32) NOT NULL,
			ticket_template blob NOT NULL,
			ticket_seating_plan_seat_numbers_visible bit DEFAULT 0 NOT NULL,
			ticket_seating_plan_connect_arrows bit DEFAULT 1 NOT NULL,
			PRIMARY KEY  (id)
			) $charset_collate;");
		
		dbDelta("CREATE TABLE kjg_ticketing_ticket_text_config (
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			content enum('date', 'time', 'block', 'row', 'seat', 'price', 'payment_status', 'event_id') NOT NULL,
			position_x float NOT NULL,
			position_y float NOT NULL,
			alignment enum('left', 'center', 'right') NOT NULL,
			font char(50) NOT NULL,
			fontSize float NOT NULL,
			color_red tinyint NOT NULL,
			color_green tinyint NOT NULL,
			color_blue tinyint NOT NULL,
			PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events(id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_ticket_text_config (event_id);");
		
		dbDelta("CREATE TABLE kjg_ticketing_ticket_image_config (
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			content enum('qr_code', 'seating_plan') NOT NULL,
			pdf_operator_number int,
			pdf_operator_name char(32),
			pdf_resource_deletable bit,
			pdf_content_stream_start_operator_index int,
			pdf_content_stream_num_operators int,
			lower_left_corner_x float NOT NULL,
			lower_left_corner_y float NOT NULL,
			lower_right_corner_x float NOT NULL,
			lower_right_corner_y float NOT NULL,
			upper_left_corner_x float NOT NULL,
			upper_left_corner_y float NOT NULL,
			font char(50),
			fontSize float,
			line_width float,
			PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events(id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_ticket_image_config (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_seating_plan_areas (
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			position_x float NOT NULL,
			position_y float NOT NULL,
			width float NOT NULL,
			length float NOT NULL,
			color char(7) NOT NULL,
			text varchar(1023),
			text_position_x float,
			text_position_y float,
			text_color char(7),
			PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events(id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_seating_plan_areas (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_entrances(
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			x0 float NOT NULL,
			y0 float NOT NULL,
			x1 float NOT NULL,
			y1 float NOT NULL,
			x2 float NOT NULL,
			y2 float NOT NULL,
			x3 float NOT NULL,
			y3 float NOT NULL,
			text varchar(1023),
			text_position_x float,
			text_position_y float,
			entrance_id int,
			PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events(id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_entrances(id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_entrances (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_seats(
			event_id int NOT NULL,
			block char(50) NOT NULL,
			row char(2) NOT NULL,
			seat int NOT NULL,
			position_x float NOT NULL,
			position_y float NOT NULL,
			rotation float NOT NULL,
			width float NOT NULL,
			length float NOT NULL,
			entrance_id int,
			PRIMARY KEY  (event_id, block, row, seat),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events(id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_entrances(id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_seats (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_processes(
			id int NOT NULL CHECK (id BETWEEN 1 and 999999999),
			event_id int NOT NULL,
			first_name varchar(255),
			last_name varchar(255),
			address varchar(1000),
			phone varchar(31),
			email varchar(255),
			ticket_price decimal(6,2),
			payment_method enum('cash', 'bank', 'box_office') NOT NULL,
			payment_state enum('open', 'paid', 'box_office') NOT NULL,
			shipping enum('pick_up', 'mail', 'email') NOT NULL,
			comment varchar(2000),
			ticket_url varchar(255),
			PRIMARY KEY  (id, event_id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events(id),
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_processes (event_id);");
	}
}
