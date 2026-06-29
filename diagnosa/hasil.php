<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$rd           = null;
$gejalaDetail = [];
$allHasil     = [];

// ── Load dari URL param (riwayat lama) ──────────────────────
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $rdQ = $conn->query("
        SELECT rd.*, p.nama_pasien, p.usia, p.usia_kehamilan, p.no_hp, p.alamat,
               k.nama_kondisi, k.kode AS k_kode, k.deskripsi,
               u.nama AS bidan_nama,
               s.tindakan, s.rujukan, s.judul AS sol_judul
        FROM riwayat_diagnosa rd
        JOIN pasien p ON rd.pasien_id = p.id
        LEFT JOIN kondisi k ON rd.kondisi_id = k.id
        LEFT JOIN solusi s ON s.kondisi_id = k.id
        JOIN users u ON rd.user_id = u.id
        WHERE rd.id=$id LIMIT 1
    ");

    if (!$rdQ->num_rows) {
        setFlash('Data diagnosa tidak ditemukan.', 'danger');
        redirect('diagnosa/riwayat.php');
    }

    $rd   = $rdQ->fetch_assoc();
    $detQ = $conn->query("
        SELECT dr.cf_user, g.kode, g.nama_gejala
        FROM detail_riwayat dr
        JOIN gejala g ON dr.gejala_id = g.id
        WHERE dr.riwayat_id=$id
        ORDER BY g.kode
    ");
    while ($d = $detQ->fetch_assoc()) $gejalaDetail[] = $d;

// ── Load dari session (hasil baru) ───────────────────────────
} elseif (isset($_SESSION['hasil_diagnosa'])) {
    $sess       = $_SESSION['hasil_diagnosa'];
    $riwayat_id = $sess['riwayat_id'];
    $allHasil   = $sess['hasil'] ?? [];
    unset($_SESSION['hasil_diagnosa']);

    $rdQ = $conn->query("
        SELECT rd.*, p.nama_pasien, p.usia, p.usia_kehamilan, p.no_hp, p.alamat,
               k.nama_kondisi, k.kode AS k_kode, k.deskripsi,
               u.nama AS bidan_nama,
               s.tindakan, s.rujukan, s.judul AS sol_judul
        FROM riwayat_diagnosa rd
        JOIN pasien p ON rd.pasien_id = p.id
        LEFT JOIN kondisi k ON rd.kondisi_id = k.id
        LEFT JOIN solusi s ON s.kondisi_id = k.id
        JOIN users u ON rd.user_id = u.id
        WHERE rd.id=$riwayat_id LIMIT 1
    ");
    $rd   = $rdQ->fetch_assoc();
    $detQ = $conn->query("
        SELECT dr.cf_user, g.kode, g.nama_gejala
        FROM detail_riwayat dr
        JOIN gejala g ON dr.gejala_id = g.id
        WHERE dr.riwayat_id=$riwayat_id
        ORDER BY g.kode
    ");
    while ($d = $detQ->fetch_assoc()) $gejalaDetail[] = $d;

} else {
    redirect('diagnosa/konsultasi.php');
}

$pct       = (float)$rd['persentase'];
$tingkat   = $pct >= 70 ? 'TINGGI' : ($pct >= 40 ? 'SEDANG' : 'RENDAH');
$cfClass   = $pct >= 70 ? 'cf-fill-high' : ($pct >= 40 ? 'cf-fill-mid' : 'cf-fill-low');
$cfBadgeClass = $pct >= 70 ? 'bg-success text-white' : ($pct >= 40 ? 'bg-warning text-white' : 'bg-danger text-white');

$cetakId   = $rd['id'] ?? $riwayat_id ?? 0;

$cfLabels  = [
    '0.2' => 'Tidak Yakin',
    '0.4' => 'Sedikit Yakin',
    '0.6' => 'Cukup Yakin',
    '0.8' => 'Yakin',
    '1.0' => 'Sangat Yakin',
];

$pageTitle  = 'Hasil Diagnosa';
$activePage = 'riwayat';
$extraJs    = '<script src="' . asset('js/diagnosa.js') . '"></script>';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Hasil Diagnosa" style="display:none">Hasil Diagnosa</h1>

            <div style="max-width:880px;margin:0 auto;" id="printArea">

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mb-4 no-print flex-wrap">
                    <a href="<?= url('diagnosa/konsultasi.php') ?>" class="btn btn-primary">
                        <i class="fas fa-stethoscope me-2"></i>Diagnosa Baru
                    </a>
                    <a href="<?= url('diagnosa/riwayat.php') ?>" class="btn btn-light-muted">
                        <i class="fas fa-clipboard-list me-2"></i>Riwayat
                    </a>
                    <?php if ($cetakId): ?>
                    <a href="<?= url('diagnosa/detail_cf.php') ?>?id=<?= $cetakId ?>"
                       class="btn btn-light-muted">
                        <i class="fas fa-calculator me-2"></i>Detail CF
                    </a>
                    <a href="<?= url('diagnosa/cetak.php') ?>?id=<?= $cetakId ?>"
                       target="_blank" class="btn btn-success">
                        <i class="fas fa-print me-2"></i>Cetak / PDF
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Hasil Header -->
                <div class="hasil-header mb-4 card">
                    <div style="position:relative;z-index:1;">
                        <div class="mb-2" style="font-size:12px;opacity:.8;font-weight:600;text-transform:uppercase;letter-spacing:.08em;">
                            <i class="fas fa-clipboard-check me-1"></i>
                            Hasil Diagnosa Sistem Pakar Kebidanan
                        </div>
                        <h2>
                            <?= $rd['nama_kondisi']
                                ? e($rd['nama_kondisi'])
                                : 'Tidak Terdeteksi Kondisi Spesifik' ?>
                        </h2>
                        <p style="opacity:.8;font-size:13px;margin-top:4px;">
                            <?= tglIndo($rd['tanggal']) ?>, <?= date('H:i', strtotime($rd['tanggal'])) ?>
                            &mdash; Bidan: <?= e($rd['bidan_nama']) ?>
                        </p>

                        <!-- CF Gauge -->
                        <div class="mt-4 p-3 rounded" style="background:rgba(255,255,255,.15);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:13px;">Tingkat Kepastian (Certainty Factor)</span>
                                <strong style="font-size:24px;"><?= number_format($pct, 2) ?>%</strong>
                            </div>
                            <div class="cf-gauge-bar">
                                <div class="cf-gauge-fill cf-animate"
                                     data-width="<?= min($pct, 100) ?>"></div>
                            </div>
                            <div class="text-center mt-2">
                                <span style="background:rgba(255,255,255,.2);padding:3px 14px;border-radius:100px;font-size:11px;font-weight:700;">
                                    KEYAKINAN <?= $tingkat ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Grid -->
                <div class="row g-3 mb-3">

                    <!-- Data Pasien -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <span class="title-icon"><i class="fas fa-user"></i></span>
                                    Data Pasien
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="info-list">
                                    <li><span>Nama</span><span><strong><?= e($rd['nama_pasien']) ?></strong></span></li>
                                    <?php if ($rd['usia']): ?>
                                    <li><span>Usia</span><span><?= $rd['usia'] ?> tahun</span></li>
                                    <?php endif; ?>
                                    <?php if ($rd['usia_kehamilan']): ?>
                                    <li><span>Usia Kehamilan</span><span><?= $rd['usia_kehamilan'] ?> minggu</span></li>
                                    <?php endif; ?>
                                    <?php if ($rd['no_hp']): ?>
                                    <li><span>No. HP</span><span><?= e($rd['no_hp']) ?></span></li>
                                    <?php endif; ?>
                                    <?php if ($rd['alamat']): ?>
                                    <li><span>Alamat</span><span><?= e($rd['alamat']) ?></span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan CF -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <span class="title-icon"><i class="fas fa-chart-pie"></i></span>
                                    Ringkasan CF
                                </h6>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($rd['k_kode']): ?>
                                <span class="badge bg-primary-soft text-primary mb-2"
                                      style="font-size:14px;padding:6px 16px;">
                                    <?= e($rd['k_kode']) ?>
                                </span>
                                <h4 class="fw-bold mb-1"><?= e($rd['nama_kondisi']) ?></h4>
                                <div class="display-heading mb-1" style="font-size:42px;color:<?= $pct >= 70 ? 'var(--success)' : ($pct >= 40 ? 'var(--warning)' : 'var(--danger)') ?>;">
                                    <?= number_format($pct, 1) ?>%
                                </div>
                                <div class="text-muted" style="font-size:12px;">
                                    CF = <?= number_format($rd['cf_hasil'], 4) ?>
                                </div>
                                <span class="badge <?= $cfBadgeClass ?> mt-2">
                                    Keyakinan <?= $tingkat ?>
                                </span>
                                <?php else: ?>
                                <div class="empty-state" style="padding:20px;">
                                    <div class="empty-icon"><i class="fas fa-question"></i></div>
                                    <p>Gejala tidak mengarah ke kondisi spesifik dalam basis pengetahuan.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gejala yang Dipilih -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title">
                            <span class="title-icon"><i class="fas fa-virus"></i></span>
                            Gejala yang Dipilih
                            <span class="badge bg-primary-soft text-primary ms-1"><?= count($gejalaDetail) ?></span>
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="45">No</th>
                                    <th width="70">Kode</th>
                                    <th>Nama Gejala</th>
                                    <th width="100">CF User</th>
                                    <th width="160">Keyakinan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; foreach ($gejalaDetail as $g):
                                $cfU    = number_format($g['cf_user'], 1);
                                $label  = $cfLabels[$cfU] ?? 'Cukup Yakin';
                                $pctU   = (float)$g['cf_user'] * 100;
                                $cls    = $pctU >= 60 ? 'cf-fill-high' : ($pctU >= 40 ? 'cf-fill-mid' : 'cf-fill-low');
                            ?>
                            <tr>
                                <td class="text-muted"><?= $no++ ?></td>
                                <td>
                                    <span class="badge bg-primary-soft text-primary">
                                        <?= e($g['kode']) ?>
                                    </span>
                                </td>
                                <td><?= e($g['nama_gejala']) ?></td>
                                <td><strong><?= number_format($g['cf_user'], 2) ?></strong></td>
                                <td>
                                    <div style="font-size:11px;color:var(--muted);margin-bottom:3px;"><?= $label ?></div>
                                    <div class="cf-bar" style="width:120px;">
                                        <div class="cf-fill <?= $cls ?>" style="width:<?= $pctU ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Informasi Kondisi -->
                <?php if ($rd['nama_kondisi'] && $rd['deskripsi']): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="card-title">
                            <span class="title-icon"><i class="fas fa-circle-info"></i></span>
                            Tentang <?= e($rd['nama_kondisi']) ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0" style="line-height:1.8;font-size:13.5px;">
                            <?= e($rd['deskripsi']) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Rekomendasi -->
                <?php if ($rd['tindakan']): ?>
                <div class="card mb-3 card-success-border">
                    <div class="card-header" style="background:#f0fdf4;">
                        <h6 class="card-title">
                            <span class="title-icon" style="background:var(--success-soft);color:var(--success);">
                                <i class="fas fa-pills"></i>
                            </span>
                            Rekomendasi Penanganan
                        </h6>
                        <?php if ($rd['rujukan'] === 'Ya'): ?>
                        <span class="badge bg-danger-soft text-danger">
                            <i class="fas fa-triangle-exclamation me-1"></i>Perlu Dirujuk
                        </span>
                        <?php else: ?>
                        <span class="badge bg-success-soft text-success">
                            <i class="fas fa-circle-check me-1"></i>Bisa Mandiri
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($rd['rujukan'] === 'Ya'): ?>
                        <div class="alert alert-danger alert-static mb-3">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span>
                                <strong>PERHATIAN:</strong> Kondisi ini memerlukan rujukan ke dokter spesialis
                                kandungan atau fasilitas kesehatan yang lebih lengkap!
                            </span>
                        </div>
                        <?php endif; ?>
                        <h5 class="fw-bold mb-2"><?= e($rd['sol_judul']) ?></h5>
                        <p style="line-height:1.8;font-size:13.5px;color:var(--body-text);">
                            <?= nl2br(e($rd['tindakan'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Disclaimer -->
                <div class="alert alert-warning alert-static mb-4">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>
                        <strong>Catatan:</strong> Hasil diagnosa ini bersifat pendukung keputusan dan tidak
                        menggantikan pemeriksaan klinis oleh tenaga medis. Selalu lakukan pemeriksaan fisik
                        dan anamnesis lengkap sebelum menegakkan diagnosis.
                    </span>
                </div>

                <!-- Bottom Actions -->
                <div class="d-flex gap-3 justify-content-center flex-wrap no-print pb-2">
                    <a href="<?= url('diagnosa/konsultasi.php') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-stethoscope me-2"></i>Diagnosa Baru
                    </a>
                    <a href="<?= url('diagnosa/riwayat.php') ?>" class="btn btn-light-muted btn-lg">
                        <i class="fas fa-clipboard-list me-2"></i>Riwayat
                    </a>
                    <?php if ($cetakId): ?>
                    <a href="<?= url('diagnosa/detail_cf.php') ?>?id=<?= $cetakId ?>"
                       class="btn btn-light-muted btn-lg">
                        <i class="fas fa-calculator me-2"></i>Detail Perhitungan CF
                    </a>
                    <a href="<?= url('diagnosa/cetak.php') ?>?id=<?= $cetakId ?>"
                       target="_blank" class="btn btn-success btn-lg">
                        <i class="fas fa-print me-2"></i>Cetak / PDF
                    </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
