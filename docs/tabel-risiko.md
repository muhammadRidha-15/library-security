# Tabel Risiko Keamanan Sistem Perpustakaan Mini (SEC-01 / Syarat 1)

**Proyek:** Aplikasi Web Perpustakaan Mini yang Diamankan di Server Linux  
**Mata Kuliah:** Keamanan Sistem Komputer (Project Pengganti UTS)  
**Pelaksana:** Muhammad Ridha (NIM: 230170010)  
**Dokumen Acuan:** PRD Perpustakaan (`08 - Tabel Risiko.md`, `04 - Persyaratan Keamanan.md`, `11 - Checklist Bukti dan Pengumpulan.md`)  
**Status Dokumen:** Analisis risiko dan target kontrol; status implementasi diverifikasi terpisah  
**Tanggal Penetapan:** 16 November 2026  

---

## Ringkasan tabel risiko untuk laporan

Tabel berikut merupakan versi ringkas syarat 1. Dampak dinilai secara **kualitatif** berdasarkan potensi akibat terhadap proyek, bukan perhitungan probabilitas. Kelemahan menggambarkan keadaan yang dapat terjadi bila kontrol tidak diterapkan; kolom kontrol mencantumkan kontrol yang diterapkan atau direncanakan, **bukan klaim semua pengujian sudah lulus**. Sesuaikan hasil PDF agar tabel muat maksimal satu halaman dan tetap terbaca.

| Aset | Ancaman | Kelemahan | Dampak | CIA | Kontrol | Syarat |
|---|---|---|---|---|---|---|
| Akun dan sesi anggota | Pengambilalihan akun admin atau anggota | Hash lemah, sesi tanpa perlindungan atau komunikasi tanpa HTTPS | **Tinggi** — penyerang dapat mengubah data dan bertindak sebagai pengguna | C, I | Hash dan salt; HTTPS; pengamanan cookie dan sesi | 2, 4, 10 |
| Nomor telepon anggota | Pembacaan atau perubahan data kontak tanpa izin | Telepon disimpan tanpa enkripsi atau kunci bocor | **Tinggi** — privasi kontak dan keutuhan data terancam | C, I | AES-256-GCM; izin file terbatas; perlindungan kunci pemulihan | 3, 5, 7 |
| Riwayat peminjaman | Anggota mengakses transaksi orang lain atau menyisipkan input SQL | Otorisasi pemilik tidak diperiksa atau query dikonkatenasi | **Tinggi** — riwayat pihak lain dapat terbuka atau data dimanipulasi | C, I | Policy pemilik/admin dan query berparameter | 8, 10 |
| Katalog dan ulasan | Skrip tersimpan dijalankan di browser pembaca | Teks ulasan ditampilkan tanpa escaping | **Tinggi** — browser korban dapat menjalankan aksi tidak sah | C, I | Escaping Blade; CSP tambahan; moderasi admin | 9, 10 |
| Server VM dan database | Layanan diserang atau database hilang/rusak | Hak layanan berlebih, port tidak dibatasi atau backup belum diuji | **Tinggi** — layanan berhenti dan riwayat dapat hilang | A, I | Hardening; UFW default deny; **korektif:** restore dari backup terenkripsi terjadwal di lokasi terpisah | 5, 6, 7 |

**Keterangan:** C = kerahasiaan, I = keutuhan, A = ketersediaan. Kelima dampak diberi label tinggi sebagai penilaian awal yang konservatif atas kemungkinan akibat; tingkat risiko akhir tetap perlu ditinjau terhadap konteks lab dan hasil uji. Operator host-only yang tercatat adalah `192.168.136.1`; alamat VM tercatat `192.168.136.128` dan masih bergantung DHCP.

### Risiko tertinggi dan alasan

Risiko yang diprioritaskan adalah **pengambilalihan akun administrator**, karena admin dapat mengelola buku, anggota, peminjaman dan ulasan. Jika akun tersebut disalahgunakan, penyerang berpotensi membaca atau mengubah data sekaligus mengganggu layanan perpustakaan. Hash password dengan salt, HTTPS, serta pengamanan dan penghentian sesi dipilih sebagai lapisan kontrol untuk mengurangi risiko tersebut. Efektivitas masing-masing kontrol tetap harus dibuktikan melalui hasil pengujian aktual sebelum dinyatakan memenuhi tugas.

