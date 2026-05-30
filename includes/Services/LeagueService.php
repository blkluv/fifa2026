<?php
/**
 * LeagueService — create and join mini private leagues.
 *
 * @package WC26Predictor\Services
 */
 
declare(strict_types=1);
 
namespace WC26Predictor\Services;
 
use WC26Predictor\Repositories\MiniLeagueMemberRepository;
use WC26Predictor\Repositories\MiniLeagueRepository;
 
class LeagueService {
 
	private MiniLeagueRepository $leagues;
	private MiniLeagueMemberRepository $members;
 
	public function __construct() {
		$this->leagues = new MiniLeagueRepository();
		$this->members = new MiniLeagueMemberRepository();
	}
 
	/** @throws \RuntimeException */
	public function create( int $ownerId, string $name ): array {
		$name = sanitize_text_field( $name );
		if ( mb_strlen( $name ) < 3 ) {
			throw new \RuntimeException( __( 'نام لیگ باید حداقل ۳ کاراکتر باشد.', 'wc26-predictor' ) );
		}
 
		$code     = $this->generateCode();
		$leagueId = $this->leagues->insert( [
			'owner_id'    => $ownerId,
			'name'        => $name,
			'invite_code' => $code,
		] );
 
		$this->members->insert( [
			'league_id' => $leagueId,
			'user_id'   => $ownerId,
		] );
 
		return [ 'id' => $leagueId, 'invite_code' => $code, 'name' => $name ];
	}
 
	/** @throws \RuntimeException */
	public function join( int $userId, string $code ): array {
		$league = $this->leagues->findByCode( strtoupper( trim( $code ) ) );
 
		if ( ! $league ) {
			throw new \RuntimeException( __( 'کد دعوت معتبر نیست.', 'wc26-predictor' ) );
		}
 
		$existing = $this->members->findBy( [
			'league_id' => $league['id'],
			'user_id'   => $userId,
		] );
 
		if ( $existing ) {
			return $league;
		}
 
		$this->members->insert( [
			'league_id' => (int) $league['id'],
			'user_id'   => $userId,
		] );
 
		return $league;
	}
 
	/** @return array<int,array<string,mixed>> */
	public function getUserLeagues( int $userId ): array {
		global $wpdb;
		$m = $wpdb->prefix . 'wc26_mini_league_members';
		$l = $wpdb->prefix . 'wc26_mini_leagues';
		$sql = $wpdb->prepare(
			"SELECT lg.* FROM {$m} ml JOIN {$l} lg ON lg.id = ml.league_id WHERE ml.user_id = %d",
			$userId
		);
		return $wpdb->get_results( $sql, ARRAY_A ) ?: [];
	}
 
	private function generateCode(): string {
		do {
			$code = strtoupper( substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 8 ) );
		} while ( $this->leagues->findByCode( $code ) );
 
		return $code;
	}
}
