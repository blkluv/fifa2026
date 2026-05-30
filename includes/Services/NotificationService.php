<?php
/**
 * NotificationService — creates in-app notifications.
 *
 * @package WC26Predictor\Services
 */
 
declare(strict_types=1);
 
namespace WC26Predictor\Services;
 
use WC26Predictor\Repositories\NotificationRepository;
 
class NotificationService {
 
	private NotificationRepository $repo;
 
	public function __construct() {
		$this->repo = new NotificationRepository();
	}
 
	public function send( int $userId, string $type, string $title, string $body = '' ): void {
		$this->repo->insert( [
			'user_id' => $userId,
			'type'    => sanitize_key( $type ),
			'title'   => sanitize_text_field( $title ),
			'body'    => sanitize_textarea_field( $body ),
		] );
	}
 
	/** @param array<int,int|string> $userIds */
	public function sendBulk( array $userIds, string $type, string $title, string $body = '' ): void {
		foreach ( $userIds as $uid ) {
			$this->send( (int) $uid, $type, $title, $body );
		}
	}
 
	/** @return array<int,array<string,mixed>> */
	public function getUnread( int $userId ): array {
		return $this->repo->findUnread( $userId );
	}
 
	public function markAllRead( int $userId ): void {
		$this->repo->markAllRead( $userId );
	}
}
