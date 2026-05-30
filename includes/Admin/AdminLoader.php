<?php
/**
 * AdminLoader — boots all WP-Admin pages and assets.
 *
 * @package WC26Predictor\Admin
 */

declare(strict_types=1);

namespace WC26Predictor\Admin;

use WC26Predictor\Plugin;

class AdminLoader {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_action( 'admin_menu',            [ $this, 'registerMenus' ] );
		add_action( 'admin_init',            [ $this, 'registerSettings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
		add_action( 'wp_ajax_wc26_import_csv',     [ new CsvImportHandler( $this->plugin ), 'handle' ] );
		add_action( 'wp_ajax_wc26_seed_sample_teams', [ new SampleDataHandler( $this->plugin ), 'seedTeams' ] );
		add_action( 'wp_ajax_wc26_reset_import_openfootball_2026', [ new OfficialDataImportHandler( $this->plugin ), 'resetAndImport' ] );
		add_action( 'wp_ajax_wc26_localize_teams_fa', [ new LocalizeTeamsHandler( $this->plugin ), 'handle' ] );
		add_action( 'wp_ajax_wc26_save_match',     [ new MatchAdminHandler( $this->plugin ), 'save' ] );
		add_action( 'wp_ajax_wc26_submit_result',  [ new MatchAdminHandler( $this->plugin ), 'submitResult' ] );
	}

	public function registerSettings(): void {
		register_setting( 'wc26_settings', 'wc26_lock_minutes', [
			'type'              => 'integer',
			'sanitize_callback' => static fn( $v ) => max( 0, min( 60, (int) $v ) ),
			'default'           => 1,
		] );

		register_setting( 'wc26_settings', 'wc26_telegram_token', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		] );
	}

	public function registerMenus(): void {
		add_menu_page(
			__( 'پیش‌بینی جام جهانی ۲۰۲۶', 'wc26-predictor' ),
			__( 'پیش‌بینی جام جهانی', 'wc26-predictor' ),
			'manage_options',
			'wc26-predictor',
			[ $this, 'renderDashboard' ],
			'dashicons-football',
			30
		);

		$pages = [
			[ 'wc26-matches',       __( 'مدیریت مسابقات', 'wc26-predictor' ),   [ $this, 'renderMatches' ] ],
			[ 'wc26-teams',         __( 'مدیریت تیم‌ها', 'wc26-predictor' ),     [ $this, 'renderTeams' ] ],
			[ 'wc26-groups',        __( 'مدیریت گروه‌ها', 'wc26-predictor' ),    [ $this, 'renderGroups' ] ],
			[ 'wc26-winners',       __( 'برندگان پیش‌بینی', 'wc26-predictor' ),   [ $this, 'renderWinners' ] ],
			[ 'wc26-leaderboard',   __( 'جدول امتیازات', 'wc26-predictor' ),     [ $this, 'renderLeaderboard' ] ],
			[ 'wc26-scoring-rules', __( 'قوانین امتیازدهی', 'wc26-predictor' ),  [ $this, 'renderScoringRules' ] ],
			[ 'wc26-csv-import',    __( 'ایمپورت CSV', 'wc26-predictor' ),      [ $this, 'renderCsvImport' ] ],
			[ 'wc26-settings',      __( 'تنظیمات', 'wc26-predictor' ),          [ $this, 'renderSettings' ] ],
		];

		foreach ( $pages as [ $slug, $title, $cb ] ) {
			add_submenu_page( 'wc26-predictor', $title, $title, 'manage_options', $slug, $cb );
		}
	}