### Status bukti yang perlu dicocokkan

- Firewall dan akses HTTPS telah memiliki hasil yang ditunjukkan dalam percakapan; bukti tersimpan serta konfigurasi akhir tetap perlu diaudit.
- Backup manual terenkripsi, kecocokan hash dan pemeriksaan database hasil restore telah dilaporkan; jadwal cron, run terjadwal serta login/riwayat/dekripsi pada instance restore belum terbukti dalam pemeriksaan ini.
- Demo sebelum/sesudah untuk SQLi, XSS dan IDOR/sesi memerlukan bukti aktual; jangan menganggap status lulus dari keberadaan kode saja.

---

## 1. Pendahuluan dan Lingkup Risk Register

Dokumen ini mendokumentasikan analisis risiko keamanan (Risk Register) sistem informasi perpustakaan mini sesuai pemenuhan **SEC-01 (Syarat 1)**. Analisis mencakup 5 aset utama (A1 s.d. A5) dan 5 skenario risiko kritis yang merefleksikan seluruh aspek pada CIA triad (*Confidentiality*, *Integrity*, *Availability*).

Setiap risiko dipetakan ke kontrol keamanan teknis yang merujuk minimal 5 syarat keamanan berbeda dari spesifikasi proyek (mencakup SEC-02 hingga SEC-10), dengan penegasan eksplisit mekanisme *restore* dari backup otomatis sebagai **kontrol korektif** pemulihan data.

---

## 2. Matriks Tabel Risiko (5 Aset, 5 Risiko, Triad CIA)

