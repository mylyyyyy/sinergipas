@extends('layouts.app')

@section('title', 'Profil Pegawai')
@section('header-title', 'Detail & Riwayat Personel')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors font-bold text-[10px] uppercase tracking-widest">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ $employee->whatsapp_link }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-green-50 text-green-600 border border-green-100 font-bold text-[10px] uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all flex items-center gap-2 shadow-sm">
                <i data-lucide="message-circle" class="w-4 h-4"></i> Hubungi WhatsApp
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Profile Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="h-32 bg-slate-900 relative">
                    <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                        @php
                            $isKalapas = strtoupper($employee->position) === 'KEPALA LEMBAGA PEMASYARAKATAN';
                        @endphp
                        <div class="relative w-24 h-24 rounded-3xl bg-white border-4 {{ $isKalapas ? 'border-amber-400 shadow-[0_0_30px_rgba(251,191,36,0.5)]' : 'border-white' }} shadow-xl overflow-visible flex items-center justify-center text-slate-300 font-black text-2xl">
                            <div class="w-full h-full rounded-3xl overflow-hidden flex items-center justify-center">
                                @if($employee->photo)
                                    <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($employee->full_name, 0, 1) }}
                                @endif
                            </div>

                            @if($isKalapas)
                                <!-- Top Center: Crown Icon -->
                                <div class="absolute -top-5 left-1/2 -translate-x-1/2 z-30">
                                    <div class="bg-linear-to-b from-amber-300 to-yellow-600 p-1.5 rounded-full shadow-xl border-2 border-white animate-pulse">
                                        <i data-lucide="crown" class="w-5 h-5 text-slate-900"></i>
                                    </div>
                                </div>
                                <!-- Bottom Center: Text Label -->
                                <div class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 z-30 whitespace-nowrap">
                                    <div class="bg-slate-900 text-amber-400 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-2xl border-2 border-amber-500/50">
                                        KALAPAS
                                    </div>
                                </div>
                            @elseif($employee->is_cpns)
                                <div class="absolute -top-2 -right-2 px-2 py-1 bg-slate-900 text-white text-[8px] font-black uppercase rounded-lg shadow-lg border border-slate-700 z-10">
                                    CPNS
                                </div>
                            @endif

                            @if($employee->squad && !$isKalapas)
                                <div class="absolute -bottom-2 -right-2 min-w-[32px] h-8 px-2 bg-{{ $employee->squad->type === 'p2u' ? 'emerald' : 'blue' }}-600 border-4 border-white rounded-xl flex items-center justify-center shadow-lg">
                                    <span class="text-[10px] font-black text-white whitespace-nowrap">{{ $employee->squad->type === 'p2u' ? 'P2U' : '' }} {{ str_replace('Regu ', '', $employee->squad->name) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pt-16 pb-8 px-8 text-center border-b border-slate-50">
                    <h3 class="text-xl font-black text-slate-900 italic leading-tight">{{ $employee->full_name }}</h3>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-[0.2em] mt-1">NIP. {{ $employee->nip }}</p>
                    
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-[9px] font-black uppercase tracking-widest text-slate-500">
                            <span class="h-1.5 w-1.5 rounded-full {{ $employee->employee_type === 'regu_jaga' ? 'bg-indigo-500' : 'bg-emerald-500' }}"></span>
                            {{ $employee->employee_type_label }}
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Informasi Kontak</h4>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-blue-500 shadow-sm">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Email Instansi</p>
                                    <p class="text-xs font-black text-slate-700 truncate">{{ $employee->user->email ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-green-500 shadow-sm">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Nomor Telepon</p>
                                    <p class="text-xs font-black text-slate-700 truncate">{{ $employee->phone_number ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Detail Identitas</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">NIK (KTP)</span>
                                <span class="text-xs font-black text-slate-700">{{ $employee->nik ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jabatan</span>
                                <span class="text-xs font-black text-slate-700 uppercase text-right">{{ $employee->position }}</span>
                            </div>
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Golongan</span>
                                <span class="text-xs font-black text-slate-700 uppercase">{{ $employee->rank_class ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</span>
                                <span class="text-xs font-black text-slate-700 uppercase">{{ $employee->work_unit->name ?? '-' }}</span>
                            </div>
                            @if($employee->squad)
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Penugasan</span>
                                <span class="px-2 py-0.5 rounded-lg bg-{{ $employee->squad->type === 'p2u' ? 'emerald' : 'indigo' }}-50 text-{{ $employee->squad->type === 'p2u' ? 'emerald' : 'indigo' }}-600 text-[10px] font-black uppercase">{{ $employee->squad->name }} [{{ strtoupper($employee->squad->type) }}]</span>
                            </div>
                            @endif
                            <div class="h-px bg-slate-100 my-2"></div>
                            @if($employee->is_cpns)
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status Khusus</span>
                                <span class="px-2 py-0.5 rounded-lg bg-slate-900 text-white text-[10px] font-black uppercase">CPNS (80%)</span>
                            </div>
                            @endif
                            @if($employee->is_tubel)
                            <div class="flex justify-between items-center px-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status Khusus</span>
                                <span class="px-2 py-0.5 rounded-lg bg-amber-500 text-white text-[10px] font-black uppercase">Tugas Belajar</span>
                            </div>
                            @endif
                            @if($employee->actingTunkin)
                            <div class="space-y-2 mt-2">
                                <div class="flex justify-between items-center px-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jabatan Plt/Plh</span>
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-600 text-white text-[10px] font-black uppercase">Grade {{ $employee->actingTunkin->grade }} (+20%)</span>
                                </div>
                                @if($employee->acting_start_date)
                                <div class="flex justify-between items-center px-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mulai Sejak</span>
                                    <span class="text-xs font-black text-slate-700 uppercase">{{ \Carbon\Carbon::parse($employee->acting_start_date)->translatedFormat('d F Y') }}</span>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Tabs (History) -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full min-h-[600px] card-3d">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.3em] flex items-center gap-3">
                        <i data-lucide="history" class="w-5 h-5 text-blue-600"></i>
                        Riwayat Perubahan Data
                    </h3>
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[9px] font-black uppercase tracking-widest">{{ $history->count() }} Records</span>
                </div>
                
                <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
                    @if($history->isEmpty())
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-40 py-20">
                            <i data-lucide="database-zap" class="w-12 h-12 mb-4"></i>
                            <p class="text-xs font-bold uppercase tracking-widest">Belum ada riwayat tercatat</p>
                        </div>
                    @else
                        <div class="relative space-y-8">
                            <div class="absolute left-4 top-2 bottom-2 w-px bg-slate-100"></div>
                            @foreach($history as $log)
                            <div class="relative pl-12 group">
                                <div class="absolute left-2.5 top-1.5 w-3 h-3 rounded-full border-2 border-white bg-blue-600 group-hover:scale-125 transition-transform shadow-sm ring-4 ring-slate-50"></div>
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-3xl border border-slate-100 bg-slate-50/30 hover:bg-white hover:shadow-xl hover:border-blue-200 transition-all">
                                    <div class="space-y-2 flex-1">
                                        <div class="flex items-center gap-3">
                                            <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">
                                                @if($log->activity === 'create_employee') Pendaftaran Awal 
                                                @elseif($log->activity === 'update_employee') Pembaruan Profil
                                                @else Aktivitas Sistem @endif
                                            </span>
                                            <span class="text-[9px] font-bold text-slate-400 italic">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-500 leading-relaxed">{{ $log->details }}</p>
                                        
                                        @if($log->old_values && $log->new_values)
                                        <div class="mt-4 p-4 rounded-2xl bg-slate-900/5 border border-slate-100 space-y-3">
                                            @php
                                                $keysToTrack = ['position', 'rank_class', 'work_unit_id', 'squad_id', 'employee_type', 'phone_number', 'nik'];
                                                $labels = [
                                                    'position' => 'Jabatan',
                                                    'rank_class' => 'Golongan',
                                                    'work_unit_id' => 'ID Unit',
                                                    'squad_id' => 'Regu/P2U',
                                                    'employee_type' => 'Tipe',
                                                    'phone_number' => 'No. Telp',
                                                    'nik' => 'NIK'
                                                ];
                                            @endphp
                                            @foreach($keysToTrack as $key)
                                                @if(isset($log->old_values[$key]) && isset($log->new_values[$key]) && $log->old_values[$key] != $log->new_values[$key])
                                                <div class="flex items-center gap-3 text-[9px] font-bold">
                                                    <span class="text-slate-400 w-16 uppercase">{{ $labels[$key] ?? $key }}</span>
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded-lg bg-red-50 text-red-500 line-through decoration-red-300">{{ $log->old_values[$key] }}</span>
                                                        <i data-lucide="arrow-right" class="w-3 h-3 text-slate-300"></i>
                                                        <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-600">{{ $log->new_values[$key] }}</span>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    <div class="shrink-0 flex flex-col items-end">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Oleh</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <div class="w-6 h-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-[8px] font-black text-blue-600">{{ substr($log->user->name ?? 'SYS', 0, 1) }}</div>
                                            <span class="text-[10px] font-black text-slate-700 uppercase tracking-tight">{{ $log->user->name ?? 'SYSTEM' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>
@endsection
