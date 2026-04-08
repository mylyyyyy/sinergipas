<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('employees')->orderBy('name')->get();
        $unassignedEmployees = Employee::whereNull('category_id')->orderBy('full_name')->get();
        
        return view('admin.categories.index', compact('categories', 'unassignedEmployees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'color' => $request->color,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_category',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " membuat kategori pegawai baru: " . $category->name
        ]);

        return back()->with('success', 'Kategori berhasil dibuat.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'color' => 'required|string',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'color' => $request->color,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_category',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " memperbarui kategori: " . $category->name
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $name = $category->name;
        
        // Unassign employees
        Employee::where('category_id', $category->id)->update(['category_id' => null]);
        
        $category->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_category',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . " menghapus kategori: " . $name
        ]);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function addMember(Request $request, Category $category)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        Employee::whereIn('id', $request->employee_ids)->update(['category_id' => $category->id]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'add_category_member',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " menambahkan anggota ke kategori: " . $category->name
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan ke kategori.');
    }

    public function removeMember(Request $request, Category $category)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        Employee::where('id', $request->employee_id)
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'remove_category_member',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " mengeluarkan anggota dari kategori: " . $category->name
        ]);

        return back()->with('success', 'Anggota berhasil dikeluarkan dari kategori.');
    }

    public function removeMembersBulk(Request $request, Category $category)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id'
        ]);

        Employee::whereIn('id', $request->employee_ids)
            ->where('category_id', $category->id)
            ->update(['category_id' => null]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'bulk_remove_category_members',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . " mengeluarkan " . count($request->employee_ids) . " anggota dari kategori: " . $category->name
        ]);

        return back()->with('success', 'Anggota terpilih berhasil dikeluarkan dari kategori.');
    }
}
