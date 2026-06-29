<?php
// layout.php - Template helper functions

function renderHead($title = '') {
    $fullTitle = $title ? "$title - SisPak Bidan" : 'SisPak Bidan';
    echo <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$fullTitle</title>
    <link rel="stylesheet" href="/sispak_bidan/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏥</text></svg>">
</head>
HTML;
}

function renderSidebar($active = '') {
    $user    = $_SESSION['nama'] ?? 'User';
    $role    = $_SESSION['role'] ?? 'bidan';
    $initial = strtoupper(substr($user, 0, 1));
    $isAdmin = ($role === 'admin');

    // Pre-compute active classes — ternary TIDAK boleh di dalam heredoc
    $aDashboard  = ($active === 'dashboard')  ? 'active' : '';
    $aKonsultasi = ($active === 'konsultasi') ? 'active' : '';
    $aRiwayat    = ($active === 'riwayat')    ? 'active' : '';
    $aGejala     = ($active === 'gejala')     ? 'active' : '';
    $aKondisi    = ($active === 'kondisi')    ? 'active' : '';
    $aSolusi     = ($active === 'solusi')     ? 'active' : '';
    $aRule       = ($active === 'rule')       ? 'active' : '';
    $aLaporan    = ($active === 'laporan')    ? 'active' : '';
    $aUsers      = ($active === 'users')      ? 'active' : '';
    $aProfil     = ($active === 'profil')     ? 'active' : '';

    echo <<<HTML
<div class="layout">
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🏥</div>
        <h2>SisPak Bidan</h2>
        <p>Sistem Pakar Diagnosa</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu Utama</div>
        <a href="/sispak_bidan/index.php" class="nav-item $aDashboard">
            <span class="icon">📊</span> Dashboard
        </a>
        <a href="/sispak_bidan/diagnosa/konsultasi.php" class="nav-item $aKonsultasi">
            <span class="icon">🔍</span> Konsultasi Baru
        </a>
        <a href="/sispak_bidan/diagnosa/riwayat.php" class="nav-item $aRiwayat">
            <span class="icon">📋</span> Riwayat Diagnosa
        </a>
HTML;

    if ($isAdmin) {
        echo <<<HTML
        <div class="nav-section">Manajemen Data</div>
        <a href="/sispak_bidan/admin/gejala.php" class="nav-item $aGejala">
            <span class="icon">🩺</span> Data Gejala
        </a>
        <a href="/sispak_bidan/admin/kondisi.php" class="nav-item $aKondisi">
            <span class="icon">🏷️</span> Data Kondisi
        </a>
        <a href="/sispak_bidan/admin/solusi.php" class="nav-item $aSolusi">
            <span class="icon">💊</span> Data Solusi
        </a>
        <a href="/sispak_bidan/admin/rule.php" class="nav-item $aRule">
            <span class="icon">⚙️</span> Rule Certainty Factor
        </a>
        <div class="nav-section">Laporan &amp; Pengaturan</div>
        <a href="/sispak_bidan/admin/laporan.php" class="nav-item $aLaporan">
            <span class="icon">📑</span> Laporan Diagnosa
        </a>
        <a href="/sispak_bidan/admin/users.php" class="nav-item $aUsers">
            <span class="icon">👥</span> Manajemen User
        </a>
HTML;
    }

    echo <<<HTML
    </nav>
    <div class="sidebar-footer">
        <a href="/sispak_bidan/admin/profil.php" class="nav-item $aProfil">
            <span class="icon">👤</span> Profil Saya
        </a>
        <a href="/sispak_bidan/logout.php" class="nav-item" style="color:#f87171;">
            <span class="icon">🚪</span> Keluar
        </a>
    </div>
</aside>
<div class="main">
<div class="topbar">
    <div class="topbar-title" id="page-title">Dashboard</div>
    <div class="topbar-user">
        <div class="user-info">
            <div class="user-name">$user</div>
            <div class="user-role">$role</div>
        </div>
        <div class="user-avatar">$initial</div>
    </div>
</div>
<div class="content">
HTML;
}

function renderFooter() {
    echo <<<HTML
</div></div></div>
<script src="/sispak_bidan/assets/js/main.js"></script>
</body></html>
HTML;
}
?>
