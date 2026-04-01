<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();
        $logs = \App\Models\AuditLog::where('user_id', $user->id)->with('document')->latest()->take(10)->get();
        return view('profile.index', compact('user', 'employee', 'logs'));
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
            if ($employee && $employee->photo) {
                Storage::delete($employee->photo);
            }
            $path = $request->file('photo')->store('photos');
            $employee->update(['photo' => $path]);
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
