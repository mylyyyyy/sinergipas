@extends('layouts.app')

@section('title', 'Pusat Dokumen')
@section('header-title', 'Pusat Dokumen Digital')

@section('content')
<!-- Search & Actions -->
<div class="flex flex-col md:flex-row gap-6 justify-between items-start md:items-center mb-12">
    <div class="flex items-center gap-3 text-sm">
        <span class="text-[#E85A4F] font-black uppercase tracking-widest">Semua Kategori</span>
        <span class="text-[#8A8A8A]">/</span>
        <span class="text-[#8A8A8A] font-bold uppercase tracking-widest">Data Pegawai</span>
    </div>

    <div class="flex gap-4 w-full md:w-auto">
        <form action="{{ route('documents.index') }}" method="GET" class="relative flex-1 md:w-80 group no-loader">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8A8A] group-focus-within:text-[#E85A4F] transition-all"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari folder pegawai..." 
                class="w-full pl-10 pr-4 py-3 rounded-2xl border border-[#EFEFEF] bg-white text-xs font-bold outline-none focus:ring-4 focus:ring-red-500/5 transition-all shadow-sm">
        </form>

        <button onclick="document.getElementById('categoryModal').classList.remove('hidden')" class="bg-[#1E2432] text-white px-6 py-3 rounded-2xl text-xs font-black hover:bg-[#343b4d] transition-all flex items-center gap-2 shadow-lg active:scale-95">
            <i data-lucide="folder-plus" class="w-4 h-4"></i>
            Tambah Kategori
        </button>
    </div>
</div>

<!-- Row 1: Document Categories -->
<h3 class="text-xs font-black text-[#8A8A8A] uppercase tracking-[0.3em] mb-6">Ringkasan Kategori</h3>
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
    @foreach($categories as $cat)
    <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" 
        class="group bg-white p-6 rounded-[32px] border border-[#EFEFEF] hover:border-[#E85A4F] hover:shadow-xl transition-all duration-500 flex flex-col justify-between h-40 {{ request('category_id') == $cat->id ? 'border-[#E85A4F] ring-4 ring-red-500/5' : '' }}">
        <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center text-[#E85A4F] group-hover:bg-[#E85A4F] group-hover:text-white transition-all">
            <i data-lucide="layers" class="w-5 h-5"></i>
        </div>
        <div>
            <h4 class="text-sm font-black text-[#1E2432] truncate">{{ $cat->name }}</h4>
            <p class="text-[10px] font-bold text-[#8A8A8A] mt-1 uppercase tracking-widest">{{ $cat->documents_count ?? 0 }} Dokumen</p>
        </div>
    </a>
    @endforeach
</div>

<!-- Row 2: Employee Folders -->
<div class="flex items-center justify-between mb-8">
    <h3 class="text-xs font-black text-[#8A8A8A] uppercase tracking-[0.3em]">Folder Pegawai</h3>
    @if(request('category_id'))
        <a href="{{ route('documents.index') }}" class="text-[10px] font-black text-[#E85A4F] uppercase tracking-widest hover:underline flex items-center gap-1">
            <i data-lucide="x" class="w-3 h-3"></i> Bersihkan Filter
        </a>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    @foreach($employees as $employee)
    <a href="{{ route('documents.employee', $employee->id) }}" 
        class="group bg-white p-8 rounded-[40px] border border-[#EFEFEF] hover:border-[#E85A4F] hover:shadow-2xl hover:shadow-red-100 transition-all duration-500 transform hover:-translate-y-2 flex flex-col justify-between h-[240px]">
        <div class="flex justify-between items-start">
            <div class="w-16 h-16 bg-[#F5F4F2] rounded-3xl flex items-center justify-center text-[#8A8A8A] group-hover:bg-[#E85A4F] group-hover:text-white transition-all duration-500 shadow-sm group-hover:shadow-lg group-hover:shadow-red-200">
                <i data-lucide="folder" class="w-8 h-8"></i>
            </div>
            <div class="text-[10px] font-black text-[#ABABAB] bg-[#FCFBF9] px-3 py-1 rounded-full uppercase tracking-tighter">SDM</div>
        </div>
        <div>
            <h3 class="text-lg font-black text-[#1E2432] group-hover:text-[#E85A4F] transition-all">{{ $employee->full_name }}</h3>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                <p class="text-xs font-bold text-[#8A8A8A]">{{ $employee->documents_count }} Dokumen Tersedia</p>
            </div>
        </div>
    </a>
    @endforeach

    @if($employees->isEmpty())
    <div class="col-span-4 py-24 text-center bg-white rounded-[56px] border border-dashed border-[#EFEFEF]">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-200">
            <i data-lucide="folder-x" class="w-10 h-10"></i>
        </div>
        <p class="text-[#8A8A8A] font-bold uppercase tracking-widest text-xs">Belum ada folder yang ditemukan.</p>
    </div>
    @endif
</div>

<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-md rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-black text-[#1E2432] tracking-tight">Kategori Baru</h3>
            <button onclick="document.getElementById('categoryModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        <form action="{{ route('documents.category.store') }}" method="POST" class="space-y-8">
            @csrf
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Nama Kategori Dokumen</label>
                <input type="text" name="name" required placeholder="Contoh: SK Kenaikan Pangkat" 
                    class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5">
            </div>
            <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-black text-lg hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-95">
                Simpan Kategori
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#E85A4F', customClass: { popup: 'rounded-[40px]' } });
</script>
@endif
@endsection
