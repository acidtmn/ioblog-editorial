/* Перенос настроек: файл проверяется сервером, а интерфейс не исполняет его HTML или JavaScript. */
(() => {
	'use strict';
	const config = window.ioblogSettingsTransfer;
	const form = document.getElementById('io-transfer-upload');
	if (!config || !form) return;
	const file = document.getElementById('io-transfer-file');
	const status = document.getElementById('io-transfer-status');
	const preview = document.getElementById('io-transfer-preview');
	const rows = document.getElementById('io-transfer-rows');
	const apply = document.getElementById('io-transfer-apply');
	const trust = document.getElementById('io-transfer-trust');
	const checks = Array.from(preview.querySelectorAll('[name="transfer-group"]'));
	let token = '';
	let changes = [];
	let busy = false;
	let revision = 0;
	const selected = () => checks.filter(check => check.checked).map(check => check.value);
	const relevant = () => changes.filter(row => selected().includes(row.group));
	const update = () => { apply.disabled = busy || !token || !trust.checked || !relevant().length; };
	const message = (text, error = false) => {
		status.textContent = text;
		status.classList.toggle('is-error', error);
	};
	const reset = () => {
		revision += 1;
		token = '';
		changes = [];
		preview.hidden = true;
		trust.checked = false;
		update();
	};
	const request = async (data) => {
		data.set('nonce', config.nonce);
		const response = await fetch(config.url, {method: 'POST', body: data, credentials: 'same-origin', cache: 'no-store'});
		let result;
		try { result = await response.json(); } catch { throw new Error(config.network); }
		if (!response.ok || !result.success) throw new Error(result?.data?.message || config.network);
		return result.data;
	};
	const render = () => {
		rows.replaceChildren();
		const headings = Array.from(preview.querySelectorAll('th')).map(th => th.textContent);
		// Даже заголовки полей и адрес сайта выводятся textContent, не innerHTML из файла.
		for (const row of relevant()) {
			const tr = document.createElement('tr');
			for (const [index, value] of [row.label, row.before, row.after].entries()) {
				const td = document.createElement('td');
				td.textContent = value;
				td.dataset.label = headings[index];
				if (index === 0) {
					const key = document.createElement('small');
					key.textContent = row.key;
					td.append(key);
				}
				tr.append(td);
			}
			rows.append(tr);
		}
		update();
	};
	file.addEventListener('change', () => { reset(); message(''); });
	checks.forEach(check => check.addEventListener('change', render));
	trust.addEventListener('change', update);
	form.addEventListener('submit', async event => {
		event.preventDefault();
		if (busy) return;
		reset();
		const backup = file.files?.[0];
		if (!backup || backup.size > 1048576 || !/\.json$/i.test(backup.name)) { message(config.invalid, true); return; }
		const currentRevision = revision;
		busy = true;
		form.querySelector('button').disabled = true;
		message(config.loading);
		try {
			const data = new FormData();
			data.set('action', 'ioblog_transfer_preview');
			data.set('backup', backup);
			const result = await request(data);
			// Выбор нового файла во время загрузки отменяет применение результата старого запроса.
			if (revision !== currentRevision) return;
			token = result.token;
			changes = result.rows;
			checks.forEach(check => { check.checked = true; });
			document.getElementById('io-transfer-source').textContent = `${config.source} ${result.source}${result.pro_active ? '' : ' ' + config.pro}`;
			const skipped = document.getElementById('io-transfer-skipped');
			skipped.hidden = !result.skipped.length;
			skipped.textContent = result.skipped.length ? `${config.skipped} ${result.skipped.join(', ')}` : '';
			preview.hidden = false;
			render();
			message(changes.length ? config.preview.replace('%d', changes.length) : config.none);
		} catch (error) { if (revision === currentRevision) message(error.message || config.network, true); }
		finally { busy = false; form.querySelector('button').disabled = false; update(); }
	});
	apply.addEventListener('click', async () => {
		if (apply.disabled) return;
		busy = true;
		file.disabled = true;
		checks.forEach(check => { check.disabled = true; });
		update();
		try {
			const data = new FormData();
			data.set('action', 'ioblog_transfer_confirm');
			data.set('token', token);
			selected().forEach(group => data.append('groups[]', group));
			const result = await request(data);
			reset();
			message(config.success.replace('%d', result.count));
		} catch (error) {
			// При потерянном ответе не повторяем импорт вслепую: сервер мог уже сохранить настройки.
			reset();
			message(error.message || config.network, true);
		} finally {
			busy = false;
			file.disabled = false;
			checks.forEach(check => { check.disabled = false; });
			update();
		}
	});
})();
