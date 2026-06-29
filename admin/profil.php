<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user    = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'profil') {
        $nama = sanitize($conn, $_POST['nama'] ?? '');
        if (!$nama) {
            setFlash('Nama tidak boleh kosong!', 'danger');
        } else {
            $conn->query("UPDATE users SET nama='$nama' WHERE id=$user_id");
            $_SESSION['nama'] = $nama;
            setFlash('Profil berhasil diperbarui!', 'success');
        }

    } elseif ($action === 'password') {
        $old     = $_POST['old_password']     ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($old, $user['password'])) {
            setFlash('Password lama salah!', 'danger');
        } elseif (strlen($new) < 6) {
            setFlash('Password baru minimal 6 karakter!', 'danger');
        } elseif ($new !== $confirm) {
            setFlash('Konfirmasi password tidak cocok!', 'danger');
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $conn->query("UPDATE users SET password='$hash' WHERE id=$user_id");
            setFlash('Password berhasil diubah!', 'success');
        }
    }

    redirect('admin/profil.php');
}

// Refresh user data
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

$totalDiagnosa = (int)$conn->query("SELECT COUNT(*) AS c FROM riwayat_diagnosa WHERE user_id=$user_id")->fetch_assoc()['c'];
$bulanIni      = (int)$conn->query("SELECT COUNT(*) AS c FROM riwayat_diagnosa WHERE user_id=$user_id AND MONTH(tanggal)=MONTH(NOW()) AND YEAR(tanggal)=YEAR(NOW())")->fetch_assoc()['c'];
$lastDiag      = $conn->query("SELECT tanggal FROM riwayat_diagnosa WHERE user_id=$user_id ORDER BY tanggal DESC LIMIT 1")->fetch_assoc();

$pageTitle  = 'Profil Saya';
$activePage = 'profil';
$extraJs    = '<script src="' . asset('js/validation.js') . '"></script>';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Profil Saya" style="display:none">Profil Saya</h1>
            <?php showFlash(); ?>

            <div class="row g-4 align-items-start">

                <!-- Left: Profile Card + Stats -->
                <div class="col-md-4 col-lg-3">

                    <!-- Profile Card -->
                    <div class="card text-center mb-4">
                        <div class="card-body p-4">
                            <div class="mx-auto mb-3"
                                 style="width:80px;height:80px;background:linear-gradient(135deg,var(--primary),var(--secondary));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;color:white;">
                                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                            </div>
                            <h5 class="fw-bold mb-1"><?= e($user['nama']) ?></h5>
                            <p class="text-muted mb-2" style="font-size:13px;">
                                <i class="fas fa-at me-1"></i><?= e($user['username']) ?>
                            </p>
                            <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-danger-soft text-danger">
                                <i class="fas fa-shield-halved me-1"></i>Administrator
                            </span>
                            <?php else: ?>
                            <span class="badge bg-info-soft text-info">
                                <i class="fas fa-user-nurse me-1"></i>Bidan
                            </span>
                            <?php endif; ?>
                            <hr>
                            <div class="text-muted" style="font-size:12px;">
                                <i class="fas fa-calendar me-1"></i>
                                Bergabung <?= tglIndo($user['created_at']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-chart-line"></i></span>
                                Statistik Saya
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="sys-info-list">
                                <li class="sys-info-item">
                                    <span class="sys-info-key">Total Diagnosa</span>
                                    <span class="sys-info-val text-primary fw-bold" style="font-size:20px;">
                                        <?= $totalDiagnosa ?>
                                    </span>
                                </li>
                                <li class="sys-info-item">
                                    <span class="sys-info-key">Bulan Ini</span>
                                    <span class="sys-info-val"><?= $bulanIni ?></span>
                                </li>
                                <li class="sys-info-item">
                                    <span class="sys-info-key">Diagnosa Terakhir</span>
                                    <span class="sys-info-val" style="font-size:12px;">
                                        <?= $lastDiag ? tglIndo($lastDiag['tanggal']) : '—' ?>
                                    </span>
                                </li>
                            </ul>
                            <a href="<?= url('diagnosa/riwayat.php') ?>" class="btn btn-primary w-100 justify-content-center mt-3 btn-sm">
                                <i class="fas fa-clock-rotate-left me-1"></i>Lihat Riwayat
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Edit Forms -->
                <div class="col-md-8 col-lg-9">
                    <div class="row g-4">

                        <!-- Edit Profil -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title">
                                        <span class="title-icon"><i class="fas fa-user-pen"></i></span>
                                        Edit Informasi Profil
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="row g-3">
                                        <input type="hidden" name="action" value="profil">
                                        <div class="col-md-6">
                                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                                            <input type="text" name="nama" class="form-control"
                                                   value="<?= e($user['nama']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control"
                                                   value="<?= e($user['username']) ?>" disabled>
                                            <div class="form-hint">Username tidak dapat diubah.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Role</label>
                                            <input type="text" class="form-control"
                                                   value="<?= ucfirst($user['role']) ?>" disabled>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-floppy-disk me-2"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Ganti Password -->
                        <div class="col-12">
                            <div class="card card-warning-border">
                                <div class="card-header">
                                    <h6 class="card-title">
                                        <span class="title-icon" style="background:var(--warning-soft);color:var(--warning);">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        Ganti Password
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="row g-3" data-validate>
                                        <input type="hidden" name="action" value="password">
                                        <div class="col-md-4">
                                            <label class="form-label">Password Lama <span class="required">*</span></label>
                                            <input type="password" name="old_password" class="form-control"
                                                   placeholder="Password saat ini" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Password Baru <span class="required">*</span></label>
                                            <input type="password" id="passwordInput" name="new_password"
                                                   class="form-control" placeholder="Min. 6 karakter"
                                                   minlength="6" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                                            <input type="password" id="passwordConfirm" name="confirm_password"
                                                   class="form-control" placeholder="Ulangi password baru" required>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-key me-2"></i>Ubah Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
