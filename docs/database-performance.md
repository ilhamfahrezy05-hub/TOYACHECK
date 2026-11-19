# Laporan Audit Performa Database MySQL & Laravel 13 — TOYACHECK

- **Tanggal Audit:** 20 November 2026
- **Auditor:** Senior MySQL Database Performance Engineer
- **Target Aplikasi:** TOYACHECK — Sistem Informasi & Inspeksi Armada Distribusi (PT. Toyamilindo)
- **Lingkungan Pengukuran:** MySQL `8.4.3` (InnoDB), Laravel Framework `v13.38.0`, PHP `8.3.16`, Localhost Development
- **Batasan:** Pengujian dilakukan secara non-destruktif dan *read-only* (menggunakan `DB::enableQueryLog()`, `information_schema`, dan `EXPLAIN`) tanpa mengubah struktur ataupun isi data database.

---

## Ringkasan Eksekutif & Profil Database Saat Ini

Berdasarkan inspeksi langsung ke skema MySQL `toyacheck`, seluruh tabel menggunakan engine **InnoDB** dengan integritas referensial (*Foreign Key Constraints*) yang sudah terdefinisi dengan baik. Pada fase pengembangan lokal saat ini, ukuran data tiap tabel masih sangat kecil (2–37 baris per tabel, masing-masing 1 page data InnoDB = `16 KB`).

### Inventaris Tabel & Volume Data Saat Ini

| Tabel | Kategori | Perkiraan Rows | Data Size | Index Size | Indeks Tersedia |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `checklists` | Transaksional (Tumbuh Cepat) | 2 | 16 KB | 48 KB | `PRIMARY`, `vehicle_id`, `driver_id`, `user_id` |
| `checklist_items` | Transaksional (Tumbuh Sangat Cepat) | 29 | 16 KB | 32 KB | `PRIMARY`, `checklist_id`, `inspection_item_id` |
| `maintenances` | Transaksional (Tumbuh Sedang-Cepat) | 6 | 16 KB | 48 KB | `PRIMARY`, `vehicle_id`, `mekanik_id`, `checklist_id` |
| `maintenance_details` | Transaksional (Tumbuh Sedang) | 6 | 16 KB | 32 KB | `PRIMARY`, `maintenance_id`, `sparepart_id` |
| `sparepart_requests` | Transaksional (Tumbuh Sedang) | 5 | 16 KB | 64 KB | `PRIMARY`, `sparepart_id`, `maintenance_id`, `requested_by`, `reviewed_by` |
| `purchases` | Transaksional (Tumbuh Sedang) | 4 | 16 KB | 64 KB | `PRIMARY`, `kode_pembelian (U)`, `user_id`, `vendor_id`, `sparepart_request_id` |
| `purchase_items` | Transaksional (Tumbuh Sedang) | 4 | 16 KB | 32 KB | `PRIMARY`, `purchase_id`, `sparepart_id` |
| `spareparts` | Master / Inventaris | 33 | 16 KB | 16 KB | `PRIMARY`, `kode_sparepart (U)` |
| `inspection_items` | Master Parameter | 35 | 16 KB | 32 KB | `PRIMARY`, `parent_id`, `user_edit` |
| `vehicles` | Master Armada | 8 | 16 KB | 16 KB | `PRIMARY`, `plat_nomor (U)` |
| `vendors` | Master Vendor | 9 | 16 KB | 0 KB | `PRIMARY` |
| `users` | Master Pengguna | 5 | 16 KB | 48 KB | `PRIMARY`, `username (U)`, `employee_id (U)`, `assigned_vehicle_id` |
| `sops` | Master Dokumen | 37 | 16 KB | 16 KB | `PRIMARY`, `role_target` |

### Pengukuran Jumlah Query Aktual per Request (`DB::enableQueryLog()`)

| Skenario / Halaman | Total Query | Waktu Render | Pola Query Berulang / Anomali |
| :--- | :---: | :---: | :--- |
| **Admin Dashboard** | **43** | 89.65 ms | Memuat data Mekanik & Pengadaan sekaligus; 4x `COUNT(*)` vehicles, 3x `COUNT(*)` sparepart_requests, `Maintenance::all()` |
| **Mekanik Dashboard** | **40** | 32.65 ms | Memuat data Admin & Pengadaan yang tidak ditampilkan; N+1 di topbar notif |
| **Pengadaan Dashboard** | **40** | 43.70 ms | Memuat data Admin & Mekanik yang tidak ditampilkan; 3x `COUNT(*)` vehicles |
| **Admin SparepartRequests Index** | **32** | 35.96 ms | 5x `COUNT(*)` `sparepart_requests` + `$purchases` tanpa pagination + eager-load duplikat |
| **Admin Checklists Index** | **24** | 25.65 ms | 5x `COUNT(*)` `checklists` + query duplikat `inspection_items` |
| **Admin Maintenances Index** | **24** | 288.31 ms | 4x `COUNT(*)` `maintenances` + eager-load semua dropdown penuh |
| **Mekanik Repairs** | **18** | 208.08 ms | 2x `COUNT(*)` vehicles; query `DATE_FORMAT()` full scan |
| **Admin History ExportPdf** | **13** | 164.28 ms | Query `filteredQuery()->get()` dobel sebelum `buildMonthlyVehicleSheets()` |
| **Driver Index** | **3** | 10.99 ms | Query `whereDate('tanggal', $today)` non-sargable |

