# Panduan membuat laporan PDF dan video demo

Panduan untuk Muhammad Ridha (NIM 230170010), tema Perpustakaan Mini. Acuan utama: dokumen dosen `Project_Pengganti_UTS_Keamanan_Sistem_Komputer.docx`, terutama Bagian 2 dan 3. Panduan ini membantu menyiapkan **laporan tertulis** dan **video**; bukan laporan final atau bukti bahwa seluruh pengujian telah selesai.

## 1. Apa yang harus dikumpulkan?

| Hasil | Ketentuan dosen |
|---|---|
| Laporan | PDF A4, font 11–12 pt, maksimal **6 halaman isi**, lampiran bukti tambahan maksimal **4 halaman**. Nama `NIM_Nama_Laporan.pdf`. |
| Kode | Repo Git privat yang dapat diakses dosen atau ZIP `NIM_Nama_Kode.zip`. Ada versi aman dan rentan terpisah, skrip backup, konfigurasi relevan dan README. |
| Video | Maksimal **5 menit**, rekaman layar dengan suara narasi, minimal 720p. MP4 `NIM_Nama_Video.mp4` atau tautan unlisted yang bisa dibuka dosen. Wajah tidak wajib. |
| Tanya jawab | Siap menjelaskan pekerjaan, hasil, dan kode dengan bahasa sendiri. |

Untuk nama, contoh `230170010_Muhammad_Ridha_Laporan.pdf` dan `230170010_Muhammad_Ridha_Video.mp4`; ikuti format lebih spesifik jika dosen memberikannya. Pastikan repo/ZIP tidak memuat `.env`, password, private key, backup asli, database aktif, atau cookie. Bila menggunakan Git, tugas meminta minimal 10 commit bertahap.

**Aturan penting:** gunakan hanya data fiktif dan pengujian pada perangkat sendiri. Versi rentan hanya di `127.0.0.1:8001`, tanpa forwarding atau proxy; browser harus berada di mesin yang menjalankannya. Jangan mengaku sebuah tes berhasil jika belum dilakukan. Potongan dan screenshot harus berasal dari hasil nyata.

## 2. Mulai dari mengumpulkan bukti

### Langkah 2.1 — Siapkan folder kerja

Di Windows, buat satu folder kerja pribadi untuk laporan, naskah dan screenshot yang sudah disensor. Simpan bukti proyek di struktur `docs/bukti/SEC-01/` sampai `SEC-10/` sesuai `docs/manifest-hasil-uji.md`. Jangan masukkan file rahasia, database asli atau arsip backup asli ke folder yang akan dikumpulkan.

### Langkah 2.2 — Daftarkan bukti untuk setiap syarat

| Syarat | Bukti inti yang perlu disiapkan |
|---|---|
| 1 — Risiko | Tabel 5 aset/risiko, CIA, kontrol yang terkait, serta alasan risiko tertinggi. `docs/tabel-risiko.md` adalah bahan awal yang perlu dicocokkan dengan tugas. |
| 2 — Hash | Kode registrasi dan verifikasi login; screenshot dua hash berbeda dari password fiktif sama. Sensor sebagian hash dan jangan tampilkan password. |
| 3 — AES-GCM | Database berisi ciphertext/envelope telepon, profil pengguna berhak menampilkan nomor fiktif, potongan enkripsi/dekripsi, dan bukti `.gitignore`. Jangan tampilkan key. |
| 4 — HTTPS | Screenshot browser HTTPS dan detail sertifikat (penerbit, masa berlaku, algoritma); bukti port 80 tertutup atau redirect. |
| 5 — Hardening | Bukti `id` akun layanan, permission rahasia, konfigurasi SSH efektif, daftar akun dan layanan aktif. Nama user VM dan waktu dicatat. |
| 6 — Firewall | Nmap **sebelum dan sesudah**, `ufw status verbose`, tabel port dan penjelasan. Untuk pembuktian port uji, listener 8080 tetap hidup saat pengujian sesudah. |
| 7 — Backup | Skrip, `crontab -l`, daftar backup/hash, serta tiga kondisi: **sebelum data uji dihapus, setelah dihapus, setelah restore**. Catat durasi restore dan salinan di luar VM. |
| 8 — SQLi | Hasil dan kode versi rentan **sebelum**, lalu hasil dan kode main aman **sesudah**, memakai input yang sama. |
| 9 — XSS | Alert lokal pada versi rentan **sebelum**, teks escaped pada main **sesudah**, dan potongan template keduanya. |
| 10 — IDOR/sesi | User A melihat data B pada versi rentan **sebelum**, kemudian ditolak di main **sesudah**; flag cookie dan kode policy. Buktikan pula logout mengakhiri sesi. |

Dokumen dosen mewajibkan kondisi sebelum/sesudah untuk **syarat 6–10**. Untuk syarat 7 dosen secara spesifik meminta **tiga tangkapan layar kondisi data**, ditambah bukti skrip, jadwal dan hash. Jumlah total screenshot seluruh tugas tidak ditetapkan. Proyek juga merencanakan pasangan bukti sesi terpisah dari IDOR; jangan menyebutnya jumlah minimal foto yang ditetapkan dosen.

### Langkah 2.3 — Cek status sebelum menulis klaim

