# Manifest Hasil Uji dan Struktur Bukti Keamanan (SEC-01 s.d. SEC-10)

**Proyek:** Aplikasi Web Perpustakaan Mini yang Diamankan di Server Linux  
**Mata Kuliah:** Keamanan Sistem Komputer — Project Pengganti UTS  
**Pelaksana:** Muhammad Ridha (NIM: 230170010)  
**Dokumen Acuan:** PRD Perpustakaan (`04 - Persyaratan Keamanan.md`, `09 - Rencana Pengujian.md`, `11 - Checklist Bukti dan Pengumpulan.md`)  
**Lingkungan Target:** Ubuntu 24.04 LTS (VM `192.168.56.10`), PHP 8.3 (CLI/FPM), Laravel 12, Nginx, SQLite 3  
**Status Pengujian:** **Belum Dijalankan (Rencana, Matriks, dan Struktur Direktori Siap)**  
**Tanggal Penyusunan Manifest:** 16 November 2026  

---

## 1. Pernyataan Integritas dan Tata Kelola Pengujian

Manifest ini merupakan instrumen pelacakan, pemetaan, dan pencatatan bukti pengujian keamanan resmi untuk memenuhi 10 Persyaratan Keamanan (**SEC-01 s.d. SEC-10**) dan 16 Kebutuhan Fungsional Kategori Must (**FR-01 s.d. FR-16**).

> [!important] **Pernyataan Integritas Pengujian (Tanpa Data Fiktif)**  
> Seluruh status eksekusi pengujian pada dokumen ini pada tahap penyusunan draf awal ditegaskan berstatus **Belum Dijalankan**. Dokumen ini secara ketat **membedakan antara Target Kriteria Penerimaan (*acceptance target*) dengan Hasil Aktual Pengujian (*actual test result*)**. Tidak ada rekaman terminal palsu, screenshot sintetis, atau klaim kelulusan prematur yang dicantumkan sebelum pengujian nyata dieksekusi pada cabang dan lingkungan yang ditentukan.

---

## 2. Standar dan Konvensi Bukti Pengujian

Setiap bukti pengujian yang dikumpulkan pada tiket-tiket implementasi berikutnya wajib mematuhi standar operasional berikut:

1. **Konvensi Penamaan Berkas Bukti:**
   `SEC-{nomor}-TST-{nomor}-{kondisi}-{YYYYMMDD}-{HHMM}.{ekstensi}`
   - Contoh tunggal: `SEC-02-TST-02-main-20261118-1430.png`
   - Contoh komparatif sebelum: `SEC-08-TST-08-sebelum-20261120-1015.png`
   - Contoh komparatif sesudah: `SEC-08-TST-08-sesudah-20261120-1045.png`
2. **Metadata Wajib Sesi Terminal VM:**
   Setiap tangkapan layar (*screenshot*) atau log sesi terminal pada server Linux wajib menyertakan eksekusi perintah:
   ```bash
   whoami; date
   ```
   Menampilkan identitas akun operator VM (`ridha0010`) dan stempel waktu aktual pada lingkungan Ubuntu 24.04 LTS.
3. **Penyensoran Rahasia (*Sanitization / Redaction*):**
   - Password plaintext tidak boleh dicatat atau ditampilkan (gunakan penyensoran `[REDACTED_PASSWORD]`).
   - Kunci enkripsi (`TELEPON_KEY_V1`, `APP_KEY`, private key TLS) dilarang keras masuk ke dalam bukti (gunakan `[REDACTED_KEY]`).
   - Hash password penuh, nilai session ID aktif, dan token CSRF wajib disamarkan sebagian (misal: `$2y$12$eXamPLe...`).
4. **Verifikasi Integritas Berkas (SHA-256):**
   Setiap berkas bukti (gambar PNG, log TXT, atau rekaman terminal) yang disimpan di dalam `docs/bukti/` wajib dihitung dan dicatat *hash checksum* SHA-256-nya pada tabel manifest ini menggunakan utilitas `sha256sum`:
   ```bash
   sha256sum docs/bukti/SEC-xx/<nama-file>
   ```
