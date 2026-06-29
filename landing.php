<?php
session_start();
require_once 'koneksi.php';
// If already logged in, redirect to dashboard
if (isLoggedIn()) redirect(BASE_URL . '/index.php');
$BASE = BASE_URL;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SisPak Bidan — Sistem Pakar Diagnosa Kebidanan berbasis metode Certainty Factor. Bantu tenaga kebidanan melakukan diagnosa kondisi ibu hamil secara cepat dan akurat.">
  <meta name="theme-color" content="#0d1b2e">
  <title>SisPak Bidan — Sistem Pakar Diagnosa Kebidanan</title>
  <link rel="stylesheet" href="<?= $BASE ?>/assets/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏥</text></svg>">
</head>
<body class="landing-body">

<!-- ═══════════════════════════════════════════════════════
     NAVIGATION
═══════════════════════════════════════════════════════ -->
<nav class="landing-nav" id="landingNav">
  <div class="nav-container">
    <a href="#" class="nav-logo">
      <div class="logo-icon-wrap">🏥</div>
      <div class="nav-logo-text">
        <h2>SisPak Bidan</h2>
        <p>Sistem Pakar Diagnosa</p>
      </div>
    </a>

    <div class="nav-links">
      <a href="#fitur" class="nav-link">Fitur</a>
      <a href="#cara-kerja" class="nav-link">Cara Kerja</a>
      <a href="#tentang" class="nav-link">Tentang</a>
      <a href="<?= $BASE ?>/login.php" class="nav-link">Masuk</a>
      <a href="<?= $BASE ?>/login.php" class="nav-cta">Mulai Sekarang →</a>
    </div>

    <button class="nav-mobile-toggle" id="navMobileToggle" aria-label="Buka menu">☰</button>
  </div>
</nav>

<!-- Mobile nav menu -->
<div id="mobileMenu" class="mobile-menu-overlay">
  <button id="closeMobileMenu" class="mobile-menu-close" aria-label="Tutup menu">✕</button>
  <a href="#fitur"       class="nav-link mobile-nav-link" onclick="closeMobile()">Fitur</a>
  <a href="#cara-kerja"  class="nav-link mobile-nav-link" onclick="closeMobile()">Cara Kerja</a>
  <a href="#tentang"     class="nav-link mobile-nav-link" onclick="closeMobile()">Tentang</a>
  <a href="<?= $BASE ?>/login.php" class="hero-btn-primary" style="margin-top:8px;">Masuk ke Sistem →</a>
</div>

