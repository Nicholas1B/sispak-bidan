<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

// ── Handle Hapus ────────────────────────────────────────────
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM gejala WHERE id=$id");
    setFlash('Data gejala berhasil dihapus.', 'success');
    redirect('admin/gejala.php');
}

// ── Handle Tambah/Edit ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['id'] ?? 0);
    $kode = sanitize($conn, $_POST['kode'] ?? '');
    $nama = sanitize($conn, $_POST['nama_gejala'] ?? '');
    $ket  = sanitize($conn, $_POST['keterangan'] ?? '');

    if (!$kode || !$nama) {
        setFlash('Kode dan Nama Gejala wajib diisi!', 'danger');
    } else {
        if ($id > 0) {
            $conn->query("UPDATE gejala SET kode='$kode', nama_gejala='$nama', keterangan='$ket' WHERE id=$id");
            setFlash('Data gejala berhasil diperbarui!', 'success');
        } else {
            $chk = $conn->query("SELECT id FROM gejala WHERE kode='$kode'")->num_rows;
            if ($chk > 0) {
                setFlash("Kode $kode sudah digunakan!", 'danger');
            } else {
                $conn->query("INSERT INTO gejala (kode, nama_gejala, keterangan) VALUES ('$kode','$nama','$ket')");
                setFlash('Data gejala berhasil ditambahkan!', 'success');
            }
        }
        redirect('admin/gejala.php');
    }
}

// ── Edit Data ───────────────────────────────────────────────
$editData = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM gejala WHERE id=$id");
    if ($res->num_rows) $editData = $res->fetch_assoc();
}

// ── Pagination & Search ─────────────────────────────────────
$search = sanitize($conn, $_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;
$where  = $search ? "WHERE nama_gejala LIKE '%$search%' OR kode LIKE '%$search%'" : '';

$total = (int)$conn->query("SELECT COUNT(*) AS c FROM gejala $where")->fetch_assoc()['c'];
$pages = (int)ceil($total / $limit);
$data  = $conn->query("SELECT * FROM gejala $where ORDER BY kode ASC LIMIT $limit OFFSET $offset");

$pageTitle  = 'Data Gejala';
$activePage = 'gejala';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Data Gejala" style="display:none">Data Gejala</h1>
            <?php showFlash(); ?>

            <div class="row g-4 align-items-start">

                <!-- ── Tabel ── -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-virus"></i></span>
                                Daftar Gejala
                                <span class="badge bg-primary-soft text-primary ms-1"><?= $total ?></span>
                            </h6>
                            <form method="GET" class="d-flex gap-2">
                                <div class="search-wrapper">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" name="q" class="form-control form-control-sm"
                                           placeholder="Cari gejala..." value="<?= e($search) ?>" style="width:200px;">
                                </div>
                                <button class="btn btn-primary btn-sm">Cari</button>
                                <?php if ($search): ?>
                                <a href="<?= url('admin/gejala.php') ?>" class="btn btn-light-muted btn-sm">Reset</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="80">Kode</th>
                                        <th>Nama Gejala</th>
                                        <th class="hide-mobile">Keterangan</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($data->num_rows === 0): ?>
                                    <tr><td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-virus"></i></div>
                                            <h5>Belum Ada Data Gejala</h5>
                                            <p>Tambahkan gejala menggunakan form di samping.</p>
                                        </div>
                                    </td></tr>
                                <?php else:
                                    $no = $offset + 1;
                                    while ($row = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted"><?= $no++ ?></td>
                                        <td>
                                            <span class="badge bg-primary-soft text-primary">
                                                <?= e($row['kode']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= e($row['nama_gejala']) ?></strong></td>
                                        <td class="text-muted hide-mobile" style="font-size:12px;">
                                            <?= e($row['keterangan'] ?: '—') ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="<?= url('admin/gejala.php') ?>?edit=<?= $row['id'] ?>"
                                                   class="btn btn-warning btn-sm"
                                                   data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="<?= url('admin/gejala.php') ?>?hapus=<?= $row['id'] ?>"
                                                   class="btn btn-danger btn-sm"
                                                   data-confirm="Yakin ingin menghapus gejala ini?"
                                                   data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($pages > 1): ?>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Menampilkan <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> dari <?= $total ?> data
                            </small>
                            <nav>
                                <ul class="pagination mb-0">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page-1 ?>&q=<?= urlencode($search) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
                                    <li class="page-item <?= $i==$page?'active':'' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page+1 ?>&q=<?= urlencode($search) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── Form ── -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon">
                                    <i class="fas fa-<?= $editData ? 'pen' : 'plus' ?>"></i>
                                </span>
                                <?= $editData ? 'Edit Gejala' : 'Tambah Gejala' ?>
                            </h6>
                            <?php if ($editData): ?>
                            <a href="<?= url('admin/gejala.php') ?>" class="btn btn-light-muted btn-sm">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($editData): ?>
                                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Kode Gejala <span class="required">*</span></label>
                                    <input type="text" name="kode" class="form-control"
                                           placeholder="Contoh: G01"
                                           value="<?= e($editData['kode'] ?? '') ?>" required>
                                    <div class="form-hint">Kode unik untuk identifikasi gejala</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nama Gejala <span class="required">*</span></label>
                                    <input type="text" name="nama_gejala" class="form-control"
                                           placeholder="Contoh: Tekanan darah tinggi"
                                           value="<?= e($editData['nama_gejala'] ?? '') ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="3"
                                              placeholder="Deskripsi tambahan..."><?= e($editData['keterangan'] ?? '') ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 justify-content-center">
                                    <i class="fas fa-<?= $editData ? 'floppy-disk' : 'plus' ?> me-2"></i>
                                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Gejala' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
