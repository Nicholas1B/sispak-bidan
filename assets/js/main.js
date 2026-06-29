/**
 * SisPak Bidan — main.js
 * Core JavaScript: Sidebar, Topbar, Alerts, Table Search
 */

(function () {
    'use strict';

    // ── DOM Ready ──────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initAlerts();
        initTableSearch();
        initConfirmDelete();
        syncPageTitle();
        initTooltips();
    });

    // ── Sidebar Toggle ──────────────────────────────────────────
    function initSidebar() {
        const sidebar     = document.getElementById('sidebar');
        const toggleBtn   = document.getElementById('sidebarToggle');
        const closeBtn    = document.getElementById('sidebarClose');
        const overlay     = document.getElementById('sidebarOverlay');

        if (!sidebar) return;

        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            overlay && overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('sidebar-open');
            overlay && overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        toggleBtn && toggleBtn.addEventListener('click', function () {
            sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        });

        closeBtn  && closeBtn.addEventListener('click', closeSidebar);
        overlay   && overlay.addEventListener('click', closeSidebar);

        // Close on ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });

        // On resize re-open sidebar if desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('sidebar-open');
                overlay && overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    // ── Auto-Dismiss Alerts ─────────────────────────────────────
    function initAlerts() {
        var alerts = document.querySelectorAll('.alert:not(.alert-static)');
        alerts.forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity .4s ease, transform .4s ease';
                el.style.opacity    = '0';
                el.style.transform  = 'translateY(-8px)';
                setTimeout(function () {
                    el.remove();
                }, 400);
            }, 4500);
        });
    }

    // ── Table Live Search ───────────────────────────────────────
    function initTableSearch() {
        var input = document.getElementById('tableSearch');
        if (!input) return;

        input.addEventListener('input', function () {
            var q    = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('tbody tr[data-searchable]');

            if (!rows.length) {
                // Fallback: search all tbody rows
                var allRows = document.querySelectorAll('tbody tr');
                allRows.forEach(function (row) {
                    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
                return;
            }

            rows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Confirm Before Delete ───────────────────────────────────
    function initConfirmDelete() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-confirm]');
            if (!btn) return;
            var msg = btn.dataset.confirm || 'Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
            if (!confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    // ── Sync Page Title to Topbar ───────────────────────────────
    function syncPageTitle() {
        var h1       = document.querySelector('h1[data-page-title]');
        var topTitle = document.getElementById('topbarPageTitle');
        if (h1 && topTitle) {
            topTitle.textContent = h1.dataset.pageTitle || h1.textContent;
        }
    }

    // ── Bootstrap Tooltips ──────────────────────────────────────
    function initTooltips() {
        if (typeof bootstrap === 'undefined') return;
        var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipEls.forEach(function (el) {
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    }

    // ── Toast Helper (global) ───────────────────────────────────
    window.showToast = function (msg, type) {
        type = type || 'success';
        var icons = {
            success: 'fa-check-circle',
            danger:  'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info:    'fa-info-circle'
        };
        var colors = {
            success: '#10b981',
            danger:  '#ef4444',
            warning: '#f59e0b',
            info:    '#3b82f6'
        };

        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        var toast = document.createElement('div');
        toast.className = 'toast show align-items-center text-white border-0 shadow';
        toast.style.cssText = 'background:' + (colors[type] || colors.info) + ';border-radius:10px;min-width:260px;';
        toast.innerHTML =
            '<div class="d-flex">' +
            '<div class="toast-body d-flex align-items-center gap-2">' +
            '<i class="fas ' + (icons[type] || icons.info) + '"></i>' + msg +
            '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
            '</div>';

        container.appendChild(toast);

        if (typeof bootstrap !== 'undefined') {
            var bsToast = new bootstrap.Toast(toast, { delay: 4000 });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
        } else {
            setTimeout(function () { toast.remove(); }, 4000);
        }
    };

    // ── Global Search (quick filter) ───────────────────────────
    var globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('input', function () {
            var q    = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('tbody tr');
            rows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

})();
