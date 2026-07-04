<?php
/**
 * PSR-4 Autoloader for WC26Predictor.
 *
 * @package WC26Predictor
 */

declare(strict_types=1);

namespace WC26Predictor;

spl_autoload_register( function ( string $class ) {
	$prefix   = 'WC26Predictor\\';
	$base_dir = __DIR__ . '/';

	// Check if the class uses our namespace.
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );
