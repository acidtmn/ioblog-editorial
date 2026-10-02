(() => {
  'use strict';
  const dialog = document.querySelector('#io-library');
  const backup = window.IOBlogLibraryBackup;
  const store = window.IOBlogLibraryStore;
  const labels = window.ioblogLibrary?.labels;
  if (!dialog || !backup || !store || !labels) return;
  const file = dialog.querySelector('.io-library__file');
  const status = dialog.querySelector('.io-library__status');
  const button = dialog.querySelector('.io-library__import');
  let busy = false;
  dialog.querySelector('.io-library__export').addEventListener('click', () => {
    let snapshot;
    try { snapshot = backup.encode(); }
    catch (_) { status.textContent = labels.error; return; }
    const blob = new Blob([snapshot], {type:'application/json;charset=utf-8'});
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url; link.download = 'io-blog-library-' + new Date().toISOString().slice(0,10) + '.json';
    link.click();
    // Освобождаем URL после того, как браузер успел начать загрузку файла.
    setTimeout(() => URL.revokeObjectURL(url), 1000);
    status.textContent = labels.exported;
  });
  button.addEventListener('click', () => { if (!busy) file.click(); });
  file.addEventListener('change', async () => {
    const selected = file.files[0];
    if (!selected || busy) return;
    busy = true; button.disabled = true;
    try {
      if (selected.size > backup.maxBytes) { status.textContent = labels.invalidBackup; return; }
      const result = backup.decode(await selected.text());
      const error = result.error || store.replace(result.items);
      status.textContent = error ? ({site:labels.backupSite, invalid:labels.invalidBackup, limit:labels.limit}[error] || labels.error) : labels.imported;
    } catch (_) { status.textContent = labels.invalidBackup; }
    finally { busy = false; button.disabled = false; file.value = ''; }
  });
})();
