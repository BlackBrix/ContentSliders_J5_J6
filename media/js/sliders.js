/** BlackBrix Content Sliders 1.0.2 – standalone vanilla JS. */
(() => {
  'use strict';
  const selector = '.bbcs-item';
  const items = () => [...document.querySelectorAll(selector)];
  const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const settings = (item) => {
    try { return JSON.parse(item.dataset.bbcsSettings || '{}'); }
    catch { return {}; }
  };
  const running = new WeakMap();
  const versions = new WeakMap();
  const openState = (item) => item.classList.contains('bbcs-open');

  async function switchPanel(item, open) {
    const panel = item.querySelector('.bbcs-panel');
    const button = item.querySelector('.bbcs-toggle');
    if (!panel || !button) return;
    const currentVersion = (versions.get(panel) || 0) + 1;
    versions.set(panel, currentVersion);
    const currentAnimation = running.get(panel);
    const startHeight = panel.hidden ? 0 : panel.getBoundingClientRect().height;
    if (currentAnimation) currentAnimation.cancel();

    item.classList.toggle('bbcs-open', open);
    button.setAttribute('aria-expanded', String(open));
    panel.hidden = false;
    const targetHeight = open ? panel.scrollHeight : 0;
    const duration = reducedMotion() ? 0 : Number(open ? settings(item).openDuration : settings(item).closeDuration) || 0;
    if (duration <= 0 || Math.abs(startHeight - targetHeight) < 1) {
      panel.hidden = !open;
      return;
    }
    const anim = panel.animate(
      [{height: `${startHeight}px`}, {height: `${targetHeight}px`}],
      {duration, easing: 'ease-in-out'}
    );
    running.set(panel, anim);
    try { await anim.finished; } catch { /* Interrupted by a subsequent click. */ }
    if (versions.get(panel) !== currentVersion) return;
    running.delete(panel);
    panel.hidden = !open;
  }

  function scrollHeading(item) {
    const opts = settings(item);
    if (!opts.autoscroll) return Promise.resolve();
    const top = Math.max(0, window.scrollY + item.querySelector('.bbcs-heading').getBoundingClientRect().top - Number(opts.offset || 0));
    const duration = reducedMotion() ? 0 : Number(opts.scrollDuration || 0);
    if (duration <= 0) {
      window.scrollTo(0, top);
      return Promise.resolve();
    }
    // requestAnimationFrame animation gives configurable duration (native smooth ignores it).
    const from = window.scrollY;
    const start = performance.now();
    return new Promise(resolve => {
      function frame(now) {
        const t = Math.min(1, (now - start) / duration);
        const e = t < .5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
        window.scrollTo(0, from + (top - from) * e);
        if (t < 1) requestAnimationFrame(frame); else resolve();
      }
      requestAnimationFrame(frame);
    });
  }

  let activation = 0;
  async function activate(item) {
    const token = ++activation;
    const isOpening = !openState(item);
    const opts = settings(item);
    const work = [];
    if (isOpening && opts.exclusive) {
      for (const other of items()) {
        if (other !== item && openState(other)) work.push(switchPanel(other, false));
      }
    }
    work.push(switchPanel(item, isOpening));
    await Promise.all(work);
    if (token === activation && isOpening && openState(item)) await scrollHeading(item);
  }

  function openFromHash() {
    const fragment = window.location.hash.slice(1);
    if (!fragment) return;
    let id;
    try { id = decodeURIComponent(fragment); } catch { id = fragment; }
    const item = document.getElementById(id);
    if (!item || !item.matches(selector) || !settings(item).hashLinks) return;
    if (openState(item)) { scrollHeading(item); return; }
    activate(item);
  }

  function initialize() {
    document.addEventListener('click', (event) => {
      const button = event.target.closest('.bbcs-toggle');
      if (button) {
        const item = button.closest(selector);
        if (item) activate(item);
      }
    });
    // Also handle repeated clicks on a same-page hash link (no hashchange event).
    document.addEventListener('click', (event) => {
      const link = event.target.closest('a[href*="#"]');
      if (!link || event.defaultPrevented) return;
      const url = new URL(link.href, window.location.href);
      if (!url.hash || url.origin !== window.location.origin ||
          url.pathname !== window.location.pathname || url.search !== window.location.search) return;
      let id;
      try { id = decodeURIComponent(url.hash.slice(1)); }
      catch { id = url.hash.slice(1); }
      const item = document.getElementById(id);
      if (!item || !item.matches(selector) || !settings(item).hashLinks) return;
      if (window.location.hash === url.hash) {
        event.preventDefault();
        if (openState(item)) scrollHeading(item); else activate(item);
      }
    });
    window.addEventListener('hashchange', openFromHash);
    // Browser's first hash scroll may happen after DOMContentLoaded; redo when layout is ready.
    requestAnimationFrame(() => requestAnimationFrame(openFromHash));
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
  else initialize();
})();
