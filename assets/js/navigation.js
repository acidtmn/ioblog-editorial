(() => {
  'use strict';
  const button = document.querySelector('.io-menu-toggle'), menu = document.querySelector('#io-mobile-navigation');
  if (!button || !menu) return;
  const mobile = matchMedia('(max-width:900px)');
  const setOpen = (open, restoreFocus = false) => {
    menu.hidden = !open;
    button.setAttribute('aria-expanded', String(open));
    button.setAttribute('aria-label', open ? button.dataset.closeLabel : button.dataset.openLabel);
    if (restoreFocus) button.focus();
  };
  button.addEventListener('click', () => setOpen(menu.hidden));
  // Панель остаётся обычной навигацией, без ловушки фокуса и блокировки прокрутки страницы.
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && !menu.hidden) setOpen(false,true); });
  document.addEventListener('click', event => { if (!menu.hidden && !menu.contains(event.target) && !button.contains(event.target)) setOpen(false); });
  menu.addEventListener('click', event => { if (event.target.closest('a,button')) setOpen(false); });
  menu.addEventListener('focusout', event => { if (event.relatedTarget && !menu.contains(event.relatedTarget) && event.relatedTarget !== button) setOpen(false); });
  mobile.addEventListener('change', () => { if (!mobile.matches) setOpen(false); });
})();
