# Perpustakaan Mini — Aplikasi Web yang Diamankan di Server Linux
**Project Pengganti UTS — Keamanan Sistem Komputer (S1 Teknik Informatika)**

---

## 1. Identitas Mahasiswa dan Penugasan

- **Nama Mahasiswa:** Muhammad Ridha
- **NIM:** 230170010
- **Digit Terakhir NIM:** `0` (Tema: **Perpustakaan**)
  - Tabel Data Utama: `buku`, `anggota`, `peminjaman` (+ tabel pendukung `ulasan`)
  - Data Sensitif Terenkripsi (Syarat 3): **Nomor telepon anggota** (`telepon_enc` via AES-256-GCM)
  - Data Terproteksi IDOR (Syarat 10): **Riwayat dan detail peminjaman anggota lain** (`/peminjaman/{id}`)
- **Target Deployment:** VM Ubuntu 24.04 LTS (Noble Numbat) pada jaringan Host-Only (`192.168.56.10:443`)

---

## 2. Tech Stack & Environment Baseline

| Komponen | Spesifikasi | Catatan Implementasi |
|---|---|---|
| **Sistem Operasi VM** | Ubuntu 24.04 LTS | Host-only network (Host: `192.168.56.1`, VM: `192.168.56.10`) |
| **Runtime Bahasa** | PHP 8.3 (CLI & FPM) | Ekstensi aktif: `pdo_sqlite`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `curl` |
| **Web Framework** | Laravel 12 (Skeleton Murni) | **Tanpa starter kit** (Breeze/Jetstream dilarang; auth manual) |
| **Basis Data** | SQLite 3 | Tersimpan di `database/database.sqlite`; WAL enabled; FK constraint aktif |
| **Web Server / Reverse Proxy** | Nginx + PHP-FPM | Root strictly `/public`; blokir dotfiles, `.env`, backup, dan SQLite |
| **Firewall** | UFW (Uncomplicated Firewall) | Policy: `default deny incoming`, allow SSH (22) dan HTTPS (443) |
| **Pengujian** | PHPUnit 11 | In-memory SQLite untuk unit test, file SQLite terpisah untuk integrasi |

---

## 3. Daftar Akun Uji Fiktif (Demo)

Seluruh akun fiktif disiapkan secara otomatis melalui `DatabaseSeeder`:

| Role | Nama | Alamat Email | Fungsi Pengujian |
|---|---|---|---|
| **Admin** | Admin Perpustakaan | `admin@perpustakaan.test` | Mengelola katalog buku, verifikasi anggota, sirkulasi peminjaman/pengembalian, dan moderasi ulasan |
| **User A** | User A | `user_a@perpustakaan.test` | Anggota biasa: pinjam buku, ulasan escaped, update telepon terenkripsi AES-GCM, verifikasi IDOR |
| **User B** | User B | `user_b@perpustakaan.test` | Anggota biasa kedua: pembuktian salt unik (password sama menghasilkan hash berbeda) dan target uji IDOR |

> [!important]
> Password akun demo disuplai melalui environment variable (`SEED_USER_PASSWORD` dan `SEED_ADMIN_PASSWORD`) atau konfigurasi lokal `.env`. Tidak ada password plaintext yang dicatat permanen dalam repositori Git publik.

---

## 4. Panduan Menjalankan Aplikasi

### A. Persiapan Lingkungan Lokal (Pengembangan & Testing)

1. **Clone Repositori:**
   ```bash
   git clone <url-repo-anda> library_UTS
   cd library_UTS
   ```

2. **Instal Dependensi:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Berkas Lingkungan (`.env`):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Generate Kunci Enkripsi Telepon (AES-256-GCM 32 Byte Base64):**
   ```bash
   # Di Linux / Git Bash:
   echo "TELEPON_KEY_V1=base64:$(openssl rand -base64 32)" >> .env
   
   # Di PowerShell:
   $bytes = 1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 }
   Add-Content .env "TELEPON_KEY_V1=base64:$([Convert]::ToBase64String($bytes))"
   ```

5. **Inisialisasi Database SQLite & Seeder:**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

6. **Jalankan Seluruh Suite Pengujian (PHPUnit):**
   ```bash
   php artisan test
   ```

7. **Jalankan Server Lokal:**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

---

## 5. Perbedaan Cabang Aman (`main`) vs Cabang Rentan (`vulnerable`)

Proyek ini memisahkan secara ketat versi produksi yang aman dengan versi demonstrasi kerentanan:

