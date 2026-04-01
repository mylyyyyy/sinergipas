<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->after('file_path');
            $table->integer('year')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('year');
        });
    }
    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['status', 'year', 'verified_at']);
        });
    }
};
