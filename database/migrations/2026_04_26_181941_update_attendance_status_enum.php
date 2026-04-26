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
        Schema::table('attendances', function (Blueprint $table) {
            // Mengubah ENUM menjadi STRING agar lebih fleksibel dan menghindari error truncation di server hosting
            $table->string('status')->default('absent')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('status', [
                'present', 'late', 'absent', 'on_leave', 'sick'
            ])->default('absent')->change();
        });
    }
};
