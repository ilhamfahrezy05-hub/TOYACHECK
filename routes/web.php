<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\InspectionItemController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChecklistHistoryController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\SparepartRequestController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DriverAppController;
use App\Http\Controllers\MekanikController;
use App\Http\Controllers\PengadaanController;

/*
|--------------------------------------------------------------------------
| Mobile & QR Code Entry Routes (Bisa diakses saat scan QR di truk)
|--------------------------------------------------------------------------
*/
Route::get('/m/qr/{code?}', [DriverAppController::class, 'qrEntry'])
    ->name('mobile.qr');

Route::get('/m/login', [DriverAppController::class, 'showLogin'])
    ->name('mobile.login');

Route::post('/m/login', [DriverAppController::class, 'authenticate'])
    ->name('mobile.login.attempt');

/*
|--------------------------------------------------------------------------
| Guest Routes — Hanya bisa diakses tanpa login
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->name('login.attempt');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes — Harus login terlebih dahulu
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Redirect root ke dashboard
    Route::redirect('/', '/dashboard');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |------------------------------------------------------------------
    | Modul Khusus Role Mekanik (Hanya untuk Role Mekanik)
    |------------------------------------------------------------------
    */
    Route::prefix('mekanik')
        ->name('mekanik.')
        ->middleware('role:mekanik')
        ->group(function () {

            // 1. Dashboard Mekanik (Rekap Perbaikan, Persediaan ABC, Rekap Armada, Tren)
            Route::get('/dashboard', [MekanikController::class, 'dashboard'])
                ->name('dashboard');

            // 2. Inventory & Request Stock
            Route::get('/inventory', [MekanikController::class, 'inventory'])
                ->name('inventory');

            Route::get('/inventory/request', [MekanikController::class, 'requestStockForm'])
                ->name('inventory.request');

            Route::post('/inventory/request', [MekanikController::class, 'storeRequestStock'])
                ->name('inventory.request.store');

            // 3. Repair Log (Rekap Log Perbaikan, Tambah Log + Select Spare Part, Edit, Hapus, Export PDF)
            Route::get('/repairs/export-pdf', [MekanikController::class, 'exportPdfRepairs'])
                ->name('repairs.export-pdf');

            Route::get('/repairs', [MekanikController::class, 'repairs'])
                ->name('repairs');

            Route::get('/repairs/create', [MekanikController::class, 'createRepair'])
                ->name('repairs.create');

            Route::post('/repairs', [MekanikController::class, 'storeRepair'])
                ->name('repairs.store');

            Route::put('/repairs/{maintenance}', [MekanikController::class, 'updateRepair'])
                ->name('repairs.update');

            Route::delete('/repairs/{maintenance}', [MekanikController::class, 'destroyRepair'])
                ->name('repairs.destroy');

            // 4. Master Data (Inventaris & Toolkit: All, Toolkit, Support, Log Baru, Edit, Hapus)
            Route::get('/master-data', [MekanikController::class, 'masterData'])
                ->name('master-data');

            Route::post('/master-data', [MekanikController::class, 'storeMasterItem'])
                ->name('master-data.store');

            Route::put('/master-data/{sparepart}', [MekanikController::class, 'updateMasterItem'])
                ->name('master-data.update');

            Route::delete('/master-data/{sparepart}', [MekanikController::class, 'destroyMasterItem'])
                ->name('master-data.destroy');

            // 5. Persetujuan / History Pembelanjaan
            Route::get('/approvals', [MekanikController::class, 'approvals'])
                ->name('approvals');

            Route::get('/approvals/export', [MekanikController::class, 'exportApprovals'])
                ->name('approvals.export');
        });

    /*
    |------------------------------------------------------------------
    | Modul Khusus Role Pengadaan / Procurement (Figma node 917-10746)
    |------------------------------------------------------------------
    */
    Route::prefix('pengadaan')
        ->name('pengadaan.')
        ->middleware('role:pengadaan|admin')
        ->group(function () {

            // 1. Dasbor Pengadaan
            Route::get('/dashboard', [PengadaanController::class, 'dashboard'])
                ->name('dashboard');

            // 2. Permintaan Pengadaan (Master-Detail & Full Detail)
            Route::get('/requests', [PengadaanController::class, 'requests'])
                ->name('requests');

            Route::get('/requests/{sparepartRequest}', [PengadaanController::class, 'showRequest'])
                ->name('requests.show');

            Route::patch('/requests/{sparepartRequest}/approve', [PengadaanController::class, 'approveRequest'])
                ->name('requests.approve');

            Route::patch('/requests/{sparepartRequest}/reject', [PengadaanController::class, 'rejectRequest'])
                ->name('requests.reject');

            Route::delete('/requests/{sparepartRequest}', [PengadaanController::class, 'destroyRequest'])
                ->name('requests.destroy');

            // 3. Lokasi Vendor & Pendaftaran Vendor
            Route::get('/vendors', [PengadaanController::class, 'vendors'])
                ->name('vendors');

            Route::get('/vendors/create', [PengadaanController::class, 'createVendor'])
                ->name('vendors.create');

            Route::post('/vendors', [PengadaanController::class, 'storeVendor'])
                ->name('vendors.store');

            Route::put('/vendors/{vendor}', [PengadaanController::class, 'updateVendor'])
                ->name('vendors.update');

            Route::delete('/vendors/{vendor}', [PengadaanController::class, 'destroyVendor'])
                ->name('vendors.destroy');

            // 4. Laporan / Riwayat Belanja & Detail Transaksi Baru (Input Nota)
            Route::get('/reports/export-pdf', [PengadaanController::class, 'exportReportsPdf'])
                ->name('reports.export-pdf');

            Route::get('/reports', [PengadaanController::class, 'reports'])
                ->name('reports');

            Route::get('/reports/create', [PengadaanController::class, 'createReport'])
                ->name('reports.create');

            Route::post('/reports', [PengadaanController::class, 'storeReport'])
                ->name('reports.store');

            Route::delete('/reports/{purchase}', [PengadaanController::class, 'destroyReport'])
                ->name('reports.destroy');

            // 5. Persetujuan (Riwayat Permintaan Disetujui)
            Route::get('/approvals/export-pdf', [PengadaanController::class, 'exportApprovalsPdf'])
                ->name('approvals.export-pdf');

            Route::get('/approvals', [PengadaanController::class, 'approvals'])
                ->name('approvals');
        });

    /*
    |------------------------------------------------------------------
    | Resource Routes
    |------------------------------------------------------------------
    */

    // Master data — hanya admin (SMD / Distribusi)
    Route::middleware('role:admin')->group(function () {

        // Manajemen Pengguna
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Manajemen Armada
        Route::resource('vehicles', VehicleController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('vehicles/qr/print', [DriverAppController::class, 'printAllQr'])
            ->name('vehicles.qr.print');

        // Manajemen Item Pemeriksaan (parameter checklist)
        Route::resource('inspection-items', InspectionItemController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::patch('inspection-items/{inspection_item}/toggle', [InspectionItemController::class, 'toggle'])
            ->name('inspection-items.toggle');
    });

    /*
    |------------------------------------------------------------------
    | Aplikasi Mobile Driver & HSE (UI Khusus Mobile)
    | Admin TIDAK diperbolehkan mengakses karena hanya bertugas memonitoring
    |------------------------------------------------------------------
    */
    Route::prefix('driver')
        ->name('driver.')
        ->middleware('role:driver|hse')
        ->group(function () {

            Route::get('/', [DriverAppController::class, 'index'])
                ->name('index');

            Route::get('/vehicles/{vehicle}', [DriverAppController::class, 'showVehicle'])
                ->name('vehicle.show');

            Route::get('/vehicles/{vehicle}/export-pdf', [DriverAppController::class, 'exportVehiclePdf'])
                ->name('vehicle.export-pdf');

            Route::get('/vehicles/{vehicle}/checklist/{type}', [DriverAppController::class, 'checklistForm'])
                ->name('checklist.form');

            Route::post('/vehicles/{vehicle}/checklist/{type}', [DriverAppController::class, 'storeChecklist'])
                ->name('checklist.store');

            Route::get('/history', [DriverAppController::class, 'history'])
                ->name('history');

            Route::get('/history/{checklist}', [DriverAppController::class, 'historyDetail'])
                ->name('history.detail');

            // Khusus Role K3 / HSE: Edit & Hapus Laporan K3 serta Kelola Item Pemeriksaan K3
            Route::middleware('role:hse')->group(function () {

                // Edit (Ganti) & Hapus Laporan K3
                Route::get('/history/{checklist}/edit', [DriverAppController::class, 'editK3Report'])
                    ->name('history.edit');

                Route::put('/history/{checklist}', [DriverAppController::class, 'updateK3Report'])
                    ->name('history.update');

                Route::delete('/history/{checklist}', [DriverAppController::class, 'destroyK3Report'])
                    ->name('history.destroy');

                // Kelola (Tambah, Edit, Hapus) Item Pemeriksaan Khusus K3 (Figma node 1190:5486)
                Route::get('/k3-items', [DriverAppController::class, 'k3Items'])
                    ->name('k3-items');

                Route::post('/k3-items', [DriverAppController::class, 'storeK3Item'])
                    ->name('k3-items.store');

                Route::put('/k3-items/{inspectionItem}', [DriverAppController::class, 'updateK3Item'])
                    ->name('k3-items.update');

                Route::delete('/k3-items/{inspectionItem}', [DriverAppController::class, 'destroyK3Item'])
                    ->name('k3-items.destroy');
            });

            Route::get('/profile', [DriverAppController::class, 'profile'])
                ->name('profile');

            Route::get('/notifications', [DriverAppController::class, 'notifications'])
                ->name('notifications');
        });

    // Checklist — admin (monitoring & edit hasil), driver (fisik), hse (k3)
    Route::middleware('role:admin|driver|hse')->group(function () {

        Route::resource('checklists', ChecklistController::class)
            ->only(['index', 'store', 'show']);
    });

    // Admin dapat mengedit hasil checklist armada (fisik & k3)
    Route::middleware('role:admin')->group(function () {

        Route::get('checklists/{checklist}/edit', [ChecklistController::class, 'edit'])
            ->name('checklists.edit');

        Route::put('checklists/{checklist}', [ChecklistController::class, 'update'])
            ->name('checklists.update');
    });

    // Riwayat Checklist — Driver/HSE melihat miliknya, Admin memonitor semua
    Route::middleware('role:admin|driver|hse')->group(function () {

        Route::get('riwayat', [ChecklistHistoryController::class, 'index'])
            ->name('riwayat.index');

        Route::get('riwayat/export-pdf', [ChecklistHistoryController::class, 'exportPdf'])
            ->name('riwayat.export-pdf');

        Route::get('riwayat/vehicle/{vehicle}/pdf', [ChecklistHistoryController::class, 'vehiclePdf'])
            ->name('riwayat.vehicle-pdf');

        Route::get('riwayat/export', [ChecklistHistoryController::class, 'export'])
            ->name('riwayat.export');
    });

    // Catatan Perbaikan & Pengajuan Suku Cadang ke Pengadaan — Admin Distribusi
    Route::middleware('role:admin')->group(function () {

        // Export PDF Catatan Perbaikan
        Route::get('maintenances/export-pdf', [MaintenanceController::class, 'exportPdf'])
            ->name('maintenances.export-pdf');

        // Admin memonitor & mengedit catatan perbaikan dari mekanik
        Route::resource('maintenances', MaintenanceController::class)
            ->only(['index', 'show', 'update']);

        // Admin membuat & mengedit pengajuan suku cadang (termasuk edit pengajuan mekanik & ajukan ke pengadaan)
        Route::post('sparepart-requests', [SparepartRequestController::class, 'store'])
            ->name('sparepart-requests.store');

        Route::put('sparepart-requests/{sparepartRequest}', [SparepartRequestController::class, 'update'])
            ->name('sparepart-requests.update');

        Route::delete('sparepart-requests/{sparepartRequest}', [SparepartRequestController::class, 'destroy'])
            ->name('sparepart-requests.destroy');
    });

    // Pengajuan & Persetujuan Suku Cadang + Vendor — Admin & Pengadaan
    Route::middleware('role:admin|pengadaan')->group(function () {

        Route::get('sparepart-requests', [SparepartRequestController::class, 'index'])
            ->name('sparepart-requests.index');

        Route::patch('sparepart-requests/{sparepartRequest}/approve', [SparepartRequestController::class, 'approve'])
            ->name('sparepart-requests.approve');

        Route::patch('sparepart-requests/{sparepartRequest}/reject', [SparepartRequestController::class, 'reject'])
            ->name('sparepart-requests.reject');

        Route::resource('vendors', VendorController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

    // Pengaturan akun — semua pengguna yang login
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::put('settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');
    Route::put('settings/preferences', [SettingController::class, 'updatePreferences'])->name('settings.preferences');

});