Gunakan status **sudah terbukti**, **sebagian**, atau **belum diuji**, beserta lokasi bukti dan tanggal. Berdasarkan percakapan: Nmap sebelum/sesudah, HTTPS `/buku`, salinan SQLite, enkripsi manual dan kecocokan hash pernah ditunjukkan/dilaporkan. **Jangan menyimpulkan seluruh tugas selesai dari itu.** Periksa kembali foto tiga kondisi restore, cron dan satu run terjadwal, login/riwayat/dekripsi pada instance restore, serta demo SQLi/XSS/IDOR dan sesi. Hanya beri status lulus jika bukti aktual tersedia dan kriterianya dipenuhi.

### Langkah 2.4 — Rapikan bukti dan sensor rahasia

1. Untuk setiap kelompok bukti terminal VM, tampilkan `whoami; date` sekali. Format nama akun tugas adalah nama depan + empat digit terakhir NIM; akun yang disiapkan proyek `ridha0010`.
2. Perbesar teks, potong bagian yang tidak dibutuhkan, tetapi jangan menghilangkan konteks atau memanipulasi hasil. Beri nama dan waktu bukti, serta catat branch, IP/port dan lokasi file pada manifest.
3. Sensor password, isi `.env`, `APP_KEY`, `TELEPON_KEY_V1`, sandi backup, private key TLS, hash password lengkap, token CSRF dan nilai cookie/session. Flag cookie boleh terlihat; nilainya tidak.
4. Data asli, file database aktif dan backup asli tidak dimasukkan ke paket. Hash bukti dapat dihitung dengan `sha256sum <file-bukti>` di Ubuntu; jangan mengetik tanda `< >` sebagai nama file.

## 3. Cara membuat laporan tertulis

### Langkah 3.1 — Buat dokumen

1. Buka Word, LibreOffice Writer, atau editor yang dapat mengekspor PDF.
2. Atur ukuran **A4**, font 11 atau 12 pt, dan pastikan teks/gambar terbaca.
3. Buat sampul berisi judul project, nama, NIM, tema dan teknologi. Batas yang eksplisit pada tugas adalah maksimal 6 halaman **isi** dan lampiran maksimal 4 halaman; bila dosen punya aturan tambahan untuk sampul, ikuti aturan tersebut.
4. Jangan isi laporan dengan foto penuh layar yang tidak terbaca. Pilih bukti inti dan rujuk lampiran seperlunya.

### Langkah 3.2 — Ikuti urutan isi dosen

| Bagian | Apa yang ditulis | Saran ruang, bukan aturan dosen |
|---|---|---|
| Sampul | Identitas, judul, tema, teknologi. | Terpisah sesuai ketentuan dosen. |
| 1. Ringkasan | Tema, teknologi, jumlah dari 10 syarat yang **benar-benar terbukti**, dan peningkatan utama. | Maksimal ½ halaman; ini batas dosen. |
| 2. Arsitektur | Diagram klien, firewall, web server/aplikasi, database, lokasi backup; tulis IP/port aktual. | Sekitar ½ halaman. |
| 3. Syarat 1–10 | Satu bagian pendek per syarat: tindakan, bukti, alasan aman; syarat 6–10 bandingkan sebelum/sesudah. | Sekitar 4 halaman, sesuaikan keterbacaan. |
| 4. Risiko tersisa | Tiga keterbatasan yang masih nyata dan rencana perbaikannya. | Ringkas. |
| 5. Refleksi dan deklarasi | Refleksi 3–5 kalimat dan tabel bantuan AI/teman/sumber yang jujur. | Ringkas. |
| Daftar pustaka | Hanya sumber yang benar-benar dipakai. | Ringkas. |

Untuk arsitektur, gambar boleh dibuat dengan Shapes di Word: Windows host → jaringan host-only → UFW → Nginx/PHP-FPM → aplikasi/database, serta panah ke lokasi backup terpisah. Ini **susunan yang perlu dicocokkan dengan kondisi nyata**, bukan gambar bukti. IP hasil pemeriksaan sebelumnya: Windows host `192.168.136.1`, VM `192.168.136.128`, HTTPS 443 dan SSH 22. Jika IP berubah, tulis yang berlaku saat pengujian. Jangan gambarkan versi rentan terbuka di jaringan.

### Langkah 3.3 — Pola menulis tiap syarat

Dosen meminta format berikut pada bagian 3 laporan:

1. **Apa yang dilakukan:** 2–4 kalimat tentang konfigurasi atau kode yang dibuat.
2. **Bukti:** gambar atau output terminal yang sudah disensor, keterangan gambar dan rujukan di teks.
3. **Mengapa ini aman:** 2–3 kalimat dengan konsep yang relevan.
4. **Sebelum dan sesudah:** khusus syarat 6–10, tampilkan kedua kondisi berdampingan jika terbaca atau berikan tabel/rujukan ke gambar.

Contoh pola untuk **syarat 6**, berdasarkan hasil Nmap yang pernah kamu tunjukkan; pastikan screenshot masih tersedia dan listener 8080 terbukti hidup saat pengujian sesudah:

> **Apa yang dilakukan:** Saya mengatur UFW untuk menolak koneksi masuk secara default. SSH dan HTTPS diizinkan dari host lab yang ditentukan. Listener uji 8080 digunakan untuk membandingkan kondisi sebelum dan sesudah.
>
> **Bukti:** Gambar [nomor] memperlihatkan hasil Nmap sebelum, sedangkan Gambar [nomor] memperlihatkan hasil sesudah. Lihat juga status UFW dan bukti listener pada [lokasi bukti].
>
> **Mengapa ini aman:** Kebijakan default deny mengurangi port yang dapat diakses dari jaringan. Akses ke layanan yang diperlukan tetap dibatasi berdasarkan sumber; firewall tidak menggantikan kontrol keamanan aplikasi.

