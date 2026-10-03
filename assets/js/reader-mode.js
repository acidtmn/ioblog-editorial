(() => {
  'use strict';
  const toggle = document.querySelector('.io-reader-toggle');
  // Режим меняет только текущую страницу; Escape всегда возвращает привычный макет.
  const reset = () => { document.body.classList.remove('io-reader-active'); toggle?.setAttribute('aria-pressed','false'); };
  toggle?.addEventListener('click', () => { const active = document.body.classList.toggle('io-reader-active'); toggle.setAttribute('aria-pressed',String(active)); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape') reset(); });
  document.querySelector('.io-print-article')?.addEventListener('click', () => window.print());
})();
