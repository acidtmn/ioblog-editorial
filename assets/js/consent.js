(() => {
  'use strict';
  const config = window.IOBlogConsentConfig, notice = document.querySelector('[data-consent-notice]'), reopen = document.querySelector('[data-consent-open]');
  if (!config || !notice) return;
  const key = 'ioblog-privacy-choice'; let choice = null, previousFocus;
  try { const stored = JSON.parse(localStorage.getItem(key)); if (stored?.version === config.version && Number.isFinite(stored.at) && stored.at <= Date.now() && Date.now()-stored.at < 180*86400000) choice = stored; } catch (_) { /* Запрет хранилища не считается разрешением необязательного кода. */ }
  const allowed = category => !!choice && choice[category] === true;
  const syncCookie = () => {
    const value = choice ? encodeURIComponent(JSON.stringify(choice)) : '';
    document.cookie = `io_privacy_choice=${value}; Path=/; Max-Age=${choice ? 180*86400 : 0}; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
  };
  syncCookie();
  window.IOBlogConsent = {allowed};
  const activate = () => {
    document.querySelectorAll('template[data-io-consent-content]').forEach(template => {
      if (!allowed(template.dataset.ioConsentContent)) return;
      // Скрипты из template не выполняются при cloneNode; восстанавливаем их только после разрешения категории.
      const source = document.createElement('template');
      try { source.innerHTML=new TextDecoder().decode(Uint8Array.from(atob(template.dataset.ioConsentHtml), c=>c.charCodeAt(0))); } catch (_) { return; }
      const fragment = source.content;
      fragment.querySelectorAll('script').forEach(old => { const script = document.createElement('script'); for (const attribute of old.attributes) script.setAttribute(attribute.name, attribute.value); if (old.src && !old.hasAttribute('async')) script.async = false; script.textContent = old.textContent; old.replaceWith(script); });
      template.replaceWith(fragment);
    });
  };
  const show = () => { previousFocus=document.activeElement; notice.hidden=false; reopen.hidden=true; for(const box of notice.querySelectorAll('[data-consent-category]')) box.checked=allowed(box.dataset.consentCategory); notice.querySelector('button').focus({preventScroll:true}); };
  reopen.addEventListener('click', show);
  notice.querySelectorAll('[data-consent-save]').forEach(button => button.addEventListener('click', () => {
    const next={version:config.version,at:Date.now(),analytics:false,marketing:false};
    for(const box of notice.querySelectorAll('[data-consent-category]')) next[box.dataset.consentCategory]=button.dataset.consentSave==='all'||button.dataset.consentSave==='selected'&&box.checked;
    const revoked = choice && ['analytics','marketing'].some(category => choice[category] && !next[category]); choice=next;
    try { localStorage.setItem(key, JSON.stringify(choice)); } catch (_) { /* Выбор действует на этой странице, если браузер не позволяет его сохранить. */ }
    syncCookie();
    notice.hidden=true; reopen.hidden=false; previousFocus?.focus({preventScroll:true});
    // Уже загруженный сторонний код нельзя безопасно «разгрузить»; перезагрузка вновь делает его инертным.
    if(revoked) { location.reload(); return; }
    activate(); dispatchEvent(new CustomEvent('ioblog-consent-change',{detail:{analytics:choice.analytics,marketing:choice.marketing}}));
  }));
  if(choice) { reopen.hidden=false; activate(); } else show();
  // wp_footer может вывести интеграционный template после файла consent.js.
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',activate,{once:true}); else activate();
})();
