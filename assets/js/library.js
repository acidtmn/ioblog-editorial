(() => {
  'use strict';
  const config = window.ioblogLibrary;
  const store = window.IOBlogLibraryStore;
  const dialog = document.querySelector('#io-library');
  if (!config || !store || !dialog || !dialog.showModal) return;
  const open = document.querySelector('.io-library-open');
  const save = document.querySelector('.io-save-article');
  const list = dialog.querySelector('.io-library__list');
  const status = dialog.querySelector('.io-library__status');
  const count = open?.querySelector('.io-library-count');
  const labels = config.labels;
  let opener;
  const report = error => { status.textContent = error === 'limit' ? labels.limit : labels.error; if (!dialog.open) { opener = save; dialog.showModal(); } };
  const render = () => {
    const items = store.read().filter(row => row.saved);
    if (count) { count.textContent = items.length; count.hidden = items.length === 0; }
    if (save && config.current) {
      const saved = items.some(row => row.id === config.current.id);
      save.setAttribute('aria-pressed', String(saved));
      save.setAttribute('aria-label', saved ? labels.saved : labels.save);
      save.dataset.tooltip = saved ? labels.saved : labels.save;
      save.querySelector('.screen-reader-text').textContent = saved ? labels.saved : labels.save;
      save.hidden = false;
    }
    // Заголовки и ссылки из хранилища никогда не вставляются через innerHTML.
    list.replaceChildren();
    for (const item of items) {
      const row = document.createElement('li');
      const body = document.createElement('div');
      const link = document.createElement('a');
      link.href = item.url;
      if (item.progress > .04 && item.progress < .98 && item.heading) link.hash = item.heading;
      link.textContent = item.title;
      const detail = document.createElement('small');
      detail.textContent = labels.progress.replace('%d', Math.round(item.progress * 100));
      const progress = document.createElement('progress');
      progress.max = 100; progress.value = Math.round(item.progress * 100); progress.setAttribute('aria-label', detail.textContent);
      body.append(link, detail, progress);
      const remove = document.createElement('button');
      remove.type = 'button'; remove.className = 'io-library__remove'; remove.textContent = '×';
      remove.setAttribute('aria-label', labels.remove + ': ' + item.title);
      remove.addEventListener('click', () => {
        const error = store.remove(item.id);
        if (error) { report(error); return; }
        // После удаления возвращаем фокус на следующую запись либо кнопку закрытия, не теряя клавиатурную позицию.
        const first = list.querySelector('a') || dialog.querySelector('.io-library-close');
        first.focus();
      });
      row.append(body, remove); list.append(row);
    }
    status.textContent = items.length ? '' : labels.empty;
  };
  if (open) {
    open.hidden = false;
    open.addEventListener('click', () => { render(); opener = open; dialog.showModal(); });
  }
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