Contoh tabel yang dapat kamu isi berdasarkan tangkapan layar aktual:

| Port | Layanan | Sebelum | Sesudah |
|---|---|---|---|
| 22 | SSH | open | open |
| 80 | HTTP | closed | filtered |
| 443 | HTTPS | open | open |
| 8080 | Listener uji | open | filtered |

`closed` berarti target merespons bahwa port tertutup; `filtered` berarti Nmap tidak dapat memastikan port terbuka karena paket terfilter. Jangan menulis `filtered` sebagai bukti listener mati. Angka/tabel ini cocok dengan output yang pernah ditunjukkan, tetapi lampirkan bukti aktual.

### Langkah 3.4 — Susun sepuluh bagian dengan pertanyaan pemandu

1. **Risiko:** apa lima aset dan ancamannya? Apakah CIA, minimal lima syarat berbeda dan satu kontrol korektif tercakup? Tabel maksimal satu halaman; jelaskan risiko tertinggi dalam 3–5 kalimat.
2. **Hash:** bagaimana password dibuat dan diverifikasi? Mengapa dua hash berbeda? Jelaskan salt, jangan cantumkan password.
3. **AES-GCM:** mengapa telepon tidak terbaca di database? Jelaskan nonce, siapa yang dapat melihat data dan akibat kunci hilang/bocor.
4. **HTTPS:** apa yang dienkripsi saat komunikasi? Jelaskan peringatan self-signed dan bedanya dengan sertifikat CA tepercaya.
5. **Linux:** siapa operator non-root, siapa akun layanan, dan bagaimana permission serta SSH dibatasi? Jika key TLS dimiliki root agar master Nginx dapat membacanya, jelaskan kondisi aktual dan cocokkan dengan bunyi tugas; jangan mengklaim persyaratan kepemilikan dosen sudah sama tanpa penilaian.
6. **Firewall:** tampilkan tabel sebelum/sesudah, default deny dan batas sumber; jelaskan apa yang berubah.
7. **Backup:** apa yang dicakup, bagaimana enkripsi/cron, lokasi salinan, tiga kondisi restore dan durasi? Bedakan backup terjadwal dari pemindahan manual ke Windows. Jelaskan mengapa backup di server yang sama berisiko.
8. **SQLi:** jelaskan perbedaan query rentan dan parameter binding, bandingkan hasil input yang sama; audit input lain, bukan hanya pencarian.
9. **XSS:** jelaskan output escaping, bukan sekadar CSP. Bandingkan alert lokal dengan teks yang aman.
10. **IDOR/sesi:** jelaskan pemeriksaan pemilik di server, 404 untuk akses silang, flag cookie dan logout. Jangan tampilkan nilai cookie.

Untuk semua bagian: jika belum diuji, tulis **belum diuji**, jangan isi target yang diharapkan sebagai hasil aktual.

### Langkah 3.5 — Risiko tersisa, refleksi dan bantuan

Pilih **tiga risiko yang memang masih relevan ketika laporan selesai**, bukan sekadar menyalin contoh. Calon yang perlu diperiksa: IP VM DHCP dapat berubah dan memengaruhi sertifikat/firewall; salinan off-VM masih manual; atau recovery key/restore aplikasi belum diuji. Jika nanti sudah selesai, ganti dengan risiko aktual lain. Untuk masing-masing tulis dampak dan rencana perbaikan.

Refleksi ditulis dengan pengalaman sendiri: apa yang sulit, bagaimana diatasi, dan pelajaran yang didapat. Buat tabel deklarasi bantuan seperti:

| Bantuan/sumber | Bagian yang dibantu | Tindakan pribadi |
|---|---|---|
| AI | Contoh: memahami langkah SSH, UFW, dan penyusunan panduan. | Contoh: menjalankan, memeriksa hasil, dan menulis penjelasan sendiri. |
| Dokumentasi resmi | Tulis hanya dokumentasi yang benar-benar dipakai. | Jelaskan bagian yang diperiksa. |

Isi sesuai bantuan sebenarnya, termasuk bila ada teman atau sumber lain. Jangan mengaku melakukan sesuatu yang tidak dilakukan.

### Langkah 3.6 — Periksa PDF

1. Beri nomor/keterangan gambar, misalnya `Gambar 4. Hasil Nmap setelah UFW`, lalu rujuk pada teks.
2. Pastikan data sebelum/sesudah dan durasi restore cocok dengan bukti, manifest dan video.
3. Periksa tidak ada password, key, token, cookie atau data pribadi. Periksa lampiran juga.
4. Ekspor ke PDF, buka ulang dan cek teks tidak terpotong, gambar terbaca, maksimal 6 halaman isi dan 4 halaman lampiran.
5. Simpan dengan nama sesuai tugas. Jangan menyatakan penyerahan selesai sebelum benar-benar dikumpulkan.

## 4. Cara membuat video demo maksimal lima menit

### Langkah 4.1 — Selesaikan prasyarat sebelum merekam

Jangan mulai merekam sebelum semua bahan di bawah ada. Video tidak boleh mengklaim hasil yang belum terjadi.

**4.1.1 — Bukti cron syarat 7.** Dari percakapan, jadwal cron belum dibuat. Kerjakan dulu di Ubuntu:

```bash
whoami; date
sudo ls -l /usr/local/sbin/library-security-backup.sh
sudo systemctl status cron --no-pager
```

Buat log dan jadwal:

