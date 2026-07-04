<?php
/**
 * Plugin Name: CRE Predictor
 * Plugin URI:  https://cremarket.io
 * Description: A production-grade, scalable CRE prediction platform built as a WordPress plugin. Designed for large traffic, REST-API-first, and extensible to Telegram Mini App, PWA, and mobile apps.
 * Version:     1.0.0
 * Author:      Hahz Terry
 * Author URI:  https://cremarket.io
 * Text Domain: cre-predictor
 * Domain Path: /languages
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package WC26Predictor
 */

declare(strict_types=1);

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'WC26_VERSION', '1.0.0' );
define( 'WC26_DB_VERSION', '2.0.0' );
define( 'WC26_PLUGIN_FILE', __FILE__ );
define( 'WC26_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC26_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC26_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load autoloader.
require_once WC26_PLUGIN_DIR . 'includes/Autoloader.php';

// Register activation/deactivation hooks.
register_activation_hook( __FILE__, [ 'WC26Predictor\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'WC26Predictor\\Plugin', 'deactivate' ] );

// Boot the plugin.
add_action( 'plugins_loaded', function () {
	$plugin = WC26Predictor\Plugin::getInstance();
	$plugin->boot();
} );
