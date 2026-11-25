# Tutorial Ubuntu untuk Tugas Perpustakaan Mini

Panduan bertahap untuk Muhammad Ridha (NIM 230170010), berdasarkan dokumen tugas dosen dan kondisi VM yang telah diperiksa. Ini panduan pekerjaan, **bukan bukti bahwa semua langkah sudah berhasil dijalankan**. Perintah belum diuji di VM kamu; baca hasil setiap langkah sebelum melanjutkan.

**Cara mengikuti:** kerjakan nomor berurutan, satu blok perintah pada satu waktu. Setiap judul seperti `6.2.1` adalah nomor langkah; jika suatu langkah sudah terbukti selesai, jangan ulangi tindakan yang membuat/menimpa file. Bagian 8–10 adalah **rencana dan checklist**, bukan skrip backup atau prosedur demo lengkap yang sudah diuji; jangan menganggapnya selesai hanya karena dibaca.

## Peta tugas dosen: sedang mengerjakan apa?

### Gambaran tugas

Menurut bagian 1 dokumen tugas, kamu berperan sebagai *security engineer*: membuat aplikasi perpustakaan sederhana di VM Ubuntu lalu mengamankannya. Digit terakhir NIM kamu adalah **0**, sehingga tema kamu perpustakaan, data sensitif yang dienkripsi adalah **nomor telepon anggota**, dan data yang harus dilindungi dari akses anggota lain adalah **riwayat peminjaman**.

Dosen meminta aplikasi dapat dipakai melalui HTTPS, mempunyai registrasi/login, admin dan pengguna biasa, pengelolaan data, pencarian, kolom teks bebas untuk uji XSS, serta minimal dua akun pengguna dan satu akun admin yang semuanya fiktif. Tampilan tidak perlu mewah; yang dinilai adalah fungsi dan keamanan beserta buktinya.

### Sepuluh syarat yang dinilai

Tabel ini merangkum bagian 2 dokumen dosen. Setiap syarat bernilai 8 poin; **syarat 6–10 harus punya perbandingan sebelum dan sesudah**. Nomor `SEC` adalah penanda proyek yang sama dengan nomor syarat dosen.


| Syarat dosen                      | Apa yang harus dikerjakan dan diamankan                                                                                                    | Bukti yang diminta                                                                                     | Lihat tutorial ini                          |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------ | ------------------------------------------- |
| **1 / SEC-01 — Tabel risiko**     | Kenali lima aset dan lima risiko; jelaskan kerahasiaan, keutuhan dan ketersediaan data (*CIA*) serta kontrol yang dipakai.                 | Tabel risiko dan alasan risiko tertinggi dalam laporan.                                                | Bagian 9–10; sumber `docs/tabel-risiko.md`. |
| **2 / SEC-02 — Password**         | Simpan password sebagai hash lambat dengan salt unik, bukan teks asli; verifikasi saat login.                                              | Kode registrasi/login dan dua hash berbeda dari password fiktif yang sama, disensor.                   | Bagian 5.2 dan 9.                           |
| **3 / SEC-03 — Enkripsi telepon** | Lindungi nomor telepon dengan AES-256-GCM, nonce acak baru dan kunci di luar kode; hanya pembaca berhak yang boleh melihat hasil dekripsi. | Data terenkripsi di database, tampilan pengguna berhak, kode dan `.gitignore`; jangan tampilkan key.   | Bagian 5.2, 8 dan 9.                        |
| **4 / SEC-04 — HTTPS**            | Enkripsi komunikasi browser–server dengan sertifikat self-signed dan lindungi private key.                                                 | Browser HTTPS, detail sertifikat, dan bukti port HTTP ditutup atau dialihkan.                          | Bagian 6.                                   |
| **5 / SEC-05 — Hardening Linux**  | Gunakan admin non-root dan akun layanan tanpa login shell; batasi izin file, amankan SSH, perbarui sistem dan tinjau layanan.              | Identitas akun layanan, permission rahasia, konfigurasi SSH efektif dan daftar layanan.                | Bagian 3–5.                                 |
| **6 / SEC-06 — Firewall**         | Default deny untuk koneksi masuk; izinkan hanya layanan yang perlu, lalu bandingkan hasil port.                                            | Nmap dari klien lab sebelum/sesudah, status UFW dan tabel perbandingan.                                | Bagian 7.                                   |
| **7 / SEC-07 — Backup**           | Backup database, unggahan dan konfigurasi; enkripsi, jadwalkan dengan cron, simpan salinan di lokasi lain dan uji pemulihan.               | Skrip dan `crontab -l`, hash, serta kondisi data sebelum dihapus, setelah dihapus dan setelah restore. | Bagian 8.                                   |
| **8 / SEC-08 — SQL Injection**    | Buktikan pencarian rentan bisa dimanipulasi, lalu gunakan query berparameter untuk input pengguna pada versi aman.                         | Hasil dan potongan kode sebelum/sesudah dengan input sama.                                             | Bagian 9, langkah 1–3.                      |
| **9 / SEC-09 — XSS**              | Buktikan skrip ulasan berjalan pada versi rentan, lalu pastikan input pengguna di-*escape* ketika ditampilkan.                             | Alert versi rentan, teks versi aman dan perbandingan template.                                         | Bagian 9, langkah 1–2 dan 4.                |
| **10 / SEC-10 — IDOR dan sesi**   | Batasi akses riwayat berdasarkan pemilik/admin di server; amankan cookie dan pastikan logout mengakhiri sesi.                              | Akses silang sebelum/sesudah, kode otorisasi dan flag cookie browser.                                  | Bagian 6.3 dan 9, langkah 1–2 dan 5.        |


**Posisi kamu berdasarkan percakapan terakhir:** SSH key `ridha0010` berhasil, database sudah dimigrasi, dan symlink `public/storage` sudah dibuat serta bisa diakses `www-data`. Seeder pernah gagal lalu kamu menyatakan sudah mengisi password dan melakukan seeding; verifikasi hasilnya bila belum yakin. Tahap yang belum dibuktikan adalah HTTPS, firewall, backup dan demonstrasi. **Lanjut ke langkah 6.1**, setelah memeriksa ulang status pada langkah 5.3. Jangan mengulang clone, membuat database, mengganti key, atau membuat symlink lagi jika sudah berhasil.

### Hasil yang dikumpulkan

1. **Laporan PDF:** A4, font 11–12, maksimal enam halaman isi; lampiran bukti tambahan maksimal empat halaman. Sertakan arsitektur, sepuluh syarat, risiko tersisa, refleksi dan deklarasi bantuan termasuk AI.
2. **Kode dan konfigurasi:** repo privat untuk dosen atau ZIP; versi aman dan rentan terpisah, skrip backup, konfigurasi relevan serta README. Jangan sertakan `.env`, password, kunci atau backup asli. Jika menggunakan Git, dosen meminta minimal 10 commit bertahap.
3. **Video:** maksimal lima menit, menunjukkan aplikasi, bukti keamanan dan perbandingan sebelum/sesudah dengan narasi. Jangan tampilkan rahasia.
4. **Tanya jawab:** bersiap menjelaskan langkah dan kode dengan bahasa sendiri. Gunakan `whoami; date` sekali per kelompok bukti VM dan sensor informasi rahasia.

Aturan dosen: pengujian hanya pada VM/laptop sendiri, tidak memakai malware nyata, dan versi rentan hanya dijalankan di localhost. Syarat dosen dibedakan dari rincian tambahan proyek seperti target IP dan tiket; jika kondisi aktual berbeda, catat dan verifikasi sebelum mengklaim selesai.

## 1. Tujuan dan aturan penting

Kamu akan menjalankan aplikasi Laravel aman di Ubuntu, memasang HTTPS, memperketat akses Linux dan firewall, membuat backup, lalu mengumpulkan bukti 10 syarat. Pengujian hanya pada komputer dan VM sendiri; data harus fiktif.


| Yang sudah diketahui                     | Nilai                                                |
| ---------------------------------------- | ---------------------------------------------------- |
| Windows, adapter VMware host-only VMnet1 | `192.168.136.1/24`                                   |
| Ubuntu, interface host-only `ens33`      | `192.168.136.128/24`, DHCP; bisa berubah             |
| Akun operator                            | `ridha0010`; akun lama `ridha` tetap ada             |
| Ethernet Windows `192.168.1.5`           | Internet rumah, **bukan** alamat host-only           |
| Target sistem                            | Ubuntu 24.04 LTS, PHP 8.3, Nginx, SQLite, Laravel 12 |


Alamat `192.168.56.10` di beberapa dokumen proyek adalah **baseline lama**, bukan IP VM yang sudah terbukti saat ini. Pakai IP aktual saat membuat sertifikat dan aturan firewall; catat perbedaan ini di manifest. Jika memilih IP statis baru, misalnya `192.168.136.10`, pastikan tidak bentrok dan berada di luar rentang DHCP. Semua contoh di bawah memakai **`192.168.136.128`**; ganti seluruh contoh IP VM secara konsisten bila IP berubah. Jangan langsung mengubah Netplan tanpa memeriksa konfigurasi dan snapshot.

