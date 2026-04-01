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
        Schema::table('report_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('report_issues', 'status')) {
                $table->string('status')->default('open')->after('message');
            }
            if (!Schema::hasColumn('report_issues', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->dropColumn(['status', 'admin_note']);
        });
    }
};
