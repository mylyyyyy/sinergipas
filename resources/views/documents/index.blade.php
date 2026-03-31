@extends('layouts.app')

@section('title', 'Dokumen Pegawai')
@section('header-title', 'Pusat Dokumen Pegawai')

@section('content')
<div class="flex flex-col md:flex-row gap-6 justify-between items-start md:items-center mb-10">
    <div class="flex items-center gap-3 text-sm">
        <span class="text-[#E85A4F] font-semibold">Semua Folder</span>
        <span class="text-[#8A8A8A]">/</span>
        <span class="text-[#8A8A8A]">Data Pegawai</span>
    </div>

    <form action="{{ route('documents.index') }}" method="GET" class="relative w-full md:w-80 group">
        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari folder pegawai..." 
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#EFEFEF] bg-white text-xs outline-none focus:ring-2 focus:ring-[#E85A4F] transition-all shadow-sm">
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    @foreach($employees as $employee)
    <a href="{{ route('documents.employee', $employee->id) }}" 
        class="group bg-white p-8 rounded-[32px] border border-[#EFEFEF] hover:border-[#E85A4F] hover:shadow-2xl hover:shadow-red-100 transition-all duration-300 transform hover:-translate-y-2 flex flex-col justify-between h-[220px]">
        <div class="flex justify-between items-start">
            <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center text-[#E85A4F] group-hover:bg-[#E85A4F] group-hover:text-white transition-all duration-300">
                <i data-lucide="folder" class="w-7 h-7"></i>
            </div>
            <button class="text-[#8A8A8A] hover:text-[#1E2432]">
                <i data-lucide="more-vertical" class="w-5 h-5"></i>
            </button>
        </div>
        <div>
            <h3 class="text-lg font-bold text-[#1E2432] group-hover:text-[#E85A4F] transition-all">{{ $employee->full_name }}</h3>
            <p class="text-sm text-[#8A8A8A] mt-1">{{ $employee->documents_count }} Dokumen</p>
        </div>
    </a>
    @endforeach

    @if($employees->isEmpty())
    <div class="col-span-4 py-20 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
            <i data-lucide="folder-x" class="w-10 h-10"></i>
        </div>
        <p class="text-[#8A8A8A]">Belum ada folder pegawai.</p>
    </div>
    @endif
</div>
@endsection
