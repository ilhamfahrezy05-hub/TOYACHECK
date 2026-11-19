# Laporan Audit Keamanan Aplikasi — TOYACHECK (Laravel 13)

- **Tanggal Audit:** 20 November 2026
- **Auditor:** Principal Application Security Engineer (Defensive Audit)
- **Target Aplikasi:** TOYACHECK — Sistem Informasi & Inspeksi Armada Distribusi (PT. Toyamilindo)
- **Stack Teknologi:** Laravel Framework `v13.38.0`, PHP `8.3.16`, Blade Templates, Tailwind CSS, MySQL (`127.0.0.1:3306`), Spatie Laravel Permission `^8.3`, Localhost Development (Laragon)
- **Metodologi:** Static Code Analysis, Configuration Review, Route/RBAC Mapping, Database Privilege Inspection (Read-Only), serta Dependency Audit (`composer audit --no-interaction`). Tidak ada modifikasi kode sumber ataupun data database selama proses audit berlangsung.

---

## Ringkasan Eksekutif

Berdasarkan hasil audit terhadap seluruh controller, model, route, view Blade, konfigurasi framework, dependensi Composer, serta hak akses database MySQL, tidak ditemukan kerentanan **Critical** seperti SQL Injection atau Remote Code Execution (RCE) pada kode aplikasi. Seluruh query database menggunakan Eloquent ORM / parameter binding secara konsisten, seluruh form state-changing dilindungi token `@csrf`, password di-hash menggunakan Bcrypt (`rounds=12`), dan seluruh model mendefinisikan `$fillable` secara eksplisit.

Namun, ditemukan sejumlah celah **High** dan **Medium** yang berdampak signifikan pada integritas, kerahasiaan data antar-peran, dan ketahanan akun pengguna, terutama di area **Stored XSS / DOM-Based XSS**, **IDOR & Broken Access Control**, **Bypass Verifikasi Password Lama**, **Ketiadaan Rate Limiting Login**, dan **Dependency Vulnerabilities**.

### Distribusi Temuan

| Klasifikasi | Critical | High | Medium | Low | Informational | Total |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Confirmed Vulnerabilities** | 0 | 5 | 6 | 3 | 0 | **14** |
| **Theoretical Risks** | 0 | 0 | 2 | 2 | 1 | **5** |
| **Total** | **0** | **5** | **8** | **5** | **1** | **19** |

---

## Ringkasan Evaluasi 15 Area Audit

| No | Area Audit | Status | Catatan Ringkas |
| :---: | :--- | :---: | :--- |
| 1 | Authentication & Session Security | **Perlu Perbaikan** | `AuthController` sudah meregenerasi session ID saat login dan invalidate saat logout; cookie `http_only=true`, `same_site=lax`, driver `database`. Namun `SettingController@updateProfile` mengizinkan ubah password tanpa cek `current_password` (`SEC-04`), dan `/m/login` berada di luar middleware `guest`. |
| 2 | Authorization & Privilege Escalation | **Perlu Perbaikan** | Mayoritas route terproteksi `role:...`, namun `ChecklistController@store` mengizinkan Admin serta tidak memvalidasi kesesuaian role vs `checklist_type`; `DriverAppController::exportVehiclePdf` terbuka untuk Driver; `UserController` tidak memvalidasi `exists:roles,name` (`SEC-06`, `SEC-07`). |
| 3 | IDOR & Resource Ownership | **Rentan (High)** | `DriverAppController::historyDetail()` dan `ChecklistController::show()` tidak memverifikasi kepemilikan (`driver_id`) atau status checklist bagi Mekanik (`SEC-05`). |
| 4 | SQL Injection | **Aman** | Tidak ditemukan interpolasi input user pada `DB::raw`, `whereRaw`, `selectRaw`, `orderByRaw`. Semua `*Raw()` menggunakan string konstan (`THEO-05`). |
| 5 | XSS & Output Escaping | **Rentan (High)** | Ditemukan 3 titik Stored/DOM XSS pada Leaflet popup vendor, toast notification (`toast.innerHTML`), dan dinamis DOM `repairs-create`/`reports-create` (`SEC-01`, `SEC-02`, `SEC-03`), serta CSV Formula Injection (`SEC-08`). |
| 6 | CSRF Protection | **Baik (Catatan Minor)** | Seluruh 45+ `<form method="POST">` di view Blade menyertakan `@csrf`. Namun `GET` route di `DriverAppController` memodifikasi kolom `assigned_vehicle_id` user di DB (`SEC-13`). |
| 7 | Mass Assignment | **Baik** | Seluruh model menggunakan `$fillable` eksplisit (termasuk `#[Fillable]` di `User.php`). Tidak ada `$guarded = []` dan tidak ada `$request->all()` langsung ke `create/update`. |
| 8 | File Upload Security | **Perlu Penguatan** | Validasi tipe/ukuran sudah ada (`image`, `mimes:jpg,jpeg,png,webp,pdf`, max `2-5 MB`). Namun file disimpan di disk `public` tanpa pembatasan server-level pada folder upload, dan file lama pada update/delete tertentu tidak ikut dibersihkan (`SEC-14`). |
| 9 | Sensitive Information Exposure | **Perlu Perbaikan** | `.env` tidak di-track git (`.gitignore` valid), namun `APP_DEBUG=true`, `laravel-debugbar` aktif di dev, log stack trace tersimpan detail, dan kredensial akun demo tercantum terbuka di `README.md` (`SEC-11`). |
| 10 | API & Route Protection | **Perlu Penguatan** | Tidak ada file `routes/api.php`; seluruh route operasional berada di dalam `middleware('auth')` kecuali `/up` (healthcheck), `/m/qr/{code?}`, `/m/login`, `/login`. Tidak ada middleware HTTP security headers (`SEC-12`). |
| 11 | Rate Limiting | **Rentan (High)** | Endpoint `POST /login` dan `POST /m/login` tidak memiliki `RateLimiter` / middleware `throttle` sama sekali (`SEC-04b` -> `SEC-04`). |
| 12 | Password Hashing | **Perlu Penguatan** | Password di-hash menggunakan Bcrypt 12 rounds (`BCRYPT_ROUNDS=12`, cast `'password' => 'hashed'`). Kebijakan minimal hanya 6 karakter tanpa kompleksitas (`SEC-13`). |
| 13 | Dependency Vulnerabilities | **Perlu Pembaruan** | `composer audit` menemukan 8 security advisories pada 5 paket (`laravel/framework`, `symfony/http-foundation`, `symfony/http-kernel`, `symfony/mime`, `symfony/routing`) (`SEC-10`). |
| 14 | Database Permissions | **Perlu Perbaikan** | Koneksi MySQL memakai `root@localhost` tanpa kata sandi dengan hak penuh `ON *.* ... WITH GRANT OPTION` (`SEC-09`). |
| 15 | Error Handling & Logging | **Perlu Penguatan** | `APP_DEBUG=true`, `LOG_CHANNEL=stack` (`single`) dengan `LOG_LEVEL=debug`; file `laravel.log` berukuran `1.32 MB` tanpa rotasi harian (`SEC-11`). |

---

## Bagian I — Confirmed Vulnerabilities

