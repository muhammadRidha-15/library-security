# Catatan Baseline Proyek dan Matriks Keputusan Implementasi (TKT-01)

**Proyek:** Aplikasi Web Perpustakaan Mini yang Diamankan di Server Linux  
**Mata Kuliah:** Keamanan Sistem Komputer — S1 Teknik Informatika (Project Pengganti UTS)  
**Tanggal Penetapan Baseline:** 16 November 2026  
**Status Dokumen:** Baseline Dikonfirmasi / Keputusan Tertunda Dicatat  
**Peran Pelaksana:** Senior Dev / Mahasiswa Pelaksana Individu (Muhammad Ridha, NIM 230170010)  
**Sumber Primer Terverifikasi:**
1. Dokumen Tugas: `Project_Pengganti_UTS_Keamanan_Sistem_Komputer.docx` (Word terlampir)
2. PRD Perpustakaan: 14 Catatan Markdown (`00 - PRD Index.md` s.d. `13 - Glosarium dan Referensi.md` di `D:\Obsidian\Library\PRD-Perpustakaan\`)
3. Rangkaian Tiket: `00 - Indeks Tickets.md` dan `TKT-01` s.d. `TKT-31`, `OPS-01`, `OPS-02` di `D:\Obsidian\Library\PRD-Perpustakaan\Tickets\`

---

## 1. Ringkasan Eksekutif dan Batasan Proyek

### 1.1 Tema dan Penugasan
Berdasarkan digit terakhir NIM mahasiswa (NIM: `230170010`, digit terakhir: `0`), tema penugasan yang berlaku adalah **Perpustakaan**:
- **Tabel data utama:** `buku`, `anggota`, `peminjaman` (+ tabel pendukung `ulasan`).
- **Data sensitif yang dienkripsi (Syarat 3 / SEC-03):** Nomor telepon anggota (kolom `telepon_enc` pada tabel `anggota`).
- **Data yang dijaga dari IDOR (Syarat 10 / SEC-10):** Riwayat peminjaman anggota lain (akses ke `/peminjaman/{id}` dan relasi riwayat).

### 1.2 Cakupan (Scope)
- **16 Functional Requirements (FR) Kategori Must:**
  - FR-01: Registrasi tamu sebagai anggota (role otomatis `user`).
  - FR-02: Login anggota/admin dengan verifikasi hash dan regenerasi sesi.
  - FR-03: Logout anggota/admin dengan invalidasi sesi dan regenerasi token CSRF.
  - FR-04: Pencarian buku dengan parameter binding (kebal SQL Injection).
  - FR-05: Detail buku dan ulasan dengan Blade contextual escaping (kebal XSS).
  - FR-06: Penulisan ulasan teks bebas terikat identitas sesi.
  - FR-07: Peminjaman buku tersedia dengan pengurangan stok atomik dalam transaksi database.
  - FR-08: Tampilan riwayat peminjaman sendiri (hanya data milik akun aktif).
  - FR-09: Detail peminjaman sendiri terproteksi policy (penolakan 404 untuk objek pihak lain).
  - FR-10: Pembaruan nomor telepon sendiri terenkripsi AES-256-GCM.
  - FR-11: Pengelolaan katalog buku oleh admin (tambah, lihat, ubah, arsip buku bersejarah).
  - FR-12: Pengelolaan akun anggota oleh admin (tambah, lihat, ubah, nonaktifkan; lindungi admin terakhir).
  - FR-13: Pengelolaan sirkulasi peminjaman buku oleh admin.
  - FR-14: Pengelolaan pengembalian buku oleh admin (pemulihan stok idempoten).
  - FR-15: Moderasi dan penghapusan ulasan oleh admin.
  - FR-16: Penyediaan fixture akun uji fiktif dan cabang demonstrasi kerentanan lokal.
- **10 Persyaratan Keamanan (SEC) Wajib (Syarat 1–10, bobot 80 poin):**
  - SEC-01 / Syarat 1: Tabel risiko 5 aset, 5 risiko, mencakup CIA, kontrol korektif backup, alasan risiko tertinggi.
  - SEC-02 / Syarat 2: Password hash lambat + salt unik otomatis (`Hash::make` bcrypt cost 12), panjang min 8 maks 72 byte, pembuktian 2 user beda hash untuk password sama.
  - SEC-03 / Syarat 3: Enkripsi telepon AES-256-GCM, key 256-bit di luar repo (`.env`), nonce acak 12 byte baru per enkripsi, tag 16 byte, AAD `anggota:{id}:telepon:v1`.
  - SEC-04 / Syarat 4: HTTPS self-signed pada Nginx, SAN cocok dengan IP/domain lab, TLS 1.2/1.3, port 80 dialihkan atau ditutup, izin kunci privat 600.
  - SEC-05 / Syarat 5: Hardening Linux Ubuntu 24.04 LTS, admin non-root sudoer, akun layanan tanpa shell (`www-data`/`appsvc`), izin berkas rahasia 600, SSH `PermitRootLogin no` dan `MaxAuthTries 3`, layanan minimum, `APP_DEBUG=false`.
  - SEC-06 / Syarat 6: Firewall UFW incoming default deny, allow SSH dan HTTPS dari IP klien uji lab, bukti nmap sebelum/sesudah dengan listener 8080 tetap hidup saat pengujian.
  - SEC-07 / Syarat 7: Backup otomatis terjadwal via cron, enkripsi backup (`openssl enc`), hash checksum, salinan di lokasi berbeda dari VM, simulasi kehilangan data dan pembuktian restore terisolasi (durasi RTO/RPO tercatat).
  - SEC-08 / Syarat 8: Parameter binding pada seluruh query masukan pengguna; demonstrasi SQLi pencarian sebelum (vulnerable) dan sesudah (main).
  - SEC-09 / Syarat 9: Contextual output escaping pada seluruh tampilan data pengguna di Blade; demonstrasi stored XSS ulasan sebelum (alert lokal) dan sesudah (escaped text).
  - SEC-10 / Syarat 10: Server-side authorization check (policy `PeminjamanPolicy`) pada setiap request, cookie sesi HttpOnly/Secure/SameSite=Lax, regenerasi ID sesi saat login, logout mengakhiri sesi; demonstrasi IDOR detail peminjaman sebelum dan sesudah.
- **Fitur Opsional (Ditunda hingga M3 / D18):**
  - FR-17 / Should: Paginasi katalog buku (OPS-01).
  - FR-18 / Could: Filter ketersediaan stok buku (OPS-02).

### 1.3 Batasan dan Non-Goals (Dikeluarkan Secara Tegas)
Hal-hal berikut berada di luar cakupan proyek:
1. Denda keterlambatan, reservasi buku, pembayaran/transaksi finansial.
2. Pengiriman email, notifikasi email, dan reset password via email.
3. Otentikasi pihak ketiga (Google Login, OAuth, SSO).
4. Multi-faktor autentikasi (MFA).
5. Aplikasi mobile, Single Page Application (SPA), REST API publik terpisah.
6. Multi-cabang perpustakaan atau multi-tenancy.
7. Tampilan antarmuka mewah/kompleks (fokus penilaian adalah keamanan dan fungsi).
8. Penggunaan malware nyata, pengujian ke jaringan luar/kampus, atau pemindaian server orang lain (pelanggaran berakibat nilai 0).

### 1.4 Batas Lingkungan Pengujian dan Keamanan Lab
- **Lingkungan Pengujian:** Pengujian HANYA dilakukan pada laptop milik mahasiswa dan VM Ubuntu lokal pada jaringan host-only (VirtualBox/VMware).
- **Isolasi Versi Rentan (`vulnerable`):** Cabang/lingkungan rentan HANYA boleh berjalan pada loopback `127.0.0.1:8001` di mesin lokal. Dilarang keras melakukan binding ke `0.0.0.0`, dilarang membuat port forwarding VirtualBox ke port 8001, dan dilarang menambahkan reverse proxy Nginx yang mengekspos port 8001 ke jaringan host-only atau publik.
- **Pemisahan Data Lab:** Cabang `main` dan cabang `vulnerable` menggunakan basis data SQLite terpisah, nama cookie sesi terpisah, direktori sesi terpisah, dan profil peramban terpisah.

---

## 2. Stack Teknologi Baseline dan Verifikasi Kompatibilitas 2026

| Komponen | Spesifikasi Baseline | Verifikasi Dukungan Resmi (2026) | Catatan Implementasi |
|---|---|---|---|
| **Sistem Operasi VM** | Ubuntu 24.04 LTS (Noble Numbat) | Rilis LTS aktif, dukungan standar hingga 2029 | Menggunakan paket APT standar, user non-root + akun layanan |
| **Runtime Bahasa** | PHP 8.3 (Spesifikasi Konkret) | Paket standar Ubuntu 24.04 LTS adalah PHP 8.3. Dukungan keamanan PHP 8.2 berakhir 31 Desember 2026 | Gunakan PHP 8.3 bawaan repositori resmi Ubuntu 24.04 |
| **Web Framework** | Laravel 12 (Keputusan Konkret) | Rilis 24 Februari 2025; perbaikan bug hingga 13 Agustus 2026; dukungan keamanan aktif hingga 24 Februari 2027. Laravel 11 berakhir perbaikan keamanan pada 12 Maret 2026 sehingga tidak dijadikan fallback otomatis untuk proyek baru | Tetapkan Laravel 12 sebagai versi konkret. Fallback Laravel 11 hanya digunakan jika diinstruksikan tertulis oleh dosen |
| **Basis Data** | SQLite 3 | Built-in PHP PDO SQLite, isolasi file lokal | Disimpan di luar direktori web (`database/database.sqlite`), WAL enabled |
| **Web Server / Proxy** | Nginx + PHP-FPM | Nginx mainline/stable, Unix domain socket FastCGI | Document root strictly `/public`, penolakan akses dotfiles & sqlite |
| **Template Engine** | Blade | Bawaan Laravel, auto-escaping `{{ }}` | Contextual HTML escaping aktif secara default |
| **Icon Library** | Blade Icons | [Blade Icons](https://blade-ui-kit.com/blade-icons) | Standar icon SVG pada Blade view jika fitur memerlukan icon |
| **Kriptografi Password** | Bcrypt (`Hash::make`) | Standar default Laravel, salt unik otomatis | Cost factor default 12, validasi panjang 8–72 byte |
| **Kriptografi Data** | AES-256-GCM (`openssl_encrypt`) | Standar OpenSSL AEAD | Key 32 byte base64 via .env, nonce 12 byte unik, tag 16 byte, AAD anggota |
| **Firewall** | UFW (Uncomplicated Firewall) | Bawaan Ubuntu Server | Policy incoming default deny, outgoing allow, rate limit SSH |
| **Penjadwal Backup** | Cron (`cron` / `crontab`) | Utilitas standar Linux | Sesuai dokumen tugas: eksekusi script bash harian via crontab |

---

## 3. Matriks Keputusan Q-01 sampai Q-10

| Kode | Pertanyaan / Masalah | Sumber & Ketentuan Terverifikasi | Status Keputusan | Rekomendasi & Nilai Terpilih | Tiket Pemakai Utama | Pekerjaan yang Diblokir |
|---|---|---|---|---|---|---|
| **Q-01** | Versi Laravel dan PHP aktual yang diizinkan dosen? | Dokumen Tugas membebaskan framework (PHP/Laravel). PRD menetapkan PHP 8.2+, Laravel 11/12. Ubuntu 24.04 membawa PHP 8.3. Per 16 Nov 2026, Laravel 11 sudah EOL security (12 Maret 2026), sedangkan Laravel 12 didukung hingga 24 Feb 2027. | **Terkonfirmasi Konkret: PHP 8.3 + Laravel 12** | **Laravel 12 pada PHP 8.3** ditetapkan sebagai keputusan konkret untuk instalasi TKT-02. Fallback Laravel 11 tidak otomatis dipakai kecuali ada instruksi tertulis dosen. | [[PRD-Perpustakaan/Tickets/TKT-01 - Tetapkan baseline dan keputusan proyek\|TKT-01]], [[PRD-Perpustakaan/Tickets/TKT-02 - Siapkan fondasi Laravel dan repositori\|TKT-02]], [[PRD-Perpustakaan/Tickets/TKT-18 - Siapkan VM deployment dan hardening Linux\|TKT-18]] | Inisialisasi awal repositori di TKT-02 (`composer create-project`). Tidak ada pemblokir teknis tersisa. |
| **Q-02** | Tanggal mulai D1, tenggat pengumpulan, dan tanggal tanya jawab sebenarnya? | Dokumen Tugas menetapkan durasi 3 minggu pengerjaan + 1 minggu tanya jawab. Tanggal baseline 16 Nov 2026 adalah tanggal penyusunan dokumen, bukan tanggal mulai D1 yang disahkan. | **Tertunda (Jadwal Relatif D1–D28 Aktif)** | Tanggal kalender absolut D1 dan tenggat resmi menunggu konfirmasi akademik/dosen. Pengerjaan operasional menggunakan skala relatif D1–D21 (Minggu 1: D1-D7, Minggu 2: D8-D14, Minggu 3: D15-D21) dan D22-D28 untuk tanya jawab. | [[PRD-Perpustakaan/Tickets/TKT-01 - Tetapkan baseline dan keputusan proyek\|TKT-01]], [[PRD-Perpustakaan/Tickets/TKT-30 - Verifikasi paket bersih dan siapkan pengumpulan\|TKT-30]], [[PRD-Perpustakaan/Tickets/TKT-31 - Latih tanya jawab dan pemulihan demo\|TKT-31]] | Tanggal absolut pengumpulan dan jadwal sesi presentasi dosen. Tidak memblokir TKT-02. |
| **Q-03** | Batas buku aktif, durasi pinjam, aturan pinjam ulang, dan batas 1 ulasan per buku? | Dokumen Tugas tidak membatasi kuota pinjam/ulasan. Workspace Memory: batas pinjam belum ditetapkan, jangan buat tenggat otomatis. Kuota belum diputuskan bukan berarti "tanpa batas" disetujui. | **Tertunda (Desain Fleksibel / Nullable Aktif)** | Kolom `jatuh_tempo` pada database dibuat **nullable** (tidak menetapkan tenggat otomatis sebelum ada kebijakan). Kuota buku, aturan perpanjangan (renew), dan pembatasan jumlah ulasan per buku dicatat tertunda; schema awal tidak memasang unique constraint `(anggota_id, buku_id)` agar tidak mengunci kebijakan prematur. | [[PRD-Perpustakaan/Tickets/TKT-04 - Buat schema database dan relasi model\|TKT-04]], [[PRD-Perpustakaan/Tickets/TKT-10 - SYR-9 - Implementasikan ulasan dengan output escaping\|TKT-10]], [[PRD-Perpustakaan/Tickets/TKT-11 - SYR-10 - Implementasikan peminjaman dan transaksi stok\|TKT-11]], [[PRD-Perpustakaan/Tickets/TKT-16 - SYR-10 - Bangun administrasi pinjam dan pengembalian\|TKT-16]] | Validasi aturan bisnis kuota pinjam, perpanjangan, dan constraint ulasan. Tidak memblokir TKT-02. |
| **Q-04** | Nama pengguna akun VM sesuai nama depan dan 4 digit terakhir NIM? | Dokumen Tugas: "Setiap bukti menampilkan nama pengguna VM (namadepan + 4 digit terakhir NIM)". Mahasiswa: Muhammad Ridha (NIM 230170010). 4 digit NIM: `0010`. | **Usulan / Draft (Menunggu Konfirmasi Pilihan Nama Depan)** | Usulan utama akun OS VM: **`ridha0010`** (nama panggilan); alternatif: **`muhammad0010`** (kata pertama). Diputuskan sebelum provisioning VM TKT-18. Akun admin aplikasi web fiktif: `admin` (Rani). | [[PRD-Perpustakaan/Tickets/TKT-18 - Siapkan VM deployment dan hardening Linux\|TKT-18]], [[PRD-Perpustakaan/Tickets/TKT-27 - Audit kelengkapan bukti dan rahasia\|TKT-27]] | Pembuatan akun user di OS Linux VM pada TKT-18. Tidak memblokir TKT-02. |
| **Q-05** | Topologi jaringan, alokasi IP host/VM, port SSH, dan lokasi backup di luar VM? | Dokumen Tugas: VM Ubuntu pada jaringan host-only/internal. Backup disimpan di lokasi berbeda (laptop/folder terpisah/flash disk). | **Draft Terkonfirmasi Struktur (IP Mengikuti Hypervisor)** | Jaringan Host-Only: Host `192.168.56.1`, VM `192.168.56.10`. Port SSH: `22` (atau custom lab). HTTPS: `443`. Backup dipindahkan ke direktori host via scp/shared folder aman. | [[PRD-Perpustakaan/Tickets/TKT-18 - Siapkan VM deployment dan hardening Linux\|TKT-18]], [[PRD-Perpustakaan/Tickets/TKT-19 - SYR-10 - Konfigurasikan HTTPS dan verifikasi cookie aman\|TKT-19]], [[PRD-Perpustakaan/Tickets/TKT-20 - Terapkan firewall default deny dan uji perbandingan\|TKT-20]], [[PRD-Perpustakaan/Tickets/TKT-21 - Otomatisasikan backup dan buktikan restore\|TKT-21]], [[PRD-Perpustakaan/Tickets/TKT-22 - SYR-8 SYR-9 SYR-10 - Siapkan cabang vulnerable yang terisolasi\|TKT-22]] | Penulisan IP spesifik pada SAN SSL, rules UFW, dan konfigurasi transfer backup. |
| **Q-06** | Ketentuan dosen untuk tabel risiko, batasan 6 halaman laporan, dan rubrik penilaian? | Dokumen Tugas memverifikasi: Tabel risiko 5 aset/5 risiko; Laporan maks 6 halaman isi (+ lampiran maks 4); Video maks 5 menit; Rubrik: 10 syarat @ 8 poin = 80, Laporan 5, Video 5, Tanya Jawab 10. | **Terkonfirmasi Penuh** | Mengikuti struktur persis dokumen tugas: Tabel risiko 5 aset di TKT-03, pembatasan 6 halaman isi di TKT-28, video 5 menit di TKT-29. | [[PRD-Perpustakaan/Tickets/TKT-03 - Susun tabel risiko dan struktur bukti\|TKT-03]], [[PRD-Perpustakaan/Tickets/TKT-27 - Audit kelengkapan bukti dan rahasia\|TKT-27]], [[PRD-Perpustakaan/Tickets/TKT-28 - Tulis laporan PDF berdasarkan hasil aktual\|TKT-28]], [[PRD-Perpustakaan/Tickets/TKT-29 - Rekam video demonstrasi lima menit\|TKT-29]], [[PRD-Perpustakaan/Tickets/TKT-30 - Verifikasi paket bersih dan siapkan pengumpulan\|TKT-30]] | Tidak ada yang diblokir. |
| **Q-07** | Parameter backup (jadwal, retensi, RPO/RTO) dan ketidaksesuaian cron vs service/timer? | Dokumen Tugas Bagian 2 Syarat 7 secara eksplisit mewajibkan **cron** (`crontab -l`) dan script shell `tar + openssl enc + sha256sum`. TKT-21 sempat merencanakan systemd timer. | **Terkonfirmasi (Wajib Gunakan Cron Sesuai Dokumen Tugas)** | **Implementasi utama menggunakan Cron** (`crontab`). Jadwal draft harian (02:00 VM), retensi 7 hari, target RPO ≤ 24 jam, target RTO ≤ 30 menit lab. Systemd timer dicatat sebagai alternatif sekunder. | [[PRD-Perpustakaan/Tickets/TKT-21 - Otomatisasikan backup dan buktikan restore\|TKT-21]] | Penulisan crontab dan script backup di TKT-21. |
| **Q-08** | Hak akses admin membaca nomor telepon terenkripsi dan riwayat anggota? | Dokumen Tugas Bagian 1: "Ada dua peran: admin (bisa melihat dan mengelola semua data) dan pengguna biasa (hanya data miliknya)." PRD: Admin mengelola data operasional. | **Terkonfirmasi** | Admin diperbolehkan melihat data sirkulasi dan mendekripsi telepon anggota untuk kebutuhan operasional perpustakaan. Pengguna biasa hanya boleh melihat data miliknya (policy enforced). | [[PRD-Perpustakaan/Tickets/TKT-12 - SYR-10 - Implementasikan riwayat dan policy pemilik\|TKT-12]], [[PRD-Perpustakaan/Tickets/TKT-13 - SYR-3 SYR-10 - Implementasikan AES-GCM dan profil telepon\|TKT-13]], [[PRD-Perpustakaan/Tickets/TKT-15 - SYR-2 SYR-3 SYR-10 - Bangun pengelolaan anggota oleh admin\|TKT-15]], [[PRD-Perpustakaan/Tickets/TKT-25 - SYR-10 - Buktikan IDOR dan sesi sebelum dan sesudah\|TKT-25]] | Tidak ada yang diblokir; implementasi policy mencakup peran admin. |
| **Q-09** | Mekanisme hapus buku dan nonaktif anggota bersejarah untuk menjaga integritas transaksi? | PRD Data Model & Workspace Memory: buku bersejarah diarahkan ke arsip (`archived_at`); anggota bersejarah dinonaktifkan (`is_active = false`); FK restrict aktif. | **Terkonfirmasi** | Buku tanpa transaksi boleh dihapus permanen; buku dengan riwayat hanya boleh diarsipkan (`archived_at` terisi). Anggota tidak dihapus fisik melainkan dinonaktifkan. Admin terakhir dilindungi dari penonaktifan. | [[PRD-Perpustakaan/Tickets/TKT-04 - Buat schema database dan relasi model\|TKT-04]], [[PRD-Perpustakaan/Tickets/TKT-14 - SYR-10 - Bangun pengelolaan buku oleh admin\|TKT-14]], [[PRD-Perpustakaan/Tickets/TKT-15 - SYR-2 SYR-3 SYR-10 - Bangun pengelolaan anggota oleh admin\|TKT-15]] | Tidak ada yang diblokir. |
| **Q-10** | Keputusan pengerjaan dua fitur opsional (FR-17 Pagination & FR-18 Filter Stok)? | Dokumen Tugas & PRD Index: Prioritaskan 16 FR Must dan 10 SEC. Fitur opsional (OPS-01 & OPS-02) hanya dikerjakan jika waktu tersisa sebelum laporan dibekukan. | **Tertunda hingga Milestone M3 (D18)** | Keputusan final pengerjaan OPS-01/OPS-02 diambil pada D18 setelah audit SEC-01 s.d. SEC-10 lulus. Jika waktu sempit, tandai status `dikeluarkan`. | [[PRD-Perpustakaan/Tickets/TKT-27 - Audit kelengkapan bukti dan rahasia\|TKT-27]], [[PRD-Perpustakaan/Tickets/OPS-01 - Tambahkan pagination katalog\|OPS-01]], [[PRD-Perpustakaan/Tickets/OPS-02 - Tambahkan filter buku tersedia\|OPS-02]] | Pelaksanaan tiket OPS-01 dan OPS-02 ditahan sampai gerbang M3. |

---

## 4. Pemetaan Syarat Tugas ke Tiket Obsidian dan Penanda Website

| Syarat Dosen | Kebutuhan Dokumen Tugas | Komponen Kontrol Teknis | Tiket Implementasi | Tiket Verifikasi & Bukti | Penanda Website |
|---|---|---|---|---|---|
| **Syarat 1 / SEC-01** | Tabel risiko 5 aset, 5 risiko, CIA, kontrol minimal 5 syarat, kontrol korektif backup, alasan risiko tertinggi. | Dokumentasi Risk Register & Traceability | [[PRD-Perpustakaan/Tickets/TKT-03 - Susun tabel risiko dan struktur bukti\|TKT-03]] | [[PRD-Perpustakaan/Tickets/TKT-27 - Audit kelengkapan bukti dan rahasia\|TKT-27]], [[PRD-Perpustakaan/Tickets/TKT-28 - Tulis laporan PDF berdasarkan hasil aktual\|TKT-28]] | *(Tidak diberikan)* |
| **Syarat 2 / SEC-02** | Hash lambat + salt unik per user, verifikasi login, panjang min 8 byte, bukti 2 user password sama memiliki hash berbeda. | `Hash::make` (bcrypt cost 12), `Auth::attempt`, hidden password, FormRequest validation. | [[PRD-Perpustakaan/Tickets/TKT-05 - SYR-2 - Siapkan fixture akun dan bukti hash salt\|TKT-05]], [[PRD-Perpustakaan/Tickets/TKT-06 - SYR-2 - Implementasikan registrasi anggota\|TKT-06]], [[PRD-Perpustakaan/Tickets/TKT-07 - SYR-2 SYR-10 - Implementasikan login dan logout manual\|TKT-07]], [[PRD-Perpustakaan/Tickets/TKT-15 - SYR-2 SYR-3 SYR-10 - Bangun pengelolaan anggota oleh admin\|TKT-15]] | [[PRD-Perpustakaan/Tickets/TKT-17 - SYR-2 SYR-3 SYR-9 SYR-10 - Implementasikan moderasi ulasan dan audit aplikasi dasar\|TKT-17]], [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | `SYR-2` |
| **Syarat 3 / SEC-03** | Satu data sensitif (telepon) dienkripsi AES-256-GCM, key 256-bit di luar kode (.env), nonce acak 12 byte unik per baris, tag 16 byte, dekripsi hanya user berhak. | Service `AesGcm` (openssl_encrypt/decrypt), envelope JSON (v1, nonce, ciphertext, tag), AAD `anggota:{id}:telepon:v1`. | [[PRD-Perpustakaan/Tickets/TKT-13 - SYR-3 SYR-10 - Implementasikan AES-GCM dan profil telepon\|TKT-13]], [[PRD-Perpustakaan/Tickets/TKT-15 - SYR-2 SYR-3 SYR-10 - Bangun pengelolaan anggota oleh admin\|TKT-15]] | [[PRD-Perpustakaan/Tickets/TKT-17 - SYR-2 SYR-3 SYR-9 SYR-10 - Implementasikan moderasi ulasan dan audit aplikasi dasar\|TKT-17]], [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | `SYR-3` |
| **Syarat 4 / SEC-04** | HTTPS self-signed pada Nginx, SAN cocok, redirect HTTP atau port 80 tutup, izin private key 600, tidak commit key. | Nginx SSL configuration, OpenSSL certificate generation script, TLS 1.2/1.3. | [[PRD-Perpustakaan/Tickets/TKT-19 - SYR-10 - Konfigurasikan HTTPS dan verifikasi cookie aman\|TKT-19]] | [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | *(Pekerjaan Server / Nginx)* |
| **Syarat 5 / SEC-05** | Admin non-root sudoer, akun layanan nologin/tanpa shell, permission berkas rahasia 600, SSH PermitRootLogin no & MaxAuthTries 3, sistem terbarui, layanan minimum. | Linux user management, permissioning storage & SQLite, hardening `sshd_config`, apt updates. | [[PRD-Perpustakaan/Tickets/TKT-18 - Siapkan VM deployment dan hardening Linux\|TKT-18]] | [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | *(Pekerjaan Server / Linux)* |
| **Syarat 6 / SEC-06** | Firewall incoming default deny, allow SSH & HTTPS terbatas ke IP klien uji, nmap sebelum/sesudah dengan listener 8080 tetap hidup saat pengujian. | UFW rules configuration, perbandingan port terbuka nmap, penutupan port 80/8080/8001 dari luar. | [[PRD-Perpustakaan/Tickets/TKT-20 - Terapkan firewall default deny dan uji perbandingan\|TKT-20]] | [[PRD-Perpustakaan/Tickets/TKT-18 - Siapkan VM deployment dan hardening Linux\|TKT-18]], [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | *(Pekerjaan Server / Firewall)* |
| **Syarat 7 / SEC-07** | Backup otomatis terjadwal via cron, enkripsi backup (openssl enc), hash checksum, simpan di luar VM, simulasi hapus data dan uji restore terverifikasi. | Script `backup.sh` (SQLite Online Backup + tar + openssl enc), penjadwalan `crontab -e`, script restore & integrity check. | [[PRD-Perpustakaan/Tickets/TKT-21 - Otomatisasikan backup dan buktikan restore\|TKT-21]] | [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | *(Pekerjaan Server / Backup)* |
| **Syarat 8 / SEC-08** | Seluruh kueri masukan pengguna memakai prepared statement / parameter binding; demonstrasi SQLi pencarian sebelum (berhasil meluas) dan sesudah (gagal/literal). | Eloquent / Query Builder binding, allowlist parameter sorting/filtering. Cabang vulnerable merakit SQL concatenation. | [[PRD-Perpustakaan/Tickets/TKT-09 - SYR-8 SYR-9 - Bangun katalog pencarian dan detail buku\|TKT-09]] | [[PRD-Perpustakaan/Tickets/TKT-22 - SYR-8 SYR-9 SYR-10 - Siapkan cabang vulnerable yang terisolasi\|TKT-22]], [[PRD-Perpustakaan/Tickets/TKT-23 - SYR-8 - Buktikan SQL Injection sebelum dan sesudah\|TKT-23]], [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | `SYR-8` |
| **Syarat 9 / SEC-09** | Output escaping pada seluruh tempat data pengguna ditampilkan; demonstrasi stored XSS ulasan sebelum (alert muncul) dan sesudah (skrip tampil sebagai teks biasa). | Blade syntax `{{ $ulasan->isi }}` tanpa unescaped `{!! !!}`, ditambah CSP header sebagai lapisan pertahanan sekunder. | [[PRD-Perpustakaan/Tickets/TKT-09 - SYR-8 SYR-9 - Bangun katalog pencarian dan detail buku\|TKT-09]], [[PRD-Perpustakaan/Tickets/TKT-10 - SYR-9 - Implementasikan ulasan dengan output escaping\|TKT-10]] | [[PRD-Perpustakaan/Tickets/TKT-22 - SYR-8 SYR-9 SYR-10 - Siapkan cabang vulnerable yang terisolasi\|TKT-22]], [[PRD-Perpustakaan/Tickets/TKT-24 - SYR-9 - Buktikan stored XSS sebelum dan sesudah\|TKT-24]], [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | `SYR-9` |
| **Syarat 10 / SEC-10** | Pemeriksaan kepemilikan data di sisi server pada setiap request (tolak IDOR), cookie HttpOnly/Secure/SameSite=Lax, rotasi sesi login, logout invalidasi sesi. | Policy `PeminjamanPolicy::view` (`denyAsNotFound`), session configuration `config/session.php`, CSRF middleware, session regenerate. | [[PRD-Perpustakaan/Tickets/TKT-07 - SYR-2 SYR-10 - Implementasikan login dan logout manual\|TKT-07]], [[PRD-Perpustakaan/Tickets/TKT-08 - SYR-10 - Terapkan middleware peran akun aktif dan sesi\|TKT-08]], [[PRD-Perpustakaan/Tickets/TKT-12 - SYR-10 - Implementasikan riwayat dan policy pemilik\|TKT-12]] | [[PRD-Perpustakaan/Tickets/TKT-19 - SYR-10 - Konfigurasikan HTTPS dan verifikasi cookie aman\|TKT-19]], [[PRD-Perpustakaan/Tickets/TKT-22 - SYR-8 SYR-9 SYR-10 - Siapkan cabang vulnerable yang terisolasi\|TKT-22]], [[PRD-Perpustakaan/Tickets/TKT-25 - SYR-10 - Buktikan IDOR dan sesi sebelum dan sesudah\|TKT-25]], [[PRD-Perpustakaan/Tickets/TKT-26 - SYR-2 SYR-3 SYR-8 SYR-9 SYR-10 - Jalankan verifikasi menyeluruh fitur dan keamanan\|TKT-26]] | `SYR-10` |

---

## 5. Rekonsiliasi Ketidaksesuaian Kritis (Gotchas & Discrepancies)

1. **Ketidaksesuaian Penjadwalan Backup (Tugas vs TKT-21):**
   - *Fakta:* Dokumen tugas resmi Bagian 2 Syarat 7 secara eksplisit meminta **cron** dan bukti keluaran `crontab -l`. Di sisi lain, rencana awal TKT-21 menyebut `library-backup.service` dan `systemd.timer`.
   - *Keputusan:* TKT-21 **wajib diimplementasikan menggunakan cron** (`crontab -e` menjalankan `/opt/library/scripts/backup.sh`) agar memenuhi rubrik penilaian dosen secara langsung. Dokumen systemd timer hanya dijadikan catatan teknis alternatif sekunder.
2. **Penamaan Environment Cookie Sesi (Dokumen Tugas vs Laravel Skeleton):**
   - *Fakta:* Dokumen tugas menyebut nama variabel konseptual `SESSION_COOKIE_HTTPONLY=True` (gaya Flask). Namun pada Laravel bawaan, variabel environment untuk cookie sesi adalah `SESSION_HTTP_ONLY`, `SESSION_SECURE_COOKIE`, dan `SESSION_SAME_SITE`.
   - *Keputusan:* Pada TKT-08 dan TKT-19, buat pemetaan eksplisit di `config/session.php` agar membaca kedua opsi env jika diperlukan, dan pastikan hasil pengujian `config('session.http_only')` serta atribut header HTTP `Set-Cookie` nyata terbukti memiliki flag `HttpOnly; Secure; SameSite=Lax`.
3. **Pemisahan Pengujian Keamanan Independen:**
   - *XSS:* Pengujian escaping Blade `{{ }}` harus dibuktikan pada response HTML mentah secara terpisah dari perlindungan Content-Security-Policy (CSP), membuktikan bahwa escaping adalah kontrol primer.
   - *Firewall:* Selama pengujian penolakan firewall pada port 8080, listener uji pada VM harus tetap dibiarkan aktif agar kegagalan koneksi terbukti berasal dari blokade firewall UFW (bukan karena port mati).
4. **Pencegahan Race Condition pada Stok Buku:**
   - *Fakta:* SQLite tidak mendukung row-level locking standar (`SELECT ... FOR UPDATE`).
   - *Keputusan:* Layanan peminjaman (TKT-11) wajib menggunakan atomic update bersyarat (`UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id = ? AND stok_tersedia > 0`) di dalam transaksi SQLite untuk mencegah race condition peminjaman stok terakhir.

---

## 6. Kesiapan Menuju TKT-02 (Gerbang Fondasi Laravel)

Dengan diselesaikannya baseline dan pemetaan keputusan ini:
- Batasan ruang lingkup 16 FR Must dan 10 SEC telah terkunci.
- Konfigurasi stack telah terverifikasi: PHP 8.3 (didukung aktif Ubuntu 24.04 LTS) + Laravel 12 (dukungan keamanan aktif hingga 24 Februari 2027) + SQLite + Blade + Nginx.
- Pemetaan seluruh pertanyaan Q-01 s.d. Q-10 telah terdokumentasi dengan pemblokir dan mitigasi yang jelas.
- Tidak ada fitur di luar scope (denda, email, payment, OAuth) yang diimplementasikan.
- Tahap berikutnya adalah **TKT-02: Siapkan fondasi Laravel dan repositori**, yang dapat dieksekusi dengan aman tanpa spekulasi arsitektur.
