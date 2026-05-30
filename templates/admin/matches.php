<?php
/**
 * Admin Template: Matches Management
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$matches_table = $wpdb->prefix . 'wc26_matches';
$teams_table   = $wpdb->prefix . 'wc26_teams';

$matches = $wpdb->get_results(
	"SELECT m.*, ht.name AS home_name, ht.code AS home_code,
	        at.name AS away_name, at.code AS away_code
	 FROM {$matches_table} m
	 LEFT JOIN {$teams_table} ht ON ht.id = m.home_team_id
	 LEFT JOIN {$teams_table} at ON at.id = m.away_team_id
	 ORDER BY m.kickoff_at ASC",
	ARRAY_A
);
?>

<div class="wrap wc26-admin-wrap">
	<div class="wc26-admin-header">
		<h1><?php esc_html_e( 'مدیریت مسابقات', 'wc26-predictor' ); ?></h1>
	</div>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th>#</th>
					<th><?php esc_html_e( 'بازی', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'مرحله', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'زمان شروع', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'محل برگزاری', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'وضعیت', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'نتیجه', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'ثبت نتیجه', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $matches ) ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'هنوز مسابقه‌ای ثبت نشده است. از بخش ایمپورت CSV استفاده کنید.', 'wc26-predictor' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $matches as $m ) : ?>
						<?php
						$statusLabels = [
							'scheduled' => __( 'برنامه‌ریزی شده', 'wc26-predictor' ),
							'live'      => __( 'زنده', 'wc26-predictor' ),
							'finished'  => __( 'پایان‌یافته', 'wc26-predictor' ),
							'postponed' => __( 'تعویق', 'wc26-predictor' ),
							'cancelled' => __( 'لغو', 'wc26-predictor' ),
						];
						$statusLabel = $statusLabels[ $m['status'] ] ?? $m['status'];
						?>
						<tr>
							<td><?php echo esc_html( $m['id'] ); ?></td>
							<td>
								<strong><?php echo esc_html( $m['home_code'] . ' vs ' . $m['away_code'] ); ?></strong><br>
								<small><?php echo esc_html( $m['home_name'] . ' vs ' . $m['away_name'] ); ?></small>
							</td>
							<td><?php echo esc_html( $m['stage'] ); ?></td>
							<td><?php echo esc_html( $m['kickoff_at'] ); ?></td>
							<td><?php echo esc_html( $m['venue'] ); ?></td>
							<td>
								<span class="wc26-match-status wc26-status-<?php echo esc_attr( $m['status'] ); ?>">
									<?php echo esc_html( $statusLabel ); ?>
								</span>
							</td>
							<td>
								<?php if ( $m['status'] === 'finished' ) : ?>
									<?php echo esc_html( $m['home_score'] . ' - ' . $m['away_score'] ); ?>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $m['status'] !== 'finished' ) : ?>
									<div class="wc26-result-form">
										<input type="number" class="wc26-res-home" min="0" max="99" value="0" style="width:48px">
										<span>-</span>
										<input type="number" class="wc26-res-away" min="0" max="99" value="0" style="width:48px">
										<button class="button button-primary wc26-submit-result-btn" data-match="<?php echo esc_attr( $m['id'] ); ?>">
											<?php esc_html_e( 'ثبت', 'wc26-predictor' ); ?>
										</button>
									</div>
								<?php else : ?>
									<span class="wc26-status-finished"><?php esc_html_e( 'ثبت شده', 'wc26-predictor' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