### 1. SEC-01 — Stored DOM-Based XSS pada Peta Logistik Vendor Pengadaan
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **High**
- **File & Nomor Baris:**
  - `resources/views/pengadaan/vendors.blade.php:126`
  - `resources/views/pengadaan/vendors.blade.php:253`
  - `resources/views/pengadaan/vendors.blade.php:267-269`
  - `resources/views/pengadaan/vendors.blade.php:272-279`
- **Root Cause:**
  Nilai field vendor (`nama_vendor`, `keahlian`, `alamat`) yang diinput melalui form pendaftaran/edit vendor hanya di-escape untuk konteks JavaScript literal (`@json($vendors)` dan `Js::from($v->nama_vendor)`), lalu diinterpolasi ke dalam string HTML template literal dan diserahkan langsung ke API Leaflet `.bindPopup(...)` dan `.setContent(...)`, yang merender HTML mentah ke dalam DOM (`innerHTML`) tanpa HTML entity escaping.
- **Evidence:**
  ```javascript
  // resources/views/pengadaan/vendors.blade.php:253, 268
  const vendorsData = @json($vendors);
  ...
  marker.bindPopup(`<strong>${v.nama_vendor}</strong><br><small>${v.keahlian || 'Supplier'}</small><br><small>${v.alamat || ''}</small>`);

  // resources/views/pengadaan/vendors.blade.php:277
  L.popup()
      .setLatLng([lat, lng])
      .setContent(`<strong>${title}</strong>`)
      .openOn(mapInstance);
  ```
- **Potential Impact:**
  Pengguna yang memiliki akses input/edit vendor (misalnya staf Pengadaan atau Admin pada `vendors.store` / `pengadaan.vendors.store`) dapat menyisipkan payload HTML/JS seperti `<img src=x onerror=...>` pada `nama_vendor` atau `alamat`. Ketika Admin atau staf Pengadaan lain membuka halaman `/pengadaan/vendors` atau mengklik baris vendor pada tabel, script dieksekusi dalam konteks origin aplikasi (pencurian token CSRF, aksi paksa atas nama Admin seperti membuat akun baru atau menyetujui pengadaan).
- **Recommended Fix:**
  Bangun node DOM secara aman menggunakan `document.createElement()` dan `.textContent` saat mengisi konten popup Leaflet, atau lakukan HTML escaping dengan helper JS khusus sebelum memanggil `bindPopup()`/`setContent()`:
  ```javascript
  const popupNode = document.createElement('div');
  const titleEl = document.createElement('strong');
  titleEl.textContent = v.nama_vendor || '';
  const skillEl = document.createElement('small');
  skillEl.textContent = v.keahlian || 'Supplier';
  const addrEl = document.createElement('small');
  addrEl.textContent = v.alamat || '';
  popupNode.append(titleEl, document.createElement('br'), skillEl, document.createElement('br'), addrEl);
  marker.bindPopup(popupNode);
  ```
- **Safe Verification Method:**
  Di lingkungan lokal terisolasi, simpan data uji pada form vendor dengan teks `<b data-xss="test">Test</b>` pada `nama_vendor`. Jika pada inspeksi DOM popup peta elemen `<b>` dirender sebagai tag HTML aktif (teks tebal tanpa menampilkan literal `<b ...>`), celah terbukti aktif.
- **Regression Test:**
  Buat test Feature/Dusk atau assertions unit view bahwa string `<script>` / `<img onerror>` di dalam `nama_vendor` tampil sebagai teks biasa (escaped) dan `vendors.blade.php` tidak lagi memakai template string HTML mentah di `bindPopup` / `setContent`.

---

### 2. SEC-02 — Stored XSS pada Sistem Notifikasi Toast Global (`toast.innerHTML`)
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **High**
- **File & Nomor Baris:**
  - `resources/views/layouts/partials/scripts.blade.php:55-68`
  - `resources/views/layouts/partials/scripts.blade.php:76-84`
  - Controller sumber pesan dinamis:
    - `app/Http/Controllers/PengadaanController.php:96, 115, 128, 248`
    - `app/Http/Controllers/SparepartRequestController.php:77`
    - `app/Http/Controllers/InspectionItemController.php:119`
    - `app/Http/Controllers/DriverAppController.php:353-354`
- **Root Cause:**
  Helper `window.showToast(message, type, title)` menyisipkan parameter `message` dan `title` menggunakan `toast.innerHTML`, sedangkan beberapa controller menyertakan data dari database (yang berasal dari input pengguna seperti `nama_sparepart`, `nama_vendor`, `nama` item inspeksi, atau `plat_nomor`) ke dalam `session('success')` / `session('error')`, yang kemudian dikirim via `@json(session(...))`.
- **Evidence:**
  ```javascript
  // resources/views/layouts/partials/scripts.blade.php:62-67, 77
  toast.innerHTML =
      '<div class="toast-icon"><i class="fa-solid ' + (icons[type] || icons.info) + '"></i></div>' +
      '<div class="toast-content">' +
      '<div class="toast-title">' + (title || titles[type] || 'Notifikasi') + '</div>' +
      '<div class="toast-message">' + message + '</div>' +
      '</div>';
  ...
  window.showToast(@json(session('success')), 'success');
  ```
- **Potential Impact:**
  Jika Mekanik mengajukan sparepart dengan `nama_sparepart` berisi payload XSS (atau HSE menambahkan item K3 dengan nama berisi `<img src=x onerror=...>`), lalu Admin atau Pengadaan meninjau, mengedit, men-toggle, atau menghapus data tersebut, pesan toast yang muncul langsung mengeksekusi JavaScript di sesi browser Admin/Pengadaan.
- **Recommended Fix:**
  Ganti pengisian teks toast menggunakan DOM API (`document.createElement` dan `textContent`) agar `message` dan `title` selalu diperlakukan sebagai teks murni:
  ```javascript
  const msgEl = document.createElement('div');
  msgEl.className = 'toast-message';
  msgEl.textContent = message;
  ```
- **Safe Verification Method:**
  Periksa elemen `.toast-message` pada DevTools saat melakukan aksi update/delete pada item yang diberi nama `<b>UjiToast</b>`. Jika tag `<b>` ter-render sebagai HTML (bukan teks literal `<b>UjiToast</b>`), kerentanan terkonfirmasi.
- **Regression Test:**
  Pastikan pada view `scripts.blade.php` tidak ada penyisipan `message` atau `title` melalui `innerHTML`, dan uji dengan menyuntikkan flash session berisi karakter `<img src=x onerror=...>`.

---

### 3. SEC-03 — DOM-Based XSS pada Form Tambah Sparepart Log Perbaikan & Preview Nama File Upload
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **Medium** (Stored DOM XSS pada `repairs-create.blade.php`; Local/Self DOM XSS pada `reports-create.blade.php`)
- **File & Nomor Baris:**
  - `resources/views/mekanik/repairs-create.blade.php:374`
  - `resources/views/mekanik/repairs-create.blade.php:471-490`
  - `resources/views/pengadaan/reports-create.blade.php:279-284`
- **Root Cause:**
  1. Pada `repairs-create.blade.php:471-490`, `renderSelectedParts()` merender `item.nama`, `item.abc`, dan `item.satuan` langsung menggunakan `div.innerHTML`. Nilai tersebut berasal dari database tabel `spareparts`.
  2. Pada `reports-create.blade.php:282`, nama file yang dipilih dari sistem lokal (`input.files[0].name`) langsung diinterpolasikan ke `titleEl.innerHTML`.
