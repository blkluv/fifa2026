<?php
/**
 * BadgeService — award badges based on leaderboard milestones.
 *
 * @package WC26Predictor\Services
 */
 
declare(strict_types=1);
 
namespace WC26Predictor\Services;
 
use WC26Predictor\Repositories\BadgeRepository;
use WC26Predictor\Repositories\LeaderboardRepository;
use WC26Predictor\Repositories\UserBadgeRepository;
 
class BadgeService {
 
	private BadgeRepository $badges;
	private UserBadgeRepository $userBadges;
	private LeaderboardRepository $lb;
	private NotificationService $notifs;
 
	public function __construct() {
		$this->badges     = new BadgeRepository();
		$this->userBadges = new UserBadgeRepository();
		$this->lb         = new LeaderboardRepository();
		$this->notifs     = new NotificationService();
	}
 
	public function evaluate( int $userId ): void {
		$row = $this->lb->findBy( [ 'user_id' => $userId ] );
		if ( ! $row ) {
			return;
		}
 
		$stats = $row[0];
 
		$criteria = [
			'exact_hits'     => (int) $stats['exact_hits'],
			'goal_diff_hits' => (int) $stats['goal_diff_hits'],
			'winner_hits'    => (int) $stats['winner_hits'],
			'total_points'   => (int) $stats['total_points'],
		];
 
		$allBadges = $this->badges->findBy( [], 'id' );
 
		foreach ( $allBadges as $badge ) {
			$badgeId = (int) $badge['id'];
 
			if ( $this->userBadges->userHasBadge( $userId, $badgeId ) ) {
				continue;
			}
 
			$criterion = (string) $badge['criteria'];
			$threshold = (int) $badge['threshold'];
 
			if ( isset( $criteria[ $criterion ] ) && $criteria[ $criterion ] >= $threshold ) {
				$this->award( $userId, $badgeId, (string) $badge['name'] );
			}
		}
	}

	/** @return array<int,array<string,mixed>> */
	public function getUserBadgesWithProgress( int $userId ): array {
		global $wpdb;
		$tP = $wpdb->prefix . 'wc26_predictions';
		$tM = $wpdb->prefix . 'wc26_matches';

		// Compute from predictions — never stale.
		$hitStats = $wpdb->get_row( $wpdb->prepare(
			"SELECT
				COALESCE(SUM(p.earned_points), 0)                                         AS total_points,
				SUM(CASE WHEN p.prediction_type = 'exact'           THEN 1 ELSE 0 END)    AS exact_hits,
				SUM(CASE WHEN p.prediction_type = 'goal_diff'       THEN 1 ELSE 0 END)    AS goal_diff_hits,
				SUM(CASE WHEN p.prediction_type IN ('winner','draw') THEN 1 ELSE 0 END)   AS winner_hits
			 FROM {$tP} p
			 JOIN {$tM} m ON m.id = p.match_id
			 WHERE p.user_id = %d AND m.status = 'finished'",
			$userId
		), ARRAY_A );

		$criteria = [
			'exact_hits'     => (int) ( $hitStats['exact_hits']    ?? 0 ),
			'goal_diff_hits' => (int) ( $hitStats['goal_diff_hits'] ?? 0 ),
			'winner_hits'    => (int) ( $hitStats['winner_hits']   ?? 0 ),
			'total_points'   => (int) ( $hitStats['total_points']  ?? 0 ),
		];

		$allBadges = $this->badges->findBy( [], 'id' );
		$userOwned = $this->userBadges->findBy( [ 'user_id' => $userId ], 'badge_id' );
		$ownedSet  = [];
		foreach ( $userOwned as $ub ) {
			$ownedSet[ (int) $ub['badge_id'] ] = true;
		}

		$out = [];
		foreach ( $allBadges as $badge ) {
			$badgeId  = (int) $badge['id'];
			$crit     = (string) ( $badge['criteria'] ?? '' );
			$threshold = (int) ( $badge['threshold'] ?? 1 );
			$threshold = max( 1, $threshold );
			$current  = isset( $criteria[ $crit ] ) ? (int) $criteria[ $crit ] : 0;
			$earned   = isset( $ownedSet[ $badgeId ] );
			$progress = (int) min( 100, round( 100 * ( $current / $threshold ) ) );

			$out[] = [
				'id'           => $badgeId,
				'slug'         => (string) ( $badge['slug'] ?? '' ),
				'name'         => (string) ( $badge['name'] ?? '' ),
				'description'  => (string) ( $badge['description'] ?? '' ),
				'icon_url'     => (string) ( $badge['icon_url'] ?? '' ),
				'criteria'     => $crit,
				'threshold'    => $threshold,
				'current'      => $current,
				'earned'       => $earned,
				'progress_pct' => $earned ? 100 : $progress,
			];
		}

		return $out;
	}
 
	private function award( int $userId, int $badgeId, string $badgeName ): void {
		$this->userBadges->insert( [
			'user_id'  => $userId,
			'badge_id' => $badgeId,
		] );
 
		$this->notifs->send(
			$userId,
			'badge_earned',
			sprintf( __( 'نشان جدید دریافت شد: %s', 'wc26-predictor' ), $badgeName ),
			__( 'تبریک! یک نشان جدید برای شما ثبت شد.', 'wc26-predictor' )
		);
 
		do_action( 'wc26_badge_earned', $userId, $badgeId );
	}
 
	public static function seed(): void {
		global $wpdb;
		$t = $wpdb->prefix . 'wc26_badges';
 
		$defaults = [
			[ 'exact-score-master',  'استاد نتیجه دقیق',     '۵ پیش‌بینی دقیق ثبت کنید',        'exact_hits',      5 ],
			[ 'goal-diff-king',      'سلطان تفاضل گل',       '۱۰ پیش‌بینی تفاضل درست بزنید',   'goal_diff_hits', 10 ],
			[ 'prediction-wizard',   'جادوگر پیش‌بینی',      '۱۰۰ امتیاز کل کسب کنید',          'total_points',  100 ],
			[ 'champion-predictor',  'پیش‌بین قهرمان',       '۵۰۰ امتیاز کل کسب کنید',          'total_points',  500 ],
		];
 
		foreach ( $defaults as [ $slug, $name, $desc, $crit, $thresh ] ) {
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO {$t} (slug, name, description, criteria, threshold)
				 VALUES (%s, %s, %s, %s, %d)",
				$slug, $name, $desc, $crit, $thresh
			) );
		}
	}
}
