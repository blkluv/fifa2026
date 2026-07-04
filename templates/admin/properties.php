<?php
/**
 * Admin Template: Properties Management
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$properties = $wpdb->get_results(
	"SELECT p.*, r.name AS region_name 
	 FROM {$wpdb->prefix}wc26_properties p
	 LEFT JOIN {$wpdb->prefix}wc26_regions r ON r.id = p.region_id
	 ORDER BY p.name",
	ARRAY_A
);
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'Properties', 'wc26-predictor' ); ?></h1>
	<p><?php printf( esc_html__( '%d properties registered.', 'wc26-predictor' ), count( $properties ) ); ?></p>

	<p>
		<button type="button" class="button button-primary" id="wc26-seed-sample-properties">
			<?php esc_html_e( 'Auto-import Sample Properties', 'wc26-predictor' ); ?>
		</button>
		<span id="wc26-seed-properties-status" style="margin-right:1rem;font-weight:600;"></span>
	</p>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Name', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Region', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Type', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'City', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Beds', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Baths', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Sq Ft', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $properties as $p ) : ?>
				<tr>
					<td><?php echo esc_html( $p['id'] ); ?></td>
					<td><strong><?php echo esc_html( $p['name'] ); ?></strong></td>
					<td><?php echo esc_html( $p['region_name'] ?? '—' ); ?></td>
					<td><?php echo esc_html( $p['property_type'] ); ?></td>
					<td><?php echo esc_html( $p['city'] ); ?></td>
					<td><?php echo $p['initial_price'] ? '$' . esc_html( number_format( (float) $p['initial_price'] ) ) : '—'; ?></td>
					<td><?php echo esc_html( $p['bedrooms'] ?? '—' ); ?></td>
					<td><?php echo esc_html( $p['bathrooms'] ?? '—' ); ?></td>
					<td><?php echo esc_html( $p['square_feet'] ?? '—' ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
