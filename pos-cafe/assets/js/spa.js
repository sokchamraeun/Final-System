/**
 * SPA router — intercepts sidebar nav clicks, fetches pos-cafe
 * pages via X-SPA header, and swaps content into #spaContent.
 *
 * Root/legacy pages that don't support SPA trigger a full
 * page load instead.
 *
 * Expects the global `window.SPA_BASE` to be set by the shell
 * (e.g. '/FinalSystem/pos-cafe/') so it knows which URLs to
 * handle via fetch vs. full navigation.
 */
var SPA = (function () {
  'use strict';

  var contentEl  = document.getElementById('spaContent');
  var titleEl    = document.getElementById('spaTitle');
  var subtitleEl = document.getElementById('spaSubtitle');
  var baseUrl    = window.SPA_BASE || '';
  var currentUrl = window.location.pathname + window.location.search;

  /* ── Initialise event delegation on the sidebar ── */
  function init() {
    if (window.SPA_DEBUG) console.log('[SPA] init  base=' + baseUrl);
    document.addEventListener('click', function (e) {
      var link = e.target.closest('#appSidebar a.nav-link');
      if (!link) return;
      var href = link.getAttribute('href');
      if (!href || href === '' || href.startsWith('#') || href.startsWith('javascript:')) return;
      e.preventDefault();

      /* Root/legacy pages — full navigation */
      if (baseUrl && href.indexOf(baseUrl) !== 0) {
        window.location.href = href;
        return;
      }

      navigate(href);
    });

    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.url && e.state.url.indexOf(baseUrl) === 0) {
        navigate(e.state.url, true);
      }
    });

    /* Intercept GET forms inside the content area (search, filter, pagination) */
    document.addEventListener('submit', function (e) {
      var form = e.target;
      if (!form || form.method.toUpperCase() !== 'GET') return;
      if (form.closest('#spaContent') === null) return;
      e.preventDefault();
      var url = form.action || window.location.pathname;
      var qs = [];
      Array.from(form.elements).forEach(function (el) {
        if (!el.name || el.disabled) return;
        if (el.tagName === 'INPUT' && (el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
        qs.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value));
      });
      if (qs.length) url += (url.indexOf('?') === -1 ? '?' : '&') + qs.join('&');
      navigate(url);
    });
  }

  /* ── Navigate to a URL (optional replace = no history push) ── */
  function navigate(url, replace) {
    if (!url || url === currentUrl) return;

    /* Show loading state */
    contentEl.innerHTML = '<div class="flex items-center justify-center py-20 text-slate-400"><i class="fa-solid fa-spinner fa-spin text-3xl text-brand"></i></div>';

    /* Update browser history */
    if (!replace) {
      history.pushState({ url: url }, '', url);
    } else {
      history.replaceState({ url: url }, '', url);
    }
    currentUrl = url;

    /* Update active sidebar link */
    document.querySelectorAll('#appSidebar .nav-link').forEach(function (l) {
      l.classList.remove('active');
      if (l.getAttribute('href') === url) l.classList.add('active');
    });

    /* Fetch page content */
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-SPA', '1');
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;

      /* Verify the server understood SPA mode */
      if (xhr.getResponseHeader('X-SPA-Content') !== '1') {
        /* Fallback: full page load */
        window.location.href = url;
        return;
      }

      if (xhr.status < 200 || xhr.status >= 400) {
        contentEl.innerHTML =
          '<div class="flex flex-col items-center justify-center py-20 text-slate-400">' +
          '<i class="fa-solid fa-triangle-exclamation text-4xl mb-4"></i>' +
          '<p>Failed to load page. <a href="' + url + '" class="text-brand underline">Retry</a></p>' +
          '</div>';
        return;
      }

      contentEl.innerHTML = xhr.responseText;

      /* Re-execute any <script> elements in the response */
      executeScripts(contentEl);

      /* Update navbar title + document title from hidden element */
      var meta = document.getElementById('spa-page');
      if (meta) {
        var t = meta.dataset.title || '';
        var s = meta.dataset.subtitle || '';
        if (titleEl)    titleEl.textContent    = t;
        if (subtitleEl) subtitleEl.textContent = s || '\u00A0';
        document.title = t ? t + ' \u00B7 Bird\'s Nest POS' : 'Bird\'s Nest POS';
      }

      /* Close mobile sidebar after navigation */
      if (window.innerWidth <= 768) {
        var sb = document.getElementById('appSidebar');
        var ov = document.querySelector('.sb-overlay');
        if (sb) sb.classList.remove('open');
        if (ov) ov.classList.remove('active');
      }
    };
    xhr.send();
  }

  /* ── Re-run <script> blocks so inline JS takes effect ── */
  function executeScripts(container) {
    container.querySelectorAll('script').forEach(function (old) {
      var s = document.createElement('script');
      Array.from(old.attributes).forEach(function (a) {
        s.setAttribute(a.name, a.value);
      });
      s.textContent = old.textContent;
      old.parentNode.replaceChild(s, old);
    });
  }

  return { init: init, navigate: navigate };
})();

/* Boot */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () { SPA.init(); });
} else {
  SPA.init();
}
