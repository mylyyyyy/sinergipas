<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Employee;
use App\Models\DocumentCategory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role === 'superadmin') {
            // Load Categories with Document Counts
            $categories = DocumentCategory::withCount('documents')->get();

            $query = Employee::withCount(['documents' => function($q) use ($request) {
                if ($request->filled('category_id')) {
                    $q->where('document_category_id', $request->category_id);
                }
            }]);
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('full_name', 'like', "%$search%")
                      ->orWhere('nip', 'like', "%$search%");
            }

            // If category filter is active, only show employees who HAVE documents in that category
            if ($request->filled('category_id')) {
                $query->whereHas('documents', function($q) use ($request) {
                    $q->where('document_category_id', $request->category_id);
                });
            }

            $employees = $query->get();
            return view('documents.index', compact('employees', 'categories'));
        } else {
            $employee = Employee::where('user_id', $user->id)->first();
            $documents = Document::where('employee_id', $employee?->id)->with('category')->latest()->get();
            $categories = DocumentCategory::all();
            return view('documents.pegawai-index', compact('documents', 'categories', 'employee'));
        }
    }

    public function showEmployeeFolders(Employee $employee)
    {
        $documents = Document::where('employee_id', $employee->id)->with('category')->latest()->get();
        $categories = DocumentCategory::all();
        return view('documents.show-folder', compact('employee', 'documents', 'categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'document_category_id' => 'required|exists:document_categories,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
        ]);

        if ($user->role === 'superadmin') {
            $request->validate(['employee_id' => 'required|exists:employees,id']);
            $employeeId = $request->employee_id;
        } else {
            $employee = Employee::where('user_id', $user->id)->first();
            $employeeId = $employee->id;
        }

        $path = $request->file('file')->store('documents');

        $existingDoc = Document::where('employee_id', $employeeId)
            ->where('title', $request->title)
            ->first();

        if ($existingDoc) {
            $latestVersionNumber = \App\Models\DocumentVersion::where('document_id', $existingDoc->id)->max('version_number') ?? 0;
            \App\Models\DocumentVersion::create([
                'document_id' => $existingDoc->id,
                'file_path' => $existingDoc->file_path,
                'version_number' => $latestVersionNumber + 1,
            ]);
            $existingDoc->update(['file_path' => $path]);
            $doc = $existingDoc;
        } else {
            $doc = Document::create([
                'employee_id' => $employeeId,
                'document_category_id' => $request->document_category_id,
                'title' => $request->title,
                'file_path' => $path,
                'description' => $request->description,
            ]);
        }

        if ($user->role === 'superadmin') {
            $doc->employee->user->notify(new \App\Notifications\NewDocumentNotification($doc));
        }

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Document $document)
    {
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        AuditLog::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'activity' => 'download',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::download($document->file_path, $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(Document $document)
    {
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        Storage::delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:document_categories,name']);
        DocumentCategory::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
        ]);
        return back()->with('success', 'Kategori baru ditambahkan.');
    }
}
