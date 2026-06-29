/**
 * SisPak Bidan — validation.js
 * Form Validation Helpers
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initFormValidation();
        initPasswordToggle();
        initPasswordStrength();
    });

    // ── Bootstrap-style Validation ──────────────────────────────
    function initFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // ── Password Show/Hide Toggle ────────────────────────────────
    function initPasswordToggle() {
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = this.dataset.togglePassword;
                var input    = document.getElementById(targetId);
                if (!input) return;

                var icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
                } else {
                    input.type = 'password';
                    if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
                }
            });
        });
    }

    // ── Password Strength Meter ──────────────────────────────────
    function initPasswordStrength() {
        var pwInput = document.getElementById('passwordInput');
        var meter   = document.getElementById('passwordStrength');
        if (!pwInput || !meter) return;

        pwInput.addEventListener('input', function () {
            var strength = calcStrength(this.value);
            var labels   = ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            var colors   = ['', 'danger', 'warning', 'info', 'success'];

            meter.innerHTML = this.value.length
                ? '<div class="progress mt-2"><div class="progress-bar bg-' + colors[strength] +
                  '" style="width:' + (strength * 25) + '%"></div></div>' +
                  '<small class="text-' + colors[strength] + '">' + labels[strength] + '</small>'
                : '';
        });
    }

    function calcStrength(pw) {
        var score = 0;
        if (pw.length >= 8)  score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;
        return score;
    }

    // ── Confirm Password Match ───────────────────────────────────
    var pw1 = document.getElementById('passwordInput');
    var pw2 = document.getElementById('passwordConfirm');

    if (pw1 && pw2) {
        pw2.addEventListener('input', function () {
            if (this.value && this.value !== pw1.value) {
                this.setCustomValidity('Password tidak cocok');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                if (this.value) this.classList.add('is-valid');
            }
        });
    }

})();
