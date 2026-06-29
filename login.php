<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) redirect('index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                redirect('index.php');
            } else {
                $error = 'Password salah. Silakan coba lagi.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
    } else {
        $error = 'Username dan password wajib diisi.';
    }
}

$pageTitle = 'Login';
$bodyClass = 'auth-page';
$extraCss  = '<link rel="stylesheet" href="' . asset('css/style.css') . '">';
$extraJs   = '<script src="' . asset('js/validation.js') . '"></script>';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <i class="fas fa-heart-pulse"></i>
            </div>
            <h1 class="auth-logo-title"><?= APP_NAME ?></h1>
            <p class="auth-logo-sub"><?= APP_SUBTITLE ?></p>
            <p class="auth-logo-method">
                <i class="fas fa-brain me-1"></i><?= APP_METHOD ?> Method
            </p>
        </div>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert alert-danger mb-3">
            <i class="fas fa-times-circle"></i>
            <span><?= e($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" data-validate>
            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-user me-1 text-muted"></i>Username
                </label>
                <input type="text"
                       name="username"
                       class="form-control"
                       placeholder="Masukkan username"
                       value="<?= e($_POST['username'] ?? '') ?>"
                       autocomplete="username"
                       required
                       autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="fas fa-lock me-1 text-muted"></i>Password
                </label>
                <div class="input-group">
                    <input type="password"
                           id="passwordInput"
                           name="password"
                           class="form-control"
                           placeholder="Masukkan password"
                           autocomplete="current-password"
                           required>
                    <button type="button"
                            class="btn btn-outline-secondary"
                            data-toggle-password="passwordInput"
                            style="border:1.5px solid var(--border);border-left:none;">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 justify-content-center">
                <i class="fas fa-right-to-bracket me-2"></i>Masuk ke Sistem
            </button>
        </form>

        <!-- Demo credentials -->
        <div class="demo-credentials mt-4">
            <strong><i class="fas fa-circle-info me-1"></i>Akun Demo:</strong><br>
            Admin &nbsp;: <code>admin</code> / <code>password</code><br>
            Bidan &nbsp;: <code>bidan1</code> / <code>password</code>
        </div>

        <!-- Footer link -->
        <div class="auth-footer">
            <a href="<?= url('/') ?>">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
            </a>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/validation.js') ?>"></script>
</body>
</html>
