@extends('layouts.app')

@section('title', 'Pegawai Dashboard')
@section('header-title', 'Portal Mandiri')

@section('content')
@php
    $employeeName = auth()->user()->name;
    $reviewDocs = max($myDocumentsCount - $verifiedDocs, 0);
    $progressTone = $careerProgress >= 100 ? 'Lengkap' : ($careerProgress >= 60 ? 'Hampir Lengkap' : 'Perlu Dilengkapi');
@endphp

<div class="space-y-10 page-fade">
    <section class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-xl card-3d">
        <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -right-8 bottom-0 h-56 w-56 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative z-10 grid gap-8 xl:grid-cols-[minmax(0,1.7fr),minmax(320px,1fr)]">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Self-Service Portal
                </div>

                <div>
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Selamat Datang, <span class="text-amber-400">{{ $employeeName }}</span></h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-slate-400">
                        Akses mandiri untuk memantau kelengkapan berkas, slip gaji, dan riwayat dokumen pribadi Anda secara real-time.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1">NIP Pegawai</p>
                        <p class="text-sm font-bold tracking-wider text-white">{{ $employee->nip ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1">Jabatan</p>
                        <p class="text-sm font-bold text-white line-clamp-1">{{ $employee->position ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1">Unit Kerja</p>
                        <p class="text-sm font-bold text-white line-clamp-1">{{ $employee?->work_unit?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Status Berkas Wajib</p>
                        <span class="px-2 py-0.5 rounded-lg bg-amber-400/10 text-amber-400 text-[9px] font-bold uppercase tracking-wider border border-amber-400/20">{{ $progressTone }}</span>
                    </div>
                    <h3 class="mt-4 text-5xl font-bold tracking-tight">{{ number_format($careerProgress, 0) }}<span class="text-xl text-slate-500">%</span></h3>
                </div>

                <div class="mt-6">
                    <div class="h-2 overflow-hidden rounded-full bg-white/5">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-amber-300" style="width: {{ min($careerProgress, 100) }}%"></div>
                    </div>
                    <div class="mt-4 grid gap-3 grid-cols-2">
                        <div class="rounded-xl bg-slate-900/50 p-3 border border-white/5 text-center">
                            <p class="text-[8px] font-bold uppercase text-slate-500">Valid</p>
                            <p class="text-lg font-bold">{{ $verifiedDocs }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-900/50 p-3 border border-white/5 text-center">
                            <p class="text-[8px] font-bold uppercase text-slate-500">Pending</p>
                            <p class="text-lg font-bold text-amber-400">{{ $reviewDocs }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-8 xl:grid-cols-[1fr,350px]">
        <!-- Left Side -->
        <div class="space-y-8">
            <!-- Stats Row -->
            <div class="grid gap-6 md:grid-cols-3">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover-lift flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i data-lucide="files" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Total Arsip</p>
                        <p class="text-xl font-bold text-slate-900">{{ $myDocumentsCount }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover-lift flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Terverifikasi</p>
                        <p class="text-xl font-bold text-slate-900">{{ $verifiedDocs }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover-lift flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="hourglass" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Review</p>
                        <p class="text-xl font-bold text-slate-900">{{ $reviewDocs }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col card-3d">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i>
                        Aktivitas Dokumen Terakhir
                    </h3>
                    <a href="{{ route('documents.index') }}" class="text-[10px] font-bold text-blue-600 uppercase tracking-widest hover:underline">Semua Arsip</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentDocuments as $doc)
                        <div class="p-5 flex items-center justify-between hover:bg-slate-50/50 transition-colors group">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-blue-600 transition-all border border-transparent group-hover:border-slate-200">
                                    <i data-lucide="{{ str_contains($doc->file_path, '.pdf') ? 'file-text' : 'image' }}" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 line-clamp-1">{{ $doc->title }}</h4>
                                    <p class="text-[10px] font-semibold text-slate-400 mt-0.5">
                                        {{ $doc->category->name ?? 'Dokumen' }} • {{ $doc->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="px-2 py-0.5 rounded-lg text-[8px] font-bold uppercase tracking-wider border {{ $doc->status === 'verified' ? 'bg-green-50 text-green-600 border-green-100' : ($doc->status === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-100 text-slate-500 border-slate-200') }}">
                                    {{ $doc->status }}
                                </span>
                                <button onclick="window.open('{{ route('documents.preview', $doc->id) }}', '_blank')" class="p-2 rounded-lg text-slate-400 hover:text-slate-900 hover:bg-white border border-transparent hover:border-slate-200 transition-all">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                                <i data-lucide="folder-search" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada dokumen yang diunggah</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="space-y-8">
            <!-- Salary Card -->
            <div class="bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl card-3d group">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="banknote" class="w-32 h-32"></i>
                </div>
                <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 mb-6">Payroll Quick Access</h4>
                <h3 class="text-2xl font-bold tracking-tight leading-snug">Unduh Slip Gaji<br>Periode Terbaru.</h3>
                <div class="mt-8">
                    @if($latestSalary)
                        <a href="{{ route('documents.download', $latestSalary->id) }}" target="_blank" class="inline-flex w-full items-center justify-center gap-3 px-6 py-4 rounded-xl bg-amber-600 text-white font-bold text-xs uppercase tracking-widest hover:bg-amber-700 transition-all shadow-lg btn-3d no-loader">
                            <i data-lucide="download-cloud" class="w-4 h-4"></i>
                            Download PDF
                        </a>
                    @else
                        <div class="px-6 py-4 rounded-xl bg-white/5 border border-white/5 text-center text-[10px] font-bold uppercase text-slate-500">
                            Data belum tersedia
                        </div>
                    @endif
                </div>
            </div>

            <!-- Help/Shortcuts -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 hover-lift">
                <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mb-2 px-2">Bantuan & Layanan</h4>
                <a href="{{ route('documents.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-blue-200 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center border border-slate-100 text-slate-400 group-hover:text-blue-600 transition-colors">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Unggah Berkas</p>
                        <p class="text-[9px] font-medium text-slate-400 mt-0.5">Kirim dokumen baru ke admin</p>
                    </div>
                </a>
                <a href="{{ route('profile.index') }}" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-amber-200 transition-all group">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center border border-slate-100 text-slate-400 group-hover:text-amber-600 transition-colors">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Lapor Koreksi</p>
                        <p class="text-[9px] font-medium text-slate-400 mt-0.5">Ajukan perubahan data profil</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
