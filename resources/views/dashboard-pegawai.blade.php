@extends('layouts.app')

@section('title', 'Pegawai Dashboard')
@section('header-title', 'Portal Mandiri')

@section('content')
@php
    $employeeName = auth()->user()->name;
    $reviewDocs = max($myDocumentsCount - $verifiedDocs - $rejectedDocsCount, 0);
    $progressTone = $careerProgress >= 100 ? 'Lengkap' : ($careerProgress >= 60 ? 'Hampir Lengkap' : 'Perlu Dilengkapi');
@endphp

<div class="space-y-10 page-fade">
    @if($rejectedDocsCount > 0)
    <!-- Rejected Documents Alert -->
    <div class="bg-red-50 border-2 border-red-100 rounded-[32px] p-6 flex items-center justify-between shadow-sm animate-pulse group hover:scale-[1.01] transition-all">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-red-600 text-white flex items-center justify-center shadow-lg shadow-red-200">
                <i data-lucide="alert-octagon" class="w-8 h-8"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-red-900 italic uppercase tracking-tight">Tindakan Diperlukan!</h3>
                <p class="text-sm font-bold text-red-600">Ada {{ $rejectedDocsCount }} dokumen yang ditolak dan memerlukan revisi segera.</p>
            </div>
        </div>
        <a href="{{ route('documents.index') }}" class="px-6 py-3 rounded-xl bg-red-600 text-white font-black text-[10px] uppercase tracking-widest hover:bg-red-700 transition-all shadow-md">
            Perbaiki Sekarang
        </a>
    </div>
    @endif

    <section class="relative overflow-hidden rounded-[48px] bg-slate-900 px-8 py-10 text-white shadow-xl card-3d">
        <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -right-8 bottom-0 h-56 w-56 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative z-10 grid gap-8 xl:grid-cols-[minmax(0,1.7fr),minmax(320px,1fr)]">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Portal Pegawai Terpadu
                </div>

                <div>
                    <h2 class="text-3xl font-black tracking-tight sm:text-4xl italic">Sinergi <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-300">Self-Service</span></h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-relaxed text-slate-400 uppercase tracking-widest text-[10px]">
                        Selamat Datang, {{ $employeeName }} • {{ $employee->nip }}
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1">Jabatan</p>
                        <p class="text-sm font-bold text-white line-clamp-1">{{ $employee->position ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1">Unit Kerja</p>
                        <p class="text-sm font-bold text-white line-clamp-1">{{ $employee?->work_unit?->name ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-1">Golongan</p>
                        <p class="text-sm font-bold text-white">{{ $employee->rank_relation->name ?? $employee->rank_class ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[32px] border border-white/10 bg-white/5 p-6 backdrop-blur-sm flex flex-col justify-between group hover:border-amber-500/50 transition-all">
                <div>
                    <div class="flex items-start justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pencapaian Berkas</p>
                        <span class="px-2 py-0.5 rounded-lg bg-amber-400/10 text-amber-400 text-[9px] font-bold uppercase tracking-wider border border-amber-400/20">{{ $progressTone }}</span>
                    </div>
                    <h3 class="mt-4 text-5xl font-black tracking-tighter">{{ number_format($careerProgress, 0) }}<span class="text-xl text-slate-500">%</span></h3>
                </div>

                <div class="mt-6">
                    <div class="h-2 overflow-hidden rounded-full bg-white/5">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-400" style="width: {{ min($careerProgress, 100) }}%"></div>
                    </div>
                    <div class="mt-4 grid gap-3 grid-cols-3">
                        <div class="rounded-xl bg-slate-900/50 p-2.5 border border-white/5 text-center group-hover:bg-slate-800 transition-colors">
                            <p class="text-[7px] font-bold uppercase text-slate-500">Valid</p>
                            <p class="text-base font-black text-emerald-400">{{ $verifiedDocs }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-900/50 p-2.5 border border-white/5 text-center group-hover:bg-slate-800 transition-colors">
                            <p class="text-[7px] font-bold uppercase text-slate-500">Antrean</p>
                            <p class="text-base font-black text-amber-400">{{ $reviewDocs }}</p>
                        </div>
                        <div class="rounded-xl bg-red-500/10 p-2.5 border border-red-500/20 text-center group-hover:bg-red-500/20 transition-colors">
                            <p class="text-[7px] font-bold uppercase text-red-400">Ditolak</p>
                            <p class="text-base font-black text-red-500">{{ $rejectedDocsCount }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Attendance Today Status -->
    <div class="bg-white rounded-[40px] border border-slate-200 p-8 shadow-sm card-3d flex flex-col md:flex-row items-center gap-8">
        <div class="w-20 h-20 rounded-3xl {{ $myAttendanceToday ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0">
            <i data-lucide="{{ $myAttendanceToday ? 'fingerprint' : 'alert-circle' }}" class="w-10 h-10"></i>
        </div>
        <div class="flex-1 text-center md:text-left">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-2">Status Presensi Hari Ini</h3>
            @if($myAttendanceToday)
                <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
                    <div>
                        <p class="text-2xl font-black text-slate-900">SUDAH ABSEN</p>
                        <p class="text-xs font-bold text-green-600 uppercase">Tercatat pada {{ Carbon\Carbon::parse($myAttendanceToday->check_in)->format('H:i') }} WIB</p>
                    </div>
                    <div class="h-10 w-px bg-slate-100 hidden md:block"></div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Status Kehadiran</p>
                        @if($myAttendanceToday->status === 'late')
                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-[10px] font-black uppercase tracking-wider border border-amber-100">Terlambat ({{ $myAttendanceToday->late_minutes }}m)</span>
                        @else
                            <span class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-[10px] font-black uppercase tracking-wider border border-green-100">Tepat Waktu</span>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-2xl font-black text-red-500">BELUM TERCATAT</p>
                <p class="text-xs font-medium text-slate-400 italic">Silakan lakukan scan pada mesin fingerprint kantor.</p>
            @endif
        </div>
        <div class="shrink-0">
            <a href="{{ route('documents.index') }}?tab=absensi" class="px-6 py-3 rounded-2xl bg-slate-900 text-white text-[10px] font-bold uppercase tracking-widest hover:bg-blue-600 transition-all flex items-center gap-2">
                Riwayat Lengkap <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <div class="grid gap-8 xl:grid-cols-[1fr,350px]">
        <!-- Left Side: Recent Activity -->
        <div class="space-y-8">
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col card-3d">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-3 uppercase tracking-widest">
                        <i data-lucide="activity" class="w-5 h-5 text-blue-600"></i>
                        Aktivitas Dokumen
                    </h3>
                    <a href="{{ route('documents.index') }}" class="text-[10px] font-bold text-blue-600 uppercase tracking-widest hover:underline">Kelola Berkas</a>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentDocuments as $doc)
                        <div class="p-6 flex items-center justify-between hover:bg-slate-50/50 transition-colors group">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-blue-600 transition-all border border-transparent group-hover:border-slate-200">
                                    <i data-lucide="{{ str_contains($doc->file_path, '.pdf') ? 'file-text' : 'image' }}" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 line-clamp-1">{{ $doc->title }}</h4>
                                    <p class="text-[10px] font-semibold text-slate-400 mt-1 uppercase tracking-tighter">
                                        {{ $doc->category->name ?? 'Dokumen' }} • {{ $doc->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $doc->status === 'verified' ? 'bg-green-50 text-green-600 border-green-100' : ($doc->status === 'pending' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-red-50 text-red-600 border-red-100') }}">
                                {{ $doc->status === 'rejected' ? 'Ditolak' : $doc->status }}
                            </span>
                        </div>
                    @empty
                        <div class="py-20 text-center">
                            <i data-lucide="folder-search" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest italic">Belum ada riwayat dokumen</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="space-y-8">
            <!-- Salary Card -->
            <div class="bg-slate-900 rounded-[40px] p-8 text-white relative overflow-hidden shadow-xl card-3d group">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <i data-lucide="banknote" class="w-32 h-32 text-amber-400"></i>
                </div>
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-6">Slip Gaji Terakhir</h4>
                <h3 class="text-2xl font-bold tracking-tight leading-snug">Unduh Slip Gaji<br>Periode Berjalan.</h3>
                <div class="mt-8">
                    @if($latestSalary)
                        <button onclick="handleDownload('{{ route('documents.download', $latestSalary->id) }}', 'Slip-Gaji-{{ auth()->user()->name }}.pdf')" class="inline-flex w-full items-center justify-center gap-3 px-6 py-4 rounded-2xl bg-amber-600 text-white font-black text-xs uppercase tracking-widest hover:bg-amber-700 transition-all shadow-lg btn-3d no-loader">
                            <i data-lucide="download-cloud" class="w-5 h-5"></i>
                            Download PDF
                        </button>
                    @else
                        <div class="px-6 py-4 rounded-2xl bg-white/5 border border-white/5 text-center text-[10px] font-bold uppercase text-slate-500 italic">
                            Belum diunggah bendahara
                        </div>
                    @endif
                </div>
            </div>

            <!-- Help/Shortcuts -->
            <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-8 space-y-4 hover-lift card-3d">
                <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em] mb-4 px-2">Shortcut Layanan</h4>
                <a href="{{ route('documents.index') }}" class="flex items-center gap-4 p-5 rounded-3xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-blue-200 transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center border border-slate-100 text-slate-400 group-hover:text-blue-600 group-hover:shadow-lg transition-all">
                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase">Kirim Berkas</p>
                        <p class="text-[10px] font-medium text-slate-400 mt-1 leading-tight">Unggah dokumen wajib/sk</p>
                    </div>
                </a>
                <a href="{{ route('profile.index') }}" class="flex items-center gap-4 p-5 rounded-3xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-amber-200 transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center border border-slate-100 text-slate-400 group-hover:text-amber-600 group-hover:shadow-lg transition-all">
                        <i data-lucide="user-cog" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase">Update Profil</p>
                        <p class="text-[10px] font-medium text-slate-400 mt-1 leading-tight">Ubah data & lapor koreksi</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
