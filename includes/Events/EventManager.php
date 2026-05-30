<?php
/**
 * EventManager — wires WordPress actions to service methods.
 *
 * @package WC26Predictor\Events
 */

declare(strict_types=1);

namespace WC26Predictor\Events;

use WC26Predictor\Plugin;

class EventManager {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function init(): void {
		// When a match finishes → score predictions + update standings
		add_action( 'wc26_match_finished', [ $this, 'onMatchFinished' ], 10, 3 );

		// When predictions are scored → check badges
		add_action( 'wc26_match_scored', [ $this, 'onMatchScored' ] );

		// When a prediction is submitted → notify "locked soon" scheduling
		add_action( 'wc26_prediction_submitted', [ $this, 'onPredictionSubmitted' ], 10, 3 );
	}

	public function onMatchFinished( int $matchId, int $homeScore, int $awayScore ): void {
		/** @var \WC26Predictor\Services\PredictionService $predService */
		$predService = $this->plugin->make( 'prediction_service' );
		$predService->scoreMatch( $matchId );

		/** @var \WC26Predictor\Services\StandingsService $standingsService */
		$standingsService = $this->plugin->make( 'standings_service' );
		$standingsService->recalculateForMatch( $matchId );

		/** @var \WC26Predictor\Services\NotificationService $notifService */
		$notifService = $this->plugin->make( 'notification_service' );

		// Notify all predictors of this match
		global $wpdb;
		$t       = $wpdb->prefix . 'wc26_predictions';
		$userIds = $wpdb->get_col( $wpdb->prepare(
			"SELECT user_id FROM {$t} WHERE match_id = %d", $matchId
		) );

		if ( $userIds ) {
			$notifService->sendBulk(
				array_map( 'intval', $userIds ),
				'leaderboard_updated',
				__( 'جدول امتیازات به‌روزرسانی شد', 'wc26-predictor' ),
				__( 'یک مسابقه به پایان رسید و امتیازهای شما محاسبه شد. رتبه‌تان را بررسی کنید.', 'wc26-predictor' )
			);
		}
	}

	public function onMatchScored( int $matchId ): void {
		// Evaluate badges for every user who predicted this match
		global $wpdb;
		$t       = $wpdb->prefix . 'wc26_predictions';
		$userIds = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT user_id FROM {$t} WHERE match_id = %d", $matchId
		) );

		/** @var \WC26Predictor\Services\BadgeService $badgeService */
		$badgeService = $this->plugin->make( 'badge_service' );

		foreach ( $userIds as $uid ) {
			$badgeService->evaluate( (int) $uid );
		}
	}

	public function onPredictionSubmitted( int $predId, int $userId, int $matchId ): void {
		// Could schedule a WP cron "kickoff_soon" reminder here
		// Placeholder for future WP-Cron integration
	}
}
