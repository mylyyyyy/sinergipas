<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScheduleTypeController extends Controller
{
    public function index()
    {
        $types = ScheduleType::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.schedule_types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:schedule_types,name',
            'description' => 'nullable|string',
            'uses_squads' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['code'] = Str::slug($request->name);
        $data['uses_squads'] = $request->has('uses_squads');
        $data['is_active'] = $request->has('is_active');

        $type = ScheduleType::create($data);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_schedule_type',
            'auditable_id' => $type->id,
            'auditable_type' => ScheduleType::class,
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " membuat tipe piket baru: " . $type->name,
            'new_values' => $type->toArray()
        ]);

        return back()->with('success', 'Tipe piket berhasil ditambahkan.');
    }

    public function update(Request $request, ScheduleType $scheduleType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:schedule_types,name,' . $scheduleType->id,
            'description' => 'nullable|string',
            'uses_squads' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $oldValues = $scheduleType->toArray();
        
        $data = $request->all();
        $data['code'] = Str::slug($request->name);
        $data['uses_squads'] = $request->has('uses_squads');
        $data['is_active'] = $request->has('is_active');

        $scheduleType->update($data);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_schedule_type',
            'auditable_id' => $scheduleType->id,
            'auditable_type' => ScheduleType::class,
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " memperbarui tipe piket: " . $scheduleType->name,
            'old_values' => $oldValues,
            'new_values' => $scheduleType->fresh()->toArray()
        ]);

        return back()->with('success', 'Tipe piket berhasil diperbarui.');
    }

    public function destroy(ScheduleType $scheduleType)
    {
        $name = $scheduleType->name;
        
        // Prevent deletion if in use
        if ($scheduleType->squads()->exists() || $scheduleType->schedules()->exists()) {
            return back()->with('error', 'Tipe piket tidak bisa dihapus karena sedang digunakan dalam data regu atau jadwal.');
        }

        $scheduleType->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_schedule_type',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . " menghapus tipe piket: " . $name
        ]);

        return back()->with('success', 'Tipe piket berhasil dihapus.');
    }
}
