# Dokumentasi Syarat Keamanan Website Perpustakaan Mini

**Nama:** Muhammad Ridha  
**NIM:** 230170010  
**Tema:** Perpustakaan (digit terakhir NIM: 0)  
**Teknologi:** Laravel 12, PHP, Blade, SQLite  
**Tanggal pemeriksaan:** 16 November 2026

## 1. Acuan dan batas dokumentasi

Dokumentasi ini mengacu pada Bagian 2 dokumen tugas **Project_Pengganti_UTS_Keamanan_Sistem_Komputer.docx**. Isinya khusus kontrol keamanan yang diterapkan pada **kode website**, yaitu syarat **2, 3, 8, 9, dan 10**. Nomor syarat mengikuti dokumen dosen, bukan urutan bab dalam berkas ini.

Syarat 1 (tabel risiko), 4 (HTTPS), 5 (hardening Linux), 6 (firewall), dan 7 (backup/restore) tidak dibahas sebagai implementasi di sini. Syarat tersebut tetap wajib untuk keseluruhan tugas. HTTPS tetap menjadi prasyarat pembuktian cookie `Secure` pada syarat 10.

Potongan kode di bawah diambil dari implementasi aman yang diperiksa. Sebagian merupakan kutipan bagian fungsi, bukan berkas lengkap yang dapat langsung dijalankan. Lokasi dan nomor baris mengacu pada kondisi kode saat pemeriksaan; nomor baris dapat bergeser setelah perubahan berikutnya.

### Peta syarat ke implementasi

| Syarat dosen | ID proyek | Implementasi website | Lokasi utama |
|---|---|---|---|
| 2 — Password Disimpan dengan Hash + Salt | SEC-02 | Hash saat registrasi, verifikasi login, salt otomatis | [RegisterController.php](app/Http/Controllers/Auth/RegisterController.php), [LoginController.php](app/Http/Controllers/Auth/LoginController.php) |
| 3 — Satu Data Sensitif Dienkripsi (AES-GCM) | SEC-03 | Enkripsi/dekripsi nomor telepon anggota | [AesGcm.php](app/Services/AesGcm.php), [ProfileController.php](app/Http/Controllers/ProfileController.php) |
| 8 — Aman dari SQL Injection | SEC-08 | Pencarian dengan parameter binding | [BookController.php](app/Http/Controllers/BookController.php) |
| 9 — Aman dari XSS | SEC-09 | Escaping ulasan di Blade dan header CSP | [buku/show.blade.php](resources/views/buku/show.blade.php), [ContentSecurityPolicy.php](app/Http/Middleware/ContentSecurityPolicy.php) |
| 10 — Aman dari IDOR dan Sesi Lemah | SEC-10 | Policy pemilik, pembatasan peran, rotasi/invalidation sesi, atribut cookie | [PeminjamanPolicy.php](app/Policies/PeminjamanPolicy.php), [PeminjamanController.php](app/Http/Controllers/PeminjamanController.php), [session.php](config/session.php) |

## 2. Syarat 2 — Password Disimpan dengan Hash + Salt

### 2.1 Ketentuan tugas dan hasil implementasi

Dosen meminta hash lambat dari pustaka, salt unik, verifikasi password saat login, panjang minimal 8 karakter saat registrasi, serta dua akun dengan password sama tetapi hash berbeda. Website menggunakan `Hash::make()` dengan Bcrypt dan menetapkan minimal **12 karakter**, lebih ketat daripada minimum tugas. Batas maksimal **72 byte** diterapkan agar password multibyte tidak terpotong pada batas Bcrypt.

Password di-hash, bukan dienkripsi untuk dikembalikan ke bentuk aslinya. Salt acak dikelola oleh pustaka dan tersimpan sebagai bagian format hash, sehingga tidak diperlukan kolom salt terpisah. `.env.example` menetapkan `BCRYPT_ROUNDS=12`; lingkungan PHPUnit menggunakan cost lebih rendah untuk mempercepat pengujian, bukan sebagai konfigurasi deployment.

### 2.2 Kode registrasi: simpan hash, bukan password asli

**Lokasi:** [app/Http/Controllers/Auth/RegisterController.php](app/Http/Controllers/Auth/RegisterController.php), metode `store()`, baris 25–35.

```php
Anggota::create([
    'nama' => $request->validated('nama'),
    'email' => strtolower(trim($request->validated('email'))),
    'password' => Hash::make($request->validated('password')),
    'role' => 'user',
    'is_active' => true,
]);
```

**Hubungan dengan syarat 2:** nilai yang masuk ke kolom `anggota.password` adalah hasil hash. Penetapan `role => 'user'` juga mencegah pendaftar menentukan dirinya sendiri sebagai admin.

