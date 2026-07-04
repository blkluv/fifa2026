<?php
/**
 * Admin Template: Winners / Prediction Leaderboard - Real Estate
 *
 * @package WC26Predictor
 * @var array $rows        User leaderboard rows
 * @var array $marketDetail Per-market prediction summary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rank = 0;
?>
<div class="wrap wc26-admin-wrap">
	<div class="wc26-admin-header">
		<h1><?php esc_html_e( 'Prediction Winners', 'wc26-predictor' ); ?></h1>
		<span style="color:#888; font-size:0.85rem;">
			<?php echo esc_html( sprintf( __( '%d participants', 'wc26-predictor' ), count( $rows ) ) ); ?>
		</span>
	</div>

	<?php if ( empty( $rows ) ) : ?>
		<div style="padding:24px; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; margin-bottom:20px;">
			<strong><?php esc_html_e( 'No markets have been settled yet or no predictions submitted.', 'wc26-predictor' ); ?></strong>
		</div>
	<?php else : ?>

	<!-- ── Overall Ranking ──────────────────────────────────────────────── -->
	<h2><?php esc_html_e( 'Overall Ranking', 'wc26-predictor' ); ?></h2>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table widefat striped">
			<thead>
				<tr>
					<th style="width:48px"><?php esc_html_e( 'Rank', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'User', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Points', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Exact', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Trend', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Range', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Total', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Accuracy', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) :
					$rank++;
					$pts      = (int) $row['total_points'];
					$exact    = (int) $row['exact_hits'];
					$trend    = (int) ( $row['trend_hits'] ?? 0 );
					$range    = (int) ( $row['range_hits'] ?? 0 );
					$total    = (int) ( $row['total_scored'] ?? 0 );
					$correct  = (int) ( $row['correct_scored'] ?? 0 );
					$accuracy = $total > 0 ? round( 100 * $correct / $total ) : 0;

					$rowClass = '';
					if ( $rank === 1 ) $rowClass = 'wc26-lb-rank-1';
					elseif ( $rank === 2 ) $rowClass = 'wc26-lb-rank-2';
					elseif ( $rank === 3 ) $rowClass = 'wc26-lb-rank-3';
				?>
				<tr class="<?php echo esc_attr( $rowClass ); ?>">
					<td style="font-weight:800; font-size:1.1rem; text-align:center;">
						<?php if ( $rank === 1 ) : ?>🥇
						<?php elseif ( $rank === 2 ) : ?>🥈
						<?php elseif ( $rank === 3 ) : ?>🥉
						<?php else : echo esc_html( $rank ); endif; ?>
					</td>
					<td>
						<strong><?php echo esc_html( $row['display_name'] ); ?></strong>
						<span style="color:#888; font-size:0.85em; margin-right:6px;">@<?php echo esc_html( $row['user_login'] ); ?></span>
					</td>
					<td style="text-align:center; font-weight:800; font-size:1.1rem; color:#0073aa;">
						<?php echo esc_html( number_format( $pts ) ); ?>
					</td>
					<td style="text-align:center; color:#00a32a; font-weight:700;"><?php echo esc_html( $exact ); ?></td>
					<td style="text-align:center; color:#7c4dff; font-weight:700;"><?php echo esc_html( $trend ); ?></td>
					<td style="text-align:center; color:#e07600; font-weight:700;"><?php echo esc_html( $range ); ?></td>
					<td style="text-align:center; color:#555;"><?php echo esc_html( $total ); ?></td>
					<td style="text-align:center;">
						<div style="display:flex; align-items:center; gap:6px; justify-content:center;">
							<div style="height:8px; width:80px; background:#eee; border-radius:4px; overflow:hidden;">
								<div style="height:100%; width:<?php echo esc_attr( $accuracy ); ?>%; background:<?php echo $accuracy >= 60 ? '#00a32a' : ( $accuracy >= 40 ? '#e07600' : '#d63638' ); ?>; border-radius:4px;"></div>
							</div>
							<span style="font-weight:700; font-size:0.85rem;"><?php echo esc_html( $accuracy ); ?>%</span>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php endif; ?>

	<!-- ── Per-Market Summary ────────────────────────────────────────────── -->
	<?php if ( ! empty( $marketDetail ) ) : ?>
	<h2 style="margin-top:32px;"><?php esc_html_e( 'Prediction Stats by Market', 'wc26-predictor' ); ?></h2>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Market', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Result', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'Total Preds', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#00a32a"><?php esc_html_e( 'Exact', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#7c4dff"><?php esc_html_e( 'Trend', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#e07600"><?php esc_html_e( 'Range', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#d63638"><?php esc_html_e( 'Miss', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $marketDetail as $m ) :
					$total  = (int) $m['total_preds'];
					$date   = $m['forecast_date'] ? wp_date( 'Y/m/d', strtotime( $m['forecast_date'] ) ) : '—';
					$trendEmoji = $m['market_trend'] === 'increase' ? '📈' : ( $m['market_trend'] === 'decrease' ? '📉' : '➡️' );
				?>
				<tr>
					<td>
						<div style="font-weight:700;"><?php echo esc_html( $m['region_name'] ?? '—' ); ?></div>
						<div style="font-size:0.8rem; color:#888;"><?php echo esc_html( $date ); ?></div>
					</td>
					<td style="text-align:center; font-weight:800; font-size:1.1rem;">
						$<?php echo esc_html( number_format( (float) $m['final_price'] ?? 0 ) ); ?>
						<small style="display:block; font-size:0.7rem;"><?php echo esc_html( $trendEmoji . ' ' . ( $m['price_change_pct'] ?? 0 ) . '%' ); ?></small>
					</td>
					<td style="text-align:center;"><?php echo esc_html( $total ); ?></td>
					<td style="text-align:center; font-weight:700; color:#00a32a;"><?php echo esc_html( $m['exact_count'] ?? 0 ); ?></td>
					<td style="text-align:center; font-weight:700; color:#7c4dff;"><?php echo esc_html( $m['trend_count'] ?? 0 ); ?></td>
					<td style="text-align:center; font-weight:700; color:#e07600;"><?php echo esc_html( $m['range_count'] ?? 0 ); ?></td>
					<td style="text-align:center; font-weight:700; color:#d63638;"><?php echo esc_html( $m['miss_count'] ?? 0 ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>