5. **Batas Isolasi Jaringan dan Lingkup Pengujian:**
   - Cabang `main` (Versi Aman): Diuji melalui koneksi host-only HTTPS pada `https://192.168.56.10:443` dari host lab `192.168.56.1`.
   - Cabang `vulnerable` (Versi Demonstrasi Kerentanan): **HANYA boleh dijalankan pada loopback localhost `127.0.0.1:8001`**. Dilarang keras melakukan binding ke `0.0.0.0` atau mengarahkan Nginx reverse proxy ke port ini.
   - Dilarang keras melakukan pemindaian atau pengujian jaringan ke luar subnet lab/komputer sendiri.

---

## 3. Struktur Direktori Bukti (`docs/bukti/`)

Direktori berkas bukti diorganisasikan secara modular untuk mendukung pengujian tunggal maupun pengujian komparatif berpasangan (*sebelum vs sesudah*):

```text
docs/bukti/
├── SEC-01/                     # Bukti Tabel Risiko & Penjelasan Analisis Risiko
├── SEC-02/                     # Bukti Bcrypt Hash cost 12 & Salt Unik Otomatis
├── SEC-03/                     # Bukti Enkripsi Telepon AES-256-GCM, Nonce, Tag, AAD & Tamper
├── SEC-04/                     # Bukti Nginx HTTPS TLS 1.2/1.3, SAN Cert, & curl --cacert
├── SEC-05/                     # Bukti Hardening Linux, Izin File, Akun www-data, SSH Key, APP_DEBUG=false
├── SEC-06/                     # Bukti Firewall UFW (Default Deny Incoming)
│   ├── sebelum/                # Kondisi awal: UFW nonaktif, port uji 8080 terbuka/reachable
│   └── sesudah/                # Kondisi akhir: UFW default deny, port 8080 diblokir, port 22 & 443 host diizinkan
├── SEC-07/                     # Bukti Backup Otomatis & Verifikasi Restore Terisolasi
│   ├── sebelum/                # Kondisi awal: ketiadaan backup otomatis, simulasi insiden kehilangan data
│   └── sesudah/                # Kondisi akhir: eksekusi backup cron, restore terisolasi, integritas data pulih
├── SEC-08/                     # Bukti Pencegahan SQL Injection pada Pencarian Buku
│   ├── sebelum/                # Cabang vulnerable: kueri mentah, payload ' OR 1=1 -- membocorkan data
│   └── sesudah/                # Cabang main: parameter binding literal, 0 hasil tanpa error SQL
├── SEC-09/                     # Bukti Pencegahan Stored XSS pada Ulasan Buku
│   ├── sebelum/                # Cabang vulnerable: render mentah, payload <script>alert() memicu popup lokal
│   └── sesudah/                # Cabang main: contextual escaping Blade & CSP header aktif tanpa popup
└── SEC-10/                     # Bukti Pencegahan IDOR & Pengamanan Sesi
    ├── idor-sebelum/           # Cabang vulnerable: user_a membaca transaksi user_b via URL IDOR
    ├── idor-sesudah/           # Cabang main: PeminjamanPolicy menolak akses user_a dengan 404 Not Found
    ├── sesi-sebelum/           # Cabang vulnerable: cookie tanpa flag HttpOnly/Secure, ID sesi tidak dirotasi
    └── sesi-sesudah/           # Cabang main: cookie Secure/HttpOnly/SameSite, rotasi login, invalidasi logout
```

---

## 4. Matriks Manifest Bukti Pengujian (SEC-01 s.d. SEC-10)

Tabel berikut memetakan setiap persyaratan keamanan, kebutuhan fungsional, dan skenario uji ke artefak bukti yang akan dikumpulkan:

