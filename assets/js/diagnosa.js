/**
 * SisPak Bidan — diagnosa.js
 * Certainty Factor UI: Gejala Selection, CF Options, Autocomplete
 */

(function () {
    'use strict';

    var selectedCount = 0;

    document.addEventListener('DOMContentLoaded', function () {
        initGejalaCheckboxes();
        initCFButtons();
        initAutocomplete();
        initSubmitValidation();
        initCFAnimations();
    });

    // ── Gejala Checkbox Toggle ──────────────────────────────────
    function initGejalaCheckboxes() {
        document.querySelectorAll('.gejala-item input[type="checkbox"]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var item = this.closest('.gejala-item');
                var wrap = item.querySelector('.cf-slider-wrap');

                if (this.checked) {
                    item.classList.add('checked');
                    if (wrap) wrap.style.display = 'flex';
                    selectedCount++;
                    // Auto-select "Cukup Yakin" (0.6) as default
                    var defaultBtn = wrap && wrap.querySelector('.cf-btn[data-val="0.6"]');
                    if (defaultBtn && !wrap.querySelector('.cf-btn.active')) {
                        defaultBtn.classList.add('active');
                    }
                } else {
                    item.classList.remove('checked');
                    if (wrap) wrap.style.display = 'none';
                    selectedCount--;
                    // Reset CF buttons
                    item.querySelectorAll('.cf-btn').forEach(function (b) {
                        b.classList.remove('active');
                    });
                    var hidden = item.querySelector('input[name^="cf_user"]');
                    if (hidden) hidden.value = '0.6';
                }

                updateSelectedCounter();
            });
        });
    }

    // ── CF Button Selection ──────────────────────────────────────
    function initCFButtons() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cf-btn');
            if (!btn) return;

            var item = btn.closest('.gejala-item');
            if (!item) return;

            item.querySelectorAll('.cf-btn').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');

            var id     = btn.dataset.id;
            var val    = btn.dataset.val;
            var hidden = document.getElementById('cf-val-' + id);
            if (hidden) hidden.value = val;
        });
    }

    // ── Update Counter Badge ─────────────────────────────────────
    function updateSelectedCounter() {
        var counter = document.getElementById('countSelected');
        if (counter) {
            counter.textContent = selectedCount;
            counter.className   = selectedCount > 0
                ? 'badge bg-primary-soft text-primary'
                : 'badge bg-secondary-soft text-muted';
        }
    }

    // ── Patient Autocomplete ─────────────────────────────────────
    function initAutocomplete() {
        var searchInput = document.getElementById('searchPasien');
        var dropdown    = document.getElementById('autocompleteList');
        if (!searchInput || !dropdown) return;

        var timer;

        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            var q = this.value.trim();

            if (q.length < 2) {
                dropdown.classList.remove('show');
                return;
            }

            timer = setTimeout(function () {
                fetch(BASE_URL + '/api/cari_pasien.php?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.length) {
                            dropdown.classList.remove('show');
                            return;
                        }
                        renderAutocomplete(data);
                    })
                    .catch(function () {
                        dropdown.classList.remove('show');
                    });
            }, 280);
        });

        function renderAutocomplete(items) {
            dropdown.innerHTML = items.map(function (p) {
                var sub = [];
                if (p.usia) sub.push(p.usia + ' th');
                if (p.usia_kehamilan) sub.push(p.usia_kehamilan + ' mgg');
                if (p.no_hp) sub.push(p.no_hp);

                return '<div class="autocomplete-item" data-pasien=\'' + JSON.stringify(p).replace(/'/g, '&#39;') + '\'>' +
                    '<strong>' + escHtml(p.nama_pasien) + '</strong>' +
                    (sub.length ? '<span>' + sub.join(' · ') + '</span>' : '') +
                    '</div>';
            }).join('');

            dropdown.classList.add('show');

            dropdown.querySelectorAll('.autocomplete-item').forEach(function (item) {
                item.addEventListener('click', function () {
                    var p = JSON.parse(this.dataset.pasien);
                    fillPatientForm(p);
                    dropdown.classList.remove('show');
                    searchInput.value = '';
                });
            });
        }

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('show');
                this.blur();
            }
        });
    }

    function fillPatientForm(p) {
        setVal('inp_nama',    p.nama_pasien);
        setVal('inp_usia',    p.usia || '');
        setVal('inp_usia_ham', p.usia_kehamilan || '');
        setVal('inp_hp',      p.no_hp || '');
        setVal('inp_alamat',  p.alamat || '');
    }

    function setVal(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }

    // ── Form Submission Validation ───────────────────────────────
    function initSubmitValidation() {
        var submitBtn = document.getElementById('btnSubmitDiagnosa');
        var form      = document.getElementById('formDiagnosa');
        if (!submitBtn || !form) return;

        submitBtn.addEventListener('click', function (e) {
            e.preventDefault();

            var checked = document.querySelectorAll('input[name="gejala[]"]:checked');
            var nama    = document.querySelector('[name="nama_pasien"]');

            if (!nama || !nama.value.trim()) {
                showFormError('Nama pasien wajib diisi!');
                nama && nama.focus();
                return;
            }

            if (checked.length === 0) {
                showFormError('Pilih minimal 1 gejala yang dialami pasien!');
                var gejalaSection = document.querySelector('.gejala-list');
                if (gejalaSection) gejalaSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Show loading state
            submitBtn.disabled   = true;
            submitBtn.innerHTML  = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

            form.submit();
        });
    }

    function showFormError(msg) {
        var existing = document.getElementById('formError');
        if (existing) existing.remove();

        var el = document.createElement('div');
        el.id        = 'formError';
        el.className = 'alert alert-danger alert-dismissible mt-3';
        el.innerHTML = '<i class="fas fa-times-circle me-2"></i>' + escHtml(msg) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

        var form = document.getElementById('formDiagnosa');
        if (form) form.insertAdjacentElement('beforebegin', el);

        el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(function () { el.remove(); }, 5000);
    }

    // ── CF Progress Bars Animation ───────────────────────────────
    function initCFAnimations() {
        // Animate CF progress bars on hasil page
        document.querySelectorAll('.cf-animate').forEach(function (bar) {
            var target = parseFloat(bar.dataset.width || 0);
            bar.style.width = '0%';
            setTimeout(function () {
                bar.style.transition = 'width 1s ease';
                bar.style.width = target + '%';
            }, 200);
        });

        // Animate gauge fill on hasil header
        var gauge = document.querySelector('.cf-gauge-fill[data-width]');
        if (gauge) {
            var w = parseFloat(gauge.dataset.width || 0);
            gauge.style.width = '0%';
            setTimeout(function () {
                gauge.style.transition = 'width 1.2s ease';
                gauge.style.width = Math.min(w, 100) + '%';
            }, 300);
        }
    }

    // ── Utility: Escape HTML ─────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

})();
