<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        $logs = \App\Models\AuditLog::where('user_id', $user->id)->with('document')->latest()->take(10)->get();
        $myIssues = \App\Models\ReportIssue::where('user_id', $user->id)->latest()->get();
        return view('profile.index', compact('user', 'employee', 'logs', 'myIssues'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update(['name' => $request->name]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $path = $image->store('photos', 'public');
            
            if ($employee) {
                // Delete old photo if it exists and is a file path (not base64)
                if ($employee->getRawOriginal('photo') && !str_starts_with($employee->getRawOriginal('photo'), 'data:image')) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->getRawOriginal('photo'));
                }
                
                $employee->update(['photo' => $path]);
            }
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function report(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        \App\Models\ReportIssue::create([
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Laporan Anda telah terkirim ke Admin.');
    }
}
