<?php
/**
 * Frontend Loader - registers shortcodes and assets
 *
 * @package WC26Predictor\Frontend
 */

declare(strict_types=1);

namespace WC26Predictor\Frontend;

use WC26Predictor\Plugin;

class FrontendLoader {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		add_shortcode( 'wc26_market_prediction', [ $this, 'renderMarketPrediction' ] );
		add_shortcode( 'wc26_market_leaderboard', [ $this, 'renderLeaderboard' ] );
		add_shortcode( 'wc26_region_standings', [ $this, 'renderRegionStandings' ] );
		add_shortcode( 'wc26_my_markets', [ $this, 'renderMyMarkets' ] );
		add_shortcode( 'wc26_my_leagues', [ $this, 'renderMyLeagues' ] );
		add_shortcode( 'wc26_scoring_rules', [ $this, 'renderScoringRules' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	/**
	 * Shortcode: Market Prediction
	 */
	public function renderMarketPrediction( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'region_id' => 0,
			'limit'     => 10,
		], $atts );

		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/market-prediction.php';
		if ( file_exists( $template ) ) {
			$regionId = (int) $atts['region_id'];
			$limit    = (int) $atts['limit'];
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode: Leaderboard
	 */
	public function renderLeaderboard( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'limit'     => 50,
			'region_id' => 0,
		], $atts );

		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/leaderboard.php';
		if ( file_exists( $template ) ) {
			$limit    = (int) $atts['limit'];
			$regionId = (int) $atts['region_id'];
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode: Region Standings
	 */
	public function renderRegionStandings( array $atts = [] ): string {
		$atts = shortcode_atts( [
			'region_id' => 0,
		], $atts );

		if ( empty( $atts['region_id'] ) ) {
			return '<p class="wc26-error">' . esc_html__( 'Please specify a region_id.', 'wc26-predictor' ) . '</p>';
		}

		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/region-standings.php';
		if ( file_exists( $template ) ) {
			$regionId = (int) $atts['region_id'];
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode: My Markets (user's predictions)
	 */
	public function renderMyMarkets( array $atts = [] ): string {
		if ( ! is_user_logged_in() ) {
			return '<p class="wc26-error">' . esc_html__( 'Please log in to see your markets.', 'wc26-predictor' ) . '</p>';
		}

		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/my-markets.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode: My Leagues
	 */
	public function renderMyLeagues( array $atts = [] ): string {
		if ( ! is_user_logged_in() ) {
			return '<p class="wc26-error">' . esc_html__( 'Please log in to manage leagues.', 'wc26-predictor' ) . '</p>';
		}

		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/my-leagues.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode: Scoring Rules
	 */
	public function renderScoringRules( array $atts = [] ): string {
		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/scoring-rules.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueueAssets(): void {
		global $post;

		// Only enqueue if shortcode is present
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		$shortcodes = [
			'wc26_market_prediction',
			'wc26_market_leaderboard',
			'wc26_region_standings',
			'wc26_my_markets',
			'wc26_my_leagues',
			'wc26_scoring_rules',
		];

		$has_shortcode = false;
		foreach ( $shortcodes as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				$has_shortcode = true;
				break;
			}
		}

		if ( ! $has_shortcode ) {
			return;
		}

		wp_enqueue_style(
			'wc26-frontend',
			WC26_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			WC26_VERSION
		);

		wp_enqueue_script(
			'wc26-frontend',
			WC26_PLUGIN_URL . 'assets/js/frontend.js',
			[ 'jquery', 'wp-api-fetch' ],
			WC26_VERSION,
			true
		);

		wp_localize_script( 'wc26-frontend', 'wc26', [
			'apiBase'      => rest_url( 'wc26/v1' ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'isLoggedIn'   => is_user_logged_in(),
			'userId'       => get_current_user_id(),
			'display_name' => is_user_logged_in() ? wp_get_current_user()->display_name : '',
			'avatar'       => is_user_logged_in() ? get_avatar_url( get_current_user_id(), [ 'size' => 96 ] ) : '',
			'strings'      => [
				'submit'           => __( 'Submit', 'wc26-predictor' ),
				'submitting'       => __( 'Submitting...', 'wc26-predictor' ),
				'error'            => __( 'Error. Please try again.', 'wc26-predictor' ),
				'success'          => __( 'Prediction submitted!', 'wc26-predictor' ),
				'locked'           => __( 'Locked', 'wc26-predictor' ),
				'no_prediction'    => __( 'No prediction', 'wc26-predictor' ),
				'logged_in_required' => __( 'Please log in to submit predictions.', 'wc26-predictor' ),
			],
		] );
	}
}
