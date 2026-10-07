# Pustaka Digital Sekolah

**Pustaka Digital Sekolah** adalah platform sistem informasi perpustakaan dan buku digital interaktif modern yang dirancang untuk mendukung ekosistem literasi sekolah dan Kurikulum Merdeka dari jenjang SD, SMP, hingga SMA/SMK.

Platform ini memadukan sensasi membaca lembaran buku fisik 3D (*realistic flipbook*), modul audio narasi Text-To-Speech (TTS), video pembelajaran multimedia, evaluasi kuis otomatis, modul penugasan membaca berbatas waktu, serta fitur stabilo digital dan catatan rangkuman mandiri yang terhubung langsung ke akun siswa.

---

## 🚀 Fitur Unggulan & Proses Bisnis

```mermaid
graph TD
    User([Pengguna Sekolah]) --> Auth{Autentikasi & Peran}
    
    Auth -->|Admin / Guru| AdminPanel["<b>Panel Guru & Admin (/admin)</b><br/>• Manajemen Buku Digital (3D & PDF)<br/>• Bab Pembelajaran & Halaman Kaya Multimedia<br/>• Bank Soal & Kuis Interaktif<br/>• Modul Penugasan Membaca (Target & Deadline)<br/>• Rekap Progres & Nilai Siswa"]
    
    Auth -->|Siswa| SiswaPanel["<b>Portal & Panel Siswa (/siswa)</b><br/>• Dasbor Literasi & Statistik Baca<br/>• Rak Buku Digital Interaktif<br/>• Penugasan Membaca dari Guru<br/>• Stabilo & Catatan Rangkuman Mandiri<br/>• Riwayat Baca & Lembar Nilai Kuis"]
    
    Auth -->|Tamu / Umum| PublicShelf["<b>Rak Buku Sekolah (/)</b><br/>• Filter Jenjang Kelas & Mata Pelajaran<br/>• Akses Baca Interaktif Langsung"]
```

### 1. 📖 Pembaca Buku Interaktif 3D & Mode Baca Fokus
- **Mode Buku 3D Realistis (*StPageFlip Engine*)**: Efek membalik lembaran kertas dinamis, bayangan buku, sampul *hardcover*, serta simulasi suara lembaran kertas prosedural (*Web Audio API*).
- **Mode Baca Fokus (*Single Sheet Classic*)**: Tampilan vertikal yang nyaman dan bersih untuk perangkat bergerak atau sesi membaca intensif.
- **Audio Narasi & Text-to-Speech (TTS)**: Mendukung audio narator rekaman guru (MP3) maupun pembacaan otomatis suara Bahasa Indonesia via *Web Speech API*.
- **Penyematan Multimedia**: Integrasi gambar ilustrasi materi dan sematan video pembelajaran interaktif (*YouTube / MP4*).
- **Dock Kontrol Cepat**: Slider penelusuran halaman (*scrubber*), tombol layar penuh (*fullscreen*), toggle suara kertas, dan daftar isi mengambang.

### 2. 🔒 Proteksi Buku Digital & PDF Terenkripsi
- **Distribusi PDF Aman**: File buku PDF dilindungi oleh *Secure PDF Controller* dengan URL bertoken waktu (*expiring token*).
- **Proteksi Hak Cipta**: Penonaktifan toolbar cetak dan unduh default browser untuk mencegah pembajakan buku sekolah tanpa izin.

### 3. 🏆 Evaluasi Pemahaman & Kuis Interaktif Bab
- **Kuis Real-Time Tanpa Reload**: Ditenagai oleh *Livewire v3*, siswa dapat langsung menjawab soal pilihan ganda di akhir bab.
- **Keamanan Kunci Jawaban**: Kunci jawaban diproses 100% di sisi server (*server-side grading*), tidak pernah dibocorkan ke kode sumber JavaScript siswa.
- **Batas Kelulusan (*Passing Score*)**: Menampilkan skor instan, status kelulusan, dan menyimpan riwayat ujian ke buku rapor digital siswa.

### 4. 📌 Modul Penugasan Membaca (Reading Assignments)
- **Penugasan oleh Guru**: Guru dapat menugaskan buku tertentu dengan target bab khusus, batas waktu pengerjaan (*deadline*), dan memilih siswa target.
- **Sinkronisasi Kemajuan Otomatis**: Progres membaca siswa diperbarui secara *real-time* saat mereka membalik lembaran buku.
- **Rekapitulasi Kelas**: Guru dapat melihat tabel ringkasan siapa saja siswa yang sudah selesai membaca, sedang membaca, atau belum membaca.
- **Widget Siswa**: Siswa memiliki menu khusus *"Tugas Membaca"* dan pengingat *deadline* di dasbor belajar mereka.

### 5. 🖍️ Stabilo & Catatan Digital (Highlighter & Sticky Notes)
- **Highlighter Toolbar Teks Cepat**: Siswa cukup menyeleksi kalimat penting di halaman buku untuk menampilkan bilah stabilo dengan 6 pilihan warna:
  - 🟡 Kuning (`#fef08a`)
  - 🟢 Hijau (`#86efac`)
  - 🔵 Biru (`#93c5fd`)
  - 🟠 Oranye (`#fdba74`)
  - 🟣 Ungu (`#d8b4fe`)
  - 🌸 Pink (`#f9a8d4`)
