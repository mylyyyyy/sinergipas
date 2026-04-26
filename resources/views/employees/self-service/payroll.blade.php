@extends('layouts.app')

@section('title', 'Tunjangan Kinerja Saya')
@section('header-title', 'Rincian Penghasilan')

@section('content')
<div class="space-y-8 page-fade">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <form action="{{ route('my.payroll') }}" method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm w-full md:w-auto">
            <i data-lucide="calendar" class="w-4 h-4 text-slate-400 ml-2"></i>
            <input type="month" name="month" value="{{ $monthStr }}" onchange="this.form.submit()" class="px-4 py-2 rounded-xl bg-slate-50 border-none text-sm font-black text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none">
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Summary Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-slate-900 rounded-[40px] p-10 text-white shadow-2xl relative overflow-hidden card-3d">
                <div class="absolute -right-8 -bottom-8 opacity-10">
                    <i data-lucide="wallet" class="w-48 h-48"></i>
                </div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-6">Total Take Home Pay</p>
                    <h2 class="text-4xl font-black mb-2">Rp {{ number_format($grand_total, 0, ',', '.') }}</h2>
                    <div class="mt-8 pt-8 border-t border-white/10 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/5 flex items-center justify-center border border-white/10">
                            <i data-lucide="calendar-days" class="w-6 h-6 text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1">Periode Laporan</p>
                            <p class="text-sm font-black">{{ $date->translatedFormat('F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Mini Cards -->
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm flex items-center gap-5 card-3d">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Kehadiran Valid</p>
                        <h4 class="text-lg font-black text-slate-900">{{ $meal_allowance_days }} Hari</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[32px] border border-slate-200 shadow-sm flex items-center gap-5 card-3d">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Pelanggaran</p>
                        <h4 class="text-lg font-black text-slate-900">{{ count($details) }} Kejadian</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Breakdown -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="px-10 py-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.2em]">Rincian Komponen Gaji</h4>
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[9px] font-black uppercase rounded-full tracking-widest">Sinkronisasi Aktif</span>
                </div>
                
                <div class="p-10 space-y-6">
                    <!-- Base Tunkin -->
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
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $meal_allowance_days }} Hari Hadir &times; Rp {{ number_format($employee->rank_relation->meal_allowance ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <span class="text-base font-black text-slate-900">Rp {{ number_format($total_meal_allowance, 0, ',', '.') }}</span>
                    </div>

                    <!-- Deductions Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-6 rounded-3xl bg-red-50/50 border border-red-100 group hover:bg-white hover:shadow-xl hover:border-red-200 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center group-hover:bg-red-600 group-hover:text-white transition-all">
                                    <i data-lucide="trending-down" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h5 class="text-sm font-black text-red-900">Potongan Kedisiplinan</h5>
                                    <p class="text-[10px] font-bold text-red-400 uppercase">Total Akumulasi: {{ number_format($deduction_percentage, 2) }}%</p>
                                </div>
                            </div>
                            <span class="text-base font-black text-red-600">- Rp {{ number_format($total_potongan_rupiah, 0, ',', '.') }}</span>
                        </div>

                        <!-- Mini Violation Log -->
                        @if(count($details) > 0)
                        <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-xs">
                            <div class="divide-y divide-slate-50 max-h-64 overflow-y-auto custom-scrollbar">
                                @foreach($details as $detail)
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center gap-4 text-left">
                                        <div class="w-2 h-2 rounded-full {{ $detail['percent'] >= 5 ? 'bg-red-500' : 'bg-amber-500' }}"></div>
                                        <div>
                                            <p class="text-xs font-black text-slate-800">{{ $detail['type'] }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $detail['date'] ? \Carbon\Carbon::parse($detail['date'])->translatedFormat('d F Y') : $detail['info'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-black text-red-600">-{{ $detail['percent'] }}%</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="p-10 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            <i data-lucide="shield-check" class="w-10 h-10 text-emerald-400 mx-auto mb-3"></i>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Luar Biasa! Tidak ada potongan bulan ini.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
