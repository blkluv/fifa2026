<?php
/**
 * Prediction Service - handles user predictions
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\PredictionRepository;
use WC26Predictor\Repositories\MarketRepository;
use WC26Predictor\Repositories\LeaderboardRepository;
use WC26Predictor\Repositories\StandingsRepository;

class PredictionService {

	private PredictionRepository $predictionRepo;
	private MarketRepository $marketRepo;
	private LeaderboardRepository $leaderboardRepo;
	private StandingsRepository $standingsRepo;
	private ScoringService $scoringService;

	public function __construct( ScoringService $scoringService ) {
		$this->predictionRepo   = new PredictionRepository();
		$this->marketRepo       = new MarketRepository();
		$this->leaderboardRepo  = new LeaderboardRepository();
		$this->standingsRepo    = new StandingsRepository();
		$this->scoringService   = $scoringService;
	}

	/**
	 * Submit a prediction
	 */
	public function submit( int $userId, int $marketId, ?float $predictedPrice, string $predictedTrend, ?string $predictedRange = null, bool $isJoker = false ): array {
		// Check if market exists and is active
		$market = $this->marketRepo->find( $marketId );
		if ( ! $market ) {
			throw new \RuntimeException( __( 'Market not found.', 'wc26-predictor' ) );
		}

		if ( $market['status'] === 'settled' ) {
			throw new \RuntimeException( __( 'This market is already settled.', 'wc26-predictor' ) );
		}

		if ( $this->isLocked( $market ) ) {
			throw new \RuntimeException( __( 'Predictions are locked for this market.', 'wc26-predictor' ) );
		}

		// Validate trend
		$validTrends = [ 'increase', 'decrease', 'stable', 'volatile' ];
		if ( ! in_array( $predictedTrend, $validTrends, true ) ) {
			throw new \RuntimeException( __( 'Invalid trend prediction.', 'wc26-predictor' ) );
		}

		// Save prediction
		$id = $this->predictionRepo->upsert( $userId, $marketId, $predictedPrice, $predictedTrend, $predictedRange, $isJoker );

		// Ensure user exists in leaderboard
		$this->leaderboardRepo->ensureUser( $userId );

		return [
			'id'              => $id,
			'user_id'         => $userId,
			'market_id'       => $marketId,
			'predicted_price' => $predictedPrice,
			'predicted_trend' => $predictedTrend,
			'predicted_range' => $predictedRange,
			'is_joker'        => $isJoker,
		];
	}

	/**
	 * Get user predictions with market details
	 */
	public function getUserPredictions( int $userId ): array {
		return $this->predictionRepo->findByUserWithMarkets( $userId );
	}

	/**
	 * Get market predictions
	 */
	public function getMarketPredictions( int $marketId ): array {
		return $this->predictionRepo->findByMarket( $marketId );
	}

	/**
	 * Check if predictions are locked for a market
	 */
	public function isLocked( array $market ): bool {
		$lockSeconds = apply_filters( 'wc26_prediction_lock_seconds', 60 );
		$forecastTime = strtotime( $market['forecast_date'] );
		return ( time() + $lockSeconds ) > $forecastTime;
	}

	/**
	 * Score a market (called when market is settled)
	 */
	public function scoreMarket( int $marketId ): void {
		$market = $this->marketRepo->find( $marketId );
		if ( ! $market || $market['status'] !== 'settled' ) {
			return;
		}

		$predictions = $this->predictionRepo->findByMarket( $marketId );
		if ( empty( $predictions ) ) {
			return;
		}

		foreach ( $predictions as $prediction ) {
			$points = $this->scoringService->calculatePoints( $prediction, $market );
			if ( $points > 0 ) {
				$this->predictionRepo->update( [
					'earned_points'  => $points,
					'prediction_type' => $this->scoringService->getPredictionType( $prediction, $market ),
				], [ 'id' => $prediction['id'] ] );

				// Update leaderboards
				$this->leaderboardRepo->upsertUser(
					$prediction['user_id'],
					$market['region_id'],
					$points,
					$this->scoringService->getPredictionType( $prediction, $market )
				);

				// Update standings
				$this->standingsRepo->upsertUser(
					$market['region_id'],
					$prediction['user_id'],
					$points,
					$this->scoringService->getPredictionType( $prediction, $market )
				);
			}
		}

		// Recalculate ranks
		$this->leaderboardRepo->recalculateRanks( $market['region_id'] );
	}
}