| ID / Aset | Ancaman (*Threat*) | Kelemahan (*Vulnerability*) | Dampak (*Impact*) | Prinsip CIA | Kontrol Keamanan Teknis | Syarat Terkait |
|---|---|---|---|---|---|---|
| **A1 — Kredensial dan sesi anggota (admin & user)** | Pengambilalihan akun melalui pencurian kredensial password atau pembajakan sesi aktif (*account takeover / session hijacking*) | Password disimpan plaintext atau hash lemah; cookie sesi tanpa proteksi atribut keamanan; sesi tidak dirotasi pasca-login; transmisi data melalui HTTP terbuka (*cleartext*) | Penyerang bertindak sebagai anggota atau admin sah; pembajakan wewenang administratif; manipulasi data katalog, data anggota, dan transaksi sirkulasi | **Confidentiality (C), Integrity (I)** | 1. Hash password lambat `Hash::make()` (Bcrypt default cost 12) dengan salt unik otomatis terkelola per hash.<br>2. Validasi password minimal 12 karakter dan maksimal 72 byte UTF-8 guna mencegah pemotongan diam-diam (*silent truncation*).<br>3. Transport Security HTTPS (TLS 1.2/1.3) self-signed.<br>4. Atribut cookie sesi ketat: `Secure = true`, `HttpOnly = true`, `SameSite = Lax`.<br>5. Regenerasi session ID saat login (`session()->regenerate()`) dan invalidasi total saat logout (`session()->invalidate()`).<br>6. Pembatasan percobaan login (*rate limiting / throttle*). | **SEC-02, SEC-04, SEC-10** |
| **A2 — Nomor telepon anggota (PII / Data Pribadi Sensitif)** | Pembacaan basis data secara tidak sah (*data breach*) atau modifikasi ciphertext kontak anggota oleh penyerang | Penyimpanan nomor telepon plaintext di tabel database atau enkripsi tanpa autentikasi integritas data (mode CBC/ECB); kunci enkripsi disimpan bersama database | Kebocoran data pribadi kontak anggota (pelanggaran kerahasiaan) dan pemalsuan nomor telepon anggota (penipuan/rekayasa identitas) | **Confidentiality (C), Integrity (I)** | 1. Enkripsi tersentralisasi AES-256-GCM (`App\Services\AesGcm.php`) menggunakan `OPENSSL_RAW_DATA`.<br>2. Nonce acak kriptografis 12 byte (`random_bytes(12)`) per enkripsi (larangan keras *nonce reuse*).<br>3. Tag autentikasi 16 byte guna mendeteksi *tampering*.<br>4. AAD (*Additional Authenticated Data*) mengikat identitas anggota: `anggota:{id}:telepon:v1`.<br>5. Kunci 256-bit disimpan terisolasi di file konfigurasi `.env` (`TELEPON_KEY_V1`) di luar database.<br>6. Format penyimpanan JSON envelope versioned (`telepon_enc`).<br>7. Dekripsi gagal/rusak menolak secara aman (*fail-secure*) tanpa membocorkan pesan error OpenSSL. | **SEC-03, SEC-05, SEC-07** |
| **A3 — Riwayat peminjaman buku** | Pengguna membaca/memanipulasi transaksi peminjaman milik anggota lain dengan mengganti parameter URL (IDOR) atau ekstraksi basis data melalui injeksi SQL pada fitur pencarian buku | Pengecekan otorisasi tingkat objek (*authorization policy*) tidak dipanggil di controller; cakupan relasi kueri tidak mengikat ID pemilik; kueri pencarian memakai konkatenasi string SQL mentah | Riwayat peminjaman anggota lain terbongkar (pelanggaran privasi baca); integritas kueri basis data rusak atau seluruh data tabel diekstraksi penyerang | **Confidentiality (C), Integrity (I)** | 1. Pemeriksaan otorisasi server-side via `PeminjamanPolicy::view` yang memvalidasi kepemilikan transaksi.<br>2. Akses silang tidak sah ditolak dengan status HTTP 404 (`Response::denyAsNotFound()`) tanpa membocorkan eksistensi data.<br>3. Kueri riwayat dibatasi (*scoped*) ke ID user aktif dari sesi (`$request->user()->peminjaman`).<br>4. Seluruh kueri basis data menggunakan Eloquent Query Builder dengan *parameter binding* otomatis.<br>5. Allowlist ketat untuk sorting dan filter katalog.<br>6. Pengujian regresi IDOR dan SQL Injection (membandingkan respons payload `' OR 1=1 -- `). | **SEC-08, SEC-10** |
| **A4 — Konten katalog buku dan ulasan anggota** | Penyerang menyisipkan skrip berbahaya (*Stored Cross-Site Scripting / XSS*) pada form ulasan buku yang dieksekusi pada peramban pengunjung lain atau admin | Perenderan teks ulasan langsung secara mentah (*raw HTML output*) tanpa *contextual escaping* (`{!! !!}`); ketiadaan validasi panjang dan format input | Sesi pengguna atau admin dibajak via pencurian token di browser; manipulasi tampilan (*defacement*) katalog buku; eksekusi aksi berbahaya atas nama korban | **Confidentiality (C), Integrity (I)** | 1. *Contextual output escaping* bawaan Blade menggunakan kurung kurawal ganda `{{ $ulasan->isi }}`.<br>2. Larangan mutlak sintaks raw `{!! !!}` untuk seluruh input berasal dari pengguna.<br>3. Validasi panjang karakter ulasan di sisi server.<br>4. Penerapan header *Content Security Policy* (CSP) ketat (`default-src 'self'; script-src 'self'; object-src 'none'`).<br>5. Hak akses moderasi dan penghapusan ulasan berbahaya oleh administrator. | **SEC-09, SEC-10** |
| **A5 — Layanan server Linux (VM) dan integritas database perpustakaan (SQLite)** | Akses tidak sah ke port layanan internal; eksploitasi salah konfigurasi server web; kehilangan atau kerusakan berkas database akibat kegagalan storage atau serangan perusakan data | Port jaringan dibuka tanpa filter; proses web berjalan dengan hak istimewa berlebih (*root*); ketiadaan firewall; ketiadaan mekanisme pencadangan otomatis dan prosedur pemulihan (*restore*) yang terverifikasi | Layanan perpustakaan terhenti total (*Denial of Service*); basis data transaksi hilang permanen tanpa dapat dipulihkan | **Availability (A), Integrity (I)** | 1. Hardening OS Linux Ubuntu 24.04 LTS (akun operator non-root `ridha0010`, proses worker PHP-FPM `www-data` tanpa shell login, izin direktori minimum *least privilege*, izin `.env` ketat `600`, SSH `PermitRootLogin no`, `APP_DEBUG=false`).<br>2. Firewall UFW berprinsip *default deny incoming*, hanya membuka port 22 (SSH) dan 443 (HTTPS) khusus dari IP host lab (`192.168.136.1`).<br>3. Pengujian nmap komparatif sebelum/sesudah dengan listener pengujian 8080 aktif.<br>4. **Kontrol Korektif:** Target prosedur restore dari backup harian otomatis SQLite (`sqlite3 database.sqlite ".backup"`) terjadwal via Linux `cron`, berkas dienkripsi OpenSSL, diverifikasi hash SHA-256, dan disimpan di luar VM (lokasi fisik/host terpisah) dengan simulasi pemulihan pada instance terisolasi (pengukuran RPO/RTO aktual). | **SEC-05, SEC-06, SEC-07** |

