@extends('layouts.app')

@section('title', 'Pegawai Self-Service')
@section('header-title', 'Portal Mandiri Pegawai')

@section('content')
<!-- Welcome & Quick Salary Hub -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
    <div class="md:col-span-2 bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm flex flex-col justify-between">
        <div>
            <h2 class="text-3xl font-black text-[#1E2432] tracking-tight">Halo, {{ auth()->user()->name }}</h2>
            <p class="text-[#8A8A8A] font-bold mt-2 uppercase tracking-widest text-xs">Pantau progres karir dan dokumen Anda di sini.</p>
        </div>
        
        <div class="mt-12">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em]">Kelengkapan Administrasi</span>
                <span class="text-lg font-black text-[#E85A4F]">{{ number_format($careerProgress, 0) }}%</span>
            </div>
            <div class="w-full h-4 bg-[#FCFBF9] rounded-full overflow-hidden border border-[#EFEFEF]">
                <div class="bg-gradient-to-r from-[#E85A4F] to-[#d44d42] h-full transition-all duration-1000" style="width: {{ $careerProgress }}%"></div>
            </div>
        </div>
    </div>

    <!-- Quick Download Hub -->
    <div class="bg-[#E85A4F] p-10 rounded-[56px] text-white shadow-2xl shadow-red-200 flex flex-col justify-between overflow-hidden relative group">
        <div class="absolute -right-10 -bottom-10 opacity-10 transform group-hover:scale-110 transition-transform duration-700">
            <i data-lucide="file-text" class="w-48 h-48"></i>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.3em]">Quick Hub</p>
            <h3 class="text-xl font-bold mt-4 leading-tight">Slip Gaji Terakhir Anda sudah tersedia untuk diunduh.</h3>
        </div>
        <div class="relative z-10 mt-8">
            @if($latestSalary)
                <a href="{{ route('documents.download', $latestSalary->id) }}" target="_blank" class="inline-flex items-center gap-3 bg-white text-[#E85A4F] px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl no-loader">
                    Unduh Sekarang <i data-lucide="download" class="w-4 h-4"></i>
                </a>
            @else
                <p class="text-xs font-bold opacity-60">Belum ada slip gaji terunggah.</p>
            @endif
        </div>
    </div>
</div>

<!-- Detailed Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="bg-white p-10 rounded-[48px] border border-[#EFEFEF] shadow-sm transform hover:-translate-y-2 transition-all duration-500">
        <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 mb-8">
            <i data-lucide="check-circle" class="w-7 h-7"></i>
        </div>
        <h4 class="text-4xl font-black text-[#1E2432]">{{ $verifiedDocs }}</h4>
        <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mt-2">Dokumen Terverifikasi</p>
    </div>

    <div class="bg-white p-10 rounded-[48px] border border-[#EFEFEF] shadow-sm transform hover:-translate-y-2 transition-all duration-500">
        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 mb-8">
            <i data-lucide="clock" class="w-7 h-7"></i>
        </div>
        <h4 class="text-4xl font-black text-[#1E2432]">{{ $myDocumentsCount - $verifiedDocs }}</h4>
        <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mt-2">Menunggu Review</p>
    </div>

    <div class="bg-white p-10 rounded-[48px] border border-[#EFEFEF] shadow-sm transform hover:-translate-y-2 transition-all duration-500">
        <div class="w-14 h-14 bg-[#1E2432] rounded-2xl flex items-center justify-center text-white mb-8">
            <i data-lucide="message-square" class="w-7 h-7"></i>
        </div>
        <h4 class="text-xl font-black text-[#1E2432]">Ada Masalah?</h4>
        <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mt-2">Lapor perbaikan data ke Admin</p>
        <button onclick="window.location='{{ route('profile.index') }}'" class="mt-6 text-[#E85A4F] text-[10px] font-black uppercase tracking-widest hover:underline flex items-center gap-2">
            Buka Pelaporan <i data-lucide="arrow-right" class="w-3 h-3"></i>
        </button>
    </div>
</div>
@endsection
