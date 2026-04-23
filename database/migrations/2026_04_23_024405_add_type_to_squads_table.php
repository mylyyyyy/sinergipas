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
        Schema::table('squads', function (Blueprint $blueprint) {
            $blueprint->enum('type', ['regu', 'p2u'])->default('regu')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('squads', function (Blueprint $blueprint) {
            $blueprint->dropColumn('type');
        });
    }
};
