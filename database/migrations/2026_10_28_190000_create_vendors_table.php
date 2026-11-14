<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Direktori vendor / supplier suku cadang (entitas eksternal SRS).
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vendor');
            $table->string('kontak_person')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('keahlian')->nullable();   // contoh: Ban, Aki, Oli
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