**Koreksi arahan sebelumnya:** alamat dan route dari `ip addr` tidak sendirinya membuktikan tipe adapter VMware; VMnet1 dan pengaturan VMware yang mengonfirmasi host-only. Tes `ping` hanya menunjukkan koneksi, bukan jaminan alamat belum dipakai. Tidak wajib menambah adapter host-only kedua jika `ens33` sudah terhubung ke VMnet1.

### Cara membaca perintah

- **Ubuntu:** jalankan di terminal VM; `$` atau `#` pada prompt tidak perlu diketik.
- **Windows PowerShell:** jalankan di laptop host, bukan di terminal Ubuntu.
- `sudo` meminta hak administrator; saat mengetik password Linux tidak terlihat karakter, itu normal.
- `nano` adalah editor: `Ctrl+O`, `Enter` untuk menyimpan; `Ctrl+X` untuk keluar. Jangan menyimpan jika belum yakin.
- Teks seperti `<URL_REPO>` adalah contoh yang harus diganti, bukan diketik beserta tanda `< >`.
- Jika muncul kesalahan, **berhenti** dan baca pesan; jangan lanjut membabi buta atau menghapus data.

### Pagar keamanan sebelum mulai

1. Ambil snapshot VM dari menu VMware dan pastikan console VM bisa diakses. Snapshot bukan pengganti backup terpisah.
2. Gunakan hanya akun dan data fiktif. Jangan tunjukkan atau unggah password, `.env`, kunci, cookie, token, atau database aktif.
3. Jangan jalankan `migrate:fresh`, reset database, atau penghapusan data pada database kerja. Uji kerusakan dan restore hanya di salinan terisolasi.
4. Sebelum perubahan SSH atau firewall, pertahankan sesi lama dan buka console VMware untuk pemulihan.
5. Versi rentan hanya untuk localhost `127.0.0.1:8001`, tidak boleh di-proxy, di-forward, atau dipublikasikan.

## 2. Urutan pekerjaan dan bukti


| Urutan | Tiket     | Tujuan                                                                                                |
| ------ | --------- | ----------------------------------------------------------------------------------------------------- |
| 1      | FIX-01    | Selesaikan pembuktian dua operasi bersaing terhadap SQLite file sebelum menyatakan perbaikan selesai. |
| 2      | TKT-18    | Siapkan VM, akun, aplikasi, permission dan hardening.                                                 |
| 3      | TKT-19    | Aktifkan HTTPS dan uji cookie sesi.                                                                   |
| 4      | TKT-20    | Ambil bukti sebelum firewall, lalu pasang UFW dan uji sesudah.                                        |
| 5      | TKT-21    | Buat backup terenkripsi dengan cron dan buktikan restore.                                             |
| 6      | TKT-22–25 | Uji isolasi versi rentan dan perbandingan SQLi, XSS, IDOR/sesi.                                       |
| 7      | TKT-26–31 | Uji menyeluruh, audit bukti, laporan, video, paket dan latihan.                                       |


FIX-01: kode perbaikan pengembalian dan pembaruan stok sudah ada, tetapi pengujian dua proses konkuren pada SQLite berbasis file belum tercatat. Test snapshot lama secara berurutan saja tidak membuktikan konkurensi. Jalankan dua proses/request yang benar-benar bersaing pada database uji terpisah; catat hasil sukses/gagal, kemungkinan `database locked`, dan stok akhir untuk pengembalian ganda serta pembaruan stok yang bersamaan dengan peminjaman/pengembalian. Perbaiki dan ulangi bila invariant stok tidak terjaga. Jangan menandai selesai sebelum ada bukti yang dapat diulang.

Setiap kelompok bukti terminal VM diawali:

```bash
whoami; date
```

Simpan gambar/log tersanitasi pada `docs/bukti/SEC-xx/`, catat tanggal, branch/commit, tujuan dan hasil aktual di `docs/manifest-hasil-uji.md`. Hitung hash setiap bukti dengan `sha256sum <file-bukti>`. Jangan tampilkan hash password lengkap, session ID, token, key, atau password.

## 3. Persiapan jaringan dan paket — TKT-18

### 3.1 Pastikan alamat dan akses

**Ubuntu:**

```bash
whoami; date
ip -br addr
ip route
hostnamectl
```

**Windows PowerShell:**

```powershell
ipconfig
ping 192.168.136.128
```

Hasil yang diharapkan: VMnet1 Windows `192.168.136.1`, Ubuntu `ens33` punya alamat satu subnet, dan ping berhasil. Jika IP VM berubah, pakai alamat baru yang tampil. Pastikan VMnet1 memang host-only di pengaturan VMware. Agar alamat tetap, pilih DHCP reservation di VMware bila didukung, atau konfigurasikan IP statis setelah membaca `/etc/netplan/*.yaml`, rentang DHCP dan panduan Netplan. Untuk perubahan jaringan gunakan `sudo netplan try` dari console VMware; catat isi awal dan jangan mengganti seluruh konfigurasi secara sembarangan. Bila tidak yakin, hentikan dan minta bantuan dengan isi konfigurasi yang sudah disensor.

### 3.2 Internet sementara

Host-only dapat tidak memiliki internet. Adapter host-only adalah `ens33`, sedangkan NAT kedua adalah `ens37`. Pada pemeriksaan berikutnya `ens37` terlihat mendapat IPv4 `192.168.64.129`; cek ulang karena IP DHCP dapat berubah. Jika internet sudah berfungsi, lewati langkah perbaikan NAT dan lanjut ke bagian 3.3.

#### A. Periksa pengaturan VMware

1. Di VMware buka **VM → Settings**.
2. Pastikan **Network Adapter** pertama tetap **Host-only**.
3. Pilih **Network Adapter 2** dan pastikan **NAT**, **Connected**, serta **Connect at power on** dicentang.
4. Klik **OK**. Jika adapter kedua belum ada, matikan VM dengan `sudo poweroff`, lalu gunakan tombol **Add → Network Adapter** untuk menambahkannya sebagai NAT.

#### B. Periksa interface di Ubuntu

Di console VMware jalankan:

```bash
ip -br addr
ip route
```

`ip -br addr` menunjukkan nama interface dan IP; `ip route` menunjukkan jalur jaringan. Pertahankan `ens33` untuk host-only. Jika `ens37` terlihat `DOWN`, aktifkan sementara:

```bash
sudo ip link set ens37 up
```

Perintah ini mengaktifkan interface, **tetapi tidak otomatis menjamin IP DHCP**. Jalankan kembali `ip -br addr`. Jika `ens37` sudah punya IPv4 dan `ip route` menampilkan rute `default` melalui interface NAT, lanjut ke bagian D.

#### C. Jika ens37 UP tetapi belum mendapat IPv4

Lakukan dari **console VMware**, bukan hanya melalui SSH, agar tetap bisa memperbaiki jaringan jika koneksi terputus. Pastikan snapshot VM tersedia. Baca konfigurasi yang ada:

```bash
ls -l /etc/netplan
sudo cat /etc/netplan/*.yaml
```

Pada pemeriksaan sebelumnya, `50-cloud-init.yaml` hanya mengatur DHCP `ens33`. Jangan menghapus atau mengganti file itu. Jika konfigurasi sekarang sudah memuat `ens37`, atau berbeda dari kondisi tersebut, periksa konflik sebelum melanjutkan. Buat file tambahan **hanya jika belum ada**:

```bash
sudo test -e /etc/netplan/60-nat-sementara.yaml && echo 'File sudah ada: periksa isinya dahulu' || echo 'File belum ada'
```

Jika belum ada, buka editor:

```bash
sudo nano /etc/netplan/60-nat-sementara.yaml
```

Masukkan konfigurasi berikut memakai spasi, bukan tab:

```yaml
network:
  version: 2
  ethernets:
    ens37:
      dhcp4: true
```

Artinya: minta alamat IPv4 otomatis untuk adapter NAT `ens37`; `ens33` tetap dikelola konfigurasi sebelumnya. Di Nano tekan **Ctrl+O**, **Enter**, lalu **Ctrl+X**. Batasi izin dan uji:

```bash
sudo chmod 600 /etc/netplan/60-nat-sementara.yaml
sudo netplan try
```

Jika muncul pesan error atau jaringan tidak sesuai, jangan konfirmasi; tunggu rollback otomatis dan periksa dari console. Jika uji berhasil, tekan Enter saat diminta untuk menerima. Konfigurasi tambahan tetap ada di disk; rollback mengembalikan konfigurasi aktif, jadi perbaiki atau nonaktifkan file tambahan yang bermasalah sebelum mencoba lagi. Jangan menghapus file konfigurasi yang tidak dikenal.

#### D. Verifikasi internet dan DNS

```bash
ip -br addr
ip route
getent hosts archive.ubuntu.com
sudo apt update
```

Harapan: `ens33` tetap memiliki `192.168.136.x`, `ens37` mendapat IPv4 NAT—biasanya `192.168.64.x`, tetapi bisa berbeda—dan terdapat rute `default` melalui NAT. `getent` menguji penerjemahan DNS; `apt update` menguji akses repositori. Jika gagal, simpan pesan error dan periksa pengaturan VMware, DHCP NAT serta output perintah di atas. Jangan memindai jaringan selain VM sendiri.