- **Evidence:**
  ```javascript
  // resources/views/mekanik/repairs-create.blade.php:471-475
  div.innerHTML =
      '<input type="hidden" name="spareparts[' + idx + '][id]" value="' + item.id + '">' +
      '<div>' +
          '<div style="font-size:13px;font-weight:700;color:#091E3A">' + item.nama + '</div>' +
          '<div style="font-size:11px;color:#64748B">Criteria ' + item.abc + '</div>' +
  ...
  // resources/views/pengadaan/reports-create.blade.php:282
  titleEl.innerHTML = `<span style="color:#16A34A"><i class="fa-solid fa-circle-check"></i> Berkas terpilih: ${input.files[0].name}</span>`;
  ```
- **Potential Impact:**
  Data `nama_sparepart` atau `satuan` bermuatan HTML/JS di katalog `spareparts` akan tereksekusi saat Mekanik memilih sparepart tersebut untuk log perbaikan.
- **Recommended Fix:**
  Gunakan pembuatan node DOM dengan `.textContent` untuk `item.nama`, `item.satuan`, serta `input.files[0].name` pada `handleFileProofSelected`.
- **Safe Verification Method:**
  Tambahkan sparepart uji dengan nama `Test <i>Italic</i>` di Master Data, lalu klik tambah pada `/mekanik/repairs/create` dan amati apakah kata `Italic` ter-render sebagai tag HTML `<i>`.
- **Regression Test:**
  Verifikasi via code review / Dusk test bahwa string HTML pada `nama_sparepart` dan nama file tampil sebagai plain text di DOM.

---

### 4. SEC-04 — Ketiadaan Rate Limiting (Brute-Force Protection) pada Login & Celah Route `/m/login`
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **High**
- **File & Nomor Baris:**
  - `routes/web.php:28-32`
  - `routes/web.php:39-47`
  - `app/Http/Controllers/AuthController.php:15-54`
  - `app/Http/Controllers/DriverAppController.php:75-78`
- **Root Cause:**
  Baik `POST /login` (`login.attempt`) maupun `POST /m/login` (`mobile.login.attempt`) tidak dilindungi oleh middleware `throttle` maupun `Illuminate\Support\Facades\RateLimiter` di dalam `AuthController::authenticate()`. Selain itu, `GET /m/login` dan `POST /m/login` diletakkan di luar grup middleware `guest`, sehingga pengguna yang sudah login pun dapat mengirim POST login ke endpoint tersebut.
- **Evidence:**
  ```php
  // routes/web.php:28-32 & 39-47
  Route::get('/m/login', [DriverAppController::class, 'showLogin'])->name('mobile.login');
  Route::post('/m/login', [DriverAppController::class, 'authenticate'])->name('mobile.login.attempt');

  Route::middleware('guest')->group(function () {
      Route::get('/login', [AuthController::class, 'login'])->name('login');
      Route::post('/login', [AuthController::class, 'authenticate'])->name('login.attempt');
  });
  ```
  `app/Http/Controllers/AuthController.php:15-54` langsung memanggil `Auth::attempt(...)` tanpa pengecekan `RateLimiter::tooManyAttempts(...)` dan tanpa `RateLimiter::hit(...)`.
- **Potential Impact:**
  Penyerang di jaringan lokal atau internet (saat di-deploy) dapat melakukan serangan *brute-force* maupun *credential stuffing* secara bebas tanpa dibatasi oleh server, terutama mengingat adanya akun bawaan dengan pola username sederhana (`distributor2`, `driver1`, `hse1`, `mekanik1`, `pengadaan1`) dan kebijakan panjang password minimal 6 karakter.
- **Recommended Fix:**
  1. Terapkan `RateLimiter` di `AuthController::authenticate()` berdasarkan kombinasi `Str::lower($identity) . '|' . $request->ip()` (misal maks. 5 percobaan per 1 menit) serta panggil `RateLimiter::clear()` saat login berhasil.
  2. Tambahkan middleware `throttle:10,1` pada route `POST /login` dan `POST /m/login`.
  3. Pertimbangkan menambahkan middleware `guest` pada `POST /m/login` (atau konsisten dengan `showLogin`).
- **Safe Verification Method:**
  Periksa daftar middleware pada route menggunakan `php artisan route:list --path=login` untuk memastikan tidak adanya middleware `throttle` saat ini. (Jangan melakukan request flooding).
- **Regression Test:**
  Buat PHPUnit Feature Test yang mengirim 6 kali `post('/login', ...)` dengan password salah dan memverifikasi bahwa percobaan ke-6 ditolak (`429 Too Many Requests` atau validation error throttle), serta direset setelah login berhasil.

---

### 5. SEC-05 — Bypass Verifikasi Password Lama melalui Endpoint Update Profil
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **High**
- **File & Nomor Baris:**
  - `app/Http/Controllers/SettingController.php:24-54` (`updateProfile`)
  - `app/Http/Controllers/SettingController.php:68-89` (`updatePassword`)
  - `routes/web.php:380-382`
- **Root Cause:**
  Aplikasi memiliki dua endpoint yang bisa mengubah kata sandi user. Endpoint `PUT /settings/password` (`updatePassword`) mewajibkan `current_password` dan memeriksanya via `Hash::check()`. Sebaliknya, endpoint `PUT /settings/profile` (`updateProfile`) menerima field `password` (`nullable|string|min:6|confirmed`) dan langsung memperbarui password pengguna **tanpa** meminta ataupun memverifikasi `current_password`.
- **Evidence:**
  ```php
  // app/Http/Controllers/SettingController.php:28-53
  $validated = $request->validate([
      'name'     => 'required|string|max:255',
      'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
      'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
      'password' => 'nullable|string|min:6|confirmed',
  ]);
  ...
  if ($request->filled('password')) {
      $validated['password'] = $request->input('password');
      $passwordChanged = true;
  }
  $user->update($validated);
  ```
- **Potential Impact:**
  Jika sesi pengguna sempat terbuka di perangkat bersama (seperti HP/tablet operasional Driver atau PC bengkel) atau terekspos oleh celah XSS (`SEC-01`–`SEC-03`), penyerang dapat mengganti password akun korban secara permanen melalui `PUT /settings/profile` tanpa mengetahui kata sandi lama korban, sehingga mengunci pemilik akun asli (*Account Takeover*).
- **Recommended Fix:**
  Wajibkan `current_password` (`required_with:password|current_password`) di `SettingController@updateProfile` apabila field `password` diisi, atau pisahkan sepenuhnya logika perubahan password hanya ke `updatePassword()` serta wajibkan verifikasi password saat ini di UI mobile (`driver/profile.blade.php`) juga.
- **Safe Verification Method:**
  Lakukan tinjauan kode/feature test mengirim `PUT /settings/profile` dengan `password` dan `password_confirmation` tanpa menyertakan `current_password`. Saat ini server menerimanya (`302` redirect sukses) dan password berubah.
