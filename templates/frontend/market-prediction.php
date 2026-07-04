<?php
/**
 * Frontend Template: Market Prediction
 * Usage: [wc26_market_prediction region_id="0" limit="10"]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$region_id = (int) ( $atts['region_id'] ?? 0 );
$limit = max( 1, min( 50, (int) ( $atts['limit'] ?? 10 ) ) );
?>

<div class="wc26-market-prediction" x-data="wc26MarketPrediction(<?php echo esc_js( $region_id ); ?>, <?php echo esc_js( $limit ); ?>)" x-init="init()">

	<!-- Loading Skeleton -->
	<template x-if="loading">
		<div class="wc26-skeleton-wrap">
			<template x-for="i in 3" :key="i">
				<div class="wc26-card wc26-skeleton">
					<div class="wc26-sk-line wc26-sk-lg"></div>
					<div class="wc26-sk-line wc26-sk-md"></div>
					<div class="wc26-sk-line wc26-sk-sm"></div>
				</div>
			</template>
		</div>
	</template>

	<!-- Auth Wall -->
	<template x-if="!wc26.isLoggedIn && !loading">
		<div class="wc26-auth-wall">
			<div class="wc26-card wc26-text-center">
				<h2><?php esc_html_e( 'Log in to Predict', 'wc26-predictor' ); ?></h2>
				<p><?php esc_html_e( 'Sign in to submit predictions and track your performance.', 'wc26-predictor' ); ?></p>
				<a class="wc26-btn wc26-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
					<?php esc_html_e( 'Login / Register', 'wc26-predictor' ); ?>
				</a>
			</div>
		</div>
	</template>

	<!-- Tabs -->
	<template x-if="wc26.isLoggedIn && !loading">
		<div class="wc26-tabs">
			<button class="wc26-tab" :class="{ active: tab === 'active' }" @click="tab = 'active'">
				<?php esc_html_e( 'Active Markets', 'wc26-predictor' ); ?>
			</button>
			<button class="wc26-tab" :class="{ active: tab === 'my-predictions' }" @click="tab = 'my-predictions'">
				<?php esc_html_e( 'My Predictions', 'wc26-predictor' ); ?>
			</button>
			<button class="wc26-tab" :class="{ active: tab === 'settled' }" @click="tab = 'settled'">
				<?php esc_html_e( 'Settled', 'wc26-predictor' ); ?>
			</button>
		</div>
	</template>

	<!-- Active Markets Tab -->
	<template x-if="tab === 'active' && !loading && wc26.isLoggedIn">
		<div class="wc26-markets-grid">
			<template x-if="markets.length === 0">
				<div class="wc26-card wc26-text-center">
					<p><?php esc_html_e( 'No active markets available.', 'wc26-predictor' ); ?></p>
				</div>
			</template>

			<template x-for="market in markets" :key="market.id">
				<div class="wc26-market-card" :class="{ locked: market.locked, 'has-prediction': getUserPrediction(market.id) }">

					<!-- Status Badge -->
					<div class="wc26-stage-badge" x-text="market.status"></div>

					<!-- Countdown -->
					<div class="wc26-countdown" x-text="market.locked ? '<?php echo esc_js( __( 'Locked', 'wc26-predictor' ) ); ?>' : countdown(market.forecast_date)"></div>

					<!-- Market Info -->
					<div class="wc26-market-info">
						<h3 x-text="market.property_name"></h3>
						<p><strong><?php esc_html_e( 'Region:', 'wc26-predictor' ); ?></strong> <span x-text="market.region_name"></span></p>
						<p><strong><?php esc_html_e( 'Current Price:', 'wc26-predictor' ); ?></strong> $<span x-text="formatPrice(market.initial_price)"></span></p>
						<p><strong><?php esc_html_e( 'Forecast Date:', 'wc26-predictor' ); ?></strong> <span x-text="market.forecast_date"></span></p>
						<p><strong><?php esc_html_e( 'Expected Trend:', 'wc26-predictor' ); ?></strong> <span x-text="market.market_trend"></span></p>
					</div>

					<!-- Prediction Form -->
					<div class="wc26-prediction-form" x-data="{ pred: getPredOrEmpty(market.id) }">
						<template x-if="!market.locked">
							<div class="wc26-prediction-fields">
								<div class="wc26-field">
									<label><?php esc_html_e( 'Predicted Price:', 'wc26-predictor' ); ?></label>
									<input type="number" step="1000" min="0" class="wc26-price-input" x-model.number="pred.price" placeholder="0">
								</div>
								<div class="wc26-field">
									<label><?php esc_html_e( 'Predicted Trend:', 'wc26-predictor' ); ?></label>
									<select x-model="pred.trend" class="wc26-trend-select">
										<option value="increase"><?php esc_html_e( 'Increase', 'wc26-predictor' ); ?></option>
										<option value="stable"><?php esc_html_e( 'Stable', 'wc26-predictor' ); ?></option>
										<option value="decrease"><?php esc_html_e( 'Decrease', 'wc26-predictor' ); ?></option>
										<option value="volatile"><?php esc_html_e( 'Volatile', 'wc26-predictor' ); ?></option>
									</select>
								</div>
								<div class="wc26-submit-row">
									<label class="wc26-joker-label">
										<input type="checkbox" x-model="pred.joker" :disabled="jokerUsed && !pred.joker">
										<?php esc_html_e( 'Joker', 'wc26-predictor' ); ?>
									</label>
									<button class="wc26-btn wc26-btn-predict" @click="submitPrediction(market.id, pred)" :disabled="submitting[market.id]">
										<span x-show="!submitting[market.id]"><?php esc_html_e( 'Submit', 'wc26-predictor' ); ?></span>
										<span x-show="submitting[market.id]"><?php esc_html_e( 'Submitting...', 'wc26-predictor' ); ?></span>
									</button>
								</div>
								<div class="wc26-feedback" x-text="feedback[market.id]" :class="feedbackType[market.id]" x-show="feedback[market.id]"></div>
							</div>
						</template>
						<template x-if="market.locked">
							<div class="wc26-locked-prediction">
								<template x-if="getUserPrediction(market.id)">
									<div>
										<span class="wc26-locked-label"><?php esc_html_e( 'Your Prediction:', 'wc26-predictor' ); ?></span>
										<span x-text="'$' + formatPrice(getUserPrediction(market.id).predicted_price) + ' (' + getUserPrediction(market.id).predicted_trend + ')'"></span>
									</div>
								</template>
								<template x-if="!getUserPrediction(market.id)">
									<span class="wc26-muted"><?php esc_html_e( 'No prediction', 'wc26-predictor' ); ?></span>
								</template>
							</div>
						</template>
					</div>
				</div>
			</template>
		</div>
	</template>

	<!-- My Predictions Tab -->
	<template x-if="tab === 'my-predictions' && !loading && wc26.isLoggedIn">
		<div class="wc26-my-predictions">
			<template x-if="myPredictions.length === 0">
				<div class="wc26-card wc26-text-center">
					<p><?php esc_html_e( 'You haven\'t made any predictions yet.', 'wc26-predictor' ); ?></p>
				</div>
			</template>
			<template x-for="pred in myPredictions" :key="pred.id">
				<div class="wc26-prediction-row" :class="pred.prediction_type">
					<div class="wc26-prediction-header">
						<span class="wc26-market-name" x-text="pred.property_name + ' (' + pred.region_name + ')'"></span>
						<span class="wc26-prediction-date" x-text="pred.forecast_date"></span>
					</div>
					<div class="wc26-prediction-details">
						<span class="wc26-prediction-price"><?php esc_html_e( 'Predicted:', 'wc26-predictor' ); ?> $<span x-text="formatPrice(pred.predicted_price)"></span></span>
						<span class="wc26-prediction-trend" x-text="pred.predicted_trend"></span>
						<span class="wc26-prediction-points" x-text="pred.earned_points + ' ' + '<?php echo esc_js( __( 'pts', 'wc26-predictor' ) ); ?>'"></span>
					</div>
				</div>
			</template>
		</div>
	</template>

	<!-- Settled Markets Tab -->
	<template x-if="tab === 'settled' && !loading && wc26.isLoggedIn">
		<div class="wc26-settled-markets">
			<template x-if="settledMarkets.length === 0">
				<div class="wc26-card wc26-text-center">
					<p><?php esc_html_e( 'No markets have been settled yet.', 'wc26-predictor' ); ?></p>
				</div>
			</template>
			<template x-for="market in settledMarkets" :key="market.id">
				<div class="wc26-market-card wc26-settled">
					<div class="wc26-market-info">
						<h3 x-text="market.property_name"></h3>
						<p><strong><?php esc_html_e( 'Final Price:', 'wc26-predictor' ); ?></strong> $<span x-text="formatPrice(market.final_price)"></span></p>
						<p><strong><?php esc_html_e( 'Change:', 'wc26-predictor' ); ?></strong> <span x-text="market.price_change_pct + '%'" :class="market.price_change_pct > 0 ? 'wc26-positive' : 'wc26-negative'"></span></p>
						<p><strong><?php esc_html_e( 'Actual Trend:', 'wc26-predictor' ); ?></strong> <span x-text="market.market_trend"></span></p>
					</div>
				</div>
			</template>
		</div>
	</template>
</div>

<script>
function wc26MarketPrediction(regionId, limit) {
	return {
		loading: true,
		tab: 'active',
		markets: [],
		settledMarkets: [],
		myPredictions: [],
		predictionsMap: {},
		submitting: {},
		feedback: {},
		feedbackType: {},
		jokerUsed: false,

		async init() {
			if (!wc26.isLoggedIn) {
				this.loading = false;
				return;
			}
			await Promise.all([
				this.fetchMarkets(),
				this.fetchMyPredictions(),
				this.fetchSettledMarkets()
			]);
			this.loading = false;
		},

		async fetchMarkets() {
			try {
				let url = wc26.apiBase + '/markets?status=active';
				if (regionId > 0) url += '&region_id=' + regionId;
				const r = await fetch(url, { headers: { 'X-WP-Nonce': wc26.nonce } });
				this.markets = await r.json();
			} catch(e) { console.error(e); }
		},

		async fetchSettledMarkets() {
			try {
				let url = wc26.apiBase + '/markets?status=settled';
				if (regionId > 0) url += '&region_id=' + regionId;
				const r = await fetch(url, { headers: { 'X-WP-Nonce': wc26.nonce } });
				this.settledMarkets = await r.json();
			} catch(e) { console.error(e); }
		},

		async fetchMyPredictions() {
			if (!wc26.isLoggedIn) return;
			try {
				const r = await fetch(wc26.apiBase + '/my-predictions', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.myPredictions = await r.json();
				this.predictionsMap = {};
				this.myPredictions.forEach(p => {
					this.predictionsMap[p.market_id] = p;
					if (p.is_joker == 1) this.jokerUsed = true;
				});
			} catch(e) { console.error(e); }
		},

		async submitPrediction(marketId, pred) {
			this.submitting[marketId] = true;
			this.feedback[marketId] = '';

			try {
				const res = await fetch(wc26.apiBase + '/predict', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': wc26.nonce,
					},
					body: JSON.stringify({
						market_id: marketId,
						predicted_price: pred.price || null,
						predicted_trend: pred.trend || 'stable',
						is_joker: pred.joker || false,
					})
				});
				const data = await res.json();

				if (!res.ok) {
					this.feedback[marketId] = data.message || '<?php echo esc_js( __( 'Error. Please try again.', 'wc26-predictor' ) ); ?>';
					this.feedbackType[marketId] = 'error';
				} else {
					this.feedback[marketId] = '<?php echo esc_js( __( 'Prediction submitted!', 'wc26-predictor' ) ); ?>';
					this.feedbackType[marketId] = 'success';
					this.predictionsMap[marketId] = data;
					await this.fetchMyPredictions();
					setTimeout(() => { this.feedback[marketId] = ''; }, 3000);
				}
			} catch(e) {
				this.feedback[marketId] = '<?php echo esc_js( __( 'Network error.', 'wc26-predictor' ) ); ?>';
				this.feedbackType[marketId] = 'error';
			} finally {
				this.submitting[marketId] = false;
			}
		},

		getUserPrediction(marketId) {
			return this.predictionsMap[marketId] || null;
		},

		getPredOrEmpty(marketId) {
			const p = this.predictionsMap[marketId];
			return p ? { price: p.predicted_price, trend: p.predicted_trend, joker: !!+p.is_joker } : { price: null, trend: 'stable', joker: false };
		},

		formatPrice(value) {
			return value ? Number(value).toLocaleString() : '0';
		},

		countdown(forecastDate) {
			const diff = new Date(forecastDate) - new Date();
			if (diff <= 0) return '<?php echo esc_js( __( 'Locked', 'wc26-predictor' ) ); ?>';
			const d = Math.floor(diff / 86400000);
			const h = Math.floor((diff % 86400000) / 3600000);
			const m = Math.floor((diff % 3600000) / 60000);
			if (d > 0) return `${d} ${'<?php echo esc_js( __( 'days', 'wc26-predictor' ) ); ?>'} ${h} ${'<?php echo esc_js( __( 'hours', 'wc26-predictor' ) ); ?>'}`;
			if (h > 0) return `${h} ${'<?php echo esc_js( __( 'hours', 'wc26-predictor' ) ); ?>'} ${m} ${'<?php echo esc_js( __( 'min', 'wc26-predictor' ) ); ?>'}`;
			return `${m} ${'<?php echo esc_js( __( 'min', 'wc26-predictor' ) ); ?>'}`;
		}
	};
}
</script>