```bash
sudo touch /var/log/library-security-backup.log
sudo chmod 640 /var/log/library-security-backup.log
sudo crontab -e
```

Tambahkan **satu baris** di akhir (jangan hapus entri lain):

```text
15 2 * * * /usr/local/sbin/library-security-backup.sh >> /var/log/library-security-backup.log 2>&1
```

Simpan (`Ctrl+O`, `Enter`, `Ctrl+X`), lalu buktikan satu eksekusi terjadwal. Cara tercepat: ganti sementara baris tersebut menjadi jadwal dekat, tunggu satu putaran, lalu kembalikan ke harian.

```text
*/3 * * * * /usr/local/sbin/library-security-backup.sh >> /var/log/library-security-backup.log 2>&1
```

```bash
sudo crontab -l
sudo tail -n 30 /var/log/library-security-backup.log
sudo ls -lt /var/backups/library-security | head
sudo sha256sum -c /var/backups/library-security/<nama-file>.enc.sha256
```

Sesuaikan `<nama-file>` dengan nama arsip baru; jangan mengetik tanda `<` `>` sebagai bagian nama. Setelah bukti ada, kembalikan jadwal ke `15 2 * * *`, jalankan `sudo crontab -l`, dan pastikan backup lama tidak terhapus.

**4.1.2 — Uji login, riwayat, dekripsi telepon pada database restore.** Gunakan instance terpisah di loopback, bukan database aktif. Pertama cek config cache:

```bash
ls -l /var/www/library-security/bootstrap/cache/config.php
```

Jika file itu ada, jalankan `sudo -u www-data php /var/www/library-security/artisan config:clear` dulu. Jalankan instance uji dengan database hasil restore:

```bash
cd /var/www/library-security
sudo -u www-data DB_DATABASE=/var/backups/library-security/restore-uji/simulasi.sqlite \
  SESSION_COOKIE=mini_library_restore_session \
  php artisan serve --host=127.0.0.1 --port=8002
```

Di Firefox **dalam VM**, buka `http://127.0.0.1:8002/login`, masuk sebagai `user_a`, lalu buka `/peminjaman` dan `/profil`. Ambil screenshot tanpa password, cookie, atau token. Hentikan dengan `Ctrl+C`. Jika sebelumnya memakai `config:cache`, jalankan ulang `sudo -u www-data php artisan config:cache` setelah selesai. Port `8002` dipakai agar tidak bertabrakan dengan demo rentan `8001`.

**4.1.3 — Recovery key terpisah.** Pastikan salinan `/etc/library-security/backup.pass` ada di lokasi terpisah (flash disk/laptop) dan **tidak** ada di repo. Di video cukup tunjukkan keberadaan file dan lokasinya; jangan buka isinya.

**4.1.4 — Akun dan data demo.** Siapkan akun fiktif dari seeder: admin, `user_a`, `user_b`. Pastikan `user_a` sudah punya nomor telepon fiktif agar dekripsi bisa ditunjukkan. Catat `id` satu peminjaman milik `user_b` untuk demo IDOR:

```bash
sudo sqlite3 /var/www/library-security/database/database.sqlite \
"SELECT id, anggota_id, buku_id FROM peminjaman ORDER BY id;"
```

Hapus ulasan XSS setelah merekam lewat panel admin agar database demo bersih.

**4.1.5 — Layar dan rahasia.** Resolusi rekaman 1920×1080. Perbesar font terminal (16–18 pt) dan zoom browser 110%. Tutup notifikasi, chat, tab `.env`, key, dan file backup asli. Jangan pernah membuka `.env` saat merekam.

**4.1.6 — Bahan yang disiapkan di VM.** Salin lima foto bukti ke VM agar bisa ditampilkan langsung di video:

```bash
mkdir -p ~/bukti-video
```

Dari PowerShell Windows:

```powershell
scp "C:\Users\Administrator\Documents\Tugas Linux\*.png" ridha0010@192.168.136.128:/home/ridha0010/bukti-video/
```

Pemetaan nama: `1.png` = Nmap sebelum UFW, `3.png` = Nmap sesudah UFW, `5.png` = data sebelum dihapus, `2.png` = setelah dihapus, `4.png` = setelah restore.

**4.1.7 — Aplikasi rentan, hanya loopback.** Jangan menjalankan branch `vulnerable` di direktori produksi `/var/www/library-security`. Clone terpisah:

```bash
cd ~
git clone -b vulnerable https://github.com/muhammadRidha-15/library-security.git library-vuln
cd library-vuln
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Isi `SEED_USER_PASSWORD` dan `SEED_ADMIN_PASSWORD` pada `~/library-vuln/.env`, lalu siapkan database dan data demo **sebelum merekam**:

```bash
php artisan migrate:fresh --seed
ss -ltnp | grep ':8001' || echo "port 8001 bebas"
```

Jangan menampilkan perintah seed berisi password di video. Jalankan server hanya saat adegan demo:

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

Browser untuk demo ini harus Firefox **di dalam VM yang sama**. Jangan reverse proxy, jangan port forwarding, jangan bind `0.0.0.0`.

### Langkah 4.2 — Siapkan jendela sebelum tombol rekam

Susun urutan jendela agar tidak mencari saat merekam:

| Urutan | Jendela | Isi |
|---|---|---|
| 1 | Terminal Ubuntu | `ridha0010@perpus-server`, folder `~` |
| 2 | Firefox tab 1 | `https://192.168.136.128/buku` (sudah login `user_a`) |
| 3 | Firefox tab 2 | `https://192.168.136.128/admin/buku` (sudah login admin) |
| 4 | Firefox private window | `http://127.0.0.1:8001` (vulnerable, login `user_a`) |
| 5 | Image Viewer | `~/bukti-video` berisi lima PNG |
| 6 | VS Code jendela 1 | Folder aman `C:\Users\Administrator\orca\workspaces\library_UTS\Linux` |
| 7 | VS Code jendela 2 | Folder rentan `C:\projectCoding\library_UTS` |
| 8 | Terminal kedua | server vulnerable `php artisan serve` |
| 9 | Catatan | poin risiko tersisa untuk penutup |

