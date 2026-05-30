<?php
/**
 * Concrete repository classes for every custom table.
 *
 * @package WC26Predictor\Repositories
 */

declare(strict_types=1);

namespace WC26Predictor\Repositories;

// ── Teams ─────────────────────────────────────────────────────────────────────

class TeamRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_teams'; }

	/** @return array<int,array<string,mixed>> */
	public function findByContinent( string $continent ): array {
		return $this->findBy( [ 'continent' => $continent ], 'name' );
	}
}

// ── Groups ────────────────────────────────────────────────────────────────────

class GroupRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_groups'; }

	/** @return array<int,array<string,mixed>> */
	public function findBySeason( string $season ): array {
		return $this->findBy( [ 'season' => $season ], 'name' );
	}
}

// ── Matches ───────────────────────────────────────────────────────────────────

class MatchRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_matches'; }

	/** @return array<int,array<string,mixed>> */
	public function findUpcoming( int $limit = 10 ): array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table}
			 WHERE status = 'scheduled' AND kickoff_at > NOW()
			 ORDER BY kickoff_at ASC
			 LIMIT %d",
			$limit
		);
		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/** @return array<int,array<string,mixed>> */
	public function findByStage( string $stage ): array {
		return $this->findBy( [ 'stage' => $stage ], 'kickoff_at' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findFinished(): array {
		return $this->findBy( [ 'status' => 'finished' ], 'kickoff_at', 'DESC' );
	}

	public function findWithTeams( int $matchId ): ?array {
		global $wpdb;
		$m  = $this->table;
		$t  = $wpdb->prefix . 'wc26_teams';
		$sql = $wpdb->prepare(
			"SELECT m.*,
			        ht.name AS home_team_name, ht.code AS home_team_code, ht.flag_url AS home_flag,
			        at.name AS away_team_name, at.code AS away_team_code, at.flag_url AS away_flag
			 FROM {$m} m
			 LEFT JOIN {$t} ht ON ht.id = m.home_team_id
			 LEFT JOIN {$t} at ON at.id = m.away_team_id
			 WHERE m.id = %d LIMIT 1",
			$matchId
		);
		$row = $wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public function findAllWithTeams(): array {
		global $wpdb;
		$m = $this->table;
		$t = $wpdb->prefix . 'wc26_teams';
		$sql = "SELECT m.*,
		               ht.name AS home_team_name, ht.code AS home_team_code, ht.flag_url AS home_flag,
		               at.name AS away_team_name, at.code AS away_team_code, at.flag_url AS away_flag
		        FROM {$m} m
		        LEFT JOIN {$t} ht ON ht.id = m.home_team_id
		        LEFT JOIN {$t} at ON at.id = m.away_team_id
		        ORDER BY m.kickoff_at ASC";
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}
}

// ── Predictions ───────────────────────────────────────────────────────────────

class PredictionRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_predictions'; }

	public function findByUserAndMatch( int $userId, int $matchId ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE user_id = %d AND match_id = %d LIMIT 1",
			$userId, $matchId
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public function findByUser( int $userId ): array {
		return $this->findBy( [ 'user_id' => $userId ], 'created_at', 'DESC' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findByUserWithMatches( int $userId ): array {
		global $wpdb;
		$p  = $this->table;
		$m  = $wpdb->prefix . 'wc26_matches';
		$t  = $wpdb->prefix . 'wc26_teams';
		$sql = $wpdb->prepare(
			"SELECT p.*,
			        m.kickoff_at, m.stage, m.venue,
			        m.status        AS match_status,
			        m.home_score    AS real_home_score,
			        m.away_score    AS real_away_score,
			        ht.name         AS home_team_name,
			        ht.code         AS home_team_code,
			        ht.flag_url     AS home_flag,
			        at.name         AS away_team_name,
			        at.code         AS away_team_code,
			        at.flag_url     AS away_flag
			 FROM {$p} p
			 JOIN {$m} m  ON m.id  = p.match_id
			 LEFT JOIN {$t} ht ON ht.id = m.home_team_id
			 LEFT JOIN {$t} at ON at.id = m.away_team_id
			 WHERE p.user_id = %d
			 ORDER BY m.kickoff_at DESC",
			$userId
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/** @return array<int,array<string,mixed>> */
	public function findByMatch( int $matchId ): array {
		return $this->findBy( [ 'match_id' => $matchId ] );
	}

	public function upsert( int $userId, int $matchId, int $homeScore, int $awayScore, bool $isJoker = false ): int {
		$existing = $this->findByUserAndMatch( $userId, $matchId );

		if ( $existing ) {
			$this->update(
				[
					'pred_home_score' => $homeScore,
					'pred_away_score' => $awayScore,
					'is_joker'        => (int) $isJoker,
				],
				[ 'id' => $existing['id'] ]
			);
			return (int) $existing['id'];
		}

		return $this->insert( [
			'user_id'         => $userId,
			'match_id'        => $matchId,
			'pred_home_score' => $homeScore,
			'pred_away_score' => $awayScore,
			'is_joker'        => (int) $isJoker,
		] );
	}
}

// ── Standings ─────────────────────────────────────────────────────────────────

class StandingsRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_standings'; }

	/** @return array<int,array<string,mixed>> */
	public function findByGroup( int $groupId ): array {
		global $wpdb;
		$s = $this->table;
		$t = $wpdb->prefix . 'wc26_teams';
		$sql = $wpdb->prepare(
			"SELECT st.*, tm.name AS team_name, tm.code, tm.flag_url
			 FROM {$s} st
			 JOIN {$t} tm ON tm.id = st.team_id
			 WHERE st.group_id = %d
			 ORDER BY st.points DESC, st.goal_difference DESC, st.goals_for DESC",
			$groupId
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}
}

// ── Leaderboards ──────────────────────────────────────────────────────────────

class LeaderboardRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_leaderboards'; }

	public function ensureUser( int $userId ): void {
		global $wpdb;
		$t = $this->table;
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$t} (user_id, total_points, exact_hits, goal_diff_hits, winner_hits)
			 VALUES (%d, 0, 0, 0, 0)",
			$userId
		) );
	}

	/** @return array<int,array<string,mixed>> */
	public function getGlobalTop( int $limit = 100 ): array {
		global $wpdb;
		$l = $this->table;
		$sql = $wpdb->prepare(
			"SELECT lb.*, u.display_name
			 FROM {$l} lb
			 JOIN {$wpdb->users} u ON u.ID = lb.user_id
			 ORDER BY lb.total_points DESC, lb.exact_hits DESC
			 LIMIT %d",
			$limit
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	public function upsertUser( int $userId, int $pointsDelta, string $type ): void {
		global $wpdb;
		$t = $this->table;

		$exactDelta    = ( $type === 'exact' ) ? 1 : 0;
		$goalDiffDelta = ( $type === 'goal_diff' ) ? 1 : 0;
		$winnerDelta   = ( $type === 'winner' || $type === 'draw' ) ? 1 : 0;

		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$t} (user_id, total_points, exact_hits, goal_diff_hits, winner_hits)
			 VALUES (%d, %d, %d, %d, %d)
			 ON DUPLICATE KEY UPDATE
			   total_points   = total_points   + %d,
			   exact_hits     = exact_hits     + %d,
			   goal_diff_hits = goal_diff_hits + %d,
			   winner_hits    = winner_hits    + %d",
			$userId, $pointsDelta, $exactDelta, $goalDiffDelta, $winnerDelta,
			$pointsDelta, $exactDelta, $goalDiffDelta, $winnerDelta
		) );
	}

	/**
	 * Rebuild leaderboard rows for specific users from their actual prediction data.
	 * This is authoritative — always correct, never double-counts.
	 *
	 * @param int[] $userIds
	 */
	public function rebuildForUsers( array $userIds ): void {
		global $wpdb;
		if ( empty( $userIds ) ) {
			return;
		}

		$t  = $this->table;
		$tP = $wpdb->prefix . 'wc26_predictions';
		$tM = $wpdb->prefix . 'wc26_matches';

		foreach ( $userIds as $uid ) {
			$uid = (int) $uid;
			$row = $wpdb->get_row( $wpdb->prepare(
				"SELECT
					COALESCE(SUM(p.earned_points), 0)                                          AS total_points,
					SUM(CASE WHEN p.prediction_type = 'exact'            THEN 1 ELSE 0 END)    AS exact_hits,
					SUM(CASE WHEN p.prediction_type = 'goal_diff'        THEN 1 ELSE 0 END)    AS goal_diff_hits,
					SUM(CASE WHEN p.prediction_type IN ('winner','draw')  THEN 1 ELSE 0 END)    AS winner_hits
				 FROM {$tP} p
				 JOIN {$tM} m ON m.id = p.match_id
				 WHERE p.user_id = %d AND m.status = 'finished'",
				$uid
			), ARRAY_A );

			$wpdb->query( $wpdb->prepare(
				"INSERT INTO {$t} (user_id, total_points, exact_hits, goal_diff_hits, winner_hits)
				 VALUES (%d, %d, %d, %d, %d)
				 ON DUPLICATE KEY UPDATE
				   total_points   = VALUES(total_points),
				   exact_hits     = VALUES(exact_hits),
				   goal_diff_hits = VALUES(goal_diff_hits),
				   winner_hits    = VALUES(winner_hits)",
				$uid,
				(int) ( $row['total_points']  ?? 0 ),
				(int) ( $row['exact_hits']    ?? 0 ),
				(int) ( $row['goal_diff_hits'] ?? 0 ),
				(int) ( $row['winner_hits']   ?? 0 )
			) );
		}
	}

	/** Recalculate rank_position for all users. Call after batch scoring. */
	public function recalculateRanks(): void {
		global $wpdb;
		$t = $this->table;
		$wpdb->query( 'SET @r := 0' );
		$wpdb->query( "UPDATE {$t} SET rank_position = (@r := @r + 1) ORDER BY total_points DESC, exact_hits DESC" );
	}
}

// ── Scoring Rules ─────────────────────────────────────────────────────────────

class ScoringRuleRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_scoring_rules'; }

	public function getByKey( string $key ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE rule_key = %s LIMIT 1", $key
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/** @return array<string,int> */
	public function getAllAsMap(): array {
		$rows = $this->findBy( [], 'rule_key' );
		$map  = [];
		foreach ( $rows as $row ) {
			$map[ $row['rule_key'] ] = (int) $row['points'];
		}
		return $map;
	}
}

// ── Mini Leagues ──────────────────────────────────────────────────────────────

class MiniLeagueRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_mini_leagues'; }

	public function findByCode( string $code ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE invite_code = %s LIMIT 1", $code
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}
}

class MiniLeagueMemberRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_mini_league_members'; }

	/** @return array<int,array<string,mixed>> */
	public function getLeagueLeaderboard( int $leagueId ): array {
		global $wpdb;
		$m = $this->table;
		$l = $wpdb->prefix . 'wc26_leaderboards';
		$u = $wpdb->users;
		$sql = $wpdb->prepare(
			"SELECT u.display_name, lb.total_points, lb.exact_hits, lb.rank_position
			 FROM {$m} ml
			 JOIN {$u} u ON u.ID = ml.user_id
			 LEFT JOIN {$l} lb ON lb.user_id = ml.user_id
			 WHERE ml.league_id = %d
			 ORDER BY lb.total_points DESC",
			$leagueId
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}
}

// ── Badges ────────────────────────────────────────────────────────────────────

class BadgeRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_badges'; }
}

class UserBadgeRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_user_badges'; }

	public function userHasBadge( int $userId, int $badgeId ): bool {
		$sql = $this->wpdb->prepare(
			"SELECT id FROM {$this->table} WHERE user_id = %d AND badge_id = %d LIMIT 1",
			$userId, $badgeId
		);
		return (bool) $this->wpdb->get_var( $sql );
	}
}

// ── Notifications ─────────────────────────────────────────────────────────────

class NotificationRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_notifications'; }

	/** @return array<int,array<string,mixed>> */
	public function findUnread( int $userId, int $limit = 20 ): array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table}
			 WHERE user_id = %d AND is_read = 0
			 ORDER BY created_at DESC LIMIT %d",
			$userId, $limit
		);
		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	public function markAllRead( int $userId ): void {
		$this->wpdb->update(
			$this->table,
			[ 'is_read' => 1 ],
			[ 'user_id' => $userId ]
		);
	}
}
