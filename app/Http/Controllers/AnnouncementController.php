<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'type' => 'required|in:banner,popup',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $ann = Announcement::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'type' => $request->type,
            'is_active' => true,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
        ]);

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'activity' => 'create_announcement',
            'ip_address' => $request->ip(),
            'details' => auth()->user()->name . ' menyiarkan pengumuman (' . $request->type . '): ' . substr($ann->message, 0, 50) . '...'
        ]);

        return back()->with('success', 'Pengumuman berhasil dijadwalkan.');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);
        
        return back()->with('success', 'Status pengumuman diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Pengumuman dihapus.');
    }
}
