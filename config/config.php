<?php
// ============================================================
// KONFIGURASI APLIKASI - SisPak Bidan
// ============================================================
// Ubah BASE_URL sesuai hosting Anda:
//   Localhost XAMPP : define('BASE_URL', '/sispak_bidan');
//   InfinityFree    : define('BASE_URL', '');  // jika di root
//   Subdirectory    : define('BASE_URL', '/nama-folder');
// ============================================================

define('BASE_URL', '');          // Kosong = root domain
define('APP_NAME', 'SisPak Bidan');
define('APP_SUBTITLE', 'Sistem Pakar Diagnosa Kebidanan');
define('APP_VERSION', '2.0.0');
define('APP_METHOD', 'Certainty Factor');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (matikan di production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Helper: Buat URL absolut dari path relatif
 * Contoh: url('login.php') => '/login.php'
 *         url('assets/css/style.css') => '/assets/css/style.css'
 */
function url(string $path = ''): string {
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $path ? "$base/$path" : ($base ?: '/');
}

/**
 * Helper: Asset URL (CSS, JS, img, dll)
 */
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}
