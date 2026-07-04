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
	echo '<p>' . esc_html__( 'Please log in to manage leagues.', 'wc26-predictor' ) . '</p>';
	return;
}
?>

<div class="wc26-my-leagues" x-data="wc26MyLeagues()" x-init="init()">

	<!-- Create League -->
	<div class="wc26-card" style="margin-bottom:1rem;">
		<h3><?php esc_html_e( 'Create Private League', 'wc26-predictor' ); ?></h3>
		<div style="display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap;">
			<input type="text" x-model="newName" placeholder="<?php esc_attr_e( 'League name...', 'wc26-predictor' ); ?>"
				class="regular-text" style="flex:1; min-width:200px; padding:0.4rem 0.75rem; border-radius:6px; border:1px solid var(--wc26-border); background:var(--wc26-surface); color:var(--wc26-text);">
			<select x-model="newRegionId" class="regular-text" style="padding:0.4rem 0.75rem; border-radius:6px; border:1px solid var(--wc26-border);">
				<option value="0"><?php esc_html_e( 'Select Region...', 'wc26-predictor' ); ?></option>
				<?php
				global $wpdb;
				$regions = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}wc26_regions ORDER BY name" );
				foreach ( $regions as $region ) {
					echo '<option value="' . esc_attr( $region->id ) . '">' . esc_html( $region->name ) . '</option>';
				}
				?>
			</select>
			<button class="wc26-btn wc26-btn-primary" @click="createLeague()" :disabled="creating">
				<span x-show="!creating"><?php esc_html_e( 'Create', 'wc26-predictor' ); ?></span>
				<span x-show="creating"><?php esc_html_e( 'Creating...', 'wc26-predictor' ); ?></span>
			</button>
		</div>
		<div x-show="createMsg" x-text="createMsg" style="margin-top:0.5rem; color:var(--wc26-green);"></div>
	</div>

	<!-- Join League -->
	<div class="wc26-card" style="margin-bottom:1rem;">
		<h3><?php esc_html_e( 'Join League', 'wc26-predictor' ); ?></h3>
		<div style="display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap;">
			<input type="text" x-model="joinCode" placeholder="<?php esc_attr_e( 'Invite code (e.g. AB12CD34)', 'wc26-predictor' ); ?>"
				class="regular-text" style="flex:1; min-width:200px; padding:0.4rem 0.75rem; border-radius:6px; border:1px solid var(--wc26-border); background:var(--wc26-surface); color:var(--wc26-text); text-transform:uppercase;">
			<button class="wc26-btn wc26-btn-primary" @click="joinLeague()" :disabled="joining">
				<span x-show="!joining"><?php esc_html_e( 'Join', 'wc26-predictor' ); ?></span>
				<span x-show="joining"><?php esc_html_e( 'Joining...', 'wc26-predictor' ); ?></span>
			</button>
		</div>
		<div x-show="joinMsg" x-text="joinMsg" style="margin-top:0.5rem; color:var(--wc26-green);"></div>
	</div>

	<!-- My Leagues List -->
	<template x-if="!loading">
		<div class="wc26-card">
			<h3><?php esc_html_e( 'My Leagues', 'wc26-predictor' ); ?></h3>
			<template x-if="leagues.length === 0">
				<p class="wc26-muted"><?php esc_html_e( 'You haven\'t joined any leagues yet.', 'wc26-predictor' ); ?></p>
			</template>
			<template x-for="lg in leagues" :key="lg.id">
				<div class="wc26-league-row" @click="toggleLeague(lg)" style="cursor:pointer;">
					<div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 0; border-bottom:1px solid var(--wc26-border);">
						<div>
							<strong x-text="lg.name"></strong>
							<span class="wc26-muted" x-text="' (' + lg.region_name + ')'"></span>
							<code style="margin-left:0.5rem; font-size:0.75rem; color:var(--wc26-muted);" x-text="'#' + lg.invite_code"></code>
						</div>
						<span x-text="activeLeague?.id === lg.id ? '▲' : '▼'" style="color:var(--wc26-muted);"></span>
					</div>

					<!-- Inline Leaderboard -->
					<template x-if="activeLeague?.id === lg.id">
						<div style="padding:0.75rem 0;">
							<template x-if="leagueRows.length === 0">
								<p class="wc26-muted"><?php esc_html_e( 'No rankings yet.', 'wc26-predictor' ); ?></p>
							</template>
							<template x-for="(row, i) in leagueRows" :key="i">
								<div class="wc26-pick-row" style="border-left-color:var(--wc26-blue);">
									<span style="color:var(--wc26-muted); min-width:24px;" x-text="i + 1"></span>
									<span x-text="row.display_name" style="flex:1;"></span>
									<strong x-text="row.total_points + ' ' + '<?php echo esc_js( __( 'pts', 'wc26-predictor' ) ); ?>'" style="color:var(--wc26-gold);"></strong>
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
		loading: true,
		leagues: [],
		leagueRows: [],
		activeLeague: null,
		newName: '',
		newRegionId: 0,
		joinCode: '',
		creating: false,
		joining: false,
		createMsg: '',
		joinMsg: '',

		async init() {
			if (!wc26.isLoggedIn) return;
			await this.fetchLeagues();
			this.loading = false;
		},

		async fetchLeagues() {
			try {
				const r = await fetch(wc26.apiBase + '/my-leagues', {
					headers: { 'X-WP-Nonce': wc26.nonce }
				});
				if (r.ok) this.leagues = await r.json();
			} catch(e) {}
		},

		async createLeague() {
			if (!this.newName.trim() || !this.newRegionId) {
				this.createMsg = '<?php echo esc_js( __( 'Please enter a name and select a region.', 'wc26-predictor' ) ); ?>';
				return;
			}
			this.creating = true;
			this.createMsg = '';
			try {
				const r = await fetch(wc26.apiBase + '/leagues', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': wc26.nonce },
					body: JSON.stringify({ name: this.newName.trim(), region_id: this.newRegionId })
				});
				const d = await r.json();
				if (r.ok) {
					this.createMsg = '<?php echo esc_js( __( 'League created! Invite code: ', 'wc26-predictor' ) ); ?>' + d.invite_code;
					this.newName = '';
					this.newRegionId = 0;
					await this.fetchLeagues();
				} else {
					this.createMsg = (d.message || '<?php echo esc_js( __( 'Error', 'wc26-predictor' ) ); ?>');
				}
			} catch(e) {
				this.createMsg = '<?php echo esc_js( __( 'Network error', 'wc26-predictor' ) ); ?>';
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
					this.joinMsg = '<?php echo esc_js( __( 'Joined: ', 'wc26-predictor' ) ); ?>' + d.name;
					this.joinCode = '';
					await this.fetchLeagues();
				} else {
					this.joinMsg = (d.message || '<?php echo esc_js( __( 'Invalid invite code.', 'wc26-predictor' ) ); ?>');
				}
			} catch(e) {
				this.joinMsg = '<?php echo esc_js( __( 'Network error', 'wc26-predictor' ) ); ?>';
			} finally {
				this.joining = false;
			}
		},

		async toggleLeague(lg) {
			if (this.activeLeague?.id === lg.id) {
				this.activeLeague = null;
				this.leagueRows = [];
				return;
			}
			this.activeLeague = lg;
			this.leagueRows = [];
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
