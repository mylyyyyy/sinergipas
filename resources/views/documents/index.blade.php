@extends('layouts.app')

@section('title', 'Arsip Digital')
@section('header-title', 'Pusat Dokumen & Arsip')

@section('content')
<div class="space-y-10 page-fade">
    <!-- Header & Categories -->
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex bg-white p-1 rounded-2xl border border-slate-200 shadow-sm card-3d">
                <a href="{{ route('documents.index') }}" 
                    class="px-6 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all {{ !request('status') ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50' }}">
                    Seluruh Pegawai
                </a>
                <a href="{{ route('documents.index', ['status' => 'pending']) }}" 
                    class="px-6 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-50' }} flex items-center gap-2">
                    Perlu Tinjauan
                    @php $pendingCount = \App\Models\Document::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="bg-white text-slate-900 px-1.5 py-0.5 rounded-lg text-[9px] font-bold">{{ $pendingCount }}</span>
                    @endif
                </a>
            </div>

            <div class="flex items-center gap-4 w-full md:w-auto">
                <form action="{{ route('documents.index') }}" method="GET" class="relative flex-1 md:w-80 no-loader">
                    <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIP..." 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold outline-none focus:border-blue-500 shadow-sm transition-all">
                </form>
                <button onclick="document.getElementById('categoryModal').classList.remove('hidden')" 
                    class="bg-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider hover:bg-slate-800 transition-all shadow-lg btn-3d">
                    + Kategori
                </button>
            </div>
        </div>

        <!-- Category Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($categories as $cat)
            <div class="group relative bg-white p-5 rounded-2xl border border-slate-200 hover:border-blue-300 transition-all shadow-sm flex flex-col justify-between h-36 card-3d">
                <div class="flex justify-between items-start">
                    <div class="w-10 h-10 {{ $cat->is_mandatory ? 'bg-red-50 text-red-600' : 'bg-slate-50 text-slate-400' }} rounded-xl flex items-center justify-center border border-slate-100 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all">
                        <i data-lucide="{{ $cat->is_mandatory ? 'alert-circle' : 'folder' }}" class="w-5 h-5"></i>
                    </div>
                    <div class="flex items-center gap-1 relative z-10">
                        @if($cat->is_mandatory)
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse" title="Kategori Wajib"></span>
                        @endif
                        <button type="button" onclick="confirmDeleteCategory({{ $cat->id }})" class="p-1.5 text-slate-300 hover:text-red-500 transition-all">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 line-clamp-1">{{ $cat->name }}</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ $cat->documents_count ?? 0 }} Dokumen</p>
                </div>
                <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" class="absolute inset-0 z-0"></a>
                <form id="deleteCatForm-{{ $cat->id }}" action="{{ route('documents.category.destroy', $cat->id) }}" method="POST" class="hidden">
                    @csrf @method('DELETE')
                </form>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Employee Grid -->
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="database" class="w-4 h-4"></i>
                Basis Data Pegawai {{ request('status') === 'pending' ? '(Perlu Tinjauan)' : '' }}
            </h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $employees->count() }} Entitas Terpilih</p>
        </div>
        
        @if($employees->isEmpty())
            <div class="py-20 text-center bg-white rounded-3xl border border-dashed border-slate-200 card-3d">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="search-x" class="w-8 h-8 text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Tidak ada data ditemukan</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($employees as $employee)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col card-3d">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 font-bold group-hover:scale-105 transition-transform">
                                    @if($employee->photo)
                                        <img src="{{ $employee->photo }}" class="w-full h-full object-cover">
                                    @else
                                        <i data-lucide="user" class="w-6 h-6 opacity-30"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-bold text-slate-900 truncate">{{ $employee->full_name }}</h3>
                                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-tight truncate">{{ $employee->position }}</p>
                                </div>
                            </div>
                            <a href="{{ route('documents.employee', $employee->id) }}" 
                                class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center hover:bg-blue-600 transition-all shadow-lg btn-3d shrink-0">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </a>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total Arsip</p>
                                <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $employee->documents_count }} <span class="text-[10px] text-slate-400 font-semibold uppercase">Berkas</span></p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(request('status') === 'pending' && $employee->pending_count > 0)
                                    <span class="px-2 py-1 bg-amber-100 text-amber-600 text-[9px] font-bold rounded-lg border border-amber-200">
                                        {{ $employee->pending_count }} PENDING
                                    </span>
                                @endif
                                <div class="flex -space-x-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 border-2 border-white flex items-center justify-center text-[8px] font-bold text-blue-600 shadow-sm">PDF</div>
                                    <div class="w-8 h-8 rounded-full bg-green-50 border-2 border-white flex items-center justify-center text-[8px] font-bold text-green-600 shadow-sm">XLS</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-3xl p-8 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -mr-12 -mt-12 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-xl font-bold text-slate-900">Kategori Baru</h3>
                <button onclick="document.getElementById('categoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form action="{{ route('documents.category.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Kategori</label>
                    <input type="text" name="name" required placeholder="Contoh: SK CPNS" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:border-blue-500 transition-all">
                </div>
                
                <label class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-white transition-all group">
                    <input type="checkbox" name="is_mandatory" value="1" class="w-5 h-5 rounded-lg border-slate-200 text-blue-600 focus:ring-0">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-700 group-hover:text-blue-600">Tandai sebagai Dokumen Wajib</span>
                        <span class="text-[9px] text-slate-400 font-medium">Pegawai akan mendapat peringatan jika belum mengunggah ini.</span>
                    </div>
                </label>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg btn-3d">
                    Simpan Kategori
                </button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-2xl' } });
    });
</script>
@endif

<script>
    function confirmDeleteCategory(id) {
        Swal.fire({
            title: 'Hapus Kategori?',
            text: "Pastikan tidak ada dokumen di dalam kategori ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#94A3B8',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteCatForm-' + id).submit();
            }
        });
    }
</script>
@endsection
