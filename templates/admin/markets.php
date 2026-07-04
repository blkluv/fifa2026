<?php
/**
 * Admin Template: Markets Management - Real Estate
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$markets_table = $wpdb->prefix . 'wc26_markets';
$properties_table = $wpdb->prefix . 'wc26_properties';
$regions_table = $wpdb->prefix . 'wc26_regions';

$markets = $wpdb->get_results(
	"SELECT m.*, p.name AS property_name, p.property_type, p.city,
	        r.name AS region_name, r.country, r.state
	 FROM {$markets_table} m
	 LEFT JOIN {$properties_table} p ON p.id = m.property_id
	 LEFT JOIN {$regions_table} r ON r.id = m.region_id
	 ORDER BY m.forecast_date ASC",
	ARRAY_A
);
?>

<div class="wrap wc26-admin-wrap">
	<div class="wc26-admin-header">
		<h1><?php esc_html_e( 'Manage Markets', 'wc26-predictor' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-csv-import' ) ); ?>" class="button"><?php esc_html_e( 'Import CSV', 'wc26-predictor' ); ?></a>
	</div>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th>#</th>
					<th><?php esc_html_e( 'Market', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Region', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Forecast Date', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Initial Price', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Trend', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Final Price', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Settle', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $markets ) ) : ?>
					<tr><td colspan="9"><?php esc_html_e( 'No markets created yet. Use CSV import to add markets.', 'wc26-predictor' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $markets as $m ) : ?>
						<?php
						$statusLabels = [
							'pending'   => __( 'Pending', 'wc26-predictor' ),
							'active'    => __( 'Active', 'wc26-predictor' ),
							'settled'   => __( 'Settled', 'wc26-predictor' ),
							'cancelled' => __( 'Cancelled', 'wc26-predictor' ),
						];
						$statusLabel = $statusLabels[ $m['status'] ] ?? $m['status'];
						$trendLabels = [
							'increase' => __( '📈 Increase', 'wc26-predictor' ),
							'decrease' => __( '📉 Decrease', 'wc26-predictor' ),
							'stable'   => __( '➡️ Stable', 'wc26-predictor' ),
							'volatile' => __( '⚡ Volatile', 'wc26-predictor' ),
						];
						$trendLabel = $trendLabels[ $m['market_trend'] ] ?? $m['market_trend'];
						?>
						<tr>
							<td><?php echo esc_html( $m['id'] ); ?></td>
							<td>
								<strong><?php echo esc_html( $m['property_name'] ); ?></strong><br>
								<small><?php echo esc_html( $m['property_type'] . ' - ' . $m['city'] ); ?></small>
							</td>
							<td><?php echo esc_html( $m['region_name'] ); ?></td>
							<td><?php echo esc_html( $m['forecast_date'] ); ?></td>
							<td>$<?php echo esc_html( number_format( (float) $m['initial_price'] ) ); ?></td>
							<td><?php echo esc_html( $trendLabel ); ?></td>
							<td>
								<span class="wc26-match-status wc26-status-<?php echo esc_attr( $m['status'] ); ?>">
									<?php echo esc_html( $statusLabel ); ?>
								</span>
							</td>
							<td>
								<?php if ( $m['status'] === 'settled' ) : ?>
									$<?php echo esc_html( number_format( (float) $m['final_price'] ) ); ?>
									<br><small><?php echo esc_html( $m['price_change_pct'] . '%' ); ?></small>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $m['status'] !== 'settled' && $m['status'] !== 'cancelled' ) : ?>
									<div class="wc26-result-form">
										<input type="number" class="wc26-res-final-price" min="0" step="1000" value="<?php echo esc_attr( $m['initial_price'] ); ?>" style="width:100px;">
										<select class="wc26-res-trend" style="width:100px;">
											<option value="increase"><?php esc_html_e( 'Increase', 'wc26-predictor' ); ?></option>
											<option value="stable" selected><?php esc_html_e( 'Stable', 'wc26-predictor' ); ?></option>
											<option value="decrease"><?php esc_html_e( 'Decrease', 'wc26-predictor' ); ?></option>
											<option value="volatile"><?php esc_html_e( 'Volatile', 'wc26-predictor' ); ?></option>
										</select>
										<button class="button button-primary wc26-submit-result-btn" data-market="<?php echo esc_attr( $m['id'] ); ?>">
											<?php esc_html_e( 'Settle', 'wc26-predictor' ); ?>
										</button>
									</div>
								<?php else : ?>
									<span class="wc26-status-finished"><?php esc_html_e( 'Settled', 'wc26-predictor' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
