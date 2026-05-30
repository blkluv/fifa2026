<?php
/**
 * Admin Template: CSV Import
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'ایمپورت CSV', 'wc26-predictor' ); ?></h1>

	<div class="wc26-import-box">
		<form id="wc26-csv-form" enctype="multipart/form-data">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="import_type"><?php esc_html_e( 'نوع ایمپورت', 'wc26-predictor' ); ?></label></th>
					<td>
						<select name="import_type" id="import_type">
							<option value="teams"><?php esc_html_e( 'تیم‌ها', 'wc26-predictor' ); ?></option>
							<option value="groups"><?php esc_html_e( 'گروه‌ها', 'wc26-predictor' ); ?></option>
							<option value="matches"><?php esc_html_e( 'مسابقات', 'wc26-predictor' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csv_file"><?php esc_html_e( 'فایل CSV', 'wc26-predictor' ); ?></label></th>
					<td>
						<input type="file" name="csv_file" id="csv_file" accept=".csv">
						<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>">
					</td>
				</tr>
			</table>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'ایمپورت', 'wc26-predictor' ); ?></button>
			<span id="wc26-import-status" style="margin-left:1rem; font-weight:600;"></span>
		</form>
	</div>

	<hr>

	<h2><?php esc_html_e( 'راهنمای فرمت CSV', 'wc26-predictor' ); ?></h2>

	<h3><?php esc_html_e( 'تیم‌ها', 'wc26-predictor' ); ?> <code>teams.csv</code></h3>
	<pre>fifa_id,name,short_name,code,flag_url,continent
FIFA001,Brazil,Brazil,BRA,https://cdn.example.com/flags/bra.png,CONMEBOL
FIFA002,Germany,Germany,GER,https://cdn.example.com/flags/ger.png,UEFA</pre>

	<h3><?php esc_html_e( 'گروه‌ها', 'wc26-predictor' ); ?> <code>groups.csv</code></h3>
	<pre>name,season
Group A,2026
Group B,2026</pre>

	<h3><?php esc_html_e( 'مسابقات', 'wc26-predictor' ); ?> <code>matches.csv</code></h3>
	<pre>group_id,stage,home_team_id,away_team_id,kickoff_at,venue
1,group,1,2,2026-06-11 18:00:00,MetLife Stadium
,round_16,3,4,2026-07-01 20:00:00,SoFi Stadium</pre>

</div>
