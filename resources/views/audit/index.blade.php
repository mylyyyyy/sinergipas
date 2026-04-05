@extends('layouts.app')

@section('title', 'Keamanan & Audit')
@section('header-title', 'Security Control Center')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    $todayLogs = $logs->getCollection()->filter(fn ($log) => $log->created_at->isToday())->count();
    $uniqueUsers = $logs->getCollection()->pluck('user_id')->filter()->unique()->count();
    $searchActive = request()->anyFilled(['search', 'date']);
@endphp

<style>
    .audit-panel {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .audit-panel:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 50px -34px rgba(30, 36, 50, 0.22);
    }
</style>

<div class="space-y-8">
    <section class="relative overflow-hidden rounded-[44px] bg-[#1E2432] px-8 py-8 text-white shadow-2xl shadow-slate-900/15 sm:px-10 sm:py-10">
        <div class="absolute -left-8 top-8 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-[#E85A4F]/25 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-black uppercase tracking-[0.28em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#E85A4F]"></span>
                    Audit & Security
                </div>
                <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">Pantau aktivitas sistem dan respons anomali dalam satu panel yang lebih rapi.</h2>
                <p class="mt-4 max-w-2xl text-sm font-medium leading-relaxed text-white/65">
                    Halaman audit diperhalus agar log penting lebih cepat ditemukan, filter lebih nyaman dipakai, dan konteks keamanan tetap terbaca walau datanya panjang.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-[24px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Log Bulan Ini</p>
                    <p class="mt-3 text-3xl font-black">{{ $logs->total() }}</p>
                </div>
                <div class="rounded-[24px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Aktivitas Hari Ini</p>
                    <p class="mt-3 text-3xl font-black">{{ $todayLogs }}</p>
                </div>
                <div class="rounded-[24px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">User Unik</p>
                    <p class="mt-3 text-3xl font-black">{{ $uniqueUsers }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-8 xl:grid-cols-[360px,minmax(0,1fr)]">
        <div class="space-y-8">
            <div class="audit-panel overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="border-b border-[#F2F1EE] bg-[#FCFBF9] px-8 py-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#E85A4F]">Statistik User</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-[#1E2432]">Akun dengan aktivitas tertinggi.</h3>
                </div>
                <div class="p-8">
                    <div class="relative h-[280px]">
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="audit-panel overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="border-b border-[#F2F1EE] bg-[#FCFBF9] px-8 py-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#E85A4F]">Aksi Cepat</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-[#1E2432]">Kelola retensi log audit.</h3>
                </div>
                <div class="space-y-4 p-8">
                    <p class="text-sm font-medium leading-relaxed text-[#8A8A8A]">Gunakan pembersihan log hanya saat benar-benar diperlukan karena seluruh riwayat aktivitas akan terhapus permanen.</p>
                    <button type="button" onclick="confirmClearLogs()" class="inline-flex w-full items-center justify-center gap-3 rounded-[22px] border border-red-100 bg-red-50 px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-red-600 transition-all hover:bg-red-600 hover:text-white">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        Bersihkan Semua Log
                    </button>
                    <form id="clearLogsForm" action="{{ route('audit.clear') }}" method="POST" class="hidden no-loader">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <div class="audit-panel overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#FCFBF9] px-8 py-7 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#E85A4F]">Riwayat Aktivitas</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-[#1E2432]">Log akses dan tindakan yang tersimpan di sistem.</h3>
                </div>
                @if($searchActive)
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-amber-700">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        Filter aktif
                    </span>
                @endif
            </div>

            <div class="border-b border-[#F2F1EE] bg-white px-8 py-7">
                <form action="{{ route('audit.index') }}" method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr),220px,auto,auto]">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8A8A8A]"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama user, aktivitas, atau detail..." class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#FCFBF9] py-4 pl-12 pr-4 text-sm font-bold text-[#1E2432] outline-none transition-all focus:border-[#E85A4F] focus:ring-4 focus:ring-red-500/5">
                    </div>
                    <div class="relative">
                        <i data-lucide="calendar" class="absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8A8A8A]"></i>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#FCFBF9] py-4 pl-12 pr-4 text-sm font-bold text-[#1E2432] outline-none transition-all focus:border-[#E85A4F] focus:ring-4 focus:ring-red-500/5">
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-[22px] bg-[#1E2432] px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white transition-all hover:bg-[#E85A4F]">
                        Terapkan
                    </button>
                    @if($searchActive)
                        <a href="{{ route('audit.index') }}" class="inline-flex items-center justify-center gap-3 rounded-[22px] border border-[#EFEFEF] bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] transition-all hover:bg-[#FCFBF9]">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-[#FCFBF9]">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">User</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Aktivitas</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Waktu</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F2F1EE]">
                        @forelse($logs as $log)
                            <tr class="transition-all hover:bg-[#FCFBF9]">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-sm font-black text-[#1E2432] shadow-sm">
                                            {{ substr($log->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-[#1E2432]">{{ $log->user->name }}</p>
                                            <span class="mt-2 inline-flex rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] {{ $log->user->role === 'superadmin' ? 'border-red-100 bg-red-50 text-red-600' : 'border-blue-100 bg-blue-50 text-blue-600' }}">
                                                {{ $log->user->role }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-bold leading-relaxed text-[#1E2432]">{{ $log->details }}</p>
                                    <p class="mt-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#E85A4F]">{{ str_replace('_', ' ', $log->activity) }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-[#1E2432]">{{ $log->created_at->format('d M Y') }}</p>
                                    <p class="mt-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">{{ $log->created_at->format('H:i:s') }} • {{ $log->created_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-[#FCFBF9] px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-[#1E2432]">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        {{ $log->ip_address }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-[#FCFBF9] text-[#ABABAB]">
                                        <i data-lucide="shield-alert" class="h-9 w-9"></i>
                                    </div>
                                    <p class="mt-5 text-sm font-black uppercase tracking-[0.22em] text-[#1E2432]">Tidak ada log ditemukan</p>
                                    <p class="mx-auto mt-3 max-w-md text-sm font-medium leading-relaxed text-[#8A8A8A]">Coba ubah filter pencarian atau tunggu aktivitas sistem berikutnya untuk melihat entri audit baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-[#F2F1EE] bg-[#FCFBF9] px-8 py-6">
                {{ $logs->links() }}
            </div>
        </div>
    </section>
</div>

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Audit Dibersihkan',
            text: "{{ session('success') }}",
            confirmButtonColor: '#1E2432',
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
            confirmButtonColor: '#E85A4F',
            cancelButtonColor: '#1E2432',
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
            labels: {!! json_encode($topDownloaders->pluck('user.name')) !!},
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
                        font: { family: 'Plus Jakarta Sans', size: 10, weight: 'bold' },
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
