<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header Log Perbaikan Armada (SRS: Modul 4 Maintenance).
     */
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();

            // Armada yang diperbaiki
            $table->foreignId('vehicle_id')
                ->constrained()
                ->restrictOnDelete();

            // Mekanik yang bertanggung jawab
            $table->foreignId('mekanik_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Hubungan opsional ke checklist temuan
            $table->foreignId('checklist_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->dateTime('tanggal_maintenance');

            $table->enum('status_maintenance', [
                'pending',
                'in_progress',
                'completed'
            ])->default('pending');

            // Gejala / kerusakan armada
            $table->text('deskripsi_masalah');

            // Tindakan perbaikan yang dilakukan
            $table->text('solusi')
                ->nullable();

            $table->text('catatan')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
