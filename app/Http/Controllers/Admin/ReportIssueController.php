<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportIssue;
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

        return back()->with('success', 'Laporan berhasil diperbarui dan notifikasi terkirim.');
    }

    public function destroy(ReportIssue $issue)
    {
        $issue->delete();
        return back()->with('success', 'Laporan berhasil dihapus.');
    }
}
