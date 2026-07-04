<?php
/**
 * Frontend Template: Region Standings
 * Usage: [wc26_region_standings region_id="1"]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$region_id = (int) ( $atts['region_id'] ?? 0 );
if ( $region_id === 0 ) {
	echo '<p class="wc26-error">' . esc_html__( 'Please specify a region_id.', 'wc26-predictor' ) . '</p>';
	return;
}
?>
<div class="wc26-standings-shortcode" x-data="wc26RegionStandings(<?php echo esc_js( $region_id ); ?>)" x-init="init()">
	<template x-if="loading">
		<div class="wc26-card">
			<div class="wc26-sk-line"></div>
		</div>
	</template>
	<template x-if="!loading">
		<div class="wc26-card" style="padding:0;overflow:hidden;">
			<table class="wc26-standings-table">
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'Player', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Points', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Exact', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Trend', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Range', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Total', 'wc26-predictor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<template x-for="(row, i) in rows" :key="row.user_id">
						<tr>
							<td x-text="i + 1"></td>
							<td><span x-text="row.display_name"></span></td>
							<td><strong x-text="row.total_points"></strong></td>
							<td x-text="row.exact_hits"></td>
							<td x-text="row.trend_hits"></td>
							<td x-text="row.range_hits"></td>
							<td x-text="row.total_predictions"></td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
	</template>
</div>

<script>
function wc26RegionStandings(regionId) {
	return {
		loading: true,
		rows: [],
		async init() {
			try {
				const r = await fetch(wc26.apiBase + '/standings?region_id=' + regionId, {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.rows = await r.json();
			} catch(e) {}
			this.loading = false;
		}
	};
}
</script>