	public function enqueueAssets( string $hook ): void {
		if ( strpos( $hook, 'wc26' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wc26-admin',
			WC26_PLUGIN_URL . 'assets/css/admin.css',
			[],
			WC26_VERSION
		);

		wp_enqueue_script(
			'wc26-admin',
			WC26_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery', 'wp-api-fetch' ],
			WC26_VERSION,
			true
		);

		wp_localize_script( 'wc26-admin', 'wc26Admin', [
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'apiBase' => rest_url( 'wc26/v1' ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		] );
	}

	// ── Page renderers ────────────────────────────────────────────────────────

	public function renderDashboard(): void {
		$this->view( 'dashboard' );
	}

	public function renderMatches(): void {
		$this->view( 'matches' );
	}

	public function renderTeams(): void {
		$this->view( 'teams' );
	}

	public function renderGroups(): void {
		$this->view( 'groups' );
	}

	public function renderWinners(): void {
		global $wpdb;
		$tP = $wpdb->prefix . 'wc26_predictions';
		$tM = $wpdb->prefix . 'wc26_matches';
		$tT = $wpdb->prefix . 'wc26_teams';
		$u  = $wpdb->users;

		// Leaderboard direct from predictions (source of truth)
		$rows = $wpdb->get_results(
			"SELECT
				u.ID                                                                        AS user_id,
				u.display_name,
				u.user_login,
				COALESCE(SUM(p.earned_points), 0)                                          AS total_points,
				SUM(CASE WHEN p.prediction_type = 'exact'            THEN 1 ELSE 0 END)    AS exact_hits,
				SUM(CASE WHEN p.prediction_type = 'goal_diff'        THEN 1 ELSE 0 END)    AS goal_diff_hits,
				SUM(CASE WHEN p.prediction_type IN ('winner','draw')  THEN 1 ELSE 0 END)   AS winner_hits,
				COUNT(p.id)                                                                 AS total_scored,
				SUM(CASE WHEN p.earned_points > 0                    THEN 1 ELSE 0 END)    AS correct_scored
			FROM {$u} u
			JOIN {$tP} p  ON p.user_id = u.ID
			JOIN {$tM} m  ON m.id = p.match_id AND m.status = 'finished'
			GROUP BY u.ID, u.display_name, u.user_login
			ORDER BY total_points DESC, exact_hits DESC",
			ARRAY_A
		) ?: [];

		// Match-by-match prediction detail (last 20 finished matches)
		$matchDetail = $wpdb->get_results(
			"SELECT
				m.id            AS match_id,
				m.kickoff_at,
				ht.name         AS home_team,
				at.name         AS away_team,
				m.home_score    AS real_home,
				m.away_score    AS real_away,
				COUNT(p.id)                                                                AS total_preds,
				SUM(CASE WHEN p.prediction_type = 'exact'           THEN 1 ELSE 0 END)    AS exact_count,
				SUM(CASE WHEN p.prediction_type = 'goal_diff'       THEN 1 ELSE 0 END)    AS diff_count,
				SUM(CASE WHEN p.prediction_type IN ('winner','draw') THEN 1 ELSE 0 END)   AS winner_count,
				SUM(CASE WHEN p.earned_points = 0                   THEN 1 ELSE 0 END)    AS miss_count
			FROM {$tM} m
			LEFT JOIN {$tT} ht ON ht.id = m.home_team_id
			LEFT JOIN {$tT} at ON at.id = m.away_team_id
			LEFT JOIN {$tP} p  ON p.match_id = m.id
			WHERE m.status = 'finished'
			GROUP BY m.id, m.kickoff_at, ht.name, at.name, m.home_score, m.away_score
			ORDER BY m.kickoff_at DESC
			LIMIT 30",
			ARRAY_A
		) ?: [];

		$this->view( 'winners', [ 'rows' => $rows, 'matchDetail' => $matchDetail ] );
	}

	public function renderLeaderboard(): void {
		/** @var \WC26Predictor\Services\LeaderboardService $svc */
		$svc  = $this->plugin->make( 'leaderboard_service' );
		$rows = $svc->getGlobal( 200 );
		$this->view( 'leaderboard', [ 'rows' => $rows ] );
	}

	public function renderScoringRules(): void {
		/** @var \WC26Predictor\Services\ScoringService $svc */
		$svc   = $this->plugin->make( 'scoring_service' );
		$rows  = $svc->getRulesRows();
		$this->view( 'scoring-rules', [ 'rows' => $rows ] );
	}

	public function renderCsvImport(): void {
		$this->view( 'csv-import' );
	}

	public function renderSettings(): void {
		$this->view( 'settings' );
	}

	private function view( string $name, array $vars = [] ): void {
		extract( $vars, EXTR_SKIP );
		$file = WC26_PLUGIN_DIR . "templates/admin/{$name}.php";
		if ( file_exists( $file ) ) {
			require $file;
		} else {
			echo '<div class="wrap"><h1>' . esc_html( $name ) . '</h1><p>' . esc_html__( 'قالب پیدا نشد.', 'wc26-predictor' ) . '</p></div>';
		}
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * MatchAdminHandler — AJAX handler for admin match management.
 */
class MatchAdminHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function save(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'دسترسی غیرمجاز.', 'wc26-predictor' ), 403 );
		}

		$data = [
			'home_team_id' => (int) ( $_POST['home_team_id'] ?? 0 ),
			'away_team_id' => (int) ( $_POST['away_team_id'] ?? 0 ),
			'kickoff_at'   => sanitize_text_field( $_POST['kickoff_at'] ?? '' ),
			'venue'        => sanitize_text_field( $_POST['venue'] ?? '' ),
			'stage'        => sanitize_key( $_POST['stage'] ?? 'group' ),
			'group_id'     => (int) ( $_POST['group_id'] ?? 0 ) ?: null,
		];

		/** @var \WC26Predictor\Services\MatchService $svc */
		$svc = $this->plugin->make( 'match_service' );

		if ( ! empty( $_POST['match_id'] ) ) {
			$svc->update( (int) $_POST['match_id'], $data );
			wp_send_json_success( [ 'updated' => true ] );
		} else {
			$id = $svc->create( $data );
			wp_send_json_success( [ 'id' => $id ] );
		}
	}

