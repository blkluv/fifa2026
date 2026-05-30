<?php
/**
 * Admin Template: Winners / Prediction Leaderboard
 *
 * @package WC26Predictor
 * @var array $rows        User leaderboard rows
 * @var array $matchDetail Per-match prediction summary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rank = 0;
?>
<div class="wrap wc26-admin-wrap">
	<div class="wc26-admin-header">
		<h1><?php esc_html_e( 'برندگان پیش‌بینی', 'wc26-predictor' ); ?></h1>
		<span style="color:#888; font-size:0.85rem;">
			<?php echo esc_html( sprintf( __( '%d شرکت‌کننده', 'wc26-predictor' ), count( $rows ) ) ); ?>
		</span>
	</div>

	<?php if ( empty( $rows ) ) : ?>
		<div style="padding:24px; background:#fff3cd; border:1px solid #ffc107; border-radius:6px; margin-bottom:20px;">
			<strong><?php esc_html_e( 'هنوز هیچ مسابقه‌ای نهایی نشده یا پیش‌بینی ثبت نشده است.', 'wc26-predictor' ); ?></strong>
		</div>
	<?php else : ?>

	<!-- ── رتبه‌بندی کلی ──────────────────────────────────────────────── -->
	<h2><?php esc_html_e( 'رتبه‌بندی کلی', 'wc26-predictor' ); ?></h2>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table widefat striped">
			<thead>
				<tr>
					<th style="width:48px"><?php esc_html_e( 'رتبه', 'wc26-predictor' ); ?></th>
					<th><?php esc_html_e( 'نام کاربر', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'امتیاز', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'دقیق', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'تفاضل', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'برد/مساوی', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'پیش‌بینی شده', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'دقت', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) :
					$rank++;
					$pts      = (int) $row['total_points'];
					$exact    = (int) $row['exact_hits'];
					$diff     = (int) $row['goal_diff_hits'];
					$winner   = (int) $row['winner_hits'];
					$total    = (int) $row['total_scored'];
					$correct  = (int) $row['correct_scored'];
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
					<td style="text-align:center; color:#7c4dff; font-weight:700;"><?php echo esc_html( $diff ); ?></td>
					<td style="text-align:center; color:#e07600; font-weight:700;"><?php echo esc_html( $winner ); ?></td>
					<td style="text-align:center; color:#555;"><?php echo esc_html( $total ); ?></td>
					<td style="text-align:center;">
						<div style="display:flex; align-items:center; gap:6px; justify-content:center;">
							<div style="height:8px; width:80px; background:#eee; border-radius:4px; overflow:hidden;">
								<div style="height:100%; width:<?php echo esc_attr( $accuracy ); ?>%; background:<?php echo $accuracy >= 60 ? '#00a32a' : ( $accuracy >= 40 ? '#e07600' : '#d63638' ); ?>; border-radius:4px;"></div>
							</div>
							<span style="font-weight:700; font-size:0.85rem;"><?php echo esc_html( $accuracy ); ?>٪</span>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php endif; ?>

	<!-- ── خلاصه هر مسابقه ────────────────────────────────────────────── -->
	<?php if ( ! empty( $matchDetail ) ) : ?>
	<h2 style="margin-top:32px;"><?php esc_html_e( 'آمار پیش‌بینی به تفکیک مسابقه', 'wc26-predictor' ); ?></h2>
	<div class="wc26-table-wrap">
		<table class="wc26-admin-table widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'مسابقه', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'نتیجه', 'wc26-predictor' ); ?></th>
					<th style="text-align:center"><?php esc_html_e( 'کل پیش‌بینی', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#00a32a"><?php esc_html_e( 'دقیق', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#7c4dff"><?php esc_html_e( 'تفاضل', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#e07600"><?php esc_html_e( 'برد/مساوی', 'wc26-predictor' ); ?></th>
					<th style="text-align:center; color:#d63638"><?php esc_html_e( 'غلط', 'wc26-predictor' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $matchDetail as $m ) :
					$total  = (int) $m['total_preds'];
					$date   = $m['kickoff_at'] ? wp_date( 'Y/m/d H:i', strtotime( $m['kickoff_at'] ) ) : '—';
				?>
				<tr>
					<td>
						<div style="font-weight:700;"><?php echo esc_html( ( $m['home_team'] ?? '—' ) . ' – ' . ( $m['away_team'] ?? '—' ) ); ?></div>
						<div style="font-size:0.8rem; color:#888;"><?php echo esc_html( $date ); ?></div>
					</td>
					<td style="text-align:center; font-weight:800; font-size:1.1rem;">
						<?php echo esc_html( $m['real_home'] . ' – ' . $m['real_away'] ); ?>
					</td>
					<td style="text-align:center;"><?php echo esc_html( $total ); ?></td>
					<td style="text-align:center; font-weight:700; color:#00a32a;"><?php echo esc_html( $m['exact_count'] ); ?></td>
					<td style="text-align:center; font-weight:700; color:#7c4dff;"><?php echo esc_html( $m['diff_count'] ); ?></td>
					<td style="text-align:center; font-weight:700; color:#e07600;"><?php echo esc_html( $m['winner_count'] ); ?></td>
					<td style="text-align:center; font-weight:700; color:#d63638;"><?php echo esc_html( $m['miss_count'] ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>