---

## Ringkasan Evaluasi 10 Area Audit

| No | Area Audit | Temuan Utama |
| :---: | :--- | :--- |
| 1 | **Eloquent Queries** | `DashboardController::index()` menjalankan query untuk 3 role sekaligus; `ChecklistHistoryController::exportPdf()` mengeksekusi query ganda; `DriverAppController` memakai `whereDate()` non-sargable. |
| 2 | **N+1 Queries** | `User::getNotificationsList()` mengalami lazy-loading `$lastChecklist?->driver` dan bug `limit(1)` di `with(['checklists' => ...->limit(1)])`; `VehicleController::destroy()` menjalankan `delete()` di dalam loop. |
| 3 | **Eager Loading** | Sebagian besar controller list sudah memakai `with()`, tetapi `User::alertCounts()` / `preference()` memicu lazy load `settings` bila relasi belum di-load, dan `User::getNotificationsList()` tidak eager-load `checklists.driver`. |
| 4 | **Query Filtering & Sorting** | Banyak filter tanggal menggunakan fungsi `DATE()`, `YEAR()`, `MONTH()`, `DATE_FORMAT()` pada kolom terindeks sehingga mematikan index range scan; pencarian teks memakai leading wildcard `LIKE '%...%'`. |
| 5 | **Pagination** | `PengadaanController` (`requests`, `reports`, `approvals`), `MekanikController::approvals()`, dan tabel realisasi `$purchases` di `SparepartRequestController::index()` menggunakan `->get()` tanpa pagination. |
| 6 | **Database Indexes** | Tabel transaksional (`checklists`, `maintenances`, `sparepart_requests`, `purchases`) belum memiliki non-FK atau *composite index* untuk pola filter tanggal & status, menyebabkan `EXPLAIN type = ALL` + `Using filesort`. |
| 7 | **Foreign Key Relationships** | Semua FK terpasang rapi dengan aturan `CASCADE` / `SET NULL` / `RESTRICT` yang masuk akal. |
| 8 | **Unnecessary SELECT Columns** | `MekanikController::calculateRepairTrendsFromDb()` memakai `Maintenance::all()` (mengambil seluruh kolom `TEXT`); `ChecklistHistoryController::exportPdf()` memuat seluruh relasi lalu hanya mengambil ID kendaraan. |
| 9 | **Aggregation Queries** | 3–5 query `COUNT(*)` terpisah per controller pada kolom `status` untuk kartu statistik; tren perbaikan dihitung di PHP alih-alih agregasi SQL. |
| 10 | **Expensive Queries** | `Maintenance::all()` tanpa batas memori/waktu saat data besar, dan `EXPLAIN` menunjukkan `Using filesort` / `Using temporary` pada hampir seluruh query sorting utama. |

---

## Detail Temuan Audit Performa (Diurutkan Berdasarkan Prioritas & Dampak)

---

### PERF-01 — Full Table Load (`Maintenance::all()`) pada Kalkulasi Tren Perbaikan yang Dipanggil di Setiap Load Dashboard

- **Prioritas:** **P1 — Tinggi**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/MekanikController.php:26-90` (`calculateRepairTrendsFromDb()`)
  - `app/Http/Controllers/DashboardController.php:95`
  - `app/Http/Controllers/MekanikController.php:130` (`inventory()`)
- **Current Behavior:**
  ```php
  // app/Http/Controllers/MekanikController.php:28
  $allMaintenances = Maintenance::all();
  ```
  Setiap kali Admin, Mekanik, atau Pengadaan membuka `/dashboard` (dan Mekanik membuka `/mekanik/inventory`), aplikasi mengeksekusi `SELECT * FROM maintenances` tanpa klausa `WHERE`, tanpa `LIMIT`, dan tanpa pemilihan kolom (`SELECT *` termasuk kolom `TEXT` seperti `deskripsi_masalah`, `solusi`, `catatan`), lalu melakukan iterasi PHP dengan `str_contains()` pada setiap baris.
- **Performance Concern:**
  Kompleksitas memori dan waktu tumbuh linear $O(N)$ terhadap total histori perbaikan seumur hidup perusahaan. Jika data `maintenances` mencapai 50.000 baris, PHP harus mengalokasikan puluhan hingga ratusan megabyte RAM untuk menghidrasi 50.000 objek Eloquent `Maintenance` pada setiap pembukaan dashboard oleh user manapun (termasuk Admin & Pengadaan yang bahkan tidak menampilkan widget tren tersebut).
- **Recommended Optimization:**
  1. Hitung tren perbaikan langsung di MySQL menggunakan query agregasi (`SELECT SUM(CASE WHEN ... THEN 1 ELSE 0 END) AS ... , COUNT(*) AS total FROM maintenances`) atau batasi rentang waktu tren (misal 90 hari / bulan berjalan) agar tidak memindai tabel historis penuh.
  2. Cache hasil agregasi tren selama 5–15 menit menggunakan `Cache::remember('repair_trends_summary', 600, ...)` dan invalidate saat `Maintenance` baru disimpan/diubah.
- **Expected Benefit:**
  Penggunaan memori query turun dari $O(N)$ objek Eloquent menjadi 1 baris hasil agregasi (konstan $O(1)$ di PHP) dan mengurangi latensi dashboard secara drastis saat tabel `maintenances` bertumbuh.
- **Possible Trade-offs:**
  Jika menggunakan `Cache::remember`, data tren di dashboard dapat tertinggal beberapa menit kecuali invalidasi cache ditambahkan pada observer/controller create & update `Maintenance`.
- **Verification Procedure:**
  Bandingkan jumlah baris yang terhidrasi (Eloquent models) dan *peak memory* (`memory_get_peak_usage`) sebelum dan sesudah optimasi pada `DashboardController::index()`.

---

### PERF-02 — `DashboardController::index()` Mengeksekusi Query untuk Semua 3 Role Tanpa Percabangan

- **Prioritas:** **P1 — Tinggi**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/DashboardController.php:39-149`
- **Current Behavior:**
  Saat Admin, Mekanik, atau Pengadaan mengakses `/dashboard`, `DashboardController::index()` mengeksekusi seluruh blok query milik **Admin** (4x count vehicles, `$kendalaFisik`, `$kendalaK3`, `$recentInspections`), **Mekanik** (`$persediaan`, `$criticalCount`, `$rekapArmada`, `$trends` / `Maintenance::all()`), dan **Pengadaan** (`$procStats`, `$procNotifRequests`, `$procRecentRequests`, `$procRecentPurchases`, `$procVendors`) tanpa memeriksa role pengguna terlebih dahulu.
