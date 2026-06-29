<?php
// ============================================================
// KONFIGURASI DATABASE
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_sispak_bidan');

// Koneksi Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Koneksi Gagal - SisPak Bidan</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: Inter, sans-serif; background: #f0f4f8; display: flex; align-items: center;
                   justify-content: center; min-height: 100vh; padding: 20px; }
            .error-card { background: white; border-radius: 16px; padding: 40px; max-width: 480px;
                          width: 100%; box-shadow: 0 10px 40px rgba(0,0,0,.1); text-align: center; }
            .error-icon { font-size: 56px; margin-bottom: 16px; }
            h2 { color: #1e293b; font-size: 20px; margin-bottom: 8px; }
            p { color: #64748b; font-size: 14px; margin-bottom: 12px; line-height: 1.6; }
            code { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 13px; color: #ef4444; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="error-icon">🔌</div>
            <h2>Koneksi Database Gagal</h2>
            <p>Tidak dapat terhubung ke database MySQL.</p>
            <p>Error: <code>' . htmlspecialchars($conn->connect_error) . '</code></p>
            <p>Pastikan MySQL sudah berjalan dan database <strong>db_sispak_bidan</strong> sudah diimport.</p>
        </div>
    </body>
    </html>');
}

$conn->set_charset('utf8mb4');

// ============================================================
// FUNGSI HELPER
// ============================================================

/**
 * Sanitasi input untuk query SQL
 */
function sanitize(mysqli $conn, string $data): string {
    return $conn->real_escape_string(trim($data));
}

/**
 * Redirect ke URL
 */
function redirect(string $path): void {
    $url = (strpos($path, 'http') === 0) ? $path : url($path);
    header("Location: $url");
    exit();
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Cek apakah user adalah bidan (atau admin)
 */
function isBidan(): bool {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['bidan', 'admin']);
}

/**
 * Wajib login - redirect ke halaman login jika belum
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Wajib admin - redirect ke dashboard jika bukan admin
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

/**
 * Simpan pesan flash ke session
 */
function setFlash(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

// Alias untuk kompatibilitas
function alert(string $msg, string $type = 'success'): void {
    setFlash($msg, $type);
}

/**
 * Tampilkan pesan flash (sekali pakai)
 */
function showFlash(): void {
    if (!isset($_SESSION['flash'])) return;

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $icons = [
        'success' => '<i class="fas fa-check-circle"></i>',
        'danger'  => '<i class="fas fa-times-circle"></i>',
        'warning' => '<i class="fas fa-exclamation-triangle"></i>',
        'info'    => '<i class="fas fa-info-circle"></i>',
    ];

    $icon = $icons[$flash['type']] ?? $icons['info'];
    $type = htmlspecialchars($flash['type']);
    $msg  = htmlspecialchars($flash['msg']);

    echo <<<HTML
    <div class="alert alert-{$type} alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        {$icon}
        <span>{$msg}</span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    HTML;
}

// Alias
function showAlert(): void {
    showFlash();
}

/**
 * Format persentase CF ke badge Bootstrap
 */
function cfBadge(float $pct): string {
    if ($pct >= 70) {
        return '<span class="badge bg-success-soft text-success"><i class="fas fa-circle-check me-1"></i>' . number_format($pct, 1) . '%</span>';
    } elseif ($pct >= 40) {
        return '<span class="badge bg-warning-soft text-warning"><i class="fas fa-circle-minus me-1"></i>' . number_format($pct, 1) . '%</span>';
    } else {
        return '<span class="badge bg-danger-soft text-danger"><i class="fas fa-circle-xmark me-1"></i>' . number_format($pct, 1) . '%</span>';
    }
}

/**
 * Escape output HTML
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format tanggal Indonesia
 */
function tglIndo(string $datetime): string {
    $bulan = [
        1  => 'Januari', 2  => 'Februari', 3  => 'Maret',
        4  => 'April',   5  => 'Mei',       6  => 'Juni',
        7  => 'Juli',    8  => 'Agustus',   9  => 'September',
        10 => 'Oktober', 11 => 'November',  12 => 'Desember',
    ];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
