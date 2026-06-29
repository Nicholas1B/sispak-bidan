<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    setFlash('ID tidak valid.', 'danger');
    redirect('diagnosa/riwayat.php');
}

// ── Load Riwayat ────────────────────────────────────────────
$rd = $conn->query("
    SELECT rd.*, p.nama_pasien, p.usia, p.usia_kehamilan,
           u.nama AS bidan_nama
    FROM riwayat_diagnosa rd
    JOIN pasien p ON rd.pasien_id = p.id
    JOIN users u ON rd.user_id = u.id
    WHERE rd.id=$id LIMIT 1
")->fetch_assoc();

if (!$rd) {
    setFlash('Data riwayat tidak ditemukan.', 'danger');
    redirect('diagnosa/riwayat.php');
}

// ── Gejala yang Dipilih ──────────────────────────────────────
$gejalaQ = $conn->query("
    SELECT dr.cf_user, g.id AS gejala_id, g.kode, g.nama_gejala
    FROM detail_riwayat dr
    JOIN gejala g ON dr.gejala_id = g.id
    WHERE dr.riwayat_id=$id
    ORDER BY g.kode
");
$gejalaDipilih = [];
while ($g = $gejalaQ->fetch_assoc()) $gejalaDipilih[] = $g;

// ── Hitung Ulang CF Per Kondisi ──────────────────────────────
$kondisiAll  = $conn->query("SELECT * FROM kondisi ORDER BY kode");
$perhitungan = [];

while ($k = $kondisiAll->fetch_assoc()) {
    $steps       = [];
    $cfKombined  = null;
    $gejalaMatch = 0;

    foreach ($gejalaDipilih as $g) {
        $gejala_id = $g['gejala_id'];
        $ruleQ     = $conn->query("
            SELECT cf_pakar FROM rule_cf
            WHERE kondisi_id={$k['id']} AND gejala_id=$gejala_id
        ");
        if (!$ruleQ->num_rows) continue;

        $rule       = $ruleQ->fetch_assoc();
        $cf_pakar   = (float)$rule['cf_pakar'];
        $cf_user    = (float)$g['cf_user'];
        $cf_combine = round($cf_user * $cf_pakar, 4);

        $cfLama = $cfKombined;
        if ($cfKombined === null) {
            $cfKombined = $cf_combine;
            $cfSetelah  = $cf_combine;
        } else {
            $cfSetelah  = round($cfKombined + $cf_combine * (1 - $cfKombined), 4);
            $cfKombined = $cfSetelah;
        }

        $steps[] = [
            'gejala'     => $g,
            'cf_pakar'   => $cf_pakar,
            'cf_user'    => $cf_user,
            'cf_combine' => $cf_combine,
            'cf_lama'    => $cfLama,
            'cf_setelah' => $cfSetelah,
        ];
        $gejalaMatch++;
    }

    if ($cfKombined !== null) {
        $perhitungan[] = [
            'kondisi'  => $k,
            'steps'    => $steps,
            'cf_final' => $cfKombined,
            'persen'   => round($cfKombined * 100, 2),
            'match'    => $gejalaMatch,
        ];
    }
}

// Sort by CF descending
usort($perhitungan, fn($a, $b) => $b['cf_final'] <=> $a['cf_final']);

$medals = ['🥇', '🥈', '🥉'];

$pageTitle  = 'Detail Perhitungan CF';
$activePage = 'riwayat';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-wrapper">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
        <div class="page-content">
            <h1 data-page-title="Detail Perhitungan CF" style="display:none">Detail CF</h1>

            <!-- Back + Title -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="<?= url('diagnosa/hasil.php') ?>?id=<?= $id ?>" class="btn btn-light-muted">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Hasil
                </a>
                <div>
                    <h5 class="fw-bold mb-0">Detail Perhitungan Certainty Factor</h5>
                    <div class="text-muted" style="font-size:12px;">
                        Diagnosa #<?= str_pad($id, 5, '0', STR_PAD_LEFT) ?>
                        &mdash; <?= tglIndo($rd['tanggal']) ?>
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="<?= url('diagnosa/cetak.php') ?>?id=<?= $id ?>"
                       target="_blank" class="btn btn-success btn-sm">
                        <i class="fas fa-print me-1"></i>Cetak
                    </a>
                </div>
            </div>

            <!-- Info Konsultasi -->
            <div class="card card-gradient mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div style="font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:.07em;">Pasien</div>
                            <div class="fw-bold" style="font-size:15px;"><?= e($rd['nama_pasien']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div style="font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:.07em;">Tanggal</div>
                            <div class="fw-bold"><?= tglIndo($rd['tanggal']) ?>, <?= date('H:i', strtotime($rd['tanggal'])) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div style="font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:.07em;">Bidan</div>
                            <div class="fw-bold"><?= e($rd['bidan_nama']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div style="font-size:10px;opacity:.7;text-transform:uppercase;letter-spacing:.07em;">Gejala Dipilih</div>
                            <div class="fw-bold"><?= count($gejalaDipilih) ?> gejala</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rumus CF -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">
                        <span class="title-icon"><i class="fas fa-square-root-variable"></i></span>
                        Rumus Certainty Factor
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background:#eff6ff;border:1px solid #bfdbfe;">
                                <div class="fw-bold mb-2" style="color:#1d4ed8;font-size:12px;">
                                    <i class="fas fa-circle-1 me-1"></i>LANGKAH 1 — CF Kombinasi
                                </div>
                                <code style="font-size:13px;color:#1e293b;">
                                    CF(H,E) = CF<sub>user</sub> × CF<sub>pakar</sub>
                                </code>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <div class="fw-bold mb-2" style="color:#15803d;font-size:12px;">
                                    <i class="fas fa-circle-2 me-1"></i>LANGKAH 2 — CF Gabungan
                                </div>
                                <code style="font-size:13px;color:#1e293b;">
                                    CF<sub>new</sub> = CF<sub>lama</sub> + CF<sub>baru</sub> × (1 − CF<sub>lama</sub>)
                                </code>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded" style="background:#fefce8;border:1px solid #fde68a;">
                                <div class="fw-bold mb-2" style="color:#b45309;font-size:12px;">
                                    <i class="fas fa-circle-3 me-1"></i>LANGKAH 3 — Persentase
                                </div>
                                <code style="font-size:13px;color:#1e293b;">
                                    Persentase = CF<sub>final</sub> × 100%
                                </code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Semua Kondisi -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title">
                        <span class="title-icon"><i class="fas fa-chart-bar"></i></span>
                        Ringkasan Hasil Semua Kondisi
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="60">Rank</th>
                                <th>Kondisi</th>
                                <th width="110">Gejala Cocok</th>
                                <th width="110">CF Final</th>
                                <th width="180">Persentase</th>
                                <th width="100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($perhitungan as $i => $p): ?>
                        <tr style="<?= $i === 0 ? 'background:#f0f8ff;' : '' ?>">
                            <td class="text-center">
                                <span style="font-size:<?= $i < 3 ? '20px' : '13px' ?>;">
                                    <?= $medals[$i] ?? '#' . ($i + 1) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-warning-soft text-warning me-1">
                                    <?= e($p['kondisi']['kode']) ?>
                                </span>
                                <strong><?= e($p['kondisi']['nama_kondisi']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-primary-soft text-primary">
                                    <?= $p['match'] ?> gejala
                                </span>
                            </td>
                            <td>
                                <code class="fw-bold"><?= number_format($p['cf_final'], 4) ?></code>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="cf-bar flex-grow-1" style="max-width:120px;">
                                        <div class="cf-fill <?= $p['persen'] >= 70 ? 'cf-fill-high' : ($p['persen'] >= 40 ? 'cf-fill-mid' : 'cf-fill-low') ?>"
                                             style="width:<?= $p['persen'] ?>%"></div>
                                    </div>
                                    <strong><?= $p['persen'] ?>%</strong>
                                </div>
                            </td>
                            <td>
                                <?php if ($i === 0): ?>
                                <span class="badge bg-success-soft text-success">
                                    <i class="fas fa-circle-check me-1"></i>Terpilih
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary text-white" style="font-size:10px;">
                                    Kandidat
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Per Kondisi -->
            <?php foreach ($perhitungan as $i => $p): ?>
            <div class="card mb-4">
                <div class="card-header" style="<?= $i === 0 ? 'background:linear-gradient(135deg,#eff6ff,#e0f2fe);border-bottom-color:#bfdbfe;' : '' ?>">
                    <h6 class="card-title">
                        <span class="title-icon" style="<?= $i === 0 ? 'background:#dbeafe;color:#1d4ed8;' : '' ?>">
                            <?php if ($i < 3): ?>
                                <span style="font-size:16px;"><?= $medals[$i] ?></span>
                            <?php else: ?>
                                <i class="fas fa-list"></i>
                            <?php endif; ?>
                        </span>
                        [<?= e($p['kondisi']['kode']) ?>] <?= e($p['kondisi']['nama_kondisi']) ?>
                        <?php if ($i === 0): ?>
                        <span class="badge bg-success-soft text-success ms-2" style="font-size:11px;">
                            Hasil Utama
                        </span>
                        <?php endif; ?>
                    </h6>
                    <span class="badge <?= $p['persen'] >= 70 ? 'bg-success' : ($p['persen'] >= 40 ? 'bg-warning' : 'bg-danger') ?> text-white">
                        CF = <?= number_format($p['cf_final'], 4) ?> (<?= $p['persen'] ?>%)
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table" style="font-size:12.5px;">
                        <thead>
                            <tr>
                                <th width="55">Step</th>
                                <th>Gejala</th>
                                <th width="90" class="text-center">CF User</th>
                                <th width="90" class="text-center">CF Pakar</th>
                                <th width="180" class="text-center">CF(H,E) = User × Pakar</th>
                                <th width="100" class="text-center">CF Lama</th>
                                <th width="220" class="text-center">CF Baru (Gabungan)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($p['steps'] as $si => $s): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary-soft text-primary"><?= $si + 1 ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary text-white me-1" style="font-size:10px;">
                                    <?= e($s['gejala']['kode']) ?>
                                </span>
                                <?= e($s['gejala']['nama_gejala']) ?>
                            </td>
                            <td class="text-center">
                                <strong><?= number_format($s['cf_user'], 2) ?></strong>
                            </td>
                            <td class="text-center">
                                <strong><?= number_format($s['cf_pakar'], 2) ?></strong>
                            </td>
                            <td class="text-center">
                                <code style="font-size:11px;background:#f1f5f9;padding:2px 8px;border-radius:4px;">
                                    <?= number_format($s['cf_user'], 2) ?> × <?= number_format($s['cf_pakar'], 2) ?>
                                    = <strong><?= number_format($s['cf_combine'], 4) ?></strong>
                                </code>
                            </td>
                            <td class="text-center text-muted">
                                <?= $s['cf_lama'] !== null
                                    ? number_format($s['cf_lama'], 4)
                                    : '<em>—</em>' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($s['cf_lama'] !== null): ?>
                                <code style="font-size:11px;color:#1e293b;">
                                    <?= number_format($s['cf_lama'], 4) ?> + <?= number_format($s['cf_combine'], 4) ?>
                                    × (1 − <?= number_format($s['cf_lama'], 4) ?>)<br>
                                    = <strong style="color:var(--primary);">
                                        <?= number_format($s['cf_setelah'], 4) ?>
                                    </strong>
                                </code>
                                <?php else: ?>
                                <strong style="color:var(--primary);">
                                    <?= number_format($s['cf_setelah'], 4) ?>
                                </strong>
                                <span class="text-muted" style="font-size:10px;">(gejala pertama)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Row Total -->
                        <tr style="background:#f8fafc;font-weight:700;border-top:2px solid var(--border);">
                            <td colspan="6" class="text-end pe-4" style="font-size:13px;">
                                <i class="fas fa-equals me-1 text-primary"></i>
                                CF Final <?= e($p['kondisi']['nama_kondisi']) ?>:
                            </td>
                            <td class="text-center" style="font-size:16px;color:var(--primary);">
                                <?= number_format($p['cf_final'], 4) ?>
                                <span class="text-muted" style="font-size:12px;">
                                    (<?= $p['persen'] ?>%)
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Bottom Actions -->
            <div class="d-flex gap-3 justify-content-center flex-wrap pb-4">
                <a href="<?= url('diagnosa/hasil.php') ?>?id=<?= $id ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Hasil Diagnosa
                </a>
                <a href="<?= url('diagnosa/cetak.php') ?>?id=<?= $id ?>"
                   target="_blank" class="btn btn-success btn-lg">
                    <i class="fas fa-print me-2"></i>Cetak / Simpan PDF
                </a>
            </div>

        </div>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
