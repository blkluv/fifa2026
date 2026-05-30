<?php
/**
 * PredictionService — handles submission, locking and scoring of predictions.
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\{MatchRepository, PredictionRepository, LeaderboardRepository};

class PredictionService {

	private PredictionRepository $predictions;
	private MatchRepository      $matches;
	private LeaderboardRepository $leaderboards;
	private ScoringService       $scoring;

	public function __construct( ScoringService $scoring ) {
		$this->predictions  = new PredictionRepository();
		$this->matches      = new MatchRepository();
		$this->leaderboards = new LeaderboardRepository();
		$this->scoring      = $scoring;
	}

	/**
	 * Submit or update a prediction.
	 *
	 * @throws \RuntimeException on validation failure.
	 */
	public function submit(
		int $userId,
		int $matchId,
		int $homeScore,
		int $awayScore,
		bool $isJoker = false
	): array {
		// 1. Validate the match exists and is not locked
		$match = $this->matches->find( $matchId );

		if ( ! $match ) {
			throw new \RuntimeException( __( 'مسابقه پیدا نشد.', 'wc26-predictor' ) );
		}

		if ( $this->isLocked( $match ) ) {
			throw new \RuntimeException( __( 'زمان ثبت/ویرایش پیش‌بینی برای این مسابقه به پایان رسیده است.', 'wc26-predictor' ) );
		}

		if ( $match['status'] !== 'scheduled' ) {
			throw new \RuntimeException( __( 'این مسابقه دیگر پذیرش پیش‌بینی ندارد.', 'wc26-predictor' ) );
		}

		// 2. Sanitize scores
		$homeScore = max( 0, min( 99, $homeScore ) );
		$awayScore = max( 0, min( 99, $awayScore ) );

		// 3. Joker: check user hasn't used it already (one per tournament)
		if ( $isJoker ) {
			$this->validateJoker( $userId, $matchId );
		}

		// 4. Persist
		$this->leaderboards->ensureUser( $userId );
		$predId = $this->predictions->upsert( $userId, $matchId, $homeScore, $awayScore, $isJoker );
		wp_cache_delete( 'wc26_global_leaderboard', 'wc26' );

		do_action( 'wc26_prediction_submitted', $predId, $userId, $matchId );

		return [
			'id'         => $predId,
			'match_id'   => $matchId,
			'home_score' => $homeScore,
			'away_score' => $awayScore,
			'is_joker'   => $isJoker,
		];
	}

	/**
	 * Score all predictions for a finished match.
	 * Called by MatchService after a result is submitted.
	 */
	public function scoreMatch( int $matchId ): void {
		$match = $this->matches->find( $matchId );

		if ( ! $match || $match['status'] !== 'finished' ) {
			return;
		}

		$realHome = (int) $match['home_score'];
		$realAway = (int) $match['away_score'];

		$preds = $this->predictions->findByMatch( $matchId );

		$affectedUsers = [];

		foreach ( $preds as $pred ) {
			$result = $this->scoring->calculate(
				(int) $pred['pred_home_score'],
				(int) $pred['pred_away_score'],
				$realHome,
				$realAway,
				(bool) $pred['is_joker']
			);

			// Update prediction record
			$this->predictions->update(
				[
					'earned_points'   => $result['points'],
					'prediction_type' => $result['type'],
				],
				[ 'id' => $pred['id'] ]
			);

			$affectedUsers[] = (int) $pred['user_id'];
		}

		// Rebuild leaderboard rows from predictions (authoritative, never drifts).
		if ( $affectedUsers ) {
			$this->leaderboards->rebuildForUsers( array_unique( $affectedUsers ) );
		}

		// Recalculate global ranks after all predictions scored
		$this->leaderboards->recalculateRanks();

		// Bust the leaderboard cache
		wp_cache_delete( 'wc26_global_leaderboard', 'wc26' );

		do_action( 'wc26_match_scored', $matchId );
	}

	/**
	 * Check if a match is locked (within 1 minute of kickoff or later).
	 *
	 * @param array<string,mixed> $match
	 */
	public function isLocked( array $match ): bool {
		$kickoffAt = (string) ( $match['kickoff_at'] ?? '' );
		if ( $kickoffAt === '' ) {
			return true;
		}

		$tz = wp_timezone();
		$dt = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $kickoffAt, $tz );
		if ( ! $dt ) {
			$dt = new \DateTimeImmutable( $kickoffAt, $tz );
		}

		$kickoffTs  = $dt->getTimestamp();
		$nowTs      = (int) current_time( 'timestamp' );
		$lockBefore = (int) apply_filters( 'wc26_prediction_lock_seconds', (int) MINUTE_IN_SECONDS );

		return $nowTs >= ( $kickoffTs - $lockBefore );
	}

	/** @throws \RuntimeException */
	private function validateJoker( int $userId, int $currentMatchId ): void {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_predictions';

		$jokerUsed = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$t} WHERE user_id = %d AND is_joker = 1 AND match_id != %d LIMIT 1",
			$userId, $currentMatchId
		) );

		if ( $jokerUsed ) {
			throw new \RuntimeException( __( 'شما قبلاً جوکر خود را در این تورنمنت استفاده کرده‌اید.', 'wc26-predictor' ) );
		}
	}

	/** @return array<int,array<string,mixed>> */
	public function getUserPredictions( int $userId ): array {
		return $this->predictions->findByUserWithMatches( $userId );
	}

	public function getUserPredictionForMatch( int $userId, int $matchId ): ?array {
		return $this->predictions->findByUserAndMatch( $userId, $matchId );
	}
}