- **Performance Concern:**
  Hasil profiling aktual menunjukkan **40–43 query** tereksekusi pada setiap kali `/dashboard` dibuka oleh role apa pun:
  - Pengadaan membuka dashboard -> tetap mengeksekusi query milik Admin & Mekanik.
  - Mekanik membuka dashboard -> tetap mengeksekusi 15+ query milik Admin & Pengadaan yang datanya dibuang dan tidak pernah dirender di Blade.
- **Recommended Optimization:**
  Lakukan percabangan role (`$user->hasRole('mekanik')`, `$user->hasRole('pengadaan')`, `$user->hasRole('admin')`) di `DashboardController::index()` sehingga hanya query yang dibutuhkan oleh view role tersebut yang dijalankan (dengan tetap menyertakan fallback array kosong untuk variabel yang tidak terpakai jika masih di-share satu view, atau memecah partial/method private per role).
- **Expected Benefit:**
  Pemangkasan jumlah query per pembukaan dashboard hingga **50–65%** (dari ~40–43 query menjadi ~10–18 query).
- **Possible Trade-offs:**
  Perlu memastikan view `resources/views/dashboard.blade.php` tidak mengakses variabel milik role lain di luar blok `@if(auth()->user()->hasRole(...))`.
- **Verification Procedure:**
  Jalankan `DB::enableQueryLog()` untuk masing-masing role pada `DashboardController::index()` dan pastikan jumlah query untuk Mekanik/Pengadaan/Admin turun di bawah 15–20 query tanpa query lintas-role.

---

### PERF-03 — N+1 Lazy Loading, Bug `with(...->limit(1))`, & Query Redundan di Topbar / Sidebar Global

- **Prioritas:** **P1 — Tinggi**
- **File & Baris Terdampak:**
  - `app/Models/User.php:115-230` (`alertCounts()` & `getNotificationsList()`)
  - `resources/views/layouts/partials/topbar.blade.php:15-16`
  - `resources/views/layouts/partials/sidebar.blade.php:41, 121, 153`
- **Current Behavior:**
  1. Pada `User::getNotificationsList()` (`app/Models/User.php:143-152`):
     ```php
     $brokenVehicles = Vehicle::where('status', Vehicle::STATUS_RUSAK)
         ->with(['checklists' => fn ($q) => $q->latest('tanggal')->limit(1)])
         ->latest('updated_at')->take(5)->get();
     foreach ($brokenVehicles as $v) {
         $lastChecklist = $v->checklists->first();
         $driverName = $lastChecklist?->driver?->name ?? 'Petugas'; // Lazy-load N+1!
     ```
     Selain memicu **N+1 lazy load** untuk `$lastChecklist?->driver`, penggunaan `->limit(1)` di dalam eager loading `hasMany` (`checklists`) hanya mengambil **1 baris total** dari seluruh 5 kendaraan (sehingga unit rusak ke-2 s/d ke-5 tidak memperoleh checklist terakhirnya).
  2. Pada **setiap** request halaman Web Desktop, `sidebar.blade.php` mengeksekusi query `SparepartRequest::where('status', 'pending')->count()` yang **sama persis** dengan query di `User::alertCounts()` (`topbar.blade.php:15`), sehingga terjadi duplikasi query berulang hingga **3–5 kali** pada halaman seperti `SparepartRequests Index`.
- **Performance Concern:**
  Eksekusi terjadi secara global di setiap halaman desktop (Admin, Mekanik, Pengadaan). Pada halaman dengan view partial berulang, profiler mencatat `select count(*) from sparepart_requests where status = ?` tereksekusi **5x** dan `select * from users where id in (...)` tereksekusi berulang.
