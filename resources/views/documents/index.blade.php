@extends('layouts.app')

@section('title', 'Pusat Dokumen Premium')
@section('header-title', 'Arsip Digital Pegawai')

@section('content')
<!-- Search & Tabs Row -->
<div class="flex flex-col md:flex-row gap-8 justify-between items-center mb-12">
    <div class="flex bg-white p-1.5 rounded-[24px] border border-[#EFEFEF] shadow-sm">
        <a href="{{ route('documents.index') }}" 
            class="px-8 py-3 rounded-[20px] text-[10px] font-black uppercase tracking-widest transition-all {{ !request('status') ? 'bg-[#0F172A] text-white shadow-xl' : 'text-[#8A8A8A] hover:bg-[#F1F5F9]' }}">
            Seluruh Pegawai
        </a>
        @php $pendingGlobal = \App\Models\Document::where('status', 'pending')->count(); @endphp
        <a href="{{ route('documents.index', ['status' => 'pending']) }}" 
            class="px-8 py-3 rounded-[20px] text-[10px] font-black uppercase tracking-widest transition-all {{ request('status') === 'pending' ? 'bg-[#EAB308] text-white shadow-xl' : 'text-[#8A8A8A] hover:bg-[#F1F5F9]' }} flex items-center gap-2">
            Perlu Tinjauan
            @if($pendingGlobal > 0)
                <span class="bg-white text-[#EAB308] px-1.5 py-0.5 rounded-md text-[8px] font-black">{{ $pendingGlobal }}</span>
            @endif
        </a>
    </div>

    <div class="flex gap-4 w-full md:w-auto">
        <form action="{{ route('documents.index') }}" method="GET" class="relative flex-1 md:w-80 group no-loader">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#ABABAB]"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIP..." 
                class="w-full pl-10 pr-4 py-3.5 rounded-2xl border border-[#EFEFEF] bg-white text-xs font-bold outline-none focus:ring-4 focus:ring-red-500/5 transition-all shadow-sm">
        </form>
        <button onclick="document.getElementById('categoryModal').classList.remove('hidden')" 
            class="bg-white text-[#0F172A] border border-[#EFEFEF] px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-[#F1F5F9] transition-all shadow-sm">
            + Kategori
        </button>
    </div>
</div>

<!-- Category Bento Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
    @foreach($categories as $cat)
    <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" 
        class="group relative overflow-hidden bg-white p-8 rounded-[48px] border {{ $cat->is_mandatory ? 'border-red-100 shadow-red-50' : 'border-[#EFEFEF]' }} hover:border-[#EAB308] transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-red-100 flex flex-col justify-between h-48">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-700 scale-0 group-hover:scale-150"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div class="w-12 h-12 {{ $cat->is_mandatory ? 'bg-red-50 text-[#EAB308]' : 'bg-[#F1F5F9]' }} rounded-2xl flex items-center justify-center border border-[#EFEFEF] group-hover:bg-[#EAB308] group-hover:text-white transition-all duration-500 shadow-sm">
                <i data-lucide="{{ $cat->is_mandatory ? 'alert-circle' : 'layers' }}" class="w-5 h-5"></i>
            </div>
            <div class="flex flex-col items-end gap-2">
                @if($cat->is_mandatory)
                    <span class="bg-red-500 text-white text-[7px] font-black uppercase px-2 py-1 rounded-lg tracking-widest animate-pulse">Wajib</span>
                @endif
                <button type="button" onclick="confirmDeleteCategory({{ $cat->id }})" class="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-all"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
                <form id="deleteCatForm-{{ $cat->id }}" action="{{ route('documents.category.destroy', $cat->id) }}" method="POST" class="hidden no-loader">
                    @csrf @method('DELETE')
                </form>
            </div>
        </div>
        <div class="relative z-10">
            <h4 class="text-lg font-black text-[#0F172A] group-hover:text-[#EAB308] transition-all">{{ $cat->name }}</h4>
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] mt-1">{{ $cat->documents_count ?? 0 }} Dokumen</p>
        </div>
    </a>
    @endforeach
</div>

<!-- Employee Intelligent Cards -->
<div class="flex items-center justify-between mb-10">
    <h3 class="text-xs font-black text-[#8A8A8A] uppercase tracking-[0.4em]">Basis Data Pegawai</h3>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    @foreach($employees as $employee)
    <div class="group bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm hover:shadow-2xl hover:border-[#EAB308] transition-all duration-500 overflow-hidden">
        <div class="p-10">
            <div class="flex items-start justify-between mb-8">
                <div class="w-20 h-20 rounded-[32px] bg-[#F1F5F9] border border-[#EFEFEF] p-1.5 shadow-inner">
                    <div class="w-full h-full rounded-[24px] overflow-hidden bg-[#EAB308] flex items-center justify-center text-white text-xl font-black shadow-lg">
                        @if($employee->photo)
                            <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($employee->full_name, 0, 1) }}
                        @endif
                    </div>
                </div>

                <a href="{{ route('documents.employee', $employee->id) }}" 
                    class="bg-[#0F172A] text-white p-4 rounded-[24px] hover:bg-[#EAB308] transition-all shadow-xl active:scale-90">
                    <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
                </a>
            </div>
            
            <h3 class="text-xl font-black text-[#0F172A] leading-tight mb-1">{{ $employee->full_name }}</h3>
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest">{{ $employee->position }}</p>
            
            <div class="mt-8 pt-8 border-t border-dashed border-[#EFEFEF] flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-[#ABABAB] uppercase tracking-widest">Total Arsip</p>
                    <p class="text-lg font-black text-[#0F172A] mt-1">{{ $employee->documents_count }} <span class="text-xs text-[#8A8A8A]">File</span></p>
                </div>
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full border-4 border-white bg-red-50 flex items-center justify-center text-[#EAB308] text-[10px] font-black uppercase">PDF</div>
                    <div class="w-10 h-10 rounded-full border-4 border-white bg-green-50 flex items-center justify-center text-green-600 text-[10px] font-black uppercase">XLS</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Modal Kategori (Tetap Sama) -->
<div id="categoryModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-md rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-black text-[#0F172A] tracking-tight">Kategori Baru</h3>
            <button onclick="document.getElementById('categoryModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        <form action="{{ route('documents.category.store') }}" method="POST" class="space-y-8">
            @csrf
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#0F172A] uppercase tracking-[0.2em] ml-1">Nama Kategori Dokumen</label>
                <input type="text" name="name" required placeholder="Contoh: SK Kenaikan Pangkat" 
                    class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#F1F5F9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5">
            </div>
            
            <div class="flex items-center gap-3 px-2">
                <input type="checkbox" name="is_mandatory" value="1" id="is_mandatory" class="w-5 h-5 rounded-lg border-[#EFEFEF] text-[#EAB308] focus:ring-0">
                <label for="is_mandatory" class="text-xs font-bold text-[#0F172A] cursor-pointer">Tandai sebagai Dokumen Wajib (Mandatory)</label>
            </div>

            <button type="submit" class="w-full bg-[#EAB308] text-white py-5 rounded-[24px] font-black text-lg hover:bg-[#CA8A04] transition-all shadow-xl shadow-red-200 active:scale-95">
                Simpan Kategori
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#EAB308', customClass: { popup: 'rounded-[40px]' } });
</script>
@endif

<script>
    function confirmDeleteCategory(id) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: "Kategori akan dihapus permanen. Pastikan tidak ada dokumen di dalamnya.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EAB308',
            cancelButtonColor: '#8A8A8A',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batalkan',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteCatForm-' + id).submit();
            }
        });
    }
</script>
@endsection
