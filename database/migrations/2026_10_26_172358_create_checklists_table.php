<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained()
                ->restrictOnDelete();

            // fisik = diisi Driver, k3 = diisi HSE
            $table->enum('checklist_type', [
                'fisik',
                'k3'
            ])->default('fisik');

            // Petugas yang melakukan inspeksi
            $table->foreignId('driver_id')
                ->constrained('users')
                ->restrictOnDelete();

            // User yang menginput data
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->dateTime('tanggal');

            $table->enum('status', [
                'aman',
                'kendala'
            ]);

            $table->text('catatan')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};
