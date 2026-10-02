(() => {
  'use strict';
  const root = document.querySelector('.io-claps');
  const config = window.ioblogClaps;
  if (!root || !config) return;
  const button = root.querySelector('.io-claps__button');
  const undo = root.querySelector('.io-claps__undo');
  const mine = root.querySelector('.io-claps__mine');
  const total = root.querySelector('[data-claps-total]');
  const readers = root.querySelector('[data-claps-readers]');
  const status = root.querySelector('.io-claps__status');
  const labels = config.labels;
  const number = new Intl.NumberFormat(document.documentElement.lang || undefined);
  let state = null;
  let target = 0;
  let sending = false;
  let timer;
  let loading;

  const request = async (url, body, token = '') => {
    const abort = new AbortController();
    const timeout = setTimeout(() => abort.abort(), 8000);
    try {
      const response = await fetch(url, {
        method: body === undefined ? 'GET' : 'POST', credentials: 'same-origin', cache: 'no-store', signal: abort.signal,
        headers: body === undefined ? {} : { 'Content-Type': 'application/json', 'X-IOBlog-Claps': token },
        body: body === undefined ? undefined : JSON.stringify(body),
      });
      const data = await response.json();
      if (!response.ok) {
        const error = new Error(typeof data.message === 'string' ? data.message : labels.error);
        error.status = response.status;
        throw error;
      }
      if (!['total', 'readers', 'mine', 'limit'].every(key => Number.isSafeInteger(data[key]) && data[key] >= 0)
        || data.limit !== 10 || data.mine > data.limit || typeof data.token !== 'string') throw new Error(labels.error);
      return data;
    } finally { clearTimeout(timeout); }
  };

  const paint = () => {
    const limit = state?.limit || 10;
    button.disabled = target >= limit;
    button.setAttribute('aria-label', target >= limit ? labels.limit : labels.clap);
    button.classList.toggle('has-claps', target > 0);
    root.setAttribute('aria-busy', String(sending));
    undo.hidden = target === 0;
    mine.textContent = state ? labels.mine.replace('%1$d', target).replace('%2$d', limit) : '';
    if (state) {
      // Пока запрос идёт, показываем намерение читателя; сервер остаётся источником итоговых чисел.
      total.textContent = number.format(state.total + target - state.mine);
      readers.textContent = number.format(state.readers + Number(target > 0) - Number(state.mine > 0));
    }
  };

  const load = () => {
    if (loading) return loading;
    status.textContent = labels.loading;
    loading = request(config.endpoint).then(data => {
      state = data; target = data.mine; paint(); status.textContent = '';
    }).catch(() => {
      status.textContent = labels.error; button.disabled = false;
    }).finally(() => { loading = null; });
    return loading;
  };

  const flush = async () => {
    if (sending || !state || target === state.mine) return;
    sending = true; paint(); status.textContent = labels.saving;
    let failed = false;
    try {
      if (!state.token) {
        const previous = state.mine;
        const session = await request(config.session, {});
        target = target === 0 ? 0 : Math.min(session.limit, session.mine + target - previous);
        state = session;
      }
      const submitted = target;
      const result = await request(config.endpoint, { claps: submitted, expected: state.mine }, state.token);
      state = result;
      // Более позднее нажатие или отмена остаются в очереди, пока завершался предыдущий запрос.
      if (target === submitted) target = result.mine;
      status.textContent = target === 0 ? labels.undone : target === state.limit ? labels.limit : labels.thanks;
    } catch (error) {
      failed = true;
      // Потерянный ответ мог следовать за успешной записью: сверяем счётчик, не повторяя действие вслепую.
      try { state = await request(config.endpoint); } catch (_) { /* Следующее нажатие повторит сверку сервера. */ }
      target = state?.mine || 0;
      status.textContent = error.status ? error.message : labels.error;
    } finally {
      sending = false; paint();
      if (!failed && target !== state.mine) queue();
    }
  };

  const queue = () => {
    clearTimeout(timer);
    timer = setTimeout(flush, 180);
  };

  button.addEventListener('click', async () => {
    if (!state) await load();
    if (!state || target >= state.limit) return;
    target++;
    paint(); queue();
    // Анимация привязана к намеренному нажатию, а reduced-motion отключает движение в CSS.
    button.classList.remove('is-clapping');
    requestAnimationFrame(() => button.classList.add('is-clapping'));
  });
  button.addEventListener('animationend', event => {
    // Искра живёт дольше движения ладоней: её завершение снимает общий класс анимации.
    if (event.animationName === 'io-clap-burst') button.classList.remove('is-clapping');
  });
  undo.addEventListener('click', () => { target = 0; paint(); queue(); });

  // Лёгкий API-запрос нужен только когда блок приблизился к экрану; чтение не устанавливает cookie.
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      if (entries.some(entry => entry.isIntersecting)) { observer.disconnect(); load(); }
    }, { rootMargin: '300px' });
    observer.observe(root);
  } else { load(); }
})();
