/**
 * SisPak Bidan — dashboard.js
 * Dashboard-specific JavaScript
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        animateStatCounters();
        initPrintButton();
    });

    // ── Animate Stat Numbers ─────────────────────────────────────
    function animateStatCounters() {
        document.querySelectorAll('.stat-value[data-count]').forEach(function (el) {
            var target   = parseInt(el.dataset.count, 10) || 0;
            var duration = 800;
            var start    = performance.now();

            function update(now) {
                var elapsed  = now - start;
                var progress = Math.min(elapsed / duration, 1);
                var eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
                el.textContent = Math.round(eased * target);
                if (progress < 1) requestAnimationFrame(update);
            }

            requestAnimationFrame(update);
        });
    }

    // ── Print Button ─────────────────────────────────────────────
    function initPrintButton() {
        var printBtn = document.getElementById('printBtn');
        if (printBtn) {
            printBtn.addEventListener('click', function () {
                window.print();
            });
        }
    }

})();
