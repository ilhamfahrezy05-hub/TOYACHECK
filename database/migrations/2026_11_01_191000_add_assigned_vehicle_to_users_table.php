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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('assigned_vehicle_id')
                ->nullable()
                ->after('avatar')
                ->constrained('vehicles')
                ->nullOnDelete();

            $table->dateTime('vehicle_assigned_at')
                ->nullable()
                ->after('assigned_vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_vehicle_id']);
            $table->dropColumn(['assigned_vehicle_id', 'vehicle_assigned_at']);
        });
    }
};

