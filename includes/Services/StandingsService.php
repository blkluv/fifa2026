<?php
/**
 * Standings Service - handles region-based standings
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\StandingsRepository;
use WC26Predictor\Repositories\RegionRepository;

class StandingsService {

	private StandingsRepository $standingsRepo;
	private RegionRepository $regionRepo;

	public function __construct() {
		$this->standingsRepo = new StandingsRepository();
		$this->regionRepo = new RegionRepository();
	}

	/**
	 * Get standings for a specific region
	 */
	public function getRegionStandings( int $regionId ): array {
		$region = $this->regionRepo->find( $regionId );
		if ( ! $region ) {
			throw new \RuntimeException( __( 'Region not found.', 'wc26-predictor' ) );
		}

		return $this->standingsRepo->findByRegion( $regionId );
	}

	/**
	 * Get all regions with standings
	 */
	public function getAllRegionsWithStandings(): array {
		$regions = $this->regionRepo->findAll();
		$result = [];

		foreach ( $regions as $region ) {
			$result[] = [
				'region' => $region,
				'standings' => $this->standingsRepo->findByRegion( (int) $region['id'] ),
			];
		}

		return $result;
	}

	/**
	 * Update standings for a specific region
	 */
	public function updateRegionStandings( int $regionId ): void {
		$this->standingsRepo->rebuildForRegion( $regionId );
	}

	/**
	 * Get user's rank in a region
	 */
	public function getUserRank( int $userId, int $regionId ): ?int {
		$standings = $this->standingsRepo->findByRegion( $regionId );
		foreach ( $standings as $index => $row ) {
			if ( (int) $row['user_id'] === $userId ) {
				return $index + 1;
			}
		}
		return null;
	}

	/**
	 * Get top performers across all regions
	 */
	public function getGlobalTop( int $limit = 10 ): array {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_standings';
		$u = $wpdb->users;
		$r = $wpdb->prefix . 'wc26_regions';

		$sql = $wpdb->prepare(
			"SELECT s.*, u.display_name, u.user_login, r.name AS region_name
			 FROM {$t} s
			 JOIN {$u} u ON u.ID = s.user_id
			 LEFT JOIN {$r} r ON r.id = s.region_id
			 ORDER BY s.total_points DESC
			 LIMIT %d",
			$limit
		);

		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}
}
