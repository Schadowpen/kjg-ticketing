<?php
/*
 * This file enables automatic loading of classes that are part of the "KjG_Ticketing" namespace.
 * Therefore, the class has to be located in the "includes" folder and the class name has to match the file name.
 * If a class is located in a sub folder of "includes", this should be added to the namespace definition.
 */

defined( 'WPINC' ) || exit;

spl_autoload_register( function ( $class_name ): void {
	$namespaces = [
		'KjG_Ticketing\\' => __DIR__ . '/includes/',
	];
	foreach ( $namespaces as $prefix => $baseDir ) {
		$len = strlen( $prefix );
		if ( 0 !== strncmp( $prefix, $class_name, $len ) ) {
			continue;
		}
		$file = $baseDir . str_replace( '\\', '/', substr( $class_name, $len ) ) . '.php';
		if ( ! file_exists( $file ) ) {
			continue;
		}
		require $file;
		break;
	}
} );
