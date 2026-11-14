<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penggunaan Suku Cadang saat Perbaikan (SRS: Detail_Maintenance).
     */
    public function up(): void
    {
        Schema::create('maintenance_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('maintenance_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('sparepart_id')
                ->nullable()
                ->constrained('spareparts')
                ->nullOnDelete();

            // Salinan nama sparepart saat perbaikan
            $table->string('nama_sparepart');

            $table->unsignedInteger('qty')
                ->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_details');
    }
};
