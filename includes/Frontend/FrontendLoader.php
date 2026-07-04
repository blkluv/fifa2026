<?php
/**
 * FrontendLoader — shortcodes, assets, and AJAX for the public-facing UI.
 *
 * Adapted for Real Estate Prediction Market with Chainlink CRE Integration
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
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueAssets' ] );

		// Shortcodes - Real Estate versions
		add_shortcode( 'wc26_market_prediction', [ $this, 'renderMarketPrediction' ] );
		add_shortcode( 'wc26_market_leaderboard', [ $this, 'renderLeaderboard' ] );
		add_shortcode( 'wc26_region_standings',   [ $this, 'renderRegionStandings' ] );
		add_shortcode( 'wc26_my_markets',         [ $this, 'renderMyMarkets' ] );
		add_shortcode( 'wc26_my_leagues',         [ $this, 'renderMyLeagues' ] );
		add_shortcode( 'wc26_scoring_rules',      [ $this, 'renderScoringRules' ] );
		
		// Legacy shortcodes (kept for backward compatibility)
		add_shortcode( 'wc26_predictor',   [ $this, 'renderMarketPrediction' ] );
		add_shortcode( 'wc26_leaderboard', [ $this, 'renderLeaderboard' ] );
		add_shortcode( 'wc26_standings',   [ $this, 'renderRegionStandings' ] );
		add_shortcode( 'wc26_app',         [ $this, 'renderApp' ] );
	}

	public function enqueueAssets(): void {
		global $post;
		
		// All shortcodes (new + legacy)
		$shortcodes = [
			'wc26_market_prediction', 'wc26_market_leaderboard', 'wc26_region_standings',
			'wc26_my_markets', 'wc26_my_leagues', 'wc26_scoring_rules',
			'wc26_predictor', 'wc26_leaderboard', 'wc26_standings', 'wc26_app'
		];
		$has = false;

		if ( is_a( $post, 'WP_Post' ) ) {
			foreach ( $shortcodes as $sc ) {
				if ( has_shortcode( $post->post_content, $sc ) ) {
					$has = true;
					break;
				}
			}
		}

		if ( ! $has && is_page_template( 'page-templates/real-estate-predictor.php' ) ) {
			$has = true;
		}

		if ( ! $has ) {
			return;
		}

		// Local fonts
		wp_enqueue_style(
			'wc26-fonts',
			WC26_PLUGIN_URL . 'assets/css/fonts.css',
			[],
			WC26_VERSION
		);

		// Alpine.js — local copy, no CDN
		wp_enqueue_script(
			'alpinejs',
			WC26_PLUGIN_URL . 'assets/js/vendor/alpine.min.js',
			[],
			'3.14.1',
			true
		);

		wp_enqueue_style(
			'wc26-frontend',
			WC26_PLUGIN_URL . 'assets/css/frontend.css',
			[ 'wc26-fonts' ],
			WC26_VERSION
		);

		wp_enqueue_script(
			'wc26-frontend',
			WC26_PLUGIN_URL . 'assets/js/frontend.js',
			[ 'wp-api-fetch' ],
			WC26_VERSION,
			true
		);

		wp_localize_script( 'wc26-frontend', 'wc26', [
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'apiBase'     => rest_url( 'wc26/v1' ),
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'isLoggedIn'  => is_user_logged_in(),
			'loginUrl'    => wp_login_url( get_permalink() ),
			'currentUser' => is_user_logged_in() ? get_current_user_id() : null,
			'strings'     => [
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

	// ── Shortcode renderers ────────────────────────────────────────────────────

	/**
	 * Shortcode: Market Prediction (Real Estate)
	 * Usage: [wc26_market_prediction region_id="0" limit="10"]
	 */
	public function renderMarketPrediction( array $atts ): string {
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
	 * Shortcode: Leaderboard (Real Estate)
	 * Usage: [wc26_market_leaderboard limit="50" region_id="0"]
	 */
	public function renderLeaderboard( array $atts ): string {
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
	 * Shortcode: Region Standings (Real Estate)
	 * Usage: [wc26_region_standings region_id="1"]
	 */
	public function renderRegionStandings( array $atts ): string {
		$atts = shortcode_atts( [ 'region_id' => 0 ], $atts );

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
	 * Shortcode: My Markets (User's predictions)
	 * Usage: [wc26_my_markets]
	 */
	public function renderMyMarkets( array $atts ): string {
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
	 * Usage: [wc26_my_leagues]
	 */
	public function renderMyLeagues( array $atts ): string {
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
	 * Usage: [wc26_scoring_rules]
	 */
	public function renderScoringRules( array $atts ): string {
		ob_start();
		$template = WC26_PLUGIN_DIR . 'templates/frontend/scoring-rules.php';
		if ( file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}

	/**
	 * Shortcode: Standalone React/Vue App (Legacy support)
	 * Usage: [wc26_app]
	 */
	public function renderApp( array $atts ): string {
		$mt   = @filemtime( WC26_PLUGIN_DIR . 'wp26 predictor/index.html' );
		$ver  = WC26_VERSION . ( $mt ? ( '-' . (string) $mt ) : '' );
		$url  = wp_make_link_relative( WC26_PLUGIN_URL . 'wp26%20predictor/index.html?v=' . rawurlencode( $ver ) );
		$user = wp_get_current_user();

		$authToken = null;
		if ( is_user_logged_in() ) {
			$authToken = $this->buildAuthToken( (int) $user->ID );
		}

		$boot = [
			'apiBase'   => wp_make_link_relative( rest_url( 'wc26/v1' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'authToken' => $authToken,
			'me'        => is_user_logged_in()
				? [
					'id'           => (int) $user->ID,
					'display_name' => (string) $user->display_name,
					'user_login'   => (string) $user->user_login,
					'avatar'       => (string) get_avatar_url( (int) $user->ID, [ 'size' => 96 ] ),
				]
				: null,
		];

		$bootJson = wp_json_encode( $boot );
		$iframeId = 'wc26-app-' . wp_generate_uuid4();

		return '<script>window.wc26AppBoot=' . $bootJson . ';</script>'
			. '<iframe id="' . esc_attr( $iframeId ) . '" src="' . esc_url( $url ) . '" style="width:100%;height:min(900px,100vh);border:0;border-radius:12px;overflow:hidden;"></iframe>'
			. '<script>(function(){var boot=' . $bootJson . ';var f=document.getElementById(' . wp_json_encode( $iframeId ) . ');if(!f){return;}var send=function(){try{f.contentWindow&&f.contentWindow.postMessage({type:"wc26_boot",boot:boot},"*");}catch(e){}};f.addEventListener("load",function(){send();setTimeout(send,200);setTimeout(send,800);});send();})();</script>';
	}

	/**
	 * Build a JWT-like auth token for the standalone app
	 */
	private function buildAuthToken( int $userId ): string {
		$payload = [
			'uid' => $userId,
			'exp' => time() + HOUR_IN_SECONDS,
			'n'   => wp_generate_uuid4(),
		];
		$json = wp_json_encode( $payload );
		$b64  = rtrim( strtr( base64_encode( (string) $json ), '+/', '-_' ), '=' );
		$sig  = hash_hmac( 'sha256', $b64, wp_salt( 'auth' ) );
		return $b64 . '.' . $sig;
	}
}
