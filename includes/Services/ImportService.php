<?php
/**
 * ImportService — CSV bulk import for teams, groups, matches.
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

class ImportService {

	/** @return array{deleted: array<string,int>} */
	public function resetAllData(): array {
		global $wpdb;

		$tables = [
			'wc26_predictions',
			'wc26_standings',
			'wc26_leaderboards',
			'wc26_mini_league_members',
			'wc26_mini_leagues',
			'wc26_user_badges',
			'wc26_badges',
			'wc26_notifications',
			'wc26_matches',
			'wc26_groups',
			'wc26_teams',
			'wc26_scoring_rules',
		];

		$deleted = [];

		foreach ( $tables as $suffix ) {
			$table = $wpdb->prefix . $suffix;
			$wpdb->query( "TRUNCATE TABLE {$table}" );
			$deleted[ $suffix ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		}

		ScoringService::seedDefaults();
		BadgeService::seed();

		wp_cache_flush();

		return [ 'deleted' => $deleted ];
	}

	/**
	 * Fetch and import WC 2026 schedule from openfootball/worldcup.json.
	 *
	 * @return array{teams:int, groups:int, matches:int, source:string}
	 * @throws \RuntimeException
	 */
	public function importWorldcup2026FromOpenFootball(): array {
		global $wpdb;

		$source = 'https://raw.githubusercontent.com/openfootball/worldcup.json/master/2026/worldcup.json';
		$res    = wp_remote_get( $source, [ 'timeout' => 30 ] );

		if ( is_wp_error( $res ) ) {
			throw new \RuntimeException( $res->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $res );
		$body = (string) wp_remote_retrieve_body( $res );

		if ( $code < 200 || $code >= 300 || $body === '' ) {
			throw new \RuntimeException( __( 'دریافت دیتای مسابقات ناموفق بود.', 'wc26-predictor' ) );
		}

		$json = json_decode( $body, true );
		if ( ! is_array( $json ) || empty( $json['matches'] ) || ! is_array( $json['matches'] ) ) {
			throw new \RuntimeException( __( 'فرمت دیتای دریافتی معتبر نیست.', 'wc26-predictor' ) );
		}

		$matches = $json['matches'];

		$groups = [];
		$teamNames = [];

		foreach ( $matches as $m ) {
			if ( ! is_array( $m ) ) {
				continue;
			}

			if ( ! empty( $m['group'] ) ) {
				$groups[] = (string) $m['group'];
			}

			if ( isset( $m['team1'] ) ) {
				$teamNames[] = (string) $m['team1'];
			}
			if ( isset( $m['team2'] ) ) {
				$teamNames[] = (string) $m['team2'];
			}
		}

		$groups = array_values( array_unique( array_filter( array_map( 'trim', $groups ) ) ) );
		sort( $groups );

		$teamNames = array_values( array_unique( array_filter( array_map( 'trim', $teamNames ) ) ) );
		sort( $teamNames );

		$groupsTable = $wpdb->prefix . 'wc26_groups';
		foreach ( $groups as $g ) {
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO {$groupsTable} (name, season) VALUES (%s, %s)",
				$g,
				'2026'
			) );
		}

		$groupRows = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, name FROM {$groupsTable} WHERE season = %s",
			'2026'
		), ARRAY_A ) ?: [];

		$groupIdByName = [];
		foreach ( $groupRows as $row ) {
			$groupIdByName[ (string) $row['name'] ] = (int) $row['id'];
		}

		$teamsTable = $wpdb->prefix . 'wc26_teams';
		foreach ( $teamNames as $name ) {
			$fifaId = 'OF-' . substr( md5( $name ), 0, 12 );
			$code   = $this->guessTeamCode( $name );

			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO {$teamsTable} (fifa_id, name, short_name, code, flag_url, continent)
				 VALUES (%s, %s, %s, %s, %s, %s)",
				$fifaId,
				$name,
				'',
				$code,
				'',
				$this->isPlaceholderTeam( $name ) ? 'TBD' : ''
			) );
		}

		$teamRows = $wpdb->get_results( "SELECT id, name FROM {$teamsTable}", ARRAY_A ) ?: [];
		$teamIdByName = [];
		foreach ( $teamRows as $row ) {
			$teamIdByName[ (string) $row['name'] ] = (int) $row['id'];
		}

		$matchesTable = $wpdb->prefix . 'wc26_matches';
		$insertedMatches = 0;

		foreach ( $matches as $m ) {
			if ( ! is_array( $m ) ) {
				continue;
			}

			$team1 = isset( $m['team1'] ) ? trim( (string) $m['team1'] ) : '';
			$team2 = isset( $m['team2'] ) ? trim( (string) $m['team2'] ) : '';

			if ( $team1 === '' || $team2 === '' ) {
				continue;
			}

			$homeId = $teamIdByName[ $team1 ] ?? 0;
			$awayId = $teamIdByName[ $team2 ] ?? 0;

			if ( $homeId <= 0 || $awayId <= 0 ) {
				continue;
			}

			$stage   = 'group';
			$groupId = null;

			if ( ! empty( $m['group'] ) ) {
				$gName   = trim( (string) $m['group'] );
				$groupId = $groupIdByName[ $gName ] ?? null;
			} else {
				$stage = $this->mapRoundToStage( (string) ( $m['round'] ?? '' ) );
			}

			$kickoff = $this->normalizeKickoffAt(
				(string) ( $m['date'] ?? '' ),
				(string) ( $m['time'] ?? '' )
			);

			if ( ! $kickoff ) {
				continue;
			}

			$roundNumber = $this->extractRoundNumber( (string) ( $m['round'] ?? '' ), $m['num'] ?? null );

			$wpdb->insert( $matchesTable, [
				'group_id'     => $groupId ? (int) $groupId : null,
				'stage'        => $stage,
				'round_number' => $roundNumber,
				'home_team_id' => $homeId,
				'away_team_id' => $awayId,
				'kickoff_at'   => $kickoff,
				'venue'        => sanitize_text_field( (string) ( $m['ground'] ?? '' ) ),
				'status'       => 'scheduled',
			] );

			$insertedMatches++;
		}

		$this->updateTeamNamesFa();

		wp_cache_delete( 'wc26_global_leaderboard', 'wc26' );

		return [
			'teams'  => count( $teamNames ),
			'groups' => count( $groups ),
			'matches'=> $insertedMatches,
			'source' => $source,
		];
	}

	private function isPlaceholderTeam( string $name ): bool {
		return (bool) preg_match( '/^(\d+[A-L]|3[A-L](?:\/[A-L]){1,}|[A-L]\d+|[0-9A-L\/]+)$/', $name );
	}

	private function guessTeamCode( string $name ): string {
		$n = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $name ) );
		if ( $n === '' ) {
			return 'TBD';
		}
		return substr( $n, 0, 3 );
	}

	private function mapRoundToStage( string $round ): string {
		$r = strtolower( trim( $round ) );
		return match ( true ) {
			str_contains( $r, 'round of 32' ) => 'round_32',
			str_contains( $r, 'round of 16' ) => 'round_16',
			str_contains( $r, 'quarter' )     => 'quarter',
			str_contains( $r, 'semi' )        => 'semi',
			str_contains( $r, 'third' )       => 'third_place',
			str_contains( $r, 'final' )       => 'final',
			default                           => 'group',
		};
	}

	private function extractRoundNumber( string $round, $num ): int {
		if ( $num !== null && is_numeric( $num ) ) {
			return (int) $num;
		}
		if ( preg_match( '/(\d+)/', $round, $m ) ) {
			return (int) $m[1];
		}
		return 1;
	}

	private function normalizeKickoffAt( string $date, string $time ): ?string {
		$date = trim( $date );
		$time = trim( $time );

		if ( $date === '' || $time === '' ) {
			return null;
		}

		if ( ! preg_match( '/^(\d{1,2}):(\d{2})\s+UTC([+-]\d{1,2})$/', $time, $m ) ) {
			return null;
		}

		$hh = (int) $m[1];
		$mm = (int) $m[2];
		$offsetHours = (int) $m[3];
		$offset = sprintf( '%+03d:00', $offsetHours );

		$dt = \DateTimeImmutable::createFromFormat(
			'Y-m-d H:i',
			sprintf( '%s %02d:%02d', $date, $hh, $mm ),
			new \DateTimeZone( $offset )
		);

		if ( ! $dt ) {
			return null;
		}

		$wpTz = wp_timezone();
		return $dt->setTimezone( $wpTz )->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Update all team names in the DB to Persian and fix FIFA codes.
	 * Safe to call multiple times — uses ON DUPLICATE KEY UPDATE via name matching.
	 *
	 * @return int number of rows updated
	 */
	public function updateTeamNamesFa(): int {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_teams';

		$map = [
			// English name => [Persian name, FIFA code]
			'Argentina'           => ['آرژانتین',         'ARG'],
			'Australia'           => ['استرالیا',          'AUS'],
			'Belgium'             => ['بلژیک',             'BEL'],
			'Bolivia'             => ['بولیوی',            'BOL'],
			'Brazil'              => ['برزیل',             'BRA'],
			'Cameroon'            => ['کامرون',            'CMR'],
			'Canada'              => ['کانادا',            'CAN'],
			'Chile'               => ['شیلی',              'CHI'],
			'Colombia'            => ['کلمبیا',            'COL'],
			'Costa Rica'          => ['کاستاریکا',         'CRC'],
			'Croatia'             => ['کرواسی',            'CRO'],
			'Denmark'             => ['دانمارک',           'DEN'],
			'Ecuador'             => ['اکوادور',           'ECU'],
			'Egypt'               => ['مصر',               'EGY'],
			'El Salvador'         => ['السالوادور',        'SLV'],
			'England'             => ['انگلیس',            'ENG'],
			'France'              => ['فرانسه',            'FRA'],
			'Germany'             => ['آلمان',             'GER'],
			'Ghana'               => ['غنا',               'GHA'],
			'Greece'              => ['یونان',             'GRE'],
			'Honduras'            => ['هندوراس',           'HON'],
			'Hungary'             => ['مجارستان',          'HUN'],
			'Iran'                => ['ایران',             'IRN'],
			'Iraq'                => ['عراق',              'IRQ'],
			'Italy'               => ['ایتالیا',           'ITA'],
			'Jamaica'             => ['جامائیکا',          'JAM'],
			'Japan'               => ['ژاپن',              'JPN'],
			'Jordan'              => ['اردن',              'JOR'],
			'Kenya'               => ['کنیا',              'KEN'],
			'Mali'                => ['مالی',              'MLI'],
			'Mexico'              => ['مکزیک',             'MEX'],
			'Morocco'             => ['مراکش',             'MAR'],
			'Netherlands'         => ['هلند',              'NED'],
			'New Zealand'         => ['نیوزیلند',          'NZL'],
			'Nigeria'             => ['نیجریه',            'NGA'],
			'Panama'              => ['پاناما',            'PAN'],
			'Paraguay'            => ['پاراگوئه',          'PAR'],
			'Peru'                => ['پرو',               'PER'],
			'Poland'              => ['لهستان',            'POL'],
			'Portugal'            => ['پرتغال',            'POR'],
			'Qatar'               => ['قطر',               'QAT'],
			'Romania'             => ['رومانی',            'ROU'],
			'Saudi Arabia'        => ['عربستان سعودی',     'KSA'],
			'Scotland'            => ['اسکاتلند',          'SCO'],
			'Senegal'             => ['سنگال',             'SEN'],
			'Serbia'              => ['صربستان',           'SRB'],
			'Slovakia'            => ['اسلواکی',           'SVK'],
			'Slovenia'            => ['اسلوونی',           'SVN'],
			'South Africa'        => ['آفریقای جنوبی',     'RSA'],
			'South Korea'         => ['کره جنوبی',         'KOR'],
			'Korea Republic'      => ['کره جنوبی',         'KOR'],
			'Spain'               => ['اسپانیا',           'ESP'],
			'Switzerland'         => ['سوئیس',             'SUI'],
			'Trinidad and Tobago' => ['ترینیداد و توباگو', 'TTO'],
			'Trinidad & Tobago'   => ['ترینیداد و توباگو', 'TTO'],
			'Tunisia'             => ['تونس',              'TUN'],
			'Turkey'              => ['ترکیه',             'TUR'],
			'Ukraine'             => ['اوکراین',           'UKR'],
			'United States'       => ['آمریکا',            'USA'],
			'USA'                 => ['آمریکا',            'USA'],
			'Uruguay'             => ['اروگوئه',           'URU'],
			'Uzbekistan'          => ['ازبکستان',          'UZB'],
			'Venezuela'           => ['ونزوئلا',           'VEN'],
			'Ivory Coast'         => ['ساحل عاج',          'CIV'],
			"Côte d'Ivoire"       => ['ساحل عاج',          'CIV'],
			"Cote d'Ivoire"       => ['ساحل عاج',          'CIV'],
			'DR Congo'            => ['کنگو',              'COD'],
			'Congo DR'            => ['کنگو',              'COD'],
			'Algeria'             => ['الجزایر',           'ALG'],
			'Benin'               => ['بنین',              'BEN'],
			'Libya'               => ['لیبی',              'LBA'],
			'Tanzania'            => ['تانزانیا',          'TAN'],
			'Guinea'              => ['گینه',              'GUI'],
			'Albania'             => ['آلبانی',            'ALB'],
			'Austria'             => ['اتریش',             'AUT'],
			'Czech Republic'      => ['جمهوری چک',        'CZE'],
			'Czechia'             => ['جمهوری چک',        'CZE'],
			'Guatemala'           => ['گواتمالا',          'GUA'],
			'Haiti'               => ['هائیتی',            'HAI'],
			'Palestine'           => ['فلسطین',            'PLE'],
			'Georgia'             => ['گرجستان',           'GEO'],
			'Fiji'                => ['فیجی',              'FIJ'],
			'Cuba'                => ['کوبا',              'CUB'],
			'Sweden'              => ['سوئد',              'SWE'],
			'Norway'              => ['نروژ',              'NOR'],
			'Wales'               => ['ولز',               'WAL'],
			'Zambia'              => ['زامبیا',            'ZAM'],
			'Israel'              => ['اسرائیل',           'ISR'],
		];

		$updated = 0;
		foreach ( $map as $engName => [ $faName, $code ] ) {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT id FROM {$t} WHERE name = %s",
				$engName
			), ARRAY_A );

			foreach ( $rows as $row ) {
				$result = $wpdb->update(
					$t,
					[ 'name' => $faName, 'code' => $code, 'short_name' => $faName ],
					[ 'id'   => (int) $row['id'] ]
				);
				if ( $result !== false ) {
					$updated++;
				}
			}
		}

		wp_cache_flush();
		return $updated;
	}

	public function importTeams( $handle ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_teams';
		$n = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			[ $fifaId, $name, $shortName, $code, $flagUrl, $continent ] = array_pad( $row, 6, '' );

			$wpdb->query( $wpdb->prepare(
				"INSERT INTO {$t} (fifa_id, name, short_name, code, flag_url, continent)
				 VALUES (%s, %s, %s, %s, %s, %s)
				 ON DUPLICATE KEY UPDATE name = VALUES(name), flag_url = VALUES(flag_url), continent = VALUES(continent), short_name = VALUES(short_name), code = VALUES(code)",
				sanitize_text_field( $fifaId ),
				sanitize_text_field( $name ),
				sanitize_text_field( $shortName ),
				strtoupper( substr( sanitize_text_field( $code ), 0, 3 ) ),
				esc_url_raw( $flagUrl ),
				sanitize_text_field( $continent )
			) );
			$n++;
		}

		return $n;
	}

	public function importGroups( $handle ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_groups';
		$n = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			[ $name, $season ] = array_pad( $row, 2, '2026' );
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO {$t} (name, season) VALUES (%s, %s)",
				sanitize_text_field( $name ),
				sanitize_text_field( $season )
			) );
			$n++;
		}

		return $n;
	}

	public function importMatches( $handle ): int {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_matches';
		$n = 0;

		$allowedStages = [ 'group', 'round_32', 'round_16', 'quarter', 'semi', 'third_place', 'final' ];

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			[ $groupId, $stage, $homeId, $awayId, $kickoff, $venue ] = array_pad( $row, 6, '' );

			$stageKey = sanitize_key( $stage ) ?: 'group';
			if ( ! in_array( $stageKey, $allowedStages, true ) ) {
				$stageKey = 'group';
			}

			$wpdb->insert( $t, [
				'group_id'     => $groupId ? (int) $groupId : null,
				'stage'        => $stageKey,
				'home_team_id' => (int) $homeId,
				'away_team_id' => (int) $awayId,
				'kickoff_at'   => sanitize_text_field( $kickoff ),
				'venue'        => sanitize_text_field( $venue ),
			] );
			$n++;
		}

		return $n;
	}
}
