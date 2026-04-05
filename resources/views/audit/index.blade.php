@extends('layouts.app')

@section('title', 'Keamanan & Audit')
@section('header-title', 'Security Control Center')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    $searchActive = request()->anyFilled(['search', 'date', 'activity', 'user_id']);
@endphp

<div class="space-y-8">
    <section class="overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
        <div class="border-b border-[#F2F1EE] bg-[#F1F5F9] px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#EAB308]">Riwayat Aktivitas</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-[#0F172A]">Log audit.</h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($searchActive)
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-amber-700">
                            <i data-lucide="filter" class="h-4 w-4"></i>
                            Filter aktif
                        </span>
                    @endif
                    <button type="button" onclick="confirmClearLogs()" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-100 bg-red-50 px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em] text-red-600 transition-all hover:bg-red-600 hover:text-white">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        Bersihkan
                    </button>
                    <form id="clearLogsForm" action="{{ route('audit.clear') }}" method="POST" class="hidden no-loader">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <form action="{{ route('audit.index') }}" method="GET" class="grid gap-4 xl:grid-cols-[minmax(0,1.3fr),200px,180px,220px,auto,auto]">
                <div class="relative min-w-0">
                    <i data-lucide="search" class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#ABABAB]"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari log..." class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] py-4 pl-11 pr-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                </div>
                <select name="activity" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-4 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                    <option value="">Aktivitas</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity }}" {{ request('activity') === $activity ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $activity) }}
                        </option>
                    @endforeach
                </select>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-4 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                <select name="user_id" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-4 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                    <option value="">Semua user</option>
                    @foreach($users as $userOption)
                        <option value="{{ $userOption->id }}" {{ (string) request('user_id') === (string) $userOption->id ? 'selected' : '' }}>
                            {{ $userOption->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center justify-center rounded-[22px] bg-[#0F172A] px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white transition-all hover:bg-[#EAB308]">
                    Terapkan
                </button>
                @if($searchActive)
                    <a href="{{ route('audit.index') }}" class="inline-flex items-center justify-center rounded-[22px] border border-[#EFEFEF] bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] transition-all hover:bg-[#F1F5F9]">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[32px] border border-[#EFEFEF] bg-white p-7 shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.28em]">Total Log</p>
            <p class="mt-4 text-4xl font-black text-[#0F172A]">{{ $totalLogs }}</p>
        </div>
        <div class="rounded-[32px] border border-[#EFEFEF] bg-white p-7 shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.28em]">Hari Ini</p>
            <p class="mt-4 text-4xl font-black text-[#0F172A]">{{ $todayLogs }}</p>
        </div>
        <div class="rounded-[32px] border border-[#EFEFEF] bg-white p-7 shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.28em]">User Unik</p>
            <p class="mt-4 text-4xl font-black text-[#0F172A]">{{ $uniqueUsers }}</p>
        </div>
        <div class="rounded-[32px] bg-[#0F172A] p-7 text-white shadow-xl">
            <p class="text-[10px] font-black uppercase tracking-[0.28em] text-white/45">Aksi Cepat</p>
            <p class="mt-4 text-sm font-bold text-white/75">Rapikan histori jika audit sudah diarsipkan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-[minmax(0,1fr),320px]">
        <div class="overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-[#F2F1EE] bg-[#F1F5F9] px-6 py-6 sm:px-8">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#EAB308]">Riwayat Aktivitas</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-[#0F172A]">Daftar log.</h3>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] border-collapse text-left">
                    <thead>
                        <tr class="bg-[#F1F5F9]">
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] sm:px-8">User</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] sm:px-8">Aktivitas</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] sm:px-8">Detail</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] sm:px-8">Waktu</th>
                            <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] sm:px-8">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F2F1EE]">
                        @forelse($logs as $log)
                            <tr class="transition-all hover:bg-[#F1F5F9]">
                                <td class="px-6 py-6 sm:px-8">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-sm font-black text-[#0F172A] shadow-sm">
                                            {{ substr($log->user->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-[#0F172A]">{{ $log->user->name ?? 'Sistem' }}</p>
                                            <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#8A8A8A]">{{ $log->user->role ?? 'system' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 sm:px-8">
                                    <span class="inline-flex rounded-full border border-[#EFEFEF] bg-[#F1F5F9] px-3 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A]">
                                        {{ str_replace('_', ' ', $log->activity) }}
                                    </span>
                                </td>
                                <td class="px-6 py-6 sm:px-8">
                                    <p class="max-w-[300px] truncate text-sm font-bold text-[#0F172A]" title="{{ $log->details }}">
                                        {{ $log->details }}
                                    </p>
                                </td>
                                <td class="px-6 py-6 sm:px-8">
                                    <p class="text-sm font-black text-[#0F172A]">{{ $log->created_at->format('d M Y') }}</p>
                                    <p class="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#8A8A8A]">{{ $log->created_at->format('H:i') }}</p>
                                </td>
                                <td class="px-6 py-6 sm:px-8">
                                    <span class="inline-flex rounded-full border border-[#EFEFEF] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-[#0F172A]">
                                        {{ $log->ip_address ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-[#F1F5F9] text-[#ABABAB]">
                                        <i data-lucide="shield-alert" class="h-9 w-9"></i>
                                    </div>
                                    <p class="mt-5 text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Tidak ada log ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[#F2F1EE] bg-[#F1F5F9] px-6 py-6 sm:px-8">
                {{ $logs->links() }}
            </div>
        </div>

        <div class="rounded-[40px] border border-[#EFEFEF] bg-white p-8 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.28em]">Statistik User</p>
                    <h3 class="mt-2 text-2xl font-black text-[#0F172A] tracking-tight">Aktivitas tertinggi.</h3>
                </div>
            </div>
            <div class="h-[280px]">
                <canvas id="userChart"></canvas>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Audit Dibersihkan',
            text: "{{ session('success') }}",
            confirmButtonColor: '#0F172A',
            customClass: { popup: 'rounded-[32px]' }
        });
    </script>
@endif

<script>
    function confirmClearLogs() {
        Swal.fire({
            title: 'Bersihkan seluruh log?',
            text: 'Semua riwayat audit akan dihapus permanen dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EAB308',
            cancelButtonColor: '#0F172A',
            confirmButtonText: 'Ya, bersihkan',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clearLogsForm').submit();
            }
        });
    }

    const userCtx = document.getElementById('userChart').getContext('2d');
    new Chart(userCtx, {
        type: 'polarArea',
        data: {
            labels: {!! json_encode($topDownloaders->map(fn ($log) => $log->user->name ?? 'Sistem')) !!},
            datasets: [{
                data: {!! json_encode($topDownloaders->pluck('total')) !!},
                backgroundColor: [
                    'rgba(232, 90, 79, 0.86)',
                    'rgba(30, 36, 50, 0.86)',
                    'rgba(59, 130, 246, 0.82)',
                    'rgba(16, 185, 129, 0.82)',
                    'rgba(245, 158, 11, 0.82)'
                ],
                borderWidth: 4,
                borderColor: '#ffffff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Segoe UI', size: 10, weight: 'bold' },
                        padding: 18,
                        usePointStyle: true
                    }
                }
            },
            scales: {
                r: {
                    grid: { display: false },
                    ticks: { display: false }
                }
            }
        }
    });
</script>
@endsection
