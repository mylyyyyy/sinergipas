<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Announcement;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        $announcements = Announcement::with('user')->latest()->get();
        return view('settings.index', compact('settings', 'announcements'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
