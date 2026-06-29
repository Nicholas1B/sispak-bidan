<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireAdmin();

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id == $_SESSION['user_id']) {
        setFlash('Tidak dapat menghapus akun Anda sendiri!', 'danger');
    } else {
        $conn->query("DELETE FROM users WHERE id=$id");
        setFlash('User berhasil dihapus.', 'success');
    }
    redirect('admin/users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nama     = sanitize($conn, $_POST['nama'] ?? '');
    $username = sanitize($conn, $_POST['username'] ?? '');
    $role     = sanitize($conn, $_POST['role'] ?? 'bidan');
    $password = $_POST['password'] ?? '';

    if (!$nama || !$username) {
        setFlash('Nama dan username wajib diisi!', 'danger');
    } else {
        if ($id > 0) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $conn->query("UPDATE users SET nama='$nama', username='$username', role='$role', password='$hash' WHERE id=$id");
            } else {
                $conn->query("UPDATE users SET nama='$nama', username='$username', role='$role' WHERE id=$id");
            }
            setFlash('Data user berhasil diperbarui!', 'success');
        } else {
            if (!$password) {
                setFlash('Password wajib diisi untuk user baru!', 'danger');
            } else {
                $chk = $conn->query("SELECT id FROM users WHERE username='$username'")->num_rows;
                if ($chk > 0) {
                    setFlash("Username '$username' sudah digunakan!", 'danger');
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $conn->query("INSERT INTO users (nama, username, password, role) VALUES ('$nama','$username','$hash','$role')");
                    setFlash('User baru berhasil ditambahkan!', 'success');
                }
            }
        }
        redirect('admin/users.php');
    }
}

$editData = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM users WHERE id=$id");
    if ($res->num_rows) $editData = $res->fetch_assoc();
}

$data = $conn->query("SELECT *, (SELECT COUNT(*) FROM riwayat_diagnosa WHERE user_id=users.id) as total_diagnosa FROM users ORDER BY role, nama");

$pageTitle  = 'Manajemen User';
$activePage = 'users';
$extraJs    = '<script src="' . asset('js/validation.js') . '"></script>';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Manajemen User" style="display:none">Manajemen User</h1>
            <?php showFlash(); ?>

            <div class="row g-4 align-items-start">

                <!-- Tabel -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-users-gear"></i></span>
                                Daftar Pengguna
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pengguna</th>
                                        <th>Username</th>
                                        <th width="100">Role</th>
                                        <th class="hide-mobile" width="110">Diagnosa</th>
                                        <th width="110">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($data->num_rows === 0): ?>
                                    <tr><td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fas fa-users"></i></div>
                                            <h5>Belum Ada Pengguna</h5>
                                        </div>
                                    </td></tr>
                                <?php else:
                                    while ($row = $data->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width:34px;height:34px;background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;">
                                                    <?= strtoupper(substr($row['nama'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold" style="font-size:13px;">
                                                        <?= e($row['nama']) ?>
                                                        <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-primary-soft text-primary ms-1" style="font-size:9px;">Anda</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size:11px;">
                                                        Bergabung <?= tglIndo($row['created_at']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code style="font-size:12px;">@<?= e($row['username']) ?></code></td>
                                        <td>
                                            <?php if ($row['role'] === 'admin'): ?>
                                            <span class="badge bg-danger-soft text-danger">
                                                <i class="fas fa-shield-halved me-1"></i>Admin
                                            </span>
                                            <?php else: ?>
                                            <span class="badge bg-info-soft text-info">
                                                <i class="fas fa-user-nurse me-1"></i>Bidan
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="hide-mobile">
                                            <span class="fw-bold text-primary"><?= (int)$row['total_diagnosa'] ?></span>
                                            <span class="text-muted" style="font-size:11px;"> diagnosa</span>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm"
                                                   data-bs-toggle="tooltip" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                                   data-confirm="Yakin ingin menghapus user <?= e($row['nama']) ?>?"
                                                   data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php endif; ?>
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
                                    <i class="fas fa-<?= $editData ? 'pen' : 'user-plus' ?>"></i>
                                </span>
                                <?= $editData ? 'Edit User' : 'Tambah User' ?>
                            </h6>
                            <?php if ($editData): ?>
                            <a href="<?= url('admin/users.php') ?>" class="btn btn-light-muted btn-sm">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST" data-validate>
                                <?php if ($editData): ?>
                                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                                    <input type="text" name="nama" class="form-control"
                                           placeholder="Nama lengkap"
                                           value="<?= e($editData['nama'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username <span class="required">*</span></label>
                                    <input type="text" name="username" class="form-control"
                                           placeholder="Username untuk login"
                                           value="<?= e($editData['username'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Role <span class="required">*</span></label>
                                    <select name="role" class="form-select">
                                        <option value="bidan" <?= ($editData['role'] ?? '') === 'bidan' ? 'selected' : '' ?>>
                                            Bidan
                                        </option>
                                        <option value="admin" <?= ($editData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                            Administrator
                                        </option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">
                                        Password <?= $editData ? '' : '<span class="required">*</span>' ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="passwordInput" name="password"
                                               class="form-control"
                                               placeholder="<?= $editData ? 'Kosongkan jika tidak diubah' : 'Min. 6 karakter' ?>"
                                               <?= $editData ? '' : 'required' ?>>
                                        <button type="button" class="btn btn-outline-secondary"
                                                data-toggle-password="passwordInput"
                                                style="border:1.5px solid var(--border);border-left:none;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if ($editData): ?>
                                    <div class="form-hint">Kosongkan jika tidak ingin mengubah password.</div>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 justify-content-center">
                                    <i class="fas fa-<?= $editData ? 'floppy-disk' : 'user-plus' ?> me-2"></i>
                                    <?= $editData ? 'Simpan Perubahan' : 'Tambah User' ?>
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
