<?php
/**
 * Frontend Template: Prediction UI
 *
 * Uses Alpine.js for reactivity. All data is fetched from the REST API.
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wc26-app" class="wc26-predictor" x-data="wc26Predictor()" x-init="init()">

	<!-- Loading Skeleton -->
	<template x-if="loading">
		<div class="wc26-skeleton-wrap">
			<template x-for="i in 3" :key="i">
				<div class="wc26-card wc26-skeleton">
					<div class="wc26-sk-line wc26-sk-sm"></div>
					<div class="wc26-sk-line wc26-sk-lg"></div>
					<div class="wc26-sk-line wc26-sk-md"></div>
				</div>
			</template>
		</div>
	</template>

	<!-- Auth Wall -->
	<template x-if="!wc26.isLoggedIn && !loading">
		<div class="wc26-auth-wall">
			<div class="wc26-card wc26-text-center">
				<h2><?php esc_html_e( 'برای پیش‌بینی وارد شوید', 'wc26-predictor' ); ?></h2>
				<p><?php esc_html_e( 'برای ثبت پیش‌بینی و دیدن جدول امتیازات، وارد حساب کاربری شوید.', 'wc26-predictor' ); ?></p>
				<a class="wc26-btn wc26-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
					<?php esc_html_e( 'ورود / ثبت‌نام', 'wc26-predictor' ); ?>
				</a>
			</div>
		</div>
	</template>

	<!-- Notification Bell -->
	<template x-if="wc26.isLoggedIn && !loading">
		<div class="wc26-top-bar">
			<div class="wc26-notif-wrap">
				<button class="wc26-notif-btn" @click="toggleNotifications()" title="<?php esc_attr_e( 'اعلان‌ها', 'wc26-predictor' ); ?>">
					<?php esc_html_e( 'اعلان‌ها', 'wc26-predictor' ); ?>
					<span class="wc26-badge" x-show="notifications.length > 0" x-text="notifications.length"></span>
				</button>
				<div class="wc26-notif-panel" x-show="showNotifications" @click.outside="showNotifications = false">
					<div class="wc26-notif-header">
						<strong><?php esc_html_e( 'اعلان‌ها', 'wc26-predictor' ); ?></strong>
						<button @click="markAllRead()" class="wc26-link"><?php esc_html_e( 'خوانده شد', 'wc26-predictor' ); ?></button>
					</div>
					<template x-if="notifications.length === 0">
						<p class="wc26-notif-empty"><?php esc_html_e( 'اعلان جدیدی ندارید.', 'wc26-predictor' ); ?></p>
					</template>
					<template x-for="n in notifications" :key="n.id">
						<div class="wc26-notif-item">
							<strong x-text="n.title"></strong>
							<p x-text="n.body"></p>
						</div>
					</template>
				</div>
			</div>
			<div class="wc26-user-rank" x-show="userRank">
				<?php esc_html_e( 'رتبه شما:', 'wc26-predictor' ); ?>
				<strong x-text="'#' + userRank"></strong>
			</div>
		</div>
	</template>

	<!-- Tabs -->
	<template x-if="wc26.isLoggedIn && !loading">
		<div class="wc26-tabs">
			<button class="wc26-tab" :class="{ active: tab === 'upcoming' }" @click="tab = 'upcoming'">
				<?php esc_html_e( 'مسابقات پیش رو', 'wc26-predictor' ); ?>
			</button>
			<button class="wc26-tab" :class="{ active: tab === 'my-picks' }" @click="tab = 'my-picks'">
				<?php esc_html_e( 'پیش‌بینی‌های من', 'wc26-predictor' ); ?>
			</button>
		</div>
	</template>

	<!-- Match Cards — Upcoming -->
	<template x-if="tab === 'upcoming' && !loading && wc26.isLoggedIn">
		<div class="wc26-matches-grid">
			<template x-if="matches.length === 0">
				<div class="wc26-card wc26-text-center">
					<p><?php esc_html_e( 'مسابقه‌ای برای نمایش وجود ندارد.', 'wc26-predictor' ); ?></p>
				</div>
			</template>

			<template x-for="match in matches" :key="match.id">
				<div class="wc26-match-card" :class="{ locked: match.locked, 'has-prediction': getUserPrediction(match.id) }">

					<!-- Stage Badge -->
					<div class="wc26-stage-badge" x-text="formatStage(match.stage)"></div>

					<!-- Kickoff Countdown -->
					<div class="wc26-countdown" x-text="match.locked ? '<?php echo esc_js( __( 'قفل شد', 'wc26-predictor' ) ); ?>' : countdown(match.kickoff_at)"></div>

					<!-- Teams Row -->
					<div class="wc26-teams-row">
						<!-- Home -->
						<div class="wc26-team wc26-home">
							<img class="wc26-flag" :src="match.home_flag || ''" :alt="match.home_team_name" loading="lazy">
							<span class="wc26-team-name" x-text="match.home_team_name"></span>
							<span class="wc26-team-code" x-text="match.home_team_code"></span>
						</div>

						<!-- Score Inputs -->
						<div class="wc26-score-inputs" x-data="{ pred: getPredOrEmpty(match.id) }">
							<template x-if="!match.locked">
								<div class="wc26-score-form">
									<input
										type="number" min="0" max="99"
										class="wc26-score-input"
										x-model.number="pred.home"
										:disabled="match.locked"
										placeholder="0"
									>
									<span class="wc26-separator">:</span>
									<input
										type="number" min="0" max="99"
										class="wc26-score-input"
										x-model.number="pred.away"
										:disabled="match.locked"
										placeholder="0"
									>
									<div class="wc26-submit-row">
										<label class="wc26-joker-label" :title="'<?php esc_attr_e( 'امتیاز این پیش‌بینی دو برابر می‌شود', 'wc26-predictor' ); ?>'">
											<input type="checkbox" x-model="pred.joker" :disabled="jokerUsed && !pred.joker">
											<?php esc_html_e( 'جوکر', 'wc26-predictor' ); ?>
										</label>
										<button
											class="wc26-btn wc26-btn-predict"
											@click="submitPrediction(match.id, pred)"
											:disabled="submitting[match.id]"
										>
											<span x-show="!submitting[match.id]"><?php esc_html_e( 'ثبت پیش‌بینی', 'wc26-predictor' ); ?></span>
											<span x-show="submitting[match.id]"><?php esc_html_e( 'در حال ارسال…', 'wc26-predictor' ); ?></span>
										</button>
									</div>
									<div class="wc26-feedback" x-text="feedback[match.id]"
										:class="feedbackType[match.id]"
										x-show="feedback[match.id]"
										x-transition></div>
								</div>
							</template>
							<template x-if="match.locked">
								<div class="wc26-locked-score">
									<template x-if="getUserPrediction(match.id)">
										<span>
											<strong x-text="getUserPrediction(match.id).pred_home_score"></strong>
											-
											<strong x-text="getUserPrediction(match.id).pred_away_score"></strong>
										</span>
									</template>
									<template x-if="!getUserPrediction(match.id)">
										<span class="wc26-muted"><?php esc_html_e( 'بدون پیش‌بینی', 'wc26-predictor' ); ?></span>
									</template>
								</div>
							</template>
						</div>

						<!-- Away -->
						<div class="wc26-team wc26-away">
							<img class="wc26-flag" :src="match.away_flag || ''" :alt="match.away_team_name" loading="lazy">
							<span class="wc26-team-name" x-text="match.away_team_name"></span>
							<span class="wc26-team-code" x-text="match.away_team_code"></span>
						</div>
					</div>

					<!-- Venue -->
					<div class="wc26-venue" x-text="match.venue"></div>
				</div>
			</template>
		</div>
	</template>

	<!-- My Picks Tab -->
	<template x-if="tab === 'my-picks' && !loading && wc26.isLoggedIn">
		<div class="wc26-my-picks">
			<template x-if="myPredictions.length === 0">
				<div class="wc26-card wc26-text-center">
					<p><?php esc_html_e( 'هنوز هیچ پیش‌بینی ثبت نکرده‌اید.', 'wc26-predictor' ); ?></p>
				</div>
			</template>
			<template x-for="pred in myPredictions" :key="pred.id">
				<div class="wc26-pick-row" :class="pred.prediction_type">
					<span class="wc26-pick-match" x-text="'مسابقه #' + pred.match_id"></span>
					<span class="wc26-pick-score" x-text="pred.pred_home_score + ' - ' + pred.pred_away_score"></span>
					<span class="wc26-pick-type" x-text="formatType(pred.prediction_type)"></span>
					<span class="wc26-pick-pts" x-text="pred.earned_points + ' امتیاز'"></span>
				</div>
			</template>
		</div>
	</template>

</div>

<script>
function wc26Predictor() {
	return {
		loading:          true,
		tab:              'upcoming',
		matches:          [],
		myPredictions:    [],
		predictionsMap:   {},
		notifications:    [],
		showNotifications: false,
		submitting:       {},
		feedback:         {},
		feedbackType:     {},
		userRank:         null,
		jokerUsed:        false,

		async init() {
			await Promise.all([
				this.fetchMatches(),
				this.fetchMyPredictions(),
				this.fetchNotifications(),
			]);
			this.loading = false;
		},

		async fetchMatches() {
			try {
				const res = await fetch(wc26.apiBase + '/matches', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.matches = await res.json();
			} catch(e) { console.error(e); }
		},

		async fetchMyPredictions() {
			if (!wc26.isLoggedIn) return;
			try {
				const res = await fetch(wc26.apiBase + '/my-predictions', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.myPredictions = await res.json();
				this.predictionsMap = {};
				this.myPredictions.forEach(p => {
					this.predictionsMap[p.match_id] = p;
					if (p.is_joker == 1) this.jokerUsed = true;
				});
			} catch(e) { console.error(e); }
		},

		async fetchNotifications() {
			if (!wc26.isLoggedIn) return;
			try {
				const res = await fetch(wc26.apiBase + '/notifications', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				this.notifications = await res.json();
			} catch(e) {}
		},

		async submitPrediction(matchId, pred) {
			this.submitting[matchId] = true;
			this.feedback[matchId]   = '';

			try {
				const res = await fetch(wc26.apiBase + '/predict', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce':   wc26.nonce,
					},
					body: JSON.stringify({
						match_id:   matchId,
						home_score: pred.home ?? 0,
						away_score: pred.away ?? 0,
						is_joker:   pred.joker ?? false,
					})
				});
				const data = await res.json();

				if (!res.ok) {
					this.feedback[matchId]     = data.message || '<?php echo esc_js( __( 'خطا. دوباره تلاش کنید.', 'wc26-predictor' ) ); ?>';
					this.feedbackType[matchId] = 'error';
				} else {
					this.feedback[matchId]     = '<?php echo esc_js( __( 'پیش‌بینی شما ثبت شد.', 'wc26-predictor' ) ); ?>';
					this.feedbackType[matchId] = 'success';
					this.predictionsMap[matchId] = data;
					await this.fetchMyPredictions();
					setTimeout(() => { this.feedback[matchId] = ''; }, 3000);
				}
			} catch(e) {
				this.feedback[matchId]     = '<?php echo esc_js( __( 'خطای شبکه.', 'wc26-predictor' ) ); ?>';
				this.feedbackType[matchId] = 'error';
			} finally {
				this.submitting[matchId] = false;
			}
		},

		getUserPrediction(matchId) {
			return this.predictionsMap[matchId] || null;
		},

		getPredOrEmpty(matchId) {
			const p = this.predictionsMap[matchId];
			return p ? { home: p.pred_home_score, away: p.pred_away_score, joker: !!+p.is_joker } : { home: 0, away: 0, joker: false };
		},

		async toggleNotifications() {
			this.showNotifications = !this.showNotifications;
		},

		async markAllRead() {
			await fetch(wc26.apiBase + '/notifications/read', {
				method: 'POST',
				headers: { 'X-WP-Nonce': wc26.nonce }
			});
			this.notifications = [];
			this.showNotifications = false;
		},

		countdown(kickoffAt) {
			const diff = new Date(kickoffAt) - new Date();
			if (diff <= 0) return '<?php echo esc_js( __( 'قفل شد', 'wc26-predictor' ) ); ?>';
			const d = Math.floor(diff / 86400000);
			const h = Math.floor((diff % 86400000) / 3600000);
			const m = Math.floor((diff % 3600000) / 60000);
			if (d > 0) return `${d} روز ${h} ساعت`;
			if (h > 0) return `${h} ساعت ${m} دقیقه`;
			return `${m} دقیقه`;
		},

		formatStage(stage) {
			const map = {
				group: '<?php echo esc_js( __( 'مرحله گروهی', 'wc26-predictor' ) ); ?>',
				round_32: '<?php echo esc_js( __( 'مرحله ۳۲ تیم', 'wc26-predictor' ) ); ?>',
				round_16: '<?php echo esc_js( __( 'مرحله یک‌هشتم', 'wc26-predictor' ) ); ?>',
				quarter: '<?php echo esc_js( __( 'مرحله یک‌چهارم', 'wc26-predictor' ) ); ?>',
				semi: '<?php echo esc_js( __( 'مرحله نیمه‌نهایی', 'wc26-predictor' ) ); ?>',
				third_place: '<?php echo esc_js( __( 'رده‌بندی', 'wc26-predictor' ) ); ?>',
				final: '<?php echo esc_js( __( 'فینال', 'wc26-predictor' ) ); ?>',
			};
			return map[stage] || stage;
		},

		formatType(type) {
			const map = {
				exact: '<?php echo esc_js( __( 'نتیجه دقیق', 'wc26-predictor' ) ); ?>',
				goal_diff: '<?php echo esc_js( __( 'تفاضل گل', 'wc26-predictor' ) ); ?>',
				winner: '<?php echo esc_js( __( 'برنده درست', 'wc26-predictor' ) ); ?>',
				draw: '<?php echo esc_js( __( 'مساوی درست', 'wc26-predictor' ) ); ?>',
				one_team: '<?php echo esc_js( __( 'گل یک تیم درست', 'wc26-predictor' ) ); ?>',
				miss: '<?php echo esc_js( __( 'اشتباه', 'wc26-predictor' ) ); ?>',
				'': '<?php echo esc_js( __( 'در انتظار نتیجه', 'wc26-predictor' ) ); ?>',
			};
			return map[type] || type;
		},
	};
}
</script>
