<?php
/**
 * Database Migrator — creates / upgrades all custom tables via dbDelta().
 *
 * Adapted for Real Estate Prediction Market with Chainlink CRE Integration
 *
 * @package WC26Predictor\Database
 */

declare(strict_types=1);

namespace WC26Predictor\Database;

class Migrator {

	public const DB_VERSION_OPTION = 'wc26_db_version';

	public static function run(): void {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		$migrations = [
			self::regionsTable( $charset ),        // replaces groups
			self::propertiesTable( $charset ),     // replaces teams
			self::marketsTable( $charset ),        // replaces matches
			self::predictionsTable( $charset ),    // adapted for real estate
			self::standingsTable( $charset ),      // adapted for real estate
			self::leaderboardsTable( $charset ),   // adapted for real estate
			self::scoringRulesTable( $charset ),   // adapted for real estate
			self::miniLeaguesTable( $charset ),    // adapted for real estate
			self::miniLeagueMembersTable( $charset ),
			self::badgesTable( $charset ),         // adapted for real estate
			self::userBadgesTable( $charset ),
			self::notificationsTable( $charset ),
			self::chainlinkReportsTable( $charset ), // NEW for CRE
		];

		foreach ( $migrations as $sql ) {
			dbDelta( $sql );
		}

		update_option( self::DB_VERSION_OPTION, WC26_DB_VERSION );
	}

	// ── Table DDL ────────────────────────────────────────────────────────────

