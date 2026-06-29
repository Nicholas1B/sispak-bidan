<nav class="topbar navbar navbar-expand-lg">
    <div class="topbar-inner d-flex align-items-center w-100">

        <!-- Hamburger Toggle (Mobile) -->
        <button class="sidebar-toggle btn btn-icon me-3 d-lg-none" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Page Title (Desktop) -->
        <div class="topbar-breadcrumb d-none d-lg-flex align-items-center">
            <span class="topbar-page-title" id="topbarPageTitle">Dashboard</span>
        </div>

        <!-- Spacer -->
        <div class="ms-auto d-flex align-items-center gap-2">

            <!-- Search (Desktop) -->
            <div class="topbar-search d-none d-md-block">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" id="globalSearch" class="form-control border-start-0 ps-0"
                           placeholder="Cari data..." autocomplete="off">
                </div>
            </div>

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-icon position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown shadow-sm">
                    <div class="notif-header d-flex align-items-center justify-content-between px-3 py-2">
                        <span class="fw-semibold small">Notifikasi</span>
                        <span class="badge bg-primary rounded-pill">1</span>
                    </div>
                    <div class="dropdown-divider m-0"></div>
                    <a href="<?= url('diagnosa/konsultasi.php') ?>" class="dropdown-item notif-item py-2 px-3">
                        <div class="d-flex gap-2 align-items-start">
                            <div class="notif-icon bg-primary-soft rounded-circle flex-shrink-0">
                                <i class="fas fa-stethoscope text-primary"></i>
                            </div>
                            <div>
                                <div class="small fw-semibold">Mulai Konsultasi Baru</div>
                                <div class="text-muted" style="font-size:11px;">Klik untuk diagnosa pasien</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-user d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <div class="user-avatar-sm">
                        <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="d-none d-sm-block text-start">
                        <div class="user-name-sm"><?= e($_SESSION['nama'] ?? 'User') ?></div>
                        <div class="user-role-sm"><?= e(ucfirst($_SESSION['role'] ?? 'bidan')) ?></div>
                    </div>
                    <i class="fas fa-chevron-down ms-1 text-muted" style="font-size:10px;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold"><?= e($_SESSION['nama'] ?? '') ?></div>
                        <div class="text-muted small"><?= e($_SESSION['username'] ?? '') ?></div>
                    </li>
                    <li><a class="dropdown-item" href="<?= url('admin/profil.php') ?>">
                        <i class="fas fa-user me-2 text-muted"></i>Profil Saya
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= url('logout.php') ?>">
                        <i class="fas fa-right-from-bracket me-2"></i>Keluar
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
