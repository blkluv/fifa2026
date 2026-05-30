<?php /* Admin Template: groups.php */
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc26_groups ORDER BY season, name", ARRAY_A );
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'گروه‌ها', 'wc26-predictor' ); ?></h1>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead><tr><th><?php esc_html_e( 'شناسه', 'wc26-predictor' ); ?></th><th><?php esc_html_e( 'نام', 'wc26-predictor' ); ?></th><th><?php esc_html_e( 'فصل', 'wc26-predictor' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $groups as $g ) : ?>
				<tr>
					<td><?php echo esc_html( $g['id'] ); ?></td>
					<td><?php echo esc_html( $g['name'] ); ?></td>
					<td><?php echo esc_html( $g['season'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
