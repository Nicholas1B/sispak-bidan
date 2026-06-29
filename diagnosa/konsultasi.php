<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$gejalaList = $conn->query("SELECT * FROM gejala ORDER BY kode ASC");

$pageTitle  = 'Konsultasi Diagnosa';
$activePage = 'konsultasi';
$extraJs    = '
<script>const BASE_URL = "' . BASE_URL . '";</script>
<script src="' . asset('js/diagnosa.js') . '"></script>';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Konsultasi Diagnosa" style="display:none">Konsultasi</h1>
            <?php showFlash(); ?>

            <div style="max-width:940px;margin:0 auto;">

                <!-- Header Banner -->
                <div class="card card-gradient mb-4">
                    <div class="card-body p-4 d-flex align-items-center gap-4">
                        <div style="font-size:48px;opacity:.9;flex-shrink:0;">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">Konsultasi Diagnosa Pasien</h4>
                            <p class="mb-0" style="opacity:.85;font-size:13px;">
                                Pilih gejala yang dialami pasien, lalu tentukan tingkat keyakinan masing-masing gejala.
                                Sistem akan menghitung Certainty Factor secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="<?= url('diagnosa/proses_cf.php') ?>" id="formDiagnosa">

                    <!-- Data Pasien -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-user"></i></span>
                                Data Pasien
                            </h6>
                        </div>
                        <div class="card-body">

                            <!-- Autocomplete Pasien Lama -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-magnifying-glass me-1 text-muted"></i>
                                    Cari Pasien Lama (opsional)
                                </label>
                                <div class="position-relative">
                                    <input type="text" id="searchPasien" class="form-control"
                                           placeholder="Ketik nama pasien yang pernah diperiksa..."
                                           autocomplete="off">
                                    <div id="autocompleteList" class="autocomplete-dropdown"></div>
                                </div>
                                <div class="form-hint">
                                    Pilih dari daftar untuk mengisi data otomatis, atau isi manual untuk pasien baru.
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Pasien <span class="required">*</span></label>
                                    <input type="text" name="nama_pasien" id="inp_nama" class="form-control"
                                           placeholder="Nama lengkap pasien" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Usia (tahun)</label>
                                    <input type="number" name="usia" id="inp_usia" class="form-control"
                                           placeholder="Contoh: 28" min="10" max="60">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Usia Kehamilan (minggu)</label>
                                    <input type="number" name="usia_kehamilan" id="inp_usia_ham"
                                           class="form-control" placeholder="Contoh: 32" min="1" max="45">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. HP</label>
                                    <input type="text" name="no_hp" id="inp_hp" class="form-control"
                                           placeholder="08xxxxxxxxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" name="alamat" id="inp_alamat" class="form-control"
                                           placeholder="Alamat pasien">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Gejala -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title">
                                <span class="title-icon"><i class="fas fa-virus"></i></span>
                                Pilih Gejala yang Dialami
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <span id="countSelected" class="badge bg-primary-soft text-primary">0</span>
                                <small class="text-muted">gejala dipilih</small>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Petunjuk -->
                            <div class="alert alert-info alert-static mb-4">
                                <i class="fas fa-circle-info"></i>
                                <span>
                                    <strong>Petunjuk:</strong> Centang gejala yang dialami pasien, lalu pilih
                                    tingkat keyakinan Anda terhadap gejala tersebut.
                                </span>
                            </div>

                            <!-- Gejala Grid -->
                            <div class="gejala-list">
                                <?php while ($g = $gejalaList->fetch_assoc()): ?>
                                <div class="gejala-item" id="item-<?= $g['id'] ?>">
                                    <input type="checkbox"
                                           name="gejala[]"
                                           value="<?= $g['id'] ?>"
                                           id="g<?= $g['id'] ?>">
                                    <div style="flex:1;">
                                        <label for="g<?= $g['id'] ?>" style="cursor:pointer;">
                                            <span class="badge bg-primary-soft text-primary me-1"
                                                  style="font-size:10px;"><?= e($g['kode']) ?></span>
                                            <strong style="font-size:13px;"><?= e($g['nama_gejala']) ?></strong>
                                        </label>
                                        <?php if ($g['keterangan']): ?>
                                        <div class="text-muted mt-1" style="font-size:11px;">
                                            <?= e($g['keterangan']) ?>
                                        </div>
                                        <?php endif; ?>

                                        <!-- CF Input -->
                                        <div class="cf-slider-wrap" id="cf-wrap-<?= $g['id'] ?>">
                                            <div class="cf-label">Tingkat Keyakinan:</div>
                                            <div class="cf-options">
                                                <button type="button" class="cf-btn" data-id="<?= $g['id'] ?>" data-val="0.2">Tidak Yakin</button>
                                                <button type="button" class="cf-btn" data-id="<?= $g['id'] ?>" data-val="0.4">Sedikit Yakin</button>
                                                <button type="button" class="cf-btn active" data-id="<?= $g['id'] ?>" data-val="0.6">Cukup Yakin</button>
                                                <button type="button" class="cf-btn" data-id="<?= $g['id'] ?>" data-val="0.8">Yakin</button>
                                                <button type="button" class="cf-btn" data-id="<?= $g['id'] ?>" data-val="1.0">Sangat Yakin</button>
                                            </div>
                                            <input type="hidden" name="cf_user[<?= $g['id'] ?>]"
                                                   id="cf-val-<?= $g['id'] ?>" value="0.6">
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex gap-3 justify-content-end">
                                <a href="<?= url('index.php') ?>" class="btn btn-light-muted btn-lg">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="button" id="btnSubmitDiagnosa" class="btn btn-primary btn-lg">
                                    <i class="fas fa-brain me-2"></i>Proses Diagnosa
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
