<?php

namespace KjG_Ticketing\database;

require_once get_home_path() . 'wp-admin/includes/upgrade.php';

global $kjg_ticketing_db_version;
$kjg_ticketing_db_version = '1.0';

class DatabaseInstaller {

	public static function create_database_tables(): void {
		global $wpdb;
		global $kjg_ticketing_db_version;

		$charset_collate = $wpdb->get_charset_collate();

		self::create_database_tables_active($charset_collate);
		self::create_database_tables_template($charset_collate);

		// TODO this option is not set correctly
		\KjG_Ticketing\Options::add_db_version($kjg_ticketing_db_version);
	}

	private static function create_database_tables_active($charset_collate): void {

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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id)
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id)
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_seating_plan_areas (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_entrances (
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_entrances (id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_entrances (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_seats (
			event_id int NOT NULL,
			seat_block char(50) NOT NULL,
			seat_row char(2) NOT NULL,
			seat_number int NOT NULL,
			position_x float NOT NULL,
			position_y float NOT NULL,
			rotation float NOT NULL,
			width float NOT NULL,
			length float NOT NULL,
			entrance_id int,
			PRIMARY KEY  (event_id, seat_block, seat_row, seat_number),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_entrances (id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_seats (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_processes (
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
			ticket_generated bit DEFAULT 0 NOT NULL,
			PRIMARY KEY  (id, event_id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_processes (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_process_additional_fields (
            id int NOT NULL AUTO_INCREMENT,
            event_id int NOT NULL,
            description varchar(255) NOT NULL,
            data_type enum('integer', 'float', 'string', 'longString', 'boolean') NOT NULL,
            required bit NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id)
        ) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_process_additional_fields (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_process_additional_entries (
            event_id int NOT NULL,
            process_id int NOT NULL,
            field_id int NOT NULL,
            integer_value int,
            float_value float,
            string_value varchar(2000),
            boolean_value bit,
            PRIMARY KEY  (event_id, process_id, field_id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id),
			FOREIGN KEY  (process_id) REFERENCES kjg_ticketing_processes (id) ON DELETE CASCADE,
			FOREIGN KEY  (field_id) REFERENCES kjg_ticketing_process_additional_fields (id)
        ) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_process_additional_entries (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_shows (
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			show_date date NOT NULL,
			show_time char(5) NOT NULL,
            PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_events (id)
		) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_shows (event_id);");

		// TODO Foreign key referencing kjg_ticketing_seats is not created
		dbDelta("CREATE TABLE kjg_ticketing_seat_state (
			event_id int NOT NULL,
			seat_block char(50) NOT NULL,
			seat_row char(2) NOT NULL,
			seat_number int NOT NULL,
			show_id int NOT NULL,
			state enum('available', 'reserved', 'booked', 'blocked', 'present') DEFAULT 'available' NOT NULL,
			process_id int,
			PRIMARY KEY  (event_id, seat_block, seat_row, seat_number, show_id),
			CONSTRAINT FK_seat FOREIGN KEY  (event_id, seat_block, seat_row, seat_number) REFERENCES kjg_ticketing_seats (event_id, seat_block, seat_row, seat_number),
			FOREIGN KEY  (show_id) REFERENCES kjg_ticketing_shows (id),
			FOREIGN KEY  (process_id) REFERENCES kjg_ticketing_processes (id) ON DELETE CASCADE
		) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_seat_state (event_id);");
	}

	private static function create_database_tables_template($charset_collate): void {

		dbDelta("CREATE TABLE kjg_ticketing_template_events (
			id int NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			ticket_price decimal(6,2) DEFAULT 5 NOT NULL,
			shipping_price decimal(6,2) DEFAULT 2.5 NOT NULL,
			seating_plan_width float,
			seating_plan_length float,
			seating_plan_length_unit char(32),
			ticket_template blob,
			ticket_seating_plan_seat_numbers_visible bit DEFAULT 0 NOT NULL,
			ticket_seating_plan_connect_arrows bit DEFAULT 1 NOT NULL,
			PRIMARY KEY  (id)
			) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_ticket_text_config (
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id)
			) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_ticket_image_config (
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id)
			) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_seating_plan_areas (
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id)
			) $charset_collate;");
		dbDelta("CREATE INDEX idx_event_id ON kjg_ticketing_seating_plan_areas (event_id);");

		dbDelta("CREATE TABLE kjg_ticketing_template_entrances (
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
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_template_entrances (id)
			) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_seats (
			event_id int NOT NULL,
			seat_block char(50) NOT NULL,
			seat_row char(2) NOT NULL,
			seat_number int NOT NULL,
			position_x float NOT NULL,
			position_y float NOT NULL,
			rotation float NOT NULL,
			width float NOT NULL,
			length float NOT NULL,
			entrance_id int,
			PRIMARY KEY  (event_id, seat_block, seat_row, seat_number),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_template_entrances (id)
			) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_seat_groups (
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			block char(50) NOT NULL,
			row_front char(2) NOT NULL,
			row_back char(2) NOT NULL,
			row_distance float NOT NULL,
			seat_number_left int NOT NULL,
			seat_number_right int NOT NULL,
			seat_distance float NOT NULL,
			position_x float NOT NULL,
			position_y float NOT NULL,
			rotation float NOT NULL,
			seat_width float NOT NULL,
			seat_length float NOT NULL,
			entrance_id int,
			PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id),
			FOREIGN KEY  (entrance_id) REFERENCES kjg_ticketing_template_entrances (id)
			) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_process_additional_fields (
            id int NOT NULL AUTO_INCREMENT,
            event_id int NOT NULL,
            description varchar(255) NOT NULL,
            data_type enum('integer', 'float', 'string', 'longString', 'boolean') NOT NULL,
            required bit NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id)
        ) $charset_collate;");

		dbDelta("CREATE TABLE kjg_ticketing_template_shows (
			id int NOT NULL AUTO_INCREMENT,
			event_id int NOT NULL,
			show_date date NOT NULL,
			show_time char(5) NOT NULL,
            PRIMARY KEY  (id),
			FOREIGN KEY  (event_id) REFERENCES kjg_ticketing_template_events (id)
		) $charset_collate;");
	}

}