(() => {
  'use strict';
  const key = 'ioblog-library-v1';
  // Хранилище валидирует даже старые записи: повреждённый JSON не превращается в HTML или внешнюю ссылку.
  const normalize = item => {
    if (!item || !Number.isSafeInteger(item.id) || item.id < 1 || typeof item.title !== 'string' || typeof item.url !== 'string') return null;
    let url;
    try { url = new URL(item.url); } catch (_) { return null; }
    if (url.origin !== location.origin || !['http:', 'https:'].includes(url.protocol) || url.username || url.password) return null;
    return {id:item.id, title:item.title.slice(0, 300), url:url.href, saved:item.saved === true,
      progress:Number.isFinite(item.progress) ? Math.max(0, Math.min(1, item.progress)) : 0,
      heading:typeof item.heading === 'string' ? item.heading.slice(0, 200) : '',
      updated:Number.isFinite(item.updated) ? item.updated : 0};
  };
  const read = (strict = false) => {
    let stored;
    try { stored = localStorage.getItem(key); }
    catch (error) {
      // Резервная копия не должна считать отказ доступа пустой библиотекой и молча перезаписывать данные.
      if (strict) throw error;
      return [];
    }
    try {
      const raw = JSON.parse(stored || '[]');
      if (!Array.isArray(raw)) return [];
      const seen = new Set();
      return raw.slice(0, 150).map(normalize).filter(item => {
        if (!item || seen.has(item.id)) return false;
        seen.add(item.id); return true;
      });
    } catch (_) { return []; }
  };
  const update = (article, changes) => {
    let items;
    try { items = read(true); } catch (_) { return 'error'; }
    const old = items.find(item => item.id === article.id);
    const item = normalize({...old, ...article, ...changes, updated:Date.now()});
    if (!item) return 'invalid';
    if (item.saved && !old?.saved && items.filter(row => row.saved).length >= 100) return 'limit';
    // Незакреплённая история ограничена 50 статьями; сохранённые записи не вытесняются чтением других материалов.
    const next = [item, ...items.filter(row => row.id !== item.id)];
    let recent = 0;
    const bounded = next.filter(row => row.saved || ++recent <= 50);
    try { localStorage.setItem(key, JSON.stringify(bounded)); }
    catch (_) { return 'error'; }
    dispatchEvent(new CustomEvent('ioblog-library-change', {detail:{id:item.id, saved:item.saved, changedSaved:!!old?.saved !== item.saved}}));
    return null;
  };
  const remove = id => {
    let items;
    try { items = read(true); } catch (_) { return 'error'; }
    const old = items.find(item => item.id === id);
    return old ? update(old, {saved:false}) : null;
  };
  const replace = items => {
    // Весь снимок записывается одной операцией: ошибка импорта не оставляет половину библиотеки.
    if (!Array.isArray(items) || items.length > 150) return 'invalid';
    const normalized = items.map(normalize);
    if (normalized.some(item => !item) || new Set(normalized.map(item => item.id)).size !== normalized.length) return 'invalid';
    if (normalized.filter(item => item.saved).length > 100 || normalized.filter(item => !item.saved).length > 50) return 'limit';
    try { localStorage.setItem(key, JSON.stringify(normalized)); }
    catch (_) { return 'error'; }
    // Восстановление файла не является новой реакцией читателя и не создаёт событий серверной статистики.
    dispatchEvent(new CustomEvent('ioblog-library-change', {detail:{bulk:true, changedSaved:false}}));
    return null;
  };
  window.IOBlogLibraryStore = {key, read, update, remove, normalize, replace};
})();
