<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1. Tambah kolom `kepemilikan` (internal / eksternal) pada tabel `vehicles`.
     * 2. Tambah status `perbaikan` (Dalam Perbaikan) pada kolom `status` tabel `checklists`.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('vehicles', 'kepemilikan')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('kepemilikan', 20)->default('internal')->after('jenis_kendaraan');
            });
        }

        DB::statement("ALTER TABLE checklists MODIFY COLUMN status ENUM('aman', 'kendala', 'perbaikan') NOT NULL DEFAULT 'aman'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'kepemilikan')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('kepemilikan');
            });
        }

        DB::statement("UPDATE checklists SET status = 'kendala' WHERE status = 'perbaikan'");
        DB::statement("ALTER TABLE checklists MODIFY COLUMN status ENUM('aman', 'kendala') NOT NULL DEFAULT 'aman'");
    }
};
