<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Transaksi Pembelian (PO / Riwayat Belanja Pengadaan) & Detail Item Pembelian,
     * serta kelengkapan kolom koordinat/kota pada tabel vendors sesuai Figma node 917-10746.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'kota')) {
                $table->string('kota', 100)->nullable()->after('alamat');
            }
            if (! Schema::hasColumn('vendors', 'provinsi')) {
                $table->string('provinsi', 100)->nullable()->after('kota');
            }
            if (! Schema::hasColumn('vendors', 'latitude')) {
                $table->string('latitude', 40)->nullable()->after('provinsi');
            }
            if (! Schema::hasColumn('vendors', 'longitude')) {
                $table->string('longitude', 40)->nullable()->after('latitude');
            }
        });

        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('kode_pembelian', 40)->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->string('vendor_name', 150)->nullable();
                $table->foreignId('sparepart_request_id')->nullable()->constrained('sparepart_requests')->nullOnDelete();
                $table->date('tanggal_pembelian');
                $table->string('waktu_pembelian', 20)->nullable();
                $table->decimal('total_harga', 15, 2)->default(0);
                $table->string('bukti_pembayaran')->nullable();
                $table->text('catatan')->nullable();
                $table->string('status_pembelian', 30)->default('selesai');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('sparepart_id')->nullable()->constrained('spareparts')->nullOnDelete();
                $table->string('nama_sparepart', 150);
                $table->string('kode_barang', 60)->nullable();
                $table->integer('qty')->default(1);
                $table->decimal('harga_satuan', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');

        Schema::table('vendors', function (Blueprint $table) {
            $cols = array_filter(['kota', 'provinsi', 'latitude', 'longitude'], fn ($c) => Schema::hasColumn('vendors', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
