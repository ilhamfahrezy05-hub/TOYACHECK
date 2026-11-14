<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah spesifikasi lengkap armada (dimensi box, tahun STNK, dan tahun KIR).
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('dimensi_box', 100)->nullable()->after('tahun');
            $table->string('tahun_stnk', 30)->nullable()->after('dimensi_box');
            $table->string('tahun_kir', 30)->nullable()->after('tahun_stnk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['dimensi_box', 'tahun_stnk', 'tahun_kir']);
        });
    }
};