- **Regression Test:**
  Buat PHPUnit Feature Test `test_update_profile_requires_current_password_when_changing_password()` yang memverifikasi bahwa `PUT /settings/profile` dengan field `password` tanpa `current_password` yang valid mengembalikan error validasi `422/302` dan hash password di DB tidak berubah.

---

### 6. SEC-06 — IDOR & Broken Access Control pada Endpoint Checklist, Riwayat Mobile, dan PDF Kendaraan
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **High**
- **File & Nomor Baris:**
  - `routes/web.php:242-254, 265, 269-279, 303-307`
  - `app/Http/Controllers/ChecklistController.php:25-60, 169-180`
  - `app/Http/Controllers/DriverAppController.php:163-180, 401-408, 413-504`
- **Root Cause:**
  Kurangnya pemeriksaan kepemilikan (*object-level authorization* / Laravel Policy) dan pemisahan role di tingkat controller pada endpoint berikut:
  1. **IDOR pada `DriverAppController@historyDetail(Checklist $checklist)` (`routes/web.php:265`, `DriverAppController.php:401-408`):** Tidak ada verifikasi `$checklist->driver_id === Auth::id()` (untuk Driver) atau pembatasan `checklist_type === 'k3'` (untuk HSE). Setiap Driver dapat mengakses `/driver/history/{id}` milik Driver lain maupun laporan K3 HSE dengan menebak ID berurutan.
  2. **Broken Access Control pada `ChecklistController@index` & `show($id)` (`routes/web.php:303-307`, `ChecklistController.php:42-52, 169-180`):**
     - `index()` hanya memfilter `status = 'perbaikan'` untuk role `mekanik`, tetapi Driver dan HSE tetap dapat mengakses `/checklists` dan melihat **seluruh checklist Driver/HSE lain**.
     - `show($id)` langsung memanggil `Checklist::with(...)->findOrFail($id)` tanpa pembatasan. Mekanik (yang seharusnya *read-only* khusus laporan `perbaikan`) bisa melihat laporan checklist berstatus `aman`/`kendala`, dan Driver bisa melihat detail laporan Driver lain.
  3. **Bypass Role pada `DriverAppController@exportVehiclePdf` (`routes/web.php:244, 253`, `DriverAppController.php:163-180`):** Di `resources/views/driver/vehicle.blade.php:337`, tombol Export PDF disembunyikan untuk non-HSE (`@role('hse')`), tetapi route `driver.vehicle.export-pdf` berada di grup `role:driver|hse` tanpa pengecekan `role:hse`. Driver dapat mengakses URL langsung untuk mengekspor riwayat inspeksi & identitas driver lain pada armada tersebut.
  4. **Cross-HSE Modification (`DriverAppController.php:413-504`):** `editK3Report`, `updateK3Report`, dan `destroyK3Report` hanya mengecek `checklist_type === 'k3'`, tanpa memeriksa ownership (`driver_id`) apabila di masa mendatang terdapat lebih dari satu petugas HSE.
- **Evidence:**
  ```php
  // app/Http/Controllers/DriverAppController.php:401-408
  public function historyDetail(Checklist $checklist)
  {
      if ($redirect = $this->ensureNotAdmin()) return $redirect;
      $checklist->load(['vehicle', 'driver', 'items.inspectionItem']);
      return view('driver.history-detail', compact('checklist'));
  }

  // app/Http/Controllers/ChecklistController.php:169-180
  public function show($id)
  {
      $checklist = Checklist::with([...])->findOrFail($id);
      return view('checklists.show', compact('checklist'));
  }
  ```
- **Potential Impact:**
  Pengungkapan data inspeksi, catatan pribadi/operasional, foto bukti kerusakan, serta nama/NIK petugas kepada pihak yang tidak berhak; serta manipulasi status unit oleh role yang tidak semestinya.
- **Recommended Fix:**
  1. Terapkan **Laravel Policy (`ChecklistPolicy`)** atau pengecekan eksplisit di `DriverAppController@historyDetail`: jika user adalah `driver`, pastikan `$checklist->driver_id === $user->id`; jika `hse`, pastikan `$checklist->checklist_type === 'k3'`.
  2. Di `ChecklistController@index` dan `show($id)`:
     - Jika role `mekanik`: wajib `$checklist->status === 'perbaikan'` (kembalikan `403` jika bukan).
     - Jika role `driver`: wajib `$checklist->driver_id === $user->id` pada `index` dan `show`.
     - Jika role `hse`: wajib `$checklist->checklist_type === 'k3'`.
  3. Pindahkan route `driver.vehicle.export-pdf` ke dalam grup `middleware('role:hse')` (`routes/web.php:269`).
- **Safe Verification Method:**
  Jalankan PHPUnit Feature Test dengan dua akun (`driver1` dan `driver2`, serta `mekanik1`) untuk mengakses `/driver/history/{id}` dan `/checklists/{id}` lintas kepemilikan / status. Saat ini responsnya `200 OK` (seharusnya `403 Forbidden`).
- **Regression Test:**
  Tambahkan test otorisasi otomatis di `tests/Feature/ChecklistAuthorizationTest.php` untuk skenario: Driver melihat checklist Driver lain (`403`), Mekanik melihat checklist berstatus `aman` (`403`), Driver mengakses `driver.vehicle.export-pdf` (`403`).

---

### 7. SEC-07 — Inkonsistensi Pembatasan Role pada Input Checklist (`ChecklistController@store`) & Validasi Role Pengguna (`UserController`)
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **Medium**
- **File & Nomor Baris:**
  - `routes/web.php:309-313`
  - `app/Http/Controllers/ChecklistController.php:96-107`
  - `app/Http/Controllers/UserController.php:36, 53, 69, 94`
- **Root Cause:**
  1. `routes/web.php:309-313` mengizinkan `role:admin|driver|hse` melakukan `POST /checklists` (`ChecklistController@store`), padahal dokumentasi bisnis & komentar kode (`ChecklistController.php:35`, `routes/web.php:239`) menyatakan Admin hanya bertugas memonitoring dan dilarang menginput checklist. Selain itu, `ChecklistController@store` (baris 100) memvalidasi `'checklist_type' => Rule::in(['fisik', 'k3'])` tanpa memverifikasi kecocokan role (Driver bisa kirim `k3`, HSE bisa kirim `fisik`).
  2. `UserController@store` (baris 36) dan `update` (baris 69) hanya memvalidasi `'role' => 'required'` tanpa `'exists:roles,name'`.
- **Evidence:**
  ```php
  // app/Http/Controllers/ChecklistController.php:98-101
  $validated = $request->validate([
      'vehicle_id'     => 'required|exists:vehicles,id',
      'checklist_type' => ['required', Rule::in(['fisik', 'k3'])],
  ...
  // app/Http/Controllers/UserController.php:36, 53
  'role' => 'required',
  ...
  $user->assignRole($validated['role']);
  ```
- **Potential Impact:**
  Driver dapat menginput laporan K3 resmi ke database melalui `POST /checklists` (melewati pembatasan role di UI), dan HSE dapat menginput checklist fisik. Input role sembarang ke `UserController` menyebabkan `RoleDoesNotExist` exception yang memicu HTTP `500` beserta kebocoran stack trace saat `APP_DEBUG=true`.
