<?php
/**
 * Scoring Service - calculates points for real estate predictions
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\ScoringRuleRepository;

class ScoringService {

	private ScoringRuleRepository $ruleRepo;

	public function __construct() {
		$this->ruleRepo = new ScoringRuleRepository();
	}

	/**
	 * Calculate points for a prediction
	 */
	public function calculatePoints( array $prediction, array $market ): int {
		$rules = $this->ruleRepo->getAllAsMap();

		// Exact price match (within 1%)
		if ( isset( $prediction['predicted_price'] ) && $prediction['predicted_price'] > 0 ) {
			$priceDiff = abs( $prediction['predicted_price'] - $market['final_price'] ) / $market['final_price'] * 100;
			if ( $priceDiff <= 1 ) {
				return ( $prediction['is_joker'] ? 2 : 1 ) * ( $rules['exact_price'] ?? 10 );
			}
			// Price range (within 5%)
			if ( $priceDiff <= 5 ) {
				return ( $prediction['is_joker'] ? 2 : 1 ) * ( $rules['price_range'] ?? 5 );
			}
		}

		// Trend prediction
		if ( isset( $prediction['predicted_trend'] ) ) {
			$actualTrend = $this->determineTrend( $market['initial_price'], $market['final_price'] );
			if ( $prediction['predicted_trend'] === $actualTrend ) {
				return ( $prediction['is_joker'] ? 2 : 1 ) * ( $rules['correct_trend'] ?? 3 );
			}
			// Partial - opposite trend but not extreme
			if ( $this->isPartialTrend( $prediction['predicted_trend'], $actualTrend ) ) {
				return ( $prediction['is_joker'] ? 2 : 1 ) * ( $rules['partial_trend'] ?? 1 );
			}
		}

		return 0;
	}

	public function determineTrend( float $initialPrice, float $finalPrice ): string {
		$changePct = ( ( $finalPrice - $initialPrice ) / $initialPrice ) * 100;
		if ( $changePct > 3 ) return 'increase';
		if ( $changePct < -3 ) return 'decrease';
		return 'stable';
	}

	private function isPartialTrend( string $predicted, string $actual ): bool {
		// If predicted increase but actual stable, or vice versa
		if ( $predicted === 'increase' && $actual === 'stable' ) return true;
		if ( $predicted === 'decrease' && $actual === 'stable' ) return true;
		if ( $predicted === 'stable' && ( $actual === 'increase' || $actual === 'decrease' ) ) return true;
		return false;
	}

	public function getPredictionType( array $prediction, array $market ): string {
		if ( isset( $prediction['predicted_price'] ) && $prediction['predicted_price'] > 0 ) {
			$priceDiff = abs( $prediction['predicted_price'] - $market['final_price'] ) / $market['final_price'] * 100;
			if ( $priceDiff <= 1 ) return 'exact';
			if ( $priceDiff <= 5 ) return 'range';
		}
		return 'trend';
	}

	/**
	 * Get all scoring rules
	 */
	public function getRules(): array {
		return $this->ruleRepo->findAll();
	}

	/**
	 * Update scoring rules
	 */
	public function updateRulesRows( array $rules ): array {
		foreach ( $rules as $rule ) {
			if ( isset( $rule['rule_key'] ) && isset( $rule['points'] ) ) {
				$this->ruleRepo->update(
					[ 'points' => (int) $rule['points'], 'label' => $rule['label'] ?? '' ],
					[ 'rule_key' => $rule['rule_key'] ]
				);
			}
		}
		return [ 'updated' => count( $rules ) ];
	}

	/**
	 * Seed default scoring rules
	 */
	public static function seedDefaults(): void {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_scoring_rules';

		$rules = [
			[ 'exact_price', 'Exact Price', 10, 'Predict the exact final price (within 1%)' ],
			[ 'price_range', 'Price Range', 5, 'Predict the correct price range (within 5%)' ],
			[ 'correct_trend', 'Correct Trend', 3, 'Predict the correct market trend' ],
			[ 'partial_trend', 'Partial Trend', 1, 'Partial trend prediction' ],
		];

		foreach ( $rules as $rule ) {
			$wpdb->replace(
				$t,
				[
					'rule_key'    => $rule[0],
					'label'       => $rule[1],
					'points'      => $rule[2],
					'description' => $rule[3],
				],
				[ '%s', '%s', '%d', '%s' ]
			);
		}
	}
}
