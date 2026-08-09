## Alur Aplikasi Web Pendaftaran Seminar SNOS 2026

### 1. Halaman Informasi Kegiatan

Pengguna membuka website seminar dan melihat:

* Nama dan tema kegiatan
* Jadwal dan lokasi
* Daftar narasumber
* Ruang lingkup artikel
* Biaya pendaftaran
* Daftar jurnal tujuan publikasi
* Timeline kegiatan
* Tombol **Daftar Sekarang**

---

## 2. Registrasi Akun

Peserta membuat akun dengan mengisi:

* Nama lengkap
* NIK atau nomor identitas
* Asal perguruan tinggi/instansi
* Nomor WhatsApp
* Email
* Password

Setelah registrasi, sistem mengirimkan verifikasi email atau kode OTP WhatsApp.

**Status akun:**
`Belum Verifikasi → Aktif`

---

## 3. Login dan Melengkapi Profil

Peserta login ke dashboard kemudian melengkapi:

* Gelar depan dan belakang
* Jenis kelamin
* Alamat
* Perguruan tinggi/instansi
* Program studi atau unit kerja
* Nomor WhatsApp
* Foto profil
* Kartu mahasiswa, jika mendaftar sebagai mahasiswa

---

## 4. Memilih Jenis Kepesertaan

Peserta memilih salah satu kategori:

### Presenter

* Presenter luring
* Presenter daring

### Peserta Nonpresenter

* Peserta umum
* Peserta mahasiswa

Pilihan ini menentukan biaya pendaftaran dan dokumen yang harus diunggah.

---

## 5. Pendaftaran Kegiatan

Peserta mengisi formulir:

* Jenis peserta
* Metode kehadiran: luring atau daring
* Bidang atau scope artikel
* Institusi
* Kebutuhan khusus
* Pilihan mengikuti malam keakraban
* Persetujuan syarat dan ketentuan

Setelah disimpan, sistem membuat:

* Nomor registrasi
* Tagihan pembayaran
* Batas waktu pembayaran

**Status pendaftaran:**
`Draft → Menunggu Pembayaran`

---

## 6. Pembayaran

Peserta melihat informasi pembayaran:

* Nominal biaya
* Nomor rekening
* Kode pembayaran
* Batas waktu pembayaran

Peserta kemudian:

1. Melakukan pembayaran.
2. Mengunggah bukti transfer.
3. Menunggu verifikasi panitia.

**Status pembayaran:**
`Belum Bayar → Menunggu Verifikasi → Terverifikasi`

Apabila bukti pembayaran tidak sesuai:

`Perlu Perbaikan`

Panitia memberikan catatan agar peserta mengunggah ulang bukti pembayaran.

---

## 7. Pengajuan Artikel untuk Presenter

Setelah pembayaran terverifikasi, presenter dapat mengunggah:

* Judul artikel
* Nama penulis
* Email setiap penulis
* Afiliasi
* Abstrak
* Kata kunci
* Bidang artikel
* File artikel
* Surat pernyataan keaslian
* Pilihan jurnal atau prosiding

Format file dapat berupa `.doc`, `.docx`, atau `.pdf`.

**Status artikel:**
`Draft → Diajukan → Pemeriksaan Administrasi`

---

## 8. Pemeriksaan Administrasi Artikel

Admin memeriksa:

* Kesesuaian template
* Kelengkapan identitas penulis
* Bidang artikel
* Similarity atau plagiarisme
* Kelengkapan dokumen pendukung

Hasil pemeriksaan:

* Diterima untuk direview
* Perlu perbaikan administrasi
* Ditolak

**Status:**
`Pemeriksaan Administrasi → Proses Review`

---

## 9. Penugasan Reviewer

Admin atau editor menentukan reviewer berdasarkan bidang artikel.

Reviewer menerima informasi:

* Judul artikel
* Abstrak
* File artikel
* Batas waktu review
* Form penilaian

Identitas reviewer dapat disembunyikan dari penulis.

---

## 10. Proses Review Artikel

Reviewer memberikan penilaian terhadap:

* Kesesuaian tema
* Kebaruan penelitian
* Metode penelitian
* Hasil dan pembahasan
* Kualitas referensi
* Tata bahasa dan penulisan
* Rekomendasi keputusan

Hasil review:

* Diterima tanpa revisi
* Diterima dengan revisi minor
* Revisi mayor
* Ditolak

**Status artikel:**
`Sedang Direview → Revisi → Diterima/Ditolak`

---

## 11. Revisi Artikel

Peserta menerima catatan reviewer melalui dashboard.

Peserta kemudian:

1. Mengunduh hasil review.
2. Memperbaiki artikel.
3. Mengunggah file revisi.
4. Mengisi tanggapan terhadap reviewer.
5. Mengirim ulang artikel.

Sistem menyimpan seluruh versi artikel dan riwayat revisi.

---

## 12. Letter of Acceptance

Artikel yang diterima akan mendapatkan:

* Letter of Acceptance atau LoA
* Nomor artikel
* Jadwal presentasi
* Ruang atau link Zoom
* Format PowerPoint
* Informasi pembayaran publikasi, jika ada

LoA dapat diunduh langsung dari dashboard.

---

## 13. Pembayaran Publikasi

Jika publikasi dikenakan biaya terpisah, peserta memperoleh tagihan tambahan berdasarkan jurnal yang dipilih.

Alurnya:

`Tagihan Publikasi → Upload Bukti → Verifikasi → Lunas`

---

## 14. Penyusunan Jadwal Seminar

Panitia menyusun:

* Kelompok presentasi
* Moderator
* Ruangan
* Waktu presentasi
* Link Zoom
* Meeting ID dan password
* Nomor sesi
* Urutan presenter