**Lokasi validasi:** [app/Http/Requests/RegisterRequest.php](app/Http/Requests/RegisterRequest.php), metode `rules()`, baris 41–52.

```php
'password' => [
    'required',
    'string',
    'min:12',
    'confirmed',
    function (string $attribute, mixed $value, Closure $fail): void {
        // SEC-02: Bcrypt truncates silently at 72 bytes. Enforce strict UTF-8 byte limit.
        if (is_string($value) && strlen($value) > 72) {
            $fail('Password tidak boleh melebihi 72 byte.');
        }
    },
],
```

`min:12` memeriksa panjang karakter, sedangkan `strlen()` menghitung byte. Keduanya berbeda untuk karakter multibyte. Jalur pembuatan anggota oleh admin juga menerapkan minimal 12 karakter dan maksimal 72 byte di [StoreMemberRequest.php](app/Http/Requests/Admin/StoreMemberRequest.php), baris 28–38, serta `Hash::make()` di [Admin/MemberController.php](app/Http/Controllers/Admin/MemberController.php), baris 77–84.

### 2.3 Kode login: verifikasi hash melalui framework

**Lokasi:** [app/Http/Controllers/Auth/LoginController.php](app/Http/Controllers/Auth/LoginController.php), metode `store()`, baris 41–47.

```php
if (! Auth::attempt(['email' => $email, 'password' => $request->input('password'), 'is_active' => true])) {
    RateLimiter::hit($throttleKey, 60);

    throw ValidationException::withMessages([
        'email' => 'Email atau password salah.',
    ]);
}
```

**Hubungan dengan syarat 2:** `Auth::attempt()` menggunakan provider autentikasi untuk memverifikasi password masukan terhadap hash tersimpan. Aplikasi tidak membandingkan password plaintext dengan isi kolom secara langsung dan tidak perlu memanggil `Hash::check()` sendiri di controller. Provider diarahkan ke model `Anggota` melalui [config/auth.php](config/auth.php), baris 62–68.

### 2.4 Bukti salt unik untuk dua akun

**Lokasi:** [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php), baris 21–22 dan 39–54.

```php
$userPassword = config('security.seed_user_password');
$adminPassword = config('security.seed_admin_password');
```

```php
$userA = Anggota::firstOrCreate(['email' => 'user_a@perpustakaan.test'], [
    'nama' => 'User A',
    'password' => Hash::make($userPassword),
    'role' => 'user',
    'is_active' => true,
    'telepon_enc' => null,
]);

$userB = Anggota::firstOrCreate(['email' => 'user_b@perpustakaan.test'], [
    'nama' => 'User B',
    'password' => Hash::make($userPassword),
    'role' => 'user',
    'is_active' => true,
    'telepon_enc' => null,
]);
```

Dua pemanggilan `Hash::make()` menerima input yang sama, tetapi menghasilkan salt dan hash berbeda ketika akun baru dibuat. `firstOrCreate()` tidak mengganti password akun yang sudah ada; demonstrasi ini sebaiknya menggunakan fixture baru pada database uji terpisah, bukan menghapus database yang berisi data penting.

**Bukti otomatis:** [tests/Feature/PasswordHashingTest.php](tests/Feature/PasswordHashingTest.php), baris 55–80, membandingkan kedua hash dan menjalankan:

```php
$this->assertNotSame($userA->password, $userB->password);
```

```php
$this->assertTrue(Hash::check(config('security.seed_user_password'), $userA->password));
$this->assertTrue(Hash::check(config('security.seed_user_password'), $userB->password));
```

**Mengapa aman:** hash lambat menambah biaya penebakan password, sedangkan salt membuat password identik tidak memiliki hash identik. Hash tetap perlu dilindungi karena kebocorannya masih memungkinkan serangan tebak password secara offline.

**Bukti pengumpulan yang perlu disiapkan:** tangkapan layar kolom hash kedua akun fiktif dan hasil login benar/salah. Jangan menampilkan password asli atau nilai konfigurasi rahasia.

## 3. Syarat 3 — Satu Data Sensitif Dienkripsi (AES-GCM)

### 3.1 Ketentuan tugas dan hasil implementasi

Untuk tema perpustakaan, data sensitif yang wajib dienkripsi adalah **nomor telepon anggota**. Website menyimpan hasil enkripsi pada `anggota.telepon_enc` menggunakan AES-256-GCM, kunci 32 byte, nonce acak baru 12 byte, dan tag autentikasi 16 byte. AAD mengikat ciphertext ke identitas anggota.

### 3.2 Konfigurasi kunci di luar kode

