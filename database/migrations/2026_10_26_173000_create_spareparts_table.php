<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Katalog Sparepart & Alat (SRS: tabel Sparepart).
     */
    public function up(): void
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();

            $table->string('kode_sparepart')
                ->unique();

            $table->string('nama_sparepart');

            $table->string('kategori')
                ->default('Umum');

            $table->integer('stok')
                ->default(0);

            $table->string('satuan')
                ->default('pcs');

            $table->decimal('harga', 14, 2)
                ->default(0);

            $table->integer('minimal_stok')
                ->default(5);

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
