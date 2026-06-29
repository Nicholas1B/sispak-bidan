<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM solusi WHERE id=$id");
    setFlash('Data solusi berhasil dihapus.', 'success');
    redirect('admin/solusi.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $kondisi_id = (int)($_POST['kondisi_id'] ?? 0);
    $judul      = sanitize($conn, $_POST['judul'] ?? '');
    $tindakan   = sanitize($conn, $_POST['tindakan'] ?? '');
    $rujukan    = sanitize($conn, $_POST['rujukan'] ?? 'Tidak');

    if (!$kondisi_id || !$judul || !$tindakan) {
        setFlash('Semua field wajib diisi!', 'danger');
    } else {
        if ($id > 0) {
            $conn->query("UPDATE solusi SET kondisi_id=$kondisi_id, judul='$judul', tindakan='$tindakan', rujukan='$rujukan' WHERE id=$id");
            setFlash('Data solusi berhasil diperbarui!', 'success');
        } else {
            $conn->query("INSERT INTO solusi (kondisi_id, judul, tindakan, rujukan) VALUES ($kondisi_id,'$judul','$tindakan','$rujukan')");
            setFlash('Data solusi berhasil ditambahkan!', 'success');
        }
        redirect('admin/solusi.php');
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM solusi WHERE id=$id");
    if ($res->num_rows) $editData = $res->fetch_assoc();
}

$kondisiList = $conn->query("SELECT id, kode, nama_kondisi FROM kondisi ORDER BY kode");
$data        = $conn->query("
    SELECT s.*, k.nama_kondisi, k.kode AS k_kode
    FROM solusi s
    JOIN kondisi k ON s.kondisi_id = k.id
    ORDER BY k.kode
");

$pageTitle  = 'Data Solusi';
$activePage = 'solusi';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Data Solusi" style="display:none">Data Solusi</h1>
            <?php showFlash(); ?>

            <div class="row g-4 align-items-start">

                <!-- Tabel -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-pills"></i></span>
                                Daftar Solusi / Penanganan
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Kondisi</th>
                                        <th>Judul Penanganan</th>
                                        <th width="110">Rujuk?</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($data->num_rows === 0): ?>
                                    <tr><td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-pills"></i></div>
                                            <h5>Belum Ada Data Solusi</h5>
                                        </div>
                                    </td></tr>
                                <?php else:
                                    $no = 1;
                                    while ($row = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted"><?= $no++ ?></td>
                                        <td>
                                            <span class="badge bg-primary-soft text-primary me-1">
                                                <?= e($row['k_kode']) ?>
                                            </span>
                                            <small><?= e($row['nama_kondisi']) ?></small>
                                        </td>
                                        <td><strong><?= e($row['judul']) ?></strong></td>
                                        <td>
                                            <?php if ($row['rujukan'] === 'Ya'): ?>
                                            <span class="badge bg-danger-soft text-danger">
                                                <i class="fas fa-triangle-exclamation me-1"></i>Perlu Rujuk
                                            </span>
                                            <?php else: ?>
                                            <span class="badge bg-success-soft text-success">
                                                <i class="fas fa-check me-1"></i>Mandiri
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"
                                                   data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                                   data-confirm="Yakin ingin menghapus solusi ini?"
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
                    </div>
                </div>

                <!-- Form -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon">
                                    <i class="fas fa-<?= $editData ? 'pen' : 'plus' ?>"></i>
                                </span>
                                <?= $editData ? 'Edit Solusi' : 'Tambah Solusi' ?>
                            </h6>
                            <?php if ($editData): ?>
                            <a href="<?= url('admin/solusi.php') ?>" class="btn btn-light-muted btn-sm">
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
                                    <label class="form-label">Kondisi <span class="required">*</span></label>
                                    <select name="kondisi_id" class="form-select" required>
                                        <option value="">— Pilih Kondisi —</option>
                                        <?php $kondisiList->data_seek(0); while ($k = $kondisiList->fetch_assoc()): ?>
                                        <option value="<?= $k['id'] ?>"
                                            <?= ($editData['kondisi_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                            [<?= e($k['kode']) ?>] <?= e($k['nama_kondisi']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Judul Penanganan <span class="required">*</span></label>
                                    <input type="text" name="judul" class="form-control"
                                           placeholder="Judul singkat penanganan"
                                           value="<?= e($editData['judul'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tindakan / Penanganan <span class="required">*</span></label>
                                    <textarea name="tindakan" class="form-control" rows="5"
                                              placeholder="Langkah-langkah penanganan..."><?= e($editData['tindakan'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Perlu Dirujuk?</label>
                                    <select name="rujukan" class="form-select">
                                        <option value="Tidak" <?= ($editData['rujukan'] ?? 'Tidak') === 'Tidak' ? 'selected' : '' ?>>
                                            Tidak — Bisa ditangani mandiri
                                        </option>
                                        <option value="Ya" <?= ($editData['rujukan'] ?? '') === 'Ya' ? 'selected' : '' ?>>
                                            Ya — Perlu rujukan
                                        </option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 justify-content-center">
                                    <i class="fas fa-<?= $editData ? 'floppy-disk' : 'plus' ?> me-2"></i>
                                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Solusi' ?>
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
