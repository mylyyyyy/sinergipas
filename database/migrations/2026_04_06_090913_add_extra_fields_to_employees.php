<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'nik')) {
                $table->string('nik')->nullable()->after('nip');
            }
            if (!Schema::hasColumn('employees', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('full_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['nik', 'phone_number']);
        });
    }
};