**Lokasi:** [config/security.php](config/security.php), baris 13.

```php
'telepon_key' => env('TELEPON_KEY_V1'),
```

**Lokasi pembacaan dan validasi:** [app/Services/AesGcm.php](app/Services/AesGcm.php), baris 9–12, 18, dan 27–38. Berikut kutipan terpisah dari bagian tersebut:

```php
private const CIPHER = 'aes-256-gcm';
private const NONCE_LENGTH = 12; // 96-bit recommended for GCM
private const TAG_LENGTH = 16;   // 128-bit authentication tag
private const KEY_LENGTH = 32;   // 256-bit key
```

```php
$rawKey = $keyBase64 ?? config('security.telepon_key');
```

```php
$decoded = base64_decode($keyString, true);
if ($decoded === false || strlen($decoded) !== self::KEY_LENGTH) {
    return null;
}
```

Service mengambil kunci melalui `config()`, bukan membaca `env()` langsung. Fungsi `parseKey()` terlebih dahulu menghapus awalan `base64:` bila ada, kemudian memeriksa hasil decode tepat 32 byte. Nilai kunci tidak dicantumkan dalam dokumentasi ini.

**Lokasi pengecualian Git:** [.gitignore](.gitignore), baris 9–11.

```gitignore
.env
.env.*
!.env.example
```

`.env.example` saat diperiksa hanya memuat `TELEPON_KEY_V1=` tanpa nilai. Namun, `.gitignore` tidak menghapus rahasia dari riwayat commit. Kunci yang pernah tercatat harus dianggap bocor dan diganti; rotasi kunci serta migrasi ciphertext deployment belum dibuktikan oleh pemeriksaan ini.

### 3.3 Kode enkripsi dan format penyimpanan

**Lokasi:** [app/Services/AesGcm.php](app/Services/AesGcm.php), metode `encrypt()`, baris 55–78.

```php
$nonce = random_bytes(self::NONCE_LENGTH);
$tag = '';

$ciphertext = openssl_encrypt(
    $plaintext,
    self::CIPHER,
    $this->keyBytes,
    OPENSSL_RAW_DATA,
    $nonce,
    $tag,
    $aad,
    self::TAG_LENGTH
);

if ($ciphertext === false || strlen($tag) !== self::TAG_LENGTH) {
    throw new RuntimeException('Gagal mengenkripsi data kontak.');
}

return [
    'v' => 1,
    'nonce' => base64_encode($nonce),
    'ct' => base64_encode($ciphertext),
    'tag' => base64_encode($tag),
];
```

**Hubungan dengan syarat 3:** nonce dibuat baru pada setiap pemanggilan enkripsi. Ciphertext, nonce, tag, dan versi disimpan bersama sebagai envelope; kunci tidak disimpan di kolom tersebut. Base64 hanya encoding untuk penyimpanan, bukan algoritma enkripsinya.

**Lokasi pemakaian pada profil:** [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php), metode `updateTelepon()`, baris 34–35 dan 45–51. Kutipan bagian pengambilan pemilik/input dan penyimpanan:

```php
$user = auth()->user();
$inputTelepon = $request->validated('telepon');
```

```php
$aad = "anggota:{$user->id}:telepon:v1";
$envelope = $aes->encrypt($inputTelepon, $aad);

$user->update([
    'telepon_enc' => $envelope,
]);
```

Model [app/Models/Anggota.php](app/Models/Anggota.php), baris 49, menetapkan `'telepon_enc' => 'array'` untuk konversi envelope ke/dari JSON. Input telepon kosong ditangani terpisah dengan menyimpan `null`.

### 3.4 Kode dekripsi dan pembatasan pembaca

**Lokasi:** [app/Services/AesGcm.php](app/Services/AesGcm.php), metode `decrypt()`, baris 128–142.

```php
$plaintext = openssl_decrypt(
    $ciphertext,
    self::CIPHER,
    $this->keyBytes,
    OPENSSL_RAW_DATA,
    $nonce,
    $tag,
    $aad
);

if ($plaintext === false) {
    return null;
}

return $plaintext;
```

Sebelum kutipan di atas, fungsi memeriksa kunci, versi envelope, hasil decode, panjang nonce, dan panjang tag. Kegagalan verifikasi GCM menghasilkan `null`, bukan plaintext atau pesan internal OpenSSL.

**Lokasi pemanggilan:** [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php), metode `show()`, baris 17–25.

```php
$user = auth()->user();
$teleponPlaintext = null;

if ($user->telepon_enc !== null) {
    $aad = "anggota:{$user->id}:telepon:v1";
    $teleponPlaintext = $aes->decrypt($user->telepon_enc, $aad);
}

return view('profil.show', compact('user', 'teleponPlaintext'));
```