| ID Syarat | ID Fitur | ID Uji | Kondisi Pengujian | Skenario & Target Pengujian | Target Host / Port & Branch | Target Kriteria Penerimaan | Status Hasil Aktual | Lokasi Berkas Bukti | Checksum SHA-256 |
|---|---|---|---|---|---|---|---|---|---|
| **SEC-01** | FR-16 | TST-01 | Tunggal | Tinjauan kelengkapan Risk Register 5 aset, 5 risiko, CIA, kontrol korektif backup, dan risiko tertinggi | Repositori Dokumen (`main`) | 5 aset, 5 risiko terisi lengkap, mencakup C/I/A, referensi >= 5 SEC berbeda, kontrol korektif restore backup tercatat | **Selesai Dokumen** (Tabel risiko siap di repo) | `docs/tabel-risiko.md`<br>`docs/bukti/SEC-01/` | *Tercatat di dokumen* |
| **SEC-02** | FR-01<br>FR-02<br>FR-12<br>FR-16 | TST-02 | Tunggal | Pengujian pembuatan password dua pengguna fiktif (`user_a`, `user_b`) dengan input password sama persis | Localhost / PHPUnit (`main`) | `Hash::make()` menghasilkan string hash berbeda untuk input sama; `Hash::check()` sukses untuk keduanya; password salah bernilai false; panjang 8-72 byte ditegakkan | **Lulus Unit/Feature Test** (100% via `PasswordHashingTest`, `RegistrationTest`, `MemberLifecycleTest`) | `docs/bukti/SEC-02/`<br>`tests/Feature/PasswordHashingTest.php` | *Pending screenshot DB* |
| **SEC-03** | FR-10<br>FR-12 | TST-03 | Tunggal | Pengujian round-trip enkripsi/dekripsi AES-256-GCM nomor telepon anggota, keunikan nonce 12-byte, pengikatan AAD, dan penolakan tamper | Localhost / PHPUnit (`main`) | Telepon tersimpan dalam format JSON envelope versioned (`telepon_enc`); dua enkripsi menghasilkan ciphertext & nonce berbeda; manipulasi tag/ciphertext/AAD atau key salah menolak secara aman | **Lulus Unit/Feature Test** (100% via `AesGcmTest`, `MemberPhoneEncryptionTest`) | `docs/bukti/SEC-03/`<br>`tests/Unit/AesGcmTest.php` | *Pending screenshot DB* |
| **SEC-04** | FR-02<br>FR-16 | TST-04 | Tunggal | Verifikasi koneksi Nginx HTTPS port 443, kesesuaian SAN sertifikat self-signed, protokol TLS 1.2/1.3, dan validasi `curl --cacert` | VM `192.168.56.10:443`<br>`mini-library.test`<br>(`main`) | Nginx menyajikan HTTPS port 443; SAN mencakup DNS dan IP; negosiasi TLS 1.2/1.3 berhasil; `curl --cacert` memverifikasi trust; private key berizin 600 | **Pending Eksekusi VM** (Menunggu deployment Nginx TLS di VM) | `docs/bukti/SEC-04/` | *Pending execution* |
| **SEC-05** | FR-16 | TST-05 | Tunggal | Audit hardening sistem operasi Linux Ubuntu 24.04 LTS: akun sudoer non-root, proses worker www-data tanpa shell, izin file, penolakan akses .env/.git, dan `APP_DEBUG=false` | VM `192.168.56.10`<br>(`main`) | Akun `ridha0010` sudoer non-root; `www-data` tanpa shell login; izin berkas `.env` dan SQLite ketat (600); akses dotfiles diblokir web server; konfigurasi SSH menolak root dan password | **Pending Eksekusi VM** (Menunggu audit hardening Ubuntu 24.04 VM) | `docs/bukti/SEC-05/` | *Pending execution* |
| **SEC-06** | FR-16 | TST-06 | **Sebelum** | Pengujian konektivitas port TCP 8080 (listener uji aktif) dan status firewall UFW sebelum aturan diterapkan | VM `192.168.56.10:8080`<br>(`main`) | Status UFW inactive atau rules belum terpasang; port uji 8080 dapat diakses (*reachable*) dari host lab `192.168.56.1` | **Pending Eksekusi Lab** (Menunggu pengujian konektivitas lab sebelum UFW) | `docs/bukti/SEC-06/sebelum/` | *Pending execution* |
| **SEC-06** | FR-16 | TST-06 | **Sesudah** | Pengujian konektivitas port TCP 8080 (listener uji tetap aktif) dan port layanan setelah UFW default deny aktif | VM `192.168.56.10:8080`<br>`:443`, `:22`<br>(`main`) | UFW status active; incoming default deny; port 8080 terblokir (*connection timed out/refused*); port 22 (SSH) dan 443 (HTTPS) tetap dapat diakses dari host lab `192.168.56.1` | **Pending Eksekusi Lab** (Menunggu UFW default deny aktif & nmap) | `docs/bukti/SEC-06/sesudah/` | *Pending execution* |
| **SEC-07** | FR-01<br>FR-09<br>FR-10<br>FR-16 | TST-07 | **Sebelum** | Pencatatan ketiadaan penjadwalan backup otomatis pada cron dan simulasi insiden kerusakan basis data SQLite | VM `192.168.56.10`<br>(`main`) | Crontab belum memuat tugas backup harian; basis data mengalami simulasi kehilangan/kerusakan data tanpa salinan aktif | **Pending Eksekusi VM** (Menunggu simulasi kehilangan data) | `docs/bukti/SEC-07/sebelum/` | *Pending execution* |
| **SEC-07** | FR-01<br>FR-09<br>FR-10<br>FR-16 | TST-07 | **Sesudah** | Eksekusi otomatis script backup SQLite via Linux cron, verifikasi enkripsi OpenSSL & hash SHA-256, serta uji pemulihan (*restore*) pada instance terisolasi | VM `192.168.56.10`<br>(`main`) | File backup terenkripsi berhasil dibuat di luar VM; hash SHA-256 cocok; prosedur restore pada database terisolasi berhasil memulihkan 100% data transaksi, login akun, dan dekripsi telepon; durasi RPO dan RTO tercatat | **Pending Eksekusi VM** (Menunggu cron backup & verifikasi restore VM) | `docs/bukti/SEC-07/sesudah/` | *Pending execution* |
| **SEC-08** | FR-04<br>FR-16 | TST-08 | **Sebelum** | Pengujian kueri pencarian buku dengan payload SQL Injection `' OR 1=1 -- ` pada rute `/buku/cari?q=` | Localhost `127.0.0.1:8001`<br>(`vulnerable`) | Kueri konkatenasi string SQL mentah mengeksekusi payload; hasil pencarian meluas menampilkan seluruh buku di luar kata kunci yang dicari | **Pending Cabang Vulnerable** (Menunggu demo SQLi di port 8001) | `docs/bukti/SEC-08/sebelum/` | *Pending execution* |
| **SEC-08** | FR-04<br>FR-16 | TST-08 | **Sesudah** | Pengujian kueri pencarian buku dengan payload SQL Injection identik `' OR 1=1 -- ` pada rute `/buku/cari?q=` | VM `192.168.56.10:443`<br>(`main`) | Eloquent Query Builder memperlakukan payload sebagai literal string melalui parameter binding; hasil pencarian menampilkan 0 buku (tidak cocok) tanpa error SQL | **Lulus Unit/Feature Test** (100% via `BookSearchSecurityTest`) | `docs/bukti/SEC-08/sesudah/`<br>`tests/Feature/BookSearchSecurityTest.php` | *Pending capture HTTPS* |
| **SEC-09** | FR-05<br>FR-06<br>FR-15<br>FR-16 | TST-09 | **Sebelum** | Pengiriman ulasan buku berisi skrip jahat `<script>alert('XSS-LAB')</script>` dan pembukaan halaman oleh pengguna lain | Localhost `127.0.0.1:8001`<br>(`vulnerable`) | Ulasan disimpan dan dirender mentah tanpa filter (`{!! !!}`); browser mengeksekusi JavaScript dan memunculkan dialog pop-up alert lokal | **Pending Cabang Vulnerable** (Menunggu demo stored XSS alert di port 8001) | `docs/bukti/SEC-09/sebelum/` | *Pending execution* |
| **SEC-09** | FR-05<br>FR-06<br>FR-15<br>FR-16 | TST-09 | **Sesudah** | Pengiriman ulasan buku berisi skrip jahat identik `<script>alert('XSS-LAB')</script>` dan pembukaan halaman oleh pengguna lain | VM `192.168.56.10:443`<br>(`main`) | Blade contextual escaping (`{{ }}`) mengubah tag HTML menjadi entitas teks aman (`&lt;script&gt;`); browser merender teks biasa tanpa memunculkan alert; CSP header aktif | **Lulus Unit/Feature Test** (100% via `ReviewSecurityTest`, `BookDetailTest`) | `docs/bukti/SEC-09/sesudah/`<br>`tests/Feature/ReviewSecurityTest.php` | *Pending capture HTTPS* |
| **SEC-10** | FR-08<br>FR-09<br>FR-16 | TST-10a | **Sebelum** | Pengujian pembacaan detail transaksi peminjaman milik user_b oleh user_a via URL langsung `/peminjaman/{id}` | Localhost `127.0.0.1:8001`<br>(`vulnerable`) | Server tidak memverifikasi kepemilikan objek (ketiadaan policy); user_a berhasil membaca transaksi privat milik user_b (HTTP 200 OK) | **Pending Cabang Vulnerable** (Menunggu demo IDOR di port 8001) | `docs/bukti/SEC-10/idor-sebelum/` | *Pending execution* |
| **SEC-10** | FR-08<br>FR-09<br>FR-16 | TST-10a | **Sesudah** | Pengujian pembacaan detail transaksi peminjaman milik user_b oleh user_a via URL langsung `/peminjaman/{id}` | VM `192.168.56.10:443`<br>(`main`) | `PeminjamanPolicy::view` memvalidasi kepemilikan objek transaksi; akses silang user_a ditolak dengan respons HTTP 404 Not Found (`denyAsNotFound()`) | **Lulus Unit/Feature Test** (100% via `BorrowingAuthorizationTest`) | `docs/bukti/SEC-10/idor-sesudah/`<br>`tests/Feature/BorrowingAuthorizationTest.php` | *Pending capture HTTPS* |
| **SEC-10** | FR-02<br>FR-16 | TST-10b | **Sebelum** | Pemeriksaan atribut cookie sesi pada respon login dan uji session fixation | Localhost `127.0.0.1:8001`<br>(`vulnerable`) | Cookie sesi ditransmisikan tanpa atribut proteksi keamanan lengkap; session ID tidak dirotasi pasca-login tamu ke terotentikasi | **Pending Cabang Vulnerable** (Menunggu demo cookie tanpa flag) | `docs/bukti/SEC-10/sesi-sebelum/` | *Pending execution* |
| **SEC-10** | FR-02<br>FR-03<br>FR-16 | TST-10b<br>TST-10c | **Sesudah** | Pemeriksaan atribut cookie sesi pada koneksi HTTPS, rotasi session ID saat login, dan invalidasi sesi saat logout | VM `192.168.56.10:443`<br>(`main`) | Header `Set-Cookie` memuat flag `Secure`, `HttpOnly`, `SameSite=Lax`; session ID berubah secara kriptografis saat login; session cookie lama ditolak setelah logout | **Lulus Unit/Feature Test** (100% via `SessionSecurityTest`, `AuthenticationTest`) | `docs/bukti/SEC-10/sesi-sesudah/`<br>`tests/Feature/SessionSecurityTest.php` | *Pending inspection DevTools HTTPS* |
| **SEC-10** | FR-02<br>FR-16 | TST-10d | Tunggal | Pengujian penolakan request mutasi data tanpa token CSRF valid dan pembatasan percobaan login | VM `192.168.56.10:443`<br>(`main`) | Request POST/PATCH/DELETE tanpa token CSRF menghasilkan HTTP 419 Page Expired; percobaan login salah berturut-turut memicu HTTP 429 Too Many Requests | **Lulus Unit/Feature Test** (100% via `AuthenticationTest`, `RegistrationTest`) | `docs/bukti/SEC-10/sesi-sesudah/`<br>`tests/Feature/AuthenticationTest.php` | *Pending capture HTTPS* |

