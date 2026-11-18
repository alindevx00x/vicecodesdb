/**
 * ViceCodes Frontend Interactivity (Vanilla JS, Zero Bloat)
 */
(function () {
  'use strict';

  const LS_PLATFORM_KEY = 'vc_platform';
  const LS_LOADOUT_KEY = 'vc_loadout';
  const MAX_LOADOUT = 10;

  // 0. Reading Progress Bar & Sticky Header Shadow
  const progressBar = document.getElementById('vc-progress');
  const siteHeader = document.getElementById('site-header');
  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    if (siteHeader) {
      siteHeader.classList.toggle('scrolled', scrollTop > 12);
    }
    if (progressBar) {
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const pct = docHeight > 0 ? Math.min(1, Math.max(0, scrollTop / docHeight)) : 0;
      progressBar.style.transform = `scaleX(${pct})`;
    }
  }, { passive: true });

  // 1. Toast Notification
  const toastEl = document.getElementById('vc-toast');
  let toastTimer = null;
  function showToast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toastEl.classList.remove('show'), 2600);
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  async function copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
    } catch {
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
    }
  }

  // 2. Mobile Nav Drawer
  const menuBtn = document.getElementById('mobile-menu-btn');
  const drawer = document.getElementById('mobile-drawer');
  if (menuBtn && drawer) {
    menuBtn.addEventListener('click', () => {
      const open = drawer.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // 3. One-Click Copy Code + Clipboard Fallback
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-copy-text]');
    if (!btn) return;
    const text = btn.getAttribute('data-copy-text') || '';
    if (!text) return;
    await copyToClipboard(text);
    const orig = btn.textContent;
    btn.textContent = 'Copied!';
    showToast('Code copied to clipboard!');
    setTimeout(() => { btn.textContent = orig; }, 1600);
  });

  // 4. Global Persistent Platform Preference & Cheat Card Tabs
  function getActivePlatform() {
    const initial = window.VC_APP?.initialPlatform || '';
    if (initial) return initial;
    try {
      return localStorage.getItem(LS_PLATFORM_KEY) || '';
    } catch {
      return '';
    }
  }

  function setSavedPlatform(plat) {
    try {
      if (plat) {
        localStorage.setItem(LS_PLATFORM_KEY, plat);
      }
    } catch {}
  }

  function switchCardPlatform(card, plat) {
    const targetTab = card.querySelector(`.plat-tab[data-plat="${plat}"]`);
    if (!targetTab) return;
    card.querySelectorAll('.plat-tab').forEach(t => t.classList.remove('active'));
    targetTab.classList.add('active');
    card.querySelectorAll('.plat-code-pane').forEach(pane => {
      pane.style.display = pane.getAttribute('data-plat-pane') === plat ? 'flex' : 'none';
    });
  }

  function applyPlatformEverywhere(plat) {
    if (!plat) return;
    document.querySelectorAll('[data-cheat-card]').forEach(card => {
      switchCardPlatform(card, plat);
    });
  }

  document.addEventListener('click', (e) => {
    const tab = e.target.closest('.plat-tab');
    if (!tab) return;
    const card = tab.closest('[data-cheat-card]');
    if (!card) return;
    const plat = tab.getAttribute('data-plat') || '';
    switchCardPlatform(card, plat);
    if (plat) {
      setSavedPlatform(plat);
      applyPlatformEverywhere(plat);
      renderLoadoutDrawer();
    }
  });

  document.querySelectorAll('[data-global-plat]').forEach(pill => {
    pill.addEventListener('click', () => {
      const p = pill.getAttribute('data-global-plat') || '';
      try {
        if (p) {
          localStorage.setItem(LS_PLATFORM_KEY, p);
        } else {
          localStorage.removeItem(LS_PLATFORM_KEY);
        }
      } catch {}
    });
  });

  const bootPlat = getActivePlatform();
  if (bootPlat) {
    if (window.VC_APP?.initialPlatform) {
      setSavedPlatform(window.VC_APP.initialPlatform);
    }
    applyPlatformEverywhere(bootPlat);
  }

  // 5. Quick Cheats Loadout Manager (localStorage, max 10, shareable URL)
  const loadoutBar = document.getElementById('vc-loadout-bar');
  const loadoutDrawer = document.getElementById('vc-loadout-drawer');
  const loadoutCountEl = document.getElementById('vc-loadout-count');
  const loadoutItemsEl = document.getElementById('vc-loadout-items');
  const loadoutOpenBtn = document.getElementById('vc-loadout-open');
  const loadoutCloseBtn = document.getElementById('vc-loadout-close');
  const loadoutShareBtn = document.getElementById('vc-loadout-share');
  const loadoutShareDrawerBtn = document.getElementById('vc-loadout-share-drawer');
  const loadoutClearBtn = document.getElementById('vc-loadout-clear');

  function getLoadout() {
    try {
      const raw = localStorage.getItem(LS_LOADOUT_KEY);
      const parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }

  function saveLoadout(items) {
    try {
      localStorage.setItem(LS_LOADOUT_KEY, JSON.stringify(items.slice(0, MAX_LOADOUT)));
    } catch {}
    syncLoadoutUi();
  }

  function openLoadoutDrawer() {
    if (!loadoutDrawer) return;
    loadoutDrawer.classList.add('open');
    loadoutDrawer.setAttribute('aria-hidden', 'false');
  }

  function closeLoadoutDrawer() {
    if (!loadoutDrawer) return;
    loadoutDrawer.classList.remove('open');
    loadoutDrawer.setAttribute('aria-hidden', 'true');
  }

  function renderLoadoutDrawer() {
    if (!loadoutItemsEl) return;
    const items = getLoadout();
    const activePlat = getActivePlatform() || 'ps5';

    if (items.length === 0) {
      loadoutItemsEl.innerHTML = '<p class="muted" style="font-size:14px;">No cheats pinned yet. Click ★ on any cheat card to save it here.</p>';
      return;
    }

    loadoutItemsEl.innerHTML = items.map(item => {
      const seq = item[activePlat] || item.ps5 || item.xbox || item.pc || item.phone || 'Coming at launch';
      const platLabel = activePlat === 'xbox' ? 'XBOX' : activePlat.toUpperCase();
      return `
        <div class="loadout-item">
          <div class="loadout-item-head">
            <a href="${escapeHtml(item.url)}">${escapeHtml(item.title)}</a>
            <button type="button" class="fav-pin-btn active" data-loadout-remove="${escapeHtml(item.slug)}" title="Remove from Quick Cheats" aria-label="Remove ${escapeHtml(item.title)}">✕</button>
          </div>
          <div style="font-size:11px;font-family:var(--f-mono);color:var(--text-secondary);margin-bottom:6px;">
            ${escapeHtml(platLabel)} · Patch ${escapeHtml(item.patch || 'v1.00')}
          </div>
          <div class="loadout-item-seq">${escapeHtml(seq)}</div>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            ${item.phone ? `<span style="font-size:12px;color:var(--color-2);">Dial: <strong>${escapeHtml(item.phone)}</strong></span>` : '<span></span>'}
            <button type="button" class="copy-btn" data-copy-text="${escapeHtml(seq)}">Copy</button>
          </div>
        </div>
      `;
    }).join('');
  }

  function syncLoadoutUi() {
    const items = getLoadout();
    const slugs = new Set(items.map(i => i.slug));

    document.querySelectorAll('[data-loadout-toggle]').forEach(btn => {
      try {
        const payload = JSON.parse(btn.getAttribute('data-loadout-toggle') || '{}');
        const active = slugs.has(payload.slug);
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        if (btn.classList.contains('fav-pin-text-btn')) {
          btn.textContent = active ? '★ Saved in Quick Cheats' : '★ Save to Quick Cheats';
        }
      } catch {}
    });

    if (loadoutCountEl) loadoutCountEl.textContent = String(items.length);
    if (loadoutBar) loadoutBar.hidden = items.length === 0;
    renderLoadoutDrawer();
  }

  document.addEventListener('click', (e) => {
    const toggleBtn = e.target.closest('[data-loadout-toggle]');
    if (toggleBtn) {
      try {
        const payload = JSON.parse(toggleBtn.getAttribute('data-loadout-toggle') || '{}');
        if (!payload.slug) return;
        const items = getLoadout();
        const idx = items.findIndex(i => i.slug === payload.slug);
        if (idx >= 0) {
          items.splice(idx, 1);
          saveLoadout(items);
          showToast('Removed from Quick Cheats.');
        } else {
          if (items.length >= MAX_LOADOUT) {
            showToast(`Quick Cheats loadout is full (max ${MAX_LOADOUT}).`);
            return;
          }
          items.push(payload);
          saveLoadout(items);
          showToast('Saved to My Quick Cheats!');
        }
      } catch {}
      return;
    }

    const removeBtn = e.target.closest('[data-loadout-remove]');
    if (removeBtn) {
      const slug = removeBtn.getAttribute('data-loadout-remove');
      const items = getLoadout().filter(i => i.slug !== slug);
      saveLoadout(items);
      showToast('Removed from Quick Cheats.');
    }
  });

  if (loadoutOpenBtn) loadoutOpenBtn.addEventListener('click', openLoadoutDrawer);
  if (loadoutCloseBtn) loadoutCloseBtn.addEventListener('click', closeLoadoutDrawer);

  async function shareCurrentLoadout() {
    const items = getLoadout();
    if (items.length === 0) return;
    const base = window.VC_APP?.cheatsUrl || '/cheats';
    const full = new URL(base, window.location.origin);
    full.searchParams.set('loadout', items.map(i => i.slug).join(','));
    await copyToClipboard(full.toString());
    showToast('Shareable loadout link copied!');
  }

  if (loadoutShareBtn) loadoutShareBtn.addEventListener('click', shareCurrentLoadout);
  if (loadoutShareDrawerBtn) loadoutShareDrawerBtn.addEventListener('click', shareCurrentLoadout);

  if (loadoutClearBtn) {
    loadoutClearBtn.addEventListener('click', () => {
      saveLoadout([]);
      closeLoadoutDrawer();
      showToast('Quick Cheats cleared.');
    });
  }

  const importSharedBtn = document.getElementById('vc-import-shared-loadout');
  if (importSharedBtn) {
    importSharedBtn.addEventListener('click', () => {
      const section = importSharedBtn.closest('.paper-card');
      if (!section) return;
      const cards = section.querySelectorAll('[data-loadout-toggle]');
      const current = getLoadout();
      const existing = new Set(current.map(i => i.slug));
      let added = 0;

      cards.forEach(btn => {
        if (current.length >= MAX_LOADOUT) return;
        try {
          const payload = JSON.parse(btn.getAttribute('data-loadout-toggle') || '{}');
          if (payload.slug && !existing.has(payload.slug)) {
            current.push(payload);
            existing.add(payload.slug);
            added++;
          }
        } catch {}
      });

      saveLoadout(current);
      openLoadoutDrawer();
      showToast(added > 0 ? `Imported ${added} cheat(s) to My Quick Cheats!` : 'Loadout already saved.');
    });
  }

  syncLoadoutUi();

  // 6. Community "Still Working?" Feedback AJAX
  const feedbackWidget = document.querySelector('.feedback-widget');
  if (feedbackWidget) {
    const endpoint = feedbackWidget.getAttribute('data-feedback-url') || '';
    const csrf = feedbackWidget.getAttribute('data-csrf') || '';
    const summaryEl = document.getElementById('vc-feedback-summary');
    const barEl = document.getElementById('vc-feedback-bar');
    const workingEl = document.getElementById('vc-votes-working');
    const brokenEl = document.getElementById('vc-votes-broken');

    feedbackWidget.querySelectorAll('.js-vote-btn').forEach(btn => {
      btn.addEventListener('click', async () => {
        if (!endpoint) return;
        const vote = btn.getAttribute('data-vote') || 'working';
        const btns = feedbackWidget.querySelectorAll('.js-vote-btn');
        btns.forEach(b => { b.disabled = true; });

        try {
          const fd = new FormData();
          fd.append('_csrf_token', csrf);
          fd.append('vote', vote);

          const res = await fetch(endpoint, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
          });
          const data = await res.json();
          if (data.ok) {
            const totalVotes = Number(data.total ?? data.total_votes ?? 0);
            if (workingEl) workingEl.textContent = String(data.votes_working);
            if (brokenEl) brokenEl.textContent = String(data.votes_broken);
            if (barEl) barEl.style.width = `${data.working_pct}%`;
            if (summaryEl) {
              summaryEl.innerHTML = `<strong>${data.working_pct}%</strong> of <strong>${totalVotes}</strong> player${totalVotes === 1 ? '' : 's'} confirmed working (${data.votes_working} working · ${data.votes_broken} reported issue)`;
            }
          }
          showToast(data.message || 'Feedback recorded.');
        } catch {
          showToast('Could not submit report right now.');
        } finally {
          btns.forEach(b => { b.disabled = false; });
        }
      });
    });
  }

  // 7. Interactive Vice City Phone Dialer (DTMF Web Audio + Keypad Animation)
  const dialerCard = document.querySelector('[data-phone-dialer]');
  if (dialerCard) {
    const targetNumber = dialerCard.getAttribute('data-phone-dialer') || '';
    const screenEl = document.getElementById('vc-dialer-screen');
    const playBtn = document.getElementById('vc-dialer-play');
    let audioCtx = null;

    const dtmfMap = {
      '1': [697, 1209], '2': [697, 1336], '3': [697, 1477],
      '4': [770, 1209], '5': [770, 1336], '6': [770, 1477],
      '7': [852, 1209], '8': [852, 1336], '9': [852, 1477],
      '*': [941, 1209], '0': [941, 1336], '#': [941, 1477]
    };

    const t9Map = {
      A: '2', B: '2', C: '2', D: '3', E: '3', F: '3',
      G: '4', H: '4', I: '4', J: '5', K: '5', L: '5',
      M: '6', N: '6', O: '6', P: '7', Q: '7', R: '7', S: '7',
      T: '8', U: '8', V: '8', W: '9', X: '9', Y: '9', Z: '9'
    };

    function playDtmf(digit) {
      const freqs = dtmfMap[digit];
      if (!freqs) return;
      try {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return;
        if (!audioCtx) audioCtx = new Ctx();
        if (audioCtx.state === 'suspended') audioCtx.resume();

        const now = audioCtx.currentTime;
        const gain = audioCtx.createGain();
        gain.gain.setValueAtTime(0.08, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.12);
        gain.connect(audioCtx.destination);

        freqs.forEach(freq => {
          const osc = audioCtx.createOscillator();
          osc.type = 'sine';
          osc.frequency.setValueAtTime(freq, now);
          osc.connect(gain);
          osc.start(now);
          osc.stop(now + 0.12);
        });
      } catch {}
    }

    function flashKey(digit) {
      const keyBtn = dialerCard.querySelector(`.phone-key[data-dial-digit="${digit}"]`);
      if (keyBtn) {
        keyBtn.classList.add('lit');
        setTimeout(() => keyBtn.classList.remove('lit'), 150);
      }
      playDtmf(digit);
    }

    dialerCard.querySelectorAll('.phone-key').forEach(btn => {
      btn.addEventListener('click', () => {
        const d = btn.getAttribute('data-dial-digit') || '';
        flashKey(d);
      });
    });

    if (playBtn) {
      let dialTimer = null;
      playBtn.addEventListener('click', () => {
        clearInterval(dialTimer);
        const chars = targetNumber.toUpperCase().split('').filter(ch => /[0-9*#A-Z]/.test(ch));
        if (chars.length === 0) return;

        let i = 0;
        if (screenEl) screenEl.textContent = '';
        playBtn.disabled = true;

        dialTimer = setInterval(() => {
          if (i >= chars.length) {
            clearInterval(dialTimer);
            playBtn.disabled = false;
            if (screenEl) screenEl.textContent = targetNumber;
            return;
          }
          const rawChar = chars[i];
          const digit = dtmfMap[rawChar] ? rawChar : (t9Map[rawChar] || '');
          if (digit) flashKey(digit);
          if (screenEl) screenEl.textContent += rawChar;
          i++;
        }, 185);
      });
    }
  }

  // 8. FAQ Accordion
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.faq-btn');
    if (!btn) return;
    const item = btn.closest('.faq-item');
    if (!item) return;
    const isOpen = item.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // 9. Launch Countdown Timer (Pre-Launch Mode)
  const cdWrap = document.getElementById('launch-countdown');
  if (cdWrap) {
    const targetIso = cdWrap.getAttribute('data-launch-date') || '2026-11-19T00:00:00Z';
    const targetMs = new Date(targetIso).getTime();

    function tick() {
      const diff = targetMs - Date.now();
      if (isNaN(targetMs) || diff <= 0) {
        ['days', 'hours', 'mins', 'secs'].forEach(k => {
          const el = cdWrap.querySelector(`[data-cd="${k}"]`);
          if (el) el.textContent = '00';
        });
        return;
      }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);

      const vals = {
        days: String(d).padStart(2, '0'),
        hours: String(h).padStart(2, '0'),
        mins: String(m).padStart(2, '0'),
        secs: String(s).padStart(2, '0'),
      };
      Object.entries(vals).forEach(([k, v]) => {
        const el = cdWrap.querySelector(`[data-cd="${k}"]`);
        if (el) el.textContent = v;
      });
    }
    tick();
    setInterval(tick, 1000);
  }

  // 10. AJAX Cheat Directory Filtering
  const filterForm = document.getElementById('cheat-filter-form');
  const gridEl = document.getElementById('cheats-results-grid');
  const countEl = document.getElementById('cheats-results-count');
  if (filterForm && gridEl) {
    const endpoint = filterForm.getAttribute('data-ajax-url');
    let debounceTimer = null;

    async function runFilter() {
      const params = new URLSearchParams(new FormData(filterForm));
      try {
        gridEl.style.opacity = '0.55';
        const res = await fetch(`${endpoint}?${params.toString()}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        gridEl.innerHTML = data.html;
        if (countEl) countEl.textContent = `${data.count} cheat${data.count === 1 ? '' : 's'} found`;
        applyPlatformEverywhere(getActivePlatform());
        syncLoadoutUi();
      } catch (err) {
        console.error(err);
      } finally {
        gridEl.style.opacity = '1';
      }
    }

    filterForm.querySelectorAll('select').forEach(sel => {
      sel.addEventListener('change', () => {
        if (sel.name === 'platform' && sel.value) {
          setSavedPlatform(sel.value);
        }
        runFilter();
      });
    });
    const qInput = filterForm.querySelector('input[name="q"]');
    if (qInput) {
      qInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(runFilter, 250);
      });
    }
    filterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      runFilter();
    });
  }

  // 11. AJAX Newsletter Submit
  document.querySelectorAll('.js-newsletter-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const statusBox = form.querySelector('.js-newsletter-msg');
      const btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (statusBox) {
          statusBox.innerHTML = `<div class="flash ${data.ok ? 'flash-success' : 'flash-error'}">${data.message}</div>`;
        }
        if (data.ok) form.reset();
      } catch {
        form.submit();
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  });

  // 12. PWA Service Worker Registration (Offline TV-Side Reference)
  if ('serviceWorker' in navigator && window.VC_APP?.swUrl) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register(window.VC_APP.swUrl).catch(() => {});
    });
  }

  // 13. Instant Command Palette Search (Cmd/Ctrl + K via /api/cheats.json)
  const cmdkModal = document.getElementById('vc-cmdk-modal');
  const cmdkInput = document.getElementById('vc-cmdk-input');
  const cmdkResults = document.getElementById('vc-cmdk-results');
  if (cmdkModal && cmdkInput && cmdkResults) {
    let cheatsCache = null;
    let activePlat = '';
    let activeIndex = 0;
    let lastFiltered = [];

    const escHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (ch) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[ch]));

    const loadCheatsIndex = async () => {
      if (cheatsCache) return cheatsCache;
      const apiUrl = window.VC_APP?.apiCheatsUrl || '/api/cheats.json';
      try {
        const res = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        cheatsCache = Array.isArray(data.cheats) ? data.cheats : [];
      } catch {
        cheatsCache = [];
      }
      return cheatsCache;
    };

    const renderCmdk = () => {
      const q = cmdkInput.value.trim().toLowerCase();
      const items = (cheatsCache || []).filter((c) => {
        if (activePlat && !c[activePlat]) return false;
        if (!q) return true;
        return (
          c.title.toLowerCase().includes(q) ||
          c.category.toLowerCase().includes(q) ||
          c.short.toLowerCase().includes(q) ||
          c.ps5.toLowerCase().includes(q) ||
          c.xbox.toLowerCase().includes(q) ||
          c.pc.toLowerCase().includes(q) ||
          c.phone.toLowerCase().includes(q)
        );
      }).slice(0, 12);

      lastFiltered = items;
      if (activeIndex >= items.length) activeIndex = 0;

      if (items.length === 0) {
        cmdkResults.innerHTML = '<div class="cmdk-empty">No matching GTA 6 cheats found.</div>';
        return;
      }

      cmdkResults.innerHTML = items.map((c, idx) => {
        const codeText = activePlat ? (c[activePlat] || c.phone || 'Tracked') : (c.ps5 || c.xbox || c.pc || c.phone || 'Coming at launch');
        const statusLabel = c.status.replace('_', ' ');
        return `
          <div class="cmdk-item ${idx === activeIndex ? 'active' : ''}" data-cmdk-idx="${idx}">
            <a class="cmdk-item-main" href="${escHtml(c.url)}">
              <div class="cmdk-item-top">
                <strong>${escHtml(c.title)}</strong>
                <span class="cmdk-cat">${escHtml(c.category)}</span>
                <span class="badge badge-${escHtml(c.status === 'coming_soon' ? 'coming' : c.status)}">${escHtml(statusLabel)}</span>
              </div>
              <div class="cmdk-item-sub">${escHtml(codeText)}</div>
            </a>
            ${codeText && codeText !== 'Coming at launch' ? `<button type="button" class="copy-btn cmdk-copy" data-copy-text="${escHtml(codeText)}">Copy</button>` : ''}
          </div>
        `;
      }).join('');
    };

    const openCmdk = async () => {
      cmdkModal.hidden = false;
      document.body.style.overflow = 'hidden';
      cmdkInput.focus();
      cmdkInput.select();
      if (!cheatsCache) {
        cmdkResults.innerHTML = '<div class="cmdk-empty">Loading instant cheat index...</div>';
        await loadCheatsIndex();
      }
      renderCmdk();
    };

    const closeCmdk = () => {
      cmdkModal.hidden = true;
      document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-open-cmdk]').forEach((btn) => {
      btn.addEventListener('click', openCmdk);
    });
    cmdkModal.querySelectorAll('[data-close-cmdk]').forEach((el) => {
      el.addEventListener('click', closeCmdk);
    });

    cmdkModal.querySelectorAll('[data-cmdk-plat]').forEach((btn) => {
      btn.addEventListener('click', () => {
        activePlat = btn.dataset.cmdkPlat || '';
        cmdkModal.querySelectorAll('[data-cmdk-plat]').forEach((b) => b.classList.toggle('active', b === btn));
        activeIndex = 0;
        renderCmdk();
      });
    });

    cmdkInput.addEventListener('input', () => {
      activeIndex = 0;
      renderCmdk();
    });

    document.addEventListener('keydown', (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        if (cmdkModal.hidden) openCmdk(); else closeCmdk();
        return;
      }
      if (cmdkModal.hidden) return;
      if (e.key === 'Escape') {
        e.preventDefault();
        closeCmdk();
      } else if (e.key === 'ArrowDown' && lastFiltered.length > 0) {
        e.preventDefault();
        activeIndex = (activeIndex + 1) % lastFiltered.length;
        renderCmdk();
      } else if (e.key === 'ArrowUp' && lastFiltered.length > 0) {
        e.preventDefault();
        activeIndex = (activeIndex - 1 + lastFiltered.length) % lastFiltered.length;
        renderCmdk();
      } else if (e.key === 'Enter' && lastFiltered[activeIndex] && document.activeElement === cmdkInput) {
        e.preventDefault();
        window.location.href = lastFiltered[activeIndex].url;
      }
    });
  }

  // 14. Footer Recognition & Trust Badge Carousel
  const badgeSlider = document.querySelector('.badge-slider');
  const badgeTrack = document.querySelector('.badge-track');
  const badgeStyleTag = document.getElementById('dynamic-carousel-style');
  if (badgeSlider && badgeTrack && badgeStyleTag) {
    const originals = Array.from(badgeTrack.children);
    if (originals.length > 0) {
      // Ensure track covers viewport cleanly before duplicating once for seamless 50% scroll
      let guard = 0;
      while (badgeTrack.scrollWidth < Math.max(badgeSlider.clientWidth, 640) && guard < 10) {
        originals.forEach((node) => badgeTrack.appendChild(node.cloneNode(true)));
        guard++;
      }
      const baseNodes = Array.from(badgeTrack.children);
      baseNodes.forEach((node) => badgeTrack.appendChild(node.cloneNode(true)));

      let started = false;
      const startCarousel = () => {
        if (started) return;
        const scrollAmount = badgeTrack.scrollWidth / 2;
        if (scrollAmount <= 0) return;
        started = true;
        const speed = Math.max(20, Number(badgeTrack.dataset.speed || 100));
        const duration = (scrollAmount / speed).toFixed(2);
        badgeStyleTag.textContent = `
          @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(-${scrollAmount}px); }
          }
          .badge-track {
            animation: scroll-left ${duration}s linear infinite;
          }
        `;
      };

      const images = Array.from(badgeTrack.querySelectorAll('img'));
      if (images.length === 0) {
        startCarousel();
      } else {
        let settled = 0;
        const onImgDone = () => {
          settled++;
          if (settled >= images.length) startCarousel();
        };
        images.forEach((img) => {
          if (img.complete) {
            settled++;
          } else {
            img.addEventListener('load', onImgDone, { once: true });
            img.addEventListener('error', onImgDone, { once: true });
          }
        });
        if (settled >= images.length) startCarousel();
        setTimeout(startCarousel, 1500);
      }
    }
  }

  // 15. Homepage Live Stream / YouTube Playlist Switcher
  const mainIframe = document.getElementById('vc-main-video-iframe');
  const mainTitle = document.getElementById('vc-main-video-title');
  const mainBadge = document.getElementById('vc-video-badge');
  const mainExt = document.getElementById('vc-video-ext-link');
  const swapBtns = document.querySelectorAll('[data-yt-swap]');
  if (mainIframe && swapBtns.length > 0) {
    swapBtns.forEach((btn) => {
      btn.addEventListener('click', () => {
        const ytId = btn.dataset.ytSwap;
        const title = btn.dataset.ytTitle || 'GTA 6 Video';
        const url = btn.dataset.ytUrl || `https://www.youtube.com/watch?v=${ytId}`;
        const isLive = btn.dataset.ytLive === '1';
        if (!ytId) return;

        mainIframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(ytId)}?rel=0&autoplay=1`;
        mainIframe.title = title;
        if (mainTitle) mainTitle.textContent = title;
        if (mainExt) mainExt.href = url;
        if (mainBadge) {
          if (isLive) {
            mainBadge.className = 'video-live-pill';
            mainBadge.innerHTML = '<span class="video-live-dot" aria-hidden="true"></span> LIVE NOW';
          } else {
            mainBadge.className = 'video-type-pill';
            mainBadge.textContent = '▶ FEATURED VIDEO';
          }
        }
        swapBtns.forEach((b) => b.classList.toggle('active', b === btn));
      });
    });
  }
})();