- **Recommended Fix:**
  1. Batasi `POST /checklists` sesuai aturan bisnis (misalnya hapus `admin` jika Admin dilarang input, dan enforce `$user->hasRole('driver') -> hanya 'fisik'`, `$user->hasRole('hse') -> hanya 'k3'`).
  2. Tambahkan `Rule::exists('roles', 'name')` pada validasi `role` di `UserController@store` dan `update`.
- **Safe Verification Method:**
  Uji dengan mengirimkan `POST /checklists` sebagai user `hse` dengan payload `checklist_type=fisik`.
- **Regression Test:**
  Feature test memastikan HSE ditolak (`403`/`422`) saat mencoba submit `checklist_type=fisik`, dan `UserController` menolak role yang tidak terdaftar dengan status validasi `422`/`302`.

---

### 8. SEC-08 — CSV Formula Injection (CSV Injection) pada Export Laporan CSV
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **Medium**
- **File & Nomor Baris:**
  - `app/Http/Controllers/ChecklistHistoryController.php:374-393`
  - `app/Http/Controllers/MekanikController.php:683-705`
- **Root Cause:**
  Kedua endpoint export CSV menulis field teks hasil input pengguna (`driver->name`, `nama_sparepart`, `vendor_name`, `plat_nomor`, `item->item`) langsung ke output `fputcsv()` tanpa menetralisasi awalan karakter pemicu formula spreadsheet (`=`, `+`, `-`, `@`, `\t`, `\r`).
  *(Catatan tambahan: pada `ChecklistHistoryController.php:374-391`, teramati juga jumlah elemen header `fputcsv` sempat diperbaiki pada sesi ini dan tetap perlu diawasi agar selalu sejajar).*
- **Evidence:**
  ```php
  // app/Http/Controllers/MekanikController.php:695-704
  fputcsv($out, [
      $reqCode,
      $req->nama_sparepart,
      $req->qty,
      strtoupper($req->priority_level ?? 'NORMAL'),
      $kategori,
      $vendor,
      $req->created_at->format('d M Y'),
      $statusLabel,
  ]);
  ```
- **Potential Impact:**
  Jika nilai `nama_sparepart`, `vendor_name`, atau nama pengguna mengandung payload formula seperti `=HYPERLINK(...)` atau `=cmd|'/C calc'!A0` (pada Excel tanpa proteksi makro/DDE), saat Admin atau Mekanik mengunduh dan membuka file CSV di Excel/LibreOffice, formula dapat tereksekusi secara lokal di komputer admin.
- **Recommended Fix:**
  Buat helper `sanitizeCsvCell(?string $value): string` yang menambahkan prefiks tanda kutip tunggal (`'`) atau spasi apabila karakter awal string adalah `=`, `+`, `-`, `@`, `\t`, atau `\r`.
- **Safe Verification Method:**
  Simpan item uji di `SparepartRequest` bernama `=1+1`, lalu ekspor CSV melalui `/mekanik/approvals/export` dan periksa isi file mentahnya: nilai masih diawali `=1+1` tanpa prefiks `'`.
- **Regression Test:**
  Unit test untuk fungsi sanitasi CSV yang memastikan input `=CMD()`, `+SUM()`, `-1+1`, `@SUM()` diawali tanda kutip tunggal saat diekspor.

---

### 9. SEC-09 — Database Menggunakan Akun `root@localhost` Tanpa Password dengan Hak Akses Penuh (`*.*`)
- **Klasifikasi:** Confirmed Vulnerability (Konfigurasi Lingkungan)
- **Severity:** **Medium** (di localhost) / **Critical** (bila terbawa ke production)
- **File & Nomor Baris:**
  - `.env:23-28` (`DB_USERNAME=root`, `DB_PASSWORD=`)
  - Runtime MySQL `SHOW GRANTS FOR CURRENT_USER()`
- **Root Cause:**
  Aplikasi menggunakan user bawaan Laragon `root@localhost` tanpa password yang memiliki seluruh hak administratif MySQL pada semua database (`ON *.*`), termasuk `SUPER`, `FILE`, `CREATE USER`, `DROP`, dan `WITH GRANT OPTION`.
- **Evidence:**
  ```sql
  User: root@localhost
  GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, SHUTDOWN, PROCESS, FILE, ... ON *.* TO `root`@`localhost` WITH GRANT OPTION
  ```
- **Potential Impact:**
  Melanggar prinsip *Least Privilege*. Jika terdapat celah pembacaan file/eksekusi query di aplikasi atau layanan lain di host yang sama, penyerang memiliki kontrol penuh atas seluruh instance MySQL dan berpotensi menulis/membaca file sistem via privilege `FILE`.
- **Recommended Fix:**
  Buat akun database khusus untuk TOYACHECK dengan kata sandi kuat dan batasi hak akses hanya pada skema `toyacheck.*` (`SELECT, INSERT, UPDATE, DELETE` untuk operasional aplikasi; serta gunakan user terpisah atau privilege minimal saat migrasi).
- **Safe Verification Method:**
  Jalankan `SHOW GRANTS FOR CURRENT_USER()` dan pastikan hak akses hanya terbatas pada `toyacheck.*` tanpa `FILE`, `SUPER`, maupun `GRANT OPTION`.
- **Regression Test:**
  Checklist *deployment hardening* otomatis / skrip verifikasi `.env.production` memastikan `DB_USERNAME !== 'root'` dan `DB_PASSWORD` tidak kosong.

---

### 10. SEC-10 — Dependency Vulnerabilities pada 5 Paket Composer (`composer audit`)
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **Medium**
- **File & Nomor Baris:**
  - `composer.lock` (`laravel/framework v13.38.0`, `symfony/http-foundation v7.4.21`, `symfony/http-kernel v7.4.21`, `symfony/mime v7.4.19`, `symfony/routing v7.4.21`)
- **Root Cause:**
  Versi paket `laravel/framework` dan beberapa komponen `symfony/*` di `composer.lock` tertinggal di bawah versi patch keamanan terbaru yang dirilis pada November 2026.
- **Evidence:**
  Output nyata dari perintah `composer audit --no-interaction`:
  1. `laravel/framework` (`v13.38.0` < `13.40.0`): **CVE-2026-128083** (Low) — *Task Output Injection - Concurrency Process*
  2. `symfony/mime` (`v7.4.19` < `7.4.22`): **CVE-2026-45070** (Medium) — *Email Header Injection via Non-Token Characters in Mime Parameter Names*
  3. `symfony/http-foundation` (`v7.4.21` < `7.4.23`): **CVE-2026-121619**, **CVE-2026-118514**, **PKSA-pr6q-2w5s-kzzf**, **PKSA-b2gg-vk1g-6d5q**
  4. `symfony/http-kernel` (`v7.4.21` < `7.4.23`): **CVE-2026-123339**
  5. `symfony/routing` (`v7.4.21` < `7.4.23`): **CVE-2026-118516**
- **Potential Impact:**
  Meskipun aplikasi saat ini belum memakai fitur email MIME secara aktif (`MAIL_MAILER=log`) maupun `Concurrency::run`, keberadaan CVE pada komponen HTTP foundation/routing dapat dieksploitasi pada konfigurasi reverse proxy atau path matching tertentu (misalnya *HEAD request bypass* atau *host header manipulation*).