- **Recommended Optimization:**
  1. Di Laravel 13, ganti `with(['checklists' => ...->limit(1)])` dengan relasi **`hasOne(...)->latestOfMany('tanggal')`** pada model `Vehicle` (`latestChecklist()`), dan eager load `'latestChecklist.driver:id,name'` untuk mengeliminasi bug `limit(1)` sekaligus menghilangkan N+1 query `driver`.
  2. Eager-load atau cache singkat `settings` / ringkasan badge notifikasi per request (misalnya menggunakan `once()` helper Laravel atau `View::composer` bersama untuk `topbar` & `sidebar` sehingga `COUNT(*)` hanya jalan 1x per request).
- **Expected Benefit:**
  Menghilangkan bug logika notifikasi unit rusak ke-2..5, menghilangkan N+1 query `users` pada topbar, dan mengurangi 2–4 query redundan di setiap perpindahan halaman.
- **Possible Trade-offs:**
  Penggunaan `View::composer` / `once()` sangat minim risiko; `latestOfMany()` menghasilkan subquery terindeks yang efisien selama terdapat index pada `(vehicle_id, tanggal)`.
- **Verification Procedure:**
  Buat 3 unit armada berstatus `rusak` dengan checklist oleh 3 driver berbeda, nyalakan `DB::enableQueryLog()`, render dashboard, dan pastikan ketiga notifikasi menampilkan nama driver yang benar tanpa query tambahan di dalam `foreach`.

---

### PERF-04 — Non-Sargable Date Predicates (`whereDate`, `whereYear`, `whereMonth`, `DATE_FORMAT`)

