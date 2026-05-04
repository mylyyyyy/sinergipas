<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'description' => 'required|string|max:255'
        ]);

        $holiday = Holiday::create($request->only('date', 'description'));

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_holiday',
            'auditable_id' => $holiday->id,
            'auditable_type' => Holiday::class,
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menambahkan hari libur nasional: ' . $holiday->date->format('d/m/Y') . ' - ' . $holiday->description
        ]);

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy(Request $request, Holiday $holiday)
    {
        $desc = $holiday->description;
        $date = $holiday->date->format('d/m/Y');
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_holiday',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menghapus hari libur nasional: ' . $date . ' - ' . $desc
        ]);

        $holiday->delete();

        return back()->with('success', 'Hari libur berhasil dihapus.');
    }
}