Gunakan NAT hanya untuk memperoleh paket dan build. Setelah seluruh unduhan selesai, matikan VM, putuskan **Network Adapter 2** saja di VMware, nyalakan kembali, dan pastikan `ens33` masih dapat diakses dari Windows. File Netplan DHCP tambahan dapat tetap ada saat adapter diputus; jangan menghapusnya tanpa memastikan file tersebut memang dibuat untuk langkah ini. Alamat host-only `192.168.136.128` masih DHCP dan dapat berubah; selalu cek alamat aktual.

#### E. Pasang SSH agar terminal Ubuntu dapat dikendalikan dari Windows

Setelah `sudo apt update` berhasil, di Ubuntu:

```bash
sudo apt install openssh-server
sudo systemctl enable --now ssh
sudo systemctl status ssh --no-pager
```

Saat instalasi, jawab `y` jika diminta. Hasil yang diharapkan adalah `active (running)`. Instalasi ini juga tercakup dalam daftar paket pada bagian 3.4; memasang paket yang sudah ada lagi tidak diperlukan.

Lihat IP host-only aktual:

```bash
ip -br addr
```

Di **Windows PowerShell**, sambungkan menggunakan akun Ubuntu yang saat ini sudah ada:

```powershell
ssh ridha@192.168.136.128
```

Ganti alamat dengan IPv4 `ens33` jika berubah. Pada koneksi pertama Windows menampilkan fingerprint server. Untuk memeriksanya, di console Ubuntu jalankan:

```bash
sudo ssh-keygen -lf /etc/ssh/ssh_host_ed25519_key.pub
```

Bandingkan dengan fingerprint **ED25519** yang ditampilkan Windows; hanya jika cocok, ketik `yes`. Masukkan password akun Ubuntu secara privat; karakter tidak terlihat ketika diketik. Setelah masuk, prompt menjadi `ridha@perpus-server:~$` atau sejenisnya. Sekarang perintah yang diketik di PowerShell berjalan pada Ubuntu; salin-tempel menggunakan klik kanan atau `Ctrl+Shift+V` sesuai terminal Windows. Ketik `exit` untuk keluar dari SSH.

Jika `Connection refused`, periksa status SSH; jika timeout, cek IP `ens33`, ping dan firewall; jika `Permission denied`, cek username/password. **Jangan matikan login password atau ubah aturan SSH dulu.** Bagian 4 memandu pembuatan dan pengujian SSH key pada akun `ridha0010` sebelum hardening.

### 3.3 Akun non-root dan akun layanan

Dokumen tugas meminta admin non-root, akun layanan tanpa shell, serta bukti `whoami` dengan nama depan + empat digit terakhir NIM. Akun proyek yang dipilih `ridha0010`. Akun lama `ridha` **tetap dipertahankan**, jangan dihapus.

**Ubuntu:**

```bash
id ridha0010
```

Jika akun belum ada, buat dan beri hak sudo:

```bash
sudo adduser ridha0010
sudo usermod -aG sudo ridha0010
su - ridha0010
whoami
sudo -v
```

`adduser` membuat akun; `usermod` menambah grup sudo. Masukkan password secara privat. Hasil `whoami` harus `ridha0010`. Jika akun sudah ada, cek `id ridha0010` dan login tanpa membuat ulang. `www-data` adalah akun layanan PHP-FPM/Nginx yang biasanya sudah ada; cek setelah pemasangan paket:

```bash
id www-data
getent passwd www-data
```

Shell akhir seharusnya `/usr/sbin/nologin` atau `/bin/false`. Jangan mengubah shell tanpa memeriksa dampaknya pada layanan.

### 3.4 Instal perangkat lunak

Setelah NAT sementara dan DNS berfungsi, sebagai `ridha0010` di Ubuntu:

```bash
sudo apt update
sudo apt install nginx openssh-server php8.3-cli php8.3-fpm php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip sqlite3 composer git unzip openssl curl ufw cron nodejs npm
```

Perintah mengunduh paket, bukan menginstal aplikasi proyek. Jika paket tidak ditemukan, cek `cat /etc/os-release` dan sumber paket; jangan menambah repositori tidak dikenal secara asal. Cek:

```bash
php -v
composer --version
node --version
npm --version
nginx -v
sqlite3 --version
sudo systemctl status nginx php8.3-fpm ssh cron --no-pager
```

Jika Node/NPM terlalu lama untuk Vite proyek, jangan memaksa instalasi; gunakan Node versi yang memenuhi `package-lock.json`, atau bangun aset pada mesin tepercaya yang kompatibel lalu pindahkan hasilnya. Catat versi aktual. Dokumen tugas juga meminta daftar layanan aktif; periksa sebelum menonaktifkan layanan yang tidak perlu, jangan menonaktifkan layanan yang belum dipahami:

```bash
systemctl list-units --type=service --state=running
```

## 4. SSH key dan hardening — TKT-18 / SEC-05

Tujuan: administrasi oleh `ridha0010`, bukan login root. Password SSH boleh dinonaktifkan **setelah** login dengan kunci diuji. Snapshot dan console wajib tersedia.

### 4.1 Pahami dua tempat dan dua jenis key

SSH key adalah pasangan dua file yang dibuat di **Windows**. Private key tetap di Windows; public key disalin ke Ubuntu agar Ubuntu mengenali Windows kamu.


| Tempat  | File contoh                                  | Fungsi                                                                           |
| ------- | -------------------------------------------- | -------------------------------------------------------------------------------- |
| Windows | `C:\Users\Administrator\.ssh\ssh_perpus`     | **Private key:** rahasia; jangan dibagikan, ditampilkan, atau disalin ke Ubuntu. |
| Windows | `C:\Users\Administrator\.ssh\ssh_perpus.pub` | Public key: isinya boleh dipasang pada Ubuntu.                                   |
| Ubuntu  | `/home/ridha0010/.ssh/authorized_keys`       | Daftar public key yang diizinkan masuk sebagai `ridha0010`.                      |


Nama `ssh_perpus` hanya contoh dan bebas diganti; gunakan nama pilihanmu secara konsisten pada semua langkah. Jangan menimpa key yang sudah ada. Di Windows prompt PowerShell biasanya `PS C:\...>`; setelah login SSH prompt berubah menjadi `ridha0010@perpus-server:~$` dan perintah dijalankan di **Ubuntu**. Ketik `exit` untuk kembali ke Windows.

### 4.2 Buat pasangan key di Windows

1. Jika sedang berada di Ubuntu melalui SSH, ketik `exit` untuk kembali ke PowerShell Windows. Periksa file yang sudah ada:
   ```powershell
    Get-ChildItem $env:USERPROFILE\.ssh
   ```

    Jika nama `ssh_perpus` sudah ada, jangan timpa; gunakan key yang memang milikmu atau pilih nama baru. File `known_hosts` bukan pasangan key login.
2. **Windows PowerShell:** buat pasangan key baru:
   ```powershell
    ssh-keygen -t ed25519 -f "$env:USERPROFILE\.ssh\ssh_perpus"
   ```

    Saat diminta *passphrase*, isi sandi pelindung key dan ulangi, atau tekan Enter dua kali jika memilih tanpa passphrase. Karakter tidak terlihat saat diketik. Passphrase adalah pelindung private key dan berbeda dari password akun Ubuntu.
3. **Windows PowerShell:** pastikan pasangan file ada:
   ```powershell
    Get-ChildItem "$env:USERPROFILE\.ssh"
   ```

    Harus terlihat `ssh_perpus` dan `ssh_perpus.pub`. Tampilkan **hanya public key**:

    ```powershell
    Get-Content "$env:USERPROFILE\.ssh\ssh_perpus.pub"
    ```

    Salin seluruh satu baris yang diawali `ssh-ed25519`. Jangan tampilkan isi file tanpa `.pub`.

### 4.3 Pasang public key di Ubuntu

1. **Windows PowerShell:** sambungkan dengan password seperti yang sudah berhasil sebelumnya:
   ```powershell
    ssh ridha0010@192.168.136.128
   ```

    Jika IP host-only Ubuntu berubah, gunakan IP `ens33` yang aktual. Masukkan password Ubuntu tanpa membagikannya. Setelah masuk, periksa:

    ```bash
    whoami
    ```

    Hasil harus `ridha0010`; jika bukan, jangan lanjut memasang key di akun yang salah.
2. **Ubuntu sebagai `ridha0010`:** buat direktori jika belum ada dan buka daftar public key:
   ```bash
    mkdir -p ~/.ssh
    chmod 700 ~/.ssh
    nano ~/.ssh/authorized_keys
   ```

    Tempel public key yang disalin dari Windows sebagai **satu baris baru**. Jangan hapus baris yang sudah ada. Simpan dengan **Ctrl+O**, tekan **Enter**, lalu keluar dengan **Ctrl+X**. Batasi izin file:

    ```bash
    chmod 600 ~/.ssh/authorized_keys
    ```

    Perintah `mkdir -p` tidak menghapus direktori yang sudah ada; izin `700` untuk folder dan `600` untuk file membatasi akses pengguna lain.

