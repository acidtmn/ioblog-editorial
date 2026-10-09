(() => {
  'use strict';
  const form = document.querySelector('#io-design-form');
  if (!form) return;
  const status = document.querySelector('#io-design-status');
  const frame = document.querySelector('.io-design-preview iframe');
  const palettes = IOBlogDesignPalettes.colors;
  const paletteButtons = form.querySelectorAll('[data-design-palette]');
  const paletteStatus = document.querySelector('#io-design-palette-status');
  const syncPalette = () => {
    const id = Object.keys(palettes).find(key => Object.entries(palettes[key]).every(([name, value]) => form.elements[`config[${name}]`].value.toLowerCase() === value));
    paletteButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.designPalette === id)));
    paletteStatus.textContent = id ? IOBlogDesignPalettes.labels[id] : IOBlogDesignPalettes.custom;
  };
  // Пресет меняет только цвета в той же форме; сохранение по-прежнему требует публикации.
  paletteButtons.forEach(button => button.addEventListener('click', () => {
    Object.entries(palettes[button.dataset.designPalette]).forEach(([name, value]) => { form.elements[`config[${name}]`].value = value; });
    form.dispatchEvent(new Event('input', {bubbles:true}));
  }));
  form.querySelector('.io-design-palettes').hidden = false;
  let busy = false;
  let dirty = false;
  let timer;
  let revision = 0;
  let queued;
  // Каждая операция получает полный снимок: снятые флажки явно представлены нулём.
  const request = async mode => {
    // Публикация не теряется, если пользователь нажал кнопку во время обновления iframe.
    if (busy) { if (queued !== 'publish') queued = mode; return; }
    busy = true;
    const requestedRevision = revision;
    const body = new FormData(form);
    form.querySelectorAll('input[type=checkbox]').forEach(input => body.set(input.name, input.checked ? '1' : '0'));
    body.set('action', 'io_design'); body.set('nonce', IOBlogDesign.nonce); body.set('mode', mode);
    try {
      const response = await fetch(IOBlogDesign.endpoint, {method:'POST', body, credentials:'same-origin'});
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.data?.message || IOBlogDesign.error);
      if (mode === 'publish') { dirty = revision !== requestedRevision; status.textContent = IOBlogDesign.saved; }
      else frame.src = result.data.url;
    } catch (error) { status.textContent = error.message; }
    finally { busy = false; if (queued) { const next = queued; queued = null; request(next); } }
  };
  document.querySelector('[data-design-publish]').addEventListener('click', () => request('publish'));
  document.querySelector('[data-design-preview]').addEventListener('click', () => request('preview'));
  document.querySelectorAll('[data-preview-width]').forEach(button => button.addEventListener('click', () => frame.style.width = button.dataset.previewWidth));
  form.addEventListener('submit', event => event.preventDefault());
  form.addEventListener('click', event => {
    const reset = event.target.closest('[data-reset]');
    if (!reset) return;
    const input = reset.parentElement.querySelector('input,select');
    if (input.type === 'checkbox') input.checked = reset.dataset.reset === '1';
    else input.value = reset.dataset.reset;
    input.dispatchEvent(new Event('input', {bubbles:true}));
  });
  // Контраст рассчитывается локально, без внешнего сервиса и передачи выбранной палитры.
  const luminance = hex => {
    const channels = hex.slice(1).match(/../g).map(x => parseInt(x,16)/255).map(x => x <= .04045 ? x/12.92 : ((x+.055)/1.055)**2.4);
    return channels[0]*.2126 + channels[1]*.7152 + channels[2]*.0722;
  };
  const contrast = () => {
    const labels = IOBlogDesignPalettes.contrast;
    const rows = ['light','dark'].map(scheme => {
      const ratio = (foreground, background) => {
        const a = luminance(form.elements[`config[${scheme}_${foreground}]`].value);
        const b = luminance(form.elements[`config[${scheme}_${background}]`].value);
        return (Math.max(a,b)+.05)/(Math.min(a,b)+.05);
      };
      const checks = [
        ['text', ratio('text', 'bg'), 4.5],
        ['muted', ratio('muted', 'surface'), 4.5],
        ['button', Math.min(ratio('button_text', 'accent'), ratio('button_text', 'accent_hover')), 4.5],
        ['focus', Math.min(...['bg','surface','surface_soft','accent_soft'].map(background => ratio('focus', background))), 3],
      ];
      const row = document.createElement('p');
      row.textContent = `${labels[scheme]}: ` + checks.map(([key, value, minimum]) => `${labels[key]} ${value.toFixed(2)}:1 (${value >= minimum ? labels.pass : labels.fail}, ${minimum}:1)`).join(' · ');
      row.classList.toggle('io-design-contrast-warning', checks.some(([, value, minimum]) => value < minimum));
      return row;
    });
    document.querySelector('#io-design-contrast').replaceChildren(...rows);
  };
  form.addEventListener('input', () => { dirty = true; ++revision; syncPalette(); contrast(); clearTimeout(timer); timer = setTimeout(() => request('preview'), 800); });
  document.querySelector('#io-design-search').addEventListener('input', event => {
    const needle = event.target.value.toLocaleLowerCase();
    form.querySelectorAll('[data-design-field]').forEach(field => field.hidden = !field.textContent.toLocaleLowerCase().includes(needle));
  });
  addEventListener('beforeunload', event => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
  syncPalette(); contrast();
})();
