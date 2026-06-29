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
