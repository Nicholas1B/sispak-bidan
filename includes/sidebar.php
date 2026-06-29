<?php
// Sidebar component
// Gunakan: $activePage = 'dashboard'; sebelum include
$activePage = $activePage ?? '';
$isAdmin    = isAdmin();
$userName   = $_SESSION['nama']  ?? 'User';
$userRole   = $_SESSION['role']  ?? 'bidan';
$userInitial = strtoupper(substr($userName, 0, 1));

$menuMain = [
    ['page' => 'dashboard',   'href' => url('index.php'),                'icon' => 'fa-gauge-high',      'label' => 'Dashboard'],
    ['page' => 'konsultasi',  'href' => url('diagnosa/konsultasi.php'),  'icon' => 'fa-stethoscope',     'label' => 'Konsultasi Baru'],
    ['page' => 'riwayat',     'href' => url('diagnosa/riwayat.php'),     'icon' => 'fa-clipboard-list',  'label' => 'Riwayat Diagnosa'],
];

$menuAdmin = [
    ['page' => 'gejala',   'href' => url('admin/gejala.php'),   'icon' => 'fa-virus',           'label' => 'Data Gejala'],
    ['page' => 'kondisi',  'href' => url('admin/kondisi.php'),  'icon' => 'fa-tag',             'label' => 'Data Kondisi'],
    ['page' => 'solusi',   'href' => url('admin/solusi.php'),   'icon' => 'fa-pills',           'label' => 'Data Solusi'],
    ['page' => 'rule',     'href' => url('admin/rule.php'),     'icon' => 'fa-brain',           'label' => 'Rule CF'],
];

$menuReport = [
    ['page' => 'laporan',  'href' => url('admin/laporan.php'),  'icon' => 'fa-chart-bar',       'label' => 'Laporan Diagnosa'],
    ['page' => 'users',    'href' => url('admin/users.php'),    'icon' => 'fa-users-gear',      'label' => 'Manajemen User'],
];
?>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-heart-pulse"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name"><?= APP_NAME ?></span>
            <span class="brand-sub">Certainty Factor</span>
        </div>
        <!-- Close button mobile -->
        <button class="btn-close-sidebar d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="sidebar-user">
        <div class="sidebar-avatar"><?= $userInitial ?></div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?= e($userName) ?></div>
            <div class="sidebar-user-role">
                <?= $isAdmin
                    ? '<i class="fas fa-shield-halved me-1"></i>Administrator'
                    : '<i class="fas fa-user-nurse me-1"></i>Bidan' ?>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- Menu Utama -->
        <div class="nav-section-label">Menu Utama</div>
        <?php foreach ($menuMain as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="nav-link-item <?= $activePage === $item['page'] ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas <?= $item['icon'] ?>"></i></span>
            <span class="nav-label"><?= $item['label'] ?></span>
            <?php if ($activePage === $item['page']): ?>
            <span class="nav-indicator"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <?php if ($isAdmin): ?>
        <!-- Manajemen Data (Admin Only) -->
        <div class="nav-section-label mt-2">Manajemen Data</div>
        <?php foreach ($menuAdmin as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="nav-link-item <?= $activePage === $item['page'] ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas <?= $item['icon'] ?>"></i></span>
            <span class="nav-label"><?= $item['label'] ?></span>
            <?php if ($activePage === $item['page']): ?>
            <span class="nav-indicator"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <!-- Laporan & Pengaturan -->
        <div class="nav-section-label mt-2">Laporan & Pengaturan</div>
        <?php foreach ($menuReport as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="nav-link-item <?= $activePage === $item['page'] ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas <?= $item['icon'] ?>"></i></span>
            <span class="nav-label"><?= $item['label'] ?></span>
            <?php if ($activePage === $item['page']): ?>
            <span class="nav-indicator"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>

    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?= url('admin/profil.php') ?>"
           class="nav-link-item <?= $activePage === 'profil' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="fas fa-circle-user"></i></span>
            <span class="nav-label">Profil Saya</span>
        </a>
        <a href="<?= url('logout.php') ?>" class="nav-link-item nav-link-logout">
            <span class="nav-icon"><i class="fas fa-right-from-bracket"></i></span>
            <span class="nav-label">Keluar</span>
        </a>

        <div class="sidebar-version">
            <i class="fas fa-code-branch me-1"></i>v<?= APP_VERSION ?>
        </div>
    </div>

</aside>
