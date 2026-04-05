@extends('layouts.app')

@section('title', 'Audit Log')
@section('header-title', 'Keamanan & Audit')

@section('content')
@php
    $searchActive = request()->anyFilled(['search', 'date', 'activity', 'user_id']);
@endphp

<div class="space-y-8 page-fade">
    <!-- Filter & Stats Section -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <div class="xl:col-span-3 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 card-3d">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <form action="{{ route('audit.index') }}" method="GET" class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                    <div class="relative group">
                        <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pesan..." 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-sm font-semibold outline-none focus:border-blue-500 transition-all">
                    </div>
                    <div class="relative">
                        <input type="date" name="date" value="{{ request('date') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 transition-all">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-slate-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all btn-3d">
                            Filter
                        </button>
                        @if($searchActive)
                            <a href="{{ route('audit.index') }}" class="px-4 flex items-center justify-center bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-all">
                                <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </form>
                <div class="h-10 w-px bg-slate-100 hidden md:block"></div>
                <button type="button" onclick="confirmClearLogs()" class="w-full md:w-auto px-6 py-2.5 bg-red-50 text-red-600 border border-red-100 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all btn-3d flex items-center justify-center gap-2">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Clear Log
                </button>
                <form id="clearLogsForm" action="{{ route('audit.clear') }}" method="POST" class="hidden no-loader">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>

        <div class="bg-slate-900 rounded-3xl p-6 text-white flex flex-col justify-center relative overflow-hidden hover-lift">
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <i data-lucide="shield-check" class="w-24 h-24"></i>
            </div>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-1">Status Keamanan</p>
            <h3 class="text-xl font-bold">Sistem Aktif</h3>
            <div class="mt-3 flex items-center gap-2 text-[9px] font-bold text-green-400 bg-green-400/10 px-2.5 py-1 rounded-full w-fit">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                LOGGING REALTIME
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pengguna</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktivitas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detail Perubahan</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alamat IP</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Waktu Kejadian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 font-bold border border-slate-200 group-hover:bg-white transition-colors">
                                    {{ substr($log->user->name ?? 'S', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">{{ $log->user->name ?? 'System' }}</p>
                                    <p class="text-[9px] font-semibold text-slate-400 uppercase">{{ $log->user->role ?? 'automation' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider {{ str_contains($log->activity, 'upload') ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $log->activity }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-semibold text-slate-600 line-clamp-1 max-w-xs" title="{{ $log->details }}">{{ $log->details }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-[10px] font-mono font-bold text-slate-400">{{ $log->ip_address ?? '::1' }}</code>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700">{{ $log->created_at->format('d M Y') }}</span>
                                <span class="text-[10px] font-medium text-slate-400 uppercase">{{ $log->created_at->format('H:i:s') }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                                <i data-lucide="database-zap" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada rekaman audit</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-6 border-t border-slate-100 bg-slate-50/30">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    function confirmClearLogs() {
        Swal.fire({
            title: 'Bersihkan Log Audit?',
            text: "Seluruh riwayat aktivitas akan dihapus permanen dari basis data.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Ya, Bersihkan!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clearLogsForm').submit();
            }
        });
    }
</script>
@endsection
