<?php
/**
 * Badge Service - awards badges based on user achievements
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\BadgeRepository;
use WC26Predictor\Repositories\UserBadgeRepository;
use WC26Predictor\Repositories\PredictionRepository;

class BadgeService {

	private BadgeRepository $badgeRepo;
	private UserBadgeRepository $userBadgeRepo;
	private PredictionRepository $predictionRepo;

	public function __construct() {
		$this->badgeRepo = new BadgeRepository();
		$this->userBadgeRepo = new UserBadgeRepository();
		$this->predictionRepo = new PredictionRepository();
	}

	/**
	 * Seed default badges
	 */
	public static function seed(): void {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_badges';

		$badges = [
			[ 'first-prediction', 'First Prediction', 'Made your first prediction', '🎯', 'predictions', 1 ],
			[ 'perfect-predictor', 'Perfect Predictor', 'Achieved 10 exact price predictions', '🏆', 'exact_hits', 10 ],
			[ 'trend-master', 'Trend Master', 'Predicted market trends correctly 20 times', '📈', 'trend_hits', 20 ],
			[ 'real-estate-guru', 'Real Estate Guru', 'Achieved 100 correct predictions', '🏠', 'total_correct', 100 ],
			[ 'chainlink-pioneer', 'Chainlink Pioneer', 'Participated in Chainlink CRE settlement', '🔗', 'chainlink_reports', 1 ],
			[ 'arbitrage-finder', 'Arbitrage Finder', 'Identified 10 cross-market arbitrage opportunities', '💡', 'arbitrage_hits', 10 ],
		];

		foreach ( $badges as $badge ) {
			$wpdb->replace(
				$t,
				[
					'slug'        => $badge[0],
					'name'        => $badge[1],
					'description' => $badge[2],
					'icon_url'    => $badge[3],
					'criteria'    => $badge[4],
					'threshold'   => $badge[5],
				],
				[ '%s', '%s', '%s', '%s', '%s', '%d' ]
			);
		}
	}

	/**
	 * Check and award badges for a user
	 */
	public function checkAndAward( int $userId ): void {
		$stats = $this->getUserStats( $userId );
		$allBadges = $this->badgeRepo->findAll();

		foreach ( $allBadges as $badge ) {
			if ( $this->userBadgeRepo->userHasBadge( $userId, (int) $badge['id'] ) ) {
				continue;
			}

			if ( $this->meetsCriteria( $stats, $badge ) ) {
				$this->awardBadge( $userId, (int) $badge['id'] );
			}
		}
	}

	/**
	 * Award a badge to a user
	 */
	public function awardBadge( int $userId, int $badgeId ): void {
		if ( $this->userBadgeRepo->userHasBadge( $userId, $badgeId ) ) {
			return;
		}

		$this->userBadgeRepo->insert( [
			'user_id'  => $userId,
			'badge_id' => $badgeId,
		] );

		$badge = $this->badgeRepo->find( $badgeId );
		if ( $badge ) {
			do_action( 'wc26_user_badge_earned', $userId, $badgeId, $badge['slug'] );
		}
	}

	/**
	 * Get user badge stats
	 */
	private function getUserStats( int $userId ): array {
		$predictions = $this->predictionRepo->findByUser( $userId );
		$totalPredictions = count( $predictions );
		$exactHits = array_filter( $predictions, fn( $p ) => $p['prediction_type'] === 'exact' );
		$trendHits = array_filter( $predictions, fn( $p ) => $p['prediction_type'] === 'trend' );
		$totalCorrect = array_filter( $predictions, fn( $p ) => $p['earned_points'] > 0 );
		$chainlinkReports = 0; // Will be fetched from Chainlink report table

		return [
			'total_predictions' => $totalPredictions,
			'exact_hits' => count( $exactHits ),
			'trend_hits' => count( $trendHits ),
			'total_correct' => count( $totalCorrect ),
			'chainlink_reports' => $chainlinkReports,
		];
	}

	/**
	 * Check if user meets badge criteria
	 */
	private function meetsCriteria( array $stats, array $badge ): bool {
		$criteria = $badge['criteria'];
		$threshold = (int) $badge['threshold'];

		return isset( $stats[ $criteria ] ) && $stats[ $criteria ] >= $threshold;
	}

	/**
	 * Get user badges with progress
	 */
	public function getUserBadgesWithProgress( int $userId ): array {
		$stats = $this->getUserStats( $userId );
		$allBadges = $this->badgeRepo->findAll();
		$userBadges = $this->userBadgeRepo->findBy( [ 'user_id' => $userId ] );
		$userBadgeIds = array_column( $userBadges, 'badge_id' );

		$result = [];
		foreach ( $allBadges as $badge ) {
			$badgeId = (int) $badge['id'];
			$criteria = $badge['criteria'];
			$threshold = (int) $badge['threshold'];
			$current = $stats[ $criteria ] ?? 0;

			$result[] = [
				'id' => $badgeId,
				'slug' => $badge['slug'],
				'name' => $badge['name'],
				'description' => $badge['description'],
				'icon_url' => $badge['icon_url'],
				'earned' => in_array( $badgeId, $userBadgeIds ),
				'progress' => min( 100, ( $current / $threshold ) * 100 ),
				'current' => $current,
				'threshold' => $threshold,
			];
		}

		return $result;
	}
}
