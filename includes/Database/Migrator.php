<?php
/**
 * Database Migrator — creates / upgrades all custom tables via dbDelta().
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
			self::teamsTable( $charset ),
			self::groupsTable( $charset ),
			self::matchesTable( $charset ),
			self::predictionsTable( $charset ),
			self::standingsTable( $charset ),
			self::leaderboardsTable( $charset ),
			self::scoringRulesTable( $charset ),
			self::miniLeaguesTable( $charset ),
			self::miniLeagueMembersTable( $charset ),
			self::badgesTable( $charset ),
			self::userBadgesTable( $charset ),
			self::notificationsTable( $charset ),
		];

		foreach ( $migrations as $sql ) {
			dbDelta( $sql );
		}

		update_option( self::DB_VERSION_OPTION, WC26_DB_VERSION );
	}

	// ── Table DDL ────────────────────────────────────────────────────────────

	private static function teamsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_teams';
		return "CREATE TABLE {$t} (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			fifa_id       VARCHAR(20)     NOT NULL DEFAULT '',
			name          VARCHAR(100)    NOT NULL,
			short_name    VARCHAR(50)     NOT NULL DEFAULT '',
			code          CHAR(3)         NOT NULL DEFAULT '',
			flag_url      VARCHAR(512)    NOT NULL DEFAULT '',
			continent     VARCHAR(50)     NOT NULL DEFAULT '',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY fifa_id (fifa_id),
			KEY code (code)
		) {$charset};";
	}

	private static function groupsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_groups';
		return "CREATE TABLE {$t} (
			id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name       VARCHAR(50)     NOT NULL,
			season     VARCHAR(20)     NOT NULL DEFAULT '2026',
			created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY season (season)
		) {$charset};";
	}

	private static function matchesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_matches';
		return "CREATE TABLE {$t} (
			id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			group_id       BIGINT UNSIGNED NULL,
			stage          ENUM('group','round_32','round_16','quarter','semi','third_place','final') NOT NULL DEFAULT 'group',
			round_number   TINYINT UNSIGNED NOT NULL DEFAULT 1,
			home_team_id   BIGINT UNSIGNED NOT NULL,
			away_team_id   BIGINT UNSIGNED NOT NULL,
			kickoff_at     DATETIME        NOT NULL,
			venue          VARCHAR(150)    NOT NULL DEFAULT '',
			status         ENUM('scheduled','live','finished','postponed','cancelled') NOT NULL DEFAULT 'scheduled',
			home_score     TINYINT UNSIGNED NULL,
			away_score     TINYINT UNSIGNED NULL,
			penalty_home   TINYINT UNSIGNED NULL,
			penalty_away   TINYINT UNSIGNED NULL,
			winner_team_id BIGINT UNSIGNED NULL,
			created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY group_id (group_id),
			KEY status (status),
			KEY kickoff_at (kickoff_at),
			KEY stage (stage),
			KEY home_team_id (home_team_id),
			KEY away_team_id (away_team_id)
		) {$charset};";
	}

	private static function predictionsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_predictions';
		return "CREATE TABLE {$t} (
			id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id          BIGINT UNSIGNED NOT NULL,
			match_id         BIGINT UNSIGNED NOT NULL,
			pred_home_score  TINYINT UNSIGNED NOT NULL,
			pred_away_score  TINYINT UNSIGNED NOT NULL,
			prediction_type  VARCHAR(30)     NOT NULL DEFAULT '',
			earned_points    SMALLINT        NOT NULL DEFAULT 0,
			is_joker         TINYINT(1)      NOT NULL DEFAULT 0,
			created_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_match (user_id, match_id),
			KEY user_id (user_id),
			KEY match_id (match_id),
			KEY earned_points (earned_points)
		) {$charset};";
	}

	private static function standingsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_standings';
		return "CREATE TABLE {$t} (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			group_id        BIGINT UNSIGNED NOT NULL,
			team_id         BIGINT UNSIGNED NOT NULL,
			played          TINYINT UNSIGNED NOT NULL DEFAULT 0,
			won             TINYINT UNSIGNED NOT NULL DEFAULT 0,
			draw            TINYINT UNSIGNED NOT NULL DEFAULT 0,
			lost            TINYINT UNSIGNED NOT NULL DEFAULT 0,
			goals_for       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			goals_against   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			goal_difference SMALLINT         NOT NULL DEFAULT 0,
			points          TINYINT UNSIGNED NOT NULL DEFAULT 0,
			updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY group_team (group_id, team_id),
			KEY group_id (group_id),
			KEY points (points)
		) {$charset};";
	}

	private static function leaderboardsTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_leaderboards';
		return "CREATE TABLE {$t} (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id         BIGINT UNSIGNED NOT NULL,
			total_points    INT             NOT NULL DEFAULT 0,
			exact_hits      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			goal_diff_hits  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			winner_hits     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			rank_position   INT UNSIGNED    NULL,
			updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_id (user_id),
			KEY total_points (total_points),
			KEY rank_position (rank_position)
		) {$charset};";
	}

	private static function scoringRulesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_scoring_rules';
		return "CREATE TABLE {$t} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			rule_key    VARCHAR(60)     NOT NULL,
			label       VARCHAR(100)    NOT NULL,
			points      SMALLINT        NOT NULL DEFAULT 0,
			description TEXT            NULL,
			PRIMARY KEY (id),
			UNIQUE KEY rule_key (rule_key)
		) {$charset};";
	}

	private static function miniLeaguesTable( string $charset ): string {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_mini_leagues';
		return "CREATE TABLE {$t} (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			owner_id    BIGINT UNSIGNED NOT NULL,
			name        VARCHAR(100)    NOT NULL,
			invite_code CHAR(8)         NOT NULL,
			is_public   TINYINT(1)      NOT NULL DEFAULT 0,
			created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY invite_code (invite_code),
			KEY owner_id (owner_id)
		) {$charset};";
	}

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
}
