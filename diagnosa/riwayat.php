<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

// ── Filter & Pagination ─────────────────────────────────────
$search      = sanitize($conn, $_GET['q'] ?? '');
$user_filter = isAdmin() ? (int)($_GET['u'] ?? 0) : $_SESSION['user_id'];
$page        = max(1, (int)($_GET['page'] ?? 1));
$limit       = 15;
$offset      = ($page - 1) * $limit;

$whereArr = [];
if ($search)       $whereArr[] = "p.nama_pasien LIKE '%$search%'";
if (!isAdmin())    $whereArr[] = "rd.user_id={$_SESSION['user_id']}";
elseif ($user_filter) $whereArr[] = "rd.user_id=$user_filter";
$where = $whereArr ? 'WHERE ' . implode(' AND ', $whereArr) : '';

$total = (int)$conn->query("
    SELECT COUNT(*) AS c FROM riwayat_diagnosa rd
    JOIN pasien p ON rd.pasien_id = p.id
    $where
")->fetch_assoc()['c'];

$pages = (int)ceil($total / $limit);

$data = $conn->query("
    SELECT rd.*, p.nama_pasien, p.usia_kehamilan,
           k.nama_kondisi, k.kode AS k_kode,
           u.nama AS bidan_nama
    FROM riwayat_diagnosa rd
    JOIN pasien p ON rd.pasien_id = p.id
    LEFT JOIN kondisi k ON rd.kondisi_id = k.id
    JOIN users u ON rd.user_id = u.id
    $where
    ORDER BY rd.tanggal DESC
    LIMIT $limit OFFSET $offset
");

$bidanList = isAdmin()
    ? $conn->query("SELECT id, nama FROM users ORDER BY nama")
    : null;

$pageTitle  = 'Riwayat Diagnosa';
$activePage = 'riwayat';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Riwayat Diagnosa" style="display:none">Riwayat</h1>
            <?php showFlash(); ?>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <i class="fas fa-filter text-muted"></i>
                <form method="GET" class="d-flex gap-2 flex-wrap align-items-end flex-grow-1">
                    <!-- Cari Pasien -->
                    <div>
                        <label class="form-label mb-1" style="font-size:12px;">Cari Pasien</label>
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="q" class="form-control form-control-sm"
                                   placeholder="Nama pasien..." value="<?= e($search) ?>" style="width:200px;">
                        </div>
                    </div>

                    <?php if (isAdmin() && $bidanList): ?>
                    <div>
                        <label class="form-label mb-1" style="font-size:12px;">Filter Bidan</label>
                        <select name="u" class="form-select form-select-sm" style="width:180px;">
                            <option value="">Semua Bidan</option>
                            <?php while ($b = $bidanList->fetch_assoc()): ?>
                            <option value="<?= $b['id'] ?>"
                                    <?= $user_filter == $b['id'] ? 'selected' : '' ?>>
                                <?= e($b['nama']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-magnifying-glass me-1"></i>Cari
                    </button>
                    <?php if ($search || $user_filter): ?>
                    <a href="<?= url('diagnosa/riwayat.php') ?>" class="btn btn-light-muted btn-sm">
                        <i class="fas fa-rotate me-1"></i>Reset
                    </a>
                    <?php endif; ?>
                </form>
                <a href="<?= url('diagnosa/konsultasi.php') ?>" class="btn btn-primary btn-sm ms-auto">
                    <i class="fas fa-stethoscope me-1"></i>Konsultasi Baru
                </a>
            </div>

            <!-- Tabel -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <span class="title-icon"><i class="fas fa-clipboard-list"></i></span>
                        Riwayat Diagnosa
                        <span class="badge bg-primary-soft text-primary ms-1"><?= $total ?></span>
                    </h6>
                    <small class="text-muted">
                        Menampilkan <?= $total > 0 ? $offset + 1 : 0 ?>–<?= min($offset + $limit, $total) ?>
                        dari <?= $total ?> data
                    </small>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th width="130">Tanggal</th>
                                <th>Nama Pasien</th>
                                <th class="hide-mobile" width="120">Usia Kehamilan</th>
                                <th>Kondisi Terdiagnosa</th>
                                <th width="100">CF</th>
                                <?php if (isAdmin()): ?>
                                <th class="hide-mobile" width="130">Bidan</th>
                                <?php endif; ?>
                                <th width="130">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($data->num_rows === 0): ?>
                            <tr><td colspan="<?= isAdmin() ? 8 : 7 ?>">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                                    <h5>Belum Ada Riwayat Diagnosa</h5>
                                    <p>Mulai konsultasi untuk menambah riwayat diagnosa pasien.</p>
                                    <a href="<?= url('diagnosa/konsultasi.php') ?>" class="btn btn-primary mt-3">
                                        <i class="fas fa-stethoscope me-2"></i>Mulai Konsultasi
                                    </a>
                                </div>
                            </td></tr>
                        <?php else:
                            $no = $offset + 1;
                            while ($r = $data->fetch_assoc()):
                                $pct = (float)$r['persentase'];
                        ?>
                            <tr>
                                <td class="text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold" style="font-size:12px;">
                                        <?= date('d/m/Y', strtotime($r['tanggal'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:11px;">
                                        <?= date('H:i', strtotime($r['tanggal'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= e($r['nama_pasien']) ?></strong>
                                </td>
                                <td class="hide-mobile">
                                    <?= $r['usia_kehamilan']
                                        ? '<span class="badge bg-info-soft text-info">' . $r['usia_kehamilan'] . ' minggu</span>'
                                        : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td>
                                    <?php if ($r['nama_kondisi']): ?>
                                    <span class="badge bg-primary-soft text-primary">
                                        <?= e($r['nama_kondisi']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary text-white" style="font-size:11px;">
                                        Tidak Terdeteksi
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= cfBadge($pct) ?></td>
                                <?php if (isAdmin()): ?>
                                <td class="hide-mobile">
                                    <small class="text-muted"><?= e($r['bidan_nama']) ?></small>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <div class="action-btns">
                                        <a href="<?= url('diagnosa/hasil.php') ?>?id=<?= $r['id'] ?>"
                                           class="btn btn-primary btn-sm"
                                           data-bs-toggle="tooltip" title="Lihat Hasil">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= url('diagnosa/detail_cf.php') ?>?id=<?= $r['id'] ?>"
                                           class="btn btn-light-muted btn-sm"
                                           data-bs-toggle="tooltip" title="Detail Perhitungan CF">
                                            <i class="fas fa-calculator"></i>
                                        </a>
                                        <a href="<?= url('diagnosa/cetak.php') ?>?id=<?= $r['id'] ?>"
                                           target="_blank"
                                           class="btn btn-success btn-sm"
                                           data-bs-toggle="tooltip" title="Cetak / PDF">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pages > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Halaman <?= $page ?> dari <?= $pages ?>
                    </small>
                    <nav aria-label="Pagination">
                        <ul class="pagination mb-0">
                            <!-- Prev -->
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?page=<?= $page-1 ?>&q=<?= urlencode($search) ?>&u=<?= $user_filter ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <!-- Pages -->
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage   = min($pages, $page + 2);
                            if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1&q=<?= urlencode($search) ?>&u=<?= $user_filter ?>">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?page=<?= $i ?>&q=<?= urlencode($search) ?>&u=<?= $user_filter ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $pages): ?>
                            <?php if ($endPage < $pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pages ?>&q=<?= urlencode($search) ?>&u=<?= $user_filter ?>">
                                    <?= $pages ?>
                                </a>
                            </li>
                            <?php endif; ?>

                            <!-- Next -->
                            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?page=<?= $page+1 ?>&q=<?= urlencode($search) ?>&u=<?= $user_filter ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
