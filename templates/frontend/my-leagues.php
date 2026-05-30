<?php
/**
 * Frontend Template: My Mini Leagues
 * Usage: [wc26_my_leagues]
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	echo '<p>' . esc_html__( 'برای مدیریت لیگ‌ها ابتدا وارد شوید.', 'wc26-predictor' ) . '</p>';
	return;
}
?>

<div class="wc26-my-leagues" x-data="wc26MyLeagues()" x-init="init()">

	<!-- Create League -->
	<div class="wc26-card" style="margin-bottom:1rem;">
		<h3><?php esc_html_e( 'ساخت لیگ خصوصی', 'wc26-predictor' ); ?></h3>
		<div style="display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap;">
			<input type="text" x-model="newName" placeholder="<?php esc_attr_e( 'نام لیگ…', 'wc26-predictor' ); ?>"
				class="regular-text" style="flex:1; min-width:200px; padding:0.4rem 0.75rem; border-radius:6px; border:1px solid var(--wc26-border); background:var(--wc26-surface); color:var(--wc26-text);">
			<button class="wc26-btn wc26-btn-primary" @click="createLeague()" :disabled="creating">
				<span x-show="!creating"><?php esc_html_e( 'ایجاد', 'wc26-predictor' ); ?></span>
				<span x-show="creating"><?php esc_html_e( 'در حال ایجاد…', 'wc26-predictor' ); ?></span>
			</button>
		</div>
		<div x-show="createMsg" x-text="createMsg" style="margin-top:0.5rem; color:var(--wc26-green);"></div>
	</div>

	<!-- Join League -->
	<div class="wc26-card" style="margin-bottom:1rem;">
		<h3><?php esc_html_e( 'عضویت در لیگ', 'wc26-predictor' ); ?></h3>
		<div style="display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap;">
			<input type="text" x-model="joinCode" placeholder="<?php esc_attr_e( 'کد دعوت (مثلاً AB12CD34)', 'wc26-predictor' ); ?>"
				class="regular-text" style="flex:1; min-width:200px; padding:0.4rem 0.75rem; border-radius:6px; border:1px solid var(--wc26-border); background:var(--wc26-surface); color:var(--wc26-text); text-transform:uppercase;">
			<button class="wc26-btn wc26-btn-primary" @click="joinLeague()" :disabled="joining">
				<span x-show="!joining"><?php esc_html_e( 'عضویت', 'wc26-predictor' ); ?></span>
				<span x-show="joining"><?php esc_html_e( 'در حال ارسال…', 'wc26-predictor' ); ?></span>
			</button>
		</div>
		<div x-show="joinMsg" x-text="joinMsg" style="margin-top:0.5rem; color:var(--wc26-green);"></div>
	</div>

	<!-- My Leagues List -->
	<template x-if="!loading">
		<div class="wc26-card">
			<h3><?php esc_html_e( 'لیگ‌های من', 'wc26-predictor' ); ?></h3>
			<template x-if="leagues.length === 0">
				<p class="wc26-muted"><?php esc_html_e( 'هنوز عضو هیچ لیگی نشده‌اید.', 'wc26-predictor' ); ?></p>
			</template>
			<template x-for="lg in leagues" :key="lg.id">
				<div class="wc26-league-row" @click="toggleLeague(lg)" style="cursor:pointer;">
					<div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 0; border-bottom:1px solid var(--wc26-border);">
						<div>
							<strong x-text="lg.name"></strong>
							<code style="margin-left:0.5rem; font-size:0.75rem; color:var(--wc26-muted);" x-text="'#' + lg.invite_code"></code>
						</div>
						<span x-text="activeLeague?.id === lg.id ? '▲' : '▼'" style="color:var(--wc26-muted);"></span>
					</div>

					<!-- Inline Leaderboard -->
					<template x-if="activeLeague?.id === lg.id">
						<div style="padding:0.75rem 0;">
							<template x-if="leagueRows.length === 0">
								<p class="wc26-muted"><?php esc_html_e( 'هنوز رتبه‌بندی‌ای وجود ندارد.', 'wc26-predictor' ); ?></p>
							</template>
							<template x-for="(row, i) in leagueRows" :key="i">
								<div class="wc26-pick-row" style="border-left-color:var(--wc26-blue);">
									<span style="color:var(--wc26-muted); min-width:24px;" x-text="i + 1"></span>
									<span x-text="row.display_name" style="flex:1;"></span>
									<strong x-text="row.total_points + ' امتیاز'" style="color:var(--wc26-gold);"></strong>
								</div>
							</template>
						</div>
					</template>
				</div>
			</template>
		</div>
	</template>

</div>

<script>
function wc26MyLeagues() {
	return {
		loading:      true,
		leagues:      [],
		leagueRows:   [],
		activeLeague: null,
		newName:      '',
		joinCode:     '',
		creating:     false,
		joining:      false,
		createMsg:    '',
		joinMsg:      '',

		async init() {
			if (!wc26.isLoggedIn) return;
			await this.fetchLeagues();
			this.loading = false;
		},

		async fetchLeagues() {
			// Fetch user's leagues via WP API
			try {
				const r = await fetch(wc26.apiBase + '/my-leagues', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				if (r.ok) this.leagues = await r.json();
			} catch(e) {}
		},

		async createLeague() {
			if (!this.newName.trim()) return;
			this.creating = true;
			this.createMsg = '';
			try {
				const r = await fetch(wc26.apiBase + '/leagues', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': wc26.nonce },
					body: JSON.stringify({ name: this.newName.trim() })
				});
				const d = await r.json();
				if (r.ok) {
					this.createMsg = '<?php echo esc_js( __( 'لیگ ساخته شد. کد دعوت: ', 'wc26-predictor' ) ); ?>' + d.invite_code;
					this.newName = '';
					await this.fetchLeagues();
				} else {
					this.createMsg = (d.message || '<?php echo esc_js( __( 'خطا', 'wc26-predictor' ) ); ?>');
				}
			} catch(e) {
				this.createMsg = '<?php echo esc_js( __( 'خطای شبکه', 'wc26-predictor' ) ); ?>';
			} finally {
				this.creating = false;
			}
		},

		async joinLeague() {
			if (!this.joinCode.trim()) return;
			this.joining = true;
			this.joinMsg = '';
			try {
				const r = await fetch(wc26.apiBase + '/leagues/join', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': wc26.nonce },
					body: JSON.stringify({ code: this.joinCode.trim() })
				});
				const d = await r.json();
				if (r.ok) {
					this.joinMsg = '<?php echo esc_js( __( 'عضویت انجام شد: ', 'wc26-predictor' ) ); ?>' + d.name;
					this.joinCode = '';
					await this.fetchLeagues();
				} else {
					this.joinMsg = (d.message || '<?php echo esc_js( __( 'کد دعوت نامعتبر است.', 'wc26-predictor' ) ); ?>');
				}
			} catch(e) {
				this.joinMsg = '<?php echo esc_js( __( 'خطای شبکه', 'wc26-predictor' ) ); ?>';
			} finally {
				this.joining = false;
			}
		},

		async toggleLeague(lg) {
			if (this.activeLeague?.id === lg.id) {
				this.activeLeague = null;
				this.leagueRows   = [];
				return;
			}
			this.activeLeague = lg;
			this.leagueRows   = [];
			try {
				const r = await fetch(wc26.apiBase + '/leagues/' + lg.id + '/leaderboard', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				if (r.ok) this.leagueRows = await r.json();
			} catch(e) {}
		}
	};
}
</script>