Rute `/profil` dilindungi `auth` dan `active` di [routes/web.php](routes/web.php), baris 70–77. Pemilik diperoleh dari sesi, bukan ID kiriman pengguna. Hasil dekripsi ditampilkan pada input profil melalui `value="{{ old('telepon', $teleponPlaintext) }}"` di [resources/views/profil/show.blade.php](resources/views/profil/show.blade.php), baris 51.

Admin yang berhak juga dapat mendekripsi telepon melalui metode `edit()` di [app/Http/Controllers/Admin/MemberController.php](app/Http/Controllers/Admin/MemberController.php), baris 111–123, setelah `Gate::authorize('update', $anggota)`.

**Mengapa aman:** AES-GCM melindungi kerahasiaan sekaligus mendeteksi perubahan ciphertext melalui tag autentikasi. AAD membuat ciphertext telepon anggota A tidak dapat digunakan begitu saja sebagai telepon anggota B; AAD bukan pengganti pemeriksaan hak akses.

**Risiko kunci:** jika kunci hilang tanpa salinan pemulihan, telepon terenkripsi tidak dapat dipulihkan melalui dekripsi. Jika kunci bocor bersama ciphertext, kerahasiaan data terancam; mengganti kunci harus disertai migrasi data lama, bukan sekadar menimpa konfigurasi.

**Bukti otomatis:** [tests/Unit/AesGcmTest.php](tests/Unit/AesGcmTest.php) menguji round-trip, nonce berbeda, tamper, AAD salah, dan kunci salah. [tests/Feature/MemberPhoneEncryptionTest.php](tests/Feature/MemberPhoneEncryptionTest.php) menguji penyimpanan terenkripsi dan akses profil.

**Bukti pengumpulan yang perlu disiapkan:** tangkapan layar `telepon_enc` yang tidak memuat telepon plaintext, telepon fiktif yang terbaca di profil pemilik, serta aturan `.gitignore`. Jangan menampilkan isi kunci.

## 4. Syarat 8 — Aman dari SQL Injection

### 4.1 Ketentuan tugas dan hasil implementasi

Dosen meminta seluruh query yang memakai input pengguna menggunakan parameter binding, serta perbandingan serangan pada versi rentan dan aman. Target demonstrasi website ini adalah pencarian katalog di **`GET /buku/cari?q=`**.

### 4.2 Kode pencarian aman

**Lokasi:** [app/Http/Controllers/BookController.php](app/Http/Controllers/BookController.php), metode `search()`, baris 30–47. Berikut bagian validasi dan query:

```php
$request->validate([
    'q' => ['nullable', 'string', 'max:100'],
]);

$query = trim($request->input('q', ''));
```

```php
$buku = Buku::query()
    ->whereNull('archived_at')
    ->where(function ($qb) use ($query) {
        $qb->where('judul', 'like', "%{$query}%")
           ->orWhere('penulis', 'like', "%{$query}%");
    })
    ->orderBy('judul')
    ->get();
```

**Hubungan dengan syarat 8:** Query Builder mengikat nilai pencarian sebagai parameter, bukan menyisipkannya ke struktur SQL. Interpolasi `"%{$query}%"` di sini menyusun **nilai parameter LIKE**, bukan membentuk string perintah SQL mentah. Kolom pencarian dan pengurutan ditentukan oleh kode, bukan input bebas pengguna.

Validasi maksimal 100 karakter membatasi ukuran input, tetapi pertahanan SQLi utamanya adalah parameter binding. Karakter `%` dan `_` masih mempunyai makna wildcard pada operasi LIKE; itu berbeda dari kemampuan menyisipkan perintah SQL.

### 4.3 Kode pengujian dan perbandingan

**Lokasi:** [tests/Feature/BookSearchSecurityTest.php](tests/Feature/BookSearchSecurityTest.php), baris 61–67.

```php
$payload = "' OR 1=1 -- ";
$response = $this->get('/buku/cari?q=' . urlencode($payload));

$response->assertStatus(200);
$response->assertDontSee('Buku Jaringan Komputer');
$response->assertDontSee('Buku Basis Data');
$response->assertSee('Tidak ada buku yang ditemukan');
```

| Kondisi | Perilaku yang harus dibuktikan |
|---|---|
| Sebelum — versi rentan, hanya localhost | Payload mengubah logika query konkatenasi sehingga hasil meluas. Demo ini belum dieksekusi dalam pekerjaan dokumentasi ini. |
| Sesudah — implementasi aman | Payload menjadi nilai pencarian, bukan sintaks SQL. Pada data uji yang tidak memuat teks tersebut, tidak ada buku ditemukan; tes otomatis lulus. |