Untuk login tanpa menampilkan password: login sebelum rekaman dimulai, atau jeda rekaman (`Win+Alt+R` berhenti, mulai lagi setelah selesai mengetik). Jika memakai dua sesi `user_a`, gunakan private window agar cookie tidak bertukar.

### Langkah 4.3 — Peta adegan lima menit

Target total **4 menit 50 detik** agar tidak melewati batas.

| Waktu | Adegan | Yang tampil |
|---|---|---|
| 0:00–0:25 | 1. Pembuka | Identitas, `whoami; date`, `hostnamectl`, `ip -br addr` |
| 0:25–1:05 | 2. Aplikasi HTTPS | Katalog, pencarian, detail buku, riwayat, profil telepon |
| 1:05–1:55 | 3. Syarat 2–3 | Hash di DB (disensor), dua hash berbeda, envelope telepon, kode AES-GCM |
| 1:55–2:40 | 4. Syarat 4–5 | Sertifikat, izin key, Nginx 443, `.env` 403, SSH, permission, akun |
| 2:40–3:15 | 5. Syarat 6 | Foto Nmap sebelum/sesudah, `ufw status verbose`, listener 8080 |
| 3:15–3:55 | 6. Syarat 7 | Skrip backup, `crontab -l`, hash, tiga foto restore, integrity check |
| 3:55–4:40 | 7. Syarat 8–10 | SQLi, XSS, IDOR: vulnerable lalu main; sesi di kode |
| 4:40–4:50 | 8. Penutup | Satu risiko tersisa dan rencananya |

### Langkah 4.4 — Naskah per adegan

Bacakan kalimat dalam tanda kutip. Ganti bagian `[...]` dengan kondisi nyata. Jika suatu bukti belum ada, lewati adegannya dan sebutkan di laporan sebagai belum diuji; jangan membacakan klaim palsu.

#### Adegan 1 — Pembuka (0:00–0:25)

**Yang dilakukan:** tampilkan terminal Ubuntu, jalankan tiga perintah, lalu sorot hasilnya dengan kursor.

```bash
whoami; date
hostnamectl
ip -br addr
```

**Narasi:** “Saya Muhammad Ridha, NIM 230170010. Project saya adalah aplikasi web Perpustakaan Mini yang diamankan di server Ubuntu. Saya bekerja sebagai akun operator non-root `ridha0010` pada VM `perpus-server`. Alamat VM saat pengujian adalah `192.168.136.128`, dan website diakses melalui HTTPS.”

**Jangan tampilkan:** password sudo, isi `.env`, key.

#### Adegan 2 — Aplikasi melalui HTTPS (0:25–1:05)

**Yang dilakukan di Firefox tab 1:**

1. Buka `https://192.168.136.128/buku`. Tunjukkan alamat berawalan `https://`.
2. Klik ikon sertifikat, buka detail, tunjukkan nama `mini-library.test` dan masa berlaku. Tutup.
3. Klik kotak pencarian di halaman katalog, ketik `keamanan`, tekan Enter. Tunjukkan hasil “Dasar Keamanan Sistem”.
4. Buka satu buku, tunjukkan detail dan ulasan.
5. Buka `/peminjaman`, tunjukkan riwayat milik `user_a`.
6. Buka `/profil`, tunjukkan nomor telepon fiktif yang berhasil didekripsi.

**Narasi:** “Aplikasi dijalankan oleh Nginx dan PHP-FPM, bukan server pengembangan. Katalog, pencarian, detail buku, peminjaman, dan ulasan berjalan melalui HTTPS. Halaman ini memakai sertifikat self-signed, sehingga browser menampilkan peringatan; saya menjelaskan hal itu di laporan. Profil menampilkan nomor telepon yang di database tersimpan sebagai ciphertext.”

**Jangan tampilkan:** nilai cookie, token CSRF, password.

#### Adegan 3 — Syarat 2 dan 3 (1:05–1:55)

**Yang dilakukan:** pindah ke terminal, jalankan `whoami; date` lagi, lalu tampilkan hash yang disensor.

```bash
whoami; date
sudo sqlite3 /var/www/library-security/database/database.sqlite \
"SELECT nama, substr(password,1,7)||'...'||substr(password,-8) FROM anggota ORDER BY id;"
```

Hasil harus memperlihatkan awalan `$2y$12$` (biaya Bcrypt 12) dan akhiran berbeda untuk `user_a` dan `user_b`. Jangan tampilkan hash penuh.

Tampilkan envelope telepon:

```bash
sudo sqlite3 /var/www/library-security/database/database.sqlite \
"SELECT nama, substr(telepon_enc,1,90)||'...' FROM anggota WHERE telepon_enc IS NOT NULL;"
```

Tampilkan kode di **VS Code pada Windows**, bukan di terminal VM. Buka folder proyek utama `C:\Users\Administrator\orca\workspaces\library_UTS\Linux`, lalu gunakan `Ctrl+P` untuk membuka file dan `Ctrl+G` untuk lompat ke nomor baris. Perbesar editor dengan `Ctrl++` agar terbaca di video.

