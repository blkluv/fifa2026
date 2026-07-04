<?php
/**
 * League Service - handles private prediction leagues
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\MiniLeagueRepository;
use WC26Predictor\Repositories\MiniLeagueMemberRepository;
use WC26Predictor\Repositories\RegionRepository;

class LeagueService {

	private MiniLeagueRepository $leagueRepo;
	private MiniLeagueMemberRepository $memberRepo;
	private RegionRepository $regionRepo;

	public function __construct() {
		$this->leagueRepo = new MiniLeagueRepository();
		$this->memberRepo = new MiniLeagueMemberRepository();
		$this->regionRepo = new RegionRepository();
	}

	/**
	 * Create a new league
	 */
	public function create( int $ownerId, string $name, int $regionId, bool $isPublic = false ): array {
		if ( empty( trim( $name ) ) ) {
			throw new \RuntimeException( __( 'League name is required.', 'wc26-predictor' ) );
		}

		$region = $this->regionRepo->find( $regionId );
		if ( ! $region ) {
			throw new \RuntimeException( __( 'Region not found.', 'wc26-predictor' ) );
		}

		$slug = sanitize_title( $name );
		$inviteCode = $this->generateInviteCode();

		$id = $this->leagueRepo->insert( [
			'owner_id'    => $ownerId,
			'region_id'   => $regionId,
			'name'        => $name,
			'slug'        => $slug,
			'description' => '',
			'invite_code' => $inviteCode,
			'is_public'   => $isPublic ? 1 : 0,
		] );

		// Add owner as first member
		$this->memberRepo->insert( [
			'league_id' => $id,
			'user_id'   => $ownerId,
		] );

		return [
			'id'          => $id,
			'name'        => $name,
			'invite_code' => $inviteCode,
			'region_id'   => $regionId,
			'region_name' => $region['name'],
		];
	}

	/**
	 * Join a league using invite code
	 */
	public function join( int $userId, string $inviteCode ): array {
		$league = $this->leagueRepo->findByCode( $inviteCode );
		if ( ! $league ) {
			throw new \RuntimeException( __( 'Invalid invite code.', 'wc26-predictor' ) );
		}

		// Check if already a member
		$existing = $this->memberRepo->findBy( [
			'league_id' => $league['id'],
			'user_id'   => $userId,
		] );

		if ( $existing ) {
			throw new \RuntimeException( __( 'You are already a member of this league.', 'wc26-predictor' ) );
		}

		$this->memberRepo->insert( [
			'league_id' => $league['id'],
			'user_id'   => $userId,
		] );

		return [
			'id'          => $league['id'],
			'name'        => $league['name'],
			'invite_code' => $league['invite_code'],
		];
	}

	/**
	 * Get leagues for a user
	 */
	public function getUserLeagues( int $userId ): array {
		global $wpdb;
		$l = $wpdb->prefix . 'wc26_mini_leagues';
		$m = $wpdb->prefix . 'wc26_mini_league_members';
		$r = $wpdb->prefix . 'wc26_regions';

		$sql = $wpdb->prepare(
			"SELECT l.*, r.name AS region_name
			 FROM {$l} l
			 JOIN {$m} m ON m.league_id = l.id
			 LEFT JOIN {$r} r ON r.id = l.region_id
			 WHERE m.user_id = %d
			 ORDER BY l.created_at DESC",
			$userId
		);

		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}

	/**
	 * Get league members with their standings
	 */
	public function getLeagueLeaderboard( int $leagueId ): array {
		return $this->memberRepo->getLeagueLeaderboard( $leagueId );
	}

	/**
	 * Check if user is a member of a league
	 */
	public function isMember( int $userId, int $leagueId ): bool {
		$result = $this->memberRepo->findBy( [
			'league_id' => $leagueId,
			'user_id'   => $userId,
		] );
		return ! empty( $result );
	}

	/**
	 * Generate a unique invite code
	 */
	private function generateInviteCode(): string {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		do {
			$code = '';
			for ( $i = 0; $i < 8; $i++ ) {
				$code .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
			}
		} while ( $this->leagueRepo->findByCode( $code ) );

		return $code;
	}
}
