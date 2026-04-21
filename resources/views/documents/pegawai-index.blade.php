@extends('layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Pusat Dokumen Pribadi')

@section('content')
<div class="space-y-10 page-fade">
    <!-- Header Section with Statistics -->
    <div class="space-y-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight mb-2">Arsip Digital Anda</h2>
                <p class="text-sm text-slate-500 font-medium">Kelola dan pantau seluruh dokumen kepegawaian Anda secara mandiri.</p>
            </div>
            
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                class="w-full lg:w-auto bg-blue-600 text-white px-8 py-3.5 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition-all flex items-center justify-center gap-3 shadow-lg shadow-blue-200 btn-3d">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                Unggah Dokumen Baru
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover-lift">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Total Arsip</p>
                <h3 class="text-2xl font-bold text-slate-900">{{ $documents->count() }} <span class="text-[10px] text-slate-400 font-semibold ml-1">BERKAS</span></h3>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover-lift border-l-4 border-l-green-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Terverifikasi</p>
                <h3 class="text-2xl font-bold text-green-600">{{ $documents->where('status', 'verified')->count() }}</h3>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover-lift border-l-4 border-l-amber-500">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Proses Tinjau</p>
                <h3 class="text-2xl font-bold text-amber-600">{{ $documents->where('status', 'pending')->count() }}</h3>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover-lift">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Update Terakhir</p>
                <h3 class="text-xs font-bold text-slate-700 mt-2 uppercase tracking-tight">{{ $documents->first() ? $documents->first()->created_at->diffForHumans() : 'Belum ada data' }}</h3>
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($documents as $doc)
        <div class="group bg-white p-6 rounded-[32px] border border-slate-200 hover:border-blue-300 hover:shadow-xl transition-all duration-500 flex flex-col justify-between min-h-[300px] relative card-3d">
            <!-- Status Badge -->
            <div class="absolute top-6 right-6">
                @if($doc->status === 'verified')
                    <span class="bg-green-50 text-green-600 text-[8px] font-bold uppercase px-2 py-1 rounded-lg border border-green-100">Verified</span>
                @elseif($doc->status === 'rejected')
                    <span class="bg-red-50 text-red-600 text-[8px] font-bold uppercase px-2 py-1 rounded-lg border border-red-100">Rejected</span>
                @else
                    <span class="bg-amber-50 text-amber-600 text-[8px] font-bold uppercase px-2 py-1 rounded-lg border border-amber-100">Pending</span>
                @endif
            </div>

            <div>
                <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 mb-6 border border-slate-100 group-hover:border-blue-600">
                    @if(str_contains($doc->file_path, '.pdf'))
                        <i data-lucide="file-text" class="w-7 h-7"></i>
                    @elseif(str_contains($doc->file_path, '.xls'))
                        <i data-lucide="file-spreadsheet" class="w-7 h-7"></i>
                    @else
                        <i data-lucide="file" class="w-7 h-7"></i>
                    @endif
                </div>

                <h4 class="text-lg font-bold text-slate-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $doc->title }}</h4>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2">{{ $doc->category->name ?? 'Dokumen' }}</p>
            </div>

            <div class="flex items-center justify-between mt-8 pt-5 border-t border-slate-50">
                <div class="flex gap-2">
                    <button onclick="openRevisionModal({{ $doc->id }}, '{{ $doc->title }}')" class="w-10 h-10 bg-slate-50 flex items-center justify-center rounded-xl text-blue-600 hover:bg-blue-600 hover:text-white transition-all border border-slate-100" title="Revisi">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="flex gap-2">
                    @if(!$doc->is_locked)
                        <a href="{{ route('documents.download', $doc->id) }}" target="_blank" class="w-10 h-10 bg-slate-50 flex items-center justify-center rounded-xl text-amber-600 hover:bg-amber-600 hover:text-white transition-all no-loader border border-slate-100" title="Unduh">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </a>
                        <button type="button" onclick="confirmDocDelete({{ $doc->id }})" class="w-10 h-10 bg-slate-50 flex items-center justify-center rounded-xl text-red-500 hover:bg-red-600 hover:text-white transition-all border border-slate-100" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    @else
                        <div class="w-10 h-10 bg-slate-50 flex items-center justify-center rounded-xl text-slate-300 border border-slate-50 cursor-not-allowed" title="Dokumen dikunci">       
                            <i data-lucide="lock" class="w-4 h-4 opacity-50"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-32 text-center bg-white rounded-[40px] border border-dashed border-slate-200">
            <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="folder-search" class="w-10 h-10 text-slate-300"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">Arsip Digital Kosong</h3>
            <p class="text-sm text-slate-400 font-medium max-w-xs mx-auto mb-10">Mulai unggah dokumen kepegawaian Anda agar tersimpan dengan aman di sistem.</p>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-slate-900 text-white px-10 py-3.5 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">
                Unggah Sekarang
            </button>
        </div>
        @endforelse
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-lg rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-300 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-center mb-10">
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Unggah Arsip Baru</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Penyimpanan Digital Terenkripsi</p>
                </div>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Judul Dokumen</label>
                    <input type="text" name="title" required placeholder="Contoh: SK Kenaikan Pangkat 2024" 
                        class="w-full px-5 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:border-blue-500 transition-all">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Kategori</label>
                    <select name="document_category_id" required class="w-full px-5 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:border-blue-500 appearance-none cursor-pointer">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">File Berkas</label>
                    <div class="p-8 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center group hover:bg-white hover:border-blue-400 transition-all cursor-pointer relative">
                        <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                        <i data-lucide="file-up" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-blue-500 group-hover:scale-110 transition-all"></i>
                        <p id="uploadFileName" class="text-xs font-bold text-slate-500 group-hover:text-blue-600">Klik atau seret file ke sini</p>
                        <p class="text-[9px] text-slate-400 mt-1 uppercase font-bold">PDF, JPG, PNG (Max 5MB)</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d mt-4">
                    Simpan Dokumen
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div id="revisionModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-300">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h3 class="text-2xl font-bold text-slate-900">Kirim Revisi</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pembaruan Berkas Terdaftar</p>
            </div>
            <button onclick="document.getElementById('revisionModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="revisionForm" action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="document_id" id="revision_doc_id">
            <div class="p-5 bg-blue-50 rounded-2xl border border-blue-100 mb-6">
                <p class="text-[9px] font-bold text-blue-400 uppercase tracking-widest mb-1">Merevisi Dokumen:</p>
                <p id="revision_doc_title" class="text-sm font-bold text-blue-700"></p>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">File Baru</label>
                <div class="p-8 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center group hover:bg-white hover:border-blue-400 transition-all cursor-pointer relative">
                    <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileNameRev(this)">
                    <i data-lucide="refresh-cw" class="w-10 h-10 text-slate-300 mx-auto mb-3 group-hover:text-blue-500 group-hover:rotate-180 transition-all duration-700"></i>
                    <p id="revFileName" class="text-xs font-bold text-slate-500 group-hover:text-blue-600">Klik untuk ganti file</p>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl btn-3d">
                Kirim Revisi
            </button>
        </form>
    </div>
</div>

<form id="deleteDocForm" action="" method="POST" class="hidden no-loader">@csrf @method('DELETE')</form>

<script>
    function handleDownload(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('uploadFileName').textContent = input.files[0].name;
            document.getElementById('uploadFileName').classList.add('text-blue-600');
        }
    }
    function updateFileNameRev(input) {
        if (input.files && input.files[0]) {
            document.getElementById('revFileName').textContent = input.files[0].name;
            document.getElementById('revFileName').classList.add('text-blue-600');
        }
    }
    function openRevisionModal(id, title) {
        const modal = document.getElementById('revisionModal');
        const form = document.getElementById('revisionForm');
        form.action = `/documents/${id}/revision`;
        document.getElementById('revision_doc_id').value = id;
        document.getElementById('revision_doc_title').innerText = title;
        modal.classList.remove('hidden');
        lucide.createIcons();
    }
    function confirmDocDelete(id) {
        Swal.fire({
            title: 'Hapus Dokumen?',
            text: "Berkas akan dimusnahkan permanen dari server.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#0F172A',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-3xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteDocForm');
                form.action = `/documents/${id}`;
                form.submit();
            }
        });
    }
</script>
@endsection