- **Catatan Rangkuman Mandiri**: Siswa dapat menyematkan catatan penjelasan pada kalimat yang distabilo atau membuat catatan mandiri per halaman buku.
- **Slide-over Drawer Catatan**: Laci catatan yang dapat dibuka kapan saja di sisi kanan layar pembaca buku, lengkap dengan fitur:
  - Lompat ke halaman catatan terkait (*One-click jump*)
  - Tombol **📋 Salin Rangkuman** untuk menyalin semua catatan buku ke *clipboard* format Markdown.
- **Manajemen Catatan di Panel Siswa (`/siswa/catatan-stabilo`)**: Halaman khusus untuk melihat seluruh kumpulan stabilo dan catatan rangkuman dari semua buku yang pernah dipelajari.

---

## 🏗️ Tumpukan Teknologi (*Tech Stack*)

- **Backend Framework**: [Laravel 11](https://laravel.com/) (PHP 8.2+)
- **Admin & Student Panel**: [Filament v3](https://filamentphp.com/)
- **Komponen Reaktif**: [Livewire v3](https://livewire.laravel.com/)
- **Frontend & Styling**: Tailwind CSS, Vanilla CSS Design System, Lora & Cinzel Typography
- **3D Flipbook Viewer**: StPageFlip Canvas Engine
- **PDF Rendering**: Mozilla PDF.js
- **Database**: SQLite (Default) / MySQL / PostgreSQL

---

## 🗄️ Skema Database Utama

| Tabel | Deskripsi |
|---|---|
| `grades` | Jenjang tingkatan kelas (e.g. Kelas 1 SD s.d. Kelas 12 SMA). |
| `subjects` | Mata pelajaran sekolah (e.g. IPA, Matematika, Bahasa Indonesia). |
| `categories` | Kategori bahan pustaka (e.g. Buku Teks Utama, Pengayaan, Ensiklopedia). |
| `books` | Data pokok buku digital (judul, ISBN, penulis, tipe 3D/PDF, sampul). |
| `chapters` | Bab dan urutan materi pembelajaran dalam buku. |
| `pages` | Halaman interaktif (isi materi, audio MP3, video, gambar). |
| `quizzes` | Evaluasi bab dengan konfigurasi passing score dan durasi. |
| `quiz_questions` & `quiz_options` | Bank butir soal dan opsi pilihan jawaban kuis. |
| `reading_assignments` | Penugasan membaca oleh guru dengan tenggat waktu. |
| `reading_assignment_students` | Relasi penugasan ke siswa dengan progres persentase baca. |
| `student_book_annotations` | Stabilo teks terpilih, catatan rangkuman, dan warna penanda. |
| `student_reading_logs` | Catatan halaman terakhir dan persentase keterbacaan siswa. |
| `student_quiz_attempts` | Lembar rekap hasil dan skor evaluasi kuis siswa. |

---

## ⚙️ Panduan Instalasi & Menjalankan Proyek

### 1. Prasyarat Sistem
- PHP >= 8.2 (dengan ekstensi `pdo_sqlite` / `pdo_mysql`, `mbstring`, `openssl`, `curl`)
- Composer >= 2.x
- Node.js >= 18.x & NPM

### 2. Kloning Repositori
```bash
git clone https://github.com/donarazhar/pustakadigital.git
cd pustakadigital
```

### 3. Instalasi Dependensi
```bash
composer install
npm install
```

### 4. Konfigurasi Lingkungan (.env)
```bash
# Salin file konfigurasi lingkungan
cp .env.example .env

# Buat kunci enkripsi aplikasi
php artisan key:generate
```

### 5. Migrasi Database & Seeder
```bash
# Jalankan migrasi dan isi data awal buku demo
php artisan migrate --seed

# Buat symbolic link ke media penyimpanan
php artisan storage:link
```

### 6. Menjalankan Server Lokal
Jalankan kompilasi aset frontend dan web server:
```bash
# Terminal 1: Dev Server PHP
php artisan serve

# Terminal 2: Vite Assets Bundler (opsional untuk pengembangan UI)
npm run dev
```

Aplikasi siap diakses melalui peramban web pada alamat:  
👉 **`http://127.0.0.1:8000`**

---

## 🔑 Akun Uji Coba Default

Setelah menjalankan seeder (`php artisan db:seed`), akun demo berikut siap digunakan:

| Peran (Role) | Email | Kata Sandi | Halaman Akses |
|---|---|---|---|
| **Administrator** | `admin@sekolah.id` | `password` | `/admin` |
| **Guru / Pendidik** | `guru@sekolah.id` | `password` | `/admin` |
| **Siswa / Pelajar** | `siswa@sekolah.id` | `password` | `/siswa` |

---

## 🧪 Pengujian Otomatis (*Automated Tests*)

Aplikasi dilengkapi dengan suite pengujian otomatis fitur berbasis PHPUnit / Pest:
```bash
php artisan test
```
*Status Uji: **17 passed (39 assertions, 100% green)*** mencakup pengujian pembaca buku, proteksi keamanan kuis, alur tugas membaca guru, serta stabilo dan catatan digital siswa.

---

## 📄 Lisensi

Platform ini dikembangkan di bawah lisensi terbuka [MIT License](LICENSE).
