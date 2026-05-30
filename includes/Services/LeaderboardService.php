<?php
/**
 * LeaderboardService — cached global + mini-league leaderboards.
 *
 * @package WC26Predictor\Services
 */
 
declare(strict_types=1);
 
namespace WC26Predictor\Services;
 
use WC26Predictor\Repositories\LeaderboardRepository;
use WC26Predictor\Repositories\MiniLeagueMemberRepository;
 
class LeaderboardService {
 
	private LeaderboardRepository $leaderboards;
	private MiniLeagueMemberRepository $members;
 
	public function __construct() {
		$this->leaderboards = new LeaderboardRepository();
		$this->members      = new MiniLeagueMemberRepository();
	}
 
	/** @return array<int,array<string,mixed>> */
	public function getGlobal( int $limit = 100 ): array {
		$limit = max( 1, min( 500, $limit ) );
 
		$cacheKey = 'wc26_global_leaderboard';
		$cache    = wp_cache_get( $cacheKey, 'wc26' );
 
		if ( false !== $cache ) {
			return array_slice( (array) $cache, 0, $limit );
		}
 
		$rows = $this->leaderboards->getGlobalTop( 500 );
		wp_cache_set( $cacheKey, $rows, 'wc26', 5 * MINUTE_IN_SECONDS );
 
		return array_slice( $rows, 0, $limit );
	}
 
	/** @return array<int,array<string,mixed>> */
	public function getMiniLeague( int $leagueId ): array {
		$cacheKey = "wc26_league_{$leagueId}";
		$cache    = wp_cache_get( $cacheKey, 'wc26' );
 
		if ( false !== $cache ) {
			return (array) $cache;
		}
 
		$rows = $this->members->getLeagueLeaderboard( $leagueId );
		wp_cache_set( $cacheKey, $rows, 'wc26', 5 * MINUTE_IN_SECONDS );
 
		return $rows;
	}
 
	public function getUserRank( int $userId ): ?int {
		$row = $this->leaderboards->findBy( [ 'user_id' => $userId ] );
		return $row ? (int) $row[0]['rank_position'] : null;
	}

	/** @return array<string,mixed> */
	public function getUserSummary( int $userId ): array {
		global $wpdb;

		$tLb = $wpdb->prefix . 'wc26_leaderboards';
		$tP  = $wpdb->prefix . 'wc26_predictions';
		$tM  = $wpdb->prefix . 'wc26_matches';

		// Compute stats directly from predictions (source of truth).
		// This is always correct even if the incremental leaderboard cache drifted.
		$hitStats = $wpdb->get_row( $wpdb->prepare(
			"SELECT
				COALESCE(SUM(p.earned_points), 0)                                         AS total_points,
				SUM(CASE WHEN p.prediction_type = 'exact'               THEN 1 ELSE 0 END) AS exact_hits,
				SUM(CASE WHEN p.prediction_type = 'goal_diff'           THEN 1 ELSE 0 END) AS goal_diff_hits,
				SUM(CASE WHEN p.prediction_type IN ('winner','draw')     THEN 1 ELSE 0 END) AS winner_hits,
				COUNT(*)                                                                    AS total_scored,
				SUM(CASE WHEN p.earned_points > 0                       THEN 1 ELSE 0 END) AS correct_scored
			 FROM {$tP} p
			 JOIN {$tM} m ON m.id = p.match_id
			 WHERE p.user_id = %d AND m.status = 'finished'",
			$userId
		), ARRAY_A );

		$totalPoints  = (int) ( $hitStats['total_points']  ?? 0 );
		$exactHits    = (int) ( $hitStats['exact_hits']    ?? 0 );
		$goalDiffHits = (int) ( $hitStats['goal_diff_hits'] ?? 0 );
		$winnerHits   = (int) ( $hitStats['winner_hits']   ?? 0 );
		$totalScored  = (int) ( $hitStats['total_scored']  ?? 0 );
		$correctScored = (int) ( $hitStats['correct_scored'] ?? 0 );

		// Compute rank from predictions directly — never needs the leaderboard table.
		// Count users with strictly more points (or same points but more exact hits).
		$usersAbove = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM (
				SELECT SUM(p2.earned_points) AS pts,
				       SUM(CASE WHEN p2.prediction_type = 'exact' THEN 1 ELSE 0 END) AS ex
				FROM {$tP} p2
				JOIN {$tM} m2 ON m2.id = p2.match_id
				WHERE m2.status = 'finished'
				GROUP BY p2.user_id
				HAVING pts > %d OR (pts = %d AND ex > %d)
			) AS better_users",
			$totalPoints, $totalPoints, $exactHits
		) );
		$rank = 1 + $usersAbove;

		// Total distinct users who have at least one scored prediction.
		$totalUsers = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT p.user_id) FROM {$tP} p
			 JOIN {$tM} m ON m.id = p.match_id
			 WHERE m.status = 'finished'"
		);
		$totalUsers = max( 1, $totalUsers ); // always at least the current user

		// Best-effort sync to leaderboard table (for global board). Failures are non-fatal.
		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$tLb} (user_id, total_points, exact_hits, goal_diff_hits, winner_hits)
			 VALUES (%d, %d, %d, %d, %d)
			 ON DUPLICATE KEY UPDATE
			   total_points   = VALUES(total_points),
			   exact_hits     = VALUES(exact_hits),
			   goal_diff_hits = VALUES(goal_diff_hits),
			   winner_hits    = VALUES(winner_hits)",
			$userId, $totalPoints, $exactHits, $goalDiffHits, $winnerHits
		) );

		$accuracy = $totalScored > 0 ? (int) round( 100 * ( $correctScored / $totalScored ) ) : 0;

		$sinceTs  = (int) current_time( 'timestamp' ) - ( 7 * DAY_IN_SECONDS );
		$sinceStr = wp_date( 'Y-m-d H:i:s', $sinceTs, wp_timezone() );

		$pointsLast7Days = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(p.earned_points), 0) FROM {$tP} p
			 JOIN {$tM} m ON m.id = p.match_id
			 WHERE p.user_id = %d AND m.status = 'finished' AND m.kickoff_at >= %s",
			$userId,
			$sinceStr
		) );

		$earnedRows = $wpdb->get_col( $wpdb->prepare(
			"SELECT p.earned_points FROM {$tP} p
			 JOIN {$tM} m ON m.id = p.match_id
			 WHERE p.user_id = %d AND m.status = 'finished'
			 ORDER BY m.kickoff_at DESC
			 LIMIT 50",
			$userId
		) ) ?: [];

		$streak = 0;
		foreach ( $earnedRows as $pts ) {
			if ( (int) $pts > 0 ) {
				$streak++;
				continue;
			}
			break;
		}

		return [
			'user_id'            => $userId,
			'total_points'       => $totalPoints,
			'exact_hits'         => $exactHits,
			'goal_diff_hits'     => $goalDiffHits,
			'winner_hits'        => $winnerHits,
			'rank_position'      => $rank,
			'total_users'        => $totalUsers,
			'total_scored'       => $totalScored,
			'correct_scored'     => $correctScored,
			'accuracy_pct'       => $accuracy,
			'streak'             => $streak,
			'points_last_7_days' => $pointsLast7Days,
		];
	}
}
