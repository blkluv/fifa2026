<?php
/**
 * StandingsService — calculates and caches group standings.
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\{MatchRepository, StandingsRepository};

class StandingsService {

	private MatchRepository    $matches;
	private StandingsRepository $standings;

	public function __construct() {
		$this->matches   = new MatchRepository();
		$this->standings = new StandingsRepository();
	}

	/** @return array<int,array<string,mixed>> */
	public function getGroupStandings( int $groupId ): array {
		$cacheKey = "wc26_standings_{$groupId}";
		$cached   = wp_cache_get( $cacheKey, 'wc26' );

		if ( false !== $cached ) {
			return (array) $cached;
		}

		$rows = $this->standings->findByGroup( $groupId );
		if ( ! $rows ) {
			$rows = $this->buildEmptyStandingsFromGroupMatches( $groupId );
		}
		wp_cache_set( $cacheKey, $rows, 'wc26', 10 * MINUTE_IN_SECONDS );

		return $rows;
	}

	/** @return array<int,array<string,mixed>> */
	private function buildEmptyStandingsFromGroupMatches( int $groupId ): array {
		global $wpdb;

		$m = $wpdb->prefix . 'wc26_matches';
		$t = $wpdb->prefix . 'wc26_teams';

		$sql = $wpdb->prepare(
			"(SELECT DISTINCT tm.id AS team_id, tm.name AS team_name, tm.code, tm.flag_url
				FROM {$m} ma
				JOIN {$t} tm ON tm.id = ma.home_team_id
				WHERE ma.group_id = %d)
			 UNION
			 (SELECT DISTINCT tm.id AS team_id, tm.name AS team_name, tm.code, tm.flag_url
				FROM {$m} ma
				JOIN {$t} tm ON tm.id = ma.away_team_id
				WHERE ma.group_id = %d)
			 ORDER BY team_name ASC",
			$groupId,
			$groupId
		);

		$teams = $wpdb->get_results( $sql, ARRAY_A ) ?: [];
		$out   = [];

		foreach ( $teams as $row ) {
			$out[] = [
				'id'              => 0,
				'group_id'         => $groupId,
				'team_id'          => (int) $row['team_id'],
				'played'           => 0,
				'won'              => 0,
				'draw'             => 0,
				'lost'             => 0,
				'goals_for'        => 0,
				'goals_against'    => 0,
				'goal_difference'  => 0,
				'points'           => 0,
				'updated_at'       => null,
				'team_name'        => (string) $row['team_name'],
				'code'             => (string) $row['code'],
				'flag_url'         => (string) $row['flag_url'],
			];
		}

		return $out;
	}

	/**
	 * Recalculate standings for the group that a match belongs to.
	 * Called via wc26_match_finished hook.
	 */
	public function recalculateForMatch( int $matchId ): void {
		global $wpdb;

		$match = $this->matches->find( $matchId );
		if ( ! $match || $match['stage'] !== 'group' || ! $match['group_id'] ) {
			return;
		}

		$groupId = (int) $match['group_id'];
		$this->recalculateGroup( $groupId );
	}

	public function recalculateGroup( int $groupId ): void {
		global $wpdb;

		$m = $wpdb->prefix . 'wc26_matches';
		$matches = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$m}
			 WHERE group_id = %d AND status = 'finished'",
			$groupId
		), ARRAY_A );

		if ( ! $matches ) {
			return;
		}

		// Aggregate per team
		$stats = [];

		foreach ( $matches as $match ) {
			$h = (int) $match['home_team_id'];
			$a = (int) $match['away_team_id'];
			$hs = (int) $match['home_score'];
			$as = (int) $match['away_score'];

			$this->ensureTeam( $stats, $h );
			$this->ensureTeam( $stats, $a );

			$stats[$h]['played']++;
			$stats[$a]['played']++;
			$stats[$h]['goals_for']     += $hs;
			$stats[$h]['goals_against'] += $as;
			$stats[$a]['goals_for']     += $as;
			$stats[$a]['goals_against'] += $hs;

			if ( $hs > $as ) {
				$stats[$h]['won']++;
				$stats[$h]['points'] += 3;
				$stats[$a]['lost']++;
			} elseif ( $as > $hs ) {
				$stats[$a]['won']++;
				$stats[$a]['points'] += 3;
				$stats[$h]['lost']++;
			} else {
				$stats[$h]['draw']++;
				$stats[$h]['points']++;
				$stats[$a]['draw']++;
				$stats[$a]['points']++;
			}
		}

		$t = $wpdb->prefix . 'wc26_standings';

		foreach ( $stats as $teamId => $s ) {
			$gd = $s['goals_for'] - $s['goals_against'];
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO {$t}
				    (group_id, team_id, played, won, draw, lost, goals_for, goals_against, goal_difference, points)
				 VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d)
				 ON DUPLICATE KEY UPDATE
				   played = VALUES(played), won = VALUES(won), draw = VALUES(draw),
				   lost = VALUES(lost), goals_for = VALUES(goals_for),
				   goals_against = VALUES(goals_against),
				   goal_difference = VALUES(goal_difference),
				   points = VALUES(points)",
				$groupId, $teamId,
				$s['played'], $s['won'], $s['draw'], $s['lost'],
				$s['goals_for'], $s['goals_against'], $gd, $s['points']
			) );
		}

		wp_cache_delete( "wc26_standings_{$groupId}", 'wc26' );
	}

	private function ensureTeam( array &$stats, int $teamId ): void {
		if ( ! isset( $stats[$teamId] ) ) {
			$stats[$teamId] = [
				'played' => 0, 'won' => 0, 'draw' => 0, 'lost' => 0,
				'goals_for' => 0, 'goals_against' => 0, 'points' => 0,
			];
		}
	}
}
