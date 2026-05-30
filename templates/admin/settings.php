<?php /* Admin Template: settings.php */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'تنظیمات', 'wc26-predictor' ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'wc26_settings' ); ?>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'بازه قفل شدن پیش‌بینی (دقیقه قبل از شروع)', 'wc26-predictor' ); ?></th>
				<td><input type="number" name="wc26_lock_minutes" value="<?php echo esc_attr( get_option( 'wc26_lock_minutes', 1 ) ); ?>" min="0" max="60"></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'توکن ربات تلگرام (اختیاری)', 'wc26-predictor' ); ?></th>
				<td><input type="password" name="wc26_telegram_token" value="<?php echo esc_attr( get_option( 'wc26_telegram_token', '' ) ); ?>" class="regular-text" autocomplete="off"></td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
