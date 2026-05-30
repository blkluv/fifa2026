<?php /* Admin Template: scoring-rules.php */
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var array $rows */
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'قوانین امتیازدهی', 'wc26-predictor' ); ?></h1>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table" id="wc26-scoring-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'عنوان', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'کلید', 'wc26-predictor' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'امتیاز', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'توضیحات', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( (array) $rows as $r ) :
				$ruleKey = isset( $r['rule_key'] ) ? (string) $r['rule_key'] : '';
				$label   = isset( $r['label'] ) ? (string) $r['label'] : '';
				$desc    = isset( $r['description'] ) ? (string) $r['description'] : '';
				$pts     = isset( $r['points'] ) ? (int) $r['points'] : 0;
			?>
				<tr>
					<td>
						<input type="text" class="regular-text wc26-sr-label" value="<?php echo esc_attr( $label ); ?>" />
					</td>
					<td>
						<code class="wc26-sr-key"><?php echo esc_html( $ruleKey ); ?></code>
					</td>
					<td>
						<input type="number" class="small-text wc26-sr-points" min="0" max="9999" value="<?php echo esc_attr( (string) $pts ); ?>" />
					</td>
					<td>
						<input type="text" class="regular-text wc26-sr-desc" value="<?php echo esc_attr( $desc ); ?>" />
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p>
		<button type="button" class="button button-primary" id="wc26-save-scoring-rules"><?php esc_html_e( 'ذخیره تغییرات', 'wc26-predictor' ); ?></button>
		<span id="wc26-save-scoring-status" class="description" style="margin-right:10px"></span>
	</p>
</div>
