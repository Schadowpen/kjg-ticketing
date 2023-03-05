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

        'Complex\\'                   => __DIR__ . '/lib/Complex/', // used by PhpSpreadsheet
        'Matrix\\'                    => __DIR__ . '/lib/Matrix/', // used by PhpSpreadsheet
        'MyCLabs\\Enum\\'             => __DIR__ . '/lib/MyCLabs/Enum/', // used by ZipStream
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/lib/PhpOffice/PhpSpreadsheet/', // generate Excel files
        // 'phpqrcode\\' (no autoloader support)   generate QR Codes
        'Psr\\'                       => __DIR__ . '/lib/Psr/', // used by PhpSpreadsheet & ZipStream
        'ZipStream\\'                 => __DIR__ . '/lib/ZipStream/', // used by PhpSpreadsheet
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
