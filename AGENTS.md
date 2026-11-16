# AGENTS.md — Petunjuk Kerja Agent: Perpustakaan Mini (Keamanan Sistem Komputer)

Dokumen panduan operasional AI Agent untuk repositori `library_UTS`. Seluruh pekerjaan implementasi, pengujian, dan verifikasi merujuk ke Obsidian Vault di `D:\Obsidian\Library\PRD-Perpustakaan`.

---

## 1. Ringkasan Proyek & Konteks

- **Tujuan Proyek:** Aplikasi Web Perpustakaan Mini yang Diamankan di Server Linux (Project Pengganti UTS Keamanan Sistem Komputer).
- **Pelaksana:** Muhammad Ridha (NIM: 230170010, Tema: Perpustakaan — Digit NIM `0`).
- **Sumber Spesifikasi Primer:**
  - Obsidian Vault: `D:\Obsidian\Library\PRD-Perpustakaan\`
  - Indeks PRD: `00 - PRD Index.md` s.d. `14 - Baseline dan Keputusan Proyek.md`
  - Rangkaian Tiket: `Tickets/00 - Indeks Tickets.md` (31 Tiket Wajib `TKT-01` s.d. `TKT-31`, 2 Tiket Opsional `OPS-01`, `OPS-02`)
  - Dokumen Asli: `Project_Pengganti_UTS_Keamanan_Sistem_Komputer.docx`

---

## 2. Tech Stack & Environment Baseline

| Komponen | Spesifikasi Terkonfirmasi | Catatan Implementasi |
|---|---|---|
| **OS Target VM** | Ubuntu 24.04 LTS (Noble Numbat) | Host-only network (Host: `192.168.56.1`, VM: `192.168.56.10`) |
| **Runtime** | PHP 8.3 (CLI & FPM) | Ekstensi aktif: `pdo_sqlite`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `curl` |
| **Framework** | Laravel 12 (Skeleton murni) | **Tanpa starter kit** (Breeze/Jetstream dilarang; auth manual) |
| **Basis Data** | SQLite 3 (`database/database.sqlite`) | Disimpan di luar root web publik; WAL enabled; FK constraint aktif |
| **Web Server** | Nginx + PHP-FPM | Root strictly `/public`; blokir dotfiles, `.env`, backup, dan SQLite |
| **Template Engine** | Blade | Contextual escaping bawaan `{{ }}` |
| **Icon Library** | Blade Icons | Standar icon SVG via [Blade Icons](https://blade-ui-kit.com/blade-icons) jika fitur memerlukan icon |
| **Testing** | PHPUnit 11 | In-memory SQLite untuk unit test, file SQLite terpisah untuk uji konkurensi |

---

## 3. Batasan Mutlak & Non-Goals (Scope Fence)

### Dilarang Dibuat (Out of Scope):
1. Starter kit auth bawaan Laravel (Breeze/Jetstream/Fortify).
2. Denda keterlambatan, reservasi buku, pembayaran/transaksi finansial.
3. Notifikasi email, SMTP, dan reset password via email.
4. OAuth pihak ketiga (Google Login, GitHub, SSO) dan MFA.
5. REST API publik terpisah, SPA (Vue/React), dan aplikasi mobile.
6. Multi-branch perpustakaan atau multi-tenancy.
7. Desain UI kompleks/mewah (fokus penilaian adalah fungsi dan 10 syarat keamanan).
8. Pengujian ke jaringan luar/internet, pemindaian server orang lain, atau penggunaan malware nyata (pelanggaran berakibat nilai 0).

---

## 4. Invarian Keamanan (10 SEC / Syarat Dosen)

Seluruh agent wajib menegakkan 10 syarat keamanan berikut pada cabang `main`:

### SEC-01 / SYR-1: Tabel Risiko
- Risk register 5 aset, 5 risiko, mencakup CIA triad.
- Kontrol wajib merujuk minimal 5 SEC berbeda; backup otomatis sebagai kontrol korektif.
- Dokumentasi di `docs/tabel-risiko.md` (merujuk `08 - Tabel Risiko.md`).

### SEC-02 / SYR-2: Password Hashing & Salt Otomatis
- Wajib menggunakan `Hash::make()` (Bcrypt default cost 12).
- Validasi panjang password eksplisit: 8 s.d. 72 byte UTF-8 (mencegah silent truncation Bcrypt).
- Salt dikelola otomatis per hash oleh framework; dilarang membuat kolom salt terpisah.
- Dilarang menyimpan atau mencetak password plaintext di DB, log, seeder git, exception, atau response.
- Wajib menyediakan fixture akun fiktif: 2 user (`user_a`, `user_b`) dengan input password sama persis tetapi menghasilkan hash berbeda di DB.

### SEC-03 / SYR-3: Enkripsi Nomor Telepon (AES-256-GCM)
- Layanan enkripsi tersentralisasi di `App\Services\AesGcm.php`.
- Cipher: `aes-256-gcm` melalui `openssl_encrypt()` / `openssl_decrypt()` dengan `OPENSSL_RAW_DATA`.
- Key 256-bit (32 byte) dibaca via `config('security.telepon_key')` dari env `TELEPON_KEY_V1` (base64). Dilarang panggil `env()` langsung di controller/service.
- Nonce wajib acak 12 byte (`random_bytes(12)`) per enkripsi. Dilarang reuse nonce.
- Tag autentikasi 16 byte.
- AAD (Additional Authenticated Data) mengikat identitas anggota: `anggota:{id}:telepon:v1`.
- Format penyimpanan di kolom `telepon_enc` berupa JSON envelope versioned:
  `{"v":1,"nonce":"<base64>","ct":"<base64>","tag":"<base64>"}`.
- Dekripsi salah/tamper/key rusak harus menolak aman tanpa membocorkan pesan internal OpenSSL.

### SEC-04 / SYR-4: Transport Security (HTTPS Self-Signed)
- Nginx melayani HTTPS port 443 dengan TLS 1.2 & TLS 1.3.
- SAN sertifikat mencakup nama domain lab (`mini-library.test`) dan IP VM (`192.168.56.10`).
- Port 80 HTTP dialihkan ke HTTPS atau ditutup total.
- Izin file private key TLS: `600`, hanya milik root/layanan web.

### SEC-05 / SYR-5: Hardening Linux
- Akun operator non-root sudoer (`ridha0010`).
- Akun worker PHP-FPM (`www-data`) tanpa login shell.
- Izin direktori: source code read-only untuk worker web; hanya `storage/`, `bootstrap/cache/`, dan direktori SQLite yang writable.
- File konfigurasi rahasia (`.env`) izin `600`.
- SSH: `PermitRootLogin no`, `MaxAuthTries 3`, autentikasi berbasis SSH Key.
- `APP_DEBUG=false` pada lingkungan deployment/evaluasi.

### SEC-06 / SYR-6: Firewall Default Deny (UFW)
- Policy UFW: `default deny incoming`, `default allow outgoing`.
- Port dibuka terbatas: SSH (22) dan HTTPS (443) hanya dari IP host lab (`192.168.56.1`).
- Pengujian nmap sebelum/sesudah dengan listener pengujian 8080 aktif untuk membuktikan pemblokiran.

### SEC-07 / SYR-7: Backup Otomatis & Verifikasi Restore
- Backup terjadwal via Linux `cron` mengeksekusi script backup harian.
- Gunakan SQLite Online Backup API (`sqlite3 database.sqlite ".backup '<target>'"`) agar konsisten saat WAL aktif.
- Hasil backup dienkripsi (`openssl enc`), diverifikasi hash checksum SHA-256, dan disimpan di luar VM (laptop/lokasi terpisah).
- Prosedur restore wajib diuji pada instance terisolasi: integrity check, pemulihan data transaksi, login akun, dan dekripsi telepon dengan recovery key.

### SEC-08 / SYR-8: Pencegahan SQL Injection
- Seluruh query database wajib memakai Query Builder / Eloquent dengan parameter binding.
- Dilarang konkatenasi variabel langsung ke raw SQL (`whereRaw`, `DB::select`).
- Filter & sorting katalog wajib memakai allowlist ketat.
- Demonstrasi perbandingan: payload `' OR 1=1 -- ` pada `/buku/cari?q=`.

