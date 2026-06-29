/**
 * SisPak Bidan — theme.js
 * Theme & Preference Management (extensible for dark mode)
 */

(function () {
    'use strict';

    // ── Init ─────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        setActiveNavItem();
        initSmoothScrollLinks();
    });

    // ── Mark Active Nav Item ─────────────────────────────────────
    function setActiveNavItem() {
        var currentPath = window.location.pathname.split('?')[0];

        document.querySelectorAll('.nav-link-item').forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href) return;

            // Normalize
            var linkPath = href.split('?')[0].replace(/\/$/, '');
            var currPath = currentPath.replace(/\/$/, '');

            if (currPath === linkPath || currPath.endsWith(linkPath)) {
                link.classList.add('active');
            }
        });
    }

    // ── Smooth Scroll for anchor links ───────────────────────────
    function initSmoothScrollLinks() {
        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

})();
