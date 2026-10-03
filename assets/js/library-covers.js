(() => {
  'use strict';
  const cache = new Map();
  const pending = new Map();
  let timer;
  const draw = (element, cover) => {
    if (!element.isConnected || !cover) return;
    element.replaceChildren();
    if (cover.html) {
      // HTML поступает только из собственного endpoint и очищен серверным whitelist; localStorage не содержит разметку.
      const parsed = new DOMParser().parseFromString(cover.html,'text/html');
      const content = parsed.body.firstElementChild;
      if (content?.classList.contains('io-cover')) element.append(document.importNode(content,true));
    } else if (cover.url) {
      const image = document.createElement('img'); image.src = cover.url; image.alt = ''; image.loading = 'lazy'; image.decoding = 'async'; element.append(image);
    }
  };
  const flush = async () => {
    const entries = [...pending.entries()].slice(0,30);
    entries.forEach(([id]) => pending.delete(id));
    try {
      const url = new URL(IOBlogLibraryCovers.endpoint); url.searchParams.set('ids',entries.map(([id]) => id).join(','));
      const response = await fetch(url,{credentials:'omit'});
      if (!response.ok) return;
      const result = await response.json();
      entries.forEach(([id,elements]) => { const cover = result[id] || {}; cache.set(id,cover); elements.forEach(element => draw(element,cover)); });
    } catch (_) { /* Отказ превью не мешает открыть, сохранить или удалить статью. */ }
    finally { if (pending.size) timer=setTimeout(flush,50); }
  };
  window.IOBlogCoverPreview = id => {
    const element = document.createElement('div'); element.className = 'io-library-cover'; element.setAttribute('aria-hidden','true');
    if (cache.has(id)) queueMicrotask(() => draw(element,cache.get(id)));
    else { if (!pending.has(id)) pending.set(id,[]); pending.get(id).push(element); clearTimeout(timer); timer=setTimeout(flush,30); }
    return element;
  };
})();
