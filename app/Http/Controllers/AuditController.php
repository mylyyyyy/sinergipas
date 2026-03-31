<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use DB;

class AuditController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with(['user', 'document'])->latest()->paginate(20);
        
        $topDownloaders = AuditLog::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        $activityOverTime = AuditLog::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(7)
            ->get();

        return view('audit.index', compact('logs', 'topDownloaders', 'activityOverTime'));
    }

    public function destroyAll()
    {
        AuditLog::truncate();
        return back()->with('success', 'Seluruh log audit telah dibersihkan.');
    }
}