### SEC-09 / SYR-9: Pencegahan Cross-Site Scripting (XSS)
- Contextual output escaping wajib di Blade via `{{ $variable }}`.
- Dilarang keras memakai `{!! $variable !!}` untuk input pengguna.
- Dilengkapi Content Security Policy (CSP) header: `default-src 'self'; script-src 'self'; object-src 'none'`.
- Demonstrasi perbandingan: payload `<script>alert('XSS-LAB')</script>` pada ulasan buku.

### SEC-10 / SYR-10: Pencegahan IDOR & Pengamanan Sesi
- Setiap request detail peminjaman `/peminjaman/{id}` divalidasi via server-side policy (`PeminjamanPolicy::view`).
- Akses silang milik user lain wajib ditolak dengan `404 Not Found` (`Response::denyAsNotFound()`) tanpa membocorkan eksistensi data.
- Cookie sesi: `HttpOnly = true`, `Secure = true` (pada HTTPS), `SameSite = Lax`, session driver `file`.
- Regenerasi Session ID saat login (`$request->session()->regenerate()`).
- Invalidation total saat logout (`$request->session()->invalidate()`, regenerasi token CSRF).

---

## 5. Isolasi Cabang `main` vs `vulnerable`

Proyek memiliki dua cabang terpisah dengan batas isolasi ketat:

