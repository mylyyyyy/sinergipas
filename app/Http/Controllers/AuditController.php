<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
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

        if ($request->filled('activity')) {
            $query->where('activity', $request->activity);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = (clone $query)->latest()->paginate(20)->withQueryString();
        $totalLogs = (clone $query)->count();
        $todayLogs = (clone $query)->whereDate('created_at', now())->count();
        $uniqueUsers = (clone $query)->distinct('user_id')->count('user_id');
        
        $topDownloaders = (clone $query)->select('user_id', DB::raw('count(*) as total'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        $activities = AuditLog::query()
            ->select('activity')
            ->distinct()
            ->orderBy('activity')
            ->pluck('activity');

        $users = User::query()
            ->whereIn('id', AuditLog::query()->select('user_id')->whereNotNull('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('audit.index', compact(
            'logs',
            'topDownloaders',
            'totalLogs',
            'todayLogs',
            'uniqueUsers',
            'activities',
            'users'
        ));
    }

    public function destroyAll(Request $request)
    {
        AuditLog::query()->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'clear_audit_logs',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' membersihkan seluruh log audit',
            'is_system' => true,
        ]);

        return back()->with('success', 'Seluruh log audit telah dibersihkan.');
    }
}
