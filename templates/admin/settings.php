<?php
/**
 * Admin Template: Settings - Real Estate
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'Settings', 'wc26-predictor' ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'wc26_settings' ); ?>
		<h2><?php esc_html_e( 'Prediction Settings', 'wc26-predictor' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Lock Time (minutes before forecast)', 'wc26-predictor' ); ?></th>
				<td><input type="number" name="wc26_lock_minutes" value="<?php echo esc_attr( get_option( 'wc26_lock_minutes', 1 ) ); ?>" min="0" max="60"> <?php esc_html_e( 'minutes', 'wc26-predictor' ); ?></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Chainlink CRE Settings', 'wc26-predictor' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'DON ID', 'wc26-predictor' ); ?></th>
				<td>
					<input type="text" name="wc26_chainlink_don_id" value="<?php echo esc_attr( get_option( 'wc26_chainlink_don_id', '' ) ); ?>" class="regular-text" placeholder="e.g. don_123456789">
					<p class="description"><?php esc_html_e( 'Your Chainlink Runtime Environment DON ID.', 'wc26-predictor' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Auto-submit Reports', 'wc26-predictor' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="wc26_auto_chainlink_report" value="1" <?php checked( get_option( 'wc26_auto_chainlink_report', 1 ), 1 ); ?>>
						<?php esc_html_e( 'Automatically submit Chainlink reports when markets are settled', 'wc26-predictor' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Telegram Integration (Optional)', 'wc26-predictor' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Bot Token', 'wc26-predictor' ); ?></th>
				<td>
					<input type="password" name="wc26_telegram_token" value="<?php echo esc_attr( get_option( 'wc26_telegram_token', '' ) ); ?>" class="regular-text" autocomplete="off">
					<p class="description"><?php esc_html_e( 'Telegram bot token for notifications and updates.', 'wc26-predictor' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