- **Recommended Fix:**
  Jalankan pembaruan dependensi terkendali (`composer update laravel/framework symfony/* --with-dependencies`) dan verifikasi ulang dengan `composer audit` hingga 0 advisory.
- **Safe Verification Method:**
  Jalankan `composer audit --no-interaction` di terminal lokal.
- **Regression Test:**
  Integrasikan `composer audit --locked` sebagai langkah wajib pada pipeline CI/CD sebelum deployment.

---

### 11. SEC-11 — Mode Debug Aktif (`APP_DEBUG=true`), Log Stack Trace Tanpa Rotasi, dan Kredensial Default Terbuka di Dokumentasi
- **Klasifikasi:** Confirmed Vulnerability (Konfigurasi Pengembangan & Kebocoran Informasi)
- **Severity:** **Medium**
- **File & Nomor Baris:**
  - `.env:4, 18-21` (`APP_DEBUG=true`, `LOG_CHANNEL=stack`, `LOG_STACK=single`, `LOG_LEVEL=debug`)
  - `composer.json:17` (`barryvdh/laravel-debugbar`)
  - `storage/logs/laravel.log` (`1,324,999` bytes)
  - `database/seeders/AdminSeeder.php:18-24` & `README.md:161-172`
- **Root Cause:**
  1. `APP_DEBUG=true` dan `LOG_LEVEL=debug` menyebabkan pesan error detail, path direktori server, nama tabel SQL, serta variabel lingkungan berpotensi tampil kepada pengguna saat terjadi exception.
  2. `LOG_STACK=single` menyimpan seluruh log tanpa rotasi otomatis ke satu file besar (`1.32 MB`).
  3. `README.md:161-172` mencantumkan username, NIK, dan password plaintext dari `AdminSeeder` (`distributor2` / `Admin123!`, dst.). Jika repositori bersifat publik di GitHub atau `AdminSeeder` dijalankan saat deploy ke staging/production tanpa mengubah password, siapa pun dapat masuk dengan akun Admin.
- **Evidence:**
  - `.env:4`: `APP_DEBUG=true`
  - `storage/logs/laravel.log`: berisi full stack trace (terverifikasi ukuran 1,324,999 bytes pada 20/11/2026).
  - `README.md:167-171`: tabel akun demo lengkap dengan kolom Password plaintext.
- **Potential Impact:**
  Eksposur struktur internal server, skema DB, dan akses tidak sah apabila server pengembangan terekspos ke jaringan publik atau seeder demo digunakan di lingkungan produksi.
- **Recommended Fix:**
  1. Pastikan di lingkungan produksi / staging: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_STACK=daily`, `LOG_LEVEL=warning`, dan instal dengan `--no-dev` agar `laravel-debugbar` tidak aktif.
  2. Jangan jalankan `AdminSeeder` di produksi, atau wajibkan perubahan password saat login pertama serta hapus kredensial Admin produksi dari dokumentasi publik.
- **Safe Verification Method:**
  Periksa `.env` dan jalankan `php artisan config:show app.debug` / `logging.default`.
- **Regression Test:**
  Audit konfigurasi environment pra-rilis (memastikan `app.debug === false` saat `app.env === 'production'`).

---

### 12. SEC-12 — Ketiadaan HTTP Security Headers & Atribut Subresource Integrity (SRI) pada Aset CDN Eksternal
- **Klasifikasi:** Confirmed Vulnerability (Defense-in-Depth Gap)
- **Severity:** **Medium**
- **File & Nomor Baris:**
  - `bootstrap/app.php:14-18`
  - CDN tanpa SRI (`integrity` / `crossorigin`):
    - `resources/views/layouts/app.blade.php:16` (`cdnjs.cloudflare.com/.../font-awesome/6.5.1/css/all.min.css`)
    - `resources/views/auth/login.blade.php:14`
    - `resources/views/driver/layout.blade.php:10`
    - `resources/views/pengadaan/vendors.blade.php:30, 251` (`unpkg.com/leaflet@1.9.4/...`)
    - `resources/views/pengadaan/vendors-create.blade.php:14, 202`
    - `resources/views/sop/pdf.blade.php:10`
    - `resources/views/vehicles/qr-print.blade.php:10`
- **Root Cause:**
  Aplikasi tidak mendaftarkan middleware untuk menyuntikkan HTTP Security Headers standar (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`, `Permissions-Policy`). Selain itu, skrip dan stylesheet eksternal (`leaflet.js`, `font-awesome`) dimuat dari CDN publik tanpa hash SRI.
- **Evidence:**
  `bootstrap/app.php:14-18` hanya mendaftarkan alias `'role'` tanpa global response headers middleware. Seluruh tag `<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">` tidak memiliki atribut `integrity="..."`.
- **Potential Impact:**
  Aplikasi rentan di-embed dalam `<iframe>` situs berbahaya (*Clickjacking*), browser dapat melakukan *MIME-type sniffing* pada file yang diunggah, dan bila terjadi kompromi pada CDN pihak ketiga, skrip berbahaya akan langsung dieksekusi oleh browser pengguna.
- **Recommended Fix:**
  1. Tambahkan middleware `SecurityHeaders` pada `bootstrap/app.php` (`$middleware->append(...)`) yang mengatur `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, dan CSP bertahap.
  2. Tambahkan hash `integrity="sha384-..." crossorigin="anonymous"` pada seluruh tag `<link>` dan `<script>` CDN (atau bundel melalui Vite lokal).
- **Safe Verification Method:**
  Periksa response header HTTP dari endpoint `/login` menggunakan DevTools atau `curl -I http://127.0.0.1:8000/login`.
- **Regression Test:**
  Feature test `$this->get('/login')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff')`.

---

### 13. SEC-13 — Kebijakan Password Lemah (`min:6`) & Mutasi Database pada Request HTTP `GET`
- **Klasifikasi:** Confirmed Vulnerability
- **Severity:** **Low**
- **File & Nomor Baris:**
  - `app/Http/Controllers/UserController.php:35` (`'password' => 'required|min:6'`)
  - `app/Http/Controllers/SettingController.php:32, 72` (`'password' => '...min:6|confirmed'`)
  - `app/Http/Controllers/DriverAppController.php:119-124, 201-204` (`showVehicle` & `checklistForm`)
- **Root Cause:**
  1. Panjang minimal kata sandi hanya 6 karakter tanpa mewajibkan kombinasi huruf besar/kecil/angka atau validasi `Password::defaults()`.
  2. Method `showVehicle()` (baris 119-124) dan `checklistForm()` (baris 201-204) yang dipetakan ke HTTP `GET` langsung melakukan `$user->update(['assigned_vehicle_id' => $vehicle->id, 'vehicle_assigned_at' => now()])`.
- **Evidence:**
  ```php
  // app/Http/Controllers/DriverAppController.php:119-124
  if ($user->hasRole('driver') || $user->hasRole('hse')) {
      $user->update([
          'assigned_vehicle_id' => $vehicle->id,
          'vehicle_assigned_at' => now(),
      ]);
  }
  ```
- **Potential Impact:**
  Password 6 karakter lebih mudah ditebak jika rate limiting belum aktif. Mutasi DB melalui HTTP `GET` melanggar prinsip idempotensi HTTP/REST (prefetching browser, link crawler, atau serangan image-tag CSRF sederhana dapat mengubah data `assigned_vehicle_id` user saat ini secara diam-diam tanpa persetujuan form POST).
