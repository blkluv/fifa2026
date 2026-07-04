<?php
/**
 * Admin Template: Chainlink CRE Integration
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$reports = $wpdb->get_results(
	"SELECT r.*, m.property_name, m.region_name 
	 FROM {$wpdb->prefix}wc26_chainlink_reports r
	 LEFT JOIN (
		SELECT m.id, p.name AS property_name, r.name AS region_name
		FROM {$wpdb->prefix}wc26_markets m
		LEFT JOIN {$wpdb->prefix}wc26_properties p ON p.id = m.property_id
		LEFT JOIN {$wpdb->prefix}wc26_regions r ON r.id = m.region_id
	 ) m ON m.id = r.market_id
	 ORDER BY r.created_at DESC
	 LIMIT 50",
	ARRAY_A
);

$pending_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_chainlink_reports WHERE status = 'pending'" );
$settled_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_chainlink_reports WHERE status = 'confirmed'" );
$failed_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_chainlink_reports WHERE status = 'failed'" );
?>

<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'Chainlink CRE Integration', 'wc26-predictor' ); ?></h1>

	<!-- Stats -->
	<div class="wc26-stat-grid" style="margin-bottom:20px;">
		<div class="wc26-stat-card">
			<h3><?php esc_html_e( 'Pending Reports', 'wc26-predictor' ); ?></h3>
			<div class="wc26-stat-val" style="color:#e07600;"><?php echo esc_html( number_format( $pending_count ) ); ?></div>
		</div>
		<div class="wc26-stat-card">
			<h3><?php esc_html_e( 'Confirmed', 'wc26-predictor' ); ?></h3>
			<div class="wc26-stat-val" style="color:#00a32a;"><?php echo esc_html( number_format( $settled_count ) ); ?></div>
		</div>
		<div class="wc26-stat-card">
			<h3><?php esc_html_e( 'Failed', 'wc26-predictor' ); ?></h3>
			<div class="wc26-stat-val" style="color:#d63638;"><?php echo esc_html( number_format( $failed_count ) ); ?></div>
		</div>
	</div>

	<!-- Settings -->
	<div class="wc26-card" style="margin-bottom:20px; padding:20px; background:#fff; border:1px solid #ccd0d4; border-radius:4px;">
		<h2><?php esc_html_e( 'Settings', 'wc26-predictor' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'wc26_settings' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Chainlink DON ID', 'wc26-predictor' ); ?></th>
					<td>
						<input type="text" name="wc26_chainlink_don_id" 
							value="<?php echo esc_attr( get_option( 'wc26_chainlink_don_id', '' ) ); ?>" 
							class="regular-text" 
							placeholder="<?php esc_attr_e( 'e.g. don_123456789', 'wc26-predictor' ); ?>">
						<p class="description"><?php esc_html_e( 'The DON ID from your Chainlink Runtime Environment deployment.', 'wc26-predictor' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto-Report on Settlement', 'wc26-predictor' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="wc26_auto_chainlink_report" value="1" 
								<?php checked( get_option( 'wc26_auto_chainlink_report', 1 ), 1 ); ?>>
							<?php esc_html_e( 'Automatically submit Chainlink report when a market is settled', 'wc26-predictor' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save Settings', 'wc26-predictor' ) ); ?>
		</form>
	</div>

	<!-- Reports Table -->
	<h2><?php esc_html_e( 'Recent Chainlink Reports', 'wc26-predictor' ); ?></h2>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Market', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'DON ID', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Tx Hash', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Created', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Action', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $reports ) ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'No Chainlink reports found.', 'wc26-predictor' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $reports as $r ) : 
						$statusColors = [
							'pending'   => '#e07600',
							'submitted' => '#0073aa',
							'confirmed' => '#00a32a',
							'failed'    => '#d63638',
							'expired'   => '#888',
						];
						$statusLabels = [
							'pending'   => __( '⏳ Pending', 'wc26-predictor' ),
							'submitted' => __( '📤 Submitted', 'wc26-predictor' ),
							'confirmed' => __( '✅ Confirmed', 'wc26-predictor' ),
							'failed'    => __( '❌ Failed', 'wc26-predictor' ),
							'expired'   => __( '⏰ Expired', 'wc26-predictor' ),
						];
						$reportData = json_decode( $r['report_data'], true );
					?>
					<tr>
						<td><?php echo esc_html( $r['id'] ); ?></td>
						<td>
							<strong><?php echo esc_html( $r['property_name'] ?? '—' ); ?></strong>
							<small style="display:block; color:#888;"><?php echo esc_html( $r['region_name'] ?? '—' ); ?></small>
						</td>
						<td><code><?php echo esc_html( $r['don_id'] ); ?></code></td>
						<td>
							<span style="color:<?php echo esc_attr( $statusColors[ $r['status'] ] ?? '#888' ); ?>; font-weight:700;">
								<?php echo esc_html( $statusLabels[ $r['status'] ] ?? $r['status'] ); ?>
							</span>
							<?php if ( $r['error_message'] ) : ?>
								<br><small style="color:#d63638;"><?php echo esc_html( $r['error_message'] ); ?></small>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $r['transaction_hash'] ) : ?>
								<code style="font-size:0.7rem;"><?php echo esc_html( substr( $r['transaction_hash'], 0, 20 ) . '…' ); ?></code>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $r['created_at'] ); ?></td>
						<td>
							<?php if ( $r['status'] === 'pending' ) : ?>
								<button class="button wc26-submit-chainlink-report" data-report="<?php echo esc_attr( $r['id'] ); ?>">
									<?php esc_html_e( 'Submit', 'wc26-predictor' ); ?>
								</button>
							<?php elseif ( $r['status'] === 'submitted' && $r['transaction_hash'] ) : ?>
								<a href="https://sepolia.etherscan.io/tx/<?php echo esc_attr( $r['transaction_hash'] ); ?>" target="_blank" class="button">
									<?php esc_html_e( 'View on Etherscan', 'wc26-predictor' ); ?>
								</a>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
