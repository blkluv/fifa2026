<?php
/**
 * Admin Template: Regions Management
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$regions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc26_regions ORDER BY country, state, name", ARRAY_A );
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'Regions', 'wc26-predictor' ); ?></h1>
	<p><?php printf( esc_html__( '%d regions registered.', 'wc26-predictor' ), count( $regions ) ); ?></p>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Name', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Country', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'State', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Description', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $regions as $r ) : ?>
				<tr>
					<td><?php echo esc_html( $r['id'] ); ?></td>
					<td><strong><?php echo esc_html( $r['name'] ); ?></strong></td>
					<td><code><?php echo esc_html( $r['slug'] ); ?></code></td>
					<td><?php echo esc_html( $r['country'] ); ?></td>
					<td><?php echo esc_html( $r['state'] ); ?></td>
					<td><?php echo esc_html( $r['description'] ?? '—' ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
