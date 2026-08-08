/* ==========================================================================
   LeadFlow CRM — App JS utilities (vanilla JS, no framework)
   ========================================================================== */
(function () {
  'use strict';

  const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  /* ------------------------------------------------------------------ *
   * Sidebar: collapse (desktop, persisted) + slide-out (mobile)
   * ------------------------------------------------------------------ */
  function initSidebar() {
    const shell = document.querySelector('.app-shell');
    if (!shell) return;

    if (localStorage.getItem('sidebar-collapsed') === '1') {
      shell.classList.add('collapsed');
    }

    document.querySelectorAll('[data-toggle="sidebar-collapse"]').forEach((btn) => {
      btn.addEventListener('click', () => {
        shell.classList.toggle('collapsed');
        localStorage.setItem('sidebar-collapsed', shell.classList.contains('collapsed') ? '1' : '0');
      });
    });

    document.querySelectorAll('[data-toggle="sidebar-mobile"]').forEach((btn) => {
      btn.addEventListener('click', () => shell.classList.toggle('mobile-open'));
    });

    document.querySelectorAll('[data-close="sidebar-mobile"]').forEach((el) => {
      el.addEventListener('click', () => shell.classList.remove('mobile-open'));
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') shell.classList.remove('mobile-open');
    });
  }

  /* ------------------------------------------------------------------ *
   * Bootstrap tooltips (icon-only buttons need aria + tooltip)
   * ------------------------------------------------------------------ */
  function initTooltips() {
    if (typeof bootstrap === 'undefined') return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
  }

  /* ------------------------------------------------------------------ *
   * Toasts
   * ------------------------------------------------------------------ */
  const TOAST_ICONS = {
    success: { cls: 'badge-success', icon: 'bi-check-lg' },
    error: { cls: 'badge-danger', icon: 'bi-x-lg' },
    warning: { cls: 'badge-warning', icon: 'bi-exclamation-lg' },
    info: { cls: 'badge-info', icon: 'bi-info-lg' },
  };

  function showToast(message, type) {
    type = type || 'success';
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    const cfg = TOAST_ICONS[type] || TOAST_ICONS.success;
    const item = document.createElement('div');
    item.className = 'toast-item';
    item.setAttribute('role', 'status');
    item.innerHTML =
      '<span class="ic ' + cfg.cls + '"><i class="bi ' + cfg.icon + '"></i></span>' +
      '<span class="msg">' + message + '</span>' +
      '<button type="button" class="tclose" aria-label="Dismiss notification"><i class="bi bi-x"></i></button>' +
      '<span class="tbar"></span>';
    stack.appendChild(item);

    const remove = () => {
      item.classList.add('leaving');
      setTimeout(() => item.remove(), 220);
    };
    item.querySelector('.tclose').addEventListener('click', remove);
    setTimeout(remove, 4000);
  }
  window.showToast = showToast;

  function initFlashToasts() {
    const flash = document.getElementById('flash-data');
    if (!flash) return;
    const success = flash.dataset.success;
    const error = flash.dataset.error;
    if (success) showToast(success, 'success');
    if (error) showToast(error, 'error');
  }

  /* ------------------------------------------------------------------ *
   * Counter animation (0 -> target)
   * ------------------------------------------------------------------ */
  function initCounters() {
    const els = document.querySelectorAll('[data-counter]');
    if (!els.length) return;

    const animate = (el) => {
      const target = parseFloat(el.dataset.counter || '0');
      const prefix = el.dataset.prefix || '';
      const suffix = el.dataset.suffix || '';
      const decimals = parseInt(el.dataset.decimals || '0', 10);
      const duration = 900;
      const start = performance.now();
      const from = 0;
      function tick(now) {
        const p = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - p, 3);
        const val = from + (target - from) * eased;
        el.textContent = prefix + val.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    };

    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) { animate(entry.target); io.unobserve(entry.target); }
        });
      }, { threshold: .3 });
      els.forEach((el) => io.observe(el));
    } else {
      els.forEach(animate);
    }
  }

  /* ------------------------------------------------------------------ *
   * Confirm-delete modal (progressive enhancement over real <form>s)
   * ------------------------------------------------------------------ */
  function initConfirmDelete() {
    let pendingForm = null;
    const modalEl = document.getElementById('confirmDeleteModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    const modal = new bootstrap.Modal(modalEl);
    const titleEl = modalEl.querySelector('[data-role="confirm-title"]');
    const bodyEl = modalEl.querySelector('[data-role="confirm-body"]');
    const confirmBtn = modalEl.querySelector('[data-role="confirm-submit"]');

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        if (form.dataset.confirmed === '1') return;
        e.preventDefault();
        pendingForm = form;
        titleEl.textContent = form.dataset.confirmTitle || 'Delete item?';
        bodyEl.textContent = form.dataset.confirm || 'This action cannot be undone.';
        modal.show();
      });
    });

    confirmBtn.addEventListener('click', () => {
      if (pendingForm) {
        pendingForm.dataset.confirmed = '1';
        modal.hide();
        pendingForm.submit();
      }
    });
  }

  /* ------------------------------------------------------------------ *
   * Global search (Ctrl+K) — fetches a small JSON endpoint
   * ------------------------------------------------------------------ */
  function initGlobalSearch() {
    const trigger = document.querySelector('[data-toggle="global-search"]');
    const modalEl = document.getElementById('globalSearchModal');
    if (!trigger || !modalEl || typeof bootstrap === 'undefined') return;
    const modal = new bootstrap.Modal(modalEl);
    const input = modalEl.querySelector('input[type="search"]');
    const resultsEl = modalEl.querySelector('[data-role="search-results"]');
    const endpoint = modalEl.dataset.endpoint;
    let debounceTimer = null;

    const open = () => { modal.show(); setTimeout(() => input.focus(), 150); };
    trigger.addEventListener('click', open);

    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        open();
      }
    });

    function render(data) {
      if (!data.length) {
        resultsEl.innerHTML = '<div class="text-center py-4 muted small">No matches found.</div>';
        return;
      }
      resultsEl.innerHTML = data.map((group) => (
        '<div class="mb-2"><div class="small fw-bold muted text-uppercase px-1 mb-1" style="font-size:11px;letter-spacing:.05em">' + group.label + '</div>' +
        group.items.map((it) => (
          '<a href="' + it.url + '" class="d-flex align-items-center gap-2 px-2 py-2 rounded-3 text-decoration-none search-result-row">' +
          '<i class="bi ' + it.icon + ' muted"></i>' +
          '<span class="text-truncate"><span class="fw-semibold" style="color:var(--text)">' + it.title + '</span>' +
          (it.subtitle ? '<span class="muted small ms-2">' + it.subtitle + '</span>' : '') + '</span></a>'
        )).join('') + '</div>'
      )).join('');
    }

    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = input.value.trim();
      if (q.length < 2) { resultsEl.innerHTML = '<div class="text-center py-4 muted small">Type at least 2 characters…</div>'; return; }
      debounceTimer = setTimeout(() => {
        fetch(endpoint + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then((r) => r.json())
          .then(render)
          .catch(() => { resultsEl.innerHTML = '<div class="text-center py-4 muted small">Search unavailable.</div>'; });
      }, 250);
    });
  }

  /* ------------------------------------------------------------------ *
   * KPI strip filter (client-side highlight; links to ?status=)
   * ------------------------------------------------------------------ */
  function initFilterDrawer() {
    const drawer = document.getElementById('filterDrawer');
    const backdrop = document.getElementById('filterDrawerBackdrop');
    if (!drawer || !backdrop) return;
    document.querySelectorAll('[data-toggle="filter-drawer"]').forEach((btn) => {
      btn.addEventListener('click', () => { drawer.classList.add('show'); backdrop.classList.add('show'); });
    });
    const close = () => { drawer.classList.remove('show'); backdrop.classList.remove('show'); };
    backdrop.addEventListener('click', close);
    drawer.querySelectorAll('[data-close="filter-drawer"]').forEach((el) => el.addEventListener('click', close));
  }

  /* ------------------------------------------------------------------ *
   * Kanban board (native HTML5 drag & drop) — posts to existing status route
   * ------------------------------------------------------------------ */
  function initKanban() {
    const board = document.querySelector('[data-kanban]');
    if (!board) return;

    let draggedCard = null;

    board.querySelectorAll('.kanban-card').forEach((card) => {
      card.addEventListener('dragstart', () => { draggedCard = card; setTimeout(() => card.classList.add('dragging'), 0); });
      card.addEventListener('dragend', () => { card.classList.remove('dragging'); draggedCard = null; });
    });

    board.querySelectorAll('.kanban-col').forEach((col) => {
      col.addEventListener('dragover', (e) => { e.preventDefault(); col.classList.add('drag-over'); });
      col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
      col.addEventListener('drop', (e) => {
        e.preventDefault();
        col.classList.remove('drag-over');
        if (!draggedCard) return;
        const list = col.querySelector('[data-kanban-list]');
        const newStatus = col.dataset.status;
        const oldStatus = draggedCard.dataset.status;
        if (newStatus === oldStatus) { list.appendChild(draggedCard); return; }

        list.appendChild(draggedCard);
        draggedCard.dataset.status = newStatus;

        fetch(draggedCard.dataset.statusUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
          body: 'status=' + encodeURIComponent(newStatus) + '&_method=POST',
        }).then((r) => {
          if (!r.ok) throw new Error('failed');
          showToast('Lead moved to ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1) + '.', 'success');
          updateKanbanCounts();
        }).catch(() => {
          showToast('Could not update lead status. Reverting.', 'error');
          const originalCol = board.querySelector('.kanban-col[data-status="' + oldStatus + '"] [data-kanban-list]');
          if (originalCol) originalCol.appendChild(draggedCard);
          draggedCard.dataset.status = oldStatus;
          updateKanbanCounts();
        });
      });
    });

    function updateKanbanCounts() {
      board.querySelectorAll('.kanban-col').forEach((col) => {
        const count = col.querySelectorAll('.kanban-card').length;
        const countEl = col.querySelector('[data-role="kanban-count"]');
        if (countEl) countEl.textContent = count;
      });
    }
  }

  /* ------------------------------------------------------------------ *
   * Renewal modal — live-calculates new expiry & amount from plan data
   * ------------------------------------------------------------------ */
  function initRenewalModal() {
    const modalEl = document.getElementById('renewModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    const modal = new bootstrap.Modal(modalEl);
    const form = modalEl.querySelector('form');
    const planSelect = modalEl.querySelector('[name="plan_id"]');
    const amountInput = modalEl.querySelector('[name="amount"]');
    const newExpiryEl = modalEl.querySelector('[data-role="new-expiry"]');
    const clientNameEl = modalEl.querySelector('[data-role="modal-client"]');
    const currentPlanEl = modalEl.querySelector('[data-role="modal-plan"]');
    const currentExpiryEl = modalEl.querySelector('[data-role="modal-expiry"]');
    const successView = modalEl.querySelector('[data-role="success-view"]');
    const formView = modalEl.querySelector('[data-role="form-view"]');

    function computeExpiry() {
      const opt = planSelect.selectedOptions[0];
      if (!opt) return;
      const durationDays = parseInt(opt.dataset.duration || '0', 10);
      const price = opt.dataset.price || '0';
      amountInput.value = price;
      const base = new Date(modalEl.dataset.baseDate || Date.now());
      const newDate = new Date(base.getTime() + durationDays * 86400000);
      newExpiryEl.textContent = newDate.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
    }

    document.querySelectorAll('[data-toggle="renew-modal"]').forEach((btn) => {
      btn.addEventListener('click', () => {
        formView.classList.remove('d-none');
        successView.classList.add('d-none');
        form.action = btn.dataset.action;
        modalEl.dataset.baseDate = btn.dataset.baseDate;
        clientNameEl.textContent = btn.dataset.client;
        currentPlanEl.textContent = btn.dataset.plan;
        currentExpiryEl.textContent = btn.dataset.expiry;
        if (btn.dataset.planId) planSelect.value = btn.dataset.planId;
        computeExpiry();
        modal.show();
      });
    });

    if (planSelect) planSelect.addEventListener('change', computeExpiry);

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const submitBtn = form.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      fetch(form.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
      }).then((r) => {
        submitBtn.disabled = false;
        if (!r.ok) throw new Error();
        formView.classList.add('d-none');
        successView.classList.remove('d-none');
        showToast('Subscription renewed successfully.', 'success');
        setTimeout(() => window.location.reload(), 1400);
      }).catch(() => { submitBtn.disabled = false; showToast('Could not renew subscription.', 'error'); });
    });
  }

  /* ------------------------------------------------------------------ *
   * Bulk actions (select rows, delete / change status / export selected)
   * ------------------------------------------------------------------ */
  function initBulkActions() {
    const bar = document.getElementById('bulk-bar');
    const form = document.getElementById('bulk-form');
    const selectAll = document.getElementById('bulk-select-all');
    if (!bar || !form) return;

    const checkboxes = () => Array.from(document.querySelectorAll('.bulk-checkbox'));
    const countEl = bar.querySelector('[data-role="bulk-count"]');

    function update() {
      const checked = checkboxes().filter((c) => c.checked);
      bar.classList.toggle('d-none', checked.length === 0);
      if (countEl) countEl.textContent = checked.length;
      if (selectAll) selectAll.checked = checked.length > 0 && checked.length === checkboxes().length;
    }

    checkboxes().forEach((cb) => cb.addEventListener('change', update));
    if (selectAll) {
      selectAll.addEventListener('change', () => {
        checkboxes().forEach((cb) => { cb.checked = selectAll.checked; });
        update();
      });
    }

    function selectedIds() {
      return checkboxes().filter((c) => c.checked).map((c) => c.value);
    }

    function submitWith(extraFields) {
      form.querySelectorAll('[data-bulk-generated]').forEach((el) => el.remove());
      selectedIds().forEach((id) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'ids[]'; input.value = id; input.setAttribute('data-bulk-generated', '1');
        form.appendChild(input);
      });
      Object.entries(extraFields || {}).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = value; input.setAttribute('data-bulk-generated', '1');
        form.appendChild(input);
      });
    }

    document.querySelectorAll('[data-bulk-delete]').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (!selectedIds().length) return;
        if (!confirm('Delete ' + selectedIds().length + ' selected lead(s)? This cannot be undone from here (use Trash to restore).')) return;
        submitWith({ action: 'delete' });
        form.submit();
      });
    });

    document.querySelectorAll('[data-bulk-status]').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (!selectedIds().length) return;
        submitWith({ action: 'status', status: btn.dataset.bulkStatus });
        form.submit();
      });
    });

    document.querySelectorAll('[data-bulk-reassign]').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (!selectedIds().length) return;
        submitWith({ action: 'reassign', assigned_to: btn.dataset.bulkReassign });
        form.submit();
      });
    });

    const exportBtn = document.getElementById('bulk-export');
    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        if (!selectedIds().length) return;
        const url = new URL(exportBtn.dataset.exportUrl, window.location.origin);
        selectedIds().forEach((id) => url.searchParams.append('ids[]', id));
        window.location.href = url.toString();
      });
    }
  }

  /* ------------------------------------------------------------------ *
   * Bootstrap init
   * ------------------------------------------------------------------ */
  document.addEventListener('DOMContentLoaded', function () {
    initSidebar();
    initTooltips();
    initFlashToasts();
    initCounters();
    initConfirmDelete();
    initGlobalSearch();
    initFilterDrawer();
    initKanban();
    initRenewalModal();
    initBulkActions();
  });
})();