- **Prioritas:** **P1 — Tinggi (Untuk Skala Produksi)**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/ChecklistHistoryController.php:409-410` (`whereDate('tanggal', '>=', $v)` / `<= $v`)
  - `app/Http/Controllers/DriverAppController.php:112, 146, 152, 639-641` (`whereDate`, `whereMonth`, `whereYear`)
  - `app/Http/Controllers/DashboardController.php:67, 110` (`whereDate`, `whereYear`, `whereMonth`)
  - `app/Http/Controllers/MekanikController.php:229-230, 243-247, 263` (`whereYear`, `whereMonth`, `DATE_FORMAT`)
  - `app/Http/Controllers/PengadaanController.php:268-269, 276-277, 449-450, 478-479, 512-513`
  - `app/Models/User.php:201` (`whereDate('tanggal', now()->toDateString())`)
- **Current Behavior:**
  Mayoritas query filter tanggal membungkus kolom `DATE`/`DATETIME` dengan fungsi MySQL:
  ```sql
  SELECT * FROM checklists WHERE DATE(tanggal) = '2026-11-19' ORDER BY tanggal DESC, id DESC LIMIT 10
  SELECT * FROM purchases WHERE DATE(tanggal_pembelian) >= '2026-11-01' ...
  ```
- **Performance Concern:**
  Hasil `EXPLAIN` di MySQL `8.4.3`:
  ```text
  Q2: WHERE DATE(tanggal) = '2026-11-19'
  -> type=ALL | possible_keys=NULL | key=NULL | Extra=Using where; Using filesort
  ```
  Membungkus kolom dengan `DATE()`, `YEAR()`, atau `MONTH()` membuat query bersifat **non-sargable** — MySQL optimizer tidak dapat menggunakan B-Tree index pada kolom `tanggal`, `tanggal_maintenance`, `tanggal_pembelian`, atau `created_at` (walaupun index sudah dibuat), sehingga selalu memaksa **Full Table Scan** ketika data membesar.
- **Recommended Optimization:**
  Ubah seluruh `whereDate`, `whereYear`, `whereMonth` menjadi rentang nilai eksplisit (*range query*):
  ```php
  // Pengganti whereDate('tanggal', $today)
  ->whereBetween('tanggal', [Carbon::parse($today)->startOfDay(), Carbon::parse($today)->endOfDay()])

  // Pengganti whereYear + whereMonth
  ->whereBetween('tanggal_maintenance', [$monthStart->startOfMonth(), $monthStart->endOfMonth()])
  ```
  *(Catatan: pada kolom bertipe `DATE` seperti `purchases.tanggal_pembelian`, cukup gunakan `>= $startDate` tanpa `whereDate()`, seperti yang ditunjukkan query `Q12` di `EXPLAIN`).*
- **Expected Benefit:**
  Mengaktifkan **Index Range Scan (`type = range`)** begitu index tanggal/composite index ditambahkan, menurunkan I/O disk pada tabel historis ratusan ribu baris dari detik ke milidetik.
- **Possible Trade-offs:**
  Memerlukan kehati-hatian batas jam `00:00:00` s/d `23:59:59` untuk kolom `DATETIME`/`TIMESTAMP` (aman dengan `startOfDay()` / `endOfDay()`).
- **Verification Procedure:**
  Jalankan `EXPLAIN` pada query sebelum & sesudah penambahan index untuk memastikan `possible_keys` terisi dan `type = range` bukan `ALL`.

---

### PERF-05 — Ketiadaan Composite Index pada Tabel Transaksional yang Tumbuh Cepat (`EXPLAIN` Evidence)

- **Prioritas:** **P2 — Menengah (Kritis seiring pertumbuhan data >1.000 baris)**
- **File & Skema Terdampak:**
  - Tabel `checklists`, `maintenances`, `sparepart_requests`, `purchases`
  - Query di `DashboardController`, `ChecklistController`, `ChecklistHistoryController`, `DriverAppController`, `PengadaanController`
- **Current Behavior & `EXPLAIN` Evidence:**
  Berdasarkan inspeksi `information_schema.STATISTICS`, ke-4 tabel transaksional di atas **hanya memiliki index Primary Key dan index tunggal bawaan Foreign Key**. Akibatnya, `EXPLAIN` di MySQL `8.4.3` menunjukkan **Full Table Scan (`type = ALL`)** dan **`Using filesort`** pada query-query utama:
  - **Q4 (Dashboard Kendala Fisik & K3):** `WHERE checklist_type = 'fisik' AND status = 'kendala' ORDER BY tanggal DESC LIMIT 5` -> `type=ALL`, `key=NULL`, `Using filesort`.
  - **Q5 (History Bulanan per Armada):** `WHERE vehicle_id IN (...) AND tanggal BETWEEN ... ORDER BY tanggal ASC, id ASC` -> hanya memakai FK `checklists_vehicle_id_foreign`, tetap mengalami `Using filesort`.
  - **Q6 (Riwayat Driver Mobile):** `WHERE driver_id = ? AND checklist_type = 'fisik' ORDER BY tanggal DESC, id DESC LIMIT 15` -> hanya memakai `checklists_driver_id_foreign`, tetap mengalami `Using filesort`.
  - **Q9 (Antrian Permintaan Pengadaan):** `ORDER BY CASE WHEN status = 'pending' ... created_at DESC` -> `type=ALL`, `key=NULL`, `Using filesort`.
  - **Q12 (Laporan Pembelian):** `WHERE tanggal_pembelian >= ? ORDER BY tanggal_pembelian DESC, id DESC` -> `type=ALL`, `key=NULL`, `Using filesort`.

- **Recommended Optimization (Hanya Index yang Terbukti Dibutuhkan dengan Selektivitas Tepat):**
  1. **`checklists` — Composite Index:**
     - `idx_checklists_type_status_tanggal (checklist_type, status, tanggal)`: Membantu query widget Dashboard (`checklist_type = ? AND status = ? ORDER BY tanggal DESC`) sekaligus query `Checklist::where('status', 'perbaikan')` & filter `ChecklistController::index`. Kolom selektif/equality (`checklist_type`, `status`) diletakkan sebelum kolom range/sort (`tanggal`) sehingga menghilangkan `Using filesort`.
     - `idx_checklists_vehicle_tanggal (vehicle_id, tanggal)`: Membantu `ChecklistHistoryController::buildMonthlyVehicleSheets` (range tanggal per `vehicle_id`) dan relasi `latestOfMany('tanggal')` pada `Vehicle` tanpa *filesort*.
     - `idx_checklists_driver_type_tanggal (driver_id, checklist_type, tanggal)`: Membantu riwayat & notifikasi Driver di `DriverAppController` tanpa *filesort*.
     - `idx_checklists_tanggal (tanggal)`: Membantu sorting default `/checklists` (`ORDER BY tanggal DESC, id DESC LIMIT 15`) dan filter rentang tanggal.
  2. **`maintenances` — Composite Index:**
     - `idx_maintenances_status_tanggal (status_maintenance, tanggal_maintenance)`: Membantu filter status pada `MaintenanceController::index` dan `MekanikController::exportPdfRepairs`.
     - `idx_maintenances_tanggal (tanggal_maintenance)`: Membantu `ORDER BY tanggal_maintenance DESC LIMIT 15` dan agregasi rentang bulan.
  3. **`sparepart_requests` — Composite Index:**
     - `idx_sr_status_created (status, created_at)`: Membantu sidebar badge `where('status', 'pending')->count()`, filter status di `SparepartRequestController` & `PengadaanController::approvals` (`where('status', 'approved')` + range `created_at`), sekaligus `ORDER BY created_at DESC`.
  4. **`purchases` — Index Tanggal:**
     - `idx_purchases_tanggal (tanggal_pembelian)`: Membantu filter tanggal dan sorting laporan di `PengadaanController::reports()` dan `SparepartRequestController::index()`.

  > **Catatan Penting (Mengapa TIDAK Disarankan Menambah Index pada Tabel Master Kecil):**  
  > Pada tabel **`vehicles` (8 baris)**, **`vendors` (9 baris)**, **`spareparts` (33 baris)**, dan **`inspection_items` (35 baris)**, seluruh baris muat dalam 1 InnoDB page (`16 KB`) dengan kardinalitas status rendah (hanya 2–4 nilai unik). Penambahan index pada kolom `vehicles.status` atau `spareparts.tipe_item` tidak akan mempercepat baca secara signifikan dan justru menambah *write overhead* saat status kendaraan/stok sparepart sering di-update. Index tambahan pada tabel master tersebut **tidak direkomendasikan** untuk skala saat ini.

- **Expected Benefit:**
  Menghilangkan *full table scan* dan `Using filesort` pada seluruh query daftar & dashboard utama untuk tabel transaksional saat volume data mencapai ribuan hingga ratusan ribu baris.
- **Possible Trade-offs:**
  Setiap tambahan index menambah sedikit ukuran storage (`~15-25%` ukuran tabel) dan overhead minimal pada operasi `INSERT`/`UPDATE`/`DELETE` tabel transaksional (pada sistem dengan frekuensi tulis relatif rendah seperti checklist harian armada, trade-off ini sangat layak dan aman).
- **Verification Procedure:**
  Setelah migrasi index (bila disetujui), jalankan ulang `EXPLAIN` untuk query Q3–Q6, Q9, Q12 dan verifikasi perubahan `type=ALL` menjadi `type=ref` / `range` serta hilangnya `Using filesort`.

---

### PERF-06 — Query Tanpa Pagination (`->get()`) pada Daftar Transaksional di Pengadaan, Mekanik, & Admin

- **Prioritas:** **P2 — Menengah**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/PengadaanController.php:45` (`requests()` -> `->get()`)
  - `app/Http/Controllers/PengadaanController.php:272` (`reports()` -> `->get()`)
  - `app/Http/Controllers/PengadaanController.php:482` (`approvals()` -> `->get()`)
  - `app/Http/Controllers/MekanikController.php:659` (`approvals()` -> `->get()`)
  - `app/Http/Controllers/SparepartRequestController.php:43` (`$purchases = Purchase::with(...)->get()`)
