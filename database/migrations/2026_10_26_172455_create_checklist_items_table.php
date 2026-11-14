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
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')
                ->constrained()
                ->cascadeOnDelete();

            // Terhubung ke master; jadi null jika master dihapus
            $table->foreignId('inspection_item_id')
                ->nullable()
                ->constrained('inspection_items')
                ->nullOnDelete();

            // Salinan nama & kriteria saat diperiksa (riwayat tetap utuh walau master diubah)
            $table->string('item');

            $table->text('criteria')
                ->nullable();

            // true = Baik (OK), false = Bermasalah (NOT OK)
            $table->boolean('kondisi')
                ->default(true);

            // Path foto bukti temuan
            $table->string('foto_dokumentasi')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
