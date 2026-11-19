<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Standar Operasional Prosedur (SOP) per role aplikasi TOYACHECK.
     */
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('role_target', 30)->index(); // driver, hse, mekanik, admin, pengadaan
            $table->unsignedInteger('urutan')->default(1);
            $table->text('isi_prosedur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
