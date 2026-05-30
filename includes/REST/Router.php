<?php
/**
 * REST API Router — registers all /wp-json/wc26/v1/ endpoints.
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

		// ── Matches ──────────────────────────────────────────────────────────
		register_rest_route( $ns, '/matches', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMatches' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( $ns, '/matches/(?P<id>\d+)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMatch' ],
			'permission_callback' => '__return_true',
			'args'                => [ 'id' => [ 'validate_callback' => fn($v) => is_numeric($v) ] ],
		] );

		// ── Groups & Standings ───────────────────────────────────────────────
		register_rest_route( $ns, '/groups', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getGroups' ],
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
				'match_id'   => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) ],
				'home_score' => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) && $v >= 0 ],
				'away_score' => [ 'required' => true, 'validate_callback' => fn($v) => is_numeric($v) && $v >= 0 ],
				'is_joker'   => [ 'required' => false, 'default' => false ],
			],
		] );

		register_rest_route( $ns, '/my-predictions', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMyPredictions' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		register_rest_route( $ns, '/me', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMe' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		register_rest_route( $ns, '/me/summary', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMeSummary' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		register_rest_route( $ns, '/me/badges', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMeBadges' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		// ── Leaderboard ──────────────────────────────────────────────────────
		register_rest_route( $ns, '/leaderboard', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getLeaderboard' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( $ns, '/my-leagues', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getMyLeagues' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		// ── Mini Leagues ─────────────────────────────────────────────────────
		register_rest_route( $ns, '/leagues', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'createLeague' ],
			'permission_callback' => [ $this, 'requireAuth' ],
			'args'                => [ 'name' => [ 'required' => true ] ],
		] );

		register_rest_route( $ns, '/leagues/join', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'joinLeague' ],
			'permission_callback' => [ $this, 'requireAuth' ],
			'args'                => [ 'code' => [ 'required' => true ] ],
		] );

		register_rest_route( $ns, '/leagues/(?P<id>\d+)/leaderboard', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getLeagueLeaderboard' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		// ── Notifications ────────────────────────────────────────────────────
		register_rest_route( $ns, '/notifications', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getNotifications' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		register_rest_route( $ns, '/notifications/read', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'markNotificationsRead' ],
			'permission_callback' => [ $this, 'requireAuth' ],
		] );

		// ── Scoring rules (public) ────────────────────────────────────────────
		register_rest_route( $ns, '/scoring-rules', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getScoringRules' ],
			'permission_callback' => '__return_true',
		] );

		// ── Admin — localize team names to Persian ────────────────────────────
		register_rest_route( $ns, '/admin/teams/localize-fa', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'localizeTeamsFa' ],
			'permission_callback' => [ $this, 'requireAdmin' ],
		] );

		register_rest_route( $ns, '/admin/scoring-rules', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'updateScoringRules' ],
			'permission_callback' => [ $this, 'requireAdmin' ],
			'args'                => [
				'rules' => [ 'required' => true ],
			],
		] );

		// ── Admin — submit result ─────────────────────────────────────────────
		register_rest_route( $ns, '/admin/matches/(?P<id>\d+)/result', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'submitResult' ],
			'permission_callback' => [ $this, 'requireAdmin' ],
			'args'                => [
				'home_score' => [ 'required' => true ],
				'away_score' => [ 'required' => true ],
			],
		] );
	}

	// ── Callbacks ─────────────────────────────────────────────────────────────

	public function getMatches( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\MatchService $svc */
		$svc  = $this->plugin->make( 'match_service' );
		$data = $svc->getAllWithTeams();

		// Add lock status for each match
		/** @var \WC26Predictor\Services\PredictionService $ps */
		$ps = $this->plugin->make( 'prediction_service' );
		$data = array_map( fn($m) => array_merge( $m, [ 'locked' => $ps->isLocked($m) ] ), $data );

		return rest_ensure_response( $data );
	}

	public function getMatch( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$svc  = $this->plugin->make( 'match_service' );
		$data = $svc->getMatchWithTeams( (int) $req['id'] );

		if ( ! $data ) {
			return new WP_Error( 'not_found', __( 'مسابقه پیدا نشد.', 'wc26-predictor' ), [ 'status' => 404 ] );
		}

		/** @var \WC26Predictor\Services\PredictionService $ps */
		$ps   = $this->plugin->make( 'prediction_service' );
		$data = array_merge( $data, [ 'locked' => $ps->isLocked( $data ) ] );

		return rest_ensure_response( $data );
	}

	public function getGroups( WP_REST_Request $req ): WP_REST_Response {
		global $wpdb;
		$t    = $wpdb->prefix . 'wc26_groups';
		$rows = $wpdb->get_results( "SELECT * FROM {$t} ORDER BY name", ARRAY_A );
		return rest_ensure_response( $rows );
	}

	public function getStandings( WP_REST_Request $req ): WP_REST_Response {
		$groupId = (int) $req->get_param( 'group_id' );
		/** @var \WC26Predictor\Services\StandingsService $svc */
		$svc  = $this->plugin->make( 'standings_service' );
		$data = $groupId ? $svc->getGroupStandings( $groupId ) : [];
		return rest_ensure_response( $data );
	}

	public function submitPrediction( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			$this->applyRateLimit( (int) get_current_user_id(), 'predict', 10, MINUTE_IN_SECONDS );
			/** @var \WC26Predictor\Services\PredictionService $svc */
			$svc  = $this->plugin->make( 'prediction_service' );
			$data = $svc->submit(
				get_current_user_id(),
				(int) $req->get_param( 'match_id' ),
				(int) $req->get_param( 'home_score' ),
				(int) $req->get_param( 'away_score' ),
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

	public function getMe( WP_REST_Request $req ): WP_REST_Response {
		$user = wp_get_current_user();
		return rest_ensure_response( [
			'id'           => (int) $user->ID,
			'display_name' => (string) $user->display_name,
			'user_login'   => (string) $user->user_login,
			'avatar'       => (string) get_avatar_url( (int) $user->ID, [ 'size' => 96 ] ),
		] );
	}

	public function getMeSummary( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\LeaderboardService $svc */
		$svc = $this->plugin->make( 'leaderboard_service' );
		$data = $svc->getUserSummary( (int) get_current_user_id() );
		return rest_ensure_response( $data );
	}

	public function getMeBadges( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\BadgeService $svc */
		$svc  = $this->plugin->make( 'badge_service' );
		$data = $svc->getUserBadgesWithProgress( (int) get_current_user_id() );
		return rest_ensure_response( $data );
	}

	public function getScoringRules( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\ScoringService $svc */
		$svc   = $this->plugin->make( 'scoring_service' );
		$rules = $svc->getRules();
		return rest_ensure_response( $rules );
	}

	public function getLeaderboard( WP_REST_Request $req ): WP_REST_Response {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_leaderboards';
		$exists = (string) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) );
		if ( $exists === '' ) {
			\WC26Predictor\Database\Migrator::run();
		}
		$limit = min( (int) ( $req->get_param( 'limit' ) ?: 100 ), 500 );
		/** @var \WC26Predictor\Services\LeaderboardService $svc */
		$svc  = $this->plugin->make( 'leaderboard_service' );
		$data = $svc->getGlobal( $limit );

		$data = array_map( static function ( $row ) {
			$uid = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
			$row['user_id']        = $uid;
			$row['rank_position']  = isset( $row['rank_position'] ) ? (int) $row['rank_position'] : null;
			$row['total_points']   = isset( $row['total_points'] ) ? (int) $row['total_points'] : 0;
			$row['exact_hits']     = isset( $row['exact_hits'] ) ? (int) $row['exact_hits'] : 0;
			$row['goal_diff_hits'] = isset( $row['goal_diff_hits'] ) ? (int) $row['goal_diff_hits'] : 0;
			$row['winner_hits']    = isset( $row['winner_hits'] ) ? (int) $row['winner_hits'] : 0;
			$row['display_name']   = isset( $row['display_name'] ) ? (string) $row['display_name'] : '';
			$row['avatar']         = $uid ? (string) get_avatar_url( $uid, [ 'size' => 96 ] ) : '';
			return $row;
		}, is_array( $data ) ? $data : [] );

		return rest_ensure_response( $data );
	}

	public function createLeague( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			/** @var \WC26Predictor\Services\LeagueService $svc */
			$svc  = $this->plugin->make( 'league_service' );
			$data = $svc->create( get_current_user_id(), (string) $req['name'] );
			return rest_ensure_response( $data );
		} catch ( \RuntimeException $e ) {
			return new WP_Error( 'league_error', $e->getMessage(), [ 'status' => 422 ] );
		}
	}

	public function joinLeague( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			/** @var \WC26Predictor\Services\LeagueService $svc */
			$svc  = $this->plugin->make( 'league_service' );
			$data = $svc->join( get_current_user_id(), (string) $req['code'] );
			return rest_ensure_response( $data );
		} catch ( \RuntimeException $e ) {
			return new WP_Error( 'league_error', $e->getMessage(), [ 'status' => 422 ] );
		}
	}

	public function getLeagueLeaderboard( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\LeaderboardService $svc */
		$svc  = $this->plugin->make( 'leaderboard_service' );
		$data = $svc->getMiniLeague( (int) $req['id'] );
		return rest_ensure_response( $data );
	}

	public function getMyLeagues( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\LeagueService $svc */
		$svc  = $this->plugin->make( 'league_service' );
		$data = $svc->getUserLeagues( get_current_user_id() );
		return rest_ensure_response( $data );
	}

	public function getNotifications( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\NotificationService $svc */
		$svc  = $this->plugin->make( 'notification_service' );
		$data = $svc->getUnread( get_current_user_id() );
		return rest_ensure_response( $data );
	}

	public function markNotificationsRead( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\NotificationService $svc */
		$svc = $this->plugin->make( 'notification_service' );
		$svc->markAllRead( get_current_user_id() );
		return rest_ensure_response( [ 'success' => true ] );
	}

	public function localizeTeamsFa( WP_REST_Request $req ): WP_REST_Response {
		/** @var \WC26Predictor\Services\ImportService $svc */
		$svc   = $this->plugin->make( 'import_service' );
		$count = $svc->updateTeamNamesFa();
		return rest_ensure_response( [ 'updated' => $count ] );
	}

	public function updateScoringRules( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		$rules = $req->get_param( 'rules' );
		if ( ! is_array( $rules ) ) {
			return new WP_Error( 'invalid_rules', __( 'فرمت قوانین نامعتبر است.', 'wc26-predictor' ), [ 'status' => 422 ] );
		}

		/** @var \WC26Predictor\Services\ScoringService $svc */
		$svc  = $this->plugin->make( 'scoring_service' );
		$res  = $svc->updateRulesRows( $rules );
		return rest_ensure_response( $res );
	}

	public function submitResult( WP_REST_Request $req ): WP_REST_Response|WP_Error {
		try {
			/** @var \WC26Predictor\Services\MatchService $svc */
			$svc = $this->plugin->make( 'match_service' );
			$svc->submitResult(
				(int) $req['id'],
				(int) $req['home_score'],
				(int) $req['away_score'],
				$req->get_param( 'penalty_home' ) !== null ? (int) $req['penalty_home'] : null,
				$req->get_param( 'penalty_away' ) !== null ? (int) $req['penalty_away'] : null
			);
			return rest_ensure_response( [ 'success' => true ] );
		} catch ( \RuntimeException $e ) {
			return new WP_Error( 'result_error', $e->getMessage(), [ 'status' => 422 ] );
		}
	}

	// ── Permission callbacks ──────────────────────────────────────────────────

	public function requireAuth( WP_REST_Request $req ): bool|WP_Error {
		if ( ! is_user_logged_in() ) {
			if ( ! $this->tryTokenAuth( $req ) ) {
				return new WP_Error( 'unauthorized', __( 'برای این عملیات باید وارد شوید.', 'wc26-predictor' ), [ 'status' => 401 ] );
			}
		}

		$method = strtoupper( $req->get_method() );
		if ( ! in_array( $method, [ 'GET', 'HEAD', 'OPTIONS' ], true ) ) {
			$nonce = (string) $req->get_header( 'X-WP-Nonce' );
			if ( $nonce === '' ) {
				$nonce = (string) ( $req->get_param( 'nonce' ) ?: '' );
			}
			if ( $nonce === '' || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				return new WP_Error( 'forbidden', __( 'درخواست نامعتبر است. صفحه را رفرش کنید و دوباره تلاش کنید.', 'wc26-predictor' ), [ 'status' => 403 ] );
			}
		}
		return true;
	}

	private function tryTokenAuth( WP_REST_Request $req ): bool {
		$auth = (string) $req->get_header( 'Authorization' );
		if ( $auth === '' || ! preg_match( '/Bearer\s+(.+)/i', $auth, $m ) ) {
			return false;
		}

		$token = trim( (string) $m[1] );
		$parts = explode( '.', $token, 2 );
		if ( count( $parts ) !== 2 ) {
			return false;
		}

		[ $b64, $sig ] = $parts;
		$calc = hash_hmac( 'sha256', $b64, wp_salt( 'auth' ) );
		if ( ! hash_equals( $calc, (string) $sig ) ) {
			return false;
		}

		$pad = strlen( $b64 ) % 4;
		if ( $pad ) {
			$b64 .= str_repeat( '=', 4 - $pad );
		}
		$json = base64_decode( strtr( $b64, '-_', '+/' ) );
		if ( ! is_string( $json ) || $json === '' ) {
			return false;
		}

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return false;
		}

		$uid = isset( $data['uid'] ) ? (int) $data['uid'] : 0;
		$exp = isset( $data['exp'] ) ? (int) $data['exp'] : 0;
		if ( $uid <= 0 || $exp <= 0 || time() > $exp ) {
			return false;
		}

		wp_set_current_user( $uid );
		return is_user_logged_in();
	}

	public function requireAdmin( WP_REST_Request $req ): bool|WP_Error {
		$auth = $this->requireAuth( $req );
		if ( $auth !== true ) {
			return $auth;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', __( 'دسترسی مدیر لازم است.', 'wc26-predictor' ), [ 'status' => 403 ] );
		}
		return true;
	}

	private function applyRateLimit( int $userId, string $action, int $maxHits, int $windowSeconds ): void {
		if ( $userId <= 0 ) {
			return;
		}

		$key  = "wc26_rl_{$action}_{$userId}";
		$hits = (int) get_transient( $key );

		if ( $hits >= $maxHits ) {
			throw new \RuntimeException( __( 'تعداد درخواست‌های شما بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.', 'wc26-predictor' ) );
		}

		set_transient( $key, $hits + 1, $windowSeconds );
	}
}
