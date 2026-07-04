<?php
/**
 * Admin Template: Dashboard - Real Estate Prediction Market
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$stats = [
	'properties'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_properties" ),
	'markets'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_markets" ),
	'predictions' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_predictions" ),
	'users'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_leaderboards" ),
	'leagues'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_mini_leagues" ),
	'badges'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_user_badges" ),
];
?>
<div class="wrap wc26-admin-wrap">
	<div class="wc26-admin-header">
		<h1><?php esc_html_e( 'Real Estate Prediction Market Dashboard', 'wc26-predictor' ); ?></h1>
		<span style="color:#888; font-size:0.85rem;"><?php echo esc_html( 'v' . WC26_VERSION ); ?></span>
	</div>

	<div class="wc26-stat-grid">
		<?php
		$cards = [
			[ __( 'Properties', 'wc26-predictor' ),       $stats['properties'],   '🏠' ],
			[ __( 'Active Markets', 'wc26-predictor' ),    $stats['markets'],      '📊' ],
			[ __( 'Predictions', 'wc26-predictor' ),       $stats['predictions'],  '🎯' ],
			[ __( 'Participants', 'wc26-predictor' ),      $stats['users'],        '👤' ],
			[ __( 'Mini Leagues', 'wc26-predictor' ),      $stats['leagues'],      '🏆' ],
			[ __( 'Badges Earned', 'wc26-predictor' ),     $stats['badges'],       '⭐' ],
		];
		foreach ( $cards as [ $label, $val, $icon ] ) :
		?>
		<div class="wc26-stat-card">
			<h3><?php echo esc_html( trim( $icon . ' ' . $label ) ); ?></h3>
			<div class="wc26-stat-val"><?php echo esc_html( number_format( $val ) ); ?></div>
		</div>
		<?php endforeach; ?>
	</div>

	<h2><?php esc_html_e( 'Quick Actions', 'wc26-predictor' ); ?></h2>
	<p>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-markets' ) ); ?>"><?php esc_html_e( 'Manage Markets', 'wc26-predictor' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-csv-import' ) ); ?>"><?php esc_html_e( 'CSV Import', 'wc26-predictor' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-leaderboard' ) ); ?>"><?php esc_html_e( 'Leaderboard', 'wc26-predictor' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-chainlink-cre' ) ); ?>"><?php esc_html_e( 'Chainlink CRE', 'wc26-predictor' ); ?></a>
	</p>

	<h2><?php esc_html_e( 'REST API', 'wc26-predictor' ); ?></h2>
	<p><?php esc_html_e( 'Base URL:', 'wc26-predictor' ); ?>
		<code><?php echo esc_html( rest_url( 'wc26/v1' ) ); ?></code>
	</p>
</div>
