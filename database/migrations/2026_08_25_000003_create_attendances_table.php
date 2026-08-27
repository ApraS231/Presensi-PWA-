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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('lat_in', 10, 7)->nullable();
            $table->decimal('long_in', 10, 7)->nullable();
            $table->decimal('lat_out', 10, 7)->nullable();
            $table->decimal('long_out', 10, 7)->nullable();
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();
            $table->enum('status', ['tepat_waktu', 'terlambat', 'izin', 'sakit', 'cuti', 'alpha'])->default('alpha');
            $table->decimal('distance_meters', 8, 2)->nullable();
            $table->boolean('auto_checkout')->default(false);
            $table->timestamps();

            // Constraint: Satu karyawan hanya memiliki satu record presensi per hari
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
