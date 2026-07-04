<?php
/**
 * Market Service - handles real estate market operations
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\MarketRepository;
use WC26Predictor\Repositories\PropertyRepository;
use WC26Predictor\Repositories\RegionRepository;
use WC26Predictor\Events\EventManager;

class MarketService {

	private MarketRepository $marketRepo;
	private PropertyRepository $propertyRepo;
	private RegionRepository $regionRepo;

	public function __construct() {
		$this->marketRepo   = new MarketRepository();
		$this->propertyRepo = new PropertyRepository();
		$this->regionRepo   = new RegionRepository();
	}

	/**
	 * Get all markets with property and region details
	 */
	public function getAllWithDetails(): array {
		return $this->marketRepo->findAllWithDetails();
	}

	/**
	 * Get a single market with details
	 */
	public function getMarketWithDetails( int $marketId ): ?array {
		return $this->marketRepo->findWithDetails( $marketId );
	}

	/**
	 * Create a new market
	 */
	public function create( array $data ): int {
		// Validate
		if ( empty( $data['region_id'] ) || empty( $data['property_id'] ) || empty( $data['forecast_date'] ) ) {
			throw new \RuntimeException( __( 'Region, property, and forecast date are required.', 'wc26-predictor' ) );
		}

		$data['status'] = $data['status'] ?? 'pending';
		$data['market_trend'] = $data['market_trend'] ?? 'stable';

		return $this->marketRepo->insert( $data );
	}

	/**
	 * Update an existing market
	 */
	public function update( int $marketId, array $data ): void {
		$this->marketRepo->update( $data, [ 'id' => $marketId ] );
	}

	/**
	 * Submit result for a market (settle it)
	 */
	public function submitResult( int $marketId, float $finalPrice, float $priceChangePct, string $marketTrend ): array {
		$market = $this->marketRepo->find( $marketId );
		if ( ! $market ) {
			throw new \RuntimeException( __( 'Market not found.', 'wc26-predictor' ) );
		}

		if ( $market['status'] === 'settled' ) {
			throw new \RuntimeException( __( 'Market is already settled.', 'wc26-predictor' ) );
		}

		// Update market with results
		$this->marketRepo->update( [
			'final_price'      => $finalPrice,
			'price_change_pct' => $priceChangePct,
			'market_trend'     => $marketTrend,
			'status'           => 'settled',
			'settlement_date'  => current_time( 'mysql' ),
		], [ 'id' => $marketId ] );

		// Calculate outcome (for Chainlink CRE)
		$outcome = $this->determineOutcome( $market['initial_price'], $finalPrice );

		// Trigger scoring for all predictions
		do_action( 'wc26_market_settled', $marketId, $outcome, $priceChangePct );

		return [
			'outcome'    => $outcome,
			'confidence' => $this->calculateConfidence( $market['initial_price'], $finalPrice ),
		];
	}

	private function determineOutcome( float $initialPrice, float $finalPrice ): string {
		$changePct = ( ( $finalPrice - $initialPrice ) / $initialPrice ) * 100;
		if ( $changePct > 3 ) return 'increase';
		if ( $changePct < -3 ) return 'decrease';
		return 'stable';
	}

	private function calculateConfidence( float $initialPrice, float $finalPrice ): float {
		// Simple confidence based on price change magnitude
		$changePct = abs( ( ( $finalPrice - $initialPrice ) / $initialPrice ) * 100 );
		return min( 100, 50 + ( $changePct * 5 ) );
	}
}
