/**
 * ViceCodes Admin Panel JS (TinyMCE via cdnjs, Draft Restore, Button Toolbar, Media Picker Modal, FAQ/Changelog Builders)
 */
(function () {
  'use strict';

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // 1. Initialize TinyMCE + LocalStorage Draft Restore Banner
  const draftBanner = document.getElementById('vc-draft-restore-banner');
  const restoreBtn = document.getElementById('vc-draft-restore-btn');
  const discardBtn = document.getElementById('vc-draft-discard-btn');
  const tinyTarget = document.querySelector('.js-tinymce');
  const editorForm = tinyTarget ? tinyTarget.closest('form') : null;
  const draftKey = editorForm ? 'vc_draft_' + window.location.pathname : null;

  if (typeof tinymce !== 'undefined' && tinyTarget) {
    const uploadUrl = window.VC_ADMIN?.uploadUrl || '/admin/upload-image';
    const csrfToken = window.VC_ADMIN?.csrfToken || '';

    tinymce.init({
      selector: '.js-tinymce',
      skin: 'oxide',
      content_style: 'body { font-family: "Atkinson Hyperlegible", Arial, sans-serif; background: #FAF6F0; color: #1B1C1D; font-size: 16px; line-height: 1.6; } a { color: #B0281F; }',
      height: 420,
      menubar: false,
      plugins: 'lists link image code table media fullscreen preview searchreplace wordcount autoresize directionality emoticons',
      toolbar: 'undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link image media table | code fullscreen preview | removeformat',
      automatic_uploads: true,
      images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) progress((e.loaded / e.total) * 100);
        };
        xhr.onload = () => {
          if (xhr.status < 200 || xhr.status >= 300) {
            reject('Upload HTTP error: ' + xhr.status);
            return;
          }
          try {
            const json = JSON.parse(xhr.responseText);
            if (!json || typeof json.location !== 'string') {
              reject(json?.error || 'Invalid upload response');
              return;
            }
            resolve(json.location);
          } catch {
            reject('Invalid JSON from upload server');
          }
        };
        xhr.onerror = () => reject('Image upload failed due to network error.');
        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        xhr.send(formData);
      }),
      setup: (editor) => {
        editor.on('init', () => {
          if (!draftKey || !draftBanner) return;
          try {
            const savedDraft = localStorage.getItem(draftKey);
            const current = editor.getContent().trim();
            if (savedDraft && savedDraft.trim() !== '' && savedDraft.trim() !== current) {
              draftBanner.hidden = false;
              if (restoreBtn) {
                restoreBtn.onclick = () => {
                  editor.setContent(savedDraft);
                  editor.save();
                  draftBanner.hidden = true;
                };
              }
              if (discardBtn) {
                discardBtn.onclick = () => {
                  try { localStorage.removeItem(draftKey); } catch {}
                  draftBanner.hidden = true;
                };
              }
            }
          } catch {}
        });

        editor.on('change keyup', () => {
          editor.save();
          if (draftKey) {
            try { localStorage.setItem(draftKey, editor.getContent()); } catch {}
          }
        });
      }
    });

    if (editorForm && draftKey) {
      editorForm.addEventListener('submit', () => {
        try { localStorage.removeItem(draftKey); } catch {}
      });
    }
  }

  // 2. Auto-generate URL slug from Title if slug is empty or untouched
  const titleInput = document.querySelector('[data-slug-source]');
  const slugInput = document.querySelector('[data-slug-target]');
  if (titleInput && slugInput) {
    let manualEdit = slugInput.value.trim() !== '';
    slugInput.addEventListener('input', () => { manualEdit = slugInput.value.trim() !== ''; });
    titleInput.addEventListener('input', () => {
      if (manualEdit) return;
      slugInput.value = titleInput.value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    });
  }

  // 3. Button-Sequence Quick-Insert Toolbar (PS5 / Xbox Cheat Editor)
  document.addEventListener('click', (e) => {
    const chip = e.target.closest('.seq-chip');
    if (!chip) return;
    const toolbar = chip.closest('[data-seq-target]');
    if (!toolbar) return;
    const input = document.getElementById(toolbar.getAttribute('data-seq-target') || '');
    if (!input) return;

    if (chip.hasAttribute('data-seq-clear')) {
      input.value = '';
      input.focus();
      return;
    }
    const token = chip.getAttribute('data-seq-token') || '';
    if (!token) return;
    const cur = input.value.trim();
    input.value = cur === '' ? token : `${cur.replace(/,\s*$/, '')}, ${token}`;
    input.focus();
  });

  // 4. Inline Media Library Picker Modal
  const mediaModal = document.getElementById('vc-media-modal');
  const mediaGrid = document.getElementById('vc-media-modal-grid');
  let activeMediaInput = null;

  function closeMediaModal() {
    if (!mediaModal) return;
    mediaModal.hidden = true;
    activeMediaInput = null;
  }

  document.querySelectorAll('[data-close-media-modal]').forEach(el => {
    el.addEventListener('click', closeMediaModal);
  });

  document.addEventListener('click', async (e) => {
    const trigger = e.target.closest('.js-open-media-picker');
    if (!trigger || !mediaModal || !mediaGrid) return;
    const sel = trigger.getAttribute('data-media-target') || '';
    activeMediaInput = sel ? document.querySelector(sel) : null;
    mediaModal.hidden = false;
    mediaGrid.innerHTML = '<p style="color:var(--adm-muted);">Loading uploaded images...</p>';

    try {
      const url = window.VC_ADMIN?.mediaJsonUrl || '/admin/media/json';
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      const items = Array.isArray(data.items) ? data.items : [];
      if (items.length === 0) {
        mediaGrid.innerHTML = '<p style="color:var(--adm-muted);">No uploaded media yet. Upload an image in Media Library first.</p>';
        return;
      }
      mediaGrid.innerHTML = items.map(item => `
        <button type="button" class="adm-modal-item" data-pick-url="${escapeHtml(item.url_path)}">
          <img src="${escapeHtml(item.url_path)}" alt="${escapeHtml(item.alt_text || item.original_name)}">
          <span>${escapeHtml(item.original_name || item.filename)}</span>
        </button>
      `).join('');
    } catch {
      mediaGrid.innerHTML = '<p style="color:var(--color-2);">Failed to load Media Library.</p>';
    }
  });

  if (mediaGrid) {
    mediaGrid.addEventListener('click', (e) => {
      const picked = e.target.closest('[data-pick-url]');
      if (!picked) return;
      const pickedUrl = picked.getAttribute('data-pick-url') || '';
      if (activeMediaInput && pickedUrl) {
        activeMediaInput.value = pickedUrl;
      }
      closeMediaModal();
    });
  }

  // 5. Dynamic FAQ Row Builder
  const addFaqBtn = document.getElementById('add-faq-row');
  const faqList = document.getElementById('faq-rows');
  if (addFaqBtn && faqList) {
    addFaqBtn.addEventListener('click', () => {
      const row = document.createElement('div');
      row.className = 'faq-builder-row';
      row.style.cssText = 'display:grid;gap:8px;padding:12px;border:1px solid var(--color-6);border-radius:6px;margin-bottom:10px;';
      row.innerHTML = `
        <input type="text" name="faq_q[]" placeholder="Question (e.g. Does this disable trophies?)" class="form-control">
        <textarea name="faq_a[]" rows="2" placeholder="Direct concise answer (40-60 words)..." class="form-control"></textarea>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">Remove FAQ</button>
      `;
      faqList.appendChild(row);
    });
  }

  // 6. Dynamic Changelog Row Builder
  const addClBtn = document.getElementById('add-cl-row');
  const clList = document.getElementById('cl-rows');
  if (addClBtn && clList) {
    addClBtn.addEventListener('click', () => {
      const today = new Date().toISOString().slice(0, 10);
      const row = document.createElement('div');
      row.style.cssText = 'display:grid;grid-template-columns:140px 1fr auto;gap:8px;margin-bottom:8px;';
      row.innerHTML = `
        <input type="text" name="cl_date[]" value="${today}" placeholder="YYYY-MM-DD">
        <input type="text" name="cl_note[]" placeholder="Update note (e.g. Verified on PS5 patch 1.02)">
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
      `;
      clList.appendChild(row);
    });
  }

  // 7. Simple Table Live Search Filter
  const tableSearch = document.getElementById('admin-table-search');
  if (tableSearch) {
    tableSearch.addEventListener('input', () => {
      const q = tableSearch.value.toLowerCase();
      document.querySelectorAll('.adm-table tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // 8. Select All Checkboxes for Bulk Actions
  const checkAll = document.getElementById('bulk-select-all');
  if (checkAll) {
    checkAll.addEventListener('change', () => {
      document.querySelectorAll('input[name="ids[]"]').forEach(cb => { cb.checked = checkAll.checked; });
    });
  }
})();
