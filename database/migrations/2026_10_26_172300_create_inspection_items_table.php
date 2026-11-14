<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master parameter inspeksi (SRS: tabel "Checklist").
     *
     * parent_id kosong  => grup (contoh: "Ban & Rem")
     * parent_id terisi  => item yang dijawab saat inspeksi (contoh: "Ban Depan")
     */
    public function up(): void
    {
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('inspection_items')
                ->nullOnDelete();

            $table->string('nama');

            $table->enum('category', ['fisik', 'k3'])
                ->default('fisik');

            // Tolok ukur penilaian / SOP pemeriksaan
            $table->text('criteria')
                ->nullable();

            $table->unsignedSmallInteger('urutan')
                ->default(0);

            // Nonaktifkan item (bukan hapus) agar riwayat checklist tetap utuh
            $table->boolean('is_active')
                ->default(true);

            // Admin yang terakhir mengubah (audit)
            $table->foreignId('user_edit')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
