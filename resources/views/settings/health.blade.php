@extends('layouts.app')

@section('title', 'Kesehatan Sistem')
@section('header-title', 'System Health Monitor')

@section('content')
<div class="max-w-6xl mx-auto pb-24">
    <!-- Header Summary -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 mb-12">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-[#0F172A] rounded-[28px] flex items-center justify-center text-white shadow-xl shadow-gray-200">
                <i data-lucide="activity" class="w-8 h-8"></i>
            </div>
            <div>
                <h2 class="text-3xl font-black text-[#0F172A] tracking-tight italic">Status Infrastruktur</h2>
                <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.4em] mt-1">Monitoring Performa & Stabilitas Realtime</p>
            </div>
        </div>
        <div class="flex items-center gap-4 bg-white px-8 py-4 rounded-[24px] border border-[#EFEFEF] shadow-sm">
            <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
            <span class="text-[10px] font-black text-[#0F172A] uppercase tracking-[0.2em]">Sistem Operasional Optimal</span>
        </div>
    </div>

    <!-- Core Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
        <!-- Database Health -->
        <div class="bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm bento-card flex flex-col justify-between h-[340px] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:scale-110 transition-transform duration-700">
                <i data-lucide="database" class="w-32 h-32"></i>
            </div>
            <div class="relative">
                <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.4em] mb-12">Database Engine</p>
                <h4 class="text-4xl font-black text-[#0F172A] tracking-tighter">{{ $dbStatus }}</h4>
                <p class="text-[11px] font-bold text-[#8A8A8A] mt-2 uppercase tracking-widest">Konektivitas MySQL Stable</p>
            </div>
            <div class="pt-8 border-t border-gray-50 flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[9px] font-black text-[#ABABAB] uppercase">Ukuran Data</span>
                    <span class="text-lg font-black text-[#0F172A]">{{ number_format($dbSize, 2) }} MB</span>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-green-600">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Storage Info -->
        <div class="bg-[#0F172A] p-12 rounded-[56px] shadow-2xl shadow-gray-400 bento-card flex flex-col justify-between h-[340px] relative overflow-hidden group text-white">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:rotate-12 transition-transform duration-700">
                <i data-lucide="hard-drive" class="w-32 h-32"></i>
            </div>
            <div class="relative">
                <p class="text-[10px] font-black opacity-40 uppercase tracking-[0.4em] mb-12">Total Volume Arsip</p>
                <h4 class="text-4xl font-black tracking-tighter">{{ $storageUsed }} <span class="text-lg opacity-40">MB</span></h4>
                <p class="text-[11px] font-bold opacity-60 mt-2 uppercase tracking-widest">Akumulasi Berkas Digital Terarsip</p>
            </div>
            <div class="pt-8 opacity-40 text-[9px] font-black uppercase tracking-widest">
                Storage Disk: Local Private
            </div>
        </div>

        <!-- App Environment -->
        <div class="bg-white p-12 rounded-[56px] border border-[#EFEFEF] shadow-sm bento-card flex flex-col h-[340px] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-8 opacity-5">
                <i data-lucide="cpu" class="w-32 h-32"></i>
            </div>
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.4em] mb-12">Runtime Info</p>
            <div class="space-y-6 flex-1 overflow-y-auto custom-scrollbar">
                @foreach($envInfo as $key => $val)
                <div class="flex justify-between items-center border-b border-gray-50 pb-4">
                    <span class="text-[9px] font-black text-[#8A8A8A] uppercase tracking-widest">{{ $key }}</span>
                    <span class="text-[10px] font-bold text-[#0F172A] bg-[#F1F5F9] px-3 py-1 rounded-lg border border-[#EFEFEF]">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Error Monitoring Feed -->
    <div class="bg-white rounded-[64px] border border-[#EFEFEF] shadow-sm overflow-hidden bento-card relative">
        <div class="p-12 border-b border-[#F1F5F9] flex justify-between items-center bg-[#F1F5F9]/50">
            <div>
                <h3 class="text-2xl font-black text-[#0F172A] italic">Log Kejadian Sistem</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-[0.4em] mt-2">Daftar Log Error & Warning Laravel Terkini</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-white px-4 py-2 rounded-2xl border border-[#EFEFEF] text-[9px] font-black uppercase tracking-widest text-[#8A8A8A]">Log: laravel.log</span>
            </div>
        </div>
        <div class="p-12">
            <div class="bg-[#0F172A] rounded-[40px] p-10 font-mono text-[11px] leading-relaxed text-blue-200 overflow-x-auto border-8 border-gray-900 shadow-inner max-h-[500px] custom-scrollbar">
                @forelse($recentLogs as $log)
                    <div class="mb-4 pb-4 border-b border-white/5 last:border-0">
                        <span class="text-[#EAB308] font-black">[{{ now()->format('Y-m-d H:i:s') }}]</span>
                        <span class="ml-2 opacity-80 whitespace-pre-wrap">{{ $log }}</span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20 opacity-30">
                        <i data-lucide="shield-check" class="w-16 h-16 mb-6"></i>
                        <p class="text-sm font-black uppercase tracking-[0.4em]">Zero Critical Errors Detected</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
