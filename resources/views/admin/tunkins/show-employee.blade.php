@extends('layouts.app')

@section('title', 'Detail Rekap Pegawai')
@section('header-title', 'Rincian Penghasilan')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Breadcrumb & Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tunkins.index', ['tab' => 'recap', 'month' => $monthStr]) }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors font-bold text-[10px] uppercase tracking-widest">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Rekap
        </a>
        <div class="flex gap-3">
            <a href="{{ route('admin.tunkins.employee.export', ['employee' => $employee->id, 'month' => $monthStr]) }}" class="px-6 py-3 rounded-xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2 shadow-lg">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Slip (PDF)
            </a>
            <button onclick="window.print()" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak Layar
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Employee Profile -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 p-8 text-center shadow-sm card-3d">
                <div class="relative w-32 h-32 mx-auto mb-6">
                    <div class="absolute inset-0 bg-blue-500/10 blur-2xl rounded-full"></div>
                    <div class="relative w-full h-full rounded-[40px] border-4 border-white shadow-xl overflow-hidden bg-slate-100">
                        <img src="{{ $employee->photo }}" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($employee->full_name) }}&background=F1F5F9&color=64748B'">
                    </div>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-1">{{ $employee->full_name }}</h3>
                <p class="text-[11px] font-bold text-slate-400 font-mono tracking-widest uppercase mb-6">NIP. {{ $employee->nip }}</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Kelas Jabatan</p>
                        <p class="text-sm font-black text-blue-600">{{ $employee->tunkin->grade ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Golongan</p>
                        <p class="text-sm font-black text-amber-600">{{ $employee->rank_relation->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 rounded-[40px] p-8 text-white shadow-2xl relative overflow-hidden card-3d">
                <div class="absolute -right-4 -bottom-4 opacity-10">
                    <i data-lucide="wallet" class="w-32 h-32"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-4">Take Home Pay (Estimasi)</p>
                    <h2 class="text-3xl font-black mb-2">Rp {{ number_format($baseTunkin + $totalMealAllowance - $potongan, 0, ',', '.') }}</h2>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Periode {{ $date->translatedFormat('F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Right: Detailed Components -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Financial Summary Card -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Rincian Komponen Gaji</h4>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[9px] font-black uppercase rounded-full tracking-widest">Real-time Sync</span>
                </div>
                <div class="p-8 space-y-6">
                    <!-- Tunkin -->
                    <div class="flex items-center justify-between p-6 rounded-3xl bg-slate-50 border border-slate-100 group hover:bg-white hover:shadow-xl hover:border-blue-200 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <i data-lucide="coins" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-slate-900">Tunjangan Kinerja Dasar</h5>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Kelas Jabatan {{ $employee->tunkin->grade ?? '-' }}</p>
                                    @if($employee->is_cpns)
                                        <span class="px-2 py-0.5 bg-slate-900 text-white text-[8px] font-black rounded uppercase tracking-tighter">CPNS 80%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-base font-black text-slate-900">Rp {{ number_format($base_tunkin, 0, ',', '.') }}</span>
                            @if($employee->is_cpns)
                                <p class="text-[9px] font-bold text-slate-400 mt-1 italic">* Pagu CPNS 80% dari Rp {{ number_format($employee->tunkin->nominal ?? 0, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Meal Allowance -->
                    <div class="flex items-center justify-between p-6 rounded-3xl bg-slate-50 border border-slate-100 group hover:bg-white hover:shadow-xl hover:border-emerald-200 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <i data-lucide="utensils" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-slate-900">Uang Makan</h5>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $meal_allowance_days }} Hari Hadir &times; Rp {{ number_format($mealAllowancePerDay, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <span class="text-base font-black text-slate-900">Rp {{ number_format($total_meal_allowance, 0, ',', '.') }}</span>
                    </div>

                    <!-- Deductions -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-6 rounded-3xl bg-red-50/50 border border-red-100 group hover:bg-white hover:shadow-xl hover:border-red-200 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-all">
                                    <i data-lucide="trending-down" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h5 class="text-sm font-black text-red-900">Total Potongan ({{ number_format($deduction_percentage, 2) }}%)</h5>
                                    <p class="text-[10px] font-bold text-red-400 uppercase">Berdasarkan Akumulasi Kedisiplinan</p>
                                </div>
                            </div>
                            <span class="text-base font-black text-red-600">- Rp {{ number_format($total_potongan_rupiah, 0, ',', '.') }}</span>
                        </div>

                        <!-- Deduction Details Breakdown -->
                        @if(count($details) > 0)
                        <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-xs">
                            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Daftar Rincian Pelanggaran Absensi</span>
                                <span class="text-[9px] font-bold text-slate-400">{{ count($details) }} Kejadian</span>
                            </div>
                            <div class="divide-y divide-slate-50 max-h-60 overflow-y-auto custom-scrollbar">
                                @foreach($details as $detail)
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-2 h-2 rounded-full {{ $detail['percent'] >= 5 ? 'bg-red-500' : 'bg-amber-500' }}"></div>
                                        <div>
                                            <p class="text-xs font-black text-slate-800">{{ $detail['type'] }} ({{ $detail['info'] }})</p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($detail['date'])->translatedFormat('d F Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-black text-red-600">- Rp {{ number_format($detail['rupiah'], 0, ',', '.') }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 text-red-500/50">{{ $detail['percent'] }}%</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Attendance Preview Table -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
                    <i data-lucide="calendar-check" class="w-4 h-4 text-blue-600"></i>
                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.2em]">Log Kehadiran (Dasar Uang Makan)</h4>
                </div>
                <div class="overflow-x-auto max-h-80 custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white z-10 shadow-xs">
                            <tr>
                                <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Scan Masuk/Pulang</th>
                                <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Jadwal</th>
                                <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Uang Makan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($processed_logs as $log)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-8 py-4">
                                    <p class="text-sm font-bold text-slate-700 leading-none mb-1">{{ \Carbon\Carbon::parse($log['date'])->format('d/m/Y') }}</p>
                                    <span class="px-2 py-0.5 {{ $log['status'] === 'present' || $log['status'] === 'late' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} text-[8px] font-black uppercase rounded border border-current opacity-70">
                                        {{ $log['status'] === 'present' ? 'Hadir' : ($log['status'] === 'late' ? 'Telat' : (str_contains($log['status'], 'absent') ? 'Mangkir' : $log['status'])) }}
                                    </span>
                                </td>
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-2 text-[10px] font-bold">
                                        <div class="px-2 py-1 bg-slate-50 rounded border border-slate-100 min-w-[50px] text-center">
                                            <span class="text-[8px] text-slate-400 block uppercase font-black">Masuk</span>
                                            {{ $log['check_in'] }}
                                        </div>
                                        <div class="px-2 py-1 bg-slate-50 rounded border border-slate-100 min-w-[50px] text-center">
                                            <span class="text-[8px] text-slate-400 block uppercase font-black">Pulang</span>
                                            {{ $log['check_out'] }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    @if($log['is_scheduled'])
                                        <span class="text-[9px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase tracking-tighter">Valid</span>
                                    @else
                                        <span class="text-[9px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full uppercase tracking-tighter">Luar Jadwal</span>
                                    @endif
                                </td>
                                <td class="px-8 py-4 text-sm font-black text-slate-900 text-right">
                                    @if($log['meal_amount'] > 0)
                                        Rp {{ number_format($log['meal_amount'], 0, ',', '.') }}
                                    @else
                                        <span class="text-slate-300">Rp 0</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
