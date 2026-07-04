<?php
/**
 * Admin Template: Scoring Rules - Real Estate
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array $rows */
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'Scoring Rules', 'wc26-predictor' ); ?></h1>
	<p><?php esc_html_e( 'Configure how many points users earn for each type of prediction.', 'wc26-predictor' ); ?></p>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table" id="wc26-scoring-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Rule Name', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Rule Key', 'wc26-predictor' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'Points', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Description', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="text" class="regular-text wc26-sr-label" value="<?php esc_attr_e( 'Exact Price', 'wc26-predictor' ); ?>" /></td>
					<td><code class="wc26-sr-key">exact_price</code></td>
					<td><input type="number" class="small-text wc26-sr-points" min="0" max="9999" value="10" /></td>
					<td><input type="text" class="regular-text wc26-sr-desc" value="<?php esc_attr_e( 'Predict the exact final price (within 1%)', 'wc26-predictor' ); ?>" /></td>
				</tr>
				<tr>
					<td><input type="text" class="regular-text wc26-sr-label" value="<?php esc_attr_e( 'Price Range', 'wc26-predictor' ); ?>" /></td>
					<td><code class="wc26-sr-key">price_range</code></td>
					<td><input type="number" class="small-text wc26-sr-points" min="0" max="9999" value="5" /></td>
					<td><input type="text" class="regular-text wc26-sr-desc" value="<?php esc_attr_e( 'Predict the correct price range (within 5%)', 'wc26-predictor' ); ?>" /></td>
				</tr>
				<tr>
					<td><input type="text" class="regular-text wc26-sr-label" value="<?php esc_attr_e( 'Correct Trend', 'wc26-predictor' ); ?>" /></td>
					<td><code class="wc26-sr-key">correct_trend</code></td>
					<td><input type="number" class="small-text wc26-sr-points" min="0" max="9999" value="3" /></td>
					<td><input type="text" class="regular-text wc26-sr-desc" value="<?php esc_attr_e( 'Predict the correct market trend (increase/decrease/stable)', 'wc26-predictor' ); ?>" /></td>
				</tr>
				<tr>
					<td><input type="text" class="regular-text wc26-sr-label" value="<?php esc_attr_e( 'Partial Trend', 'wc26-predictor' ); ?>" /></td>
					<td><code class="wc26-sr-key">partial_trend</code></td>
					<td><input type="number" class="small-text wc26-sr-points" min="0" max="9999" value="1" /></td>
					<td><input type="text" class="regular-text wc26-sr-desc" value="<?php esc_attr_e( 'Predict the correct direction but off by more than 5%', 'wc26-predictor' ); ?>" /></td>
				</tr>
			</tbody>
		</table>
	</div>

	<p>
		<button type="button" class="button button-primary" id="wc26-save-scoring-rules"><?php esc_html_e( 'Save Changes', 'wc26-predictor' ); ?></button>
		<span id="wc26-save-scoring-status" class="description" style="margin-right:10px"></span>
	</p>
</div>