### 4.4 Uji SSH key sebelum mematikan password

Biarkan sesi Ubuntu yang sudah masuk tetap terbuka. Buka **jendela PowerShell Windows baru**, lalu jalankan:

```powershell
ssh -i "$env:USERPROFILE\.ssh\ssh_perpus" -o PreferredAuthentications=publickey -o PasswordAuthentication=no ridha0010@192.168.136.128
```

Opsi `PasswordAuthentication=no` memastikan tes tidak diam-diam beralih ke password akun Ubuntu. Jika diminta passphrase key yang kamu buat pada langkah 4.2, itu normal. Setelah masuk, jalankan `whoami` dan `sudo -v`; hasil `whoami` harus `ridha0010`. `sudo` tetap boleh meminta password Ubuntu, terpisah dari autentikasi SSH.

Jika muncul `Identity file ... not accessible`, nama/path private key salah atau file belum dibuat. Jika `Permission denied (publickey)`, periksa public key satu baris di akun `ridha0010` dan izin `.ssh`. Jika IP tidak bisa dijangkau, cek `ip -br addr` di console VMware. **Jangan matikan password login jika tes key gagal.**

### 4.5 Hardening SSH setelah key berhasil

1. **Ubuntu/console VMware:** setelah tes key berhasil, ambil snapshot dan pertahankan sesi SSH lama. Periksa file konfigurasi yang ada dengan `ls -l /etc/ssh/sshd_config.d/`; jangan menimpa file yang tidak dikenal.
2. **Ubuntu:** bila file `00-mini-library.conf` belum ada, buat dengan `sudo nano /etc/ssh/sshd_config.d/00-mini-library.conf`. Isi:

   ```text
   PermitRootLogin no
   MaxAuthTries 3
   PubkeyAuthentication yes
   PasswordAuthentication no
   ```

3. Simpan, lalu jalankan `sudo sshd -t` dan `sudo sshd -T | grep -E 'permitrootlogin|maxauthtries|passwordauthentication|pubkeyauthentication'`. Jika validasi error atau nilai efektif tidak sesuai, periksa konflik; **jangan reload**.
4. Jika validasi benar, `sudo systemctl reload ssh`. Di jendela Windows baru ulangi tes key pada langkah 4.4. Jangan menutup sesi lama sampai berhasil. Jika terkunci, perbaiki dari console VMware. Login password dinonaktifkan hanya setelah key terbukti.

## 5. Deploy aplikasi aman — TKT-18

### 5.1 Salin source dan instal dependensi

Branch `main` aman; branch `vulnerable` jangan dipasang ke direktori web. **Untuk kondisi kamu repo sudah ada:** jangan ulangi clone atau membuat folder. Perintah di bawah hanya untuk instalasi baru pada folder kosong. Jangan menyalin `.env`, database lama, atau key ke Git.

```bash
sudo install -d -o ridha0010 -g www-data -m 750 /var/www/library-security
cd /var/www/library-security
git clone --branch main --single-branch https://github.com/muhammadRidha-15/library-security.git .
git switch main
git status --short
git log -1 --oneline
composer install
npm ci
npm run build
```

1. Cek lokasi dengan `pwd` dan `ls -la`. Jika `artisan`, `composer.json` dan `.git` sudah ada, **lewati clone**. `git branch --show-current` harus `main`.
2. Jalankan `composer install` sebagai `ridha0010` tanpa sudo. Pastikan `vendor/autoload.php` ada sebelum memakai `php artisan`; jika gagal, berhenti dan baca error.
3. Periksa `node -v` dan `npm -v`, lalu jalankan `npm ci` dan `npm run build`. Keduanya memakai lockfile yang benar-benar di-clone dan memerlukan Node yang kompatibel; jangan hapus lockfile untuk melewati error. Build Vite membutuhkan devDependencies NPM.
4. Pada instalasi baru saja lanjutkan ke `.env` pada 5.2. Jika clone gagal karena folder tidak kosong, jangan hapus isinya. `composer install` dan `npm ci` memerlukan internet sementara dan akses repo/dependensi.

### 5.2 Siapkan data fiktif dengan aman

Seeder proyek **menolak** `APP_ENV=production`, walaupun diberi `--force`. Oleh karena itu siapkan **database baru khusus lab** di mode lokal sementara, lalu kembali ke production sebelum server dibuka. Jangan ubah environment ini pada sistem yang sudah memiliki data penting.

Pertama periksa apakah `.env` sudah ada:

```bash
cd /var/www/library-security
test -e .env && echo '.env sudah ada: jangan buat key ulang' || echo '.env belum ada'
```

**Hanya jika `.env` belum ada** dan ini instalasi baru, jalankan:

```bash
cp .env.example .env
php artisan key:generate
```

Jika `.env` sudah ada, **jangan** menjalankan `key:generate` ulang secara sembarangan: mengganti key dapat merusak akses data terenkripsi/sesi. Jika key pernah terbuka kepada orang lain, perlakukan sebagai bocor; pada instalasi baru tanpa data atau sesi yang perlu dipertahankan, ganti key yang bocor sekali sebelum mulai memakai aplikasi. Jika data penting sudah ada, rencanakan rotasi dahulu.

#### Setelah APP_KEY: isi password akun fiktif sebelum migrasi/seeder

**Tujuan:** memberi seeder password untuk membuat dua akun pengguna dan satu admin. Langkah ini wajib dilakukan **sebelum** `php artisan migrate --seed` atau `php artisan db:seed`. Pastikan posisi terminal di folder utama proyek:

```bash
cd /var/www/library-security
pwd
nano .env
```

Di dalam editor, cari baris berikut dan **edit baris yang sudah ada**, jangan menambahkan duplikat. Teks di bawah adalah penjelasan—ganti keterangan dengan nilai yang kamu pilih sendiri di `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=false
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/library-security/database/database.sqlite
SEED_USER_PASSWORD=PASSWORD_FIKTIF_USER_PILIHANMU
SEED_ADMIN_PASSWORD=PASSWORD_FIKTIF_ADMIN_PILIHANMU
```

Gunakan password fiktif minimal 12 karakter dan maksimal 72 byte. Pilih password admin berbeda; kedua akun user fiktif sengaja memakai password user yang sama agar dapat membuktikan salt menghasilkan hash berbeda. Gunakan huruf dan angka tanpa spasi agar mudah dimasukkan ke `.env`. Jangan memakai contoh di atas sebagai password sebenarnya. Pastikan `APP_ENV=local`: seeder proyek menolak `production` meskipun memakai `--force`.

Simpan di Nano dengan **Ctrl+O**, **Enter**, lalu **Ctrl+X**. Jangan jalankan `cat .env`, jangan screenshot editor yang berisi rahasia, dan jangan kirim password atau key ke chat. Batasi izin selama penyiapan dengan `chmod 600 .env`. Perintah `php artisan config:clear` pada langkah database berikutnya memastikan Laravel membaca konfigurasi terbaru.

Untuk key telepon, secara privat jalankan `openssl rand -base64 32`, salin hasil ke nilai `TELEPON_KEY_V1=base64:<hasil>` di `.env`. Jangan rekam layar/terminal saat key ditampilkan, jangan simpan di Git, dan jangan mengganti key pada data lama tanpa rencana migrasi. Simpan recovery key secara aman terpisah dari backup database.

**Selalu dari folder utama proyek**, bukan dari dalam `database/`:

```bash
cd /var/www/library-security
pwd
```

Hasil `pwd` harus `/var/www/library-security`. Pada instalasi baru, periksa dulu apakah database sudah ada:

```bash
test -e database/database.sqlite && echo 'Database sudah ada: periksa sebelum lanjut' || echo 'Database belum ada'
```

`test -e` memeriksa apakah file ada. Jika file sudah ada, **berhenti dan periksa isinya**; jangan menimpanya atau langsung menjalankan seeder. Jika belum ada dan kamu ingin menyiapkan database lab baru, jalankan satu per satu:

```bash
touch database/database.sqlite
php artisan config:clear
php artisan migrate --seed
```

`touch` membuat file SQLite kosong; file itu belum berisi tabel. `config:clear` membersihkan cache konfigurasi Laravel, sedangkan `migrate --seed` membuat tabel dan data fiktif. Lanjutkan hanya jika setiap perintah sebelumnya berhasil.

Pada catatan lama kamu mungkin melihat versi singkat ini:

```bash
test ! -e database/database.sqlite && touch database/database.sqlite
```

Artinya, `test ! -e` memeriksa bahwa file **belum ada**, lalu `&&` menjalankan `touch` hanya jika kondisi itu benar. Jika file sudah ada, perintah tersebut tidak menimpanya. Namun, perintah itu **tidak otomatis menghentikan perintah migrasi yang diketik setelahnya**; karena itu panduan ini memisahkan pemeriksaan dan pembuatan agar kamu bisa mengambil keputusan dahulu.

