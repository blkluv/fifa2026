<?php
/**
 * Frontend Template: Leaderboard
 * Usage: [wc26_leaderboard limit="50"]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$limit = max( 5, min( 500, (int) ( $atts['limit'] ?? 50 ) ) );
?>
<div class="wc26-lb-shortcode" x-data="wc26Leaderboard(<?php echo esc_js( $limit ); ?>)" x-init="init()">
	<template x-if="loading">
		<div class="wc26-card">
			<div class="wc26-sk-line wc26-sk-lg"></div>
			<div class="wc26-sk-line wc26-sk-md"></div>
			<div class="wc26-sk-line wc26-sk-sm"></div>
		</div>
	</template>
	<template x-if="!loading">
		<div class="wc26-card" style="padding:0;overflow:hidden;">
			<table class="wc26-leaderboard-table">
				<thead>
					<tr>
						<th>#</th>
						<th><?php esc_html_e( 'بازیکن', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'امتیاز', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'دقیق', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'تفاضل', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'برد/مساوی', 'wc26-predictor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<template x-for="(row, i) in rows" :key="row.user_id">
						<tr :class="'wc26-rank-' + (i + 1)">
							<td x-text="row.rank_position || (i + 1)"></td>
							<td x-text="row.display_name"></td>
							<td><strong x-text="row.total_points"></strong></td>
							<td x-text="row.exact_hits"></td>
							<td x-text="row.goal_diff_hits"></td>
							<td x-text="row.winner_hits"></td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
	</template>
</div>

<script>
function wc26Leaderboard(limit) {
	return {
		loading: true,
		rows: [],
		async init() {
			try {
				const r = await fetch(wc26.apiBase + '/leaderboard?limit=' + limit, {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.rows = await r.json();
			} catch(e) {}
			this.loading = false;
		}
	};
}
</script>
