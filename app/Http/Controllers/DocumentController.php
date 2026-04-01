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
            $categories = DocumentCategory::withCount('documents')->get();
            $query = Employee::withCount(['documents' => function($q) use ($request) {
                if ($request->filled('category_id')) { $q->where('document_category_id', $request->category_id); }
            }]);
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('full_name', 'like', "%$search%")->orWhere('nip', 'like', "%$search%");
            }
            if ($request->filled('category_id')) {
                $query->whereHas('documents', function($q) use ($request) { $q->where('document_category_id', $request->category_id); });
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

    public function preview(Document $document)
    {
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) abort(403);
        }
        
        $path = storage_path('app/private/' . $document->file_path);
        if (!file_exists($path)) abort(404);

        $mimeType = mime_content_type($path);
        
        // Use stream response for better "inline" behavior
        return response()->make(file_get_contents($path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->title . '"'
        ]);
    }

    public function verify(Document $document)
    {
        $document->update([
            'status' => 'verified',
            'verified_at' => now(),
            'is_locked' => true // Auto-lock when verified
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'document_id' => $document->id,
            'activity' => 'verify_document',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' memverifikasi dokumen: ' . $document->title
        ]);

        return back()->with('success', 'Dokumen berhasil diverifikasi dan dikunci.');
    }

    public function toggleLock(Document $document)
    {
        $document->is_locked = !$document->is_locked;
        $document->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'document_id' => $document->id,
            'activity' => $document->is_locked ? 'lock_document' : 'unlock_document',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ($document->is_locked ? ' mengunci ' : ' membuka ') . 'dokumen: ' . $document->title
        ]);

        return back()->with('success', $document->is_locked ? 'Dokumen berhasil dikunci.' : 'Kunci dokumen dibuka.');
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
            $employee = Employee::find($employeeId);
        } else {
            $employee = Employee::where('user_id', $user->id)->first();
            $employeeId = $employee->id;
        }

        $path = $request->file('file')->store('documents', 'private');

        $doc = Document::create([
            'employee_id' => $employeeId,
            'document_category_id' => $request->document_category_id,
            'title' => $request->title,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'document_id' => $doc->id,
            'activity' => $user->role === 'superadmin' ? 'upload_admin' : 'upload_pegawai',
            'ip_address' => $request->ip(),
            'details' => $user->name . ' mengunggah dokumen: ' . $doc->title
        ]);

        if ($user->role === 'superadmin') {
            $doc->employee->user->notify(new \App\Notifications\NewDocumentNotification($doc));
        } else {
            $admins = \App\Models\User::where('role', 'superadmin')->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\DocumentUploadedNotification($doc, $employee));
        }

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Document $document)
    {
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) abort(403);
        }
        AuditLog::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'activity' => 'download',
            'ip_address' => request()->ip(),
            'details' => $user->name . ' mengunduh dokumen: ' . $document->title
        ]);
        return Storage::disk('private')->download($document->file_path);
    }

    public function destroy(Document $document)
    {
        $user = auth()->user();
        if ($document->is_locked && $user->role !== 'superadmin') {
            return back()->with('error', 'Dokumen ini telah dikunci oleh Admin.');
        }
        if ($user->role !== 'superadmin') {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($document->employee_id !== $employee?->id) abort(403);
        }
        
        AuditLog::create([
            'user_id' => $user->id,
            'activity' => 'delete_document',
            'ip_address' => request()->ip(),
            'details' => $user->name . ' menghapus dokumen: ' . $document->title
        ]);

        Storage::disk('private')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:document_categories,name']);
        DocumentCategory::create(['name' => $request->name, 'slug' => \Illuminate\Support\Str::slug($request->name)]);
        return back()->with('success', 'Kategori baru ditambahkan.');
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;
        if (empty($ids)) return back()->with('error', 'Tidak ada data terpilih.');

        if ($action === 'delete') {
            foreach (Document::whereIn('id', $ids)->get() as $doc) {
                if (auth()->user()->role === 'superadmin' || !$doc->is_locked) {
                    Storage::disk('private')->delete($doc->file_path);
                    $doc->delete();
                }
            }
            $msg = 'Dokumen terpilih berhasil dihapus.';
        } elseif ($action === 'lock') {
            Document::whereIn('id', $ids)->update(['is_locked' => true]);
            $msg = 'Dokumen terpilih berhasil dikunci.';
        } elseif ($action === 'unlock') {
            Document::whereIn('id', $ids)->update(['is_locked' => false]);
            $msg = 'Kunci dokumen terpilih dibuka.';
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'bulk_' . $action,
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' melakukan aksi massal ' . $action
        ]);

        return back()->with('success', $msg);
    }
}
