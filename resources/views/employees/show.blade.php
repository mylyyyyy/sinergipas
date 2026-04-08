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
                <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Profile Card -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden card-3d">
                <div class="h-32 bg-slate-900 relative">
                    <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                        <div class="w-24 h-24 rounded-3xl bg-white border-4 border-white shadow-xl overflow-hidden flex items-center justify-center text-slate-300 font-black text-2xl">
                            @if($employee->photo)
                                <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($employee->full_name, 0, 1) }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pt-16 pb-8 px-8 text-center">
                    <h3 class="text-xl font-black text-slate-900 italic leading-tight">{{ $employee->full_name }}</h3>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-[0.2em] mt-1">NIP. {{ $employee->nip }}</p>
                    
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-100 text-[9px] font-black uppercase tracking-widest text-slate-500">
                            <span class="h-1.5 w-1.5 rounded-full {{ $employee->employee_type === 'regu_jaga' ? 'bg-indigo-500' : 'bg-emerald-500' }}"></span>
                            {{ $employee->employee_type_label }}
                        </div>
                        @if($employee->category)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-{{ $employee->category->color }}-50 border border-{{ $employee->category->color }}-100 text-[9px] font-black uppercase tracking-widest text-{{ $employee->category->color }}-600">
                            <i data-lucide="tag" class="w-2.5 h-2.5"></i>
                            {{ $employee->category->name }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="border-t border-slate-50 p-8 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jabatan</span>
                        <span class="text-xs font-black text-slate-700 uppercase text-right">{{ $employee->position }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Golongan</span>
                        <span class="text-xs font-black text-slate-700 uppercase">{{ $employee->rank_class ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Unit Kerja</span>
                        <span class="text-xs font-black text-slate-700 uppercase">{{ $employee->work_unit->name ?? '-' }}</span>
                    </div>
                    @if($employee->category)
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kategori</span>
                        <span class="text-xs font-black text-indigo-600 uppercase">{{ $employee->category->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Tabs (History) -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[600px] card-3d">
                <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                    <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.3em] flex items-center gap-3">
                        <i data-lucide="history" class="w-5 h-5 text-blue-600"></i>
                        Riwayat Perubahan Data
                    </h3>
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[9px] font-black uppercase tracking-widest">{{ $history->count() }} Records</span>
                </div>
                
                <div class="p-8 overflow-y-auto custom-scrollbar flex-1">
                    @if($history->isEmpty())
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-40">
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
                                            <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ $log->activity === 'create_employee' ? 'Pendaftaran Awal' : 'Pembaruan Profil' }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 italic">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-500 leading-relaxed">{{ $log->details }}</p>
                                        
                                        @if($log->old_values && $log->new_values)
                                        <div class="mt-4 p-4 rounded-2xl bg-slate-900/5 border border-slate-100 space-y-3">
                                            @php
                                                $keysToTrack = ['position', 'rank_class', 'work_unit_id', 'picket_regu', 'employee_type'];
                                                $labels = [
                                                    'position' => 'Jabatan',
                                                    'rank_class' => 'Golongan',
                                                    'work_unit_id' => 'ID Unit',
                                                    'picket_regu' => 'Regu',
                                                    'employee_type' => 'Tipe'
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
