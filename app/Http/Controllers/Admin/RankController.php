<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index()
    {
        $ranks = Rank::orderBy('name')->get();
        return view('admin.ranks.index', compact('ranks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:ranks,name',
            'description' => 'nullable|string',
            'meal_allowance' => 'required|integer|min:0',
        ]);

        Rank::create($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_rank',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " membuat golongan baru: $request->name"
        ]);

        return back()->with('success', 'Golongan berhasil ditambahkan.');
    }

    public function update(Request $request, Rank $rank)
    {
        $request->validate([
            'name' => 'required|string|unique:ranks,name,' . $rank->id,
            'description' => 'nullable|string',
            'meal_allowance' => 'required|integer|min:0',
        ]);

        $rank->update($request->all());

        // Real-time Sync: Update all attendance records for employees with this rank in the current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        \App\Models\Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereHas('employee', function($q) use ($rank) {
                $q->where('rank_id', $rank->id);
            })
            ->update(['allowance_amount' => $rank->meal_allowance]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_rank',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " memperbarui golongan: $rank->name"
        ]);

        return back()->with('success', 'Golongan berhasil diperbarui.');
    }

    public function destroy(Rank $rank)
    {
        $name = $rank->name;
        $rank->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_rank',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . " menghapus golongan: $name"
        ]);

        return back()->with('success', 'Golongan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids) return back()->with('error', 'Pilih data yang ingin dihapus.');

        $count = Rank::whereIn('id', $ids)->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'bulk_delete_rank',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menghapus ' . $count . ' golongan secara massal'
        ]);

        return back()->with('success', $count . ' golongan berhasil dihapus.');
    }
}