---

## 5. Lembar Rencana Verifikasi Rinci (Checklist per Syarat)

### SEC-01 — Risk Register & CIA Matrix
- [x] Dokumen `docs/tabel-risiko.md` tersedia dan terhubung ke PRD.
- [ ] Bukti evaluasi kesesuaian 5 aset dan 5 risiko CIA dengan rubrik penilaian dosen.
- [ ] Penjelasan lisan atas rantai ancaman–kontrol siap dipresentasikan.

### SEC-02 — Bcrypt Password Hashing & Salt Otomatis
- [ ] Tangkapan layar kode implementasi `Hash::make()` dan validasi panjang 8–72 byte.
- [x] Eksekusi PHPUnit test `PasswordHashingTest`: pembuktian dua hash berbeda untuk input password yang sama.
- [x] Hasil penolakan password salah dan akun nonaktif tanpa kebocoran pesan internal.
- [ ] Verifikasi ketiadaan password plaintext di file konfigurasi, database, maupun log aplikasi.

### SEC-03 — Enkripsi AES-256-GCM Nomor Telepon Anggota
- [ ] Tangkapan layar kode service `App\Services\AesGcm.php` (key 32 byte, nonce 12 byte acak, tag 16 byte, AAD anggota).
- [ ] Tampilan basis data membuktikan kolom `telepon_enc` berupa JSON envelope versioned tanpa nomor telepon plaintext.
- [x] Eksekusi test round-trip enkripsi–dekripsi sukses mengembalikan nomor telepon asli.
- [x] Eksekusi test tamper: modifikasi ciphertext, tag autentikasi, nonce, atau AAD gagal didekripsi secara aman.

