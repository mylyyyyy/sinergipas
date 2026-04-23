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
        Schema::table('squad_schedules', function (Blueprint $table) {
            // Drop foreign key first because it might be using the unique index
            $table->dropForeign(['shift_id']);
            
            // Now drop the unique constraint
            $table->dropUnique('squad_schedules_shift_id_date_unique');
            
            // Re-add foreign key
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            
            // Add new unique constraint that includes squad_id
            $table->unique(['squad_id', 'shift_id', 'date'], 'squad_schedules_squad_shift_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squad_schedules', function (Blueprint $table) {
            $table->dropUnique('squad_schedules_squad_shift_date_unique');
            $table->unique(['shift_id', 'date'], 'squad_schedules_shift_id_date_unique');
        });
    }
};