**Mengapa aman:** struktur query dipisahkan dari data pengguna. Tanda kutip atau komentar SQL dalam input tidak mendapat kewenangan mengubah struktur query.

**Batas pembuktian:** tes di atas membuktikan skenario pencarian, bukan audit menyeluruh semua query aplikasi. Pemeriksaan pola raw query pada direktori `app/` tidak menemukan `whereRaw`, `orWhereRaw`, `orderByRaw`, `selectRaw`, atau `DB::select/statement/unprepared`; pemeriksaan pola tersebut bukan bukti formal bahwa seluruh kemungkinan SQLi telah tercakup.

**Bukti pengumpulan yang perlu disiapkan:** kode dan tangkapan layar pencarian dengan payload identik pada versi rentan dan aman, hanya di lingkungan lab milik sendiri.

## 5. Syarat 9 — Aman dari XSS

### 5.1 Ketentuan tugas dan hasil implementasi

Dosen meminta kolom teks bebas yang ditampilkan kembali, demonstrasi skrip pada versi rentan, dan output escaping pada versi aman. Target website ini adalah **isi ulasan buku**. Pertahanan utama adalah escaping Blade; CSP menjadi lapisan tambahan.

### 5.2 Kode template aman pada halaman pengguna dan admin

**Lokasi publik:** [resources/views/buku/show.blade.php](resources/views/buku/show.blade.php), baris 197–199.

```blade
<div class="text-sm text-slate-700 leading-relaxed break-words">
    {{ $ulasan->isi }}
</div>
```

**Lokasi admin:** [resources/views/admin/ulasan/index.blade.php](resources/views/admin/ulasan/index.blade.php), baris 95.

```blade
{{ $item->isi }}
```

**Hubungan dengan syarat 9:** `{{ }}` melakukan escaping untuk konteks HTML sehingga tag dari teks ulasan tidak diperlakukan sebagai elemen skrip. Halaman moderasi admin juga memakai escaping. Pemeriksaan `resources/views/` saat dokumentasi dibuat tidak menemukan ekspresi output mentah `{!! ... !!}`.

Escaping bukan berarti menghapus isi ulasan dari database. Teks disimpan sebagai data, kemudian di-escape ketika dikeluarkan ke halaman. Untuk konteks lain seperti JavaScript atau URL, tetap diperlukan perlakuan yang sesuai; keamanan satu konteks tidak otomatis berlaku untuk semua konteks.

### 5.3 Kode Content Security Policy

**Lokasi:** [app/Http/Middleware/ContentSecurityPolicy.php](app/Http/Middleware/ContentSecurityPolicy.php), baris 18–20.

```php
$csp = "default-src 'self'; script-src 'self'; object-src 'none'; style-src 'self' 'unsafe-inline' https://fonts.bunny.net; font-src 'self' https://fonts.bunny.net;";

$response->headers->set('Content-Security-Policy', $csp);
```

**Lokasi pemasangan:** [bootstrap/app.php](bootstrap/app.php), baris 14–16.

```php
$middleware->web(append: [
    \App\Http\Middleware\ContentSecurityPolicy::class,
]);
```

`script-src 'self'` membatasi sumber skrip dan memblokir inline script tanpa izin tambahan; `object-src 'none'` melarang konten objek. Kebijakan yang ada masih mengizinkan inline style dan font dari Bunny Fonts, sehingga tidak tepat disebut melarang seluruh resource eksternal. CSP tidak menggantikan escaping.

### 5.4 Kode pengujian dan perbandingan

**Lokasi:** [tests/Feature/ReviewSecurityTest.php](tests/Feature/ReviewSecurityTest.php), baris 133–144.

```php
$xssPayload = "<script>alert('XSS-LAB')</script>";

$this->actingAs($user)->post(route('buku.ulasan.store', $book), [
    'isi' => $xssPayload,
]);

$response = $this->get(route('buku.show', $book));
$response->assertOk();

// Verifikasi output escaping: karakter tag harus ter-escape di response HTML
$response->assertDontSee($xssPayload, false);
$response->assertSee('&lt;script&gt;alert(&#039;XSS-LAB&#039;)&lt;/script&gt;', false);
```

| Kondisi | Perilaku yang harus dibuktikan |
|---|---|
| Sebelum — versi rentan, hanya localhost | Render mentah ulasan membuat skrip berjalan dan alert muncul. Demo ini belum dieksekusi dalam pekerjaan dokumentasi ini. |
| Sesudah — implementasi aman | HTML respons memuat entitas hasil escaping; browser diharapkan menampilkan teks skrip tanpa menjalankannya. Tes HTML dan header CSP lulus; tangkapan layar browser tetap perlu diambil. |