### SEC-04 — Transport Security HTTPS Self-Signed
- [ ] Rincian sertifikat publik TLS: SAN mencakup domain `mini-library.test` dan IP `192.168.56.10`.
- [ ] Eksekusi `nginx -t` sukses dan listener port 443 berstatus LISTEN.
- [ ] Verifikasi akses menggunakan `curl --cacert mini-library.crt https://mini-library.test` sukses dengan status HTTP 200.
- [ ] Verifikasi izin file private key TLS berizin ketat `600` milik root/layanan web.

### SEC-05 — Hardening Sistem Operasi Linux Ubuntu 24.04 LTS
- [ ] Output terminal `whoami; date` membuktikan akun non-root `ridha0010`.
- [ ] Bukti proses PHP-FPM berjalan sebagai akun worker `www-data` tanpa shell login aktif.
- [ ] Audit izin berkas: berkas rahasia `.env` dan direktori database SQLite berizin `600`/`750`.
- [ ] Konfigurasi SSH: `PermitRootLogin no`, `MaxAuthTries 3`, autentikasi berbasis kunci publik (SSH key).
- [ ] Verifikasi konfigurasi web server memblokir akses ke berkas tersembunyi (`.env`, `.git`) dan `APP_DEBUG=false`.

### SEC-06 — Firewall UFW Default Deny Incoming
- [ ] **Sebelum:** Screenshot status UFW inactive / port 8080 berhasil diakses dari komputer host.
- [ ] **Sesudah:** Screenshot status UFW active dengan kebijakan incoming default deny.
- [ ] **Sesudah:** Bukti port 8080 diblokir saat listener pengujian tetap aktif di VM.
- [ ] **Sesudah:** Bukti port 22 (SSH) dan 443 (HTTPS) tetap dapat diakses dari host lab `192.168.56.1`.

