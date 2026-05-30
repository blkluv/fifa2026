<?php
/**
 * ScoringService — flexible point engine driven by scoring_rules table.
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\ScoringRuleRepository;

class ScoringService {

	private ScoringRuleRepository $repo;

	/** @var array<string,int>|null Cached rule map */
	private ?array $rules = null;

	public function __construct() {
		$this->repo = new ScoringRuleRepository();
	}

	/**
	 * Calculate points for a single prediction against the real result.
	 *
	 * Returns an array with 'points' (int) and 'type' (string).
	 *
	 * @return array{points: int, type: string}
	 */
	public function calculate(
		int $predHome,
		int $predAway,
		int $realHome,
		int $realAway,
		bool $isJoker = false
	): array {
		$rules  = $this->getRules();
		$points = 0;
		$type   = 'miss';

		if ( $predHome === $realHome && $predAway === $realAway ) {
			// STATE 1 — Exact score
			$points = (int) ( $rules['exact_score'] ?? 0 );
			$type   = 'exact';

		} elseif ( $realHome === $realAway ) {
			// Real match is a draw
			if ( $predHome === $predAway ) {
				// Predicted a draw (correct winner/draw, wrong score)
				$points = (int) ( $rules['correct_draw'] ?? 0 );
				$type   = 'draw';
			} else {
				// Not a draw prediction. Still award "one team correct" if applicable.
				if ( $predHome === $realHome || $predAway === $realAway ) {
					$points = (int) ( $rules['one_team_correct'] ?? 0 );
					$type   = 'one_team';
				}
			}
		} else {
			// Real match has a winner
			$realDiff  = $realHome - $realAway;   // e.g. +2 means home won by 2
			$predDiff  = $predHome - $predAway;

			if ( $predDiff === $realDiff ) {
				// STATE 2 — Goal difference correct (same margin, different score)
				$points = (int) ( $rules['goal_difference'] ?? 0 );
				$type   = 'goal_diff';
			} elseif ( ( $predHome > $predAway ) === ( $realHome > $realAway ) ) {
				// STATE 3 — Correct winner only
				$points = (int) ( $rules['correct_winner'] ?? 0 );
				$type   = 'winner';
			} else {
				if ( $predHome === $realHome || $predAway === $realAway ) {
					$points = (int) ( $rules['one_team_correct'] ?? 0 );
					$type   = 'one_team';
				}
			}
		}

		// Joker doubles earned points
		if ( $isJoker && $points > 0 ) {
			$points *= 2;
		}

		return [ 'points' => $points, 'type' => $type ];
	}

	/** @return array<string,int> */
	public function getRules(): array {
		if ( null === $this->rules ) {
			$cached = wp_cache_get( 'wc26_scoring_rules', 'wc26' );

			if ( false === $cached ) {
				$this->rules = $this->repo->getAllAsMap();
				wp_cache_set( 'wc26_scoring_rules', $this->rules, 'wc26', HOUR_IN_SECONDS );
			} else {
				$this->rules = (array) $cached;
			}
		}

		return $this->rules;
	}

	/** @return array<int,array<string,mixed>> */
	public function getRulesRows(): array {
		return $this->repo->findBy( [], 'id', 'ASC' );
	}

	/**
	 * @param array<int,array<string,mixed>> $rows
	 * @return array{updated:int, inserted:int}
	 */
	public function updateRulesRows( array $rows ): array {
		$updated  = 0;
		$inserted = 0;

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$key = isset( $row['rule_key'] ) ? sanitize_key( (string) $row['rule_key'] ) : '';
			if ( $key === '' ) {
				continue;
			}

			$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
			$desc  = isset( $row['description'] ) ? sanitize_text_field( (string) $row['description'] ) : '';
			$pts   = isset( $row['points'] ) ? (int) $row['points'] : 0;
			$pts   = max( 0, min( 9999, $pts ) );

			$existing = $this->repo->findBy( [ 'rule_key' => $key ], 'id', 'ASC', 1 );

			if ( $existing ) {
				$affected = $this->repo->update(
					[
						'label'       => $label,
						'description' => $desc,
						'points'      => $pts,
					],
					[ 'rule_key' => $key ]
				);
				$updated += $affected > 0 ? 1 : 0;
			} else {
				$this->repo->insert(
					[
						'rule_key'    => $key,
						'label'       => $label,
						'description' => $desc,
						'points'      => $pts,
					]
				);
				$inserted++;
			}
		}

		wp_cache_delete( 'wc26_scoring_rules', 'wc26' );
		$this->rules = null;

		return [ 'updated' => $updated, 'inserted' => $inserted ];
	}

	/** Seed default scoring rules on activation. */
	public static function seedDefaults(): void {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_scoring_rules';

		$defaults = [
			[ 'exact_score',      'نتیجه دقیق',     10, 'پیش‌بینی دقیق هر دو تیم' ],
			[ 'goal_difference',  'تفاضل گل درست',   5, 'تفاضل گل درست با نتیجه متفاوت' ],
			[ 'correct_winner',   'برد صحیح',        3, 'برنده بازی درست پیش‌بینی شده' ],
			[ 'correct_draw',     'مساوی صحیح',      4, 'پیش‌بینی مساوی درست' ],
			[ 'one_team_correct', 'گل یک تیم درست',  1, 'گل یکی از تیم‌ها درست است' ],
		];

		foreach ( $defaults as [ $key, $label, $pts, $desc ] ) {
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO {$t} (rule_key, label, points, description) VALUES (%s, %s, %d, %s)",
				$key, $label, $pts, $desc
			) );
		}
	}
}