Peserta dapat melihat jadwal pribadi pada dashboard.

---

## 15. Pengiriman Materi Presentasi

Presenter mengunggah:

* File PowerPoint
* Video presentasi, jika diperlukan
* Biodata singkat
* Foto resmi
* Pernyataan kesediaan presentasi

Batas waktu unggahan ditentukan oleh panitia.

---

## 16. Registrasi Ulang Hari Pelaksanaan

Pada hari kegiatan, peserta melakukan registrasi ulang dengan:

* Memindai QR Code
* Memasukkan nomor registrasi
* Menunjukkan tiket digital

Sistem mencatat:

* Waktu hadir
* Jenis kehadiran
* Lokasi atau sesi
* Status hadir

**Status kehadiran:**
`Belum Hadir → Hadir`

Untuk peserta daring, kehadiran dapat dicatat melalui link khusus atau kode yang ditampilkan saat Zoom.

---

## 17. Pelaksanaan Seminar

### Peserta Luring

* Check-in di lokasi
* Mengikuti pembukaan
* Mengikuti sesi seminar
* Presentasi sesuai ruangan
* Mengisi absensi setiap sesi

### Peserta Daring

* Membuka link Zoom dari dashboard
* Mengisi absensi daring
* Mengikuti sesi presentasi
* Mengunggah bukti atau kode kehadiran jika diperlukan

Moderator dapat mencatat:

* Presenter hadir
* Presentasi selesai
* Tidak hadir
* Jadwal ulang
* Catatan pelaksanaan

---

## 18. Penilaian Presentasi

Moderator atau tim penilai mengisi:

* Penguasaan materi
* Kualitas presentasi
* Ketepatan waktu
* Kemampuan menjawab
* Kesesuaian materi dengan artikel

Hasil penilaian dapat digunakan untuk menentukan:

* Best Paper
* Best Presenter
* Best Poster
* Artikel terbaik setiap bidang

---

## 19. Evaluasi Kegiatan

Setelah acara selesai, peserta wajib mengisi kuesioner:

* Penilaian narasumber
* Penilaian panitia
* Kualitas materi
* Fasilitas kegiatan
* Pelaksanaan Zoom
* Kritik dan saran

Sertifikat hanya dapat diunduh setelah peserta:

* Lunas pembayaran
* Dinyatakan hadir
* Mengisi evaluasi
* Menyelesaikan kewajiban presentasi

---

## 20. Sertifikat Elektronik

Sistem membuat sertifikat otomatis untuk:

* Peserta
* Presenter
* Moderator
* Reviewer
* Narasumber
* Panitia

Sertifikat memiliki:

* Nomor sertifikat
* QR Code validasi
* Nama peserta
* Peran peserta
* Jumlah JP
* Tanda tangan elektronik
* Link verifikasi sertifikat

---

## 21. Publikasi dan Pengumuman

Peserta dapat melihat:

* Status penerbitan artikel
* Nama jurnal
* Volume dan nomor
* DOI
* Link artikel
* Prosiding kegiatan
* Pengumuman pemenang
* Dokumentasi kegiatan

---

# Alur Singkat Sistem

```text
Buka Website
      ↓
Registrasi Akun
      ↓
Verifikasi Email/WhatsApp
      ↓
Lengkapi Profil
      ↓
Pilih Jenis Peserta
      ↓
Daftar Kegiatan
      ↓
Pembayaran
      ↓
Verifikasi Panitia
      ↓
Khusus Presenter: Upload Artikel
      ↓
Pemeriksaan Administrasi
      ↓
Review Artikel
      ↓
Revisi
      ↓
Artikel Diterima dan LoA
      ↓
Penetapan Jadwal Presentasi
      ↓
Upload Materi
      ↓
Registrasi Ulang/Check-in
      ↓
Pelaksanaan Seminar
      ↓
Absensi dan Evaluasi
      ↓
Sertifikat
      ↓
Publikasi Artikel
```

# Hak Akses Pengguna

### 1. Peserta/Presenter

* Mengelola profil
* Mendaftar kegiatan
* Membayar biaya
* Mengunggah artikel
* Melihat hasil review
* Mengunggah revisi
* Mengunduh LoA
* Melihat jadwal
* Check-in
* Mengisi evaluasi
* Mengunduh sertifikat

### 2. Admin/Panitia

* Mengelola kegiatan
* Mengelola peserta
* Memverifikasi pembayaran
* Memeriksa artikel
* Mengatur reviewer
* Menyusun jadwal
* Mengelola absensi
* Mengelola sertifikat
* Membuat laporan

### 3. Reviewer

* Melihat artikel yang ditugaskan
* Mengunduh artikel
* Memberikan penilaian
* Mengunggah hasil review
* Memberikan rekomendasi

### 4. Moderator

* Melihat jadwal sesi
* Melihat daftar presenter
* Mencatat kehadiran
* Memberikan penilaian presentasi
* Menulis berita acara

### 5. Pimpinan

* Melihat dashboard statistik
* Melihat jumlah peserta
* Melihat pemasukan
* Melihat jumlah artikel
* Melihat hasil publikasi
* Mengunduh laporan kegiatan

# Status Utama Pendaftaran

```text
Draft
Menunggu Pembayaran
Menunggu Verifikasi
Pembayaran Terverifikasi
Artikel Diajukan
Sedang Direview
Perlu Revisi
Artikel Diterima
Jadwal Ditetapkan
Siap Mengikuti Seminar
Hadir
Selesai
Sertifikat Terbit
```

Aplikasi sebaiknya menyediakan dashboard statistik jumlah peserta, pembayaran, artikel, reviewer, kehadiran, sertifikat, dan publikasi secara real-time.