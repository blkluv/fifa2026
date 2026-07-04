<?php
/**
 * REST API Router — registers all /wp-json/wc26/v1/ endpoints.
 *
 * Adapted for Real Estate Prediction Market with Chainlink CRE Integration
 *
 * @package WC26Predictor\REST
 */

declare(strict_types=1);

namespace WC26Predictor\REST;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WC26Predictor\Plugin;

class Router {

	private const NAMESPACE = 'wc26/v1';
	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function register(): void {
		$ns = self::NAMESPACE;

		// ── Markets (replaces Matches) ──────────────────────────────────────
		register_rest_route( $ns, '/markets', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMarkets' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( $ns, '/markets/(?P<id>\d+)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMarket' ],
			'permission_callback' => '__return_true',
			'args'                => [ 'id' => [ 'validate_callback' => fn($v) => is_numeric($v) ] ],
		] );

		// ── Regions (replaces Groups) & Standings ────────────────────────────
		register_rest_route( $ns, '/regions', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getRegions' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( $ns, '/standings', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getStandings' ],
			'permission_callback' => '__return_true',
		] );

		// ── Predictions ──────────────────────────────────────────────────────
		register_rest_route( $ns, '/predict', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'submitPrediction' ],
			'permission_callback' => [ $this, 'requireAuth' ],
			'args'                => [
				'market_id'        => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) ],
				'predicted_price'  => [ 'required' => false, 'validate_callback' => fn($v) => is_numeric($v) ],
				'predicted_trend'  => [ 'required' => true, 'validate_callback' => fn($v) => in_array($v, ['increase','decrease','stable','volatile']) ],
				'predicted_range'  => [ 'required' => false ],
				'is_joker'         => [ 'required' => false, 'default' => false ],
			],
		] );

		register_rest_route( $ns, '/my-predictions', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMyPredictions' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		// ── Admin — submit market result ─────────────────────────────────────
		register_rest_route( $ns, '/admin/markets/(?P<id>\d+)/result', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'submitMarketResult' ],
			'permission_callback' => [ $this, 'requireAdmin' ],
			'args'                => [
				'final_price'      => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) ],
				'price_change_pct' => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) ],
				'market_trend'     => [ 'required' => true, 'validate_callback' => fn($v) => in_array($v, ['increase','decrease','stable','volatile']) ],
			],
		] );

		// ── Chainlink CRE Endpoints (NEW) ──────────────────────────────────
		register_rest_route( $ns, '/chainlink/report', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'submitChainlinkReport' ],
			'permission_callback' => [ $this, 'requireAdmin' ],
			'args'                => [
				'market_id'    => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) ],
				'don_id'       => [ 'required' => true ],
				'report_data'  => [ 'required' => true ],
				'signature'    => [ 'required' => false ],
			],
		] );

		register_rest_route( $ns, '/chainlink/reports/(?P<id>\d+)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getChainlinkReport' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		// ... (keep other endpoints like leaderboard, leagues, notifications with minor text changes)
	}

	// ── Updated Callbacks ────────────────────────────────────────────────────

	public function getMarkets( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\MarketService $svc */
		$svc  = $this->plugin->make( 'market_service' );
		$data = $svc->getAllWithDetails();

		/** @var \WC26Predictor\Services\PredictionService $ps */
		$ps = $this->plugin->make( 'prediction_service' );
		$data = array_map( fn($m) => array_merge( $m, [ 'locked' => $ps->isLocked($m) ] ), $data );

		return rest_ensure_response( $data );
	}

	public function getMarket( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$svc  = $this->plugin->make( 'market_service' );
		$data = $svc->getMarketWithDetails( (int) $req['id'] );

		if ( ! $data ) {
			return new WP_Error( 'not_found', __( 'Market not found.', 'wc26-predictor' ), [ 'status' => 404 ] );
		}

		/** @var \WC26Predictor\Services\PredictionService $ps */
		$ps   = $this->plugin->make( 'prediction_service' );
		$data = array_merge( $data, [ 'locked' => $ps->isLocked( $data ) ] );

		return rest_ensure_response( $data );
	}

	public function getRegions( WP_REST_Request $req ): WP_REST_Response {
		global $wpdb;
		$t    = $wpdb->prefix . 'wc26_regions';
		$rows = $wpdb->get_results( "SELECT * FROM {$t} ORDER BY name", ARRAY_A );
		return rest_ensure_response( $rows );
	}

	public function submitPrediction( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			$this->applyRateLimit( (int) get_current_user_id(), 'predict', 10, MINUTE_IN_SECONDS );
			/** @var \WC26Predictor\Services\PredictionService $svc */
			$svc  = $this->plugin->make( 'prediction_service' );
			$data = $svc->submit(
				get_current_user_id(),
				(int) $req->get_param( 'market_id' ),
				$req->get_param( 'predicted_price' ) !== null ? (float) $req->get_param( 'predicted_price' ) : null,
				(string) $req->get_param( 'predicted_trend' ),
				$req->get_param( 'predicted_range' ),
				(bool) $req->get_param( 'is_joker' )
			);
			return rest_ensure_response( $data );
		} catch ( \RuntimeException $e ) {
			return new WP_Error( 'prediction_error', $e->getMessage(), [ 'status' => 422 ] );
		}
	}

	public function getMyPredictions( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\PredictionService $svc */
		$svc  = $this->plugin->make( 'prediction_service' );
		$data = $svc->getUserPredictions( get_current_user_id() );
		return rest_ensure_response( $data );
	}

	public function submitMarketResult( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			/** @var \WC26Predictor\Services\MarketService $svc */
			$svc = $this->plugin->make( 'market_service' );
			$result = $svc->submitResult(
				(int) $req['id'],
				(float) $req['final_price'],
				(float) $req['price_change_pct'],
				(string) $req['market_trend']
			);

			// Trigger Chainlink CRE hook for DON reporting
			do_action( 'wc26_market_settled', (int) $req['id'], $result['outcome'], $result['confidence'] ?? 0.0 );

			return rest_ensure_response( [ 'success' => true, 'result' => $result ] );
		} catch ( \RuntimeException $e ) {
			return new WP_Error( 'result_error', $e->getMessage(), [ 'status' => 422 ] );
		}
	}

	// ── Chainlink CRE Callbacks (NEW) ──────────────────────────────────────

	public function submitChainlinkReport( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			/** @var \WC26Predictor\Services\ChainlinkService $svc */
			$svc = $this->plugin->make( 'chainlink_service' );
			$id = $svc->createReport(
				(int) $req['market_id'],
				(string) $req['don_id'],
				$req->get_param( 'report_data' ),
				$req->get_param( 'signature' )
			);
			return rest_ensure_response( [ 'report_id' => $id ] );
		} catch ( \RuntimeException $e ) {
			return new WP_Error( 'report_error', $e->getMessage(), [ 'status' => 422 ] );
		}
	}

	public function getChainlinkReport( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		/** @var \WC26Predictor\Services\ChainlinkService $svc */
		$svc = $this->plugin->make( 'chainlink_service' );
		$data = $svc->getReport( (int) $req['id'] );
		if ( ! $data ) {
			return new WP_Error( 'not_found', __( 'Report not found.', 'wc26-predictor' ), [ 'status' => 404 ] );
		}
		return rest_ensure_response( $data );
	}

	// ... (keep other methods with updated text strings)
}
