<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('diagnosa/konsultasi.php');
}

// ── Ambil Data POST ──────────────────────────────────────────
$nama_pasien     = sanitize($conn, $_POST['nama_pasien'] ?? '');
$usia            = (int)($_POST['usia'] ?? 0);
$usia_kehamilan  = (int)($_POST['usia_kehamilan'] ?? 0);
$no_hp           = sanitize($conn, $_POST['no_hp'] ?? '');
$alamat          = sanitize($conn, $_POST['alamat'] ?? '');
$gejala_dipilih  = $_POST['gejala'] ?? [];
$cf_user_input   = $_POST['cf_user'] ?? [];

if (empty($gejala_dipilih) || !$nama_pasien) {
    setFlash('Data tidak lengkap. Silakan ulangi konsultasi.', 'danger');
    redirect('diagnosa/konsultasi.php');
}

// ── Simpan Data Pasien ───────────────────────────────────────
$conn->query("
    INSERT INTO pasien (nama_pasien, usia, usia_kehamilan, no_hp, alamat)
    VALUES ('$nama_pasien', $usia, $usia_kehamilan, '$no_hp', '$alamat')
");
$pasien_id = $conn->insert_id;

// ── ALGORITMA CERTAINTY FACTOR ───────────────────────────────
$kondisiAll = $conn->query("SELECT * FROM kondisi ORDER BY kode");
$hasilCF    = [];

while ($k = $kondisiAll->fetch_assoc()) {
    $kondisi_id = $k['id'];
    $cfKombined = null;

    foreach ($gejala_dipilih as $gejala_id) {
        $gejala_id = (int)$gejala_id;

        // Ambil CF pakar untuk kombinasi ini
        $ruleQ = $conn->query("
            SELECT cf_pakar FROM rule_cf
            WHERE kondisi_id=$kondisi_id AND gejala_id=$gejala_id
        ");

        if ($ruleQ->num_rows === 0) continue;

        $rule     = $ruleQ->fetch_assoc();
        $cf_pakar = (float)$rule['cf_pakar'];
        $cf_user  = (float)($cf_user_input[$gejala_id] ?? 0.6);

        // CF(H,E) = CF_user × CF_pakar
        $cf_combine = $cf_user * $cf_pakar;

        // CF_total = CF_lama + CF_baru × (1 - CF_lama)
        if ($cfKombined === null) {
            $cfKombined = $cf_combine;
        } else {
            $cfKombined = $cfKombined + $cf_combine * (1 - $cfKombined);
        }
    }

    if ($cfKombined !== null) {
        $hasilCF[] = [
            'kondisi' => $k,
            'cf'      => $cfKombined,
            'persen'  => round($cfKombined * 100, 2),
        ];
    }
}

// Sort by CF descending
usort($hasilCF, fn($a, $b) => $b['cf'] <=> $a['cf']);

// ── Simpan Hasil Diagnosa Utama ───────────────────────────────
$top               = $hasilCF[0] ?? null;
$kondisi_id_simpan = $top ? $top['kondisi']['id'] : null;
$cf_simpan         = $top ? $top['cf'] : 0;
$pct_simpan        = $top ? $top['persen'] : 0;
$ket_simpan        = $top
    ? "Diagnosa: {$top['kondisi']['nama_kondisi']} dengan CF {$top['persen']}%"
    : "Tidak terdiagnosa";

$user_id = $_SESSION['user_id'];

$conn->query("
    INSERT INTO riwayat_diagnosa (user_id, pasien_id, kondisi_id, cf_hasil, persentase, keterangan)
    VALUES ($user_id, $pasien_id, " . ($kondisi_id_simpan ?: 'NULL') . ", $cf_simpan, $pct_simpan, '$ket_simpan')
");
$riwayat_id = $conn->insert_id;

// Simpan detail gejala
foreach ($gejala_dipilih as $gejala_id) {
    $gejala_id = (int)$gejala_id;
    $cf_u      = (float)($cf_user_input[$gejala_id] ?? 0.6);
    $conn->query("INSERT INTO detail_riwayat (riwayat_id, gejala_id, cf_user) VALUES ($riwayat_id, $gejala_id, $cf_u)");
}

// Simpan ke session untuk halaman hasil
$_SESSION['hasil_diagnosa'] = [
    'riwayat_id'     => $riwayat_id,
    'pasien_id'      => $pasien_id,
    'nama_pasien'    => $nama_pasien,
    'usia_kehamilan' => $usia_kehamilan,
    'hasil'          => $hasilCF,
    'gejala_dipilih' => $gejala_dipilih,
    'cf_user_input'  => $cf_user_input,
    'tanggal'        => date('Y-m-d H:i:s'),
];

redirect('diagnosa/hasil.php');
