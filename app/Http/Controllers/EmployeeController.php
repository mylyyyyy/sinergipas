<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Document;
use App\Models\Position;
use App\Models\WorkUnit;
use App\Models\AuditLog;
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
        $query = Employee::with(['user', 'position_relation', 'work_unit']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('nip', 'like', "%$search%")
                  ->orWhere('position', 'like', "%$search%");
            });
        }

        if ($request->filled('work_unit_id')) {
            $query->where('work_unit_id', $request->work_unit_id);
        }

        $employees = $query->latest()->paginate(10)->withQueryString();
        $positions = Position::orderBy('name')->get();
        $workUnits = WorkUnit::orderBy('name')->get();

        return view('employees.index', compact('employees', 'positions', 'workUnits'));
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
            'position_id' => 'required|exists:positions,id',
            'work_unit_id' => 'required|exists:work_units,id',
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

        $position = Position::find($request->position_id);
        
        $employeeData = [
            'user_id' => $user->id,
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $position->name,
            'position_id' => $request->position_id,
            'work_unit_id' => $request->work_unit_id,
        ];

        if ($request->hasFile('photo')) {
            $employeeData['photo'] = $request->file('photo')->store('photos', 'public');
        }

        Employee::create($employeeData);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_employee',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mendaftarkan pegawai baru: ' . $request->full_name
        ]);

        return back()->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'nip' => 'required|unique:employees,nip,' . $employee->id,
            'full_name' => 'required|string|max:255',
            'position_id' => 'required|exists:positions,id',
            'work_unit_id' => 'required|exists:work_units,id',
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

        $position = Position::find($request->position_id);

        $employeeData = [
            'nip' => $request->nip,
            'full_name' => $request->full_name,
            'position' => $position->name,
            'position_id' => $request->position_id,
            'work_unit_id' => $request->work_unit_id,
        ];

        if ($request->hasFile('photo')) {
            if ($employee->getRawOriginal('photo') && !str_starts_with($employee->getRawOriginal('photo'), 'data:image')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->getRawOriginal('photo'));
            }
            $employeeData['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $employee->update($employeeData);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_employee',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' memperbarui data pegawai: ' . $employee->full_name
        ]);

        return back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function importExcel(Request $request)
    {
        set_time_limit(300);
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new EmployeesImport, $request->file('file'));

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'import_employees',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' melakukan impor data pegawai massal'
        ]);

        return back()->with('success', 'Data pegawai berhasil diimpor.');
    }

    public function exportExcel(Request $request)
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'export_employees_excel',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mengekspor data pegawai ke Excel',
        ]);

        return Excel::download(new EmployeesExport, 'daftar-pegawai.xlsx');
    }
    
    public function exportPdf(Request $request) {
        $employees = Employee::with(['position_relation', 'work_unit'])->get();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'export_employees_pdf',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' mengekspor data pegawai ke PDF',
        ]);

        $pdf = Pdf::loadView('employees.pdf', compact('employees'));
        return $pdf->download('daftar-pegawai.pdf');
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->user->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_employee',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' menghapus pegawai: ' . $name
        ]);

        return back()->with('success', 'Pegawai berhasil dihapus.');
    }

    public function deletePhoto(Employee $employee)
    {
        if ($employee->getRawOriginal('photo')) {
            $name = $employee->full_name;
            if (!str_starts_with($employee->getRawOriginal('photo'), 'data:image')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->getRawOriginal('photo'));
            }
            $employee->update(['photo' => null]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => 'delete_employee_photo',
                'ip_address' => request()->ip(),
                'details' => auth()->user()->name . ' menghapus foto profil pegawai: ' . $name
            ]);

            return back()->with('success', 'Foto pegawai berhasil dihapus.');
        }
        return back()->with('error', 'Tidak ada foto untuk dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:employees,id'],
        ])['ids'];

        if (empty($ids)) return back()->with('error', 'Tidak ada data terpilih.');

        $employees = Employee::whereIn('id', $ids)->get();
        foreach ($employees as $employee) {
            $employee->user->delete(); 
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'bulk_delete_employees',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menghapus ' . count($ids) . ' data pegawai secara massal'
        ]);

        return back()->with('success', count($ids) . ' data pegawai berhasil dihapus.');
    }
}
