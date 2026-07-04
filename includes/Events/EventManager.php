<?php
/**
 * Event Manager - Event-driven architecture for Chainlink CRE
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
		// Market lifecycle events
		add_action( 'wc26_market_created', [ $this, 'onMarketCreated' ], 10, 2 );
		add_action( 'wc26_market_active', [ $this, 'onMarketActive' ], 10, 2 );
		add_action( 'wc26_market_settled', [ $this, 'onMarketSettled' ], 10, 3 );
		add_action( 'wc26_market_cancelled', [ $this, 'onMarketCancelled' ], 10, 2 );

		// Prediction events
		add_action( 'wc26_prediction_submitted', [ $this, 'onPredictionSubmitted' ], 10, 3 );
		add_action( 'wc26_prediction_scored', [ $this, 'onPredictionScored' ], 10, 4 );

		// Chainlink CRE events
		add_action( 'wc26_chainlink_report_requested', [ $this, 'onChainlinkReportRequested' ], 10, 3 );
		add_action( 'wc26_chainlink_report_submitted', [ $this, 'onChainlinkReportSubmitted' ], 10, 2 );
		add_action( 'wc26_chainlink_report_confirmed', [ $this, 'onChainlinkReportConfirmed' ], 10, 2 );
		add_action( 'wc26_chainlink_report_failed', [ $this, 'onChainlinkReportFailed' ], 10, 3 );

		// User events
		add_action( 'wc26_user_joined', [ $this, 'onUserJoined' ], 10, 2 );
		add_action( 'wc26_user_badge_earned', [ $this, 'onUserBadgeEarned' ], 10, 3 );
	}

	/**
	 * Market Events
	 */
	public function onMarketCreated( int $marketId, array $marketData ): void {
		// Log market creation
		$log = sprintf( 'Market %d created: %s', $marketId, wp_json_encode( $marketData ) );
		do_action( 'wc26_log', 'info', $log );

		// Trigger notifications
		do_action( 'wc26_notify_admins', 'market_created', $marketId );
	}

	public function onMarketActive( int $marketId, array $marketData ): void {
		$log = sprintf( 'Market %d is now active', $marketId );
		do_action( 'wc26_log', 'info', $log );

		// Send notifications to users following this region
		do_action( 'wc26_notify_followers', 'market_active', $marketId );
	}

	public function onMarketSettled( int $marketId, string $outcome, float $confidence ): void {
		$log = sprintf( 'Market %d settled with outcome: %s (confidence: %.2f%%)', $marketId, $outcome, $confidence );
		do_action( 'wc26_log', 'info', $log );

		// Auto-submit Chainlink report if enabled
		if ( get_option( 'wc26_auto_chainlink_report', 1 ) ) {
			$donId = get_option( 'wc26_chainlink_don_id', '' );
			if ( $donId ) {
				do_action( 'wc26_chainlink_report_requested', $marketId, $outcome, $confidence );
			}
		}

		// Award badges to users with top predictions
		do_action( 'wc26_award_settlement_badges', $marketId );
	}

	public function onMarketCancelled( int $marketId, array $marketData ): void {
		$log = sprintf( 'Market %d was cancelled', $marketId );
		do_action( 'wc26_log', 'info', $log );
	}

	/**
	 * Prediction Events
	 */
	public function onPredictionSubmitted( int $predictionId, int $userId, array $predictionData ): void {
		$log = sprintf( 'User %d submitted prediction %d', $userId, $predictionId );
		do_action( 'wc26_log', 'debug', $log );

		// Update user stats
		do_action( 'wc26_update_user_stats', $userId );
	}

	public function onPredictionScored( int $predictionId, int $userId, int $points, string $type ): void {
		$log = sprintf( 'User %d earned %d points for prediction %d (%s)', $userId, $points, $predictionId, $type );
		do_action( 'wc26_log', 'info', $log );

		// Check for badge eligibility
		do_action( 'wc26_check_badges', $userId );
	}

	/**
	 * Chainlink CRE Events
	 */
	public function onChainlinkReportRequested( int $marketId, string $outcome, float $confidence ): void {
		/** @var \WC26Predictor\Services\ChainlinkService $svc */
		$svc = $this->plugin->make( 'chainlink_service' );
		$donId = get_option( 'wc26_chainlink_don_id', '' );

		if ( ! $donId ) {
			do_action( 'wc26_log', 'error', 'Chainlink report requested but no DON ID configured' );
			return;
		}

		$reportId = $svc->createReport( $marketId, $donId, [
			'market_id'  => $marketId,
			'outcome'    => $outcome,
			'confidence' => $confidence,
			'timestamp'  => current_time( 'mysql' ),
		] );

		do_action( 'wc26_log', 'info', sprintf( 'Chainlink report %d created for market %d', $reportId, $marketId ) );
	}

	public function onChainlinkReportSubmitted( int $reportId, string $transactionHash ): void {
		/** @var \WC26Predictor\Services\ChainlinkService $svc */
		$svc = $this->plugin->make( 'chainlink_service' );
		$svc->updateStatus( $reportId, 'submitted', $transactionHash );

		do_action( 'wc26_log', 'info', sprintf( 'Chainlink report %d submitted: %s', $reportId, $transactionHash ) );
	}

	public function onChainlinkReportConfirmed( int $reportId, string $transactionHash ): void {
		/** @var \WC26Predictor\Services\ChainlinkService $svc */
		$svc = $this->plugin->make( 'chainlink_service' );
		$svc->updateStatus( $reportId, 'confirmed', $transactionHash );

		do_action( 'wc26_log', 'info', sprintf( 'Chainlink report %d confirmed on-chain: %s', $reportId, $transactionHash ) );
	}

	public function onChainlinkReportFailed( int $reportId, string $error, ?string $transactionHash = null ): void {
		/** @var \WC26Predictor\Services\ChainlinkService $svc */
		$svc = $this->plugin->make( 'chainlink_service' );
		$svc->updateStatus( $reportId, 'failed', $transactionHash, $error );

		do_action( 'wc26_log', 'error', sprintf( 'Chainlink report %d failed: %s', $reportId, $error ) );
	}

	/**
	 * User Events
	 */
	public function onUserJoined( int $userId, array $userData ): void {
		/** @var \WC26Predictor\Services\LeaderboardService $lb */
		$lb = $this->plugin->make( 'leaderboard_service' );
		$lb->ensureUser( $userId );

		do_action( 'wc26_log', 'info', sprintf( 'User %d joined the prediction market', $userId ) );
	}

	public function onUserBadgeEarned( int $userId, int $badgeId, string $badgeSlug ): void {
		do_action( 'wc26_notify_user', $userId, 'badge_earned', $badgeSlug );
		do_action( 'wc26_log', 'info', sprintf( 'User %d earned badge: %s', $userId, $badgeSlug ) );
	}
}