---

## 3. Analisis Draf Risiko Tertinggi

Risiko tertinggi pada sistem perpustakaan mini ini adalah **pengambilalihan akun administrator (A1)** karena peran admin memegang wewenang penuh untuk mengubah katalog buku, menonaktifkan akun anggota, memanipulasi sirkulasi peminjaman, serta menghapus ulasan. Kerentanan berupa penyimpanan password plaintext, ketiadaan HTTPS, cookie sesi tanpa atribut keamanan (`HttpOnly`/`Secure`), atau kegagalan invalidasi sesi pasca-logout memungkinkan penyerang mencegat atau mencuri sesi aktif admin. Dampak eksploitasi mencakup kebocoran data sensitif anggota, kerusakan integritas seluruh data sirkulasi, hingga penghentian operasional perpustakaan. Oleh karena itu, pada cabang `main`, kontrol **SEC-02** (Bcrypt cost 12), **SEC-04** (HTTPS TLS 1.2/1.3), dan **SEC-10** (cookie aman, regenerasi ID sesi saat login, serta invalidasi total saat logout) wajib diimplementasikan dan diuji secara bersamaan sebagai satu kesatuan pertahanan berlapis.

---

## 4. Analisis Rantai Ancaman–Kontrol Terpilih

Sebagai demonstrasi penelusuran hubungan ancaman terhadap kontrol teknis dan pengujian:

```
[Aset A5: Database SQLite & Layanan VM]
       │
       ▼
[Ancaman: Kerusakan Storage / Ransomware / Malfungsi Data]
       │
       ▼
[Kelemahan: Ketiadaan Cadangan Teruji & Prosedur Restore Terverifikasi]
       │
       ▼
[Dampak: Kehilangan Ketersediaan (Availability) & Integritas (Integrity)]
       │
       ├─► [Kontrol Preventif: SEC-05 (Hardening Izin File 600, Least Privilege www-data)]
       ├─► [Kontrol Preventif: SEC-06 (UFW Default Deny, Blokir Akses Luar Port DB/Internal)]
       └─► [Kontrol Korektif: SEC-07 (Backup Cron Otomatis + Enkripsi + Hash SHA-256
                                    + Prosedur Restore Terisolasi & Verifikasi Integritas)]
```

Kontrol korektif **SEC-07** ditujukan untuk memulihkan data setelah insiden; cakupan data, titik pemulihan dan durasi pemulihan baru dapat dinyatakan berdasarkan hasil restore dan pengukuran yang benar-benar dilakukan.

---

## 5. Kriteria Tinjau SEC-01

- [x] **5 Aset dan 5 Risiko:** Terdefinisi tepat 5 baris risiko untuk 5 aset utama (A1 s.d. A5) tanpa kolom kosong.
- [x] **Cakupan CIA Triad:**
  - *Confidentiality (C):* Tercakup pada A1, A2, A3, dan A4.
  - *Integrity (I):* Tercakup pada A1, A2, A3, A4, dan A5.
  - *Availability (A):* Tercakup secara eksplisit pada A5.
- [x] **Rujukan Kontrol >= 5 SEC:** Kontrol merujuk 9 syarat keamanan proyek (**SEC-02, SEC-03, SEC-04, SEC-05, SEC-06, SEC-07, SEC-08, SEC-09, SEC-10**).
- [x] **Kontrol Korektif Backup:** Backup otomatis dan pemulihan (*restore*) tercatat eksplisit sebagai kontrol korektif pada aset A5.
- [x] **Analisis Risiko Tertinggi:** Penjelasan naratif komprehensif 4 kalimat (dalam rentang 3–5 kalimat) mendasari prioritas perlindungan akun admin.
- [x] **Traceability ke Rencana Pengujian:** Seluruh kontrol terhubung dengan ID uji TST-01 s.d. TST-10d pada `docs/manifest-hasil-uji.md`.
