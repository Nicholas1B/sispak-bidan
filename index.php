<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Jika sudah login, tampilkan dashboard
if (isLoggedIn()) {
    // ── Load Dashboard Data ──────────────────────────────────────
    $stats = [];
    foreach (['gejala', 'kondisi', 'solusi', 'rule_cf', 'pasien'] as $t) {
        $r          = $conn->query("SELECT COUNT(*) AS c FROM $t");
        $stats[$t]  = (int)$r->fetch_assoc()['c'];
    }

    $riwayat = (int)$conn->query("SELECT COUNT(*) AS c FROM riwayat_diagnosa")->fetch_assoc()['c'];
    $bulanIni = (int)$conn->query(
        "SELECT COUNT(*) AS c FROM riwayat_diagnosa
         WHERE MONTH(tanggal)=MONTH(NOW()) AND YEAR(tanggal)=YEAR(NOW())"
    )->fetch_assoc()['c'];
    $totalUsers = (int)$conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];

    $recent = $conn->query("
        SELECT rd.*, p.nama_pasien, k.nama_kondisi, u.nama AS bidan_nama
        FROM riwayat_diagnosa rd
        JOIN pasien p ON rd.pasien_id = p.id
        LEFT JOIN kondisi k ON rd.kondisi_id = k.id
        JOIN users u ON rd.user_id = u.id
        ORDER BY rd.tanggal DESC
        LIMIT 8
    ");

    $pageTitle  = 'Dashboard';
    $activePage = 'dashboard';
    $extraJs    = '<script src="' . asset('js/dashboard.js') . '"></script>';

    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="app-wrapper">
        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

        <main class="main-content">
            <?php require_once __DIR__ . '/includes/navbar.php'; ?>

            <div class="page-content">
                <h1 data-page-title="Dashboard" style="display:none">Dashboard</h1>
                <?php showFlash(); ?>

                <!-- Welcome Card -->
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="welcome-title">
                                Selamat datang, <?= e($_SESSION['nama']) ?> 👋
                            </div>
                            <p>
                                <?= date('l, d F Y') ?> &mdash; <?= APP_SUBTITLE ?>
                            </p>
                            <div class="welcome-action">
                                <a href="<?= url('diagnosa/konsultasi.php') ?>" class="btn btn-lg">
                                    <i class="fas fa-stethoscope me-2"></i>Mulai Konsultasi
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 d-none d-md-flex justify-content-end">
                            <div style="font-size:90px;opacity:.15;line-height:1;">
                                <i class="fas fa-heart-pulse"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-blue"><i class="fas fa-virus"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $stats['gejala'] ?>"><?= $stats['gejala'] ?></div>
                            <div class="stat-label">Total Gejala</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-pink"><i class="fas fa-tag"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $stats['kondisi'] ?>"><?= $stats['kondisi'] ?></div>
                            <div class="stat-label">Kondisi/Penyakit</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-green"><i class="fas fa-pills"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $stats['solusi'] ?>"><?= $stats['solusi'] ?></div>
                            <div class="stat-label">Data Solusi</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-orange"><i class="fas fa-brain"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $stats['rule_cf'] ?>"><?= $stats['rule_cf'] ?></div>
                            <div class="stat-label">Rule CF</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-purple"><i class="fas fa-person-pregnant"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $stats['pasien'] ?>"><?= $stats['pasien'] ?></div>
                            <div class="stat-label">Data Pasien</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-blue"><i class="fas fa-clipboard-list"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $riwayat ?>"><?= $riwayat ?></div>
                            <div class="stat-label">Total Diagnosa</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-green"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $bulanIni ?>"><?= $bulanIni ?></div>
                            <div class="stat-label">Diagnosa Bulan Ini</div>
                        </div>
                    </div>
                    <?php if (isAdmin()): ?>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-cyan"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="stat-value" data-count="<?= $totalUsers ?>"><?= $totalUsers ?></div>
                            <div class="stat-label">Total Pengguna</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Content Grid -->
                <div class="row g-4">
                    <!-- Recent Diagnosa Table -->
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <span class="title-icon"><i class="fas fa-clock-rotate-left"></i></span>
                                    Riwayat Diagnosa Terbaru
                                </h6>
                                <a href="<?= url('diagnosa/riwayat.php') ?>" class="btn btn-light-muted btn-sm">
                                    Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Pasien</th>
                                            <th>Kondisi</th>
                                            <th>CF</th>
                                            <th class="hide-mobile">Bidan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent->num_rows === 0): ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state" style="padding:32px;">
                                                    <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                                                    <h5>Belum Ada Diagnosa</h5>
                                                    <p>Mulai konsultasi untuk melihat riwayat di sini.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php else: while ($r = $recent->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/y H:i', strtotime($r['tanggal'])) ?>
                                                </small>
                                            </td>
                                            <td><strong><?= e($r['nama_pasien']) ?></strong></td>
                                            <td>
                                                <?php if ($r['nama_kondisi']): ?>
                                                <span class="badge bg-primary-soft text-primary">
                                                    <?= e($r['nama_kondisi']) ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary text-white">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= cfBadge((float)$r['persentase']) ?></td>
                                            <td class="hide-mobile">
                                                <small><?= e($r['bidan_nama']) ?></small>
                                            </td>
                                        </tr>
                                        <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Cards -->
                    <div class="col-lg-4 d-flex flex-column gap-4">

                        <!-- Quick Action -->
                        <div class="card card-gradient">
                            <div class="card-body p-4">
                                <div class="mb-3" style="font-size:32px;">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Mulai Diagnosa</h5>
                                <p class="mb-4" style="font-size:13px;opacity:.85;">
                                    Gunakan metode Certainty Factor untuk mendiagnosa kondisi pasien secara akurat.
                                </p>
                                <a href="<?= url('diagnosa/konsultasi.php') ?>"
                                   class="btn btn-lg w-100 justify-content-center"
                                   style="background:white;color:var(--primary);font-weight:700;">
                                    Mulai Sekarang
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <span class="title-icon"><i class="fas fa-circle-info"></i></span>
                                    Info Sistem
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="sys-info-list">
                                    <li class="sys-info-item">
                                        <span class="sys-info-key">Metode</span>
                                        <span class="sys-info-val badge bg-primary-soft text-primary">Certainty Factor</span>
                                    </li>
                                    <li class="sys-info-item">
                                        <span class="sys-info-key">Domain</span>
                                        <span class="sys-info-val">Kebidanan</span>
                                    </li>
                                    <li class="sys-info-item">
                                        <span class="sys-info-key">Basis Pengetahuan</span>
                                        <span class="sys-info-val"><?= $stats['rule_cf'] ?> Rule</span>
                                    </li>
                                    <li class="sys-info-item">
                                        <span class="sys-info-key">Kondisi</span>
                                        <span class="sys-info-val"><?= $stats['kondisi'] ?> Kondisi</span>
                                    </li>
                                    <li class="sys-info-item">
                                        <span class="sys-info-key">Versi</span>
                                        <span class="sys-info-val">v<?= APP_VERSION ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div><!-- /.row -->

            </div><!-- /.page-content -->
        </main>
    </div>

    <?php
    require_once __DIR__ . '/includes/footer.php';

} else {
    // ── Landing Page (belum login) ───────────────────────────────
    $pageTitle = APP_NAME . ' — ' . APP_SUBTITLE;
    $bodyClass = 'landing';
    $extraCss  = '<link rel="stylesheet" href="' . asset('css/style.css') . '">';
    require_once __DIR__ . '/includes/header.php';
    ?>

    <!-- Navbar -->
    <nav class="landing-navbar navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= url('/') ?>">
                <div class="landing-nav-brand-icon">
                    <i class="fas fa-heart-pulse"></i>
                </div>
                <span class="landing-nav-name"><?= APP_NAME ?></span>
            </a>
            <div class="ms-auto d-flex gap-2">
                <a href="<?= url('login.php') ?>" class="btn btn-light-muted">
                    <i class="fas fa-right-to-bracket me-1"></i>Masuk
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-badge">
                        <i class="fas fa-circle-check"></i>
                        Sistem Pakar Terintegrasi
                    </div>
                    <h1 class="hero-title">
                        Diagnosa Kebidanan
                        <span class="text-gradient">Lebih Akurat</span>
                        dengan AI
                    </h1>
                    <p class="hero-desc">
                        Platform sistem pakar diagnosa kebidanan berbasis metode
                        <strong>Certainty Factor</strong>. Bantu bidan dalam
                        mengidentifikasi kondisi ibu hamil secara cepat, akurat,
                        dan terdokumentasi.
                    </p>
                    <div class="hero-actions">
                        <a href="<?= url('login.php') ?>" class="btn btn-primary btn-xl">
                            <i class="fas fa-stethoscope me-2"></i>Mulai Sekarang
                        </a>
                        <a href="#features" class="btn btn-light-muted btn-xl">
                            Pelajari Lebih Lanjut
                            <i class="fas fa-arrow-down ms-2"></i>
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">9+</span>
                            <span class="hero-stat-label">Kondisi Diagnosa</span>
                        </div>
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">25+</span>
                            <span class="hero-stat-label">Basis Gejala</span>
                        </div>
                        <div class="hero-stat-item">
                            <span class="hero-stat-value">CF</span>
                            <span class="hero-stat-label">Metode Ilmiah</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="hero-visual">
                        <!-- Float card top -->
                        <div class="hero-float-card float-top">
                            <div class="stat-icon stat-icon-green" style="width:36px;height:36px;font-size:14px;">
                                <i class="fas fa-circle-check"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13px;">Diagnosa Selesai</div>
                                <div style="color:var(--muted);font-size:11px;">CF = 87.3%</div>
                            </div>
                        </div>

                        <!-- Main demo card -->
                        <div class="hero-card-demo">
                            <div class="demo-header">
                                <div class="demo-icon-wrap">
                                    <i class="fas fa-heart-pulse"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:14px;">Hasil Diagnosa</div>
                                    <div style="color:var(--muted);font-size:12px;">Pasien: Ny. Sari Dewi</div>
                                </div>
                                <span class="badge bg-success-soft text-success ms-auto">
                                    <i class="fas fa-check me-1"></i>Selesai
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size:13px;font-weight:600;">Preeklampsia</span>
                                    <span style="font-size:13px;font-weight:700;color:var(--success);">87.3%</span>
                                </div>
                                <div class="cf-bar">
                                    <div class="cf-fill cf-fill-high" style="width:87%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size:13px;font-weight:600;">Hiperemesis</span>
                                    <span style="font-size:13px;font-weight:700;color:var(--warning);">42.1%</span>
                                </div>
                                <div class="cf-bar">
                                    <div class="cf-fill cf-fill-mid" style="width:42%"></div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="font-size:13px;font-weight:600;">Anemia</span>
                                    <span style="font-size:13px;font-weight:700;color:var(--danger);">18.5%</span>
                                </div>
                                <div class="cf-bar">
                                    <div class="cf-fill cf-fill-low" style="width:18%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Float card bottom -->
                        <div class="hero-float-card float-bottom">
                            <div class="stat-icon stat-icon-blue" style="width:36px;height:36px;font-size:14px;">
                                <i class="fas fa-user-nurse"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:13px;">Bidan Sri Wahyuni</div>
                                <div style="color:var(--muted);font-size:11px;">Poli Kebidanan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="text-center">
                <div class="section-badge">
                    <i class="fas fa-star"></i>
                    Fitur Unggulan
                </div>
                <h2 class="section-title">Semua yang Anda Butuhkan</h2>
                <p class="section-desc">
                    Sistem pakar lengkap untuk membantu tenaga bidan memberikan
                    pelayanan kebidanan yang lebih baik dan akurat.
                </p>
            </div>

            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-brain"></i></div>
                    <h4 class="feature-title">Certainty Factor</h4>
                    <p class="feature-desc">
                        Algoritma CF yang teruji mengkombinasikan keyakinan pakar dan
                        pengguna untuk menghasilkan diagnosa yang akurat.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-stethoscope"></i></div>
                    <h4 class="feature-title">9 Kondisi Kebidanan</h4>
                    <p class="feature-desc">
                        Mencakup preeklampsia, hiperemesis, anemia, KPD, dan kondisi
                        kebidanan penting lainnya.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h4 class="feature-title">Laporan Lengkap</h4>
                    <p class="feature-desc">
                        Riwayat diagnosa tersimpan dengan detail perhitungan CF,
                        rekomendasi penanganan, dan cetak PDF.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-users-gear"></i></div>
                    <h4 class="feature-title">Multi User</h4>
                    <p class="feature-desc">
                        Manajemen akun Admin dan Bidan dengan hak akses berbeda
                        untuk keamanan data.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-mobile-screen"></i></div>
                    <h4 class="feature-title">Responsif</h4>
                    <p class="feature-desc">
                        Dapat diakses dari desktop, tablet, maupun smartphone
                        untuk fleksibilitas penggunaan.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-print"></i></div>
                    <h4 class="feature-title">Cetak Hasil</h4>
                    <p class="feature-desc">
                        Hasil diagnosa dapat dicetak atau disimpan sebagai PDF
                        sebagai dokumentasi medis.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center position-relative">
            <h2 class="cta-title">Siap Memulai Diagnosa?</h2>
            <p class="cta-desc">
                Masuk ke sistem dan mulai gunakan kekuatan Certainty Factor
                untuk diagnosa kebidanan yang lebih akurat.
            </p>
            <a href="<?= url('login.php') ?>" class="btn btn-primary btn-xl">
                <i class="fas fa-right-to-bracket me-2"></i>Masuk ke Sistem
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <p class="mb-0">
                &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; <?= APP_SUBTITLE ?>.
                Dibangun dengan <i class="fas fa-heart text-danger mx-1"></i> menggunakan PHP Native &amp; MySQL.
            </p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/theme.js') ?>"></script>
    </body>
    </html>
    <?php
}