1. `app/Http/Controllers/Auth/RegisterController.php` → **baris 30**: `'password' => Hash::make(...)`.
2. `app/Http/Requests/RegisterRequest.php` → **baris 44–48**: `'min:12'` dan pemeriksaan `strlen($value) > 72`.
3. `app/Services/AesGcm.php` → **baris 9**: `CIPHER = 'aes-256-gcm'`.
4. File yang sama → **baris 55**: `random_bytes(self::NONCE_LENGTH)`.
5. File yang sama → **baris 58–65**: `openssl_encrypt` dengan `OPENSSL_RAW_DATA` dan `$aad`.

**Narasi:** “Password tidak disimpan sebagai teks biasa. Laravel memakai `Hash::make` dengan Bcrypt biaya dua belas, dan salt dibuat otomatis per hash. Karena salt berbeda, password yang sama pada `user_a` dan `user_b` menghasilkan hash berbeda; saya hanya menampilkan sebagian hash. Panjang password dibatasi dua belas sampai tujuh puluh dua byte untuk mencegah pemotongan diam-diam oleh Bcrypt. Nomor telepon dienkripsi AES-256-GCM dengan nonce acak dua belas byte, tag enam belas byte, dan AAD yang mengikat id anggota. Tanpa kunci, isi database tidak dapat dibaca.”

**Jangan tampilkan:** password asli, `TELEPON_KEY_V1`, hash penuh.

#### Adegan 4 — Syarat 4 dan 5 (1:55–2:40)

**Yang dilakukan:** masih di terminal, mulai dengan identitas/waktu.

```bash
whoami; date
sudo openssl x509 -in /etc/nginx/tls/mini-library.crt -noout -subject -issuer -dates -ext subjectAltName
sudo ls -l /etc/nginx/tls/mini-library.key
sudo ss -ltnp | grep -E ':443|:80' || true
sudo sed -n '1,45p' /etc/nginx/sites-available/mini-library
```

Tunjukkan `ssl_protocols TLSv1.2 TLSv1.3`, `root` menunjuk ke `/public`, dan tidak ada listener port 80.

```bash
curl -sk -o /dev/null -w '%{http_code}\n' https://192.168.136.128/.env
```

Hasil harus `403`.

```bash
sudo sshd -T | grep -Ei 'permitrootlogin|maxauthtries|passwordauthentication|pubkeyauthentication'
id
id www-data
getent passwd www-data
sudo ls -l /var/www/library-security/.env
ls -ld /var/www/library-security /var/www/library-security/storage /var/www/library-security/bootstrap/cache
sudo -u www-data php /var/www/library-security/artisan tinker --execute="dump(config('session.secure'), config('session.http_only'), config('session.same_site'));"
```

Hasil tinker: `true`, `true`, `"lax"`.

**Narasi:** “Nginx hanya melayani HTTPS pada port empat ratus empat puluh tiga dengan TLS satu titik dua dan satu titik tiga. Sertifikat mencakup nama domain dan alamat IP VM, dan private key hanya dapat dibaca root. Port delapan puluh tidak melayani aplikasi, dan permintaan ke `.env` ditolak empat ratus tiga. SSH menolak login root, membatasi tiga percobaan, dan memakai kunci, bukan password. Akun layanan `www-data` tidak punya shell login. File `.env` hanya dapat dibaca pemiliknya, sedangkan kode aplikasi tidak dapat ditulis oleh akun web. Cookie sesi memakai `Secure`, `HttpOnly`, dan `SameSite=Lax`.”

**Jangan tampilkan:** isi `.env`, private key, fingerprint yang bisa dipakai ulang tanpa alasan.

#### Adegan 5 — Syarat 6, firewall sebelum dan sesudah (2:40–3:15)

**Yang dilakukan:** buka Image Viewer `~/bukti-video/1.png` (sebelum) lalu `3.png` (sesudah). Tunjukkan port 8080 berubah `open` menjadi `filtered`. Kembali ke terminal:

```bash
whoami; date
sudo ufw status verbose
sudo python3 -m http.server 8080 --bind 0.0.0.0
```

Biarkan listener berjalan, buka terminal kedua:

```bash
ss -ltnp | grep ':8080'
curl -m 5 -s -o /dev/null -w '%{http_code}\n' http://192.168.136.128:8080 || echo "tidak dapat terhubung"
```

Hasil curl harus gagal/timeout walaupun listener hidup. Hentikan listener dengan `Ctrl+C`.

**Narasi:** “Firewall memakai kebijakan default deny untuk koneksi masuk. Port dua puluh dua dan empat ratus empat puluh tiga hanya diizinkan dari host lab `192.168.136.1`. Pada pengujian sebelum, port delapan ribu delapan puluh terlihat terbuka. Setelah UFW aktif, port itu terfilter walaupun listener pengujian masih berjalan. Ini membuktikan paketnya diblokir firewall, bukan karena layanan dimatikan.”

**Catatan:** kalau ingin nmap langsung dari Windows, potong klip terpisah dan jalankan `& "C:\Program Files (x86)\Nmap\nmap.exe" -Pn -p 22,80,443,8080 192.168.136.128`. Jangan menjalankan ulang UFW disable hanya demi rekaman.

#### Adegan 6 — Syarat 7, backup dan restore (3:15–3:55)

**Yang dilakukan:** terminal, identitas/waktu, lalu tampilkan jadwal dan hasil.

