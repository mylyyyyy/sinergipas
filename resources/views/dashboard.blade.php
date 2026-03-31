@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Overview Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <!-- Stat Cards -->
    <div class="bg-white p-6 rounded-2xl border border-[#EFEFEF] shadow-sm hover:shadow-md transition-all">
        <p class="text-sm font-medium text-[#8A8A8A] mb-4">Total Pegawai</p>
        <div class="flex items-end justify-between">
            <h3 class="text-3xl font-bold text-[#1E2432]">{{ $totalEmployees }}</h3>
            <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-lg">Pegawai Aktif</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-[#EFEFEF] shadow-sm hover:shadow-md transition-all">
        <p class="text-sm font-medium text-[#8A8A8A] mb-4">Total Dokumen Sistem</p>
        <div class="flex items-end justify-between">
            <h3 class="text-3xl font-bold text-[#1E2432]">{{ $totalDocuments }}</h3>
            <span class="text-xs font-semibold text-[#8A8A8A] bg-[#F5F4F2] px-2 py-1 rounded-lg">File Terbit</span>
        </div>
    </div>

    @if(auth()->user()->role === 'pegawai')
    <div class="bg-white p-6 rounded-2xl border border-[#EFEFEF] shadow-sm hover:shadow-md transition-all">
        <p class="text-sm font-medium text-[#8A8A8A] mb-4">Dokumen Saya</p>
        <div class="flex items-end justify-between">
            <h3 class="text-3xl font-bold text-[#E85A4F]">{{ $myDocumentsCount }}</h3>
            <span class="text-xs font-semibold text-[#E85A4F] bg-red-50 px-2 py-1 rounded-lg">Milik Anda</span>
        </div>
    </div>
    @else
    <div class="bg-white p-6 rounded-2xl border border-[#EFEFEF] shadow-sm hover:shadow-md transition-all">
        <p class="text-sm font-medium text-[#8A8A8A] mb-4">Status Admin</p>
        <div class="flex items-end justify-between">
            <h3 class="text-3xl font-bold text-[#1E2432]">Aktif</h3>
            <span class="text-xs font-semibold text-[#E85A4F] bg-red-50 px-2 py-1 rounded-lg">Super Admin</span>
        </div>
    </div>
    @endif
</div>

@if(auth()->user()->role === 'superadmin')
<!-- Table Section (Superadmin only) -->
<div class="bg-white rounded-2xl border border-[#EFEFEF] shadow-sm overflow-hidden">
    <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center">
        <h3 class="text-lg font-bold text-[#1E2432]">Daftar Pegawai Terbaru</h3>
        <a href="{{ route('employees.index') }}" class="text-[#E85A4F] text-sm font-semibold hover:underline flex items-center gap-2">
            Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FCFBF9]">
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Nama Pegawai</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">NIP</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Jabatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFEFEF]">
                @foreach($latestEmployees as $employee)
                <tr class="hover:bg-[#FCFBF9] transition-all">
                    <td class="px-8 py-5 text-sm font-semibold text-[#1E2432]">{{ $employee->full_name }}</td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $employee->nip }}</td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $employee->position }}</td>
                </tr>
                @endforeach
                @if($latestEmployees->isEmpty())
                <tr>
                    <td colspan="3" class="px-8 py-10 text-center text-[#8A8A8A] text-sm italic">Belum ada data pegawai terbaru.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@else
<!-- Welcome Section (Pegawai) -->
<div class="bg-white p-10 rounded-3xl border border-[#EFEFEF] shadow-sm text-center">
    <div class="w-16 h-16 bg-[#E85A4F] rounded-2xl mx-auto mb-6 flex items-center justify-center text-white">
        <i data-lucide="user" class="w-8 h-8"></i>
    </div>
    <h2 class="text-2xl font-bold text-[#1E2432] mb-2">Selamat Datang, {{ auth()->user()->name }}</h2>
    <p class="text-[#8A8A8A] max-w-md mx-auto mb-8">Gunakan menu di samping untuk melihat dan mengunduh slip gaji atau dokumen kepegawaian Anda secara mandiri.</p>
    <a href="{{ route('documents.index') }}" class="inline-flex items-center gap-2 bg-[#E85A4F] text-white px-8 py-4 rounded-2xl font-bold hover:bg-[#d44d42] transition-all shadow-lg shadow-red-200">
        Lihat Dokumen Saya <i data-lucide="arrow-right" class="w-5 h-5"></i>
    </a>
</div>
@endif
</div>

<script>
    const ctx = document.getElementById('docChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($chartData->pluck('documents_count')) !!},
                backgroundColor: ['#E85A4F', '#1E2432', '#8A8A8A', '#EFEFEF'],
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, font: { family: 'Plus Jakarta Sans', weight: 'bold' } } }
            }
        }
    });
</script>
@endsection
