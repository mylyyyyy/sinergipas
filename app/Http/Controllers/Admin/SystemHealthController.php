<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SystemHealthController extends Controller
{
    public function index()
    {
        // 1. Database Status
        $dbStatus = 'Online';
        try {
            DB::connection()->getPdo();
            $dbSize = DB::select('SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = ?', [env('DB_DATABASE')])[0]->size;
        } catch (\Exception $e) {
            $dbStatus = 'Offline';
            $dbSize = 0;
        }

        // 2. Disk Usage
        $totalSizeBytes = 0;
        $disk = Storage::disk('private');
        $allFiles = $disk->allFiles('documents');
        foreach ($allFiles as $file) {
            $totalSizeBytes += $disk->size($file);
        }
        $storageUsed = round($totalSizeBytes / (1024 * 1024), 2);

        // 3. Environment Info
        $envInfo = [
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'Timezone' => config('app.timezone'),
            'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
        ];

        // 4. Recent Logs (from storage/logs/laravel.log)
        $logPath = storage_path('logs/laravel.log');
        $recentLogs = [];
        if (File::exists($logPath)) {
            $logContent = File::get($logPath);
            $lines = explode("\n", $logContent);
            $recentLogs = array_slice(array_reverse(array_filter($lines)), 0, 10);
        }

        return view('settings.health', compact('dbStatus', 'dbSize', 'storageUsed', 'envInfo', 'recentLogs'));
    }
}