- **Recommended Fix:**
  1. Gunakan aturan `Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()` di `UserController` dan `SettingController`.
  2. Pindahkan pembaruan `assigned_vehicle_id` ke saat form checklist benar-benar disubmit melalui `POST` (`storeChecklist`), bukan saat membuka halaman `GET`.
- **Safe Verification Method:**
  Akses `/driver/vehicles/{id}` via `GET` lalu cek nilai `assigned_vehicle_id` pada tabel `users` — nilainya berubah tanpa submit form `POST`.
- **Regression Test:**
  Feature test memastikan request `GET` ke `driver.vehicle.show` tidak mengubah `assigned_vehicle_id` milik user di database.

---

### 14. SEC-14 — File Upload Disimpan ke Disk Publik Tanpa Pembatasan Eksekusi Web Server & Meninggalkan File Yatim
- **Klasifikasi:** Confirmed Vulnerability (Defense-in-Depth)
- **Severity:** **Low**
- **File & Nomor Baris:**
  - `app/Http/Controllers/ChecklistController.php:111, 139, 211, 238`
  - `app/Http/Controllers/DriverAppController.php:304, 331, 439, 465`
  - `app/Http/Controllers/PengadaanController.php:328, 343`
  - `app/Http/Controllers/UserController.php:122`
- **Root Cause:**
  1. Validasi upload foto checklist di `ChecklistController` & `DriverAppController` hanya memakai `'nullable|image|max:3072'`/`4096` tanpa menyebutkan eksplisit `mimes:jpeg,png,jpg,webp` seperti pada `UserController` dan `SettingController`.
  2. Seluruh upload (termasuk `pdf` pada `bukti_pembayaran`) disimpan ke disk `public` (`storage/app/public`, dapat diakses langsung lewat `/storage/...` tanpa autentikasi!). Tidak ada `.htaccess` di `storage/app/public` yang mematikan script handler (`php_flag engine off` / `RemoveHandler`) ataupun header `X-Content-Type-Options: nosniff` dari server upload.
  3. Pada `ChecklistController@update` dan `DriverAppController@updateK3Report`, foto dokumentasi lama tidak dihapus ketika diganti; dan di `UserController@destroy`, file avatar tidak ikut dihapus.
- **Evidence:**
  ```php
  // app/Http/Controllers/ChecklistController.php:111
  'foto.*' => 'nullable|image|max:3072',
  // app/Http/Controllers/PengadaanController.php:328, 343
  'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
  $buktiPath = $request->file('bukti_pembayaran')->store('purchases', 'public');
  ```
- **Potential Impact:**
  File nota/bukti pembayaran (`purchases/*.pdf`) dapat diakses langsung melalui URL `/storage/purchases/...` tanpa login jika URL/nama file hash diketahui, dan dokumen PDF berpotensi dirender inline oleh browser.
- **Recommended Fix:**
  1. Untuk dokumen sensitif seperti `bukti_pembayaran` Pengadaan, simpan ke disk `local` (`storage/app/private`) dan sajikan melalui endpoint controller terautentikasi (`role:pengadaan|admin`) dengan header `Content-Disposition: inline/attachment` dan `X-Content-Type-Options: nosniff`.
  2. Tambahkan `mimes:jpeg,png,jpg,webp` pada validasi foto checklist, serta bersihkan file lama saat update/delete.
  3. Tambahkan `.htaccess` defensif di `storage/app/public` untuk menonaktifkan eksekusi CGI/PHP sebagai pertahanan berlapis.
- **Safe Verification Method:**
  Periksa folder `storage/app/public` — saat ini hanya terdapat `.gitignore` dan gambar `avatars`/`checklists`, tanpa `.htaccess` pengaman eksekusi.
- **Regression Test:**
  Feature test menguji upload file `.php` atau `.svg` berekstensi palsu ditolak validasi (`422`), dan route download bukti pembelian mengembalikan `403`/`302` untuk user tidak login.

---

## Bagian II — Theoretical Risks

### 1. THEO-01 — Session Cookie Tanpa Flag `secure` & Enkripsi Session Tidak Aktif
- **Klasifikasi:** Theoretical Risk
- **Severity:** **Medium** *(saat deployment produksi HTTPS)*
- **File & Nomor Baris:**
  - `.env:30-34`
  - `config/session.php:50` (`'encrypt' => env('SESSION_ENCRYPT', false)`)
  - `config/session.php:172` (`'secure' => env('SESSION_SECURE_COOKIE')`)
- **Alasan Klasifikasi:**
  Di lingkungan localhost HTTP (`127.0.0.1` / `http://192.168.1.7:8000`), `SESSION_SECURE_COOKIE` memang tidak dapat bernilai `true` karena browser akan menolak menyimpan cookie di atas HTTP biasa. Namun bila konfigurasi ini terbawa ke produksi tanpa disetel ulang, risiko terbuka.
- **Potential Impact:**
  Jika dideploy pada HTTPS tanpa `SESSION_SECURE_COOKIE=true`, cookie session dapat terkirim lewat koneksi HTTP polos (MITM / SSL stripping). Jika `SESSION_ENCRYPT=false`, payload data session di tabel `sessions` tersimpan dalam format base64-encoded serialize biasa.
- **Recommended Fix:**
  Setel `SESSION_SECURE_COOKIE=true` dan `SESSION_ENCRYPT=true` pada konfigurasi `.env.production` saat deployment HTTPS.
- **Safe Verification Method:**
  Periksa nilai `config('session.secure')` dan `config('session.encrypt')`.

---

### 2. THEO-02 — Potensi Race Condition pada Persetujuan & Penambahan/Pengurangan Stok Sparepart
- **Klasifikasi:** Theoretical Risk
- **Severity:** **Medium**
- **File & Nomor Baris:**
  - `app/Http/Controllers/PengadaanController.php:79-97` (`approveRequest`)
  - `app/Http/Controllers/SparepartRequestController.php:154-175` (`review`)
  - `app/Http/Controllers/MekanikController.php:409-415` (`storeRepair`)
- **Alasan Klasifikasi:**
  Eksploitasi memerlukan dua atau lebih request POST/PATCH dikirim secara paralel dalam hitungan milidetik (*TOCTOU / Time-of-Check to Time-of-Use*), tidak terpicu pada penggunaan tunggal normal.
- **Potential Impact:**
  Di `PengadaanController@approveRequest`, pengecekan `if (! $sparepartRequest->isPending())` dilakukan di luar `DB::transaction` dan tanpa `lockForUpdate()`. Dua klik/request bersamaan berpotensi menyebabkan `$sparepartRequest->sparepart->increment('stok', ...)` tereksekusi dua kali.
- **Recommended Fix:**
  Bungkus proses approve/reject dan perubahan stok di dalam `DB::transaction()` menggunakan `SparepartRequest::whereKey($id)->lockForUpdate()->first()`.
- **Safe Verification Method:**
  Review blok `approveRequest()` di `PengadaanController.php:79-97` untuk memastikan ada tidaknya `DB::transaction` dan `lockForUpdate()`.

---

