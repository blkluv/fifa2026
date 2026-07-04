<?php
/**
 * Notification Service - handles user notifications
 *
 * @package WC26Predictor\Services
 */

declare(strict_types=1);

namespace WC26Predictor\Services;

use WC26Predictor\Repositories\NotificationRepository;

class NotificationService {

	private NotificationRepository $notificationRepo;

	public function __construct() {
		$this->notificationRepo = new NotificationRepository();
	}

	/**
	 * Send a notification to a user
	 */
	public function notify( int $userId, string $type, string $title, string $body, ?string $link = null ): void {
		$this->notificationRepo->insert( [
			'user_id' => $userId,
			'type'    => $type,
			'title'   => $title,
			'body'    => $body,
			'link'    => $link,
		] );
	}

	/**
	 * Notify all users in a region
	 */
	public function notifyRegion( int $regionId, string $type, string $title, string $body ): void {
		global $wpdb;
		$standingsTable = $wpdb->prefix . 'wc26_standings';
		$users = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT user_id FROM {$standingsTable} WHERE region_id = %d",
			$regionId
		) );

		foreach ( $users as $userId ) {
			$this->notify( (int) $userId, $type, $title, $body );
		}
	}

	/**
	 * Notify all admins
	 */
	public function notifyAdmins( string $type, string $title, string $body ): void {
		$admins = get_users( [ 'role__in' => [ 'administrator' ] ] );
		foreach ( $admins as $admin ) {
			$this->notify( (int) $admin->ID, $type, $title, $body );
		}
	}

	/**
	 * Get unread notifications for a user
	 */
	public function getUnread( int $userId ): array {
		return $this->notificationRepo->findUnread( $userId );
	}

	/**
	 * Mark all notifications as read
	 */
	public function markAllRead( int $userId ): void {
		$this->notificationRepo->markAllRead( $userId );
	}

	/**
	 * Get notification count for a user
	 */
	public function getUnreadCount( int $userId ): int {
		return count( $this->notificationRepo->findUnread( $userId, 9999 ) );
	}
}
