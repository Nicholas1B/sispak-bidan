<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM kondisi WHERE id=$id");
    setFlash('Data kondisi berhasil dihapus.', 'success');
    redirect('admin/kondisi.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['id'] ?? 0);
    $kode = sanitize($conn, $_POST['kode'] ?? '');
    $nama = sanitize($conn, $_POST['nama_kondisi'] ?? '');
    $desk = sanitize($conn, $_POST['deskripsi'] ?? '');

    if (!$kode || !$nama) {
        setFlash('Kode dan Nama Kondisi wajib diisi!', 'danger');
    } else {
        if ($id > 0) {
            $conn->query("UPDATE kondisi SET kode='$kode', nama_kondisi='$nama', deskripsi='$desk' WHERE id=$id");
            setFlash('Data kondisi berhasil diperbarui!', 'success');
        } else {
            $chk = $conn->query("SELECT id FROM kondisi WHERE kode='$kode'")->num_rows;
            if ($chk > 0) {
                setFlash("Kode $kode sudah digunakan!", 'danger');
            } else {
                $conn->query("INSERT INTO kondisi (kode, nama_kondisi, deskripsi) VALUES ('$kode','$nama','$desk')");
                setFlash('Data kondisi berhasil ditambahkan!', 'success');
            }
        }
        redirect('admin/kondisi.php');
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM kondisi WHERE id=$id");
    if ($res->num_rows) $editData = $res->fetch_assoc();
}

$data  = $conn->query("SELECT * FROM kondisi ORDER BY kode ASC");
$total = $data->num_rows;

$pageTitle  = 'Data Kondisi';
$activePage = 'kondisi';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Data Kondisi" style="display:none">Data Kondisi</h1>
            <?php showFlash(); ?>

            <div class="row g-4 align-items-start">

                <!-- Tabel -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-tag"></i></span>
                                Daftar Kondisi / Penyakit
                                <span class="badge bg-warning-soft text-warning ms-1"><?= $total ?></span>
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="80">Kode</th>
                                        <th>Nama Kondisi</th>
                                        <th class="hide-mobile">Deskripsi</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($total === 0): ?>
                                    <tr><td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-tag"></i></div>
                                            <h5>Belum Ada Data Kondisi</h5>
                                        </div>
                                    </td></tr>
                                <?php else:
                                    $no = 1; $data->data_seek(0);
                                    while ($row = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted"><?= $no++ ?></td>
                                        <td>
                                            <span class="badge bg-warning-soft text-warning">
                                                <?= e($row['kode']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= e($row['nama_kondisi']) ?></strong></td>
                                        <td class="text-muted hide-mobile" style="font-size:12px;max-width:260px;">
                                            <?= e(mb_substr($row['deskripsi'] ?? '', 0, 90)) ?>
                                            <?= strlen($row['deskripsi'] ?? '') > 90 ? '…' : '' ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"
                                                   data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                                   data-confirm="Yakin hapus kondisi ini? Semua rule terkait akan ikut terhapus."
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
                                <?= $editData ? 'Edit Kondisi' : 'Tambah Kondisi' ?>
                            </h6>
                            <?php if ($editData): ?>
                            <a href="<?= url('admin/kondisi.php') ?>" class="btn btn-light-muted btn-sm">
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
                                    <label class="form-label">Kode Kondisi <span class="required">*</span></label>
                                    <input type="text" name="kode" class="form-control"
                                           placeholder="Contoh: K01"
                                           value="<?= e($editData['kode'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nama Kondisi <span class="required">*</span></label>
                                    <input type="text" name="nama_kondisi" class="form-control"
                                           placeholder="Contoh: Preeklampsia"
                                           value="<?= e($editData['nama_kondisi'] ?? '') ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="4"
                                              placeholder="Deskripsi kondisi/penyakit..."><?= e($editData['deskripsi'] ?? '') ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 justify-content-center">
                                    <i class="fas fa-<?= $editData ? 'floppy-disk' : 'plus' ?> me-2"></i>
                                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Kondisi' ?>
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
