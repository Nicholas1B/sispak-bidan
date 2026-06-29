<?php
require_once __DIR__ . '/config/config.php';

http_response_code(404);
$pageTitle = '404 — Halaman Tidak Ditemukan';
$bodyClass = 'error-page';
$extraCss  = '<link rel="stylesheet" href="' . asset('css/style.css') . '">';
require_once __DIR__ . '/includes/header.php';
?>

<div class="error-container">
    <div class="error-code">404</div>

    <div style="font-size:56px;margin-bottom:16px;">
        <i class="fas fa-map-location-dot" style="color:var(--border);"></i>
    </div>

    <h2 style="font-size:22px;font-weight:700;color:var(--dark);margin-bottom:8px;">
        Halaman Tidak Ditemukan
    </h2>
    <p style="color:var(--muted);margin-bottom:28px;font-size:14px;">
        Halaman yang Anda cari tidak ada atau telah dipindahkan.
    </p>

    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= url('/') ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-house me-2"></i>Kembali ke Dashboard
        </a>
        <button onclick="history.back()" class="btn btn-light-muted btn-lg">
            <i class="fas fa-arrow-left me-2"></i>Halaman Sebelumnya
        </button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
