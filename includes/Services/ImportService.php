<?php
/**
 * Import Service - handles CSV imports and API data
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\{
	PropertyRepository,
	RegionRepository,
	MarketRepository
};

class ImportService {

	private PropertyRepository $propertyRepo;
	private RegionRepository $regionRepo;
	private MarketRepository $marketRepo;

	public function __construct() {
		$this->propertyRepo = new PropertyRepository();
		$this->regionRepo   = new RegionRepository();
		$this->marketRepo   = new MarketRepository();
	}

	/**
	 * Import regions from CSV
	 */
	public function importRegions( $handle ): int {
		$count = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( empty( $row[0] ) ) continue;
			$this->regionRepo->insert( [
				'name'        => $row[0],
				'slug'        => sanitize_title( $row[0] ),
				'description' => $row[1] ?? '',
				'country'     => $row[2] ?? '',
				'state'       => $row[3] ?? '',
			] );
			$count++;
		}
		return $count;
	}

	/**
	 * Import properties from CSV
	 */
	public function importProperties( $handle ): int {
		$count = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( empty( $row[0] ) ) continue;
			$this->propertyRepo->insert( [
				'region_id'      => (int) $row[0],
				'name'           => $row[1],
				'slug'           => sanitize_title( $row[1] ),
				'property_type'  => $row[2] ?? 'single-family',
				'address'        => $row[3] ?? '',
				'city'           => $row[4] ?? '',
				'latitude'       => $row[5] ?? null,
				'longitude'      => $row[6] ?? null,
				'initial_price'  => $row[7] ?? null,
				'square_feet'    => $row[8] ?? null,
				'bedrooms'       => $row[9] ?? null,
				'bathrooms'      => $row[10] ?? null,
				'year_built'     => $row[11] ?? null,
			] );
			$count++;
		}
		return $count;
	}

	/**
	 * Import markets from CSV
	 */
	public function importMarkets( $handle ): int {
		$count = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( empty( $row[0] ) ) continue;
			$this->marketRepo->insert( [
				'region_id'      => (int) $row[0],
				'property_id'    => (int) $row[1],
				'forecast_date'  => $row[2],
				'initial_price'  => (float) $row[3],
				'market_trend'   => $row[4] ?? 'stable',
				'status'         => 'pending',
			] );
			$count++;
		}
		return $count;
	}

	/**
	 * Reset all data
	 */
	public function resetAllData(): void {
		global $wpdb;
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_predictions" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_leaderboards" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_standings" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_markets" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_properties" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_regions" );
		$wpdb->query( "TRUNCATE {$wpdb->prefix}wc26_chainlink_reports" );
	}
}
