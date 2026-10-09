(() => {
  'use strict';
  const config = window.ioblogLibrary;
  const store = window.IOBlogLibraryStore;
  const dialog = document.querySelector('#io-library');
  if (!config || !store || !dialog || !dialog.showModal) return;
  const openButtons = [...document.querySelectorAll('.io-library-open')];
  const save = document.querySelector('.io-save-article');
  const list = dialog.querySelector('.io-library__list');
  const status = dialog.querySelector('.io-library__status');
  const count = openButtons[0]?.querySelector('.io-library-count');
  const labels = config.labels;
  const search = dialog.querySelector('.io-library__search');
  const filter = dialog.querySelector('.io-library__filter');
  const views = [...dialog.querySelectorAll('[data-library-view]')];
  const summary = dialog.querySelector('.io-library__summary');
  let view = 'saved';
  let opener;
  const report = error => {
    if (!dialog.open) { opener = save; dialog.showModal(); render(); }
    status.textContent = error === 'limit' ? labels.limit : labels.error;
  };
  const render = () => {
    const all = store.read().sort((a,b) => b.updated - a.updated || a.id - b.id);
    const savedItems = all.filter(row => row.saved);
    if (count) { count.textContent = savedItems.length; count.hidden = savedItems.length === 0; }
    if (save && config.current) {
      const saved = savedItems.some(row => row.id === config.current.id);
      save.setAttribute('aria-pressed', String(saved));
      save.setAttribute('aria-label', saved ? labels.saved : labels.save);
      save.dataset.tooltip = saved ? labels.saved : labels.save;
      save.querySelector('.screen-reader-text').textContent = saved ? labels.saved : labels.save;
      save.hidden = false;
    }
    if (!dialog.open) return;
    const query = search.value.trim().toLocaleLowerCase();
    const items = all.filter(row => (view === 'saved' ? row.saved : row.progress > 0) && row.title.toLocaleLowerCase().includes(query) &&
      (filter.value === 'all' || (filter.value === 'unread' && row.progress <= .04) || (filter.value === 'reading' && row.progress > .04 && row.progress < .98) || (filter.value === 'complete' && row.progress >= .98)));
    const focused = list.contains(document.activeElement) ? {id:document.activeElement.dataset.articleId, action:document.activeElement.dataset.action} : null;
    summary.textContent = labels.results.replace('%d', items.length);
    // Заголовки и ссылки из хранилища никогда не вставляются через innerHTML.
    list.replaceChildren();
    for (const item of items) {
      const row = document.createElement('li');
      const body = document.createElement('div');
      const link = document.createElement('a');
      link.href = item.url;
      if (item.progress > .04 && item.progress < .98 && item.heading) link.hash = item.heading;
      link.textContent = item.title;
      link.dataset.articleId = item.id; link.dataset.action = 'read';
      const detail = document.createElement('small');
      detail.textContent = labels.progress.replace('%d', Math.round(item.progress * 100));
      const progress = document.createElement('progress');
      progress.max = 100; progress.value = Math.round(item.progress * 100); progress.setAttribute('aria-label', detail.textContent);
      body.append(link, detail, progress);
      const remove = document.createElement('button');
      remove.type = 'button'; remove.className = 'io-library__remove'; remove.textContent = '×';
      remove.dataset.articleId = item.id; remove.dataset.action = 'remove';
      remove.setAttribute('aria-label', labels.remove + ': ' + item.title);
      remove.hidden = !item.saved;
      remove.addEventListener('click', () => {
        const error = store.remove(item.id);
        if (error) { report(error); return; }
      });
      if (window.IOBlogCoverPreview) row.append(IOBlogCoverPreview(item.id));
      row.append(body, remove); list.append(row);
    }
    // Перерисовка при сохранении прогресса не выбрасывает пользователя из списка при работе клавиатурой.
    if (focused) {
      const target = [...list.querySelectorAll('[data-article-id]')].find(el => el.dataset.articleId === focused.id && el.dataset.action === focused.action && !el.hidden);
      (target || list.querySelector('a') || search).focus();
    }
    status.textContent = items.length ? '' : query || filter.value !== 'all' ? labels.noMatches : view === 'history' ? labels.emptyHistory : labels.empty;
  };
  openButtons.forEach(button => {
    button.hidden = false;
    button.addEventListener('click', () => {
      opener = button.closest('.io-mobile-nav') ? document.querySelector('.io-menu-toggle') : button;
      dialog.showModal(); render();
    });
  });
  views.forEach(button => button.addEventListener('click', () => {
    view = button.dataset.libraryView;
    views.forEach(item => item.setAttribute('aria-pressed', String(item === button)));
    render();
  }));
  search.addEventListener('input', render);
  filter.addEventListener('change', render);
  dialog.querySelector('.io-library-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => opener?.focus());
  dialog.addEventListener('click', event => { if (event.target === dialog) { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close(); } });
  save?.addEventListener('click', () => {
    const old = store.read().find(row => row.id === config.current.id);
    const error = store.update(config.current, {saved:!old?.saved});
    if (error) report(error);
  });
  addEventListener('ioblog-library-change', render);
  addEventListener('storage', event => { if (event.key === store.key || event.key === null) render(); });
  render();
})();
