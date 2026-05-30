<?php /* Admin Template: teams.php */
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$teams = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wc26_teams ORDER BY name", ARRAY_A );
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'تیم‌ها', 'wc26-predictor' ); ?></h1>
	<p><?php printf( esc_html__( '%d تیم ثبت شده است.', 'wc26-predictor' ), count( $teams ) ); ?></p>

	<p>
		<button type="button" class="button button-primary" id="wc26-seed-sample-teams">
			<?php esc_html_e( 'درون‌ریزی خودکار تیم‌های نمونه', 'wc26-predictor' ); ?>
		</button>
		<span id="wc26-seed-teams-status" style="margin-right:1rem;font-weight:600;"></span>
	</p>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead><tr><th><?php esc_html_e( 'شناسه', 'wc26-predictor' ); ?></th><th><?php esc_html_e( 'کد', 'wc26-predictor' ); ?></th><th><?php esc_html_e( 'نام', 'wc26-predictor' ); ?></th><th><?php esc_html_e( 'قاره', 'wc26-predictor' ); ?></th><th><?php esc_html_e( 'شناسه FIFA', 'wc26-predictor' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $teams as $t ) : ?>
				<tr>
					<td><?php echo esc_html( $t['id'] ); ?></td>
					<td><strong><?php echo esc_html( $t['code'] ); ?></strong></td>
					<td><?php echo esc_html( $t['name'] ); ?></td>
					<td><?php echo esc_html( $t['continent'] ); ?></td>
					<td><?php echo esc_html( $t['fifa_id'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
