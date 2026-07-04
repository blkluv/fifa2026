<?php
/**
 * Leaderboard Service - handles ranking calculations
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\LeaderboardRepository;
use WC26Predictor\Repositories\PredictionRepository;

class LeaderboardService {

	private LeaderboardRepository $leaderboardRepo;
	private PredictionRepository $predictionRepo;

	public function __construct() {
		$this->leaderboardRepo = new LeaderboardRepository();
		$this->predictionRepo  = new PredictionRepository();
	}

	/**
	 * Get global leaderboard
	 */
	public function getGlobal( int $limit = 100 ): array {
		return $this->leaderboardRepo->getGlobalTop( $limit );
	}

	/**
	 * Get region-specific leaderboard
	 */
	public function getRegion( int $regionId, int $limit = 100 ): array {
		return $this->leaderboardRepo->getRegionTop( $regionId, $limit );
	}

	/**
	 * Get user summary
	 */
	public function getUserSummary( int $userId ): array {
		$predictions = $this->predictionRepo->findByUser( $userId );
		$total = count( $predictions );
		$scored = array_filter( $predictions, fn( $p ) => $p['earned_points'] > 0 );
		$totalPoints = array_sum( array_column( $predictions, 'earned_points' ) );

		return [
			'user_id'         => $userId,
			'total_points'    => $totalPoints,
			'total_predictions' => $total,
			'correct_predictions' => count( $scored ),
			'accuracy'        => $total > 0 ? round( ( count( $scored ) / $total ) * 100, 1 ) : 0,
			'rank'            => $this->getUserRank( $userId ),
		];
	}

	private function getUserRank( int $userId ): ?int {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_leaderboards';
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT rank_position FROM {$t} WHERE user_id = %d",
			$userId
		), ARRAY_A );
		return $row ? (int) $row['rank_position'] : null;
	}
}
