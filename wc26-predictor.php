<?php
/**
 * Plugin Name:       پیش‌بینی مسابقات جام جهانی 2026
 * Plugin URI:        https://github.com/updesire
 * Description:       سیستم اختصاصی شرکت کادک جهت پیش‌بینی مسابقات جام جهانی 2026
 * Version:           2.5.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            سوران
 * Author URI:        https://soraun.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc26-predictor
 * Domain Path:       /languages
 *
 * @package WC26Predictor
 */

declare(strict_types=1);

namespace WC26Predictor;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'WC26_VERSION', '2.5.0' );
define( 'WC26_PLUGIN_FILE', __FILE__ );
define( 'WC26_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC26_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WC26_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WC26_DB_VERSION', '1.1.1' );

// Autoloader
require_once WC26_PLUGIN_DIR . 'includes/Autoloader.php';
Autoloader::register();

// Activation / Deactivation hooks
register_activation_hook( __FILE__, [ Plugin::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Plugin::class, 'deactivate' ] );

// Boot the plugin
add_action( 'plugins_loaded', function () {
	Plugin::getInstance()->boot();
} );