**Bukti otomatis tambahan:** [tests/Feature/BookDetailTest.php](tests/Feature/BookDetailTest.php) memeriksa escaping ulasan pada detail buku. Tes respons HTML tidak menjalankan mesin JavaScript browser.

**Bukti pengumpulan yang perlu disiapkan:** alert versi rentan, teks ulasan versi aman, potongan template kedua versi, dan header CSP. Semua payload hanya digunakan pada akun uji di lab sendiri.

## 6. Syarat 10 — Aman dari IDOR dan Sesi Lemah

### 6.1 Ketentuan tugas dan hasil implementasi

Data yang wajib dilindungi untuk tema perpustakaan adalah **riwayat dan detail peminjaman anggota lain**. Pemeriksaan dilakukan di server: pemilik atau admin boleh melihat detail, sedangkan pengguna lain menerima 404. Perlindungan sesi meliputi rotasi ID saat login, invalidation saat logout, dan konfigurasi cookie.

### 6.2 Kode policy kepemilikan

**Lokasi:** [app/Policies/PeminjamanPolicy.php](app/Policies/PeminjamanPolicy.php), metode `view()`, baris 31–38.

```php
public function view(Anggota $user, Peminjaman $peminjaman): Response
{
    if ($user->role === 'admin' || $user->id === $peminjaman->anggota_id) {
        return Response::allow();
    }

    return Response::denyAsNotFound('Data peminjaman tidak ditemukan.');
}
```

**Lokasi pemanggilan setiap permintaan detail:** [app/Http/Controllers/PeminjamanController.php](app/Http/Controllers/PeminjamanController.php), metode `show()`, baris 67–75.

```php
public function show(Peminjaman $peminjaman): View
{
    // SEC-10: Memeriksa policy pemilik. Tolak dengan 404 jika bukan milik user
    Gate::authorize('view', $peminjaman);

    $peminjaman->load(['buku', 'anggota']);

    return view('peminjaman.show', compact('peminjaman'));
}
```

**Hubungan dengan syarat 10:** mengubah ID pada URL tidak melewati pemeriksaan pemilik. Route model binding hanya mengambil objek; keputusan boleh/tidaknya membaca objek dilakukan oleh policy. Respons 404 menghindari konfirmasi eksplisit bahwa transaksi pengguna lain tersedia.

### 6.3 Kode pembatasan riwayat dan identitas peminjam

**Lokasi:** [app/Http/Controllers/PeminjamanController.php](app/Http/Controllers/PeminjamanController.php), metode `index()`, baris 53–59.

```php
Gate::authorize('viewAny', Peminjaman::class);

// Hanya tampilkan riwayat milik anggota login
$peminjaman = Peminjaman::with('buku')
    ->where('anggota_id', auth()->id())
    ->orderByDesc('dipinjam_pada')
    ->get();
```

Saat membuat peminjaman, metode `store()` di berkas yang sama, baris 41, memakai:

```php
$peminjaman = $service->pinjam(auth()->user(), (int) $validated['buku_id']);
```

Pemilik diambil dari sesi terautentikasi, bukan `anggota_id` kiriman klien. Pola yang sama dipakai untuk pemilik ulasan di [app/Http/Controllers/UlasanController.php](app/Http/Controllers/UlasanController.php), baris 26–30.

### 6.4 Middleware login, akun aktif, dan peran

**Lokasi rute detail:** [routes/web.php](routes/web.php), baris 93–96.

```php
Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show'])
    ->whereNumber('peminjaman')
    ->middleware(['auth', 'active'])
    ->name('peminjaman.show');
```

Panel admin dibungkus `Route::prefix('admin')->middleware(['auth', 'active', 'role:admin'])` pada baris 19–21. Pembatasan ini dilakukan di server, bukan hanya menyembunyikan tombol admin.

**Lokasi pemeriksaan peran:** [app/Http/Middleware/EnsureRole.php](app/Http/Middleware/EnsureRole.php), baris 19–21.

```php
if (! auth()->check() || ! in_array(auth()->user()->role, $roles, true)) {
    abort(403, 'Akses tidak diizinkan.');
}
```

[app/Http/Middleware/EnsureActiveAccount.php](app/Http/Middleware/EnsureActiveAccount.php), baris 18–24, juga melakukan logout, invalidation sesi, dan regenerasi token ketika akun yang sedang login sudah dinonaktifkan.

### 6.5 Kode pengamanan sesi saat login dan logout

