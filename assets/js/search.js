(() => {
	'use strict';

	const config = window.ioblogSearch || {};
	const modal = document.querySelector('.io-search-modal');
	const input = modal?.querySelector('.io-search-form__input');
	const results = modal?.querySelector('.io-search-results');
	const status = modal?.querySelector('.io-search-status');
	const openButtons = document.querySelectorAll('.io-search-open');
	const closeButtons = modal?.querySelectorAll('.io-search-close, .io-search-modal__backdrop') || [];
	let timer;
	let controller;
	let revision = 0;
	let opener;

	if (!modal || !input || !results || !status) return;

	const escapeHtml = (value) => String(value || '').replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);

	const close = () => {
		window.clearTimeout(timer);
		controller?.abort();
		revision++;
		modal.hidden = true;
		document.body.classList.remove('io-modal-open');
		opener?.focus();
	};

	const open = () => {
		opener = document.activeElement;
		modal.hidden = false;
		document.body.classList.add('io-modal-open');
		input.focus();
		search();
	};

	openButtons.forEach((button) => button.addEventListener('click', open));
	closeButtons.forEach((button) => button.addEventListener('click', close));
	document.addEventListener('keydown', (event) => {
		if (modal.hidden) return;
		if (event.key === 'Escape') close();
		// Tab остаётся внутри диалога; после закрытия фокус возвращается на кнопку поиска.
		if (event.key !== 'Tab') return;
		const controls = Array.from(modal.querySelectorAll('.io-search-modal__dialog a[href], .io-search-modal__dialog button, .io-search-modal__dialog input'))
			.filter((element) => !element.disabled && element.getClientRects().length);
		const first = controls[0];
		const last = controls[controls.length - 1];
		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last?.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first?.focus();
		}
	});

	const render = (items) => {
		results.innerHTML = items.map((item) => {
			const media = item.thumbnail ? `<img src="${escapeHtml(item.thumbnail)}" alt="" loading="lazy">` : '<span class="io-search-result__placeholder"></span>';
			return `<a class="io-search-result" href="${escapeHtml(item.url)}">${media}<span><small class="io-search-result__meta">${escapeHtml(item.category)} · ${escapeHtml(item.date)}</small><strong>${escapeHtml(item.title)}</strong></span></a>`;
		}).join('');
		results.hidden = items.length === 0;
	};

	const search = async () => {
		const query = input.value.trim();
		const requestRevision = ++revision;
		if (query.length < Number(config.minChars || 3)) {
			controller?.abort();
			render([]);
			status.textContent = config.labels?.empty || '';
			return;
		}

		controller?.abort();
		controller = new AbortController();
		status.textContent = config.labels?.loading || '';
		const url = new URL(config.endpoint, window.location.origin);
		url.searchParams.set('action', 'ioblog_live_search');
		url.searchParams.set('q', query);

		try {
			const response = await fetch(url, { signal: controller.signal });
			if (!response.ok) throw new Error(`HTTP ${response.status}`);
			const payload = await response.json();
			// Ответ предыдущего запроса не должен появиться после изменения строки или закрытия окна.
			if (requestRevision !== revision || modal.hidden) return;
			const items = Array.isArray(payload.items) ? payload.items : [];
			render(items);
			status.textContent = items.length ? (config.labels?.count || '%d matching articles').replace('%d', String(items.length)) : (config.labels?.none || '');
		} catch (error) {
			if (error.name !== 'AbortError' && requestRevision === revision && !modal.hidden) {
				render([]);
				status.textContent = config.labels?.error || '';
			}
		}
	};

	input.addEventListener('input', () => {
		window.clearTimeout(timer);
		// Отменяем старый запрос сразу, а не после паузы debounce: иначе он успевает показать чужие результаты.
		controller?.abort();
		revision++;
		render([]);
		if (input.value.trim().length < Number(config.minChars || 3)) {
			status.textContent = config.labels?.empty || '';
			return;
		}
		status.textContent = config.labels?.loading || '';
		timer = window.setTimeout(search, 220);
	});
})();
