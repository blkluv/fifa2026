<?php
/**
 * Frontend Template: My Markets (User's predictions)
 * Usage: [wc26_my_markets]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	echo '<p class="wc26-error">' . esc_html__( 'Please log in to see your markets.', 'wc26-predictor' ) . '</p>';
	return;
}
?>

<div class="wc26-my-markets" x-data="wc26MyMarkets()" x-init="init()">
	<template x-if="loading">
		<div class="wc26-card"><div class="wc26-sk-line"></div></div>
	</template>
	<template x-if="!loading">
		<div class="wc26-card">
			<h3><?php esc_html_e( 'My Market Predictions', 'wc26-predictor' ); ?></h3>
			<template x-if="predictions.length === 0">
				<p class="wc26-muted"><?php esc_html_e( 'You haven\'t made any predictions yet.', 'wc26-predictor' ); ?></p>
			</template>
			<template x-for="pred in predictions" :key="pred.id">
				<div class="wc26-prediction-row" :class="pred.prediction_type">
					<div>
						<strong x-text="pred.property_name"></strong>
						<span class="wc26-muted" x-text="' (' + pred.region_name + ')'"></span>
					</div>
					<div class="wc26-prediction-details">
						<span><?php esc_html_e( 'Predicted:', 'wc26-predictor' ); ?> $<span x-text="formatPrice(pred.predicted_price)"></span></span>
						<span x-text="pred.predicted_trend"></span>
						<span class="wc26-points" x-text="pred.earned_points + ' ' + '<?php echo esc_js( __( 'pts', 'wc26-predictor' ) ); ?>'"></span>
					</div>
					<small class="wc26-muted" x-text="pred.forecast_date"></small>
				</div>
			</template>
		</div>
	</template>
</div>

<script>
function wc26MyMarkets() {
	return {
		loading: true,
		predictions: [],
		async init() {
			if (!wc26.isLoggedIn) {
				this.loading = false;
				return;
			}
			try {
				const r = await fetch(wc26.apiBase + '/my-predictions', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.predictions = await r.json();
			} catch(e) {}
			this.loading = false;
		},
		formatPrice(value) {
			return value ? Number(value).toLocaleString() : '0';
		}
	};
}
</script>