```bash
whoami; date
sudo crontab -l
sudo ls -lh /var/backups/library-security
sudo sha256sum -c /var/backups/library-security/<nama-file>.enc.sha256
sudo sed -n '1,70p' /usr/local/sbin/library-security-backup.sh
```

Jika skrip memuat sandi secara langsung, **jangan** tampilkan baris itu; hentikan video dan perbaiki skrip agar membaca dari file.

```bash
sudo sqlite3 /var/backups/library-security/restore-uji/simulasi.sqlite "PRAGMA integrity_check;"
sudo sqlite3 /var/backups/library-security/restore-uji/simulasi.sqlite \
"SELECT 'anggota', COUNT(*) FROM anggota
UNION ALL SELECT 'buku', COUNT(*) FROM buku
UNION ALL SELECT 'peminjaman', COUNT(*) FROM peminjaman;"
```

Buka Image Viewer `5.png`, `2.png`, `4.png` berurutan untuk memperlihatkan tiga kondisi data.

**Narasi:** “Backup berjalan terjadwal dengan cron. Skrip memakai SQLite Online Backup API agar snapshot konsisten saat WAL aktif, lalu arsipnya dienkripsi dan checksum SHA-256 dibuat. Salinan terenkripsi juga saya simpan di luar VM, dan hash di kedua sisi cocok. Saya menguji restore pada salinan terisolasi, bukan database aktif. Sebelum dihapus ada tiga anggota, tiga buku, dan dua peminjaman; setelah satu peminjaman dihapus tersisa satu; setelah restore kembali dua dan `integrity_check` menghasilkan `ok`.”

**Jangan tampilkan:** sandi backup, isi file `backup.pass`, arsip asli, `uji-database.sqlite` yang belum terenkripsi.

#### Adegan 7 — Syarat 8, 9, dan 10 (3:55–4:40)

**Persiapan:** server vulnerable sudah berjalan di terminal kedua, browser private window sudah login `user_a`, dan VS Code membuka **dua jendela**: folder aman `C:\Users\Administrator\orca\workspaces\library_UTS\Linux` serta folder rentan `C:\projectCoding\library_UTS`. Gunakan `Ctrl+P` untuk membuka file dan `Ctrl+G` untuk lompat ke baris.

**a) SQLi.** Di vulnerable: cari `zzz` (nol hasil), lalu buka alamat:

```text
http://127.0.0.1:8001/buku/cari?q=%27%20OR%201%3D1%20--%20
```

Tunjukkan seluruh buku muncul. Pindah ke main, buka alamat yang sama dengan host aman:

```text
https://192.168.136.128/buku/cari?q=%27%20OR%201%3D1%20--%20
```

Tunjukkan hasil kosong. Tampilkan kode di VS Code:

1. Folder rentan `C:\projectCoding\library_UTS` → `app/Http/Controllers/BookController.php` → **baris 43**: `whereRaw(...)` yang menyambung `$query`.
2. Folder aman `C:\Users\Administrator\orca\workspaces\library_UTS\Linux` → `app/Http/Controllers/BookController.php` → **baris 42–44**: `where(function ...)` dengan `where('judul', 'like', ...)`.

**b) XSS.** Di vulnerable, buka detail buku, tulis ulasan:

```text
<script>alert('XSS-LAB')</script>
```

Kirim, tunjukkan alert. Pindah ke main, buka buku yang sama, tunjukkan teks yang sama tampil sebagai tulisan biasa. Tampilkan template di VS Code:

1. Folder rentan → `resources/views/buku/show.blade.php` → **baris 199**: `{!! $ulasan->isi !!}`.
2. Folder aman → `resources/views/buku/show.blade.php` → **baris 198**: `{{ $ulasan->isi }}`.

**c) IDOR.** Di vulnerable, buka:

```text
http://127.0.0.1:8001/peminjaman/<id-milik-user_b>
```

Tunjukkan data peminjaman milik `user_b` terbuka bagi `user_a`. Di main, buka alamat yang sama dengan host aman:

```text
https://192.168.136.128/peminjaman/<id-milik-user_b>
```

Tunjukkan `404 Not Found`. Tampilkan policy di folder aman:

1. `app/Policies/PeminjamanPolicy.php` → **baris 37**: `Response::denyAsNotFound(...)`.
2. `app/Http/Controllers/Auth/LoginController.php` → **baris 51**: `$request->session()->regenerate();`.
3. File yang sama → **baris 67–68**: `invalidate()` dan `regenerateToken()`.
4. Folder rentan → `app/Http/Controllers/PeminjamanController.php` → **baris 69–71**: baris `Gate::authorize('view', ...)` dikomentari.
5. Folder rentan → `app/Http/Controllers/Auth/LoginController.php` → **baris 51–53**: `session()->regenerate()` dikomentari.

**Narasi:** “Versi rentan hanya berjalan di loopback dan database terpisah, khusus demonstrasi. Pada pencarian, versi rentan menyambung input langsung ke SQL sehingga payload `OR 1=1` mengembalikan semua buku; versi aman memakai parameter binding sehingga hasilnya kosong. Pada ulasan, versi rentan merender HTML mentah hingga skrip berjalan; versi aman memakai escaping Blade sehingga payload tampil sebagai teks. Pada peminjaman, versi rentan tidak memeriksa pemilik sehingga `user_a` dapat membuka data `user_b`; versi aman memakai policy dan menolak dengan empat ratus empat, tanpa membocorkan apakah data itu ada. Login juga meregenerasi sesi, dan logout membatalkan sesi serta token CSRF.”

