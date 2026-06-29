# 🏥 SisPak Bidan v2.0

**Sistem Pakar Diagnosa Kebidanan** berbasis metode **Certainty Factor**

---

## 📋 Deskripsi

SisPak Bidan adalah sistem pakar berbasis web yang membantu tenaga bidan
dalam mendiagnosa kondisi kebidanan menggunakan metode **Certainty Factor (CF)**.

### Fitur Utama
- ✅ 9 Kondisi Kebidanan (Preeklampsia, Hiperemesis, Anemia, KPD, dll)
- ✅ 25 Gejala dengan bobot CF pakar
- ✅ Algoritma CF terverifikasi secara klinis
- ✅ Dashboard modern & responsif (Bootstrap 5)
- ✅ Multi-user (Admin & Bidan)
- ✅ Laporan & cetak PDF
- ✅ Riwayat diagnosa lengkap dengan detail perhitungan CF
- ✅ Autocomplete pasien

---

## 🗂️ Struktur Folder

```
/
├── index.php           → Landing page (guest) / Dashboard (logged in)
├── login.php           → Halaman login
├── logout.php          → Proses logout
├── 404.php             → Halaman error 404
├── .htaccess           → Konfigurasi Apache
│
├── config/
│   ├── config.php      → Konfigurasi aplikasi & BASE_URL
│   └── database.php    → Koneksi DB & fungsi helper
│
├── includes/
│   ├── header.php      → HTML head & CSS
│   ├── navbar.php      → Top navigation bar
│   ├── sidebar.php     → Sidebar navigasi
│   └── footer.php      → JS & penutup HTML
│
├── admin/
│   ├── gejala.php      → CRUD data gejala
│   ├── kondisi.php     → CRUD kondisi/penyakit
│   ├── solusi.php      → CRUD solusi penanganan
│   ├── rule.php        → CRUD basis pengetahuan CF
│   ├── users.php       → Manajemen pengguna
│   ├── laporan.php     → Laporan & statistik
│   └── profil.php      → Edit profil & ganti password
│
├── diagnosa/
│   ├── konsultasi.php  → Form input gejala
│   ├── proses_cf.php   → Engine Certainty Factor
│   ├── hasil.php       → Tampilan hasil diagnosa
│   ├── riwayat.php     → Riwayat semua diagnosa
│   ├── detail_cf.php   → Detail langkah perhitungan CF
│   └── cetak.php       → Cetak / export PDF
│
├── api/
│   └── cari_pasien.php → Endpoint autocomplete pasien
│
├── assets/
│   ├── css/
│   │   ├── theme.css       → CSS variables & base styles
│   │   ├── components.css  → Komponen UI (buttons, cards, dll)
│   │   ├── dashboard.css   → Layout sidebar & topbar
│   │   ├── responsive.css  → Media queries
│   │   └── style.css       → Landing page & auth pages
│   └── js/
│       ├── main.js         → Core JS (sidebar, alerts)
│       ├── diagnosa.js     → CF UI & autocomplete
│       ├── validation.js   → Form validation
│       ├── dashboard.js    → Dashboard animations
│       └── theme.js        → Theme & nav helpers
│
├── uploads/            → File upload (opsional)
└── database/
    └── db_sispak_bidan.sql  → SQL database lengkap
```

---

## 🚀 Cara Deploy

### A. Localhost (XAMPP/WAMP/Laragon)

1. Copy folder project ke `htdocs/sispak_bidan/`
2. Buka phpMyAdmin → Create database `db_sispak_bidan`
3. Import file `database/db_sispak_bidan.sql`
4. Edit `config/config.php`:
   ```php
   define('BASE_URL', '/sispak_bidan');
   ```
5. Edit `config/database.php` jika perlu:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'db_sispak_bidan');
   ```
6. Akses: `http://localhost/sispak_bidan`

---

### B. InfinityFree / 000webhost (Free Hosting)

1. Login ke panel InfinityFree → **File Manager**
2. Upload semua file ke folder `htdocs/` atau `public_html/`
3. Buat database MySQL via cPanel → catat host, user, password
4. Import `database/db_sispak_bidan.sql` via phpMyAdmin cPanel
5. Edit `config/config.php`:
   ```php
   define('BASE_URL', '');   // Domain root
   ```
6. Edit `config/database.php`:
   ```php
   define('DB_HOST', 'sql111.infinityfree.com'); // sesuaikan
   define('DB_USER', 'ifX_namauser');
   define('DB_PASS', 'password_anda');
   define('DB_NAME', 'ifX_db_sispak_bidan');
   ```

---

### C. Hostinger

1. Login cPanel Hostinger → **File Manager** → upload ke `public_html/`
2. Buat database via **MySQL Databases**
3. Import SQL via phpMyAdmin
4. Edit `config/config.php`:
   ```php
   define('BASE_URL', '');
   ```
5. Edit `config/database.php` dengan kredensial MySQL Hostinger

---

### D. Render.com (dengan PHP buildpack)

1. Push project ke GitHub repository
2. Buat **Web Service** baru di Render
3. Set environment: `PHP`, build command: `composer install` (jika ada)
4. Tambahkan environment variables:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
5. Update `config/database.php` untuk membaca env vars:
   ```php
   define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
   ```

---

## 🔐 Akun Default

| Role  | Username | Password |
|-------|----------|----------|
| Admin | `admin`  | `password` |
| Bidan | `bidan1` | `password` |
| Bidan | `bidan2` | `password` |

> ⚠️ **Wajib** ganti password default setelah deploy!

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend  | PHP Native (tanpa framework) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| CSS Framework | Bootstrap 5.3 |
| Icons | Font Awesome 6 |
| Fonts | Google Fonts (Poppins + Inter) |

---

## 📐 Algoritma Certainty Factor

```
Langkah 1: CF(H,E) = CF_user × CF_pakar
Langkah 2: CF_gabung = CF_lama + CF_baru × (1 − CF_lama)
Langkah 3: Persentase = CF_final × 100%
```

**Interpretasi:**
- CF ≥ 70% → Keyakinan TINGGI
- CF 40–69% → Keyakinan SEDANG  
- CF < 40% → Keyakinan RENDAH

---

## 📝 Lisensi

Proyek ini dibuat untuk keperluan akademis (UAS/Tugas Akhir).

---

*SisPak Bidan v2.0 — Certainty Factor Based Expert System for Midwifery Diagnosis*