**Lokasi login:** [app/Http/Controllers/Auth/LoginController.php](app/Http/Controllers/Auth/LoginController.php), baris 51, setelah autentikasi berhasil.

```php
$request->session()->regenerate();
```

Regenerasi mengganti ID sesi untuk mengurangi risiko session fixation: ID yang diketahui sebelum login tidak dipertahankan sebagai ID sesi login.

**Lokasi logout:** berkas yang sama, metode `destroy()`, baris 63–70.

```php
Auth::logout();

$request->session()->invalidate();
$request->session()->regenerateToken();

return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
```

Logout menghapus status autentikasi, menginvalidasi sesi, dan mengganti token CSRF; bukan sekadar memindahkan halaman ke formulir login.

### 6.6 Konfigurasi cookie sesi

**Lokasi:** [config/session.php](config/session.php), baris 21, 172, 185, dan 202. Kutipan entri yang tidak bersebelahan:

```php
'driver' => env('SESSION_DRIVER', 'file'),
'secure' => env('SESSION_SECURE_COOKIE'),
'http_only' => (bool) env('SESSION_COOKIE_HTTPONLY', env('SESSION_HTTP_ONLY', true)),
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```

**Hubungan dengan syarat 10:**

| Atribut | Fungsi |
|---|---|
| `HttpOnly` | Mencegah JavaScript membaca cookie sesi melalui API seperti `document.cookie`; tidak menghilangkan seluruh dampak XSS. |
| `Secure` | Membatasi pengiriman cookie sesi melalui HTTPS. |
| `SameSite=Lax` | Membatasi pengiriman cookie pada sebagian permintaan lintas situs; bukan pengganti token CSRF. |
| Driver `file` | Menyimpan data sesi di sisi server pada `storage/framework/sessions`. |

**Penting:** `Secure` pada kode ini bergantung pada environment, bukan bernilai `true` secara tetap. `.env.example` yang diperiksa belum mencantumkan `SESSION_SECURE_COOKIE`. Pada deployment HTTPS, konfigurasi yang diperlukan adalah sebagai berikut (contoh konfigurasi, bukan kutipan `.env` aktual):

```dotenv
SESSION_DRIVER=file
SESSION_COOKIE=mini_library_session
SESSION_SECURE_COOKIE=true
SESSION_COOKIE_HTTPONLY=true
SESSION_SAME_SITE=lax
```

Pengujian lokal HTTP tidak cukup untuk menyatakan cookie HTTPS telah terbukti aman. Periksa cookie sesi yang benar pada Developer Tools melalui HTTPS; jangan hanya melihat cookie `XSRF-TOKEN`.

### 6.7 Kontrol tambahan: CSRF dan pembatasan percobaan login

Rute menggunakan grup middleware `web` Laravel. Form login di [resources/views/auth/login.blade.php](resources/views/auth/login.blade.php), baris 24, menyertakan:

```blade
@csrf
```

Token membantu mencegah permintaan perubahan data yang dipalsukan dari situs lain. [LoginController.php](app/Http/Controllers/Auth/LoginController.php), baris 28–33, membatasi percobaan berdasarkan email dan IP; kegagalan dicatat melalui `RateLimiter::hit()`. Tes memverifikasi HTTP 429 pada percobaan keenam setelah lima kegagalan.

### 6.8 Bukti otomatis dan perbandingan

**Lokasi:** [tests/Feature/BorrowingAuthorizationTest.php](tests/Feature/BorrowingAuthorizationTest.php), baris 85–90.

```php
// User A mencoba membaca data transaksi peminjaman milik User B
$response = $this->actingAs($userA)->get(route('peminjaman.show', $loanB));

// Wajib 404, BUKAN 403 atau 200, agar tidak membocorkan eksistensi data transaksi
$response->assertStatus(404);
$response->assertDontSee($userB->nama);
```

| Kondisi | Perilaku yang harus dibuktikan |
|---|---|
| Sebelum — versi rentan, hanya localhost | User A dapat membuka ID peminjaman user B tanpa policy. Demo ini belum dieksekusi dalam pekerjaan dokumentasi ini. |
| Sesudah — implementasi aman | Akses silang ditolak 404; pemilik dan admin tetap dapat melihat detail. Tes otomatis lulus. |
| Sesi melalui HTTPS | Cookie sesi harus memuat `Secure`, `HttpOnly`, dan `SameSite=Lax`; flag browser aktual masih perlu direkam. |

**Mengapa aman:** autentikasi membuktikan siapa penggunanya, sedangkan policy memutuskan data mana yang boleh diakses. Pembatasan pemilik di server tetap berlaku walaupun pengguna mengubah URL atau mengirim permintaan tanpa memakai tombol antarmuka.

