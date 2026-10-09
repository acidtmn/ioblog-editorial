(() => {
	'use strict';

	const { __ } = window.wp.i18n;
	const root = document.documentElement;
	const themeButton = document.querySelector('.io-theme-toggle');

	if (themeButton) {
		themeButton.addEventListener('click', () => {
			const current = root.dataset.theme;
			const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
			const next = current === 'dark' || (current === 'system' && systemDark) ? 'light' : 'dark';
			root.dataset.theme = next;
			try {
				localStorage.setItem('ioblog-theme', next);
			} catch (error) {
				// Приватные настройки браузера могут запретить хранилище; переключение всё равно работает.
			}
		});
	}

	const copyButton = document.querySelector('.io-copy-link');
	if (copyButton && navigator.clipboard) {
		copyButton.addEventListener('click', async () => {
			// Пиктограмма остаётся на месте, а состояние подтверждается цветом и доступной подписью.
			try {
				await navigator.clipboard.writeText(window.location.href);
			} catch (error) {
				// Отказ в доступе не является успешным копированием и не должен оставлять rejected Promise.
				window.prompt(__('Copy this link:', 'ioblog-editorial'), window.location.href);
				return;
			}
			copyButton.classList.add('is-copied');
			copyButton.setAttribute('aria-label', copyButton.dataset.label);
			window.setTimeout(() => {
				copyButton.classList.remove('is-copied');
				copyButton.setAttribute('aria-label', __('Copy link', 'ioblog-editorial'));
			}, 1600);
		});
	}

	const tocLinks = Array.from(document.querySelectorAll('.io-toc a'));
	// ID с цифрой или процентным кодированием не является CSS-селектором. Точный поиск не ломает весь интерфейс статьи.
	const headings = [...new Set(tocLinks.map((link) => document.getElementById(link.getAttribute('href').slice(1))).filter(Boolean))];
	if (headings.length && 'IntersectionObserver' in window) {
		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				tocLinks.forEach((link) => link.classList.toggle('is-active', link.hash === `#${entry.target.id}`));
			});
		}, { rootMargin: '-18% 0px -70% 0px' });
		headings.forEach((heading) => observer.observe(heading));
	}

	const commentForm = document.querySelector('.comment-form');
	const commentText = commentForm?.querySelector('#comment');
	const fileInput = commentForm?.querySelector('input[name="ioblog_comment_image"]');
	const preview = commentForm?.querySelector('.io-comment-preview');
	const emojiToggle = commentForm?.querySelector('.io-emoji-toggle');
	const emojiPanel = commentForm?.querySelector('.io-emoji-panel');

	if (commentForm) commentForm.enctype = 'multipart/form-data';
	if (emojiToggle && emojiPanel && commentText) {
		emojiToggle.addEventListener('click', () => {
			const open = emojiToggle.getAttribute('aria-expanded') === 'true';
			emojiToggle.setAttribute('aria-expanded', String(!open));
			emojiPanel.hidden = open;
		});
		emojiPanel.addEventListener('click', (event) => {
			const button = event.target.closest('[data-emoji]');
			if (!button) return;
			const start = commentText.selectionStart;
			commentText.setRangeText(button.dataset.emoji, start, commentText.selectionEnd, 'end');
			commentText.focus();
		});
	}

	if (fileInput && preview) {
		let previewUrl;
		fileInput.addEventListener('change', () => {
			// При замене вложения освобождаем предыдущий blob, не дожидаясь удаления файла пользователем.
			if (previewUrl) URL.revokeObjectURL(previewUrl);
			previewUrl = null;
			const file = fileInput.files?.[0];
			if (!file) {
				preview.hidden = true;
				preview.innerHTML = '';
				return;
			}
			const url = URL.createObjectURL(file);
			previewUrl = url;
			const image = document.createElement('img');
			image.src = url;
			image.alt = __('Image preview', 'ioblog-editorial');
			const remove = document.createElement('button');
			remove.type = 'button';
			remove.setAttribute('aria-label', __('Remove image', 'ioblog-editorial'));
			remove.textContent = '×';
			preview.replaceChildren(image, remove);
			preview.hidden = false;
			preview.querySelector('button').addEventListener('click', () => {
				URL.revokeObjectURL(url);
				previewUrl = null;
				fileInput.value = '';
				preview.hidden = true;
				preview.innerHTML = '';
			});
		});
	}

	const lightbox = document.querySelector('.io-lightbox');
	if (lightbox) {
		const lightboxImage = lightbox.querySelector('img');
		const closeLightbox = () => { lightbox.hidden = true; lightboxImage.src = ''; document.body.classList.remove('io-modal-open'); };
		document.addEventListener('click', (event) => {
			const imageButton = event.target.closest('.io-comment-image');
			if (!imageButton) return;
			lightboxImage.src = imageButton.dataset.full;
			lightbox.hidden = false;
			document.body.classList.add('io-modal-open');
		});
		lightbox.querySelectorAll('.io-lightbox__backdrop,.io-lightbox__close').forEach((button) => button.addEventListener('click', closeLightbox));
		document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !lightbox.hidden) closeLightbox(); });
	}
})();