| Aspek | Cabang `main` | Cabang `vulnerable` (Khusus Demo) |
|---|---|---|
| **Tujuan** | Implementasi aman & penilaian tugas | Demonstrasi bukti kerentanan sebelum perbaikan |
| **Binding Jaringan** | Host-only HTTPS (`192.168.56.10:443`) | **HANYA Loopback Localhost `127.0.0.1:8001`** |
| **Basis Data** | `database/database.sqlite` | `database/vulnerable.sqlite` (terpisah) |
| **Sesi & Cookie** | Cookie name `mini_library_session` | Cookie name terpisah `mini_library_vuln_session` |
| **Kerentanan Sengaja** | Nihil (kebal SQLi, XSS, IDOR) | 1) SQLi pencarian, 2) Stored XSS ulasan, 3) IDOR peminjaman |
| **Aturan Keras** | Evaluasi resmi dosen | **DILARANG bind 0.0.0.0, dilarang reverse proxy Nginx** |

---

## 6. Model Data & Konvensi Entitas

Model dan tabel database merujuk `06 - Model Data.md`:

1. **`Anggota` (`app/Models/Anggota.php` -> tabel `anggota`)**
   - Mengimplementasikan `Illuminate\Foundation\Auth\User as Authenticatable`.
   - Menggantikan tabel `users` default (provider `auth.providers.users.model` diarahkan ke `Anggota`).
   - Kolom: `id`, `nama`, `email` (UK), `password` (hash), `role` (`admin` / `user`), `is_active` (boolean, default true), `telepon_enc` (JSON envelope, nullable).
   - Melindungi admin terakhir: admin terakhir tidak boleh dinonaktifkan.

2. **`Buku` (`app/Models/Buku.php` -> tabel `buku`)**
   - Kolom: `id`, `judul`, `penulis`, `deskripsi`, `stok_total`, `stok_tersedia`, `archived_at` (nullable).
   - Buku bersejarah yang pernah dipinjam **tidak dihapus fisik**, melainkan diarsipkan (`archived_at` diisi timestamp).

3. **`Peminjaman` (`app/Models/Peminjaman.php` -> tabel `peminjaman`)**
   - Kolom: `id`, `anggota_id` (FK), `buku_id` (FK), `dipinjam_pada`, `jatuh_tempo` (nullable, kebijakan tertunda Q-03), `dikembalikan_pada` (nullable), `status` (`dipinjam` / `kembali`).
   - Peminjaman mengurangi stok secara atomik dalam database transaction.
   - Pengembalian memulihkan stok secara idempoten (buku yang sudah kembali tidak bisa dikembalikan lagi).

