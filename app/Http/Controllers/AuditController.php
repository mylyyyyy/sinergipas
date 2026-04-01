<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        // Auto-cleanup
        AuditLog::where('created_at', '<', now()->subDays(30))->delete();

        $query = AuditLog::with(['user', 'document']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('activity', 'like', "%$search%")
                  ->orWhere('details', 'like', "%$search%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        
        $topDownloaders = AuditLog::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        return view('audit.index', compact('logs', 'topDownloaders'));
    }

    public function destroyAll()
    {
        AuditLog::truncate();
        return back()->with('success', 'Seluruh log audit telah dibersihkan.');
    }
}
