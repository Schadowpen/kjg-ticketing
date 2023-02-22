<?php
/*
 * This file enables automatic loading of classes that are part of the "KjG_Ticketing" namespace.
 * Therefore, the class has to be located in the "includes" folder and the class name has to match the file name.
 * If a class is located in a sub folder of "includes", this should be added to the namespace definition.
 */

spl_autoload_register('kjg_ticketing_autoloader');
function kjg_ticketing_autoloader( $class_name ): void {
    if ( strncmp( $class_name, 'KjG_Ticketing', 13 ) === 0 ) {
        $classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . "/includes";
        $path = str_replace("\\", "/", substr($class_name, 13)) . ".php";
        require_once $classes_dir . $path;
    }
}

// TODO load files that are no classes