**Bukti pengumpulan yang perlu disiapkan:** perbandingan akses A ke data B sebelum/sesudah, akses pemilik yang berhasil, flag cookie di Developer Tools, dan penolakan akses halaman privat setelah logout. Setelah logout, lakukan permintaan ulang; jangan hanya mengandalkan halaman yang masih terlihat di cache browser.

## 7. Hasil pengujian dan lokasi bukti kode

Perintah verifikasi pada 16 November 2026:

```bash
php artisan test
```

Hasil aktual: **142 tests passed, 543 assertions**. Ini hasil seluruh suite lokal, bukan 142 tes khusus keamanan, dan bukan bukti bahwa semua persyaratan tugas sudah lengkap secara operasional.

| Syarat | Berkas pengujian terkait | Perilaku yang diperiksa |
|---|---|---|
| 2 | [PasswordHashingTest.php](tests/Feature/PasswordHashingTest.php), [RegistrationTest.php](tests/Feature/RegistrationTest.php), [MemberLifecycleTest.php](tests/Feature/MemberLifecycleTest.php) | Hash Bcrypt, salt berbeda, verifikasi password, batas panjang registrasi/admin |
| 3 | [AesGcmTest.php](tests/Unit/AesGcmTest.php), [MemberPhoneEncryptionTest.php](tests/Feature/MemberPhoneEncryptionTest.php) | Round-trip, nonce berbeda, tamper, AAD/kunci salah, enkripsi pada profil |
| 8 | [BookSearchSecurityTest.php](tests/Feature/BookSearchSecurityTest.php) | Pencarian normal dan payload SQLi tanpa perluasan hasil |
| 9 | [ReviewSecurityTest.php](tests/Feature/ReviewSecurityTest.php), [BookDetailTest.php](tests/Feature/BookDetailTest.php) | Escaping ulasan dan keberadaan header CSP |
| 10 | [BorrowingAuthorizationTest.php](tests/Feature/BorrowingAuthorizationTest.php), [SessionSecurityTest.php](tests/Feature/SessionSecurityTest.php), [AuthenticationTest.php](tests/Feature/AuthenticationTest.php) | Kepemilikan, peran, akun aktif, konfigurasi HttpOnly/SameSite, login/logout, CSRF, rate limiting |

Pengujian tersebut menggunakan lingkungan testing terisolasi. Jangan mengganti database pengujian dengan database deployment atau menjalankan migrasi destruktif hanya untuk mengambil bukti.

### Yang belum dibuktikan oleh dokumentasi ini

- Demo browser versi rentan dan perbandingan sebelum/sesudah untuk syarat 8, 9, dan 10.
- Flag cookie sesi aktual melalui HTTPS di VM, termasuk `Secure=true`.
- Kelengkapan tangkapan layar database dan browser sesuai aturan bukti dosen.
- Rotasi kunci yang pernah terekspos dan migrasi ciphertext pada deployment bila kunci tersebut pernah dipakai.

Untuk demo versi rentan, gunakan hanya **`127.0.0.1:8001`**, database dan cookie terpisah; jangan bind `0.0.0.0`, memasang reverse proxy, atau mempublikasikannya ke internet. Kode rentan tidak ditambahkan ke implementasi aman untuk membuat dokumentasi ini.

Saat mengumpulkan bukti, gunakan data fiktif, sertakan identitas pengguna VM dan waktu sistem sekali per kelompok bukti sesuai tugas, serta sensor seluruh rahasia. Manifest pelacakan bukti proyek tersedia di [docs/manifest-hasil-uji.md](docs/manifest-hasil-uji.md).

## 8. Rujukan

1. Dokumen tugas dosen: `Project_Pengganti_UTS_Keamanan_Sistem_Komputer.docx`, Bagian 2, syarat 2, 3, 8, 9, dan 10; ketentuan bukti pada Bagian 3.
2. Kode implementasi dan pengujian pada tautan berkas di setiap bagian dokumentasi ini.
3. [Laravel 12 — Hashing](https://laravel.com/docs/12.x/hashing).
4. [Laravel 12 — Authentication](https://laravel.com/docs/12.x/authentication).
5. [Laravel 12 — Query Builder](https://laravel.com/docs/12.x/queries).
6. [Laravel 12 — Blade: Displaying Data](https://laravel.com/docs/12.x/blade#displaying-data).
7. [Laravel 12 — Session](https://laravel.com/docs/12.x/session).
8. [PHP — openssl_encrypt](https://www.php.net/manual/en/function.openssl-encrypt.php) dan [openssl_decrypt](https://www.php.net/manual/en/function.openssl-decrypt.php).
