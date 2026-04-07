<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'kop_line_1'],
            ['value' => 'KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN RI']
        );
        Setting::updateOrCreate(
            ['key' => 'kop_line_2'],
            ['value' => 'KANTOR WILAYAH KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert specifically as it's just data update
    }
};