	public function submitResult(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'دسترسی غیرمجاز.', 'wc26-predictor' ), 403 );
		}

		try {
			/** @var \WC26Predictor\Services\MatchService $svc */
			$svc = $this->plugin->make( 'match_service' );
			$svc->submitResult(
				(int) ( $_POST['match_id'] ?? 0 ),
				(int) ( $_POST['home_score'] ?? 0 ),
				(int) ( $_POST['away_score'] ?? 0 )
			);
			wp_send_json_success();
		} catch ( \RuntimeException $e ) {
			wp_send_json_error( $e->getMessage(), 422 );
		}
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * CsvImportHandler — handles CSV bulk imports for teams, matches, groups.
 */
class CsvImportHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function handle(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'دسترسی غیرمجاز.', 'wc26-predictor' ), 403 );
		}

		$type = sanitize_key( $_POST['import_type'] ?? '' );
		$file = $_FILES['csv_file'] ?? null;

		if ( ! $file || ! in_array( $type, [ 'teams', 'matches', 'groups' ], true ) ) {
			wp_send_json_error( __( 'درخواست نامعتبر است.', 'wc26-predictor' ) );
		}

		$tmpPath = $file['tmp_name'];
		$handle  = fopen( $tmpPath, 'r' );

		if ( ! $handle ) {
			wp_send_json_error( __( 'امکان باز کردن فایل وجود ندارد.', 'wc26-predictor' ) );
		}

		$headers = fgetcsv( $handle );
		$count   = 0;

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );

		switch ( $type ) {
			case 'teams':
				$count = $importService->importTeams( $handle );
				break;
			case 'matches':
				$count = $importService->importMatches( $handle );
				break;
			case 'groups':
				$count = $importService->importGroups( $handle );
				break;
		}

		fclose( $handle );
		wp_send_json_success( [ 'imported' => $count ] );
	}
}

class SampleDataHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function seedTeams(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'دسترسی غیرمجاز.', 'wc26-predictor' ), 403 );
		}

		$path = WC26_PLUGIN_DIR . 'migrations/sample-teams.csv';
		if ( ! file_exists( $path ) ) {
			wp_send_json_error( __( 'فایل نمونه تیم‌ها پیدا نشد.', 'wc26-predictor' ), 404 );
		}

		$handle = fopen( $path, 'r' );
		if ( ! $handle ) {
			wp_send_json_error( __( 'امکان باز کردن فایل نمونه وجود ندارد.', 'wc26-predictor' ) );
		}

		fgetcsv( $handle );

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );
		$count         = $importService->importTeams( $handle );

		fclose( $handle );

		wp_send_json_success( [ 'imported' => $count ] );
	}
}

class OfficialDataImportHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function resetAndImport(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'دسترسی غیرمجاز.', 'wc26-predictor' ), 403 );
		}

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );

		try {
			$importService->resetAllData();
			$stats = $importService->importWorldcup2026FromOpenFootball();
		} catch ( \Throwable $e ) {
			wp_send_json_error( $e->getMessage() );
		}

		wp_send_json_success( $stats );
	}
}

class LocalizeTeamsHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function handle(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'دسترسی غیرمجاز.', 'wc26-predictor' ), 403 );
		}

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );
		$count         = $importService->updateTeamNamesFa();

		wp_send_json_success( [ 'updated' => $count ] );
	}
}
