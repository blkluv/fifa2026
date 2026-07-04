<?php
/**
 * Main Plugin bootstrap class.
 *
 * Adapted for Real Estate Prediction Market with Chainlink CRE Integration
 *
 * @package WC26Predictor
 */

declare(strict_types=1);

namespace WC26Predictor;

use WC26Predictor\Database\Migrator;
use WC26Predictor\Services\{
	MarketService,        
	PredictionService,
	ScoringService,
	StandingsService,
	LeaderboardService,
	NotificationService,
	BadgeService,
	LeagueService,
	ImportService,
	ChainlinkService      
};
use WC26Predictor\REST\Router;
use WC26Predictor\Admin\AdminLoader;
use WC26Predictor\Frontend\FrontendLoader;
use WC26Predictor\Events\EventManager;

final class Plugin {

	private static ?Plugin $instance = null;

	/** @var array<string, object> */
	private array $container = [];

	private function __construct() {}

	public static function getInstance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function boot(): void {
		$this->loadTextDomain();
		$this->registerServices();

		$needsMigrate = ( get_option( Database\Migrator::DB_VERSION_OPTION ) !== WC26_DB_VERSION );
		if ( ! $needsMigrate ) {
			global $wpdb;
			$t = $wpdb->prefix . 'wc26_leaderboards';
			$exists = (string) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) );
			$needsMigrate = ( $exists === '' );
		}
		if ( $needsMigrate ) {
			static::activate();
		}

		$this->registerHooks();
	}

	private function loadTextDomain(): void {
		load_plugin_textdomain(
			'wc26-predictor',
			false,
			dirname( WC26_PLUGIN_BASENAME ) . '/languages'
		);
	}

	private function registerServices(): void {
		// Core services — lazy singletons via closures
		$this->bind( 'market_service',        fn() => new MarketService() );
		$this->bind( 'prediction_service',    fn() => new PredictionService( $this->make( 'scoring_service' ) ) );
		$this->bind( 'scoring_service',       fn() => new ScoringService() );
		$this->bind( 'standings_service',     fn() => new StandingsService() );
		$this->bind( 'leaderboard_service',   fn() => new LeaderboardService() );
		$this->bind( 'notification_service',  fn() => new NotificationService() );
		$this->bind( 'badge_service',         fn() => new BadgeService() );
		$this->bind( 'league_service',        fn() => new LeagueService() );
		$this->bind( 'import_service',        fn() => new ImportService() );
		$this->bind( 'chainlink_service',     fn() => new ChainlinkService() ); // NEW
	}

	private function registerHooks(): void {
		add_filter( 'wc26_prediction_lock_seconds', static function ( int $seconds ): int {
			$minutes = (int) get_option( 'wc26_lock_minutes', 1 );
			$minutes = max( 0, min( 60, $minutes ) );
			return (int) ( $minutes * MINUTE_IN_SECONDS );
		} );

		// REST API
		add_action( 'rest_api_init', function () {
			( new Router( $this ) )->register();
		} );

		// Admin
		if ( is_admin() ) {
			( new AdminLoader( $this ) )->init();
		}

		// Frontend
		( new FrontendLoader( $this ) )->init();

		// Event bus
		( new EventManager( $this ) )->init();

		// Chainlink CRE event hooks
		add_action( 'wc26_market_settled', [ $this->make( 'chainlink_service' ), 'onMarketSettled' ], 10, 3 );
	}

	// ── DI Container ────────────────────────────────────────────────────────

	public function bind( string $key, \Closure $resolver ): void {
		$this->container[ $key ] = $resolver;
	}

	public function make( string $key ): object {
		if ( ! isset( $this->container[ $key ] ) ) {
			throw new \RuntimeException( "Service [{$key}] not registered." );
		}

		// Resolve once and cache
		if ( $this->container[ $key ] instanceof \Closure ) {
			$this->container[ $key ] = ( $this->container[ $key ] )();
		}

		return $this->container[ $key ];
	}

	// ── Lifecycle ────────────────────────────────────────────────────────────

	public static function activate(): void {
		Migrator::run();
		ScoringService::seedDefaults();
		BadgeService::seed();
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
