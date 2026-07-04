<?php
/**
 * Frontend Template: Scoring Rules
 * Usage: [wc26_scoring_rules]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wc26-scoring-rules" x-data="wc26ScoringRules()" x-init="init()">
	<template x-if="loading">
		<div class="wc26-card"><div class="wc26-sk-line"></div></div>
	</template>
	<template x-if="!loading">
		<div class="wc26-card">
			<h3><?php esc_html_e( 'How Scoring Works', 'wc26-predictor' ); ?></h3>
			<table class="wc26-scoring-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Rule', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Points', 'wc26-predictor' ); ?></th>
						<th><?php esc_html_e( 'Description', 'wc26-predictor' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<template x-for="rule in rules" :key="rule.rule_key">
						<tr>
							<td><strong x-text="rule.label"></strong></td>
							<td><span class="wc26-points-badge" x-text="rule.points"></span></td>
							<td x-text="rule.description"></td>
						</tr>
					</template>
				</tbody>
			</table>
			<p class="wc26-muted" style="margin-top:1rem; font-size:0.9rem;">
				<?php esc_html_e( 'Joker cards double your points for one prediction.', 'wc26-predictor' ); ?>
			</p>
		</div>
	</template>
</div>

<script>
function wc26ScoringRules() {
	return {
		loading: true,
		rules: [],
		async init() {
			try {
				const r = await fetch(wc26.apiBase + '/scoring-rules', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.rules = await r.json();
			} catch(e) {}
			this.loading = false;
		}
	};
}
</script>
