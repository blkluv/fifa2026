<?php
/**
 * Admin Template: Dashboard
 *
 * @package WC26Predictor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$stats = [
	'teams'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_teams" ),
	'matches'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_matches" ),
	'predictions' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_predictions" ),
	'users'       => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_leaderboards" ),
	'leagues'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_mini_leagues" ),
	'badges'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wc26_user_badges" ),
];
?>
<div class="wrap wc26-admin-wrap">
	<div class="wc26-admin-header">
		<h1><?php esc_html_e( 'داشبورد پیش‌بینی جام جهانی ۲۰۲۶', 'wc26-predictor' ); ?></h1>
		<span style="color:#888; font-size:0.85rem;"><?php echo esc_html( 'v' . WC26_VERSION ); ?></span>
	</div>

	<div class="wc26-stat-grid">
		<?php
		$cards = [
			[ __( 'تعداد تیم‌ها', 'wc26-predictor' ),         $stats['teams'],       '' ],
			[ __( 'تعداد مسابقات', 'wc26-predictor' ),        $stats['matches'],     '' ],
			[ __( 'تعداد پیش‌بینی‌ها', 'wc26-predictor' ),    $stats['predictions'], '' ],
			[ __( 'کاربران امتیازدار', 'wc26-predictor' ),    $stats['users'],       '' ],
			[ __( 'تعداد لیگ‌ها', 'wc26-predictor' ),         $stats['leagues'],     '' ],
			[ __( 'نشان‌های کسب‌شده', 'wc26-predictor' ),     $stats['badges'],      '' ],
		];
		foreach ( $cards as [ $label, $val, $icon ] ) :
		?>
		<div class="wc26-stat-card">
			<h3><?php echo esc_html( trim( $icon . ' ' . $label ) ); ?></h3>
			<div class="wc26-stat-val"><?php echo esc_html( number_format( $val ) ); ?></div>
		</div>
		<?php endforeach; ?>
	</div>

	<h2><?php esc_html_e( 'دسترسی سریع', 'wc26-predictor' ); ?></h2>
	<p>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-matches' ) ); ?>"><?php esc_html_e( 'مدیریت مسابقات', 'wc26-predictor' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-csv-import' ) ); ?>"><?php esc_html_e( 'ایمپورت CSV', 'wc26-predictor' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc26-leaderboard' ) ); ?>"><?php esc_html_e( 'جدول امتیازات', 'wc26-predictor' ); ?></a>
	</p>

	<h2><?php esc_html_e( 'دیتای واقعی مسابقات', 'wc26-predictor' ); ?></h2>
	<p style="max-width: 900px;">
		<?php esc_html_e( 'این عملیات تمام دیتای فعلی (تیم‌ها، گروه‌ها، مسابقات، پیش‌بینی‌ها، لیدربورد و…) را پاک می‌کند و سپس برنامه مسابقات جام جهانی ۲۰۲۶ را به صورت اتوماتیک وارد می‌کند.', 'wc26-predictor' ); ?>
	</p>
	<p>
		<button type="button" class="button button-primary" id="wc26-reset-import-openfootball">
			<?php esc_html_e( 'پاکسازی کامل و ایمپورت دیتای جام جهانی ۲۰۲۶', 'wc26-predictor' ); ?>
		</button>
		<span id="wc26-reset-import-status" style="margin-right:1rem;font-weight:600;"></span>
	</p>

	<h2><?php esc_html_e( 'فارسی‌سازی نام تیم‌ها', 'wc26-predictor' ); ?></h2>
	<p style="max-width: 900px;">
		<?php esc_html_e( 'این دکمه نام تمام تیم‌های وارد شده (انگلیسی) را به فارسی تبدیل می‌کند و کد FIFA را اصلاح می‌کند. بعد از هر ایمپورت مجدداً اجرا کنید.', 'wc26-predictor' ); ?>
	</p>
	<p>
		<button type="button" class="button button-secondary" id="wc26-localize-teams">
			<?php esc_html_e( 'تبدیل نام تیم‌ها به فارسی', 'wc26-predictor' ); ?>
		</button>
		<span id="wc26-localize-status" style="margin-right:1rem;font-weight:600;"></span>
	</p>

	<h2><?php esc_html_e( 'REST API', 'wc26-predictor' ); ?></h2>
	<p><?php esc_html_e( 'آدرس پایه:', 'wc26-predictor' ); ?>
		<code><?php echo esc_html( rest_url( 'wc26/v1' ) ); ?></code>
	</p>
	<ul>
		<?php
		$endpoints = [
			'GET /matches', 'GET /groups', 'GET /standings?group_id=N',
			'POST /predict', 'GET /my-predictions',
			'GET /leaderboard', 'POST /leagues', 'POST /leagues/join',
			'GET /leagues/{id}/leaderboard',
			'GET /notifications', 'POST /notifications/read',
			'POST /admin/matches/{id}/result (فقط مدیر)',
		];
		foreach ( $endpoints as $ep ) :
		?>
		<li><code><?php echo esc_html( rest_url( 'wc26/v1/' ) . $ep ); ?></code></li>
		<?php endforeach; ?>
	</ul>
</div>