### SEC-07 — Backup Otomatis & Verifikasi Restore Terisolasi
- [ ] **Sebelum:** Bukti awal ketiadaan konfigurasi cron backup dan skenario simulasi kehilangan data.
- [ ] **Sesudah:** Konfigurasi entri Linux cron harian mengeksekusi script backup berbasis SQLite Online Backup API.
- [ ] **Sesudah:** Bukti berkas backup terenkripsi OpenSSL, hash SHA-256 terhitung, dan salinan tersimpan di luar VM.
- [ ] **Sesudah:** Eksekusi prosedur restore terverifikasi pada instance database terisolasi: `PRAGMA integrity_check`, pengujian login, dekripsi nomor telepon, dan pencatatan metrik RTO/RPO aktual.

### SEC-08 — Pencegahan SQL Injection pada Pencarian Katalog
- [ ] **Sebelum (vulnerable):** Request `/buku/cari?q=%27%20OR%201%3D1%20--%20` memperluas hasil pencarian ke seluruh buku.
- [x] **Sesudah (main):** Request dengan payload identik diperlakukan sebagai string literal dan menghasilkan 0 buku.
- [ ] Perbandingan kode kueri mentah (*raw string concatenation*) vs kueri aman (*parameter binding* Eloquent).

### SEC-09 — Pencegahan Cross-Site Scripting (XSS) pada Ulasan Buku
- [ ] **Sebelum (vulnerable):** Input ulasan `<script>alert('XSS-LAB')</script>` dieksekusi oleh peramban pembaca kedua.
- [x] **Sesudah (main):** Input ulasan identik di-escape secara kontekstual oleh Blade (`&lt;script&gt;`) dan CSP header aktif.
- [ ] Tangkapan layar kode Blade template: pembuktian pemakaian `{{ }}` dan ketiadaan `{!! !!}` untuk input pengguna.

