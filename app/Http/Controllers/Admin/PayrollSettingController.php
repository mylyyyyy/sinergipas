<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PayrollSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'tl_1' => Setting::getValue('payroll_tl_1_percent', 0.5),
            'tl_2' => Setting::getValue('payroll_tl_2_percent', 1.0),
            'tl_3' => Setting::getValue('payroll_tl_3_percent', 1.25),
            'tl_4' => Setting::getValue('payroll_tl_4_percent', 1.5),
            'max_late' => Setting::getValue('payroll_max_late_count', 8),
            'mangkir' => Setting::getValue('payroll_mangkir_percent', 5.0),
            'lupa_absen' => Setting::getValue('payroll_lupa_absen_percent', 1.5),
            'sakit_3_6' => Setting::getValue('payroll_sakit_3_6_percent', 2.5),
            'sakit_7' => Setting::getValue('payroll_sakit_7_plus_percent', 10.0),
            'apel' => Setting::getValue('payroll_apel_percent', 0.5),
            
            // Jam Kerja Staff (Reguler)
            'staff_in' => Setting::getValue('payroll_staff_in', '07:30'),
            'staff_out_mon_thu' => Setting::getValue('payroll_staff_out_mon_thu', '16:00'),
            'staff_out_fri' => Setting::getValue('payroll_staff_out_fri', '16:30'),

            // Jam Kerja Staff (Sabtu - Opsional)
            'staff_saturday_enabled' => Setting::getValue('payroll_staff_saturday_enabled', 'off'),
            'staff_saturday_in' => Setting::getValue('payroll_staff_saturday_in', '07:30'),
            'staff_saturday_out' => Setting::getValue('payroll_staff_saturday_out', '12:00'),

            // Jam Kerja Staff (Bulan Puasa - Opsional)
            'ramadan_enabled' => Setting::getValue('payroll_ramadan_enabled', 'off'),
            'ramadan_start' => Setting::getValue('payroll_ramadan_start', date('Y-m-d')),
            'ramadan_end' => Setting::getValue('payroll_ramadan_end', date('Y-m-d')),
            'ramadan_staff_in' => Setting::getValue('payroll_ramadan_staff_in', '08:00'),
            'ramadan_staff_out_mon_thu' => Setting::getValue('payroll_ramadan_staff_out_mon_thu', '15:00'),
            'ramadan_staff_out_fri' => Setting::getValue('payroll_ramadan_staff_out_fri', '15:30'),

            // Jam Kerja Staff (Bulan Puasa - Sabtu)
            'ramadan_saturday_enabled' => Setting::getValue('payroll_ramadan_saturday_enabled', 'off'),
            'ramadan_saturday_in' => Setting::getValue('payroll_ramadan_saturday_in', '08:00'),
            'ramadan_saturday_out' => Setting::getValue('payroll_ramadan_saturday_out', '12:00'),
        ];

        return view('admin.settings.payroll', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'payroll_tl_1_percent' => 'required|numeric|min:0',
            'payroll_tl_2_percent' => 'required|numeric|min:0',
            'payroll_tl_3_percent' => 'required|numeric|min:0',
            'payroll_tl_4_percent' => 'required|numeric|min:0',
            'payroll_max_late_count' => 'required|integer|min:0',
            'payroll_mangkir_percent' => 'required|numeric|min:0',
            'payroll_lupa_absen_percent' => 'required|numeric|min:0',
            'payroll_sakit_3_6_percent' => 'required|numeric|min:0',
            'payroll_sakit_7_plus_percent' => 'required|numeric|min:0',
            'payroll_apel_percent' => 'required|numeric|min:0',
            'payroll_staff_in' => 'required|string',
            'payroll_staff_out_mon_thu' => 'required|string',
            'payroll_staff_out_fri' => 'required|string',
            'payroll_staff_saturday_enabled' => 'nullable|string',
            'payroll_staff_saturday_in' => 'required_if:payroll_staff_saturday_enabled,on|string',
            'payroll_staff_saturday_out' => 'required_if:payroll_staff_saturday_enabled,on|string',
            
            'payroll_ramadan_enabled' => 'nullable|string',
            'payroll_ramadan_start' => 'required_if:payroll_ramadan_enabled,on|date',
            'payroll_ramadan_end' => 'required_if:payroll_ramadan_enabled,on|date',
            'payroll_ramadan_staff_in' => 'required_if:payroll_ramadan_enabled,on|string',
            'payroll_ramadan_staff_out_mon_thu' => 'required_if:payroll_ramadan_enabled,on|string',
            'payroll_ramadan_staff_out_fri' => 'required_if:payroll_ramadan_enabled,on|string',

            'payroll_ramadan_saturday_enabled' => 'nullable|string',
            'payroll_ramadan_saturday_in' => 'required_if:payroll_ramadan_saturday_enabled,on|string',
            'payroll_ramadan_saturday_out' => 'required_if:payroll_ramadan_saturday_enabled,on|string',
        ]);

        // Handle checkboxes
        if (!isset($data['payroll_staff_saturday_enabled'])) {
            $data['payroll_staff_saturday_enabled'] = 'off';
        }
        if (!isset($data['payroll_ramadan_enabled'])) {
            $data['payroll_ramadan_enabled'] = 'off';
        }
        if (!isset($data['payroll_ramadan_saturday_enabled'])) {
            $data['payroll_ramadan_saturday_enabled'] = 'off';
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Aturan perhitungan payroll berhasil diperbarui.');
    }
}