	/**
	 * Regions table (replaces groups)
	 * Geographic regions for real estate markets (e.g., "Austin Metro", "Miami Beach")
	 */
	private static function regionsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_regions';
		return "CREATE TABLE {$t} (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name          VARCHAR(100)    NOT NULL,
			slug          VARCHAR(100)    NOT NULL,
			description   TEXT            NULL,
			country       VARCHAR(100)    NOT NULL DEFAULT '',
			state         VARCHAR(100)    NOT NULL DEFAULT '',
			timezone      VARCHAR(50)     NOT NULL DEFAULT 'UTC',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY name (name)
		) {$charset};";
	}

	/**
	 * Properties table (replaces teams)
	 * Real estate properties with pricing and location data
	 */
	private static function propertiesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_properties';
		return "CREATE TABLE {$t} (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			region_id       BIGINT UNSIGNED NOT NULL,
			name            VARCHAR(255)    NOT NULL,
			slug            VARCHAR(255)    NOT NULL,
			property_type   VARCHAR(50)     NOT NULL DEFAULT '',
			address         TEXT            NULL,
			city            VARCHAR(100)    NOT NULL DEFAULT '',
			latitude        DECIMAL(10, 8)  NULL,
			longitude       DECIMAL(11, 8)  NULL,
			initial_price   DECIMAL(15, 2)  NULL,
			square_feet     INT UNSIGNED    NULL,
			bedrooms        TINYINT UNSIGNED NULL,
			bathrooms       DECIMAL(3, 1)   NULL,
			year_built      SMALLINT UNSIGNED NULL,
			created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY region_id (region_id),
			KEY property_type (property_type),
			KEY city (city)
		) {$charset};";
	}

	/**
	 * Markets table (replaces matches)
	 * Forecast periods for real estate price predictions
	 */
	private static function marketsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_markets';
		return "CREATE TABLE {$t} (
			id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			region_id         BIGINT UNSIGNED NOT NULL,
			property_id       BIGINT UNSIGNED NOT NULL,
			forecast_date     DATETIME        NOT NULL,
			initial_price     DECIMAL(15, 2)  NOT NULL,
			market_trend      ENUM('increase','decrease','stable','volatile') NOT NULL DEFAULT 'stable',
			status            ENUM('pending','active','settled','cancelled') NOT NULL DEFAULT 'pending',
			final_price       DECIMAL(15, 2)  NULL,
			price_change_pct  DECIMAL(6, 2)   NULL,
			settlement_date   DATETIME        NULL,
			settlement_source VARCHAR(255)    NULL,
			created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY region_id (region_id),
			KEY property_id (property_id),
			KEY forecast_date (forecast_date),
			KEY status (status),
			KEY market_trend (market_trend)
		) {$charset};";
	}

	/**
	 * Predictions table (adapted for real estate)
	 * User predictions for real estate markets
	 */
	private static function predictionsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_predictions';
		return "CREATE TABLE {$t} (
			id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id          BIGINT UNSIGNED NOT NULL,
			market_id        BIGINT UNSIGNED NOT NULL,
			predicted_price  DECIMAL(15, 2)  NULL,
			predicted_trend  VARCHAR(20)     NOT NULL DEFAULT '',
			predicted_range  VARCHAR(50)     NULL,
			prediction_type  VARCHAR(30)     NOT NULL DEFAULT '',
			earned_points    SMALLINT        NOT NULL DEFAULT 0,
			is_joker         TINYINT(1)      NOT NULL DEFAULT 0,
			confidence_score DECIMAL(5, 2)   NULL,
			created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_market (user_id, market_id),
			KEY user_id (user_id),
			KEY market_id (market_id),
			KEY earned_points (earned_points),
			KEY predicted_trend (predicted_trend)
		) {$charset};";
	}

	/**
	 * Standings table (adapted for real estate)
	 * Region-based standings for users
	 */
	private static function standingsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_standings';
		return "CREATE TABLE {$t} (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			region_id       BIGINT UNSIGNED NOT NULL,
			user_id         BIGINT UNSIGNED NOT NULL,
			total_points    INT             NOT NULL DEFAULT 0,
			exact_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			trend_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			range_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			total_predictions SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			last_updated    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY region_user (region_id, user_id),
			KEY region_id (region_id),
			KEY user_id (user_id),
			KEY total_points (total_points)
		) {$charset};";
	}

	/**
	 * Leaderboards table (adapted for real estate)
	 * Global and region-based leaderboards
	 */
	private static function leaderboardsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_leaderboards';
		return "CREATE TABLE {$t} (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id         BIGINT UNSIGNED NOT NULL,
			region_id       BIGINT UNSIGNED NULL,
			total_points    INT             NOT NULL DEFAULT 0,
			exact_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			trend_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			range_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			rank_position   INT UNSIGNED    NULL,
			updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_region (user_id, region_id),
			KEY user_id (user_id),
			KEY region_id (region_id),
			KEY total_points (total_points),
			KEY rank_position (rank_position)
		) {$charset};";
	}

	/**
	 * Scoring Rules table (adapted for real estate)
	 * Dynamic point values for real estate predictions
	 */
	private static function scoringRulesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_scoring_rules';
		return "CREATE TABLE {$t} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			rule_key    VARCHAR(60)     NOT NULL,
			label       VARCHAR(100)    NOT NULL,
			points      SMALLINT        NOT NULL DEFAULT 0,
			description TEXT            NULL,
			is_active   TINYINT(1)      NOT NULL DEFAULT 1,
			PRIMARY KEY (id),
			UNIQUE KEY rule_key (rule_key)
		) {$charset};";
	}

	/**
	 * Mini Leagues table (adapted for real estate)
	 * Private prediction leagues
	 */
	private static function miniLeaguesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_mini_leagues';
		return "CREATE TABLE {$t} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			owner_id    BIGINT UNSIGNED NOT NULL,
			region_id   BIGINT UNSIGNED NOT NULL,
			name        VARCHAR(100)    NOT NULL,
			slug        VARCHAR(100)    NOT NULL,
			description TEXT            NULL,
			invite_code CHAR(8)         NOT NULL,
			is_public   TINYINT(1)      NOT NULL DEFAULT 0,
			created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			UNIQUE KEY invite_code (invite_code),
			KEY owner_id (owner_id),
			KEY region_id (region_id)
		) {$charset};";
	}

	/**
	 * Mini League Members table
	 */
	private static function miniLeagueMembersTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_mini_league_members';
		return "CREATE TABLE {$t} (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			league_id  BIGINT UNSIGNED NOT NULL,
			user_id    BIGINT UNSIGNED NOT NULL,
			joined_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY league_user (league_id, user_id),
			KEY league_id (league_id),
			KEY user_id (user_id)
		) {$charset};";
	}

	/**
	 * Badges table (adapted for real estate)
	 */
	private static function badgesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_badges';
		return "CREATE TABLE {$t} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			slug        VARCHAR(80)     NOT NULL,
			name        VARCHAR(100)    NOT NULL,
			description TEXT            NULL,
			icon_url    VARCHAR(512)    NOT NULL DEFAULT '',
			criteria    VARCHAR(50)     NOT NULL DEFAULT '',
			threshold   INT UNSIGNED    NOT NULL DEFAULT 1,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) {$charset};";
	}

	/**
	 * User Badges table
	 */
	private static function userBadgesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_user_badges';
		return "CREATE TABLE {$t} (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id    BIGINT UNSIGNED NOT NULL,
			badge_id   BIGINT UNSIGNED NOT NULL,
			earned_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_badge (user_id, badge_id),
			KEY badge_id (badge_id)
		) {$charset};";
	}

	/**
	 * Notifications table
	 */
	private static function notificationsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_notifications';
		return "CREATE TABLE {$t} (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id    BIGINT UNSIGNED NOT NULL,
			type       VARCHAR(60)     NOT NULL,
			title      VARCHAR(200)    NOT NULL,
			body       TEXT            NULL,
			is_read    TINYINT(1)      NOT NULL DEFAULT 0,
			created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY is_read (is_read),
			KEY created_at (created_at)
		) {$charset};";
	}

	/**
	 * Chainlink CRE Reports table (NEW)
	 * Stores DON reports, signatures, and settlement data
	 */
	private static function chainlinkReportsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_chainlink_reports';
		return "CREATE TABLE {$t} (
			id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			market_id        BIGINT UNSIGNED NOT NULL,
			don_id           VARCHAR(100)    NOT NULL DEFAULT '',
			report_type      VARCHAR(50)     NOT NULL DEFAULT 'settlement',
			report_data      JSON            NOT NULL,
			signature        TEXT            NULL,
			status           ENUM('pending','submitted','confirmed','failed','expired') NOT NULL DEFAULT 'pending',
			transaction_hash VARCHAR(255)    NULL,
			error_message    TEXT            NULL,
			created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			submitted_at     DATETIME        NULL,
			confirmed_at     DATETIME        NULL,
			PRIMARY KEY (id),
			KEY market_id (market_id),
			KEY status (status),
			KEY don_id (don_id),
			KEY created_at (created_at)
		) {$charset};";
	}
}