Akun fiktif: `admin@perpustakaan.test`, `user_a@perpustakaan.test`, `user_b@perpustakaan.test`. Seeder tidak menimpa akun yang sudah ada. Jangan mencatat password.

#### Jika migrasi sudah berhasil tetapi seeder gagal karena password fixture

Kondisi ini bisa terjadi jika Laravel sudah membuat database dan semua migrasi berstatus `DONE`, tetapi menampilkan `Password fixture harus disediakan dan berukuran 8-72 byte`. **Jangan hapus database dan jangan jalankan `migrate:fresh`.** Isi password di `.env` sesuai petunjuk di atas, lalu dari folder utama proyek jalankan:

```bash
cd /var/www/library-security
php artisan config:clear
php artisan db:seed
```

Jika seeder kembali ke prompt tanpa error, cek `php artisan migrate:status`: migrasi yang telah berhasil seharusnya berstatus `Ran`. Jika seeder tetap gagal, catat pesan error tanpa menampilkan isi `.env`. Jika database berisi data yang bukan fixture ini, periksa isinya dan dampak seeder sebelum menjalankannya.

Edit `.env` menjadi:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mini-library.test
SESSION_DRIVER=file
SESSION_COOKIE=mini_library_session
SESSION_SECURE_COOKIE=true
SESSION_COOKIE_HTTPONLY=true
SESSION_SAME_SITE=lax
```

Hapus nilai `SEED_USER_PASSWORD` dan `SEED_ADMIN_PASSWORD` dari `.env` setelah fixture dibuat; password login tetap yang telah dibuat. Jalankan `php artisan config:clear` sebelum cache ulang. Database berada di luar `public`, sesuai kebutuhan. Aktifkan dan verifikasi WAL untuk database lab:

```bash
sqlite3 database/database.sqlite 'PRAGMA journal_mode=WAL;'
sqlite3 database/database.sqlite 'PRAGMA journal_mode;'
```

Hasil yang diharapkan `wal`. Pengaturan foreign key SQLite berlaku per koneksi; periksa `config/database.php`, konfigurasi efektif dan pengujian aplikasi, jangan mengklaim semua koneksi aktif hanya dari satu perintah CLI.

### 5.3 Izin file — jangan membuat aplikasi kehilangan akses

Dokumen dosen meminta rahasia mode `600` milik akun layanan. Untuk lab ini `.env` dimiliki `www-data` agar PHP-FPM bisa membaca file tersebut; private key TLS dibaca proses master Nginx sebagai root, **bukan** worker `www-data`. Ini kompromi teknis yang perlu dijelaskan ke dosen karena dokumen tugas menyebut kepemilikan akun layanan. Jangan menaruh secret dalam screenshot.

**Langkah 5.3.1 — Periksa sebelum mengubah:** ini langkah untuk penyiapan, jangan mengulang perubahan kepemilikan secara rekursif jika sudah dilakukan. Folder utama tetap milik operator agar `ridha0010` bisa masuk. `www-data` harus bisa menelusuri folder melalui grupnya.

```bash
sudo chown ridha0010:www-data /var/www/library-security
sudo chmod 750 /var/www/library-security
sudo chown www-data:www-data /var/www/library-security/.env
sudo chmod 600 /var/www/library-security/.env
sudo chown -R www-data:www-data /var/www/library-security/storage /var/www/library-security/bootstrap/cache /var/www/library-security/database
sudo chmod -R u+rwX,go-rwx /var/www/library-security/storage /var/www/library-security/bootstrap/cache /var/www/library-security/database
```

**Langkah 5.3.2 — Verifikasi akses:** jangan mengubah kepemilikan source secara rekursif tanpa memeriksa `git status` dan file lain. Source harus dapat dibaca worker tetapi tidak writable olehnya; direktori SQLite writable untuk WAL/SHM. Perintah Artisan yang memerlukan `.env` dan runtime dijalankan sebagai akun layanan:

```bash
cd /var/www/library-security
sudo -u www-data php artisan config:cache
sudo -u www-data test -r /var/www/library-security/.env && echo 'Akun layanan dapat membaca konfigurasi'
sudo -u www-data test -w /var/www/library-security/storage && echo 'Storage dapat ditulis'
sudo -u www-data test -w /var/www/library-security/database && echo 'Direktori database dapat ditulis'
sudo -u www-data test ! -w /var/www/library-security/app && echo 'Source app tidak writable oleh worker'
sudo ls -l .env
```

Jika tes source gagal, hentikan: pastikan `app/` dan source lain dimiliki operator/root, bukan `www-data`, permission read-only untuk worker, tanpa `777`. Config cache dapat berisi rahasia; lindungi `bootstrap/cache` dan jangan sertakan dalam paket. Setelah mengubah `.env`, jalankan `sudo -u www-data php artisan config:clear` lalu `sudo -u www-data php artisan config:cache`.

**Langkah 5.3.3 — Siapkan symlink unggahan:** `storage` adalah folder data Laravel, sedangkan `public/storage` adalah jalan pintas ke unggahan. Dalam kondisi kamu symlink **sudah dibuat**; cukup periksa, jangan buat ulang:

```bash
ls -ld public/storage
sudo -u www-data test -d /var/www/library-security/public/storage && echo 'Target dapat diakses www-data'
sudo -u www-data test ! -w /var/www/library-security/public && echo 'public tidak writable oleh worker'
```

Harapan: link menunjuk ke `../storage/app/public` dan kedua pesan sukses tampil. Jika `public/storage` belum ada, periksa dulu `sudo ls -ld storage/app/public`; bila target ada dan `public` memang writable oleh operator, jalankan `ln -s ../storage/app/public public/storage` sebagai `ridha0010`. Jika sudah ada link/file yang berbeda, **jangan hapus atau timpa**; periksa dulu. `sudo -u www-data php artisan storage:link` gagal pada konfigurasi ini karena worker tidak boleh menulis `public`; Artisan sebagai operator juga dapat gagal mengakses log yang hanya writable oleh `www-data`. Uji foto lewat browser setelah Nginx aktif.

## 6. HTTPS dan Nginx — TKT-19 / SEC-04

Gunakan alamat VM yang benar-benar dipilih. Di bawah ini `192.168.136.128`; jika berganti menjadi `.10`, ganti di sertifikat, host, tes dan manifest. Pastikan alamat stabil sebelum membuat sertifikat.

### 6.1 Buat sertifikat di Ubuntu

**Langkah 6.1.1 — Cek IP dan file yang sudah ada.** Di Ubuntu jalankan `ip -br addr` dan pastikan IPv4 `ens33` benar serta stabil. Contoh di bawah menggunakan `192.168.136.128`; alamat DHCP dapat berubah. Jika IP berubah nanti, sertifikat, Nginx, hosts dan UFW perlu disesuaikan. Periksa `sudo ls -l /etc/nginx/tls/` jika direktori sudah ada. **Jangan jalankan pembuatan key di bawah jika file sertifikat/key sudah ada**: perintah OpenSSL dapat menimpanya dan memutus layanan. Periksa dulu.

**Langkah 6.1.2 — Buat sertifikat baru.** Jalankan di terminal Ubuntu; jangan rekam output kunci privat:

```bash
sudo install -d -o root -g root -m 700 /etc/nginx/tls
sudo openssl req -x509 -newkey rsa:2048 -sha256 -nodes -days 365 \
  -keyout /etc/nginx/tls/mini-library.key \
  -out /etc/nginx/tls/mini-library.crt \
  -subj '/CN=mini-library.test' \
  -addext 'subjectAltName=DNS:mini-library.test,IP:192.168.136.128'
