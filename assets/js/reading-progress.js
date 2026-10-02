(() => {
  'use strict';
  const config = window.ioblogLibrary;
  const store = window.IOBlogLibraryStore;
  const content = document.querySelector('.io-article-content');
  const resume = document.querySelector('.io-reading-resume');
  if (!config?.current || !store || !content || !resume) return;
  const article = config.current;
  const old = store.read().find(row => row.id === article.id);
  const headings = [...content.querySelectorAll('h2[id], h3[id]')];
  let position = old?.progress || 0;
  let heading = old?.heading || '';
  let timer;
  let interacted = false;
  const progress = document.createElement('progress');
  progress.className = 'io-reading-progress'; progress.max = 100; progress.value = 0;
  progress.setAttribute('aria-label', config.labels.progress.replace('%d', 0));
  document.body.append(progress);
  if (position > .04 && position < .98 && !location.hash) {
    resume.hidden = false;
    resume.querySelector('.io-reading-resume__text').textContent = config.labels.progress.replace('%d', Math.round(position * 100));
  }
  resume.querySelector('.io-reading-resume__go').addEventListener('click', () => {
    const anchor = old?.heading ? document.getElementById(old.heading) : null;
    const top = anchor && content.contains(anchor) ? anchor.getBoundingClientRect().top + scrollY - 100
      : content.getBoundingClientRect().top + scrollY + (old?.progress || 0) * content.offsetHeight - innerHeight * .65;
    scrollTo({top:Math.max(0, top), behavior:'instant'});
    resume.hidden = true;
  });
  resume.querySelector('.io-reading-resume__dismiss').addEventListener('click', () => { resume.hidden = true; });
  const persist = () => {
    if (interacted && position > 0) store.update(article, {progress:position, heading});
  };
  const measure = () => {
    const rect = content.getBoundingClientRect();
    const before = rect.top > innerHeight * .7;
    position = before ? 0 : Math.min(1, Math.max(0, (innerHeight * .65 - rect.top) / Math.max(1, rect.height)));
    heading = [...headings].reverse().find(el => el.getBoundingClientRect().top < 160)?.id || '';
    const percent = Math.round(position * 100);
    progress.value = percent;
    progress.setAttribute('aria-label', config.labels.progress.replace('%d', percent));
  };
  // На загрузке страницы не перезаписываем сохранённое место её началом. Запись начинается с действия читателя.
  const schedule = () => { interacted = true; clearTimeout(timer); measure(); timer = setTimeout(persist, 800); };
  addEventListener('scroll', schedule, {passive:true});
  addEventListener('resize', measure, {passive:true});
  addEventListener('pagehide', persist);
  document.addEventListener('visibilitychange', () => { if (document.hidden) persist(); });
  measure();
})();
