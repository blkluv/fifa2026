<?php
/**
 * MatchService — CRUD and result submission for matches.
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\MatchRepository;

class MatchService {

	private MatchRepository $repo;

	public function __construct() {
		$this->repo = new MatchRepository();
	}

	/** @return array<int,array<string,mixed>> */
	public function getUpcoming( int $limit = 10 ): array {
		$cache = wp_cache_get( "wc26_upcoming_{$limit}", 'wc26' );
		if ( false !== $cache ) {
			return (array) $cache;
		}
		$results = $this->repo->findUpcoming( $limit );
		wp_cache_set( "wc26_upcoming_{$limit}", $results, 'wc26', 5 * MINUTE_IN_SECONDS );
		return $results;
	}

	/** @return array<int,array<string,mixed>> */
	public function getAllWithTeams(): array {
		return $this->repo->findAllWithTeams();
	}

	public function getMatchWithTeams( int $matchId ): ?array {
		return $this->repo->findWithTeams( $matchId );
	}

	/**
	 * Submit the real result for a match. Triggers scoring pipeline.
	 *
	 * @throws \RuntimeException
	 */
	public function submitResult(
		int $matchId,
		int $homeScore,
		int $awayScore,
		?int $penaltyHome = null,
		?int $penaltyAway = null
	): void {
		$match = $this->repo->find( $matchId );

		if ( ! $match ) {
			throw new \RuntimeException( __( 'مسابقه پیدا نشد.', 'wc26-predictor' ) );
		}

		// Determine winner
		$winnerId = null;
		if ( $homeScore > $awayScore ) {
			$winnerId = (int) $match['home_team_id'];
		} elseif ( $awayScore > $homeScore ) {
			$winnerId = (int) $match['away_team_id'];
		}
		// Draws in knockout handled via penalties
		if ( $penaltyHome !== null && $penaltyAway !== null ) {
			$winnerId = $penaltyHome > $penaltyAway
				? (int) $match['home_team_id']
				: (int) $match['away_team_id'];
		}

		$this->repo->update(
			[
				'status'         => 'finished',
				'home_score'     => $homeScore,
				'away_score'     => $awayScore,
				'penalty_home'   => $penaltyHome,
				'penalty_away'   => $penaltyAway,
				'winner_team_id' => $winnerId,
			],
			[ 'id' => $matchId ]
		);

		// Bust upcoming cache
		wp_cache_delete( 'wc26_upcoming_10', 'wc26' );

		do_action( 'wc26_match_finished', $matchId, $homeScore, $awayScore );
	}

	public function create( array $data ): int {
		return $this->repo->insert( $data );
	}

	public function update( int $matchId, array $data ): void {
		$this->repo->update( $data, [ 'id' => $matchId ] );
	}
}
