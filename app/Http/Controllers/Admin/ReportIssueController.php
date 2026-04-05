<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportIssue;
use App\Models\AuditLog;
use Illuminate\Http\Request;

use App\Notifications\IssueRepliedNotification;

class ReportIssueController extends Controller
{
    public function index()
    {
        $issues = ReportIssue::with('user')->latest()->paginate(10);
        return view('admin.report-issues.index', compact('issues'));
    }

    public function update(Request $request, ReportIssue $issue)
    {
        $request->validate([
            'status' => 'required|in:open,resolved,closed',
            'admin_note' => 'nullable|string'
        ]);

        $issue->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note
        ]);

        // Send notification to the user
        $issue->user->notify(new IssueRepliedNotification($issue));

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'update_report_issue',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' memperbarui laporan masalah: ' . $issue->subject,
        ]);

        return back()->with('success', 'Laporan berhasil diperbarui dan notifikasi terkirim.');
    }

    public function destroy(ReportIssue $issue)
    {
        $subject = $issue->subject;
        $issue->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_report_issue',
            'ip_address' => request()->ip(),
            'details' => auth()->user()->name . ' menghapus laporan masalah: ' . $subject,
        ]);

        return back()->with('success', 'Laporan berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:report_issues,id'],
        ]);

        $issues = ReportIssue::whereIn('id', $validated['ids'])->get();

        if ($issues->isEmpty()) {
            return back()->with('error', 'Tidak ada laporan valid yang dipilih.');
        }

        $count = $issues->count();
        ReportIssue::whereIn('id', $issues->pluck('id'))->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'bulk_delete_report_issues',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menghapus ' . $count . ' laporan masalah secara massal',
        ]);

        return back()->with('success', $count . ' laporan berhasil dihapus.');
    }

    public function destroyAll(Request $request)
    {
        $count = ReportIssue::count();

        if ($count === 0) {
            return back()->with('error', 'Tidak ada laporan untuk dihapus.');
        }

        ReportIssue::query()->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'delete_all_report_issues',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menghapus seluruh laporan masalah (' . $count . ' data)',
        ]);

        return back()->with('success', 'Seluruh laporan masalah berhasil dihapus.');
    }
}
