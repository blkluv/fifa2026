/* global wc26Admin, jQuery */
(function($) {
	'use strict';

	// ── CSV Import ────────────────────────────────────────────────
	$(document).on('submit', '#wc26-csv-form', function(e) {
		e.preventDefault();

		const $form   = $(this);
		const $btn    = $form.find('[type=submit]').prop('disabled', true).text('Importing…');
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
					$status.text('Import completed: ' + res.data.imported + ' rows').css('color', 'green');
				} else {
					$status.text((res.data || 'Error')).css('color', 'red');
				}
			},
			error() {
				$status.text('Network error').css('color', 'red');
			},
			complete() {
				$btn.prop('disabled', false).text('Import CSV');
			}
		});
	});

	// ── Submit Result (inline on market list) ──────────────────────
	$(document).on('click', '.wc26-submit-result-btn', function() {
		const $btn    = $(this).prop('disabled', true);
		const marketId = $btn.data('market');
		const $row    = $btn.closest('tr');
		const finalPrice = $row.find('.wc26-res-final-price').val();
		const marketTrend = $row.find('.wc26-res-trend').val();

		fetch(wc26Admin.apiBase + '/admin/markets/' + marketId + '/result', {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   wc26Admin.nonce,
			},
			body: JSON.stringify({
				final_price: parseFloat(finalPrice),
				price_change_pct: 0, // Will be calculated server-side
				market_trend: marketTrend,
			})
		})
		.then(async r => ({ ok: r.ok, data: await r.json().catch(() => ({})) }))
		.then(({ ok, data }) => {
			if (ok) {
				$row.find('.wc26-match-status').text('Settled').addClass('wc26-status-settled').removeClass('wc26-status-pending');
				alert('Market settled and scores calculated.');
			} else {
				alert(data.message || 'Error');
			}
		})
		.catch(() => alert('Network error'))
		.finally(() => $btn.prop('disabled', false));
	});

	// ── Seed Sample Properties ──────────────────────────────────────
	$(document).on('click', '#wc26-seed-sample-properties', function() {
		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-seed-properties-status').text('Seeding…').css('color', '#555');

		$.post(wc26Admin.ajaxUrl, {
			action: 'wc26_seed_sample_properties',
			nonce: wc26Admin.nonce
		})
		.done(function(res) {
			if (res && res.success) {
				$status.text('Seeded: ' + res.data.imported + ' properties').css('color', 'green');
				window.location.reload();
			} else {
				$status.text((res && res.data) ? res.data : 'Error').css('color', 'red');
			}
		})
		.fail(function() {
			$status.text('Network error').css('color', 'red');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});

	// ── Reset and Import Real Estate Data ──────────────────────────
	$(document).on('click', '#wc26-reset-import-realestate', function() {
		if (!window.confirm('This will delete ALL current data. Continue?')) return;

		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-reset-import-status').text('Resetting and importing…').css('color', '#555');

		$.post(wc26Admin.ajaxUrl, {
			action: 'wc26_reset_import_realestate',
			nonce: wc26Admin.nonce
		})
		.done(function(res) {
			if (res && res.success) {
				$status.text('Import complete. Properties: ' + res.data.properties + ' | Regions: ' + res.data.regions + ' | Markets: ' + res.data.markets).css('color', 'green');
				window.location.reload();
			} else {
				$status.text((res && res.data) ? res.data : 'Error').css('color', 'red');
			}
		})
		.fail(function() {
			$status.text('Network error').css('color', 'red');
		})
		.always(function() {
			$btn.prop('disabled', false);
		});
	});

	// ── Update Market Data from API ─────────────────────────────────
	$(document).on('click', '#wc26-update-market-data', function() {
		const $btn    = $(this).prop('disabled', true);
		const $status = $('#wc26-update-status').text('Updating…').css('color', '#555');

		fetch(wc26Admin.apiBase + '/admin/markets/update-data', {
			method:  'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': wc26Admin.nonce },
			body:    JSON.stringify({}),
		})
		.then(async function(r) { return { ok: r.ok, data: await r.json().catch(function() { return {}; }) }; })
		.then(function(res) {
			if (res.ok) {
				$status.text('Updated: ' + (res.data.updated || 0) + ' markets').css('color', 'green');
			} else {
				$status.text(res.data.message || 'Error').css('color', 'red');
			}
		})
		.catch(function() { $status.text('Network error').css('color', 'red'); })
		.finally(function() { $btn.prop('disabled', false); });
	});

	// ── Save Scoring Rules ──────────────────────────────────────────
	$(document).on('click', '#wc26-save-scoring-rules', function() {
		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-save-scoring-status').text('Saving…').css('color', '#555');

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
				$status.text('Saved' + (u || i ? (' (Updated: ' + u + ', New: ' + i + ')') : '')).css('color', 'green');
			} else {
				$status.text(res.data.message || 'Error').css('color', 'red');
			}
		})
		.catch(function() { $status.text('Network error').css('color', 'red'); })
		.finally(function() { $btn.prop('disabled', false); });
	});

	// ── Submit Chainlink Report ─────────────────────────────────────
	$(document).on('click', '.wc26-submit-chainlink-report', function() {
		const $btn = $(this).prop('disabled', true);
		const reportId = $btn.data('report');
		const $row = $btn.closest('tr');

		if (!confirm('Submit this Chainlink report to the DON?')) {
			$btn.prop('disabled', false);
			return;
		}

		fetch(wc26Admin.apiBase + '/chainlink/report/submit', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wc26Admin.nonce,
			},
			body: JSON.stringify({ report_id: reportId })
		})
		.then(async r => ({ ok: r.ok, data: await r.json().catch(() => ({})) }))
		.then(({ ok, data }) => {
			if (ok) {
				$row.find('.wc26-report-status').text('Submitted').css('color', '#0073aa');
				alert('Report submitted successfully. Transaction hash: ' + (data.tx_hash || 'pending'));
			} else {
				alert(data.message || 'Error submitting report');
			}
		})
		.catch(() => alert('Network error'))
		.finally(() => $btn.prop('disabled', false));
	});

	// ── Save Chainlink CRE Settings ────────────────────────────────
	$(document).on('click', '#wc26-save-chainlink-settings', function() {
		const $btn = $(this).prop('disabled', true);
		const $status = $('#wc26-chainlink-settings-status').text('Saving…').css('color', '#555');

		const data = {
			don_id: $('#wc26_don_id').val(),
			auto_report: $('#wc26_auto_report').is(':checked') ? 1 : 0,
		};

		fetch(wc26Admin.apiBase + '/admin/chainlink/settings', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wc26Admin.nonce,
			},
			body: JSON.stringify(data)
		})
		.then(async function(r) { return { ok: r.ok, data: await r.json().catch(function() { return {}; }) }; })
		.then(function(res) {
			if (res.ok) {
				$status.text('Settings saved successfully').css('color', 'green');
			} else {
				$status.text(res.data.message || 'Error').css('color', 'red');
			}
		})
		.catch(function() { $status.text('Network error').css('color', 'red'); })
		.finally(function() { $btn.prop('disabled', false); });
	});

	// ── Confirm Delete (with better UX) ─────────────────────────────
	$(document).on('click', '.wc26-delete-confirm', function(e) {
		if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
			e.preventDefault();
		}
	});

})(jQuery);
