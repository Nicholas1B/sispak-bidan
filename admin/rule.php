<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM rule_cf WHERE id=$id");
    setFlash('Rule CF berhasil dihapus.', 'success');
    redirect('admin/rule.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $kondisi_id = (int)($_POST['kondisi_id'] ?? 0);
    $gejala_id  = (int)($_POST['gejala_id'] ?? 0);
    $cf_pakar   = (float)($_POST['cf_pakar'] ?? 0);

    if (!$kondisi_id || !$gejala_id || $cf_pakar <= 0) {
        setFlash('Semua field wajib diisi!', 'danger');
    } elseif ($cf_pakar < 0.01 || $cf_pakar > 1.0) {
        setFlash('Nilai CF harus antara 0.01 – 1.0', 'danger');
    } else {
        if ($id > 0) {
            $conn->query("UPDATE rule_cf SET kondisi_id=$kondisi_id, gejala_id=$gejala_id, cf_pakar=$cf_pakar WHERE id=$id");
            setFlash('Rule CF berhasil diperbarui!', 'success');
        } else {
            $chk = $conn->query("SELECT id FROM rule_cf WHERE kondisi_id=$kondisi_id AND gejala_id=$gejala_id")->num_rows;
            if ($chk > 0) {
                setFlash('Rule untuk kombinasi kondisi–gejala ini sudah ada!', 'danger');
            } else {
                $conn->query("INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES ($kondisi_id, $gejala_id, $cf_pakar)");
                setFlash('Rule CF berhasil ditambahkan!', 'success');
            }
        }
        redirect('admin/rule.php');
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM rule_cf WHERE id=$id");
    if ($res->num_rows) $editData = $res->fetch_assoc();
}

$filterKondisi = (int)($_GET['k'] ?? 0);
$whereK        = $filterKondisi ? "WHERE r.kondisi_id=$filterKondisi" : '';

$kondisiList = $conn->query("SELECT id, kode, nama_kondisi FROM kondisi ORDER BY kode");
$gejalaList  = $conn->query("SELECT id, kode, nama_gejala FROM gejala ORDER BY kode");

$data = $conn->query("
    SELECT r.*, k.nama_kondisi, k.kode AS k_kode, g.nama_gejala, g.kode AS g_kode
    FROM rule_cf r
    JOIN kondisi k ON r.kondisi_id = k.id
    JOIN gejala g ON r.gejala_id = g.id
    $whereK
    ORDER BY k.kode, g.kode
");

$total = (int)$conn->query("SELECT COUNT(*) AS c FROM rule_cf")->fetch_assoc()['c'];

$cfLabels = [
    '0.2' => 'Tidak Yakin',
    '0.4' => 'Sedikit Yakin',
    '0.6' => 'Cukup Yakin',
    '0.8' => 'Yakin',
    '1.0' => 'Sangat Yakin',
];

$pageTitle  = 'Rule Certainty Factor';
$activePage = 'rule';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Rule Certainty Factor" style="display:none">Rule CF</h1>
            <?php showFlash(); ?>

            <!-- Filter Bar -->
            <div class="filter-bar no-print">
                <i class="fas fa-filter text-muted"></i>
                <strong style="font-size:13px;white-space:nowrap;">Filter Kondisi:</strong>
                <form method="GET" class="d-flex gap-2 flex-wrap align-items-center flex-grow-1">
                    <select name="k" class="form-select form-select-sm" style="width:280px;"
                            onchange="this.form.submit()">
                        <option value="">Semua Kondisi (<?= $total ?> Rule)</option>
                        <?php $kondisiList->data_seek(0); while ($k = $kondisiList->fetch_assoc()): ?>
                        <option value="<?= $k['id'] ?>" <?= $filterKondisi == $k['id'] ? 'selected' : '' ?>>
                            [<?= e($k['kode']) ?>] <?= e($k['nama_kondisi']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if ($filterKondisi): ?>
                    <a href="<?= url('admin/rule.php') ?>" class="btn btn-light-muted btn-sm">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="row g-4 align-items-start">

                <!-- Tabel -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-brain"></i></span>
                                Basis Pengetahuan — Rule CF
                                <span class="badge bg-primary-soft text-primary ms-1"><?= $data->num_rows ?></span>
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kondisi</th>
                                        <th>Gejala</th>
                                        <th width="120">CF Pakar</th>
                                        <th width="140">Keyakinan</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($data->num_rows === 0): ?>
                                    <tr><td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-brain"></i></div>
                                            <h5>Belum Ada Rule CF</h5>
                                            <p>Tambahkan rule menggunakan form di samping.</p>
                                        </div>
                                    </td></tr>
                                <?php else:
                                    while ($row = $data->fetch_assoc()):
                                        $cfVal   = number_format((float)$row['cf_pakar'], 1);
                                        $cfLabel = $cfLabels[$cfVal] ?? 'Custom';
                                        $cfPct   = (float)$row['cf_pakar'] * 100;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning-soft text-warning me-1">
                                                <?= e($row['k_kode']) ?>
                                            </span>
                                            <small class="fw-semibold"><?= e($row['nama_kondisi']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-soft text-primary me-1">
                                                <?= e($row['g_kode']) ?>
                                            </span>
                                            <small><?= e($row['nama_gejala']) ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-bold" style="font-size:15px;">
                                                <?= number_format((float)$row['cf_pakar'], 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-size:11px;color:var(--muted);margin-bottom:3px;">
                                                <?= $cfLabel ?>
                                            </div>
                                            <div class="cf-bar" style="width:100px;">
                                                <div class="cf-fill <?= $cfPct >= 70 ? 'cf-fill-high' : ($cfPct >= 40 ? 'cf-fill-mid' : 'cf-fill-low') ?>"
                                                     style="width:<?= $cfPct ?>%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="?edit=<?= $row['id'] ?><?= $filterKondisi ? '&k='.$filterKondisi : '' ?>"
                                                   class="btn btn-warning btn-sm"
                                                   data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="?hapus=<?= $row['id'] ?><?= $filterKondisi ? '&k='.$filterKondisi : '' ?>"
                                                   class="btn btn-danger btn-sm"
                                                   data-confirm="Yakin hapus rule CF ini?"
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
                                <?= $editData ? 'Edit Rule CF' : 'Tambah Rule CF' ?>
                            </h6>
                            <?php if ($editData): ?>
                            <a href="<?= url('admin/rule.php') ?>" class="btn btn-light-muted btn-sm">
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
                                    <label class="form-label">Gejala <span class="required">*</span></label>
                                    <select name="gejala_id" class="form-select" required>
                                        <option value="">— Pilih Gejala —</option>
                                        <?php $gejalaList->data_seek(0); while ($g = $gejalaList->fetch_assoc()): ?>
                                        <option value="<?= $g['id'] ?>"
                                            <?= ($editData['gejala_id'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                                            [<?= e($g['kode']) ?>] <?= e($g['nama_gejala']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Nilai CF Pakar <span class="required">*</span></label>
                                    <input type="number" name="cf_pakar" class="form-control"
                                           placeholder="0.0 – 1.0"
                                           min="0.01" max="1.0" step="0.01"
                                           value="<?= $editData['cf_pakar'] ?? '' ?>" required>
                                    <div class="form-hint">
                                        Panduan: 0.2=Tidak Yakin | 0.4=Sedikit | 0.6=Cukup | 0.8=Yakin | 1.0=Sangat Yakin
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 justify-content-center">
                                    <i class="fas fa-<?= $editData ? 'floppy-disk' : 'plus' ?> me-2"></i>
                                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Rule' ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card mt-4">
                        <div class="card-body card-body-sm">
                            <h6 class="fw-bold mb-3" style="font-size:13px;">
                                <i class="fas fa-circle-info me-1 text-primary"></i>
                                Formula CF
                            </h6>
                            <div class="p-3 rounded" style="background:#f8fafc;font-size:12px;font-family:monospace;line-height:1.8;">
                                CF(H,E) = CF<sub>user</sub> × CF<sub>pakar</sub><br>
                                CF<sub>gabung</sub> = CF<sub>lama</sub> + CF<sub>baru</sub> × (1 − CF<sub>lama</sub>)
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
