<?php
/**
 * Frontend Template: Group Standings
 * Usage: [wc26_standings group_id="1"]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$group_id = (int) ( $atts['group_id'] ?? 0 );
?>
<div class="wc26-standings-shortcode" x-data="wc26Standings(<?php echo esc_js( $group_id ); ?>)" x-init="init()">
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
						<th><?php esc_html_e( 'تیم', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'بازی', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'برد', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'مساوی', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'باخت', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'زده', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'خورده', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'تفاضل', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'امتیاز', 'wc26-predictor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<template x-for="(row, i) in rows" :key="row.team_id">
						<tr>
							<td x-text="i + 1"></td>
							<td>
								<img class="wc26-flag" :src="row.flag_url" :alt="row.team_name" loading="lazy">
								<span x-text="row.team_name"></span>
							</td>
							<td x-text="row.played"></td>
							<td x-text="row.won"></td>
							<td x-text="row.draw"></td>
							<td x-text="row.lost"></td>
							<td x-text="row.goals_for"></td>
							<td x-text="row.goals_against"></td>
							<td x-text="row.goal_difference"></td>
							<td><strong x-text="row.points"></strong></td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
	</template>
</div>

<script>
function wc26Standings(groupId) {
	return {
		loading: true,
		rows: [],
		async init() {
			try {
				const r = await fetch(wc26.apiBase + '/standings?group_id=' + groupId, {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.rows = await r.json();
			} catch(e) {}
			this.loading = false;
		}
	};
}
</script>