- **Current Behavior:**
  Halaman daftar permintaan pengadaan, riwayat belanja pembelian, daftar persetujuan, dan tabel realisasi pembelian pada menu Permintaan Admin memanggil `->get()` tanpa `paginate()` maupun `take()`, serta melakukan eager-load 3–4 relasi sekaligus untuk seluruh baris di database.
- **Performance Concern:**
  Ketika jumlah transaksi pembelian (`purchases`) dan pengajuan (`sparepart_requests`) bertumbuh ribuan baris dalam 1–2 tahun operasional, setiap pembukaan halaman akan menarik ribuan baris beserta relasi `items`, `vendor`, `sparepart`, dan `requester` ke memori PHP dan merender ribuan node DOM HTML sekaligus, membuat TTFB dan waktu render browser melambat drastis.
- **Recommended Optimization:**
  Ganti `->get()` menjadi `->paginate($user->perPage())->withQueryString()` pada halaman daftar (`PengadaanController::requests`, `reports`, `approvals`, `MekanikController::approvals`) dan batasi atau paginasi tabel `$purchases` di `SparepartRequestController::index()` (misalnya 15 baris per halaman dengan named page `purchase_page`).
- **Expected Benefit:**
  Ukuran payload query, penggunaan RAM PHP, dan ukuran HTML respons terjaga konstan (hanya 10–15 baris per halaman) berapa pun besar pertumbuhan histori transaksi.
- **Possible Trade-offs:**
  Pada `PengadaanController::requests` (master-detail UI), perlu penyesuaian kecil agar panel detail sebelah kanan tetap berfungsi saat paginasi berpindah halaman.
- **Verification Procedure:**
  Periksa query dengan `DB::enableQueryLog()` untuk memastikan klausa `limit 15 offset ...` muncul di SQL halaman-halaman tersebut.

---

### PERF-07 — Query Ganda di `ChecklistHistoryController::exportPdf()` & Loop Delete pada `VehicleController::destroy()`

- **Prioritas:** **P2 — Menengah**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/ChecklistHistoryController.php:40-58` (`exportPdf()`)
  - `app/Http/Controllers/VehicleController.php:82-97` (`destroy()`)
- **Current Behavior:**
  1. Di `ChecklistHistoryController::exportPdf()`:
     ```php
     $filtered = $this->filteredQuery($request)->get(); // Mengambil vehicle, driver, items lengkap!
     ```
     Namun hasil `$filtered` hanya dipakai untuk `first()?->tanggal` dan `pluck('vehicle_id')`, kemudian `buildMonthlyVehicleSheets()` menjalankan query ulang `Checklist::with(['driver', 'items.inspectionItem'])` untuk bulan yang dipilih!
  2. Di `VehicleController::destroy()` (baris 83-96):
     Mengambil semua `$checklists` dan `$maintenances`, lalu melakukan loop `foreach` untuk memanggil `$chk->items()->delete(); $chk->delete();` dan `$m->details()->delete(); $m->delete();` satu per satu.
- **Performance Concern:**
  1. `exportPdf()` mengeksekusi query berat ganda (memuat seluruh `checklist_items` dua kali untuk semua checklist yang cocok dengan filter) sebelum merender matriks PDF.
  2. `VehicleController::destroy()` menghasilkan pola **N+1 DELETE** jika sebuah armada memiliki ratusan riwayat checklist/maintenance, padahal FK `checklist_items.checklist_id` dan `maintenance_details.maintenance_id` di MySQL sudah disetel **`ON DELETE CASCADE`**.
- **Recommended Optimization:**
  1. Di `ChecklistHistoryController::exportPdf()`, ketika `vehicle_id` tidak diisi, cukup jalankan query ringan `Checklist::query()->...->distinct()->pluck('vehicle_id')` (atau ambil `MIN(tanggal)` / `latest('tanggal')->value('tanggal')` tanpa `with(['vehicle', 'driver', 'items'])`).
  2. Di `VehicleController::destroy()`, manfaatkan `ON DELETE CASCADE` bawaan InnoDB dengan memanggil `Checklist::where('vehicle_id', $vehicle->id)->delete()` dan `Maintenance::where('vehicle_id', $vehicle->id)->delete()` secara massal (atau cukup `whereIn('checklist_id', ...)->delete()` jika butuh logik eksplisit) tanpa loop per item.
- **Expected Benefit:**
  Mengurangi ~50% query & penggunaan memori saat mencetak PDF semua armada, dan menghapus N+1 delete saat menghapus data armada.
- **Possible Trade-offs:**
  Query massal `Model::where(...)->delete()` tidak memicu Eloquent model `deleting` event pada tiap child row (aman karena tidak ada model observer pada `Checklist`, `ChecklistItem`, `Maintenance`, `MaintenanceDetail`).
- **Verification Procedure:**
  Jalankan profiler `DB::enableQueryLog()` saat memanggil `ChecklistHistoryController::exportPdf()` dan verifikasi bahwa `checklist_items` hanya di-query 1 kali (di dalam `buildMonthlyVehicleSheets()`).

---

### PERF-08 — Agregasi `COUNT(*)` Berulang untuk Stat Cards di Setiap Controller Halaman

- **Prioritas:** **P3 — Rendah (Optimalisasi Kebersihan & Penghematan Query)**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/DashboardController.php:40-45` (4x query terpisah pada `vehicles`)
  - `app/Http/Controllers/VehicleController.php:19-25` (5x query pada `vehicles`)
  - `app/Http/Controllers/ChecklistController.php:58-66` (5x query pada `checklists`)
  - `app/Http/Controllers/MaintenanceController.php:45-50` (4x query pada `maintenances`)
  - `app/Http/Controllers/SparepartRequestController.php:45-52` (5x query pada `sparepart_requests`)
  - `app/Http/Controllers/VendorController.php:35-39` (3x query pada `vendors`)
  - `app/Http/Controllers/InspectionItemController.php:36-41` (4x query pada `inspection_items`)