4. **`Ulasan` (`app/Models/Ulasan.php` -> tabel `ulasan`)**
   - Kolom: `id`, `anggota_id` (FK), `buku_id` (FK), `isi`, `created_at`, `updated_at`.
   - Terikat pada anggota yang sedang login dari sesi.

---

## 7. Rute & Middleware

Mewarisi middleware group `web` di `routes/web.php` (merujuk `07 - Rute dan Otorisasi.md`):

- **Publik:**
  - `GET /` -> redirect `/buku`
  - `GET /buku` -> katalog buku aktif
  - `GET /buku/cari?q=` -> pencarian parameter binding (**T1 Target SQLi demo**)
  - `GET /buku/{buku}` -> detail buku & ulasan escaped (**T2 Target XSS demo**)
- **Autentikasi Tamu:**
  - `GET /registrasi`, `POST /registrasi` (role otomatis `user`)
  - `GET /login`, `POST /login` (verifikasi hash, regenerasi sesi)
- **Anggota / User:**
  - `POST /logout` (auth, csrf)
  - `POST /buku/{buku}/ulasan` (auth, active, csrf)
  - `GET /profil`, `PATCH /profil/telepon` (auth, active, encrypt AES-GCM)
  - `GET /peminjaman` (auth, active, role:user -> riwayat milik sendiri)
  - `POST /peminjaman` (auth, active, role:user -> pinjam buku)
  - `GET /peminjaman/{id}` (auth, active -> policy `view`, **T3 Target IDOR demo**)
- **Admin Panel (`/admin/*`):**
  - Dilindungi middleware `auth`, `active`, `role:admin`.
  - CRUD & arsip buku (`/admin/buku/*`)
  - Manajemen anggota & deaktivasi (`/admin/anggota/*`)
  - Sirkulasi pinjam & pengembalian (`/admin/peminjaman/*`)
  - Moderasi / hapus ulasan (`DELETE /admin/ulasan/{ulasan}`)

---

## 8. Panduan Eksekusi Tugas & Perintah Esensial

### Menjalankan Testing
```bash
# Jalankan seluruh test suite
php artisan test

# Jalankan pengujian spesifik keamanan
php artisan test --filter=Security
php artisan test tests/Feature/PasswordHashingTest.php
php artisan test tests/Feature/MemberPhoneEncryptionTest.php
php artisan test tests/Feature/BorrowingAuthorizationTest.php
php artisan test tests/Feature/BookSearchSecurityTest.php
php artisan test tests/Feature/ReviewSecurityTest.php
```

### Menjalankan Server Dev Lokal
```bash
# Server utama (versi aman)
php artisan serve --host=127.0.0.1 --port=8000

# Server demonstrasi rentan (jika di cabang vulnerable)
php artisan serve --host=127.0.0.1 --port=8001
```

### Migrasi & Seeder Fixture
```bash
php artisan migrate:fresh --seed
```

---

## 9. Aturan Kontribusi bagi Agent

1. **Lazy Senior Dev:** Tulis kode seminimal mungkin yang memenuhi spesifikasi; jangan membuat abstraksi atau repositori/pola desain yang tidak diminta.
2. **Tanpa Starter Kit:** Jangan menginstal Laravel Breeze, Jetstream, atau Livewire.
3. **Kerahasiaan:** Jangan pernah melakukan hardcode terhadap password, API key, app secret, TLS key, atau base64 encryption key ke dalam git.
4. **Verifikasi Diagnostik:** Setiap selesai membuat atau mengubah file PHP/Blade, jalankan validasi linter atau `lsp_diagnostics` untuk memastikan nihil sintaks error.
5. **Dokumentasi Hasil:** Setiap perubahan yang memenuhi tiket Obsidian harus memperbarui status dan bukti di manifest pengujian lokal sesuai konvensi `docs/`.
6. **Standar Icon:** Ketika membuat sebuah fitur yang memerlukan icon (navigasi, tombol, form, tabel admin, status badge), wajib gunakan [Blade Icons](https://blade-ui-kit.com/blade-icons).
