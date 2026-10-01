(() => {
  'use strict';

  const form = document.querySelector('.io-admin-form');
  if (!form) return;
  const navigation = form.querySelector('.io-admin-tabs');
  const panels = [...form.querySelectorAll('.io-admin-card')];
  const status = form.querySelector('.io-admin-save-status');
  const buttons = [];

  // Все поля остаются в одной штатной форме WordPress: смена вкладки не теряет значения.
  const activate = (index, focus = false) => {
    panels.forEach((panel, position) => {
      const selected = position === index;
      panel.hidden = !selected;
      buttons[position].setAttribute('aria-selected', String(selected));
      buttons[position].tabIndex = selected ? 0 : -1;
    });
    if (focus) buttons[index].focus();
  };

  // Без JavaScript секции доступны обычным списком; роли вкладок добавляются только вместе с поведением.
  panels.forEach((panel, index) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.id = `io-tab-${panel.id}`;
    button.setAttribute('role', 'tab');
    button.setAttribute('aria-controls', panel.id);
    button.textContent = panel.querySelector('h2').textContent;
    button.addEventListener('click', () => activate(index));
    button.addEventListener('keydown', (event) => {
      const directions = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 };
      if (!(event.key in directions) && !['Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      const next = event.key === 'Home' ? 0 : event.key === 'End' ? panels.length - 1
        : (index + directions[event.key] + panels.length) % panels.length;
      activate(next, true);
    });
    panel.setAttribute('role', 'tabpanel');
    panel.setAttribute('aria-labelledby', button.id);
    panel.tabIndex = 0;
    navigation.append(button);
    buttons.push(button);
  });
  navigation.setAttribute('role', 'tablist');
  navigation.setAttribute('aria-orientation', 'vertical');
  navigation.hidden = false;
  form.classList.add('is-enhanced');
  const initialIndex = panels.findIndex((panel) => `#${panel.id}` === location.hash);
  activate(initialIndex < 0 ? 0 : initialIndex);

  // Проверка браузера должна открыть скрытое некорректное поле до попытки перевести в него фокус.
  form.addEventListener('invalid', (event) => {
    const index = panels.findIndex((panel) => panel.contains(event.target));
    if (index >= 0) activate(index);
  }, true);

  // Сравнение значений, а не числа событий, снимает предупреждение после ручной отмены правок.
  const snapshot = () => JSON.stringify([...new FormData(form).entries()]);
  const original = snapshot();
  let dirty = false;
  let submitting = false;
  const updateStatus = () => {
    dirty = snapshot() !== original;
    status.textContent = dirty ? status.dataset.dirty : status.dataset.clean;
    status.classList.toggle('is-dirty', dirty);
  };
  form.addEventListener('input', updateStatus);
  form.addEventListener('change', updateStatus);
  form.addEventListener('submit', () => {
    // Штатный редирект options.php возвращает в редактируемый раздел, не теряя сообщение сохранения.
    const referer = form.querySelector('input[name="_wp_http_referer"]');
    const current = panels.find((panel) => !panel.hidden);
    if (referer && current) referer.value = `${referer.value.split('#')[0]}#${current.id}`;
    submitting = true;
  });
  window.addEventListener('beforeunload', (event) => {
    if (!dirty || submitting) return;
    event.preventDefault();
    event.returnValue = '';
  });
  updateStatus();
})();