sudo chmod 600 /etc/nginx/tls/mini-library.key
sudo chmod 644 /etc/nginx/tls/mini-library.crt
sudo openssl x509 -in /etc/nginx/tls/mini-library.crt -noout -subject -issuer -dates -ext subjectAltName
sudo ls -l /etc/nginx/tls/mini-library.key
```

**Langkah 6.1.3 — Periksa hasil.** Perintah `openssl x509` di atas harus menampilkan SAN DNS `mini-library.test` dan IP VM yang benar; `ls` harus menunjukkan key mode `-rw-------`. Output sertifikat publik boleh direkam; isi private key tidak boleh ditampilkan. Sertifikat self-signed tidak otomatis dipercaya browser karena tidak ditandatangani CA tepercaya; koneksi tetap terenkripsi, tetapi identitas harus diverifikasi lewat sertifikat yang kamu percaya sendiri.

### 6.2 Konfigurasi Nginx

**Langkah 6.2.1 — Cek sebelum membuat konfigurasi.** Di Ubuntu periksa socket PHP-FPM dengan `ls /run/php/` dan konfigurasi yang ada dengan `ls -l /etc/nginx/sites-available/ /etc/nginx/sites-enabled/`. Jika file `mini-library` sudah ada, baca dan pahami terlebih dahulu; jangan menimpa pekerjaan yang ada.

**Langkah 6.2.2 — Tulis konfigurasi.** Jika file belum ada, buka `sudo nano /etc/nginx/sites-available/mini-library`, masukkan isi berikut, sesuaikan IP dan socket, lalu simpan Ctrl+O, Enter, Ctrl+X:

```nginx
server {
    listen 192.168.136.128:443 ssl;
    server_name mini-library.test 192.168.136.128;
    root /var/www/library-security/public;
    index index.php;
    ssl_certificate /etc/nginx/tls/mini-library.crt;
    ssl_certificate_key /etc/nginx/tls/mini-library.key;
    ssl_protocols TLSv1.2 TLSv1.3;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location = /index.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ \.php$ { return 404; }
    location ~ /\.(?!well-known) { deny all; }
    location ~* \.(sqlite|sqlite3|db|bak|backup|sql)$ { deny all; }
}
```

**Langkah 6.2.3 — Periksa layanan lain.** Konfigurasi ini hanya melayani HTTPS pada IP host-only, tidak menyediakan listener HTTP sendiri. Periksa situs default dan listener lain dengan `ls -l /etc/nginx/sites-enabled/` dan `sudo ss -ltnp`. Bila situs default melayani port 80, nonaktifkan **hanya symlink default yang sudah dipastikan tidak dibutuhkan**, jangan hapus konfigurasi yang tidak dikenal.

Untuk memeriksa sebelum menonaktifkan situs default:

```bash
sudo cat /etc/nginx/sites-available/default
```

Pada VM kamu, file yang diperiksa adalah halaman bawaan Nginx (`root /var/www/html`) dan membuka port 80 pada IPv4 serta IPv6. Setelah memastikan tidak dipakai aplikasi lain, kamu sudah menonaktifkan **symlink** berikut:

```bash
sudo unlink /etc/nginx/sites-enabled/default
```

Perintah ini tidak menghapus file asli di `sites-available`. **Jangan ulangi `unlink` jika symlink sudah tidak ada.** Bila port 80 masih terbuka setelah reload, periksa konfigurasi/layanan lain; jangan sembarang menghapusnya.

**Langkah 6.2.4 — Aktifkan dan uji.** Buat symlink hanya jika belum ada. Jalankan perintah satu per satu; bila `nginx -t` gagal, **berhenti dan jangan reload**:

```bash
sudo ln -s /etc/nginx/sites-available/mini-library /etc/nginx/sites-enabled/mini-library
sudo nginx -t
```

Jika hasilnya `syntax is ok` dan `test is successful`, lanjut:

```bash
sudo systemctl reload nginx
sudo ss -ltnp
```

Pastikan port 443 mendengarkan di IP host-only. Jika symlink sudah ada, periksa dengan `ls -l` sebelum membuat ulang. Jika NAT masih aktif, binding host-only di atas mengurangi paparan, tetapi NAT tetap sementara.

**Hasil yang sudah kamu tunjukkan:** `nginx -t` sukses sebelum penonaktifan default; setelah reload, `ss -ltnp` menampilkan Nginx pada `192.168.136.128:443` dan tidak menampilkan port 80. SSH port 22 masih aktif. Ini membuktikan kondisi listener saat diperiksa, **belum** membuktikan halaman Laravel, cookie atau verifikasi sertifikat dari Windows. Jangan membuat ulang sertifikat atau symlink; lanjut ke 6.3. Jika validasi setelah perubahan default belum dicatat, jalankan `sudo nginx -t` lagi dan simpan hasilnya sebagai bukti.

### 6.3 Uji dari Windows

**Langkah 6.3.1 — Atur nama di Windows.** Buka Notepad sebagai Administrator → File → Open → pilih `C:\Windows\System32\drivers\etc\hosts` (ubah filter menjadi All Files). Tambahkan `192.168.136.128 mini-library.test` pada baris baru, sesuaikan IP bila berubah, lalu simpan.

**Langkah 6.3.2 — Salin sertifikat publik.** Direktori TLS sengaja tidak bisa dibaca operator. Di **Ubuntu**, buat salinan sertifikat publik di home operator:

```bash
sudo install -o ridha0010 -g ridha0010 -m 644 /etc/nginx/tls/mini-library.crt /home/ridha0010/mini-library.crt
```

Jika file tujuan sudah ada, periksa dulu karena `install` dapat menimpanya. Di **Windows PowerShell**, salin sertifikat publik saja menggunakan nama key SSH yang kamu pilih:

```powershell
scp -i "$env:USERPROFILE\.ssh\ssh_perpus" ridha0010@192.168.136.128:/home/ridha0010/mini-library.crt "$env:USERPROFILE\Downloads\mini-library.crt"
```

Jangan salin private key. Pastikan fingerprint sertifikat publik yang disalin cocok dengan sertifikat VM melalui kanal tepercaya sebelum dipakai untuk verifikasi.

**Langkah 6.3.3 — Uji HTTPS dari Windows PowerShell:**

```powershell
curl.exe --cacert "$env:USERPROFILE\Downloads\mini-library.crt" https://mini-library.test/
curl.exe -I --cacert "$env:USERPROFILE\Downloads\mini-library.crt" https://mini-library.test/.env
```

Harapkan respons aplikasi/redirect dan `.env` ditolak 403/404. Tes juga `/.git/config`, URL database dan backup; 403/404, bukan isinya. Jangan gunakan `-k` sebagai bukti trust. Di browser tampilkan HTTPS dan detail sertifikat; trust lokal browser adalah langkah tersendiri, dan hanya pasang sertifikat publik yang fingerprint-nya sudah diverifikasi. Uji login/logout, lalu di DevTools periksa cookie **sesi** `mini_library_session`: `Secure`, `HttpOnly`, `SameSite=Lax`; sensor nilainya. Uji akses halaman privat setelah logout dengan request baru.

## 7. Firewall — TKT-20 / SEC-06

Snapshot, console dan SSH key yang sudah terbukti diperlukan. Periksa `sudo ufw status verbose` dan `sudo ss -ltnp` dahulu. Jangan menonaktifkan firewall yang sudah aktif hanya demi membuat bukti; jika kondisi awal tidak sesuai, catat dan rencanakan ulang di VM uji terisolasi.

### 7.0 Siapkan perintah Nmap di Windows

**Tujuan:** Nmap dijalankan dari Windows sebagai klien uji untuk melihat port VM sendiri. Pada komputer kamu Nmap sudah ditemukan di `C:\Program Files (x86)\Nmap\nmap.exe`; tidak perlu menginstal ulang.

1. Buka **PowerShell di Windows**, bukan terminal Ubuntu. Periksa instalasi dengan alamat lengkap:

   ```powershell
   & "C:\Program Files (x86)\Nmap\nmap.exe" --version
   ```

   Jika versi tampil tetapi `nmap --version` tidak dikenali, Nmap berfungsi; foldernya kemungkinan belum ada di variabel `PATH` PowerShell. `PATH` adalah daftar folder yang dicari Windows saat kamu mengetik nama perintah.

2. **Pilihan sementara:** tambahkan folder untuk jendela PowerShell yang sedang dipakai:

   ```powershell
   $env:Path += ";C:\Program Files (x86)\Nmap"
   nmap --version
   ```

   Perubahan ini hilang ketika jendela PowerShell ditutup. Untuk tugas ini pilihan sementara sudah cukup.

3. **Pilihan permanen, jika diinginkan:** di Windows cari **Edit the system environment variables** → **Environment Variables...** → pada **User variables for Administrator**, pilih **Path** → **Edit** → **New**. Ketik manual satu baris berikut:

   ```text
   C:\Program Files (x86)\Nmap
   ```

   Jangan pakai tanda kutip, titik koma, atau karakter/baris baru tersembunyi di entri tersebut. Jangan hapus entri lain. Klik **OK** sampai semua dialog tertutup, tutup PowerShell, buka PowerShell baru, lalu jalankan `nmap --version`.

4. Jika muncul pesan `A path component can't contain any control characters`, hapus isi **entri Nmap yang sedang diedit saja**, lalu ketik ulang alamat tersebut secara manual. Jangan ubah entri lain. Jika tetap gagal, lewati pengaturan PATH dan jalankan Nmap memakai alamat lengkap seperti langkah 1; gunakan alamat lengkap yang sama diikuti opsi pemindaian pada langkah 7.1 dan 7.2.

### 7.1 Catat kondisi sebelum firewall

Di VM buka listener sementara yang tidak menyajikan data pribadi, gunakan direktori kosong:

```bash
mkdir -p /tmp/mini-library-firewall-test
cd /tmp/mini-library-firewall-test
python3 -m http.server 8080 --bind 192.168.136.128
```

Biarkan berjalan. Dari Windows host gunakan Nmap yang sudah terinstal di Windows (atau klien uji lain di lab):

```powershell
nmap -Pn -p 22,80,443,8080 192.168.136.128
curl.exe --connect-timeout 5 http://192.168.136.128:8080/
```

**[AMBIL FOTO SEC-06 SEBELUM]** Screenshot hasil Nmap dan curl di Windows sebelum mengubah aturan UFW. Simpan juga identitas VM dan waktu dengan `whoami; date` pada terminal Ubuntu. Jika port 8080 tidak dapat diakses, jangan menyimpulkan apa pun; cek listener dan jaringan. `nmap` di VM sendiri tidak menggantikan pengukuran dari Windows host.

