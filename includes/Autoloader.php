<?php
/**
 * PSR-4 Autoloader for WC26Predictor namespace.
 *
 * @package WC26Predictor
 */

declare(strict_types=1);

namespace WC26Predictor;

class Autoloader {

	public static function register(): void {
		spl_autoload_register( [ self::class, 'load' ] );
	}

	public static function load( string $class ): void {
		$prefix = 'WC26Predictor\\';
		$base   = WC26_PLUGIN_DIR . 'includes/';

		if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = $base . str_replace( '\\', '/', $relative ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
			return;
		}

		// Back-compat: repositories are bundled in a single file.
		if ( str_starts_with( $class, 'WC26Predictor\\Repositories\\' ) ) {
			$repoBundle = $base . 'Repositories/Repositories.php';
			if ( file_exists( $repoBundle ) ) {
				require_once $repoBundle;
			}
		}
	}
}
