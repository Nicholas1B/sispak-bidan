-- ============================================================
-- DATABASE SISTEM PAKAR DIAGNOSA BIDAN
-- Metode: Certainty Factor (CF)
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_sispak_bidan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_sispak_bidan;

-- ============================================================
-- TABEL USERS (Admin & Bidan)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','bidan') NOT NULL DEFAULT 'bidan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL GEJALA
-- ============================================================
CREATE TABLE IF NOT EXISTS gejala (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama_gejala VARCHAR(255) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL KONDISI (Penyakit/Kondisi Ibu Hamil)
-- ============================================================
CREATE TABLE IF NOT EXISTS kondisi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama_kondisi VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL SOLUSI / PENANGANAN
-- ============================================================
CREATE TABLE IF NOT EXISTS solusi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kondisi_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    tindakan TEXT NOT NULL,
    rujukan ENUM('Ya','Tidak') DEFAULT 'Tidak',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kondisi_id) REFERENCES kondisi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL RULE CF (Basis Pengetahuan)
-- ============================================================
CREATE TABLE IF NOT EXISTS rule_cf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kondisi_id INT NOT NULL,
    gejala_id INT NOT NULL,
    cf_pakar DECIMAL(3,2) NOT NULL COMMENT 'CF dari pakar (0.0 - 1.0)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rule (kondisi_id, gejala_id),
    FOREIGN KEY (kondisi_id) REFERENCES kondisi(id) ON DELETE CASCADE,
    FOREIGN KEY (gejala_id) REFERENCES gejala(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL PASIEN
-- ============================================================
CREATE TABLE IF NOT EXISTS pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pasien VARCHAR(100) NOT NULL,
    usia INT,
    usia_kehamilan INT COMMENT 'Dalam minggu',
    no_hp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL RIWAYAT DIAGNOSA
-- ============================================================
CREATE TABLE IF NOT EXISTS riwayat_diagnosa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pasien_id INT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    kondisi_id INT,
    cf_hasil DECIMAL(5,4),
    persentase DECIMAL(5,2),
    keterangan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE,
    FOREIGN KEY (kondisi_id) REFERENCES kondisi(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL DETAIL RIWAYAT (Gejala yang dipilih)
-- ============================================================
CREATE TABLE IF NOT EXISTS detail_riwayat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    riwayat_id INT NOT NULL,
    gejala_id INT NOT NULL,
    cf_user DECIMAL(3,2) NOT NULL COMMENT 'CF dari pengguna',
    FOREIGN KEY (riwayat_id) REFERENCES riwayat_diagnosa(id) ON DELETE CASCADE,
    FOREIGN KEY (gejala_id) REFERENCES gejala(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATA AWAL: USERS
-- ============================================================
INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Bidan Sri Wahyuni', 'bidan1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bidan'),
('Bidan Dewi Sartika', 'bidan2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bidan');
-- Password default: password

-- ============================================================
-- DATA AWAL: GEJALA
-- ============================================================
INSERT INTO gejala (kode, nama_gejala, keterangan) VALUES
('G01', 'Tekanan darah tinggi (≥140/90 mmHg)', 'Hipertensi pada ibu hamil'),
('G02', 'Pembengkakan pada wajah dan tangan', 'Edema patologis'),
('G03', 'Nyeri kepala hebat', 'Sakit kepala yang tidak biasa'),
('G04', 'Gangguan penglihatan (pandangan kabur)', 'Mata berkunang-kunang atau buram'),
('G05', 'Protein dalam urin (proteinuria)', 'Hasil tes urin positif protein'),
('G06', 'Mual dan muntah berlebihan', 'Lebih dari 3 kali sehari'),
('G07', 'Penurunan berat badan', 'BB turun >5% dari sebelum hamil'),
('G08', 'Dehidrasi', 'Mulut kering, urin gelap'),
('G09', 'Tidak bisa makan apapun', 'Intoleransi makanan total'),
('G10', 'Perdarahan ringan dari vagina', 'Flek atau bercak darah'),
('G11', 'Perdarahan banyak dari vagina', 'Perdarahan aktif yang mengkhawatirkan'),
('G12', 'Nyeri perut bawah', 'Kram atau nyeri di area panggul'),
('G13', 'Kontraksi sebelum 37 minggu', 'His yang teratur sebelum cukup bulan'),
('G14', 'Keluarnya cairan dari vagina', 'Cairan jernih/keruh bukan urin'),
('G15', 'Demam (suhu ≥38°C)', 'Peningkatan suhu tubuh'),
('G16', 'Nyeri saat buang air kecil', 'Rasa terbakar/perih saat BAK'),
('G17', 'Urin keruh atau berbau', 'Perubahan warna/bau urin'),
('G18', 'Anemia (Hb < 11 g/dL)', 'Hasil laboratorium Hb rendah'),
('G19', 'Kulit dan selaput mata pucat', 'Tampak pucat secara visual'),
('G20', 'Lemas dan mudah lelah', 'Kelelahan tidak normal'),
('G21', 'Detak jantung bayi tidak terdeteksi', 'DJJ tidak ada pada pemeriksaan'),
('G22', 'Tidak ada gerakan janin >24 jam', 'Janin tidak bergerak dalam sehari'),
('G23', 'Ukuran rahim tidak sesuai usia', 'TFU tidak normal'),
('G24', 'Nyeri perut atas (epigastrium)', 'Nyeri di bawah tulang dada'),
('G25', 'Kejang', 'Kejang seluruh tubuh pada ibu hamil');

-- ============================================================
-- DATA AWAL: KONDISI
-- ============================================================
INSERT INTO kondisi (kode, nama_kondisi, deskripsi) VALUES
('K01', 'Preeklampsia', 'Kondisi hipertensi yang disertai proteinuria pada kehamilan >20 minggu'),
('K02', 'Hiperemesis Gravidarum', 'Mual muntah berlebihan yang menyebabkan dehidrasi dan gangguan nutrisi'),
('K03', 'Ancaman Abortus', 'Tanda-tanda keguguran pada usia kehamilan <20 minggu'),
('K04', 'Partus Prematurus Imminens (PPI)', 'Ancaman persalinan prematur sebelum 37 minggu'),
('K05', 'Ketuban Pecah Dini (KPD)', 'Pecahnya ketuban sebelum onset persalinan'),
('K06', 'Infeksi Saluran Kemih (ISK)', 'Infeksi bakteri pada saluran kemih ibu hamil'),
('K07', 'Anemia Kehamilan', 'Kadar hemoglobin di bawah normal pada ibu hamil'),
('K08', 'Kematian Janin dalam Rahim (KJDR)', 'Janin tidak menunjukkan tanda kehidupan'),
('K09', 'Eklampsia', 'Preeklampsia yang disertai kejang');

-- ============================================================
-- DATA AWAL: SOLUSI
-- ============================================================
INSERT INTO solusi (kondisi_id, judul, tindakan, rujukan) VALUES
(1, 'Penanganan Preeklampsia', 'Istirahat total (bed rest), pantau tekanan darah setiap 4 jam, batasi asupan garam, pemberian MgSO4 jika diperlukan, rujuk ke dokter spesialis kandungan segera.', 'Ya'),
(2, 'Penanganan Hiperemesis Gravidarum', 'Rehidrasi oral/infus, pemberian antiemetik (ondansetron/prometazin), nutrisi parenteral jika perlu, istirahat cukup, hindari makanan berbau menyengat, makan porsi kecil tapi sering.', 'Tidak'),
(3, 'Penanganan Ancaman Abortus', 'Bed rest total, hindari aktivitas berat dan hubungan seksual, pemberian progesteron, pantau perdarahan, segera rujuk jika perdarahan bertambah banyak.', 'Ya'),
(4, 'Penanganan Partus Prematurus Imminens', 'Segera rujuk ke RS dengan NICU, berikan tokolitik (nifedipine), kortikosteroid untuk pematangan paru janin, tirah baring posisi miring kiri.', 'Ya'),
(5, 'Penanganan Ketuban Pecah Dini', 'Rujuk segera ke RS, hindari VT berulang, pantau tanda infeksi (demam, cairan berbau), berikan antibiotik profilaksis, observasi ketat DJJ.', 'Ya'),
(6, 'Penanganan ISK Kehamilan', 'Pemberian antibiotik sesuai sensitivitas (amoksisilin/sefaleksin), perbanyak minum air putih, jaga kebersihan area genital, kontrol urin rutin.', 'Tidak'),
(7, 'Penanganan Anemia Kehamilan', 'Suplemen Fe 60mg/hari, konsumsi makanan tinggi zat besi (hati, bayam, kacang-kacangan), hindari minum teh/kopi saat makan, konsumsi vitamin C untuk penyerapan Fe.', 'Tidak'),
(8, 'Penanganan KJDR', 'Segera rujuk ke RS untuk konfirmasi USG, dukungan psikologis kepada keluarga, persiapan terminasi kehamilan, konseling pasca kejadian.', 'Ya'),
(9, 'Penanganan Eklampsia', 'DARURAT MEDIS - Hubungi 119 segera! Posisikan miring kiri, bebaskan jalan napas, berikan O2, injeksi MgSO4 bolus IV, rujuk SEGERA ke RS dengan ICU.', 'Ya');

-- ============================================================
-- DATA AWAL: RULE CF
-- ============================================================
-- K01 - Preeklampsia
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(1, 1, 0.8),  -- Tekanan darah tinggi
(1, 2, 0.7),  -- Pembengkakan wajah/tangan
(1, 3, 0.6),  -- Nyeri kepala hebat
(1, 4, 0.6),  -- Gangguan penglihatan
(1, 5, 0.9),  -- Proteinuria
(1, 24, 0.5); -- Nyeri epigastrium

-- K02 - Hiperemesis Gravidarum
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(2, 6, 0.9),  -- Mual muntah berlebihan
(2, 7, 0.8),  -- Penurunan berat badan
(2, 8, 0.8),  -- Dehidrasi
(2, 9, 0.7);  -- Tidak bisa makan

-- K03 - Ancaman Abortus
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(3, 10, 0.8), -- Perdarahan ringan
(3, 12, 0.7), -- Nyeri perut bawah
(3, 11, 0.6); -- Perdarahan banyak

-- K04 - Partus Prematurus Imminens
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(4, 13, 0.9), -- Kontraksi sebelum 37 minggu
(4, 12, 0.6), -- Nyeri perut bawah
(4, 14, 0.5); -- Keluarnya cairan

-- K05 - KPD
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(5, 14, 0.9), -- Keluarnya cairan
(5, 15, 0.5), -- Demam
(5, 13, 0.4); -- Kontraksi

-- K06 - ISK
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(6, 16, 0.9), -- Nyeri BAK
(6, 17, 0.8), -- Urin keruh/berbau
(6, 15, 0.6), -- Demam
(6, 12, 0.4); -- Nyeri perut bawah

-- K07 - Anemia Kehamilan
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(7, 18, 0.9), -- Hb rendah
(7, 19, 0.8), -- Pucat
(7, 20, 0.7); -- Lemas

-- K08 - KJDR
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(8, 21, 0.9), -- DJJ tidak terdeteksi
(8, 22, 0.8), -- Tidak ada gerakan
(8, 23, 0.6); -- Ukuran rahim tidak sesuai

-- K09 - Eklampsia
INSERT INTO rule_cf (kondisi_id, gejala_id, cf_pakar) VALUES
(9, 25, 0.9), -- Kejang
(9, 1, 0.8),  -- Tekanan darah tinggi
(9, 3, 0.7),  -- Nyeri kepala
(9, 5, 0.7),  -- Proteinuria
(9, 4, 0.6);  -- Gangguan penglihatan
