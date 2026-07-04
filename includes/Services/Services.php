<?php
/**
 * Service definitions and factory.
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Plugin;

class Services {

	private Plugin $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register all services with the plugin container.
	 */
	public function register(): void {
		$this->plugin->bind( 'market_service', fn() => new MarketService() );
		$this->plugin->bind( 'prediction_service', fn() => new PredictionService( $this->plugin->make( 'scoring_service' ) ) );
		$this->plugin->bind( 'scoring_service', fn() => new ScoringService() );
		$this->plugin->bind( 'standings_service', fn() => new StandingsService() );
		$this->plugin->bind( 'leaderboard_service', fn() => new LeaderboardService() );
		$this->plugin->bind( 'notification_service', fn() => new NotificationService() );
		$this->plugin->bind( 'badge_service', fn() => new BadgeService() );
		$this->plugin->bind( 'league_service', fn() => new LeagueService() );
		$this->plugin->bind( 'import_service', fn() => new ImportService() );
		$this->plugin->bind( 'chainlink_service', fn() => new ChainlinkService() );
	}
}