<!-- ═══════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-glow-1"></div>
  <div class="hero-glow-2"></div>
  <div class="hero-particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
  </div>

  <div class="hero-container">
    <div class="hero-content">
      <div class="hero-tag" data-reveal>
        <span class="dot"></span>
        Metode Certainty Factor · Berbasis Web
      </div>

      <h1 class="hero-title" data-reveal data-delay="80">
        Diagnosa Kebidanan<br>
        yang <em>Cepat</em> &<br>
        <em>Akurat</em>
      </h1>

      <p class="hero-subtitle" data-reveal data-delay="160">
        SisPak Bidan membantu tenaga kebidanan melakukan diagnosa kondisi ibu hamil
        secara sistematis menggunakan kecerdasan buatan berbasis metode Certainty Factor.
      </p>

      <div class="hero-actions" data-reveal data-delay="240">
        <a href="<?= $BASE ?>/login.php" class="hero-btn-primary">
          🔐 Masuk ke Sistem
        </a>
        <a href="#cara-kerja" class="hero-btn-secondary">
          ▶ Lihat Cara Kerja
        </a>
      </div>

      <div class="hero-stats" data-reveal data-delay="320">
        <div class="hero-stat-item">
          <div class="hero-stat-num"><span id="ctrGejala">0</span>+</div>
          <div class="hero-stat-label">Gejala Terdeteksi</div>
        </div>
        <div class="hero-stat-item">
          <div class="hero-stat-num"><span id="ctrKondisi">0</span></div>
          <div class="hero-stat-label">Kondisi Kebidanan</div>
        </div>
        <div class="hero-stat-item">
          <div class="hero-stat-num"><span id="ctrRule">0</span>+</div>
          <div class="hero-stat-label">Rule CF</div>
        </div>
      </div>
    </div>

    <!-- Hero visual mockup -->
    <div class="hero-visual" data-reveal data-delay="180">
      <div class="hero-card-mockup">
        <div class="mockup-header">
          <div class="mockup-dot red"></div>
          <div class="mockup-dot yellow"></div>
          <div class="mockup-dot green"></div>
          <span class="mockup-title">Hasil Diagnosa</span>
        </div>

        <div class="mockup-cf-result">
          <div class="mockup-cf-label">Tingkat Keyakinan</div>
          <div class="mockup-cf-value">82%</div>
          <div class="mockup-cf-sub">Certainty Factor</div>
        </div>

        <div class="mockup-bar">
          <div class="mockup-bar-fill"></div>
        </div>

        <div class="mockup-kondisi">Preeklamsia Ringan</div>
        <div style="font-size:11px;color:rgba(255,255,255,0.45);margin-top:3px;">Gejala yang terdeteksi:</div>

        <div class="mockup-gejala-list">
          <div class="mockup-gejala-item">
            <div class="mockup-check">✓</div>
            Tekanan darah ≥ 140/90 mmHg
          </div>
          <div class="mockup-gejala-item">
            <div class="mockup-check">✓</div>
            Edema pada kaki dan pergelangan
          </div>
          <div class="mockup-gejala-item">
            <div class="mockup-check">✓</div>
            Proteinuria positif (+1)
          </div>
        </div>
      </div>

      <div class="hero-badge">
        <div class="hero-badge-icon">✅</div>
        <div class="hero-badge-text">
          <strong>Diagnosa Selesai</strong>
          <span>Waktu: 2 menit</span>
        </div>
      </div>

      <div class="hero-badge-2">
        <strong>CF</strong>
        Metode
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FITUR
═══════════════════════════════════════════════════════ -->
<section class="landing-section dark-bg" id="fitur">
  <div class="section-container">
    <div data-reveal>
      <span class="section-tag">✦ Fitur Unggulan</span>
      <h2 class="section-title">Semua yang Anda Butuhkan<br>dalam Satu Platform</h2>
      <p class="section-subtitle">Dirancang khusus untuk kebutuhan tenaga kebidanan — dari konsultasi hingga laporan.</p>
    </div>

    <div class="features-grid">
      <div class="feature-card" data-reveal data-delay="0">
        <div class="feature-icon">🔍</div>
        <div class="feature-title">Diagnosa Cerdas</div>
        <div class="feature-desc">Sistem menghitung kepastian diagnosa menggunakan metode Certainty Factor berdasarkan gejala yang dipilih bidan secara interaktif.</div>
      </div>

      <div class="feature-card" data-reveal data-delay="80">
        <div class="feature-icon teal-icon">📊</div>
        <div class="feature-title">Analisis Persentase</div>
        <div class="feature-desc">Setiap diagnosa menghasilkan persentase keyakinan yang transparan, membantu bidan memahami dasar keputusan medis.</div>
      </div>

      <div class="feature-card" data-reveal data-delay="160">
        <div class="feature-icon rose-icon">👤</div>
        <div class="feature-title">Manajemen Pasien</div>
        <div class="feature-desc">Simpan data pasien lengkap beserta riwayat pemeriksaan. Cari pasien lama dengan fitur autocomplete yang cepat.</div>
      </div>

      <div class="feature-card" data-reveal data-delay="0">
        <div class="feature-icon green-icon">📋</div>
        <div class="feature-title">Riwayat Diagnosa</div>
        <div class="feature-desc">Rekam jejak lengkap semua diagnosa. Filter berdasarkan tanggal, kondisi, atau nama pasien dengan mudah.</div>
      </div>

      <div class="feature-card" data-reveal data-delay="80">
        <div class="feature-icon purple-icon">📑</div>
        <div class="feature-title">Laporan & Cetak</div>
        <div class="feature-desc">Generate laporan diagnosa dalam format siap cetak, lengkap dengan detail CF, solusi, dan rekomendasi penanganan.</div>
      </div>

      <div class="feature-card" data-reveal data-delay="160">
        <div class="feature-icon orange-icon">⚙️</div>
        <div class="feature-title">Basis Pengetahuan</div>
        <div class="feature-desc">Admin dapat mengelola gejala, kondisi, solusi, dan rule CF secara dinamis tanpa perlu mengubah kode program.</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     CARA KERJA