- **Current Behavior:**
  Setiap controller menjalankan 3 hingga 5 query `SELECT COUNT(*)` secara terpisah hanya untuk menghitung jumlah per status pada tabel yang sama.
- **Performance Concern:**
  Menambah 3–4 *round-trip* database yang tidak perlu pada setiap page load. Pada tabel yang lebih besar, memaksa 4–5 kali pemindaian data terpisah.
- **Recommended Optimization:**
  Gabungkan menjadi **1 query tunggal** menggunakan conditional aggregation atau `GROUP BY`:
  ```php
  $counts = Vehicle::selectRaw("
      COUNT(*) as total,
      SUM(status = 'siap_jalan') as siap_jalan,
      SUM(status = 'maintenance') as maintenance,
      SUM(status = 'rusak') as rusak,
      SUM(status = 'nonaktif') as nonaktif
  ")->first();
  ```
- **Expected Benefit:**
  Memangkas 3–4 query menjadi 1 query pada setiap halaman master/operasional (total menghemat ~20+ query di seluruh aplikasi).
- **Possible Trade-offs:**
  Sintaks `SUM(status = '...')` spesifik MySQL/MariaDB; untuk kompatibilitas ANSI SQL lebih luas gunakan `SUM(CASE WHEN status = '...' THEN 1 ELSE 0 END)`.
- **Verification Procedure:**
  Verifikasi dengan query log bahwa 5 baris `COUNT(*)` di `VehicleController`, `ChecklistController`, `MaintenanceController`, dan `SparepartRequestController` berkurang menjadi 1 query agregasi per tabel dan angka statistik tetap identik 100%.

---

### PERF-09 — Pemilihan Kolom Berlebih (`SELECT *`) pada Query Dropdown & Pencarian `LIKE '%...%'`

- **Prioritas:** **P3 — Rendah**
- **File & Baris Terdampak:**
  - `app/Http/Controllers/MaintenanceController.php:53-64`
  - `app/Http/Controllers/SparepartRequestController.php:54-59`
  - `app/Http/Controllers/MekanikController.php:150-155, 259-260, 327-329`
  - `app/Http/Controllers/PengadaanController.php:300-301`
  - Pencarian `LIKE '%{$q}%'` pada `MaintenanceController::exportPdf`, `MekanikController::repairs`, `PengadaanController`, `VendorController`
- **Current Behavior:**
  Query pengisian dropdown `<select>` di modal form mengambil seluruh kolom (`SELECT *`) termasuk kolom `TEXT` (`alasan`, `deskripsi_masalah`, `keterangan`, `alamat`) meskipun di view dropdown hanya memakai `id`, `plat_nomor`, `merk`, `nama_sparepart`, `nama_vendor`, dan `name`.
- **Performance Concern:**
  Pemborosan bandwidth transfer MySQL <-> PHP serta memori hidrasi model apabila tabel katalog sparepart, vendor, atau daftar log perbaikan belum selesai semakin panjang. Sedangkan `LIKE '%keyword%'` (leading wildcard) mencegah penggunaan B-Tree index (meski wajar untuk search bar teks bebas skala UKM/perusahaan menengah).
