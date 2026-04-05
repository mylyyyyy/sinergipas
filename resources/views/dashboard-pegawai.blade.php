@extends('layouts.app')

@section('title', 'Pegawai Self-Service')
@section('header-title', 'Portal Mandiri Pegawai')

@section('content')
@php
    $employeeName = auth()->user()->name;
    $reviewDocs = max($myDocumentsCount - $verifiedDocs, 0);
    $progressTone = $careerProgress >= 100 ? 'Lengkap' : ($careerProgress >= 60 ? 'Hampir Lengkap' : 'Perlu Dilengkapi');
@endphp

<style>
    .employee-surface {
        transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    }

    .employee-surface:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 50px -30px rgba(30, 36, 50, 0.28);
    }
</style>

<div class="space-y-10">
    <section class="relative overflow-hidden rounded-[48px] bg-[#1E2432] px-8 py-8 text-white shadow-2xl shadow-slate-900/15 sm:px-10 sm:py-10">
        <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -right-8 bottom-0 h-56 w-56 rounded-full bg-[#E85A4F]/25 blur-3xl"></div>

        <div class="relative z-10 grid gap-8 xl:grid-cols-[minmax(0,1.7fr),minmax(320px,1fr)]">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-black uppercase tracking-[0.28em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#E85A4F]"></span>
                    Portal Mandiri
                </div>

                <div>
                    <h2 class="max-w-3xl text-3xl font-black tracking-tight sm:text-4xl">Selamat datang, <span class="text-[#FFB9B2]">{{ $employeeName }}</span>.</h2>
                    <p class="mt-4 max-w-2xl text-sm font-medium leading-relaxed text-white/70">
                        Pantau kelengkapan berkas, akses slip gaji terbaru, dan cek dokumen terakhir tanpa harus berpindah-pindah menu.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">NIP Pegawai</p>
                        <p class="mt-3 text-sm font-black tracking-[0.14em] text-white">{{ $employee->nip ?? '-' }}</p>
                    </div>
                    <div class="rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">Jabatan</p>
                        <p class="mt-3 text-sm font-black text-white">{{ $employee->position ?? '-' }}</p>
                    </div>
                    <div class="rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/50">Unit Kerja</p>
                        <p class="mt-3 text-sm font-black text-white">{{ $employee?->work_unit?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[36px] border border-white/10 bg-white/5 p-7 backdrop-blur">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/55">Progress Berkas Wajib</p>
                        <h3 class="mt-3 text-5xl font-black tracking-tight">{{ number_format($careerProgress, 0) }}%</h3>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-white/80">{{ $progressTone }}</span>
                </div>

                <div class="mt-6 h-4 overflow-hidden rounded-full border border-white/10 bg-white/10 p-1">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#E85A4F] to-[#FF8D84]" style="width: {{ min($careerProgress, 100) }}%"></div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[24px] border border-white/10 bg-[#10151f]/35 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Terverifikasi</p>
                        <p class="mt-2 text-2xl font-black">{{ $verifiedDocs }}</p>
                    </div>
                    <div class="rounded-[24px] border border-white/10 bg-[#10151f]/35 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-white/45">Butuh Tinjau</p>
                        <p class="mt-2 text-2xl font-black">{{ $reviewDocs }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-8 xl:grid-cols-[minmax(0,1.7fr),380px]">
        <div class="space-y-8">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="employee-surface rounded-[36px] border border-[#EFEFEF] bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Total Dokumen</p>
                            <h4 class="mt-4 text-4xl font-black tracking-tight text-[#1E2432]">{{ $myDocumentsCount }}</h4>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-[24px] bg-blue-50 text-blue-600">
                            <i data-lucide="folder-open" class="h-7 w-7"></i>
                        </div>
                    </div>
                </div>

                <div class="employee-surface rounded-[36px] border border-[#EFEFEF] bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Sudah Valid</p>
                            <h4 class="mt-4 text-4xl font-black tracking-tight text-[#1E2432]">{{ $verifiedDocs }}</h4>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-[24px] bg-emerald-50 text-emerald-600">
                            <i data-lucide="shield-check" class="h-7 w-7"></i>
                        </div>
                    </div>
                </div>

                <div class="employee-surface rounded-[36px] border border-[#EFEFEF] bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Menunggu Review</p>
                            <h4 class="mt-4 text-4xl font-black tracking-tight text-[#1E2432]">{{ $reviewDocs }}</h4>
                        </div>
                        <div class="flex h-14 w-14 items-center justify-center rounded-[24px] bg-amber-50 text-amber-600">
                            <i data-lucide="clock" class="h-7 w-7"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="employee-surface overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#FCFBF9] px-8 py-7 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#E85A4F]">Unggahan Terakhir</p>
                        <h3 class="mt-2 text-2xl font-black tracking-tight text-[#1E2432]">Dokumen yang paling baru Anda kirim.</h3>
                    </div>
                    <a href="{{ route('documents.index') }}" class="inline-flex items-center gap-3 rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em] text-[#1E2432] transition-all hover:bg-[#1E2432] hover:text-white no-loader">
                        Lihat Semua
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>

                <div class="divide-y divide-[#F2F1EE]">
                    @forelse($recentDocuments as $doc)
                        <div class="flex flex-col gap-5 px-8 py-6 transition-all hover:bg-[#FCFBF9] sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-5">
                                <div class="flex h-14 w-14 items-center justify-center rounded-[22px] bg-[#FCFBF9] text-[#1E2432]">
                                    <i data-lucide="{{ str_contains($doc->file_path, '.pdf') ? 'file-text' : 'image' }}" class="h-6 w-6"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-[#1E2432]">{{ $doc->title }}</h4>
                                    <p class="mt-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">
                                        {{ $doc->category->name ?? 'Dokumen' }} • {{ $doc->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] {{ $doc->status === 'verified' ? 'border-emerald-100 bg-emerald-50 text-emerald-600' : ($doc->status === 'pending' ? 'border-amber-100 bg-amber-50 text-amber-600' : 'border-slate-200 bg-slate-50 text-slate-500') }}">
                                    {{ $doc->status }}
                                </span>
                                <button onclick="window.open('{{ route('documents.preview', $doc->id) }}', '_blank')" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#1E2432] shadow-sm transition-all hover:bg-[#1E2432] hover:text-white">
                                    <i data-lucide="eye" class="h-4 w-4"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="px-8 py-20 text-center">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-[#FCFBF9] text-[#ABABAB]">
                                <i data-lucide="folder-open" class="h-9 w-9"></i>
                            </div>
                            <p class="mt-5 text-sm font-black uppercase tracking-[0.22em] text-[#1E2432]">Belum ada dokumen</p>
                            <p class="mx-auto mt-3 max-w-md text-sm font-medium leading-relaxed text-[#8A8A8A]">Mulai unggah dokumen dari menu pusat dokumen agar riwayat aktivitas Anda tampil di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="employee-surface overflow-hidden rounded-[40px] bg-[#1E2432] text-white shadow-2xl shadow-slate-900/10">
                <div class="px-8 py-8">
                    <div class="flex h-14 w-14 items-center justify-center rounded-[24px] bg-white/10 text-white">
                        <i data-lucide="banknote" class="h-7 w-7"></i>
                    </div>
                    <h3 class="mt-6 text-2xl font-black tracking-tight">Akses cepat slip gaji terbaru.</h3>
                    <p class="mt-3 text-sm font-medium leading-relaxed text-white/65">Saat slip gaji terbaru tersedia, Anda bisa langsung mengunduhnya dari kartu ini.</p>
                </div>
                <div class="border-t border-white/10 px-8 py-6">
                    @if($latestSalary)
                        <a href="{{ route('documents.download', $latestSalary->id) }}" target="_blank" class="inline-flex w-full items-center justify-center gap-3 rounded-[22px] bg-[#E85A4F] px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-red-950/30 transition-all hover:bg-[#d44d42] no-loader">
                            Unduh Slip Gaji
                            <i data-lucide="download" class="h-4 w-4"></i>
                        </a>
                    @else
                        <div class="rounded-[24px] border border-white/10 bg-white/5 px-5 py-4 text-center text-[10px] font-black uppercase tracking-[0.22em] text-white/45">
                            Belum ada slip gaji tersedia
                        </div>
                    @endif
                </div>
            </div>

            <div class="employee-surface overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="border-b border-[#F2F1EE] bg-[#FCFBF9] px-8 py-7">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#E85A4F]">Aksi Cepat</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-[#1E2432]">Butuh bantuan atau ingin meninjau data?</h3>
                </div>
                <div class="space-y-4 p-8">
                    <a href="{{ route('documents.index') }}" class="flex items-center justify-between rounded-[26px] border border-[#EFEFEF] bg-[#FCFBF9] px-5 py-4 transition-all hover:bg-white hover:shadow-md">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#1E2432]">Pusat Dokumen</p>
                            <p class="mt-2 text-sm font-medium text-[#8A8A8A]">Lihat arsip dan unggah dokumen baru.</p>
                        </div>
                        <i data-lucide="folder-open" class="h-5 w-5 text-[#E85A4F]"></i>
                    </a>

                    <a href="{{ route('profile.index') }}" class="flex items-center justify-between rounded-[26px] border border-[#EFEFEF] bg-[#FCFBF9] px-5 py-4 transition-all hover:bg-white hover:shadow-md">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#1E2432]">Perbarui Profil</p>
                            <p class="mt-2 text-sm font-medium text-[#8A8A8A]">Atur akun dan laporkan ketidaksesuaian data.</p>
                        </div>
                        <i data-lucide="user-pen" class="h-5 w-5 text-[#E85A4F]"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
