(() => {
  'use strict';
  const store = window.IOBlogLibraryStore;
  if (!store) return;
  const format = 'io-blog-library';
  const maxBytes = 256 * 1024;
  const encode = () => JSON.stringify({format, version:1, site:location.origin, items:store.read(true)}, null, 2);
  const decode = source => {
    // Проверяем полный файл до записи, включая происхождение: ID статей другого сайта нельзя смешивать с этим.
    if (typeof source !== 'string' || new TextEncoder().encode(source).length > maxBytes) return {error:'invalid'};
    let backup;
    try { backup = JSON.parse(source); } catch (_) { return {error:'invalid'}; }
    if (!backup || backup.format !== format || backup.version !== 1 || !Array.isArray(backup.items) || backup.items.length > 150) return {error:'invalid'};
    if (backup.site !== location.origin) return {error:'site'};
    const incoming = [], seen = new Set();
    for (const row of backup.items) {
      const item = store.normalize(row);
      if (!item || !item.title.trim() || typeof row.saved !== 'boolean' || !Number.isFinite(row.progress) || row.progress < 0 || row.progress > 1 ||
          typeof row.heading !== 'string' || !Number.isFinite(row.updated) || row.updated < 0 || row.updated > Date.now() + 60000 || seen.has(item.id)) return {error:'invalid'};
      seen.add(item.id); incoming.push(item);
    }
    if (incoming.filter(item => item.saved).length > 100 || incoming.filter(item => !item.saved).length > 50) return {error:'limit'};
    // Закладки объединяются без удаления местных; позиция чтения выбирается по времени последнего изменения.
    let current;
    try { current = store.read(true); } catch (_) { return {error:'error'}; }
    const merged = new Map(current.map(item => [item.id, item]));
    for (const item of incoming) {
      const old = merged.get(item.id);
      merged.set(item.id, old ? {...(old.updated >= item.updated ? old : item), saved:old.saved || item.saved} : item);
    }
    const items = [...merged.values()].sort((a, b) => b.updated - a.updated || a.id - b.id);
    if (items.filter(item => item.saved).length > 100) return {error:'limit'};
    let recent = 0;
    return {items:items.filter(item => item.saved || ++recent <= 50)};
  };
  window.IOBlogLibraryBackup = {encode, decode, maxBytes};
})();
