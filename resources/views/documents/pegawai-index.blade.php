@extends('layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Pusat Dokumen Pribadi')

@section('content')
<!-- Header Section with Statistics -->
<div class="mb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div>
            <h2 class="text-3xl font-black text-[#0F172A] tracking-tight mb-2">Arsip Digital Anda</h2>
            <p class="text-sm text-[#8A8A8A] font-medium">Halo, <span class="text-[#EAB308] font-bold">{{ auth()->user()->name }}</span>. Kelola dan akses seluruh dokumen kepegawaian Anda di sini.</p>
        </div>
        
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
            class="bg-[#EAB308] text-white px-10 py-4 rounded-[24px] font-black hover:bg-[#CA8A04] transition-all flex items-center gap-3 shadow-xl shadow-red-100 active:scale-95 group">
            <div class="bg-white/20 p-2 rounded-xl group-hover:rotate-12 transition-transform">
                <i data-lucide="upload-cloud" class="w-5 h-5 text-white"></i>
            </div>
            Unggah Dokumen Baru
        </button>
</div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-[32px] border border-[#EFEFEF] shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mb-1">Total Dokumen</p>
            <h3 class="text-2xl font-black text-[#0F172A]">{{ $documents->count() }} <span class="text-xs text-[#ABABAB] font-bold uppercase ml-1">Berkas</span></h3>
        </div>
        <div class="bg-white p-6 rounded-[32px] border border-[#EFEFEF] shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mb-1">Terverifikasi</p>
            <h3 class="text-2xl font-black text-green-600">{{ $documents->where('status', 'verified')->count() }} <span class="text-xs text-[#ABABAB] font-bold uppercase ml-1">Selesai</span></h3>
        </div>
        <div class="bg-white p-6 rounded-[32px] border border-[#EFEFEF] shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mb-1">Menunggu</p>
            <h3 class="text-2xl font-black text-orange-500">{{ $documents->where('status', 'pending')->count() }} <span class="text-xs text-[#ABABAB] font-bold uppercase ml-1">Proses</span></h3>
        </div>
        <div class="bg-white p-6 rounded-[32px] border border-[#EFEFEF] shadow-sm">
            <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mb-1">Pembaruan Terakhir</p>
            <h3 class="text-sm font-black text-[#0F172A] mt-2">{{ $documents->first() ? $documents->first()->created_at->diffForHumans() : '-' }}</h3>
        </div>
    </div>
</div>

<!-- Documents Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
    @forelse($documents as $doc)
    <div class="group bg-white p-8 rounded-[48px] border border-[#EFEFEF] hover:border-[#EAB308] hover:shadow-2xl hover:shadow-red-100/30 transition-all duration-500 flex flex-col justify-between h-[320px] relative overflow-hidden">
        <!-- Status Badge -->
        <div class="absolute top-6 right-8">
            @if($doc->status === 'verified')
                <span class="bg-green-50 text-green-600 text-[8px] font-black uppercase px-2.5 py-1 rounded-lg border border-green-100">Verified</span>
            @elseif($doc->status === 'rejected')
                <span class="bg-red-50 text-red-600 text-[8px] font-black uppercase px-2.5 py-1 rounded-lg border border-red-100">Rejected</span>
            @else
                <span class="bg-orange-50 text-orange-600 text-[8px] font-black uppercase px-2.5 py-1 rounded-lg border border-orange-100">Pending</span>
            @endif
        </div>

        <div>
            <div class="w-16 h-16 bg-[#F1F5F9] rounded-3xl flex items-center justify-center text-[#8A8A8A] group-hover:bg-[#EAB308] group-hover:text-white transition-all duration-500 mb-6 shadow-inner">
                @if(str_contains($doc->file_path, '.pdf'))
                    <i data-lucide="file-text" class="w-8 h-8 text-red-500 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.xls') || str_contains($doc->file_path, '.xlsx'))
                    <i data-lucide="file-spreadsheet" class="w-8 h-8 text-green-600 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.doc') || str_contains($doc->file_path, '.docx'))
                    <i data-lucide="file-text" class="w-8 h-8 text-blue-600 group-hover:text-white"></i>
                @elseif(str_contains($doc->file_path, '.jpg') || str_contains($doc->file_path, '.jpeg') || str_contains($doc->file_path, '.png'))   
                    <i data-lucide="image" class="w-8 h-8 text-blue-500 group-hover:text-white"></i>
                @else
                    <i data-lucide="file" class="w-8 h-8"></i>
                @endif
            </div>

            <h4 class="text-xl font-black text-[#0F172A] leading-tight group-hover:text-[#EAB308] transition-colors line-clamp-2">{{ $doc->title }}</h4>
            <p class="text-[10px] font-black text-[#ABABAB] uppercase tracking-[0.2em] mt-2">{{ $doc->category->name ?? 'Tanpa Kategori' }}</p>
        </div>

        <div class="flex items-center justify-between mt-auto pt-6 border-t border-dashed border-[#EFEFEF]">
            <div class="flex gap-2">
                <button onclick="openPreview('{{ route('documents.preview', $doc->id) }}', '{{ $doc->title }}', '{{ $doc->file_path }}')" class="bg-[#F1F5F9] p-3 rounded-2xl text-[#0F172A] hover:bg-[#0F172A] hover:text-white transition-all shadow-sm" title="Pratinjau">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
                <button onclick="openRevisionModal({{ $doc->id }}, '{{ $doc->title }}')" class="bg-[#F1F5F9] p-3 rounded-2xl text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Revisi">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="flex gap-2">
                @if(!$doc->is_locked)
                    <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="bg-[#F1F5F9] p-3 rounded-2xl text-[#EAB308] hover:bg-[#EAB308] hover:text-white transition-all no-loader shadow-sm" title="Unduh">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </a>
                    <form id="deleteDoc-{{ $doc->id }}" action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="no-loader">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDocDelete({{ $doc->id }})" class="bg-[#F1F5F9] p-3 rounded-2xl text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                @else
                    <div class="bg-gray-50 p-3 rounded-2xl text-gray-300 cursor-not-allowed border border-gray-100" title="Dokumen dikunci">       
                        <i data-lucide="lock" class="w-4 h-4 opacity-50"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-[48px] p-20 border border-dashed border-[#EFEFEF] text-center">
        <div class="w-24 h-24 bg-[#F1F5F9] rounded-[32px] flex items-center justify-center mx-auto mb-6">
            <i data-lucide="folder-open" class="w-10 h-10 text-[#ABABAB]"></i>
        </div>
        <h3 class="text-2xl font-black text-[#0F172A] mb-2">Belum Ada Dokumen</h3>
        <p class="text-sm text-[#8A8A8A] font-medium max-w-xs mx-auto mb-8">Anda belum mengunggah dokumen apapun ke dalam sistem.</p>
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-[#0F172A] text-white px-8 py-4 rounded-2xl font-black hover:bg-[#EAB308] transition-all shadow-xl">
            Unggah Sekarang
        </button>
    </div>
    @endforelse
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-lg rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-2xl font-black text-[#0F172A] tracking-tight">Unggah Arsip</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Penyimpanan Digital Aman</p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="bg-[#F1F5F9] p-3 rounded-2xl text-[#8A8A8A] hover:text-red-500 transition-all border border-[#EFEFEF]">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#0F172A] uppercase tracking-[0.2em] ml-1">Nama Dokumen</label>
                    <input type="text" name="title" required placeholder="Contoh: SK CPNS 2024" 
                        class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#F1F5F9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 transition-all">
                </div>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#0F172A] uppercase tracking-[0.2em] ml-1">Kategori</label>
                    <select name="document_category_id" required class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#F1F5F9] text-sm font-bold outline-none focus:ring-4 focus:ring-red-500/5 transition-all appearance-none cursor-pointer">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#0F172A] uppercase tracking-[0.2em] ml-1">Berkas Dokumen</label>
                    <div class="relative group">
                        <input type="file" name="file" required class="w-full px-6 py-12 rounded-[32px] border-2 border-dashed border-[#EFEFEF] bg-[#F1F5F9] text-xs font-bold text-[#8A8A8A] file:hidden cursor-pointer hover:border-[#EAB308] transition-all text-center">
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none opacity-40 group-hover:opacity-100 transition-opacity">
                            <i data-lucide="file-up" class="w-10 h-10 text-[#EAB308] mb-2"></i>
                            <span class="text-[10px] uppercase font-black">Seret atau Klik untuk Pilih File</span>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full bg-[#EAB308] text-white py-5 rounded-[28px] font-black text-lg hover:bg-[#CA8A04] transition-all shadow-xl shadow-red-200 active:scale-95 flex items-center justify-center gap-3">
                Simpan Dokumen <i data-lucide="check-circle" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-[#0F172A]/95 hidden flex items-center justify-center z-[100] p-6 md:p-12 backdrop-blur-xl">
    <div class="bg-white w-full h-full max-w-7xl rounded-[56px] overflow-hidden flex flex-col shadow-2xl relative">
        <div class="p-8 border-b border-[#EFEFEF] flex justify-between items-center bg-white">
            <div>
                <h3 id="previewTitle" class="text-2xl font-black text-[#0F172A]">Pratinjau</h3>
                <p class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest mt-1">Sistem Keamanan Sinergi PAS</p>
            </div>
            <button onclick="document.getElementById('previewModal').classList.add('hidden')" class="bg-[#F1F5F9] p-4 rounded-[24px] shadow-sm border border-[#EFEFEF] hover:bg-red-50 hover:text-red-500 transition-all group">
                <i data-lucide="x" class="w-7 h-7 group-hover:rotate-90 transition-transform"></i>
            </button>
        </div>
        <div class="flex-1 bg-[#F1F5F9] overflow-auto flex items-center justify-center p-6 md:p-12 relative" id="previewContent">
            <!-- Content Injected Here -->
            @if($watermarkEnabled)
            <div id="watermarkOverlay" class="absolute inset-0 pointer-events-none hidden flex-wrap gap-20 p-20 opacity-[0.03] overflow-hidden content-center justify-center select-none z-10">
                @for($i=0; $i<30; $i++)
                    <div class="text-5xl font-black -rotate-45 uppercase tracking-[1em] whitespace-nowrap">{{ $watermarkText }}</div>
                @endfor
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div id="revisionModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-md">
    <div class="bg-white w-full max-w-md rounded-[48px] p-12 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-2xl font-black text-[#0F172A]">Pembaruan</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Kirim Revisi Dokumen</p>
            </div>
            <button onclick="document.getElementById('revisionModal').classList.add('hidden')" class="bg-[#F1F5F9] p-3 rounded-2xl text-[#8A8A8A] hover:text-red-500 transition-all border border-[#EFEFEF]">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="revisionForm" action="" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <input type="hidden" name="document_id" id="revision_doc_id">
            <div class="space-y-4">
                <div class="bg-[#F1F5F9] p-6 rounded-3xl border border-[#EFEFEF]">
                    <p class="text-[10px] font-black text-[#ABABAB] uppercase tracking-widest mb-2">Merevisi Berkas:</p>
                    <p id="revision_doc_title" class="text-sm font-black text-[#EAB308]"></p>
                </div>
                <div class="relative group">
                    <input type="file" name="file" required class="w-full px-6 py-12 rounded-[32px] border-2 border-dashed border-[#EFEFEF] bg-[#F1F5F9] text-xs font-bold text-[#8A8A8A] file:hidden cursor-pointer hover:border-blue-500 transition-all text-center">
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none opacity-40 group-hover:opacity-100 transition-opacity">
                        <i data-lucide="refresh-cw" class="w-10 h-10 text-blue-600 mb-2"></i>
                        <span class="text-[10px] uppercase font-black">Pilih Berkas Baru</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full bg-[#0F172A] text-white py-5 rounded-[28px] font-black text-lg hover:bg-blue-600 transition-all shadow-xl active:scale-95 flex items-center justify-center gap-3">
                Unggah Revisi <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openPreview(url, title, filePath) {
        document.getElementById('previewTitle').innerText = title;
        const container = document.getElementById('previewContent');
        const watermark = document.getElementById('watermarkOverlay');
        
        container.innerHTML = '';
        if (filePath.toLowerCase().endsWith('.pdf')) {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.className = 'w-full h-full rounded-3xl border-0 shadow-2xl relative z-0 bg-white';
            container.appendChild(iframe);
        } else {
            const img = document.createElement('img');
            img.src = url;
            img.className = 'max-w-full max-h-full object-contain shadow-2xl rounded-3xl border-[12px] border-white relative z-0';
            container.appendChild(img);
        }

        if (watermark) {
            watermark.classList.remove('hidden');
            watermark.classList.add('flex');
        }
        document.getElementById('previewModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openRevisionModal(id, title) {
        document.getElementById('revisionForm').action = `/documents/${id}/revision`;
        document.getElementById('revision_doc_id').value = id;
        document.getElementById('revision_doc_title').innerText = title;
        document.getElementById('revisionModal').classList.remove('hidden');
    }

    function confirmDocDelete(id) {
        Swal.fire({
            title: 'Hapus Dokumen?',
            text: "Berkas ini akan dihapus permanen dari sistem kami.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EAB308',
            cancelButtonColor: '#0F172A',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { 
                popup: 'rounded-[48px]',
                confirmButton: 'rounded-2xl px-8 py-3',
                cancelButton: 'rounded-2xl px-8 py-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteDoc-' + id).submit();
            }
        });
    }
</script>
@endpush
