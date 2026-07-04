<?php
/**
 * Admin Template: CSV Import - Real Estate
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'CSV Import', 'wc26-predictor' ); ?></h1>

	<div class="wc26-import-box">
		<form id="wc26-csv-form" enctype="multipart/form-data">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="import_type"><?php esc_html_e( 'Import Type', 'wc26-predictor' ); ?></label></th>
					<td>
						<select name="import_type" id="import_type">
							<option value="properties"><?php esc_html_e( 'Properties', 'wc26-predictor' ); ?></option>
							<option value="regions"><?php esc_html_e( 'Regions', 'wc26-predictor' ); ?></option>
							<option value="markets"><?php esc_html_e( 'Markets', 'wc26-predictor' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csv_file"><?php esc_html_e( 'CSV File', 'wc26-predictor' ); ?></label></th>
					<td>
						<input type="file" name="csv_file" id="csv_file" accept=".csv">
						<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>">
					</td>
				</tr>
			</table>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'wc26-predictor' ); ?></button>
			<span id="wc26-import-status" style="margin-left:1rem; font-weight:600;"></span>
		</form>
	</div>

	<hr>

	<h2><?php esc_html_e( 'CSV Format Guide', 'wc26-predictor' ); ?></h2>

	<h3><?php esc_html_e( 'Properties', 'wc26-predictor' ); ?> <code>properties.csv</code></h3>
	<pre>region_id,name,slug,property_type,address,city,latitude,longitude,initial_price,square_feet,bedrooms,bathrooms,year_built
1,123 Main St,123-main-st,single-family,123 Main St,Austin,30.2672,-97.7431,250000,2000,3,2.5,2005
2,456 Oak Ave,456-oak-ave,condo,456 Oak Ave,Miami,25.7617,-80.1918,450000,1500,2,2,2010</pre>

	<h3><?php esc_html_e( 'Regions', 'wc26-predictor' ); ?> <code>regions.csv</code></h3>
	<pre>name,slug,description,country,state
Austin Metro,austin-metro,Austin metropolitan area,USA,Texas
Miami Beach,miami-beach,Miami Beach area,USA,Florida</pre>

	<h3><?php esc_html_e( 'Markets', 'wc26-predictor' ); ?> <code>markets.csv</code></h3>
	<pre>region_id,property_id,forecast_date,initial_price,market_trend
1,1,2026-07-01 00:00:00,250000,stable
2,2,2026-07-01 00:00:00,450000,increase</pre>
</div>
