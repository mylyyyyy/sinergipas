@extends('layouts.app')

@section('title', 'Master Aturan Payroll')
@section('header-title', 'Master Aturan & Perhitungan')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header Info -->
    <div class="bg-white p-8 md:p-10 rounded-[40px] border border-slate-200 shadow-sm card-3d relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32 opacity-40"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-widest border border-blue-100">
                    <i data-lucide="shield-check" class="w-3 h-3"></i> Konfigurasi Sistem
                </div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight">Aturan Main Payroll</h2>
                <p class="text-sm text-slate-500 font-medium max-w-xl">
                    Pusat pengaturan persentase potongan, kebijakan absensi, dan standar perhitungan finansial pegawai secara real-time.
                </p>
            </div>
            <div class="shrink-0">
                <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-xl flex items-center gap-5 border border-white/10">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10">
                        <i data-lucide="settings-2" class="w-6 h-6 text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status Sistem</p>
                        <h3 class="text-xl font-bold text-emerald-400">Terhubung Real-time</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.payroll-settings.update') }}" method="POST" class="space-y-8">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Section 1: TL & PSW -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <i data-lucide="clock-alert" class="w-5 h-5 text-amber-500"></i>
                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Keterlambatan & PSW (%)</h4>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">TL 1 / PSW 1 (1-30m)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="payroll_tl_1_percent" value="{{ $settings['tl_1'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">TL 2 / PSW 2 (31-60m)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="payroll_tl_2_percent" value="{{ $settings['tl_2'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">TL 3 / PSW 3 (61-90m)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="payroll_tl_3_percent" value="{{ $settings['tl_3'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">TL 4 / PSW 4 (>90m)</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="payroll_tl_4_percent" value="{{ $settings['tl_4'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2 pt-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Batas Maksimal Telat (Bulanan)</label>
                        <div class="relative">
                            <input type="number" name="payroll_max_late_count" value="{{ $settings['max_late'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">KALI</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Ketidakhadiran & Sakit -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <i data-lucide="user-x" class="w-5 h-5 text-red-500"></i>
                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Ketidakhadiran & Sakit (%)</h4>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Mangkir / Tanpa Keterangan</label>
                        <div class="relative">
                            <input type="number" step="0.1" name="payroll_mangkir_percent" value="{{ $settings['mangkir'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%/HARI</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Lupa Absen (Masuk/Pulang)</label>
                        <div class="relative">
                            <input type="number" step="0.1" name="payroll_lupa_absen_percent" value="{{ $settings['lupa_absen'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sakit Hari ke 3-6</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="payroll_sakit_3_6_percent" value="{{ $settings['sakit_3_6'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%/HARI</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sakit Hari ke 7+</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="payroll_sakit_7_plus_percent" value="{{ $settings['sakit_7'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">%/HARI</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Jam Kerja Reguler -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                    <i data-lucide="clock" class="w-5 h-5 text-indigo-500"></i>
                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Jam Kerja Reguler (Staff)</h4>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Masuk (Setiap Hari)</label>
                        <input type="time" name="payroll_staff_in" value="{{ $settings['staff_in'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Pulang (Sen-Kam)</label>
                            <input type="time" name="payroll_staff_out_mon_thu" value="{{ $settings['staff_out_mon_thu'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Pulang (Jumat)</label>
                            <input type="time" name="payroll_staff_out_fri" value="{{ $settings['staff_out_fri'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.15em]">Opsi Hari Sabtu</h5>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic text-blue-500">Aktifkan jika ada acara/jadwal khusus staff di hari Sabtu</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="payroll_staff_saturday_enabled" {{ $settings['staff_saturday_enabled'] === 'on' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-6 {{ $settings['staff_saturday_enabled'] === 'on' ? '' : 'opacity-40 grayscale pointer-events-none' }}" id="saturday-inputs">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Masuk (Sabtu)</label>
                                <input type="time" name="payroll_staff_saturday_in" value="{{ $settings['staff_saturday_in'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Pulang (Sabtu)</label>
                                <input type="time" name="payroll_staff_saturday_out" value="{{ $settings['staff_saturday_out'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-blue-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Bulan Puasa -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d lg:col-span-2">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i data-lucide="moon" class="w-5 h-5 text-emerald-500"></i>
                        <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Opsi Jam Kerja Ramadhan (Staff)</h4>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="payroll_ramadan_enabled" {{ $settings['ramadan_enabled'] === 'on' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>
                <div class="p-8 space-y-6 {{ $settings['ramadan_enabled'] === 'on' ? '' : 'opacity-40 grayscale pointer-events-none' }}" id="ramadan-inputs">
                    <p class="text-[10px] font-bold text-slate-500 italic mt-0">Aktifkan opsi ini jika memasuki bulan puasa. Aturan jam kerja ini akan otomatis menggantikan jam kerja reguler pada rentang tanggal yang ditentukan, dan sinkron dengan perhitungan Tunkin maupun absensi.</p>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Mulai Berlaku</label>
                            <input type="date" name="payroll_ramadan_start" value="{{ $settings['ramadan_start'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Berakhir Pada</label>
                            <input type="date" name="payroll_ramadan_end" value="{{ $settings['ramadan_end'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                        </div>
                    </div>
                    <div class="space-y-2 pt-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Masuk (Setiap Hari)</label>
                        <input type="time" name="payroll_ramadan_staff_in" value="{{ $settings['ramadan_staff_in'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Pulang (Sen-Kam)</label>
                            <input type="time" name="payroll_ramadan_staff_out_mon_thu" value="{{ $settings['ramadan_staff_out_mon_thu'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Pulang (Jumat)</label>
                            <input type="time" name="payroll_ramadan_staff_out_fri" value="{{ $settings['ramadan_staff_out_fri'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h5 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.15em]">Opsi Hari Sabtu (Bulan Puasa)</h5>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic text-emerald-500">Aktifkan jika ada jadwal masuk staff di hari Sabtu selama bulan puasa</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="payroll_ramadan_saturday_enabled" {{ $settings['ramadan_saturday_enabled'] === 'on' ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-6 {{ $settings['ramadan_saturday_enabled'] === 'on' ? '' : 'opacity-40 grayscale pointer-events-none' }}" id="ramadan-saturday-inputs">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Masuk (Sabtu Puasa)</label>
                                <input type="time" name="payroll_ramadan_saturday_in" value="{{ $settings['ramadan_saturday_in'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jam Pulang (Sabtu Puasa)</label>
                                <input type="time" name="payroll_ramadan_saturday_out" value="{{ $settings['ramadan_saturday_out'] }}" class="w-full px-6 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-sm font-black text-slate-700 outline-none focus:bg-white focus:border-emerald-500 transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button Row -->
        <div class="flex justify-end pt-4 pb-12">
            <button type="submit" class="px-12 py-5 rounded-[24px] bg-slate-900 text-white font-black text-xs uppercase tracking-[0.2em] hover:bg-blue-600 hover:shadow-2xl hover:shadow-blue-200 transition-all active:scale-95 flex items-center gap-3 group">
                <i data-lucide="save" class="w-4 h-4 group-hover:rotate-12 transition-transform"></i> Simpan Seluruh Aturan
            </button>
        </div>
    </form>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'success',
            title: 'Sinkronisasi Berhasil',
            text: "{{ session('success') }}",
            confirmButtonColor: '#0F172A',
            customClass: { popup: 'rounded-[32px]' }
        });
    });
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Saturday logic
        const satCheckbox = document.querySelector('input[name="payroll_staff_saturday_enabled"]');
        const satInputs = document.getElementById('saturday-inputs');

        satCheckbox.addEventListener('change', () => {
            if (satCheckbox.checked) {
                satInputs.classList.remove('opacity-40', 'grayscale', 'pointer-events-none');
            } else {
                satInputs.classList.add('opacity-40', 'grayscale', 'pointer-events-none');
            }
        });

        // Ramadan logic
        const ramadanCheckbox = document.querySelector('input[name="payroll_ramadan_enabled"]');
        const ramadanInputs = document.getElementById('ramadan-inputs');

        ramadanCheckbox.addEventListener('change', () => {
            if (ramadanCheckbox.checked) {
                ramadanInputs.classList.remove('opacity-40', 'grayscale', 'pointer-events-none');
            } else {
                ramadanInputs.classList.add('opacity-40', 'grayscale', 'pointer-events-none');
            }
        });

        // Ramadan Saturday logic
        const ramadanSatCheckbox = document.querySelector('input[name="payroll_ramadan_saturday_enabled"]');
        const ramadanSatInputs = document.getElementById('ramadan-saturday-inputs');

        ramadanSatCheckbox.addEventListener('change', () => {
            if (ramadanSatCheckbox.checked) {
                ramadanSatInputs.classList.remove('opacity-40', 'grayscale', 'pointer-events-none');
            } else {
                ramadanSatInputs.classList.add('opacity-40', 'grayscale', 'pointer-events-none');
            }
        });
    });
</script>
@endsection
