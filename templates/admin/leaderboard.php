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
	<h1><?php esc_html_e( 'Leaderboard (Global)', 'wc26-predictor' ); ?></h1>

	<div class="wc26-table-wrap">
		<table class="wc26-admin-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Rank', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Player', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Points', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Exact', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Trend', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'Range', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $rows ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No data available yet.', 'wc26-predictor' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $rows as $i => $row ) : ?>
						<tr class="<?php echo $i < 3 ? 'wc26-lb-rank-' . ( $i + 1 ) : ''; ?>">
							<td><?php echo esc_html( $row['rank_position'] ?? ( $i + 1 ) ); ?></td>
							<td><?php echo esc_html( $row['display_name'] ); ?></td>
							<td><strong><?php echo esc_html( number_format( (int) $row['total_points'] ) ); ?></strong></td>
							<td><?php echo esc_html( $row['exact_hits'] ?? 0 ); ?></td>
							<td><?php echo esc_html( $row['trend_hits'] ?? 0 ); ?></td>
							<td><?php echo esc_html( $row['range_hits'] ?? 0 ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