| Aspek | Cabang `main` (Aman / Penilaian Resmi) | Cabang `vulnerable` (Khusus Demonstrasi Lab) |
|---|---|---|
| **Tujuan** | Implementasi aman memenuhi 10 Syarat Keamanan dosen | Membuktikan keberadaan eksploit sebelum dilakukan mitigasi |
| **Jaringan & Host** | Host-only HTTPS (`https://192.168.56.10:443`) | **HANYA Loopback Localhost `http://127.0.0.1:8001`** |
| **Basis Data** | `database/database.sqlite` | `database/vulnerable.sqlite` (terpisah) |
| **Sesi & Cookie** | Cookie `mini_library_session` (Secure, HttpOnly, SameSite=Lax) | Cookie `mini_library_vuln_session` (tanpa atribut keamanan) |
| **Syarat 8 (SQLi)** | Parameter binding via Query Builder / Eloquent; payload `' OR 1=1 -- ` dianggap literal string | Penggabungan string SQL mentah pada `/buku/cari?q=`; payload membocorkan seluruh buku |
| **Syarat 9 (XSS)** | Blade contextual escaping `{{ }}` & CSP header aktif; skrip dirender sebagai teks entitas | Penggunaan render mentah tanpa sanitasi; skrip `<script>alert('XSS')</script>` memicu popup dialog |
| **Syarat 10 (IDOR)** | Server-side policy `PeminjamanPolicy::view`; akses silang ditolak aman dengan respons `404 Not Found` | URL IDOR `/peminjaman/{id}` tanpa otorisasi pemilik; user A bebas membaca peminjaman user B |
| **Aturan Isolasi** | Evaluasi resmi dosen | **Dilarang bind 0.0.0.0, dilarang reverse proxy Nginx** |

---

## 6. Ringkasan Implementasi 10 Syarat Keamanan

- **Syarat 1 (Tabel Risiko):** Analisis 5 aset, 5 risiko (Confidentiality, Integrity, Availability), kontrol terhubung minimal 5 syarat, dan backup korektif di `docs/tabel-risiko.md`.
- **Syarat 2 (Password Hashing):** Menggunakan `Hash::make()` Bcrypt (default cost 12), validasi password minimal 12 karakter dan maksimal 72 byte UTF-8. Dua akun demo memiliki password sama dengan hash berbeda.
- **Syarat 3 (Enkripsi AES-256-GCM):** Enkripsi nomor telepon anggota via `App\Services\AesGcm.php` menggunakan kunci 256-bit di `.env`, nonce 12 byte acak per enkripsi, tag autentikasi 16 byte, dan AAD terikat identitas anggota (`anggota:{id}:telepon:v1`).
- **Syarat 4 (Transport Security HTTPS):** Nginx port 443 dengan sertifikat self-signed, SAN IP `192.168.56.10` dan domain `mini-library.test`, TLS 1.2 & 1.3, izin private key `600`, port 80 redirect ke HTTPS.
- **Syarat 5 (Hardening Linux):** Akun admin non-root sudoer `ridha0010`, worker `www-data` tanpa shell login, izin file rahasia `600`, SSH `PermitRootLogin no` & `MaxAuthTries 3`, `APP_DEBUG=false`.
- **Syarat 6 (Firewall UFW Default Deny):** Kebijakan default deny incoming, membuka port 22 (SSH) dan port 443 (HTTPS) hanya dari IP host lab (`192.168.56.1`), diverifikasi dengan nmap sebelum vs sesudah.
- **Syarat 7 (Backup & Restore Terverifikasi):** Script cron harian mengeksekusi backup SQLite konsisten (SQLite Online Backup API), enkripsi arsip via OpenSSL, pencatatan checksum SHA-256, dan verifikasi restore terisolasi.
- **Syarat 8 (Pencegahan SQLi):** Seluruh kueri katalog dan transaksi menggunakan parameter binding Eloquent.
- **Syarat 9 (Pencegahan XSS):** Kontekstual output escaping bawaan Blade `{{ }}` pada seluruh input teks ulasan dan header Content Security Policy (CSP).
- **Syarat 10 (Pencegahan IDOR & Pengamanan Sesi):** Otorisasi kepemilikan data di server via `PeminjamanPolicy`, cookie sesi `Secure`, `HttpOnly`, `SameSite=Lax`, rotasi ID sesi saat login, dan invalidasi sesi total saat logout.