- **Recommended Optimization:**
  1. Gunakan `->select(['id', 'plat_nomor', 'merk', 'jenis_kendaraan'])` atau `Sparepart::orderBy('nama_sparepart')->get(['id', 'kode_sparepart', 'nama_sparepart', 'stok', 'satuan'])` khusus untuk query dropdown.
  2. Gunakan `Vendor::where('is_active', true)->orderBy('nama_vendor')->get(['id', 'nama_vendor'])`.
  3. Untuk pencarian `plat_nomor` atau `kode_pembelian` yang formatnya memiliki awalan tetap (misal plat `E ...` atau kode `#PR-...`), pertimbangkan mendukung prefix search bila dibutuhkan pada skala besar, atau pertahankan `%...%` jika volume master tetap kecil (<5.000 baris) karena lebih ramah pengguna.
- **Expected Benefit:**
  Mengurangi ukuran payload hasil query dropdown secara signifikan dan mempercepat serialisasi/hidrasi Eloquent.
- **Possible Trade-offs:**
  Jika ada kode view di masa depan yang mengakses kolom baru pada objek dropdown, kolom tersebut harus ditambahkan ke daftar `select([...])`.
- **Verification Procedure:**
  Periksa query log untuk memastikan query dropdown tidak lagi memakai `select *` melainkan hanya kolom yang diperlukan.

---

### PERF-10 — Evaluasi Foreign Key Relationships & Integritas Data

- **Prioritas:** **Informational (Kondisi Saat Ini Baik)**
- **File & Skema Terdampak:**
  - Seluruh 27 Foreign Key Constraints di skema `toyacheck`
- **Hasil Pemeriksaan:**
  1. Seluruh kolom relasional utama (`vehicle_id`, `driver_id`, `user_id`, `checklist_id`, `inspection_item_id`, `maintenance_id`, `sparepart_id`, `mekanik_id`, `vendor_id`, `sparepart_request_id`, `requested_by`, `reviewed_by`, `assigned_vehicle_id`, `parent_id`, `user_edit`) telah memiliki **Foreign Key Constraint** resmi dan **index otomatis InnoDB**.
  2. Kebijakan `ON DELETE` sudah selaras dengan integritas audit:
     - Child details (`checklist_items.checklist_id`, `maintenance_details.maintenance_id`, `purchase_items.purchase_id`, `user_settings.user_id`) menggunakan `CASCADE`.
     - Data historis utama (`checklists.driver_id`, `checklists.vehicle_id`, `maintenances.mekanik_id`, `maintenances.vehicle_id`, `sparepart_requests.requested_by`) menggunakan `RESTRICT` untuk mencegah hilangnya jejak audit secara tidak sengaja.
     - Referensi opsional/master katalog (`sparepart_id`, `vendor_id`, `checklist_id`, `reviewed_by`, `assigned_vehicle_id`) menggunakan `SET NULL`.
  3. **Catatan Kecil:** Pada `VehicleController::destroy()` (`app/Http/Controllers/VehicleController.php:71-104`), penghapusan kendaraan melewati pengecekan dengan cara menghapus paksa seluruh `checklists` dan `maintenances` kendaraan tersebut terlebih dahulu di dalam `DB::transaction()`. Meskipun konsisten secara FK, hal ini menghapus jejak historis operasional kendaraan yang bersangkutan; ke depannya disarankan memanfaatkan status `nonaktif` atau *Soft Deletes* pada `vehicles`.

---

## Peta Jalan Implementasi yang Disarankan (Prioritized Roadmap)

| Urutan | Finding ID | Estimasi Dampak | Kompleksitas | Ringkasan Perubahan |
| :---: | :--- | :---: | :---: | :--- |
| **1** | **PERF-01** | Sangat Tinggi (Skala) | Rendah | Ganti `Maintenance::all()` di `calculateRepairTrendsFromDb()` menjadi agregasi SQL `SUM(CASE ...)` + caching ringan. |
| **2** | **PERF-02** | Tinggi (Instan) | Rendah | Pisahkan eksekusi query di `DashboardController::index()` sesuai role yang sedang login (`admin` vs `mekanik` vs `pengadaan`). |
| **3** | **PERF-03** | Tinggi (Instan) | Sedang | Ganti `limit(1)` di `with(['checklists'])` memakai `hasOne()->latestOfMany('tanggal')` + eager-load `driver` serta deduplikasi badge count di `topbar`/`sidebar`. |
| **4** | **PERF-04** | Tinggi (Skala) | Rendah | Ubah `whereDate()`, `whereYear()`, `whereMonth()` menjadi rentang `whereBetween()` yang *sargable*. |
| **5** | **PERF-05** | Tinggi (Skala) | Rendah | Tambahkan 7 composite/range index terukur hanya pada tabel transaksional (`checklists`, `maintenances`, `sparepart_requests`, `purchases`). |
| **6** | **PERF-06** | Sedang-Tinggi | Rendah | Terapkan `->paginate()` pada endpoint daftar `PengadaanController`, `MekanikController::approvals()`, dan `$purchases` di `SparepartRequestController`. |
| **7** | **PERF-07** | Sedang | Rendah | Hapus query ganda di `ChecklistHistoryController::exportPdf()` dan loop delete di `VehicleController::destroy()`. |
| **8** | **PERF-08, PERF-09** | Rendah-Sedang | Rendah | Konsolidasikan query `COUNT(*)` stat cards per controller menjadi 1 query, dan spesifikasikan kolom `select([...])` pada dropdown modal. |
