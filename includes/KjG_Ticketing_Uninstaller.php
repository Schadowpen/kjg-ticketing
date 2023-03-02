<?php

namespace KjG_Ticketing;

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
    public static function uninstall(): void {
        \KjG_Ticketing\database\DatabaseUninstaller::delete_database_tables();
        \KjG_Ticketing\Options::delete_all_options();
        KjG_Ticketing_Security::delete_user_roles();
    }

}