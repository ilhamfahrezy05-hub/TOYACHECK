<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahan atribut untuk mendukung modul Role Mekanik sesuai desain Figma & SRS:
     * - spareparts: kategori_abc (A/B/C), tipe_item (sparepart/toolkit/support), frekuensi, lokasi
     * - maintenances: nama_driver, kategori_kendala, estimasi_downtime
     * - sparepart_requests: priority_level, expected_delivery_date, vendor_name
     */
    public function up(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            if (! Schema::hasColumn('spareparts', 'kategori_abc')) {
                $table->string('kategori_abc', 5)->default('A')->after('kategori');
            }
            if (! Schema::hasColumn('spareparts', 'tipe_item')) {
                $table->string('tipe_item', 30)->default('sparepart')->after('kategori_abc');
            }
            if (! Schema::hasColumn('spareparts', 'frekuensi')) {
                $table->string('frekuensi', 30)->default('Rutin')->after('tipe_item');
            }
            if (! Schema::hasColumn('spareparts', 'lokasi')) {
                $table->string('lokasi', 100)->nullable()->default('Warehouse Alpha')->after('keterangan');
            }
        });

        Schema::table('maintenances', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenances', 'nama_driver')) {
                $table->string('nama_driver', 100)->nullable()->after('mekanik_id');
            }
            if (! Schema::hasColumn('maintenances', 'kategori_kendala')) {
                $table->string('kategori_kendala', 60)->nullable()->default('Service Ringan')->after('status_maintenance');
            }
            if (! Schema::hasColumn('maintenances', 'estimasi_downtime')) {
                $table->string('estimasi_downtime', 40)->nullable()->default('4.5 Jam')->after('kategori_kendala');
            }
        });

        Schema::table('sparepart_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('sparepart_requests', 'priority_level')) {
                $table->string('priority_level', 30)->default('normal')->after('qty');
            }
            if (! Schema::hasColumn('sparepart_requests', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('priority_level');
            }
            if (! Schema::hasColumn('sparepart_requests', 'vendor_name')) {
                $table->string('vendor_name', 120)->nullable()->after('expected_delivery_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropColumn(['kategori_abc', 'tipe_item', 'frekuensi', 'lokasi']);
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['nama_driver', 'kategori_kendala', 'estimasi_downtime']);
        });

        Schema::table('sparepart_requests', function (Blueprint $table) {
            $table->dropColumn(['priority_level', 'expected_delivery_date', 'vendor_name']);
        });
    }
};

