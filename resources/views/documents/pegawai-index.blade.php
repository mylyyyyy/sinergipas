@extends('layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Pusat Dokumen Pribadi')

@section('content')
<div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
    <div class="text-sm">
        <p class="text-[#8A8A8A]">Halo, <span class="text-[#1E2432] font-black">{{ auth()->user()->name }}</span>. Berikut adalah dokumen Anda.</p>
    </div>
    
    <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
        class="bg-[#E85A4F] text-white px-8 py-3.5 rounded-2xl font-black hover:bg-[#d44d42] transition-all flex items-center gap-2 shadow-lg shadow-red-100 active:scale-95">
        <i data-lucide="upload-cloud" class="w-5 h-5 text-white"></i>
        Unggah Dokumen Baru
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
    @foreach($documents as $doc)
    <div class="group bg-white p-8 rounded-[40px] border border-[#EFEFEF] hover:border-[#E85A4F] hover:shadow-2xl hover:shadow-red-100/50 transition-all duration-500 transform hover:-translate-y-2 flex flex-col justify-between h-[240px]">
        <div class="flex justify-between items-start">
            <div class="w-14 h-14 bg-[#F5F4F2] rounded-2xl flex items-center justify-center text-[#8A8A8A] group-hover:bg-[#E85A4F] group-hover:text-white transition-all duration-500">
                @if(str_contains($doc->file_path, '.pdf'))
                    <i data-lucide="file-text" class="w-7 h-7 text-red-500 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.xls'))
                    <i data-lucide="file-spreadsheet" class="w-7 h-7 text-green-600 group-hover:text-white"></i>
                @else
                    <i data-lucide="file" class="w-7 h-7"></i>
                @endif
            </div>
            <div class="flex gap-2">
                <button onclick="openPreview('{{ Storage::url($doc->file_path) }}', '{{ $doc->title }}')" class="bg-green-50 p-3 rounded-xl text-green-600 hover:bg-green-600 hover:text-white transition-all">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
                <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="bg-[#FCFBF9] p-3 rounded-xl text-[#E85A4F] hover:bg-[#E85A4F] hover:text-white transition-all no-loader">
                    <i data-lucide="download" class="w-4 h-4"></i>
                </a>
                <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 p-3 rounded-xl text-red-500 hover:bg-red-500 hover:text-white transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
        <div>
            <h3 class="text-lg font-black text-[#1E2432] truncate group-hover:text-[#E85A4F] transition-all" title="{{ $doc->title }}">{{ $doc->title }}</h3>
            <div class="flex items-center gap-3 mt-2">
                <span class="px-3 py-1 bg-gray-100 text-[#1E2432] text-[10px] font-black rounded-lg uppercase tracking-widest">{{ $doc->category->name }}</span>
                <span class="text-[10px] font-bold text-[#ABABAB]">{{ $doc->created_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>
    @endforeach

    @if($documents->isEmpty())
    <div class="col-span-4 py-24 text-center bg-white rounded-[56px] border-4 border-dashed border-[#F5F4F2]">
        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-200">
            <i data-lucide="folder-open" class="w-12 h-12"></i>
        </div>
        <p class="text-[#8A8A8A] font-bold uppercase tracking-widest text-xs">Belum ada dokumen yang diunggah.</p>
    </div>
    @endif
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-lg rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <h3 class="text-2xl font-black text-[#1E2432] tracking-tight">Unggah Dokumen Saya</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-[#8A8A8A] hover:text-[#1E2432] transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
        </div>
        
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Kategori Dokumen</label>
                <select name="document_category_id" required class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 appearance-none cursor-pointer">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Judul Dokumen</label>
                <input type="text" name="title" required placeholder="Contoh: Slip Gaji Maret" class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5">
            </div>
            
            <div class="space-y-3">
                <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Pilih File (PDF/Excel)</label>
                <div class="relative group">
                    <input type="file" name="file" required class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-xs font-bold text-[#8A8A8A] file:hidden cursor-pointer">
                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-[#E85A4F] font-black text-[10px] uppercase tracking-widest group-hover:underline">Browse</div>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[28px] font-black text-lg hover:bg-[#d44d42] transition-all shadow-xl shadow-red-200 active:scale-95 flex items-center justify-center gap-3">
                Mulai Unggah
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-[100] p-10 backdrop-blur-xl">
    <div class="bg-white w-full h-full max-w-6xl rounded-[48px] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center bg-[#FCFBF9]/50">
            <h3 id="previewTitle" class="text-xl font-black text-[#1E2432]">Pratinjau Dokumen</h3>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="bg-white p-3 rounded-2xl shadow-sm border border-[#EFEFEF] hover:bg-red-50 hover:text-red-500 transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 bg-gray-100">
            <iframe id="previewFrame" src="" class="w-full h-full border-none"></iframe>
        </div>
    </div>
</div>

<script>
    function openPreview(url, title) {
        document.getElementById('previewTitle').innerText = title;
        document.getElementById('previewFrame').src = url;
        document.getElementById('previewModal').classList.remove('hidden');
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: "{{ session('success') }}", 
        confirmButtonColor: '#E85A4F',
        customClass: { popup: 'rounded-[40px]' }
    });
</script>
@endif
@endsection
