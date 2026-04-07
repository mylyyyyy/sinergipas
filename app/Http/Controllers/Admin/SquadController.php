<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Squad;
use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SquadController extends Controller
{
    public function index()
    {
        $squads = Squad::withCount('employees')->get();
        $unassignedEmployees = Employee::whereNull('squad_id')
            ->where(function($q) {
                $q->where('employee_type', 'regu_jaga')
                  ->orWhere('position', 'like', '%JAGA%')
                  ->orWhere('position', 'like', '%PENJAGA%');
            })
            ->orderBy('full_name')
            ->get();
            
        return view('admin.squads.index', compact('squads', 'unassignedEmployees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:squads,name|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $squad = Squad::create($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_squad',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " membuat regu baru: " . $squad->name
        ]);

        return back()->with('success', 'Regu berhasil dibuat.');
    }

    public function update(Request $request, Squad $squad)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:squads,name,' . $squad->id,
            'description' => 'nullable|string|max:500',
        ]);

        $squad->update($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_squad',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " memperbarui regu: " . $squad->name
        ]);

        return back()->with('success', 'Regu berhasil diperbarui.');
    }

    public function destroy(Squad $squad)
    {
        $name = $squad->name;
        
        // Unassign employees
        Employee::where('squad_id', $squad->id)->update(['squad_id' => null]);
        
        $squad->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_squad',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . " menghapus regu: " . $name
        ]);

        return back()->with('success', 'Regu berhasil dihapus.');
    }

    public function addMember(Request $request, Squad $squad)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        Employee::whereIn('id', $request->employee_ids)->update(['squad_id' => $squad->id]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'add_squad_member',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " menambahkan anggota ke regu: " . $squad->name
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan ke regu.');
    }

    public function removeMember(Request $request, Squad $squad)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        Employee::where('id', $request->employee_id)
            ->where('squad_id', $squad->id)
            ->update(['squad_id' => null]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'remove_squad_member',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " mengeluarkan anggota dari regu: " . $squad->name
        ]);

        return back()->with('success', 'Anggota berhasil dikeluarkan dari regu.');
    }
}
