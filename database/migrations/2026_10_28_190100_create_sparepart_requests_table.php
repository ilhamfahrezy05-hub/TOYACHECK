<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permintaan suku cadang dari Mekanik, disetujui / ditolak Admin Distribusi.
     */
    public function up(): void
    {
        Schema::create('sparepart_requests', function (Blueprint $table) {
            $table->id();

            // Terhubung ke katalog; null jika sparepart belum terdaftar di katalog
            $table->foreignId('sparepart_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('nama_sparepart');
            $table->unsignedInteger('qty');

            // Perbaikan yang membutuhkan sparepart ini (opsional)
            $table->foreignId('maintenance_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('requested_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->text('alasan')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->text('catatan_admin')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sparepart_requests');
    }
};
