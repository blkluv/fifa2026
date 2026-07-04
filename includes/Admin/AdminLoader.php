<?php
/**
 * AdminLoader — boots all WP-Admin pages and assets.
 * 
 * Adapted for Real Estate Prediction Market with Chainlink CRE Integration
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
		add_action( 'wp_ajax_wc26_seed_sample_teams', [ new SampleDataHandler( $this->plugin ), 'seedMarkets' ] );
		add_action( 'wp_ajax_wc26_reset_import_openfootball_2026', [ new OfficialDataImportHandler( $this->plugin ), 'resetAndImport' ] );
		add_action( 'wp_ajax_wc26_localize_teams_fa', [ new LocalizeMarketsHandler( $this->plugin ), 'handle' ] );
		add_action( 'wp_ajax_wc26_save_market',     [ new MarketAdminHandler( $this->plugin ), 'save' ] );
		add_action( 'wp_ajax_wc26_submit_result',  [ new MarketAdminHandler( $this->plugin ), 'submitResult' ] );
		
		// Chainlink CRE Integration Hooks
		add_action( 'wc26_market_settled', [ $this, 'triggerChainlinkReport' ], 10, 3 );
	}

	/**
	 * Chainlink CRE Integration: Trigger DON report on market settlement
	 */
	public function triggerChainlinkReport( int $market_id, string $outcome, float $confidence ): void {
		// This hook would be consumed by a separate Chainlink CRE workflow
		// The workflow would read the market data and submit a DON-signed report
		do_action( 'wc26_chainlink_report_requested', $market_id, $outcome, $confidence );
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

		// Real Estate specific settings
		register_setting( 'wc26_settings', 'wc26_market_data_api', [
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'default'           => 'https://api.realestate-data.com/v1',
		] );

		register_setting( 'wc26_settings', 'wc26_chainlink_don_id', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		] );
	}

	public function registerMenus(): void {
		add_menu_page(
			__( 'Real Estate Prediction Market', 'wc26-predictor' ),
			__( 'RE Prediction Market', 'wc26-predictor' ),
			'manage_options',
			'wc26-predictor',
			[ $this, 'renderDashboard' ],
			'dashicons-building',
			30
		);

		$pages = [
			[ 'wc26-markets',       __( 'Manage Markets', 'wc26-predictor' ),   [ $this, 'renderMarkets' ] ],
			[ 'wc26-properties',    __( 'Manage Properties', 'wc26-predictor' ), [ $this, 'renderProperties' ] ],
			[ 'wc26-regions',       __( 'Manage Regions', 'wc26-predictor' ),    [ $this, 'renderRegions' ] ],
			[ 'wc26-winners',       __( 'Prediction Winners', 'wc26-predictor' ), [ $this, 'renderWinners' ] ],
			[ 'wc26-leaderboard',   __( 'Leaderboard', 'wc26-predictor' ),       [ $this, 'renderLeaderboard' ] ],
			[ 'wc26-scoring-rules', __( 'Scoring Rules', 'wc26-predictor' ),     [ $this, 'renderScoringRules' ] ],
			[ 'wc26-csv-import',    __( 'CSV Import', 'wc26-predictor' ),        [ $this, 'renderCsvImport' ] ],
			[ 'wc26-chainlink-cre', __( 'Chainlink CRE', 'wc26-predictor' ),     [ $this, 'renderChainlinkCre' ] ],
			[ 'wc26-settings',      __( 'Settings', 'wc26-predictor' ),          [ $this, 'renderSettings' ] ],
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

	public function renderMarkets(): void {
		$this->view( 'markets' );
	}

	public function renderProperties(): void {
		$this->view( 'properties' );
	}

	public function renderRegions(): void {
		$this->view( 'regions' );
	}

	public function renderWinners(): void {
		global $wpdb;
		$tP = $wpdb->prefix . 'wc26_predictions';
		$tM = $wpdb->prefix . 'wc26_markets';
		$tR = $wpdb->prefix . 'wc26_regions';
		$u  = $wpdb->users;

		// Leaderboard direct from predictions (source of truth)
		$rows = $wpdb->get_results(
			"SELECT
				u.ID                                                                        AS user_id,
				u.display_name,
				u.user_login,
				COALESCE(SUM(p.earned_points), 0)                                          AS total_points,
				SUM(CASE WHEN p.prediction_type = 'exact'            THEN 1 ELSE 0 END)    AS exact_hits,
				SUM(CASE WHEN p.prediction_type = 'price_range'      THEN 1 ELSE 0 END)    AS price_range_hits,
				SUM(CASE WHEN p.prediction_type IN ('increase','decrease','stable')  THEN 1 ELSE 0 END)   AS trend_hits,
				COUNT(p.id)                                                                 AS total_scored,
				SUM(CASE WHEN p.earned_points > 0                    THEN 1 ELSE 0 END)    AS correct_scored
			FROM {$u} u
			JOIN {$tP} p  ON p.user_id = u.ID
			JOIN {$tM} m  ON m.id = p.market_id AND m.status = 'settled'
			GROUP BY u.ID, u.display_name, u.user_login
			ORDER BY total_points DESC, exact_hits DESC",
			ARRAY_A
		) ?: [];

		// Market-by-market prediction detail (last 30 settled markets)
		$marketDetail = $wpdb->get_results(
			"SELECT
				m.id                AS market_id,
				m.forecast_date,
				r.name              AS region_name,
				m.initial_price     AS initial_price,
				m.final_price       AS final_price,
				m.price_change_pct  AS price_change_pct,
				m.market_trend      AS market_trend,
				COUNT(p.id)                                                                AS total_preds,
				SUM(CASE WHEN p.prediction_type = 'exact'           THEN 1 ELSE 0 END)    AS exact_count,
				SUM(CASE WHEN p.prediction_type = 'price_range'     THEN 1 ELSE 0 END)    AS range_count,
				SUM(CASE WHEN p.prediction_type IN ('increase','decrease','stable') THEN 1 ELSE 0 END)   AS trend_count,
				SUM(CASE WHEN p.earned_points = 0                   THEN 1 ELSE 0 END)    AS miss_count
			FROM {$tM} m
			LEFT JOIN {$tR} r ON r.id = m.region_id
			LEFT JOIN {$tP} p  ON p.market_id = m.id
			WHERE m.status = 'settled'
			GROUP BY m.id, m.forecast_date, r.name, m.initial_price, m.final_price, m.price_change_pct, m.market_trend
			ORDER BY m.forecast_date DESC
			LIMIT 30",
			ARRAY_A
		) ?: [];

		$this->view( 'winners', [ 'rows' => $rows, 'marketDetail' => $marketDetail ] );
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
	
	public function renderChainlinkCre(): void {
		$this->view( 'chainlink-cre' );
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
			echo '<div class="wrap"><h1>' . esc_html( $name ) . '</h1><p>' . esc_html__( 'Template not found.', 'wc26-predictor' ) . '</p></div>';
		}
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * MarketAdminHandler — AJAX handler for admin market management.
 * Adapted for Real Estate Markets
 */
class MarketAdminHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function save(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'wc26-predictor' ), 403 );
		}

		$data = [
			'region_id'        => (int) ( $_POST['region_id'] ?? 0 ),
			'property_type'    => sanitize_text_field( $_POST['property_type'] ?? '' ),
			'forecast_date'    => sanitize_text_field( $_POST['forecast_date'] ?? '' ),
			'initial_price'    => (float) ( $_POST['initial_price'] ?? 0 ),
			'market_trend'     => sanitize_key( $_POST['market_trend'] ?? 'stable' ),
			'region_id'        => (int) ( $_POST['region_id'] ?? 0 ) ?: null,
		];

		/** @var \WC26Predictor\Services\MarketService $svc */
		$svc = $this->plugin->make( 'market_service' );

		if ( ! empty( $_POST['market_id'] ) ) {
			$svc->update( (int) $_POST['market_id'], $data );
			wp_send_json_success( [ 'updated' => true ] );
		} else {
			$id = $svc->create( $data );
			wp_send_json_success( [ 'id' => $id ] );
		}
	}

	public function submitResult(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'wc26-predictor' ), 403 );
		}

		try {
			/** @var \WC26Predictor\Services\MarketService $svc */
			$svc = $this->plugin->make( 'market_service' );
			$result = $svc->submitResult(
				(int) ( $_POST['market_id'] ?? 0 ),
				(float) ( $_POST['final_price'] ?? 0 ),
				(float) ( $_POST['price_change_pct'] ?? 0 ),
				sanitize_key( $_POST['market_trend'] ?? 'stable' )
			);
			
			// Trigger Chainlink CRE hook for DON reporting
			do_action( 'wc26_market_settled', (int) $_POST['market_id'], $result['outcome'], $result['confidence'] );
			
			wp_send_json_success( $result );
		} catch ( \RuntimeException $e ) {
			wp_send_json_error( $e->getMessage(), 422 );
		}
	}
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * CsvImportHandler — handles CSV bulk imports for markets, properties, regions.
 * Adapted for Real Estate
 */
class CsvImportHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function handle(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'wc26-predictor' ), 403 );
		}

		$type = sanitize_key( $_POST['import_type'] ?? '' );
		$file = $_FILES['csv_file'] ?? null;

		if ( ! $file || ! in_array( $type, [ 'markets', 'properties', 'regions' ], true ) ) {
			wp_send_json_error( __( 'Invalid request.', 'wc26-predictor' ) );
		}

		$tmpPath = $file['tmp_name'];
		$handle  = fopen( $tmpPath, 'r' );

		if ( ! $handle ) {
			wp_send_json_error( __( 'Could not open file.', 'wc26-predictor' ) );
		}

		$headers = fgetcsv( $handle );
		$count   = 0;

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );

		switch ( $type ) {
			case 'markets':
				$count = $importService->importMarkets( $handle );
				break;
			case 'properties':
				$count = $importService->importProperties( $handle );
				break;
			case 'regions':
				$count = $importService->importRegions( $handle );
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

	public function seedMarkets(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'wc26-predictor' ), 403 );
		}

		$path = WC26_PLUGIN_DIR . 'migrations/sample-markets.csv';
		if ( ! file_exists( $path ) ) {
			wp_send_json_error( __( 'Sample markets file not found.', 'wc26-predictor' ), 404 );
		}

		$handle = fopen( $path, 'r' );
		if ( ! $handle ) {
			wp_send_json_error( __( 'Could not open sample file.', 'wc26-predictor' ) );
		}

		fgetcsv( $handle );

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );
		$count         = $importService->importMarkets( $handle );

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
			wp_send_json_error( __( 'Unauthorized access.', 'wc26-predictor' ), 403 );
		}

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );

		try {
			$importService->resetAllData();
			$stats = $importService->importRealEstateDataFromAPI();
		} catch ( \Throwable $e ) {
			wp_send_json_error( $e->getMessage() );
		}

		wp_send_json_success( $stats );
	}
}

class LocalizeMarketsHandler {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function handle(): void {
		check_ajax_referer( 'wp_rest', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'wc26-predictor' ), 403 );
		}

		/** @var \WC26Predictor\Services\ImportService $importService */
		$importService = $this->plugin->make( 'import_service' );
		$count         = $importService->updateMarketDataFromAPI();

		wp_send_json_success( [ 'updated' => $count ] );
	}
}
