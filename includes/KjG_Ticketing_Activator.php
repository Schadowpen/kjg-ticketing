<?php

namespace KjG_Ticketing;

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
    public static function activate(): void {
        \KjG_Ticketing\database\DatabaseInstaller::create_database_tables();
        Options::add_default_options();
        KjG_Ticketing_Security::create_user_roles();
    }

}
