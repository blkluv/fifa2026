/* global wc26Admin, jQuery */
(function($) {
	'use strict';

	// ── CSV Import ────────────────────────────────────────────────
	$(document).on('submit', '#wc26-csv-form', function(e) {
		e.preventDefault();

		const $form   = $(this);
		const $btn    = $form.find('[type=submit]').prop('disabled', true).text('در حال ایمپورت…');
		const $status = $('#wc26-import-status');
		const data    = new FormData(this);

		data.append('action', 'wc26_import_csv');
		data.append('nonce',  wc26Admin.nonce);

		$.ajax({
			url:         wc26Admin.ajaxUrl,
			type:        'POST',
			data:        data,
			processData: false,
			contentType: false,
			success(res) {
				if (res.success) {
					$status.text('ایمپورت انجام شد: ' + res.data.imported + ' ردیف').css('color', 'green');
				} else {
					$status.text((res.data || 'خطا')).css('color', 'red');
				}
			},
			error() {
				$status.text('خطای شبکه').css('color', 'red');
			},
			complete() {
				$btn.prop('disabled', false).text('ایمپورت CSV');
			}
		});
	});

	// ── Submit Result (inline on match list) ──────────────────────
	$(document).on('click', '.wc26-submit-result-btn', function() {
		const $btn    = $(this).prop('disabled', true);
		const matchId = $btn.data('match');
		const $row    = $btn.closest('tr');
		const homeScore = $row.find('.wc26-res-home').val();
		const awayScore = $row.find('.wc26-res-away').val();

		fetch(wc26Admin.apiBase + '/admin/matches/' + matchId + '/result', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   wc26Admin.nonce,
			},
			body: JSON.stringify({
				home_score: parseInt(homeScore, 10),
				away_score: parseInt(awayScore, 10),
			})
		})
		.then(async r => ({ ok: r.ok, data: await r.json().catch(() => ({})) }))
		.then(({ ok, data }) => {
			if (ok) {
				$row.find('.wc26-match-status').text('پایان‌یافته').addClass('wc26-status-finished').removeClass('wc26-status-scheduled');
				alert('نتیجه ثبت شد و امتیازها محاسبه شدند.');
			} else {
				alert(data.message || 'خطا');
			}
		})
		.catch(() => alert('خطای شبکه'))
		.finally(() => $btn.prop('disabled', false));
	});

	$(document).on('click', '#wc26-seed-sample-teams', function() {
		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-seed-teams-status').text('در حال درون‌ریزی…').css('color', '#555');

		$.post(wc26Admin.ajaxUrl, {
			action: 'wc26_seed_sample_teams',
			nonce: wc26Admin.nonce
		})
		.done(function(res) {
			if (res && res.success) {
				$status.text('درون‌ریزی انجام شد: ' + res.data.imported + ' ردیف').css('color', 'green');
				window.location.reload();
			} else {
				$status.text((res && res.data) ? res.data : 'خطا').css('color', 'red');
			}
		})
		.fail(function() {
			$status.text('خطای شبکه').css('color', 'red');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});

	$(document).on('click', '#wc26-reset-import-openfootball', function() {
		if (!window.confirm('این عملیات همه دیتای فعلی را پاک می‌کند. ادامه می‌دهید؟')) return;

		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-reset-import-status').text('در حال پاکسازی و ایمپورت…').css('color', '#555');

		$.post(wc26Admin.ajaxUrl, {
			action: 'wc26_reset_import_openfootball_2026',
			nonce: wc26Admin.nonce
		})
		.done(function(res) {
			if (res && res.success) {
				$status.text('ایمپورت انجام شد. تیم‌ها: ' + res.data.teams + ' | گروه‌ها: ' + res.data.groups + ' | مسابقات: ' + res.data.matches).css('color', 'green');
				window.location.reload();
			} else {
				$status.text((res && res.data) ? res.data : 'خطا').css('color', 'red');
			}
		})
		.fail(function() {
			$status.text('خطای شبکه').css('color', 'red');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});

	$(document).on('click', '#wc26-localize-teams', function() {
		const $btn    = $(this).prop('disabled', true);
		const $status = $('#wc26-localize-status').text('در حال تبدیل…').css('color', '#555');

		fetch(wc26Admin.apiBase + '/admin/teams/localize-fa', {
			method:  'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': wc26Admin.nonce },
			body:    JSON.stringify({}),
		})
		.then(async function(r) { return { ok: r.ok, data: await r.json().catch(function() { return {}; }) }; })
		.then(function(res) {
			if (res.ok) {
				$status.text('تبدیل انجام شد: ' + (res.data.updated || 0) + ' تیم آپدیت شد').css('color', 'green');
			} else {
				$status.text(res.data.message || 'خطا').css('color', 'red');
			}
		})
		.catch(function() { $status.text('خطای شبکه').css('color', 'red'); })
		.finally(function() { $btn.prop('disabled', false); });
	});

	$(document).on('click', '#wc26-save-scoring-rules', function() {
		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-save-scoring-status').text('در حال ذخیره…').css('color', '#555');

		const rules = [];
		$('#wc26-scoring-table tbody tr').each(function() {
			const $tr = $(this);
			const ruleKey = String($tr.find('.wc26-sr-key').text() || '').trim();
			if (!ruleKey) return;
			rules.push({
				rule_key: ruleKey,
				label: String($tr.find('.wc26-sr-label').val() || '').trim(),
				points: parseInt(String($tr.find('.wc26-sr-points').val() || '0'), 10) || 0,
				description: String($tr.find('.wc26-sr-desc').val() || '').trim(),
			});
		});

		fetch(wc26Admin.apiBase + '/admin/scoring-rules', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wc26Admin.nonce,
			},
			body: JSON.stringify({ rules: rules })
		})
		.then(async function(r) { return { ok: r.ok, data: await r.json().catch(function() { return {}; }) }; })
		.then(function(res) {
			if (res.ok) {
				const u = res.data.updated || 0;
				const i = res.data.inserted || 0;
				$status.text('ذخیره شد' + (u || i ? (' (آپدیت: ' + u + '، جدید: ' + i + ')') : '')).css('color', 'green');
			} else {
				$status.text(res.data.message || 'خطا').css('color', 'red');
			}
		})
		.catch(function() { $status.text('خطای شبکه').css('color', 'red'); })
		.finally(function() { $btn.prop('disabled', false); });
	});

})(jQuery);
