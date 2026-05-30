<?php
/**
 * Admin Template: Leaderboard
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var array $rows */
?>
<div class="wrap wc26-admin-wrap">
	<h1><?php esc_html_e( 'جدول امتیازات (سراسری)', 'wc26-predictor' ); ?></h1>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'رتبه', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'بازیکن', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'امتیاز', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'دقیق', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'تفاضل', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'برد/مساوی', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'هنوز داده‌ای ثبت نشده است.', 'wc26-predictor' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $i => $row ) : ?>
						<tr class="<?php echo $i < 3 ? 'wc26-lb-rank-' . ( $i + 1 ) : ''; ?>">
							<td><?php echo esc_html( $row['rank_position'] ?? ( $i + 1 ) ); ?></td>
							<td><?php echo esc_html( $row['display_name'] ); ?></td>
							<td><strong><?php echo esc_html( number_format( (int) $row['total_points'] ) ); ?></strong></td>
							<td><?php echo esc_html( $row['exact_hits'] ); ?></td>
							<td><?php echo esc_html( $row['goal_diff_hits'] ); ?></td>
							<td><?php echo esc_html( $row['winner_hits'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
