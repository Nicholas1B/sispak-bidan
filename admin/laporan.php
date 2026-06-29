<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

$bulan          = sanitize($conn, $_GET['bulan'] ?? date('Y-m'));
$kondisi_filter = (int)($_GET['k'] ?? 0);

$whereArr = [];
if ($bulan)           $whereArr[] = "DATE_FORMAT(rd.tanggal,'%Y-%m')='$bulan'";
if ($kondisi_filter)  $whereArr[] = "rd.kondisi_id=$kondisi_filter";
$where = $whereArr ? 'WHERE ' . implode(' AND ', $whereArr) : '';

$data = $conn->query("
    SELECT rd.*, p.nama_pasien, p.usia, p.usia_kehamilan,
           k.nama_kondisi, k.kode AS k_kode,
           u.nama AS bidan_nama
    FROM riwayat_diagnosa rd
    JOIN pasien p ON rd.pasien_id = p.id
    LEFT JOIN kondisi k ON rd.kondisi_id = k.id
    JOIN users u ON rd.user_id = u.id
    $where
    ORDER BY rd.tanggal DESC
");

$stats = $conn->query("
    SELECT k.nama_kondisi, COUNT(*) AS total
    FROM riwayat_diagnosa rd
    LEFT JOIN kondisi k ON rd.kondisi_id = k.id
    $where
    GROUP BY rd.kondisi_id
    ORDER BY total DESC
    LIMIT 5
");

$totalDiag  = (int)$conn->query("SELECT COUNT(*) AS c FROM riwayat_diagnosa $where")->fetch_assoc()['c'];
$avgCF      = $conn->query("SELECT AVG(persentase) AS a FROM riwayat_diagnosa $where")->fetch_assoc()['a'];
$kondisiList = $conn->query("SELECT id, kode, nama_kondisi FROM kondisi ORDER BY kode");

$pageTitle  = 'Laporan Diagnosa';
$activePage = 'laporan';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Laporan Diagnosa" style="display:none">Laporan</h1>
            <?php showFlash(); ?>

            <!-- Filter & Actions -->
            <div class="filter-bar no-print">
                <i class="fas fa-filter text-muted"></i>
                <form method="GET" class="d-flex gap-3 flex-wrap align-items-end flex-grow-1">
                    <div>
                        <label class="form-label mb-1" style="font-size:12px;">Bulan</label>
                        <input type="month" name="bulan" class="form-control form-control-sm"
                               value="<?= e($bulan) ?>">
                    </div>
                    <div>
                        <label class="form-label mb-1" style="font-size:12px;">Kondisi</label>
                        <select name="k" class="form-select form-select-sm" style="min-width:200px;">
                            <option value="">Semua Kondisi</option>
                            <?php while ($k = $kondisiList->fetch_assoc()): ?>
                            <option value="<?= $k['id'] ?>"
                                    <?= $kondisi_filter == $k['id'] ? 'selected' : '' ?>>
                                [<?= e($k['kode']) ?>] <?= e($k['nama_kondisi']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-magnifying-glass me-1"></i>Filter
                    </button>
                    <a href="<?= url('admin/laporan.php') ?>" class="btn btn-light-muted btn-sm">
                        <i class="fas fa-rotate me-1"></i>Reset
                    </a>
                </form>
                <button onclick="window.print()" class="btn btn-light-muted btn-sm ms-auto">
                    <i class="fas fa-print me-1"></i>Cetak
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-blue"><i class="fas fa-clipboard-list"></i></div>
                        <div>
                            <div class="stat-value"><?= $totalDiag ?></div>
                            <div class="stat-label">Total Diagnosa</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-green"><i class="fas fa-percent"></i></div>
                        <div>
                            <div class="stat-value"><?= $avgCF ? number_format($avgCF, 1) . '%' : '—' ?></div>
                            <div class="stat-label">Rata-rata CF</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-purple"><i class="fas fa-calendar"></i></div>
                        <div>
                            <div class="stat-value" style="font-size:18px;"><?= date('M Y', strtotime($bulan . '-01')) ?></div>
                            <div class="stat-label">Periode Filter</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-orange"><i class="fas fa-tag"></i></div>
                        <div>
                            <div class="stat-value"><?= $stats->num_rows ?></div>
                            <div class="stat-label">Variasi Kondisi</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Kondisi + Tabel -->
            <div class="row g-4">

                <!-- Top 5 Kondisi -->
                <?php $stats->data_seek(0); ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-chart-bar"></i></span>
                                Top Kondisi Terdiagnosa
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if ($stats->num_rows === 0): ?>
                            <div class="empty-state" style="padding:24px;">
                                <div class="empty-icon"><i class="fas fa-chart-bar"></i></div>
                                <p>Belum ada data.</p>
                            </div>
                            <?php else:
                                $maxVal = null;
                                $statsArr = [];
                                while ($s = $stats->fetch_assoc()) $statsArr[] = $s;
                                $maxVal = $statsArr[0]['total'] ?? 1;
                                foreach ($statsArr as $s):
                                    $pct = round(($s['total'] / $maxVal) * 100);
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-semibold"><?= e($s['nama_kondisi'] ?: 'Tidak Teridentifikasi') ?></small>
                                    <small class="text-muted"><?= $s['total'] ?> kasus</small>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tabel Laporan -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-table-list"></i></span>
                                Detail Laporan
                                <span class="badge bg-primary-soft text-primary ms-1"><?= $totalDiag ?></span>
                            </h6>
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
                                        <th class="no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($data->num_rows === 0): ?>
                                    <tr><td colspan="6">
                                        <div class="empty-state" style="padding:32px;">
                                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                                            <h5>Tidak Ada Data</h5>
                                            <p>Tidak ada diagnosa pada periode dan filter yang dipilih.</p>
                                        </div>
                                    </td></tr>
                                <?php else:
                                    while ($r = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td style="white-space:nowrap;">
                                            <div style="font-size:12px;font-weight:600;"><?= date('d/m/Y', strtotime($r['tanggal'])) ?></div>
                                            <div class="text-muted" style="font-size:11px;"><?= date('H:i', strtotime($r['tanggal'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= e($r['nama_pasien']) ?></div>
                                            <?php if ($r['usia_kehamilan']): ?>
                                            <div class="text-muted" style="font-size:11px;">
                                                <?= $r['usia_kehamilan'] ?> minggu
                                            </div>
                                            <?php endif; ?>
                                        </td>
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
                                        <td class="no-print">
                                            <a href="<?= url('diagnosa/hasil.php') ?>?id=<?= $r['id'] ?>"
                                               class="btn btn-primary btn-sm"
                                               data-bs-toggle="tooltip" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
