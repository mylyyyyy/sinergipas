<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%")
                  ->orWhere('position', 'like', "%$search%");
            });
        }

        $employees = $query->latest()->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        return redirect()->route('documents.employee', $employee->id);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:employees,nip',
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pegawai',
        ]);

        $employeeData = [
            'user_id' => $user->id,
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $request->position,
        ];

        if ($request->hasFile('photo')) {
            $employeeData['photo'] = $request->file('photo')->store('photos', 'public');
        }

        Employee::create($employeeData);

        return back()->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'nip' => 'required|unique:employees,nip,' . $employee->id,
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'password' => 'nullable|min:8',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userData = [
            'name' => $request->full_name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $employee->user->update($userData);

        $employeeData = [
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $request->position,
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo if it exists and is a file path
            if ($employee->getRawOriginal('photo') && !str_starts_with($employee->getRawOriginal('photo'), 'data:image')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->getRawOriginal('photo'));
            }
            $employeeData['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $employee->update($employeeData);

        return back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function importExcel(Request $request)
    {
        set_time_limit(300); // 5 minutes
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new EmployeesImport, $request->file('file'));
        return back()->with('success', 'Data pegawai berhasil diimpor.');
    }

    public function exportExcel() { return Excel::download(new EmployeesExport, 'daftar-pegawai.xlsx'); }
    
    public function exportPdf() {
        $employees = Employee::all();
        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download('daftar-pegawai.pdf');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->delete();
        return back()->with('success', 'Pegawai berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (empty($ids)) return back()->with('error', 'Tidak ada data terpilih.');

        $employees = Employee::whereIn('id', $ids)->get();
        foreach ($employees as $employee) {
            $employee->user->delete(); // Cascades
        }

        return back()->with('success', count($ids) . ' data pegawai berhasil dihapus.');
    }
}