**Jangan tampilkan:** nilai cookie/session, password, ID nyata milik orang lain di luar data fiktif.

#### Adegan 8 — Penutup (4:40–4:50)

**Narasi:** “Dari sepuluh syarat, yang sudah saya buktikan adalah [sebutkan yang benar-benar ada]. Risiko yang masih tersisa adalah [pilih satu: alamat VM masih DHCP sehingga sertifikat dan firewall dapat rusak bila IP berubah / salinan backup ke luar VM masih manual / uji restore aplikasi baru sebagian]. Rencana perbaikan saya adalah [langkah konkret].”

### Langkah 4.5 — Merekam di Windows

1. Besarkan VM ke layar penuh (`Ctrl+Alt+Enter` di VMware) agar rekaman 1080p.
2. Aktifkan Game Bar: `Win+G`, nyalakan mikrofon, atur volume input. Mulai/berhenti cepat dengan `Win+Alt+R`. File tersimpan di `C:\Users\Administrator\Videos\Captures`.
3. Jika Game Bar tidak dapat merekam VMware, pasang OBS Studio atau rekam di dalam VM dengan `sudo apt install simpleScreenRecorder`; keduanya gratis. Pilih satu, jangan mencampur alat di tengah rekaman.
4. Rekam satu kali jalan sesuai peta adegan. Untuk bagian yang salah, berhenti, ulangi dari awal adegan itu, dan gabungkan saat menyunting; jangan menyambung potongan hingga mengubah makna hasil.
5. Saat mengetik password atau membuka file rahasia: hentikan rekaman dengan `Win+Alt+R`, lakukan, lalu mulai lagi.
6. Setelah selesai, hentikan rekaman dan simpan.

**Jika merekam dua sumber** (VM dan Windows untuk nmap), rekam terpisah, lalu gabungkan. Jangan menampilkan desktop yang memuat file rahasia di sela-sela.

### Langkah 4.6 — Menyunting, mengekspor, memeriksa

1. Buka Clipchamp (bawaan Windows) atau editor lain. Gabungkan klip sesuai urutan adegan; potong bagian hening dan kesalahan.
2. Periksa durasi total di timeline. Target `4:50`; jika lebih dari `5:00`, potong penjelasan yang berulang, bukan buktinya.
3. Ekspor `MP4`, resolusi `1920×1080` (minimal `1280×720`), audio jelas.
4. Simpan sebagai `230170010_Muhammad_Ridha_Video.mp4` di `C:\Users\Administrator\Documents\Tugas Linux`.
5. Tonton **seluruh video tanpa melompat**. Periksa setiap frame: tidak ada password, key, token, cookie, isi `.env`, atau hash penuh. Periksa audio juga tidak menyebut sandi.
6. Cocokkan isi video dengan laporan: angka restore, hasil Nmap, dan nama akun harus sama.
7. Jika mengunggah ke layanan video, gunakan mode *unlisted* dan uji tautan dari browser yang tidak login sebagai pemilik. Jangan mengunggah kode rentan atau data sensitif.

### Langkah 4.7 — Jika ada yang belum siap

| Masalah | Tindakan |
|---|---|
| Cron belum ada | Selesaikan 4.1.1; jangan rekam adegan 6 sampai ada log run terjadwal. |
| Login restore belum diuji | Selesaikan 4.1.2; jika belum, lewati bagian itu dan tulis “belum diuji” di laporan. |
| Port 8001 dipakai proses lain | `ss -ltnp \| grep ':8001'`, hentikan proses milikmu, jangan membunuh proses sistem. |
| Vulnerable tidak mau bind loopback | Periksa `php artisan serve --host=127.0.0.1 --port=8001`; jangan mengganti host. |
| Sertifikat kedaluwarsa | Periksa `sudo openssl x509 -enddate`; jangan membuat ulang tanpa memperbarui konfigurasi. |
| Video lebih dari 5 menit | Potong pengulangan; bukti tambahan taruh di lampiran laporan. |

## 5. Checklist sebelum kirim

- [ ] Syarat 1–10 punya bukti aktual; khusus 6–10 ada sebelum/sesudah, dan syarat 7 mempunyai tiga kondisi data.
- [ ] Foto terminal punya identitas VM/waktu sesuai tugas; bukti terbaca, diberi keterangan, dan rahasia disensor.
- [ ] PDF mengikuti format, batas halaman, ringkasan, arsitektur, risiko tersisa, refleksi, deklarasi bantuan dan pustaka.
- [ ] Video maksimal lima menit, audio jelas, HTTPS dan seluruh urutan tugas ditampilkan; rahasia tidak tampak.
- [ ] Kode aman/rentan terpisah; backup script dan konfigurasi relevan tersedia; `.env`, key, password dan backup asli tidak dikumpulkan.
- [ ] Nama file sesuai ketentuan, tautan/ZIP dapat dibuka, dan bukti penyerahan dicatat hanya setelah benar-benar dikumpulkan.

## Sumber

- Dokumen dosen `Project_Pengganti_UTS_Keamanan_Sistem_Komputer.docx`, Bagian 2 dan Bagian 3.
- `docs/tutorial-deployment-ubuntu-pemula.md` untuk langkah teknis VM dan titik screenshot.
- `docs/manifest-hasil-uji.md` untuk pencatatan hasil dan lokasi bukti; statusnya perlu diselaraskan dengan pengujian aktual.
- `docs/tabel-risiko.md` untuk bahan tabel risiko proyek.