### 7.2 Aktifkan UFW dan catat kondisi sesudah

Di console Ubuntu, dengan listener tetap hidup:

```bash
sudo ufw status numbered
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from 192.168.136.1 to 192.168.136.128 port 22 proto tcp
sudo ufw allow from 192.168.136.1 to 192.168.136.128 port 443 proto tcp
sudo ufw enable
sudo ufw status verbose
sudo ufw status numbered
```

Jangan menganggap penambahan aturan menghapus aturan lama: periksa setiap rule IPv4/IPv6 dan hapus **hanya rule yang sudah dipahami dan memang terlalu luas**. Lihat juga `sudo ss -ltnp`; tidak boleh ada forward/proxy ke 8001. Dari Windows ulangi Nmap dan curl sebelumnya, lalu uji SSH dan HTTPS. Port 8080 harus terblokir **saat listener yang sama tetap hidup**; status `closed` karena listener berhenti tidak membuktikan UFW. Catat `Port | Layanan | Sebelum | Sesudah`, jangan mengarang hasil. Hentikan listener dengan Ctrl+C setelah selesai. Jika SSH terputus, gunakan console VM untuk memeriksa rule dan perbaiki; jangan hapus semua aturan tanpa analisis.

**[AMBIL FOTO SEC-06 SESUDAH]** Screenshot Nmap/curl dari Windows dan screenshot Ubuntu yang memuat `whoami; date`, `sudo ufw status verbose`, serta listener 8080 masih `LISTEN`. Sensor nilai cookie atau token bila ikut terlihat. Kamu sudah menunjukkan hasil sebelum dan sesudah; simpan file gambar aslinya.

## 8. Backup dan restore — TKT-21 / SEC-07

**Tujuan:** menyelamatkan database, foto unggahan dan konfigurasi; buktikan pemulihan dari salinan di luar VM. Tugas meminta **cron**, bukan hanya timer systemd.

**Status sesuai percakapan:** skrip backup telah kamu jalankan secara manual, backup terenkripsi disalin ke Windows dan hash cocok. Salinan database hasil dekripsi dilaporkan `ok`, dengan 3 anggota, 3 buku, 2 peminjaman. Simulasi penghapusan dilaporkan sudah dilakukan; hasil akhir restore simulasi, foto yang benar-benar tersimpan, cron, login dan dekripsi pada instance terisolasi masih perlu dipastikan. Jangan mengulang atau menimpa file uji yang sudah ada tanpa memeriksa.

### 8.1 Titik foto wajib: sebelum, dihapus, dipulihkan

Tiga foto berikut adalah **tiga kondisi berbeda pada database simulasi**, bukan tiga foto hasil yang sama. Ambil screenshot segera setelah menjalankan perintah pada kondisi tersebut. Setiap kelompok bukti VM perlu `whoami; date`. Jika lupa mengambil foto suatu kondisi, ulangi percobaan pada salinan uji baru dan catat waktu serta hasil aktual; jangan membuat bukti palsu.

**PENTING:** perintah di bawah hanya untuk `/var/backups/library-security/restore-uji/simulasi.sqlite`. Jangan menghapus data dari database aktif `/var/www/library-security/database/database.sqlite`.

#### Foto 1 — Sebelum data simulasi dihapus

Pada saat salinan simulasi belum dihapus, jalankan di Ubuntu:

```bash
whoami; date
sudo sqlite3 /var/backups/library-security/restore-uji/simulasi.sqlite \
"SELECT 'anggota', COUNT(*) FROM anggota
UNION ALL SELECT 'buku', COUNT(*) FROM buku
UNION ALL SELECT 'peminjaman', COUNT(*) FROM peminjaman;"
```

**[AMBIL FOTO 1 SEKARANG — SEBELUM DIHAPUS]** Hasil pada percobaanmu diharapkan `anggota|3`, `buku|3`, `peminjaman|2`. Foto harus memperlihatkan perintah/path, hasil, dan identitas/waktu. Jika simulasi sudah diubah, jangan mengaku ini kondisi sebelumnya; ulangi dengan salinan baru.

#### Foto 2 — Setelah satu data simulasi dihapus

Setelah satu peminjaman dihapus pada **salinan simulasi saja**, jalankan query yang sama:

```bash
whoami; date
sudo sqlite3 /var/backups/library-security/restore-uji/simulasi.sqlite \
"SELECT 'anggota', COUNT(*) FROM anggota
UNION ALL SELECT 'buku', COUNT(*) FROM buku
UNION ALL SELECT 'peminjaman', COUNT(*) FROM peminjaman;"
```

**[AMBIL FOTO 2 SEKARANG — SETELAH DIHAPUS]** Pada percobaan tersebut hasil yang diharapkan adalah `anggota|3`, `buku|3`, `peminjaman|1`. Jangan jalankan penghapusan lagi jika sudah dilakukan. Simpan nama file dan waktu sebenarnya.

#### Foto 3 — Setelah database simulasi dipulihkan

Setelah memulihkan isi `simulasi.sqlite` dari arsip terenkripsi yang berasal dari Windows dan sudah diverifikasi hash-nya, jalankan:

```bash
whoami; date
sudo sqlite3 /var/backups/library-security/restore-uji/simulasi.sqlite "PRAGMA integrity_check;"
sudo sqlite3 /var/backups/library-security/restore-uji/simulasi.sqlite \
"SELECT 'anggota', COUNT(*) FROM anggota
UNION ALL SELECT 'buku', COUNT(*) FROM buku
UNION ALL SELECT 'peminjaman', COUNT(*) FROM peminjaman;"
```

**[AMBIL FOTO 3 SEKARANG — SETELAH RESTORE]** Hasil yang diharapkan `ok`, lalu `3`, `3`, `2`. Catat pula waktu mulai dan selesai restore serta durasinya. Ini membuktikan isi database simulasi kembali, bukan otomatis membuktikan login atau dekripsi aplikasi.

### 8.2 Foto tambahan backup, hash, cron dan uji aplikasi

1. **[AMBIL FOTO — DAFTAR BACKUP]** Di Ubuntu jalankan `whoami; date` lalu `sudo ls -lh /var/backups/library-security`; screenshot nama backup terenkripsi `.enc` dan checksum `.sha256`. Jangan salin `uji-database.sqlite` yang belum terenkripsi ke Windows.
2. **[AMBIL FOTO — VERIFIKASI CHECKSUM UBUNTU]** Screenshot hasil `sudo sha256sum -c /var/backups/library-security/backup-20261122-084125-719361.tar.gz.enc.sha256` yang menunjukkan `OK`; sesuaikan nama jika file berubah.
3. **[AMBIL FOTO — SALINAN DI WINDOWS]** Screenshot file terenkripsi di folder Windows dan hasil `Get-FileHash` yang cocok dengan `sudo sha256sum` file Ubuntu. Penyalinan yang sudah dilakukan bersifat manual; jangan menyebutnya otomatis.
4. **[AMBIL FOTO — SKRIP DAN CRON]** Setelah jadwal benar-benar dibuat dan minimal satu run terjadwal sukses, screenshot isi skrip yang telah diperiksa bebas rahasia, hasil `sudo crontab -l` bila menggunakan crontab root, dan bukti run/log yang disensor. **Cron belum terverifikasi**, jangan mengarang hasil.
5. **[AMBIL FOTO — UJI APLIKASI RESTORE]** Setelah menyiapkan instance terisolasi menggunakan database restore dan recovery key yang sesuai, screenshot hasil login, riwayat dan dekripsi telepon fiktif. Sensor password, key, isi `.env`, cookie dan token. Database yang lolos `integrity_check` saja belum membuktikan ketiga fungsi ini.

### 8.3 Urutan kerja tersisa dan keamanan backup

1. Konfirmasi lokasi penyimpanan di Windows/flash disk, aksesnya, jadwal/retensi dan cara menyimpan recovery key terpisah. Snapshot VM sendiri tidak cukup.
2. Skrip yang kamu buat memakai SQLite Online Backup API, mengenkripsi dengan OpenSSL dan menghasilkan hash. Pastikan backup juga memuat unggahan dan konfigurasi nonrahasia; simpan `.env`/key pemulihan secara aman terpisah dari arsip, bukan di Git. Jangan hapus backup lama sebelum salinan baru terverifikasi. `openssl enc` tidak mengautentikasi ciphertext; checksum perlu dilindungi terpisah.
3. Gunakan `sudo crontab -e` hanya setelah skrip dan hak akses dipahami; jangan menghapus entri lain. Pastikan cron aktif dan buktikan satu run terjadwal. Simpan `sudo crontab -l` dan log yang disensor. Pengiriman ke Windows perlu dibuktikan terpisah; SCP manual bukan transfer otomatis.
4. Uji restore dan aplikasi di instance terisolasi, bukan database aktif. Catat hasil tiga foto, durasi, cron, checksum, lokasi dan keterbatasan pada manifest. Jangan menandai SEC-07 selesai sebelum semua bukti yang diperlukan tersedia.