### 3. THEO-03 — Penggunaan `addslashes()` pada Handler JS Inline & Atribut `onclick`
- **Klasifikasi:** Theoretical Risk
- **Severity:** **Low**
- **File & Nomor Baris:**
  - `resources/views/mekanik/repairs.blade.php:228-232, 241`
  - `resources/views/users/index.blade.php:114-117, 127, 521-524`
  - `resources/views/vehicles/index.blade.php:263, 801-808`
  - `resources/views/inspection-items/index.blade.php:183, 190, 254`
  - `resources/views/driver/k3-items.blade.php:274`
- **Alasan Klasifikasi:**
  Karena `{{ addslashes($val) }}` di Blade otomatis melewati `htmlspecialchars(..., ENT_QUOTES)`, karakter kutip tunggal (`'`) berubah menjadi `\&#039;` yang aman dari breakout tag/atribut HTML dan umumnya aman dari breakout JS string di browser modern. Namun pola ini rapuh (*anti-pattern*): karakter newline (`\r`/`\n`) atau karakter Unicode separator pada input multiline (`deskripsi_masalah`, `solusi`, `criteria`) tidak di-escape oleh `addslashes()`.
- **Potential Impact:**
  Input multiline yang berisi baris baru di `deskripsi_masalah` pada `mekanik/repairs.blade.php:231` akan merusak sintaks JS pada tombol Edit (`SyntaxError: Invalid or unexpected token`), dan berpotensi menimbulkan celah jika konteks berubah.
- **Recommended Fix:**
  Gantikan seluruh pola `'{{ addslashes(...) }}'` pada atribut `onclick` atau inline `<script>` dengan atribut HTML `data-*` (seperti yang sudah diterapkan di `vehicles/index.blade.php` tombol edit dan `sop/index.blade.php`) atau gunakan directive `@js($value)`.
- **Safe Verification Method:**
  Isi field `deskripsi_masalah` atau `solusi` dengan teks dua baris (mengandung Enter/newline), lalu periksa Console browser saat membuka `/mekanik/repairs` dan klik tombol Edit.

---

### 4. THEO-04 — Ketergantungan Layanan Eksternal untuk Avatar Fallback & Generator QR Code
- **Klasifikasi:** Theoretical Risk
- **Severity:** **Low**
- **File & Nomor Baris:**
  - `app/Models/User.php:31` (`https://ui-avatars.com/api/?name=...`)
  - `resources/views/vehicles/index.blade.php:742` (`https://api.qrserver.com/v1/create-qr-code/...`)
  - `resources/views/vehicles/qr-print.blade.php:410` (`https://api.qrserver.com/v1/create-qr-code/...`)
- **Alasan Klasifikasi:**
  Tidak memungkinkan eksekusi kode langsung di server aplikasi, namun bergantung pada ketersediaan pihak ketiga dan membocorkan metadata URL / nama karyawan.
- **Potential Impact:**
  Nama pengguna (`User.php:31`) dikirim sebagai query parameter ke `ui-avatars.com` setiap halaman dimuat tanpa avatar lokal, dan URL internal aplikasi (`url('/m/qr/TOYACHECK')`) dikirim ke `api.qrserver.com`.
- **Recommended Fix:**
  Gunakan generator SVG lokal (misal package PHP `simplesoftwareio/simple-qrcode` atau generator inisial SVG lokal) agar tidak bergantung pada jaringan eksternal.

---

### 5. THEO-05 — Evaluasi Penggunaan Query Mentah (`*Raw`) untuk SQL Injection
- **Klasifikasi:** Informational (Verified Safe)
- **Severity:** **Informational**
- **File & Nomor Baris:**
  - `app/Http/Controllers/DashboardController.php:78, 114`
  - `app/Http/Controllers/MekanikController.php:117, 263`
  - `app/Http/Controllers/PengadaanController.php:42`
  - `app/Http/Controllers/SopController.php:23`
  - `app/Models/Sop.php:56`
- **Hasil Verifikasi:**
  Seluruh 7 pemanggilan `selectRaw()` dan `orderByRaw()` di aplikasi hanya menggunakan string konstan statis tanpa variabel pengguna. Tidak ada `DB::unprepared()`, `whereRaw()`, maupun penggabungan variabel ke SQL query mentah di controller manapun. Area SQL Injection dinyatakan **bersih**.

---

## Rencana Remediasi yang Direkomendasikan (Prioritas Eksekusi)

Jika Anda menyetujui rencana remediasi berikut, perbaikan disarankan dieksekusi sesuai urutan prioritas di bawah ini:

1. **Prioritas 1 (Segera — High Severity):**
   - **[SEC-01, SEC-02, SEC-03]** Sanitasi DOM sinks pada `scripts.blade.php` (`showToast`), `pengadaan/vendors.blade.php` (Leaflet popup), `mekanik/repairs-create.blade.php`, dan `pengadaan/reports-create.blade.php` menggunakan `textContent` / DOM construction aman.
   - **[SEC-05]** Wajibkan verifikasi `current_password` pada `SettingController@updateProfile` apabila password diubah (atau hapus perubahan password dari endpoint profil).
   - **[SEC-06]** Terapkan Policy / validasi ownership & role pada `DriverAppController@historyDetail`, `DriverAppController@exportVehiclePdf`, `DriverAppController@editK3Report`/`updateK3Report`/`destroyK3Report`, serta `ChecklistController@index`/`show`.
   - **[SEC-04]** Terapkan `RateLimiter` dan middleware `throttle` pada `POST /login` dan `POST /m/login`, serta pindahkan `POST /m/login` ke grup `guest`.

2. **Prioritas 2 (Menengah — Medium Severity):**
   - **[SEC-07]** Validasi kecocokan role terhadap `checklist_type` pada `ChecklistController@store` serta tambahkan `exists:roles,name` pada `UserController`.
   - **[SEC-08]** Tambahkan helper sanitasi formula CSV (`=`, `+`, `-`, `@`, `\t`, `\r`) pada seluruh fitur export CSV.
   - **[SEC-10]** Jalankan pembaruan minor Composer (`laravel/framework`, `symfony/*`) dan verifikasi ulang dengan `composer audit`.
   - **[SEC-12]** Tambahkan middleware HTTP Security Headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) dan SRI hash pada tag `<script>`/`<link>` CDN.
   - **[THEO-02]** Tambahkan `DB::transaction` + `lockForUpdate()` pada `approveRequest()`.

3. **Prioritas 3 (Hardening & Pra-Deployment):**
   - **[SEC-09, SEC-11, THEO-01]** Konfigurasi user MySQL *least privilege* khusus database `toyacheck`, matikan `APP_DEBUG`, aktifkan log harian (`daily`), serta aktifkan `SESSION_SECURE_COOKIE=true` & `SESSION_ENCRYPT=true` pada environment produksi HTTPS.
   - **[SEC-13, SEC-14, THEO-03]** Tingkatkan kebijakan panjang & kompleksitas password (`Password::min(8)->letters()->numbers()`), pindahkan pembaruan `assigned_vehicle_id` dari HTTP `GET` ke `POST`, pindahkan file bukti pembayaran sensitif ke disk `private`, dan migrasikan `addslashes()` inline handler ke `data-*` / `@js()`.