### SEC-10 — Pencegahan IDOR & Pengamanan Sesi
- [ ] **Sebelum (vulnerable):** `user_a` dapat mengakses detail transaksi peminjaman milik `user_b` pada `/peminjaman/{id_user_b}`.
- [x] **Sesudah (main):** `PeminjamanPolicy::view` menolak akses silang dengan respons HTTP 404 Not Found (`denyAsNotFound()`).
- [ ] **Sebelum (vulnerable):** Cookie sesi tanpa atribut keamanan dan session ID tidak berubah pasca-login.
- [ ] **Sesudah (main):** Cookie sesi memiliki flag `Secure; HttpOnly; SameSite=Lax`, rotasi session ID saat login, dan invalidasi sesi saat logout.
- [x] Pengujian penolakan CSRF (HTTP 419) dan rate limiter login (HTTP 429).

---

## 6. Log Riwayat Pembaruan Manifest

### FIX-01 — Verifikasi lokal integritas sirkulasi

- `CirculationTest` memeriksa pengembalian ulang HTTP 409 dan dua snapshot pinjaman yang dibaca sebelum pengembalian; stok bertambah satu kali.
- `AdminBookTest` memeriksa pembaruan stok menggunakan model buku lama setelah peminjaman mengubah stok.
- Pengujian ini berjalan lokal dengan SQLite dalam memori; belum merupakan uji dua proses konkuren pada SQLite berbasis file atau bukti evaluasi VM.

### FIX-02 — Verifikasi lokal seeder dan rahasia

- `PasswordHashingTest` memeriksa hash fixture, seeding berulang tanpa menimpa data, penolakan production, dan penolakan password fixture kosong.
- `.env.example` tidak memuat kunci telepon atau password fixture; PHPUnit menggunakan nilai khusus pengujian.
- Pengujian lokal tidak membuktikan bahwa deployment telah mengganti kunci yang pernah dipublikasikan atau memigrasikan ciphertext lama.

### FIX-03 — Verifikasi lokal validasi byte admin dan rekonsiliasi jatuh tempo

- `StoreMemberRequest` diperbarui dengan penegakan closure byte length check `strlen($value) > 72` untuk mencegah silent truncation Bcrypt pada password multibyte.
- `MemberLifecycleTest::test_admin_create_member_rejects_password_over_72_bytes` memverifikasi penolakan password multibyte dan string 73 byte.
- `PeminjamanService` mengembalikan `jatuh_tempo = null` saat transaksi peminjaman baru sesuai baseline PRD Q-03 (kolom nullable tanpa asumsi sepihak).

### FIX-04 — Penyusunan README resmi dan sinkronisasi matriks manifest

- `README.md` disusun lengkap memenuhi ketentuan penugasan Bagian 3.B (identitas mahasiswa, arsitektur, panduan deploy, daftar akun demo, perbedaan main vs vulnerable, dan 10 syarat keamanan).
- Matriks manifest disinkronkan dengan hasil pengujian aktual 142 test suite PHPUnit yang lulus 100%, sembari menegaskan status pending untuk pengujian Linux VM dan demonstrasi cabang vulnerable.

| Tanggal | Branch / Commit | Penanggung Jawab | Deskripsi Pembaruan |
|---|---|---|---|
| 2026-11-16 | `task-003-susun-tabel-risiko-dan-struktur-bukti-tkt-03` | Muhammad Ridha (NIM: 230170010) | Penyusunan awal struktur manifest bukti pengujian, pemetaan traceability SEC-01 s.d. SEC-10, dan pembuatan kerangka direktori `docs/bukti/`. |
| 2026-11-16 | `main` | Muhammad Ridha (NIM: 230170010) | Sinkronisasi status riil manifest: 142 unit/feature tests (543 assertions) lulus. Penegasan status pending pengujian lingkungan Ubuntu VM dan demo vulnerable. |
