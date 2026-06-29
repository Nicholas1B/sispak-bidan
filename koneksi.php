<?php
// ============================================================
// KONFIGURASI DATABASE
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_sispak_bidan');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:Arial;padding:20px;background:#fee;border:1px solid #f00;margin:20px;">
        <h3>❌ Koneksi Database Gagal</h3>
        <p>Error: ' . $conn->connect_error . '</p>
        <p>Pastikan XAMPP MySQL sudah berjalan dan database <b>db_sispak_bidan</b> sudah diimport.</p>
    </div>');
}

$conn->set_charset("utf8mb4");

// Fungsi helper
function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isBidan() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'bidan' || $_SESSION['role'] === 'admin');
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/sispak_bidan/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('/sispak_bidan/index.php');
    }
}

function alert($msg, $type = 'success') {
    $_SESSION['alert'] = ['msg' => $msg, 'type' => $type];
}

function showAlert() {
    if (isset($_SESSION['alert'])) {
        $a = $_SESSION['alert'];
        unset($_SESSION['alert']);
        $icon = $a['type'] === 'success' ? '✅' : ($a['type'] === 'danger' ? '❌' : 'ℹ️');
        $color = ['success' => '#d1fae5', 'danger' => '#fee2e2', 'warning' => '#fef3c7', 'info' => '#dbeafe'];
        $border = ['success' => '#10b981', 'danger' => '#ef4444', 'warning' => '#f59e0b', 'info' => '#3b82f6'];
        $c = $color[$a['type']] ?? $color['info'];
        $b = $border[$a['type']] ?? $border['info'];
        echo "<div class='alert alert-{$a['type']}' style='background:$c;border-left:4px solid $b;padding:12px 16px;margin-bottom:16px;border-radius:6px;'>$icon {$a['msg']}</div>";
    }
}
?>
