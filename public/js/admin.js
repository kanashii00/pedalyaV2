/* ============================================================
   PEDALYA ENTERPRISE ADMIN — Application JS
   ============================================================ */
(function () {
  'use strict';

  if (!document.body.classList.contains('admin-shell')) return;
  const shell = document.body;

  const $ = (sel, el) => (el || document).querySelector(sel);
  const $$ = (sel, el) => Array.from((el || document).querySelectorAll(sel));

  /* ---------- Theme ---------- */
  const themeToggle = $('#themeToggle');
  function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('pedalya_theme', theme);
    if (themeToggle) {
      const icon = themeToggle.querySelector('i');
      if (icon) icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
    }
  }
  if (themeToggle) {
    const saved = localStorage.getItem('pedalya_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(saved || (prefersDark ? 'dark' : 'light'));
    themeToggle.addEventListener('click', () => {
      applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
    });
  }

  /* ---------- Sidebar ---------- */
  const sidebar = $('#adminSidebar');
  const overlay = $('.sidebar-overlay');
  const toggleBtn = $('#sidebarToggle');

  function isMobile() { return window.innerWidth <= 1199.98; }

  function setCollapsed(collapsed) {
    shell.classList.toggle('sidebar-collapsed', collapsed);
    localStorage.setItem('pedalya_sidebar', collapsed ? 'collapsed' : 'expanded');
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      if (isMobile()) {
        shell.classList.toggle('sidebar-open');
        if (overlay) overlay.classList.toggle('show');
      } else {
        setCollapsed(!shell.classList.contains('sidebar-collapsed'));
      }
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      shell.classList.remove('sidebar-open');
      overlay.classList.remove('show');
    });
  }
  if (sidebar) {
    const stored = localStorage.getItem('pedalya_sidebar');
    if (stored === 'collapsed' && !isMobile()) setCollapsed(true);
  }
  window.addEventListener('resize', () => {
    if (!isMobile() && shell.classList.contains('sidebar-open')) {
      shell.classList.remove('sidebar-open');
      if (overlay) overlay.classList.remove('show');
    }
  });

  /* Expandable nav groups — only the group header button toggles */
  $$('.admin-nav[data-collapsible] > .admin-nav__link').forEach(btn => {
    btn.addEventListener('click', () => {
      const parent = btn.closest('.admin-nav');
      if (shell.classList.contains('sidebar-collapsed')) setCollapsed(false);
      parent.classList.toggle('admin-nav--open');
    });
  });

  /* Ensure exactly one sidebar link is active, matching the current URL.
     Belt-and-suspenders alongside the server-side $isActive logic. */
  function syncActiveFromUrl() {
    const links = $$('.admin-nav__link[href]');
    if (!links.length) return;
    const cur = location.pathname + location.search;
    let match = null;
    for (const l of links) {
      const u = new URL(l.href);
      if ((u.pathname + u.search) === cur) { match = l; break; }
    }
    if (!match) return;
    links.forEach(l => {
      const on = l === match;
      l.classList.toggle('active', on);
      l.setAttribute('aria-current', on ? 'page' : 'false');
    });
    const parent = match.closest('.admin-nav[data-collapsible]');
    if (parent) parent.classList.add('admin-nav--open');
  }
  syncActiveFromUrl();
  window.addEventListener('hashchange', syncActiveFromUrl);

  /* ---------- Live clock ---------- */
  const clock = $('#adminClock');
  if (clock) {
    function tick() {
      const now = new Date();
      const opts = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
      clock.textContent = now.toLocaleDateString('en-US', opts).replace(',', ' •');
    }
    tick();
    setInterval(tick, 1000);
  }

  /* ---------- Connection indicators ---------- */
  const indicators = {
    iot: $('#connIoT'),
    gps: $('#connGPS'),
    ws: $('#connWS'),
  };
  function setConn(key, online) {
    const el = indicators[key];
    if (!el) return;
    el.classList.toggle('online', online);
    el.classList.toggle('offline', !online);
    const dot = el.querySelector('.dot');
    const label = el.querySelector('.label');
    if (label) label.textContent = online ? (el.dataset.on || 'Online') : (el.dataset.off || 'Offline');
    void dot; // dot animation controlled by CSS
  }
  if (window.PedalyaStatus) {
    if (window.PedalyaStatus.iot !== undefined) setConn('iot', window.PedalyaStatus.iot);
    if (window.PedalyaStatus.gps !== undefined) setConn('gps', window.PedalyaStatus.gps);
    if (window.PedalyaStatus.ws !== undefined) setConn('ws', window.PedalyaStatus.ws);
  }
  if (window.Echo) {
    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
      setConn('ws', states.current === 'connected');
    });
    setConn('ws', window.Echo.connector.pusher.connection.state === 'connected');
  }

  /* ---------- Global search ---------- */
  const searchInput = $('#adminSearchInput');
  const searchResults = $('#adminSearchResults');
  if (searchInput && searchResults) {
    const navLinks = $$('.admin-nav__link[href]').map(a => ({
      href: a.href,
      label: a.querySelector('span') ? a.querySelector('span').textContent.trim() : a.dataset.tooltip,
      group: (a.closest('.admin-navgroup__label') ? a.closest('.admin-navgroup__label').textContent.trim() : '') || 'Navigation',
    }));

    function render(filter) {
      const q = (filter || '').toLowerCase();
      searchResults.innerHTML = '';
      const matches = navLinks.filter(i => !q || i.label.toLowerCase().includes(q));
      if (!matches.length) {
        searchResults.innerHTML = '<div class="admin-search__item"><i class="bi bi-search"></i>No results found</div>';
      }
      matches.slice(0, 12).forEach(i => {
        const a = document.createElement('a');
        a.className = 'admin-search__item';
        a.href = i.href;
        a.innerHTML = '<i class="bi bi-arrow-up-right"></i><span>' + i.label + '</span><span class="k">' + i.group + '</span>';
        a.addEventListener('click', () => { hide(); searchInput.value = ''; });
        searchResults.appendChild(a);
      });
      searchResults.classList.add('show');
    }
    function hide() { searchResults.classList.remove('show'); }
    searchInput.addEventListener('input', (e) => {
      if (e.target.value.trim()) render(e.target.value);
      else hide();
    });
    searchInput.addEventListener('focus', () => { if (searchInput.value.trim()) render(searchInput.value); });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.admin-search')) hide();
    });
    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        searchInput.focus();
      }
      if (e.key === 'Escape') hide();
    });
  }

  /* ---------- Dropdowns ---------- */
  $$('.admin-dropdown').forEach(dd => {
    const btn = dd.querySelector('[data-dropdown-toggle]');
    const menu = dd.querySelector('.admin-dropdown__menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      $$('.admin-dropdown__menu.open').forEach(m => { if (m !== menu) m.classList.remove('open'); });
      menu.classList.toggle('open');
    });
  });
  document.addEventListener('click', () => {
    $$('.admin-dropdown__menu.open').forEach(m => m.classList.remove('open'));
  });

  /* ---------- Toast system ---------- */
  const toasts = $('#adminToasts');
  function toast(title, msg, type) {
    if (!toasts) return;
    const t = document.createElement('div');
    t.className = 'admin-toast admin-toast--' + (type || 'info');
    const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    t.innerHTML =
      '<i class="bi ' + (icons[type] || icons.info) + ' admin-toast__icon"></i>' +
      '<div><div class="admin-toast__title">' + (title || '') + '</div>' +
      '<div class="admin-toast__msg">' + (msg || '') + '</div></div>' +
      '<button class="admin-toast__close" aria-label="Close"><i class="bi bi-x-lg"></i></button>';
    toasts.appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    const remove = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 320); };
    t.querySelector('.admin-toast__close').addEventListener('click', remove);
    setTimeout(remove, type === 'error' ? 7000 : 4200);
  }
  window.PedalyaToast = { success: (m, t) => toast(t || 'Success', m, 'success'), error: (m, t) => toast(t || 'Error', m, 'error'), warning: (m, t) => toast(t || 'Warning', m, 'warning'), info: (m, t) => toast(t || 'Info', m, 'info') };

  /* ---------- Confirm dialog ---------- */
  const confirmModal = $('#adminConfirm');
  const confirmBackdrop = confirmModal && $('.admin-modal__backdrop', confirmModal);
  let confirmAction = null;

  window.PedalyaConfirm = function (options) {
    if (!confirmModal) return Promise.resolve();
    return new Promise((resolve) => {
      $('#adminConfirmTitle').textContent = options.title || 'Are you sure?';
      $('#adminConfirmMsg').textContent = options.message || 'This action cannot be undone.';
      $('#adminConfirmOk').textContent = options.confirmText || 'Confirm';
      $('#adminConfirmOk').className = 'btn-admin ' + (options.danger ? 'btn-admin--danger' : 'btn-admin--primary');
      confirmModal.classList.add('open');
      confirmAction = resolve;
    });
  };
  function closeConfirm() {
    confirmModal.classList.remove('open');
    if (confirmAction) { const cb = confirmAction; confirmAction = null; cb(false); }
  }
  if (confirmModal) {
    $('#adminConfirmOk').addEventListener('click', () => {
      confirmModal.classList.remove('open');
      if (confirmAction) { const cb = confirmAction; confirmAction = null; cb(true); }
    });
    $('#adminConfirmCancel').addEventListener('click', closeConfirm);
    if (confirmBackdrop) confirmBackdrop.addEventListener('click', closeConfirm);
  }

  /* Confirm on [data-confirm] links/buttons */
  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;
    e.preventDefault();
    const form = el.closest('form');
    PedalyaConfirm({
      title: el.dataset.confirmTitle || 'Are you sure?',
      message: el.dataset.confirm || 'This action cannot be undone.',
      confirmText: el.dataset.confirmOk || 'Confirm',
      danger: el.dataset.confirmDanger !== 'false',
    }).then(ok => {
      if (ok) { if (form) form.submit(); else window.location = el.href; }
    });
  });

  /* ---------- Modal / drawer helpers ---------- */
  window.PedalyaModal = {
    open: (id) => { const m = document.getElementById(id); if (m) m.classList.add('open'); },
    close: (id) => { const m = document.getElementById(id); if (m) m.classList.remove('open'); },
  };
  window.PedalyaDrawer = {
    open: (id) => { const d = document.getElementById(id); if (d) d.classList.add('open'); },
    close: (id) => { const d = document.getElementById(id); if (d) d.classList.remove('open'); },
  };
  document.addEventListener('click', (e) => {
    const close = e.target.closest('[data-modal-close]');
    if (close) { const modal = close.closest('.admin-modal'); if (modal) modal.classList.remove('open'); }
    const drawer = e.target.closest('[data-drawer-close]');
    if (drawer) { const d = drawer.closest('.admin-drawer'); if (d) d.classList.remove('open'); }
  });

  /* ---------- Tabs ---------- */
  document.addEventListener('click', (e) => {
    const tab = e.target.closest('[data-tab]');
    if (!tab) return;
    const scope = tab.closest('[data-tabscope]') || document;
    $$('[data-tab]', scope).forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    $$('[data-tabpanel]', scope).forEach(p => p.classList.toggle('active', p.dataset.tabpanel === tab.dataset.tab));
  });

  /* ---------- Table enhancements ---------- */
  function initTables(scope) {
    $$('.admin-table', scope).forEach(table => {
      const wrap = table.closest('.admin-table-wrap');
      if (!wrap) return;

      /* Select all + row selection */
      const selectAll = table.querySelector('thead input.admin-check');
      const checkboxes = $$('tbody input.admin-check', table);
      const bulk = wrap.querySelector('.admin-bulk');
      const bulkCount = bulk && bulk.querySelector('.admin-bulk__count');
      if (selectAll) {
        selectAll.addEventListener('change', () => {
          checkboxes.forEach(c => { c.checked = selectAll.checked; c.closest('tr').classList.toggle('selected', c.checked); });
          updateBulk();
        });
      }
      checkboxes.forEach(c => c.addEventListener('change', () => {
        c.closest('tr').classList.toggle('selected', c.checked);
        if (selectAll) selectAll.checked = checkboxes.every(x => x.checked);
        updateBulk();
      }));
      function updateBulk() {
        const n = checkboxes.filter(c => c.checked).length;
        if (bulk && bulkCount) {
          bulkCount.textContent = n + ' selected';
          bulk.classList.toggle('show', n > 0);
        }
      }
      if (bulk) {
        const clear = bulk.querySelector('[data-bulk-clear]');
        if (clear) clear.addEventListener('click', () => {
          checkboxes.forEach(c => { c.checked = false; c.closest('tr').classList.remove('selected'); });
          if (selectAll) selectAll.checked = false;
          updateBulk();
        });
      }

      /* Client-side search */
      const search = wrap.querySelector('[data-table-search]');
      if (search) {
        search.addEventListener('input', () => {
          const q = search.value.toLowerCase();
          $$('tbody tr', table).forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
          });
        });
      }

      /* Client-side sort */
      $$('thead th.sortable', table).forEach(th => {
        th.addEventListener('click', () => {
          const idx = $$('thead th', table).indexOf(th);
          const rows = $$('tbody tr', table);
          const dir = th.dataset.dir === 'asc' ? 'desc' : 'asc';
          $$('thead th', table).forEach(h => { h.dataset.dir = ''; h.querySelector('.sort-ind').textContent = ''; });
          th.dataset.dir = dir;
          th.querySelector('.sort-ind').textContent = dir === 'asc' ? '↑' : '↓';
          rows.sort((a, b) => {
            const av = $$('td', a)[idx] ? $$('td', a)[idx].textContent.trim() : '';
            const bv = $$('td', b)[idx] ? $$('td', b)[idx].textContent.trim() : '';
            const num = /^-?\d[\d.,]*$/.test(av) && /^-?\d[\d.,]*$/.test(bv);
            const cmp = num ? parseFloat(av) - parseFloat(bv) : av.localeCompare(bv);
            return dir === 'asc' ? cmp : -cmp;
          });
          rows.forEach(r => table.querySelector('tbody').appendChild(r));
        });
      });

      /* Pagination */
      const pager = wrap.querySelector('[data-pager]');
      const pageSizeInput = wrap.querySelector('[data-page-size]');
      if (pager) {
        const size = pageSizeInput ? parseInt(pageSizeInput.value, 10) || 10 : 10;
        let page = 0;
        function renderPager() {
          const rows = $$('tbody tr', table).filter(r => r.style.display !== 'none');
          const pages = Math.max(1, Math.ceil(rows.length / size));
          page = Math.min(page, pages - 1);
          rows.forEach((r, i) => { r.style.display = (i >= page * size && i < (page + 1) * size) ? '' : 'none'; });
          pager.innerHTML = '';
          const btn = (label, disabled, active, cb) => {
            const b = document.createElement('button');
            b.innerHTML = label;
            b.disabled = !!disabled;
            if (active) b.className = 'active';
            b.addEventListener('click', cb);
            pager.appendChild(b);
          };
          btn('«', page === 0, false, () => { page = 0; renderPager(); });
          btn('‹', page === 0, false, () => { page--; renderPager(); });
          for (let i = 0; i < pages; i++) {
            if (pages > 7 && i !== 0 && i !== pages - 1 && Math.abs(i - page) > 1) {
              if (Math.abs(i - page) === 2) btn('…', true, false, () => {});
              continue;
            }
            btn(String(i + 1), false, i === page, () => { page = i; renderPager(); });
          }
          btn('›', page >= pages - 1, false, () => { page++; renderPager(); });
          btn('»', page >= pages - 1, false, () => { page = pages - 1; renderPager(); });
        }
        renderPager();
        if (pageSizeInput) pageSizeInput.addEventListener('change', () => renderPager());
      }
    });
  }
  initTables(document);

  /* ---------- Live reload of sidebar badges via Echo ---------- */
  if (window.Echo && window.PedalyaChannels) {
    const chan = window.Echo.private(window.PedalyaChannels.notifications);
    if (chan) {
      chan.listen('.notification.created', (e) => {
        window.PedalyaToast.info(e.title || 'New notification', e.message || '');
        const badge = $('#sidebarNotifBadge');
        if (badge) badge.textContent = parseInt(badge.textContent || '0', 10) + 1;
      });
    }
  }

  /* ---------- Auto-dismiss alerts ---------- */
  setTimeout(() => {
    $$('.alert-pedalya').forEach(a => a.classList.add('fade'));
  }, 5000);
})();
