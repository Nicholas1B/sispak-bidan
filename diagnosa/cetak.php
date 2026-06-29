<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo "ID tidak valid.";
    exit;
}

$rd = $conn->query("
    SELECT rd.*, p.nama_pasien, p.usia, p.usia_kehamilan, p.no_hp, p.alamat,
           k.nama_kondisi, k.kode AS k_kode, k.deskripsi,
           u.nama AS bidan_nama,
           s.tindakan, s.rujukan, s.judul AS sol_judul
    FROM riwayat_diagnosa rd
    JOIN pasien p ON rd.pasien_id = p.id
    LEFT JOIN kondisi k ON rd.kondisi_id = k.id
    LEFT JOIN solusi s ON s.kondisi_id = k.id
    JOIN users u ON rd.user_id = u.id
    WHERE rd.id=$id
    LIMIT 1
")->fetch_assoc();

if (!$rd) {
    echo "Data tidak ditemukan.";
    exit;
}

$gejalaDetail = [];
$det = $conn->query("
    SELECT dr.cf_user, g.kode, g.nama_gejala
    FROM detail_riwayat dr
    JOIN gejala g ON dr.gejala_id = g.id
    WHERE dr.riwayat_id=$id
    ORDER BY g.kode
");
while ($d = $det->fetch_assoc()) $gejalaDetail[] = $d;

$pct      = (float)$rd['persentase'];
$tingkat  = $pct >= 70 ? 'TINGGI' : ($pct >= 40 ? 'SEDANG' : 'RENDAH');
$cfLabels = [
    '0.2' => 'Tidak Yakin',
    '0.4' => 'Sedikit Yakin',
    '0.6' => 'Cukup Yakin',
    '0.8' => 'Yakin',
    '1.0' => 'Sangat Yakin',
];

$noRiwayat = str_pad($id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Hasil Diagnosa — <?= htmlspecialchars($rd['nama_pasien']) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ─── Reset ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #f8fafc;
            line-height: 1.5;
        }

        /* ─── Wrapper ─── */
        .print-container {
            max-width: 820px;
            margin: 24px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* ─── Action Bar (hidden on print) ─── */
        .action-bar {
            padding: 14px 24px;
            background: #f1f5f9;
            display: flex;
            gap: 10px;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .btn-print {
            padding: 8px 20px;
            background: linear-gradient(135deg,#0e7fc0,#0e9fd8);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-close-win {
            padding: 8px 20px;
            background: transparent;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        /* ─── Document ─── */
        .doc { padding: 32px; }

        /* Header */
        .doc-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding-bottom: 18px;
            margin-bottom: 20px;
            border-bottom: 3px solid #0e7fc0;
        }
        .doc-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg,#0e7fc0,#06b6d4);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .doc-logo svg { width: 28px; height: 28px; fill: white; }
        .doc-title h1 { font-size: 18px; font-weight: 800; color: #0e7fc0; }
        .doc-title p  { font-size: 11px; color: #64748b; margin-top: 2px; }
        .doc-meta {
            margin-left: auto;
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }
        .doc-meta strong { display: block; font-size: 13px; color: #1e293b; font-weight: 700; }

        /* Result Box */
        .result-box {
            background: linear-gradient(135deg,#0e7fc0 0%,#06b6d4 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 10px;
            margin-bottom: 18px;
        }
        .result-box .label { font-size: 10px; opacity: .75; letter-spacing: .07em; text-transform: uppercase; margin-bottom: 4px; }
        .result-box h2 { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
        .cf-row { display: flex; align-items: center; gap: 16px; margin-top: 12px; }
        .cf-num { font-size: 38px; font-weight: 900; line-height: 1; }
        .cf-unit { font-size: 18px; font-weight: 700; opacity: .8; }
        .cf-detail { font-size: 11px; opacity: .7; margin-top: 2px; }
        .cf-bar-wrap { flex: 1; background: rgba(255,255,255,.25); border-radius: 100px; height: 10px; }
        .cf-bar-fill { height: 100%; border-radius: 100px; background: white; }
        .cf-level { text-align: right; }
        .cf-level-val { font-size: 16px; font-weight: 800; }
        .cf-level-lbl { font-size: 10px; opacity: .7; }

        /* Sections */
        .section { margin-bottom: 14px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .section-hd {
            background: #f8fafc;
            padding: 9px 14px;
            font-weight: 700;
            font-size: 11.5px;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-hd .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #0e7fc0;
            flex-shrink: 0;
        }
        .section-bd { padding: 14px; }

        /* Info Grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; }
        .info-row { display: flex; gap: 8px; align-items: baseline; }
        .info-lbl { color: #64748b; min-width: 110px; flex-shrink: 0; font-size: 11px; }
        .info-val { font-weight: 600; font-size: 12px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        thead th {
            background: #f1f5f9;
            padding: 7px 10px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 100px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-orange { background: #fef3c7; color: #92400e; }

        /* Alert boxes */
        .alert-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 11.5px;
            margin-top: 14px;
            color: #78350f;
        }
        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 11.5px;
            margin-bottom: 12px;
            color: #7f1d1d;
        }

        /* CF Progress in gejala table */
        .mini-bar {
            background: #e2e8f0;
            border-radius: 100px;
            height: 5px;
            width: 80px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
        }
        .mini-fill {
            height: 100%;
            border-radius: 100px;
        }
        .mini-high { background: #10b981; }
        .mini-mid  { background: #f59e0b; }
        .mini-low  { background: #ef4444; }

        /* TTD & Footer */
        .doc-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 11px;
            color: #94a3b8;
        }
        .ttd-block { text-align: center; }
        .ttd-line {
            width: 160px;
            border-top: 1px solid #475569;
            margin: 52px auto 6px;
        }
        .ttd-name { font-size: 12px; font-weight: 700; color: #1e293b; }
        .ttd-role { font-size: 10px; color: #64748b; }

        /* ─── Print styles ─── */
        @media print {
            body { background: white; font-size: 11px; }
            .action-bar { display: none !important; }
            .print-container { margin: 0; border-radius: 0; box-shadow: none; }
            .result-box {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .section-hd {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .doc { padding: 16px; }
        }
    </style>
</head>
<body>

<div class="print-container">

    <!-- Action Bar -->
    <div class="action-bar">
        <button class="btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zm4 11v2H5v-2h4zm-2-6a1 1 0 0 1 1 1v1H7v-1a1 1 0 0 1 1-1z"/>
            </svg>
            Cetak / Simpan PDF
        </button>
        <button class="btn-close-win" onclick="window.close()">✕ Tutup</button>
        <span style="font-size:11px;color:#64748b;margin-left:8px;">
            Gunakan browser untuk simpan sebagai PDF. Pilih "Save as PDF" pada dialog cetak.
        </span>
    </div>

    <!-- Document Body -->
    <div class="doc">

        <!-- Header -->
        <div class="doc-header">
            <div class="doc-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/></svg>
            </div>
            <div class="doc-title">
                <h1>SisPak Bidan</h1>
                <p>Sistem Pakar Diagnosa Kebidanan</p>
                <p style="color:#0e7fc0;font-weight:600;font-size:11px;">Metode Certainty Factor</p>
            </div>
            <div class="doc-meta">
                <strong>HASIL DIAGNOSA</strong>
                No. Riwayat: <strong>#<?= $noRiwayat ?></strong><br>
                Tanggal: <?= date('d F Y, H:i', strtotime($rd['tanggal'])) ?><br>
                Bidan: <?= htmlspecialchars($rd['bidan_nama']) ?>
            </div>
        </div>

        <!-- Hasil Utama -->
        <div class="result-box">
            <div class="label">Kondisi Terdiagnosa</div>
            <h2><?= $rd['nama_kondisi'] ? htmlspecialchars($rd['nama_kondisi']) : 'Tidak Terdeteksi Kondisi Spesifik' ?></h2>
            <?php if ($rd['k_kode']): ?>
            <span class="badge badge-blue" style="margin-top:4px;font-size:11px;"><?= $rd['k_kode'] ?></span>
            <?php endif; ?>
            <div class="cf-row">
                <div>
                    <div class="cf-num"><?= number_format($pct, 1) ?><span class="cf-unit">%</span></div>
                    <div class="cf-detail">CF = <?= number_format($rd['cf_hasil'], 4) ?></div>
                </div>
                <div class="cf-bar-wrap">
                    <div class="cf-bar-fill" style="width:<?= min($pct, 100) ?>%"></div>
                </div>
                <div class="cf-level">
                    <div class="cf-level-val"><?= $tingkat ?></div>
                    <div class="cf-level-lbl">Tingkat Keyakinan</div>
                </div>
            </div>
        </div>

        <!-- Data Pasien -->
        <div class="section">
            <div class="section-hd">
                <div class="dot" style="background:#3b82f6;"></div>
                DATA PASIEN
            </div>
            <div class="section-bd">
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-lbl">Nama Pasien</span>
                        <span class="info-val"><?= htmlspecialchars($rd['nama_pasien']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Tanggal Periksa</span>
                        <span class="info-val"><?= date('d F Y, H:i', strtotime($rd['tanggal'])) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Usia</span>
                        <span class="info-val"><?= $rd['usia'] ? $rd['usia'] . ' tahun' : '—' ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Usia Kehamilan</span>
                        <span class="info-val"><?= $rd['usia_kehamilan'] ? $rd['usia_kehamilan'] . ' minggu' : '—' ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">No. HP</span>
                        <span class="info-val"><?= htmlspecialchars($rd['no_hp'] ?: '—') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-lbl">Bidan Pemeriksa</span>
                        <span class="info-val"><?= htmlspecialchars($rd['bidan_nama']) ?></span>
                    </div>
                    <?php if ($rd['alamat']): ?>
                    <div class="info-row" style="grid-column: 1/-1;">
                        <span class="info-lbl">Alamat</span>
                        <span class="info-val"><?= htmlspecialchars($rd['alamat']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Gejala -->
        <div class="section">
            <div class="section-hd">
                <div class="dot" style="background:#8b5cf6;"></div>
                GEJALA YANG DITEMUKAN (<?= count($gejalaDetail) ?> gejala)
            </div>
            <table>
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th width="50">Kode</th>
                        <th>Nama Gejala</th>
                        <th width="70" style="text-align:center;">CF User</th>
                        <th width="100">Keyakinan</th>
                        <th width="80" style="text-align:center;">Progress</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($gejalaDetail as $g):
                    $cfU   = number_format($g['cf_user'], 1);
                    $label = $cfLabels[$cfU] ?? 'Cukup Yakin';
                    $pctU  = (float)$g['cf_user'] * 100;
                    $cls   = $pctU >= 60 ? 'mini-high' : ($pctU >= 40 ? 'mini-mid' : 'mini-low');
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($g['kode']) ?></span></td>
                    <td><?= htmlspecialchars($g['nama_gejala']) ?></td>
                    <td style="text-align:center;font-weight:700;"><?= number_format($g['cf_user'], 2) ?></td>
                    <td><?= $label ?></td>
                    <td style="text-align:center;">
                        <div class="mini-bar">
                            <div class="mini-fill <?= $cls ?>" style="width:<?= $pctU ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tentang Kondisi -->
        <?php if ($rd['nama_kondisi'] && $rd['deskripsi']): ?>
        <div class="section">
            <div class="section-hd">
                <div class="dot" style="background:#0e7fc0;"></div>
                TENTANG <?= strtoupper(htmlspecialchars($rd['nama_kondisi'])) ?>
            </div>
            <div class="section-bd">
                <p style="line-height:1.8;color:#334155;"><?= htmlspecialchars($rd['deskripsi']) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rekomendasi -->
        <?php if ($rd['tindakan']): ?>
        <div class="section">
            <div class="section-hd">
                <div class="dot" style="background:#10b981;"></div>
                REKOMENDASI PENANGANAN
                <?php if ($rd['rujukan'] === 'Ya'): ?>
                <span class="badge badge-red" style="margin-left:8px;">⚠ Perlu Rujukan</span>
                <?php else: ?>
                <span class="badge badge-green" style="margin-left:8px;">✓ Mandiri</span>
                <?php endif; ?>
            </div>
            <div class="section-bd">
                <?php if ($rd['rujukan'] === 'Ya'): ?>
                <div class="alert-danger">
                    <strong>⚠ PERHATIAN:</strong> Kondisi ini memerlukan rujukan ke dokter spesialis kandungan
                    atau fasilitas kesehatan yang lebih lengkap. Segera koordinasikan dengan dokter SpOG.
                </div>
                <?php endif; ?>
                <strong><?= htmlspecialchars($rd['sol_judul']) ?></strong>
                <p style="margin-top:8px;line-height:1.8;color:#334155;">
                    <?= nl2br(htmlspecialchars($rd['tindakan'])) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Disclaimer -->
        <div class="alert-warning">
            <strong>⚠ Catatan Penting:</strong> Hasil diagnosa sistem pakar ini bersifat sebagai alat bantu
            pengambilan keputusan berbasis metode Certainty Factor dan <u>tidak menggantikan</u> pemeriksaan
            klinis langsung oleh tenaga medis yang kompeten. Selalu lakukan anamnesis dan pemeriksaan fisik
            lengkap sebelum menegakkan diagnosis akhir.
        </div>

        <!-- Footer -->
        <div class="doc-footer">
            <div>
                <div style="font-weight:600;color:#475569;">SisPak Bidan v<?= APP_VERSION ?></div>
                <div>Sistem Pakar Diagnosa Kebidanan — Metode Certainty Factor</div>
                <div>Dicetak: <?= date('d/m/Y H:i') ?> WIB</div>
            </div>
            <div class="ttd-block">
                <div style="font-size:11px;color:#475569;">Mengetahui, Bidan Pemeriksa</div>
                <div class="ttd-line"></div>
                <div class="ttd-name"><?= htmlspecialchars($rd['bidan_nama']) ?></div>
                <div class="ttd-role">Bidan Pemeriksa</div>
            </div>
        </div>

    </div><!-- /.doc -->
</div><!-- /.print-container -->

</body>
</html>
