<?php
/**
 * Concrete repository classes for every custom table.
 *
 * Adapted for Real Estate Prediction Market with Chainlink CRE Integration
 *
 * @package WC26Predictor\Repositories
 */

declare(strict_types=1);

namespace WC26Predictor\Repositories;

// ── Properties (replaces Teams) ─────────────────────────────────────────────

class PropertyRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_properties'; }

	/** @return array<int,array<string,mixed>> */
	public function findByRegion( int $regionId ): array {
		return $this->findBy( [ 'region_id' => $regionId ], 'name' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findByType( string $type ): array {
		return $this->findBy( [ 'property_type' => $type ], 'name' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findByCity( string $city ): array {
		return $this->findBy( [ 'city' => $city ], 'name' );
	}
}

// ── Regions (replaces Groups) ───────────────────────────────────────────────

class RegionRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_regions'; }

	/** @return array<int,array<string,mixed>> */
	public function findByCountry( string $country ): array {
		return $this->findBy( [ 'country' => $country ], 'name' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findByState( string $state ): array {
		return $this->findBy( [ 'state' => $state ], 'name' );
	}
}

// ── Markets (replaces Matches) ──────────────────────────────────────────────

class MarketRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_markets'; }

	/** @return array<int,array<string,mixed>> */
	public function findActive( int $limit = 10 ): array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table}
			 WHERE status = 'active' AND forecast_date > NOW()
			 ORDER BY forecast_date ASC
			 LIMIT %d",
			$limit
		);
		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/** @return array<int,array<string,mixed>> */
	public function findPending(): array {
		return $this->findBy( [ 'status' => 'pending' ], 'forecast_date' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findSettled(): array {
		return $this->findBy( [ 'status' => 'settled' ], 'forecast_date', 'DESC' );
	}

	public function findWithDetails( int $marketId ): ?array {
		global $wpdb;
		$m  = $this->table;
		$p  = $wpdb->prefix . 'wc26_properties';
		$r  = $wpdb->prefix . 'wc26_regions';
		$sql = $wpdb->prepare(
			"SELECT m.*,
			        p.name AS property_name, p.property_type, p.city,
			        r.name AS region_name, r.country, r.state
			 FROM {$m} m
			 LEFT JOIN {$p} p ON p.id = m.property_id
			 LEFT JOIN {$r} r ON r.id = m.region_id
			 WHERE m.id = %d LIMIT 1",
			$marketId
		);
		$row = $wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public function findAllWithDetails(): array {
		global $wpdb;
		$m = $this->table;
		$p = $wpdb->prefix . 'wc26_properties';
		$r = $wpdb->prefix . 'wc26_regions';
		$sql = "SELECT m.*,
		               p.name AS property_name, p.property_type, p.city,
		               r.name AS region_name, r.country, r.state
		        FROM {$m} m
		        LEFT JOIN {$p} p ON p.id = m.property_id
		        LEFT JOIN {$r} r ON r.id = m.region_id
		        ORDER BY m.forecast_date ASC";
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/** @return array<int,array<string,mixed>> */
	public function findByRegion( int $regionId ): array {
		return $this->findBy( [ 'region_id' => $regionId ], 'forecast_date' );
	}
}

// ── Predictions (adapted for real estate) ────────────────────────────────────

class PredictionRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_predictions'; }

	public function findByUserAndMarket( int $userId, int $marketId ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE user_id = %d AND market_id = %d LIMIT 1",
			$userId, $marketId
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public function findByUser( int $userId ): array {
		return $this->findBy( [ 'user_id' => $userId ], 'created_at', 'DESC' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findByUserWithMarkets( int $userId ): array {
		global $wpdb;
		$p  = $this->table;
		$m  = $wpdb->prefix . 'wc26_markets';
		$pr = $wpdb->prefix . 'wc26_properties';
		$r  = $wpdb->prefix . 'wc26_regions';
		$sql = $wpdb->prepare(
			"SELECT p.*,
			        m.forecast_date, m.initial_price, m.final_price, m.price_change_pct, m.market_trend,
			        m.status        AS market_status,
			        pr.name         AS property_name,
			        pr.property_type,
			        pr.city,
			        r.name          AS region_name,
			        r.country
			 FROM {$p} p
			 JOIN {$m} m ON m.id = p.market_id
			 LEFT JOIN {$pr} pr ON pr.id = m.property_id
			 LEFT JOIN {$r} r ON r.id = m.region_id
			 WHERE p.user_id = %d
			 ORDER BY m.forecast_date DESC",
			$userId
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/** @return array<int,array<string,mixed>> */
	public function findByMarket( int $marketId ): array {
		return $this->findBy( [ 'market_id' => $marketId ] );
	}

	/** @return array<int,array<string,mixed>> */
	public function getMarketStats( int $marketId ): array {
		global $wpdb;
		$p = $this->table;
		$sql = $wpdb->prepare(
			"SELECT
				COUNT(*) AS total_predictions,
				AVG(predicted_price) AS avg_predicted_price,
				MIN(predicted_price) AS min_price,
				MAX(predicted_price) AS max_price,
				SUM(CASE WHEN predicted_trend = 'increase' THEN 1 ELSE 0 END) AS trend_up,
				SUM(CASE WHEN predicted_trend = 'decrease' THEN 1 ELSE 0 END) AS trend_down,
				SUM(CASE WHEN predicted_trend = 'stable' THEN 1 ELSE 0 END) AS trend_stable
			FROM {$p}
			WHERE market_id = %d",
			$marketId
		);
		return $wpdb->get_row( $sql, ARRAY_A ) ?: [];
	}

	public function upsert( int $userId, int $marketId, ?float $predictedPrice, string $predictedTrend, ?string $predictedRange = null, bool $isJoker = false ): int {
		$existing = $this->findByUserAndMarket( $userId, $marketId );

		$data = [
			'predicted_price' => $predictedPrice,
			'predicted_trend' => $predictedTrend,
			'predicted_range' => $predictedRange,
			'is_joker'        => (int) $isJoker,
		];

		if ( $existing ) {
			$this->update( $data, [ 'id' => $existing['id'] ] );
			return (int) $existing['id'];
		}

		$data['user_id'] = $userId;
		$data['market_id'] = $marketId;
		return $this->insert( $data );
	}
}

// ── Standings (adapted for real estate) ──────────────────────────────────────

class StandingsRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_standings'; }

	/** @return array<int,array<string,mixed>> */
	public function findByRegion( int $regionId ): array {
		global $wpdb;
		$s = $this->table;
		$u = $wpdb->users;
		$sql = $wpdb->prepare(
			"SELECT st.*, u.display_name, u.user_login
			 FROM {$s} st
			 JOIN {$u} u ON u.ID = st.user_id
			 WHERE st.region_id = %d
			 ORDER BY st.total_points DESC, st.exact_hits DESC",
			$regionId
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	public function upsertUser( int $regionId, int $userId, int $pointsDelta, string $type ): void {
		global $wpdb;
		$t = $this->table;

		$exactDelta = ( $type === 'exact' ) ? 1 : 0;
		$trendDelta = ( $type === 'trend' ) ? 1 : 0;
		$rangeDelta = ( $type === 'range' ) ? 1 : 0;

		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$t} (region_id, user_id, total_points, exact_hits, trend_hits, range_hits, total_predictions)
			 VALUES (%d, %d, %d, %d, %d, %d, 1)
			 ON DUPLICATE KEY UPDATE
			   total_points      = total_points + %d,
			   exact_hits        = exact_hits + %d,
			   trend_hits        = trend_hits + %d,
			   range_hits        = range_hits + %d,
			   total_predictions = total_predictions + 1",
			$regionId, $userId, $pointsDelta, $exactDelta, $trendDelta, $rangeDelta,
			$pointsDelta, $exactDelta, $trendDelta, $rangeDelta
		) );
	}

	public function rebuildForRegion( int $regionId ): void {
		global $wpdb;
		$t  = $this->table;
		$tP = $wpdb->prefix . 'wc26_predictions';
		$tM = $wpdb->prefix . 'wc26_markets';

		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$t} WHERE region_id = %d",
			$regionId
		) );

		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$t} (region_id, user_id, total_points, exact_hits, trend_hits, range_hits, total_predictions)
			 SELECT
			   %d AS region_id,
			   p.user_id,
			   COALESCE(SUM(p.earned_points), 0) AS total_points,
			   SUM(CASE WHEN p.prediction_type = 'exact' THEN 1 ELSE 0 END) AS exact_hits,
			   SUM(CASE WHEN p.prediction_type = 'trend' THEN 1 ELSE 0 END) AS trend_hits,
			   SUM(CASE WHEN p.prediction_type = 'range' THEN 1 ELSE 0 END) AS range_hits,
			   COUNT(p.id) AS total_predictions
			 FROM {$tP} p
			 JOIN {$tM} m ON m.id = p.market_id
			 WHERE m.region_id = %d AND m.status = 'settled'
			 GROUP BY p.user_id",
			$regionId, $regionId
		) );
	}
}

// ── Leaderboards (adapted for real estate) ──────────────────────────────────

class LeaderboardRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_leaderboards'; }

	public function ensureUser( int $userId ): void {
		global $wpdb;
		$t = $this->table;
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$t} (user_id, total_points, exact_hits, trend_hits, range_hits)
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

	/** @return array<int,array<string,mixed>> */
	public function getRegionTop( int $regionId, int $limit = 100 ): array {
		global $wpdb;
		$l = $this->table;
		$sql = $wpdb->prepare(
			"SELECT lb.*, u.display_name
			 FROM {$l} lb
			 JOIN {$wpdb->users} u ON u.ID = lb.user_id
			 WHERE lb.region_id = %d
			 ORDER BY lb.total_points DESC, lb.exact_hits DESC
			 LIMIT %d",
			$regionId, $limit
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	public function upsertUser( int $userId, int $regionId, int $pointsDelta, string $type ): void {
		global $wpdb;
		$t = $this->table;

		$exactDelta = ( $type === 'exact' ) ? 1 : 0;
		$trendDelta = ( $type === 'trend' ) ? 1 : 0;
		$rangeDelta = ( $type === 'range' ) ? 1 : 0;

		$wpdb->query( $wpdb->prepare(
			"INSERT INTO {$t} (user_id, region_id, total_points, exact_hits, trend_hits, range_hits)
			 VALUES (%d, %d, %d, %d, %d, %d)
			 ON DUPLICATE KEY UPDATE
			   total_points = total_points + %d,
			   exact_hits   = exact_hits + %d,
			   trend_hits   = trend_hits + %d,
			   range_hits   = range_hits + %d",
			$userId, $regionId, $pointsDelta, $exactDelta, $trendDelta, $rangeDelta,
			$pointsDelta, $exactDelta, $trendDelta, $rangeDelta
		) );
	}

	public function rebuildForUsers( array $userIds ): void {
		global $wpdb;
		if ( empty( $userIds ) ) {
			return;
		}

		$t  = $this->table;
		$tP = $wpdb->prefix . 'wc26_predictions';
		$tM = $wpdb->prefix . 'wc26_markets';

		foreach ( $userIds as $uid ) {
			$uid = (int) $uid;
			$row = $wpdb->get_row( $wpdb->prepare(
				"SELECT
					COALESCE(SUM(p.earned_points), 0) AS total_points,
					SUM(CASE WHEN p.prediction_type = 'exact' THEN 1 ELSE 0 END) AS exact_hits,
					SUM(CASE WHEN p.prediction_type = 'trend' THEN 1 ELSE 0 END) AS trend_hits,
					SUM(CASE WHEN p.prediction_type = 'range' THEN 1 ELSE 0 END) AS range_hits
				 FROM {$tP} p
				 JOIN {$tM} m ON m.id = p.market_id
				 WHERE p.user_id = %d AND m.status = 'settled'",
				$uid
			), ARRAY_A );

			if ( $row ) {
				$wpdb->query( $wpdb->prepare(
					"INSERT INTO {$t} (user_id, total_points, exact_hits, trend_hits, range_hits)
					 VALUES (%d, %d, %d, %d, %d)
					 ON DUPLICATE KEY UPDATE
					   total_points = VALUES(total_points),
					   exact_hits   = VALUES(exact_hits),
					   trend_hits   = VALUES(trend_hits),
					   range_hits   = VALUES(range_hits)",
					$uid,
					(int) ( $row['total_points'] ?? 0 ),
					(int) ( $row['exact_hits'] ?? 0 ),
					(int) ( $row['trend_hits'] ?? 0 ),
					(int) ( $row['range_hits'] ?? 0 )
				) );
			}
		}
	}

	public function recalculateRanks( ?int $regionId = null ): void {
		global $wpdb;
		$t = $this->table;
		if ( $regionId ) {
			$wpdb->query( 'SET @r := 0' );
			$wpdb->query( $wpdb->prepare(
				"UPDATE {$t} SET rank_position = (@r := @r + 1)
				 WHERE region_id = %d OR region_id IS NULL
				 ORDER BY total_points DESC, exact_hits DESC",
				$regionId
			) );
		} else {
			$wpdb->query( 'SET @r := 0' );
			$wpdb->query( "UPDATE {$t} SET rank_position = (@r := @r + 1) ORDER BY total_points DESC, exact_hits DESC" );
		}
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
		$rows = $this->findBy( [ 'is_active' => 1 ], 'rule_key' );
		$map  = [];
		foreach ( $rows as $row ) {
			$map[ $row['rule_key'] ] = (int) $row['points'];
		}
		return $map;
	}
}

// ── Mini Leagues (adapted for real estate) ──────────────────────────────────

class MiniLeagueRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_mini_leagues'; }

	public function findByCode( string $code ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE invite_code = %s LIMIT 1", $code
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public function findByRegion( int $regionId ): array {
		return $this->findBy( [ 'region_id' => $regionId ], 'name' );
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
			"SELECT u.display_name, lb.total_points, lb.exact_hits, lb.trend_hits, lb.range_hits, lb.rank_position
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

	/** @return array<int,array<string,mixed>> */
	public function getUserBadges( int $userId ): array {
		return $this->findBy( [ 'user_id' => $userId ], 'earned_at', 'DESC' );
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

// ── Chainlink CRE Reports (NEW) ─────────────────────────────────────────────

class ChainlinkReportRepository extends AbstractRepository {
	protected function getTableName(): string { return 'wc26_chainlink_reports'; }

	public function create( int $marketId, string $donId, array $reportData, ?string $signature = null ): int {
		return $this->insert( [
			'market_id'   => $marketId,
			'don_id'      => $donId,
			'report_data' => wp_json_encode( $reportData ),
			'signature'   => $signature,
			'status'      => 'pending',
		] );
	}

	public function updateStatus( int $reportId, string $status, ?string $txHash = null, ?string $error = null ): void {
		$data = [ 'status' => $status ];
		if ( $txHash ) {
			$data['transaction_hash'] = $txHash;
			$data['submitted_at'] = current_time( 'mysql' );
		}
		if ( $error ) {
			$data['error_message'] = $error;
		}
		if ( $status === 'confirmed' ) {
			$data['confirmed_at'] = current_time( 'mysql' );
		}
		$this->update( $data, [ 'id' => $reportId ] );
	}

	/** @return array<int,array<string,mixed>> */
	public function findPending(): array {
		return $this->findBy( [ 'status' => 'pending' ], 'created_at' );
	}

	/** @return array<int,array<string,mixed>> */
	public function findByMarket( int $marketId ): array {
		return $this->findBy( [ 'market_id' => $marketId ], 'created_at', 'DESC' );
	}

	/** @return array<string,mixed>|null */
	public function getLatestForMarket( int $marketId ): ?array {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table} WHERE market_id = %d ORDER BY created_at DESC LIMIT 1",
			$marketId
		);
		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return $row ?: null;
	}
}
