<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->foreignId('schedule_type_id')->nullable()->constrained('schedule_types')->onDelete('set null')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('squads', function (Blueprint $table) {
            $table->dropForeign(['schedule_type_id']);
            $table->dropColumn('schedule_type_id');
        });
    }
};