1. Konfirmasi lokasi penyimpanan di Windows/flash disk, aksesnya, jadwal/retensi dan cara menyerahkan recovery key terpisah. Snapshot VM sendiri tidak cukup. Catat jumlah data fiktif dan backup konfigurasi yang diperlukan tanpa merekam kuncinya.
2. Buat skrip backup dengan hak minimal. Gunakan `sqlite3 /var/www/library-security/database/database.sqlite ".backup '<file-sementara>'"` untuk snapshot SQLite konsisten walaupun WAL aktif. Jalankan `PRAGMA integrity_check` pada snapshot; hasil harus `ok`. Sertakan unggahan dari `storage/app/public` dan konfigurasi yang dibutuhkan dalam arsip, **jangan sertakan sesi**, lindungi direktori sementara dan bersihkan dengan aman setelah keberhasilan.
3. Enkripsi arsip menggunakan `openssl enc` dengan sandi yang disediakan lewat file berizin `600` di luar repo, tidak ditulis di command line/log. Buat checksum arsip terenkripsi `sha256sum`. Simpan kunci/sandi recovery **terpisah** dari arsip. Jangan menghapus backup lama sebelum salinan baru benar-benar terverifikasi. `openssl enc` tidak menyediakan autentikasi ciphertext; simpan checksum secara tepercaya/terpisah dan catat keterbatasannya.
4. Salin arsip terenkripsi ke lokasi berbeda dari VM menggunakan metode yang telah diuji, dan cocokkan checksum di kedua sisi. Script dan pengiriman belum tersedia di repo ini; siapkan dan uji sesuai akses penyimpananmu sebelum menjadwalkan. Jangan mengklaim off-VM otomatis bila salinan masih manual.
5. Gunakan `crontab -e` untuk menjadwalkan skrip yang **sudah terbukti**; catat `crontab -l` dan log tanpa rahasia. Pastikan service cron berjalan. Untuk membuktikan jadwal, boleh sementara memakai jadwal dekat, tunggu hasil nyata, lalu kembalikan ke harian dan verifikasi lagi. Pastikan akun cron dapat membaca `.env`/database/unggahan dan menulis tujuan; hak berlebihan harus dihindari.
6. Uji restore **di instance/database salinan terisolasi**, bukan database kerja: catat jumlah data sebelum, hapus sebagian data fiktif pada salinan tersebut, catat setelah dihapus, lalu restore dari arsip. Bandingkan jumlah/isi data sesudah restore, jalankan `PRAGMA integrity_check`, dan uji login, riwayat serta dekripsi nomor telepon fiktif dengan recovery key. Rekam tiga kondisi, waktu restore, dan checksum. Jangan memulihkan sesi aktif.

## 9. Pembuktian aplikasi dan demo rentan — TKT-22–26

Branch `vulnerable` sudah ada pada commit `da846cc` pada saat pemeriksaan, dengan launcher di branch tersebut. Ini **bukan** bukti demo sudah lulus. TKT-22–25 masih memerlukan pemeriksaan lingkungan dan bukti nyata.

1. Siapkan checkout terpisah pada mesin yang menjalankan browser demo; baca `scripts/serve-vulnerable.sh` atau `.bat` di checkout itu sebelum menjalankannya. Pastikan database `vulnerable.sqlite`, direktori sesi, nama cookie `mini_library_vuln_session`, serta profil browser terpisah. Pastikan konfigurasi `.env` tidak menunjuk database main: nilai environment bisa mengalahkan default kode.
2. Jalankan hanya lewat launcher loopback pada `127.0.0.1:8001`; periksa listener dengan `ss -ltnp` di Linux atau `netstat -ano` di Windows. Jangan jalankan di `0.0.0.0`, IP host-only, Nginx, atau port forwarding. Browser harus berada di mesin yang menjalankan server rentan. Jangan merge perubahan rentan ke main.
3. SQLi: dengan fixture fiktif yang setara, cari normal lalu masukkan `' OR 1=1 --`  pada kedua versi; simpan hasil rentan dan aman serta potongan query. Main harus memperlakukan input sebagai nilai, bukan memperluas hasil. Audit juga query lain, bukan pencarian saja.
4. XSS: kirim `<script>alert('XSS-LAB')</script>` sebagai ulasan pada dua lingkungan terpisah. Pembaca kedua pada versi rentan melihat alert lokal; pada main lihat teks biasa dan HTML escaped. Periksa escaping secara terpisah dari CSP, termasuk halaman admin. Tidak boleh callback eksternal atau data nyata.
5. IDOR: login user A, minta detail pinjaman milik user B. Versi rentan menunjukkan data, main harus 404; pemilik/admin yang berhak tetap dapat melihat. Rekam pasangan IDOR dan sesi secara terpisah. Uji cookie HTTPS, rotasi login dan penolakan sesi lama setelah logout tanpa merekam nilainya.
6. Jalankan suite pada lingkungan test terpisah: `php artisan test`. PHPUnit tidak menggantikan pengujian browser/VM; validasi CSRF 419 perlu enforcement nyata, dan konkurensi perlu dua proses yang benar-benar bersaing. Catat hasil aktual, termasuk kegagalan.

Untuk SEC-02, gunakan dua akun fiktif dengan password sama dan tunjukkan kedua hash berbeda dengan sebagian nilai disensor; cek login benar/salah tanpa menunjukkan password. Untuk SEC-03, perlihatkan envelope ciphertext di DB dan telepon fiktif pada profil yang berhak; jangan tampilkan key. SEC-01 ada di `docs/tabel-risiko.md`, tetap cocokkan dengan tugas dosen.

## 10. Pengumpulan dan pemeriksaan akhir — TKT-27–31

- [ ] Sepuluh syarat punya bukti nyata; syarat 6–10 mempunyai pasangan sebelum/sesudah, IDOR dan sesi dicatat terpisah.
- [ ] `docs/manifest-hasil-uji.md` memuat hasil, tanggal, branch/commit, path dan checksum aktual; status belum diuji tidak ditulis lulus.
- [ ] Laporan PDF A4 font 11–12, maksimal 6 halaman isi dan maksimal 4 halaman lampiran bukti; sertakan diagram arsitektur dengan IP/port aktual, risiko tersisa, refleksi serta deklarasi bantuan AI secara jujur.
- [ ] Video maksimal lima menit, suara jelas, identitas dan `whoami; date`, aplikasi HTTPS, bukti SEC-02–07, perbandingan SEC-08–10, risiko tersisa. Sensor rahasia.
- [ ] Paket kode memuat main dan vulnerable terpisah, script backup, konfigurasi server relevan dan README. Jangan sertakan `.env`, key, password, sesi, database aktif, backup asli atau cache konfigurasi berisi rahasia. Periksa riwayat Git dan ZIP.
- [ ] Uji instalasi dari salinan bersih; catat minimal 10 commit bermakna jika memakai Git. Tidak ada upload/pengumpulan yang dianggap terjadi sebelum benar-benar dilakukan.
- [ ] Siapkan tanya jawab tentang hash vs enkripsi, salt, nonce, AES-GCM, HTTPS self-signed, default deny, restore, SQL binding, escaping, policy dan sesi.

## 11. Bila ada masalah


| Masalah                          | Pemeriksaan awal                                                                                                                         |
| -------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| Host tidak bisa ping VM          | Pastikan VM aktif, adapter VMware VMnet1, `ip -br addr`, dan alamat yang dipakai; ICMP bisa diblokir firewall.                           |
| `apt` tidak bisa mengunduh       | Periksa NAT sementara, `ip route` dan `getent hosts archive.ubuntu.com`; jangan ubah host-only sembarangan.                              |
| `Permission denied` untuk `.env` | Periksa `ls -l`, pemilik dan akses `sudo -u www-data test -r ...`; jangan ubah ke `777`.                                                 |
| Seeder menolak production        | Ini sengaja; gunakan prosedur data fiktif awal di bagian 5.2. Jangan mengubah environment deployment yang sudah berdata sembarangan.     |
| HTTP 502                         | Periksa `systemctl status php8.3-fpm`, socket `/run/php/`, `nginx -t` dan log yang sudah disensor.                                       |
| HTTPS gagal dipercaya            | Pastikan domain/IP cocok SAN, file publik benar, dan `curl --cacert`; `-k` melewati validasi, bukan pembuktian trust.                    |
| SSH gagal setelah perubahan      | Gunakan console VMware, cek `sudo sshd -t` dan `sudo sshd -T`; pulihkan konfigurasi yang diketahui baik, lalu uji lagi.                  |
| UFW memutus SSH                  | Pakai console VMware; lihat `sudo ufw status numbered` dan aturan sumber/tujuan. Jangan menghapus semua aturan tanpa memahami dampaknya. |


## Sumber

- Dokumen dosen: `Project_Pengganti_UTS_Keamanan_Sistem_Komputer.md`, terutama bagian 2 dan 3.
- Indeks tiket Obsidian: `D:\Obsidian\Library\PRD-Perpustakaan\Tickets\00 - Indeks Tickets.md`.
- Manifest proyek: `docs/manifest-hasil-uji.md`; risiko: `docs/tabel-risiko.md`.
- [Laravel 12: deployment dan Nginx](https://laravel.com/docs/12.x/deployment).