═══════════════════════════════════════════════════════ -->
<section class="landing-section" id="cara-kerja">
  <div class="section-container">
    <div style="text-align:center;" data-reveal>
      <span class="section-tag">✦ Cara Kerja</span>
      <h2 class="section-title">Diagnosa dalam 4 Langkah</h2>
      <p class="section-subtitle" style="margin:0 auto;">Proses yang sederhana, hasil yang dapat diandalkan.</p>
    </div>

    <div class="steps-grid">
      <div class="step-card" data-reveal data-delay="0">
        <div class="step-num">1</div>
        <div class="step-title">Input Data Pasien</div>
        <div class="step-desc">Masukkan identitas pasien: nama, usia, usia kehamilan, dan data pendukung lainnya.</div>
      </div>
      <div class="step-card" data-reveal data-delay="100">
        <div class="step-num">2</div>
        <div class="step-title">Pilih Gejala</div>
        <div class="step-desc">Centang gejala yang dialami pasien dan tentukan tingkat keyakinan tiap gejala.</div>
      </div>
      <div class="step-card" data-reveal data-delay="200">
        <div class="step-num">3</div>
        <div class="step-title">Proses CF</div>
        <div class="step-desc">Sistem menghitung nilai Certainty Factor dari kombinasi gejala menggunakan rule base.</div>
      </div>
      <div class="step-card" data-reveal data-delay="300">
        <div class="step-num">4</div>
        <div class="step-title">Hasil & Solusi</div>
        <div class="step-desc">Tampilkan kondisi terdiagnosa, persentase keyakinan, dan rekomendasi penanganan.</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     TENTANG / CF METHOD
═══════════════════════════════════════════════════════ -->
<section class="landing-section dark-bg" id="tentang">
  <div class="section-container">
    <div class="tentang-grid">
      <div data-reveal>
        <span class="section-tag">✦ Tentang Metode</span>
        <h2 class="section-title" style="font-size:34px;">Mengapa<br><em style="font-style:italic;color:#60a5fa;">Certainty Factor?</em></h2>
        <p style="color:rgba(255,255,255,0.55);font-size:14.5px;line-height:1.8;margin-bottom:20px;">
          Certainty Factor (CF) adalah metode yang umum digunakan dalam sistem pakar untuk merepresentasikan
          tingkat kepercayaan seorang ahli terhadap suatu fakta atau aturan.
        </p>
        <p style="color:rgba(255,255,255,0.55);font-size:14.5px;line-height:1.8;">
          Metode ini sangat cocok untuk domain medis karena mampu menggabungkan
          ketidakpastian dari berbagai gejala klinis menjadi satu nilai keyakinan yang terukur.
        </p>
        <div style="margin-top:28px;display:flex;flex-direction:column;gap:12px;">
          <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:rgba(255,255,255,0.7);">
            <span class="tentang-check">✓</span>
            Mengakomodasi ketidakpastian diagnosis
          </div>
          <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:rgba(255,255,255,0.7);">
            <span class="tentang-check">✓</span>
            Menggabungkan keyakinan bidan dengan basis pengetahuan
          </div>
          <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:rgba(255,255,255,0.7);">
            <span class="tentang-check">✓</span>
            Hasil transparan dan dapat dipertanggungjawabkan
          </div>
        </div>
      </div>

      <div data-reveal data-delay="120">
        <div class="cf-formula-box">
          <div style="font-size:12px;color:rgba(255,255,255,0.35);text-transform:uppercase;letter-spacing:.1em;margin-bottom:20px;">Formula CF Kombinasi</div>

          <div class="cf-formula-code">
            CF<sub>combine</sub> = CF₁ + CF₂ × (1 − CF₁)
          </div>

          <div style="display:flex;flex-direction:column;gap:10px;">
            <div class="cf-formula-row">
              <span style="color:rgba(255,255,255,0.45);">MB (Measure of Belief)</span>
              <strong style="color:#60a5fa;">0.0 – 1.0</strong>
            </div>
            <div class="cf-formula-row">
              <span style="color:rgba(255,255,255,0.45);">MD (Measure of Disbelief)</span>
              <strong style="color:#60a5fa;">0.0 – 1.0</strong>
            </div>
            <div class="cf-formula-row">
              <span style="color:rgba(255,255,255,0.45);">CF = MB − MD</span>
              <strong style="color:#4ade80;">Hasil Final</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     CTA
═══════════════════════════════════════════════════════ -->
<section class="cta-section">
  <div class="section-container">
    <span class="section-tag" data-reveal>✦ Mulai Sekarang</span>
    <h2 class="section-title" data-reveal data-delay="60">Siap Meningkatkan Akurasi<br>Diagnosa Anda?</h2>
    <p class="section-subtitle" data-reveal data-delay="120">
      Sistem ini tersedia gratis untuk tenaga kebidanan. Masuk dan mulai konsultasi pertama Anda hari ini.
    </p>
    <div class="cta-actions" data-reveal data-delay="200">
      <a href="<?= $BASE ?>/login.php" class="hero-btn-primary" style="font-size:15px;padding:15px 32px;">
        🔐 Masuk ke Sistem
      </a>
      <a href="#fitur" class="hero-btn-secondary" style="font-size:14.5px;padding:14px 28px;">
        Pelajari Fitur Lebih Lanjut
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════ -->
<footer class="landing-footer">
  <div class="footer-container">
    <div class="footer-brand">
      <div class="logo-icon-wrap">🏥</div>
      <span>SisPak Bidan</span>
    </div>
    <div class="footer-text">
      <div>Sistem Pakar Diagnosa Kebidanan</div>
      <div style="margin-top:4px;">Metode Certainty Factor · <?= date('Y') ?></div>
    </div>
  </div>
</footer>

<script src="<?= $BASE ?>/assets/js/main.js"></script>
<script>
  // Mobile nav
  const mobileMenu = document.getElementById('mobileMenu');
  document.getElementById('navMobileToggle')?.addEventListener('click', () => {
    mobileMenu.classList.add('active');
    document.body.style.overflow = 'hidden';
  });
  document.getElementById('closeMobileMenu')?.addEventListener('click', closeMobile);
  function closeMobile() {
    mobileMenu.classList.remove('active');
    document.body.style.overflow = '';
  }

  // Animate hero stats
  function countUp(el, target, duration = 1200) {
    if (!el) return;
    const start = performance.now();
    const update = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(ease * target);
      if (progress < 1) requestAnimationFrame(update);
      else el.textContent = target;
    };
    requestAnimationFrame(update);
  }

  const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        countUp(document.getElementById('ctrGejala'), 25);
        countUp(document.getElementById('ctrKondisi'), 10);
        countUp(document.getElementById('ctrRule'), 50);
        io.disconnect();
      }
    });
  }, { threshold: 0.5 });

  const statsEl = document.querySelector('.hero-stats');
  if (statsEl) io.observe(statsEl);
</script>
</body>
</html>
