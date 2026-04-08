@extends('layouts.app')

@section('title', 'Kategori Pegawai')
@section('header-title', 'Manajemen Kategori')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header & Tools -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex items-center gap-6">
            <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors font-bold text-[10px] uppercase tracking-widest">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm">
                <input type="checkbox" id="selectAllCategories" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pilih Semua</span>
            </div>
            <!-- Bulk delete could be added later if needed, but for now we focus on CRUD and membership -->
        </div>

        <button onclick="document.getElementById('categoryModal').classList.remove('hidden')" class="px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold text-[10px] uppercase tracking-wider hover:bg-slate-900 transition-all shadow-lg btn-3d flex items-center justify-center gap-2">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kategori Baru
        </button>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($categories as $cat)
        <div class="relative bg-white p-8 rounded-[40px] border border-slate-200 shadow-sm hover:border-indigo-300 transition-all card-3d group">
            <div class="flex justify-end items-start mb-6">
                <div class="flex gap-2">
                    <button onclick="openEditModal({{ json_encode($cat) }})" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" class="no-loader">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Hapus kategori ini? Pegawai di dalamnya akan kehilangan kategori mereka.')" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-20 h-20 rounded-[28px] bg-{{ $cat->color }}-50 text-{{ $cat->color }}-600 flex items-center justify-center border border-{{ $cat->color }}-100 transition-all mb-4 shadow-inner group-hover:scale-110 duration-500">
                    <i data-lucide="tag" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900 italic tracking-tight">{{ $cat->name }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 min-h-[1.5rem]">{{ $cat->description ?? 'Tidak ada deskripsi' }}</p>
            </div>
            
            <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                <div class="text-left">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Personel</p>
                    <p class="text-lg font-black text-slate-900">{{ $cat->employees_count }} <span class="text-[10px] text-slate-400 font-bold">PEGAWAI</span></p>
                </div>
                <button onclick="openManageMembersModal({{ json_encode($cat->load('employees')) }})" class="px-5 py-2.5 rounded-2xl bg-indigo-600 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg">
                    Kelola Anggota
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-24 text-center bg-white rounded-[48px] border border-dashed border-slate-200">
            <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="tag" class="w-10 h-10 text-indigo-200"></i>
            </div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-[0.2em] italic">Belum ada kategori yang terdaftar</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modals -->
<div id="categoryModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-2xl font-bold text-slate-900 mb-8">Tambah Kategori Baru</h3>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Kategori</label>
                <input type="text" name="name" required placeholder="Contoh: Rupam, P2U, CPNS" class="w-full px-5 py-3 rounded-2xl border border-slate-200 text-sm font-bold focus:border-indigo-500 bg-slate-50 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Warna Identitas</label>
                <select name="color" required class="w-full px-5 py-3 rounded-2xl border border-slate-200 text-sm font-bold focus:border-indigo-500 bg-slate-50 outline-none">
                    <option value="indigo">Indigo (Default)</option>
                    <option value="blue">Blue</option>
                    <option value="emerald">Emerald</option>
                    <option value="amber">Amber</option>
                    <option value="rose">Rose</option>
                    <option value="slate">Slate</option>
                    <option value="violet">Violet</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Opsional..." class="w-full px-5 py-3 rounded-2xl border border-slate-200 text-sm font-bold focus:border-indigo-500 bg-slate-50 outline-none"></textarea>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('categoryModal').classList.add('hidden')" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button type="submit" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-lg">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[32px] p-10 shadow-2xl animate-in zoom-in duration-200">
        <h3 class="text-2xl font-bold text-slate-900 mb-8">Edit Kategori</h3>
        <form id="editForm" method="POST" class="space-y-6">
            @csrf @method('PUT')
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Nama Kategori</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-5 py-3 rounded-2xl border border-slate-200 text-sm font-bold focus:border-indigo-500 bg-slate-50 outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Warna Identitas</label>
                <select name="color" id="edit_color" required class="w-full px-5 py-3 rounded-2xl border border-slate-200 text-sm font-bold focus:border-indigo-500 bg-slate-50 outline-none">
                    <option value="indigo">Indigo</option>
                    <option value="blue">Blue</option>
                    <option value="emerald">Emerald</option>
                    <option value="amber">Amber</option>
                    <option value="rose">Rose</option>
                    <option value="slate">Slate</option>
                    <option value="violet">Violet</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Deskripsi</label>
                <textarea name="description" id="edit_description" rows="3" class="w-full px-5 py-3 rounded-2xl border border-slate-200 text-sm font-bold focus:border-indigo-500 bg-slate-50 outline-none"></textarea>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button type="submit" class="flex-[2] py-4 bg-indigo-600 text-white rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Members Modal -->
<div id="membersModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-5xl rounded-[40px] shadow-2xl animate-in zoom-in duration-200 flex flex-col max-h-[90vh]">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center shrink-0">
            <div>
                <h3 id="members_category_name" class="text-2xl font-bold text-slate-900 italic">Kelola Anggota</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Daftar Pegawai dalam Kategori Ini</p>
            </div>
            <button onclick="document.getElementById('membersModal').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex-1 overflow-hidden flex flex-col lg:flex-row">
            <!-- Current Members Section -->
            <div class="flex-1 p-8 border-r border-slate-100 overflow-y-auto custom-scrollbar">
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pegawai Saat Ini</h4>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="confirmBulkRemoveMembers()" id="btnBulkRemove" class="hidden px-4 py-1.5 bg-red-50 text-red-600 border border-red-100 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all">
                            Hapus Terpilih (<span id="memberSelectedCount">0</span>)
                        </button>
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-100">
                            <input type="checkbox" id="selectAllMembers" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-0 cursor-pointer">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">Pilih Semua</span>
                        </div>
                    </div>
                </div>
                
                <form id="bulkRemoveMembersForm" method="POST" class="no-loader">
                    @csrf
                    <div id="current_members_list" class="space-y-3"></div>
                </form>
            </div>

            <!-- Add Members Section -->
            <div class="lg:w-96 p-8 bg-slate-50 overflow-y-auto custom-scrollbar">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Assign Pegawai</h4>
                <form id="addMemberForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="max-h-[400px] overflow-y-auto pr-2 space-y-2">
                            @forelse($unassignedEmployees as $emp)
                            <label class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-slate-200 cursor-pointer hover:border-indigo-400 transition-all">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" class="w-5 h-5 rounded-lg border-slate-200 text-indigo-600 focus:ring-0">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold text-slate-900 truncate">{{ $emp->full_name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">NIP. {{ $emp->nip }}</p>
                                </div>
                            </label>
                            @empty
                            <div class="text-center py-10">
                                <i data-lucide="user-x" class="w-8 h-8 text-slate-200 mx-auto mb-2"></i>
                                <p class="text-[10px] font-bold text-slate-400 italic">Semua pegawai sudah memiliki kategori.</p>
                            </div>
                            @endforelse
                        </div>
                        <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl btn-3d">
                            Tambahkan ke Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Selection Handlers
    const selectAllCategories = document.getElementById('selectAllCategories');
    // ... bulk delete logic for categories can be added here ...

    // Modal Handlers
    function openEditModal(category) {
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_description').value = category.description;
        document.getElementById('edit_color').value = category.color;
        document.getElementById('editForm').action = `/admin/categories/${category.id}`;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function openManageMembersModal(category) {
        document.getElementById('members_category_name').innerText = `Kelola Kategori ${category.name}`;
        document.getElementById('addMemberForm').action = `/admin/categories/${category.id}/add-member`;
        document.getElementById('bulkRemoveMembersForm').action = `/admin/categories/${category.id}/remove-members-bulk`;
        
        const list = document.getElementById('current_members_list');
        list.innerHTML = '';
        
        if (category.employees.length === 0) {
            list.innerHTML = `<div class="text-center py-20"><i data-lucide="tag" class="w-12 h-12 text-slate-100 mx-auto mb-4"></i><p class="text-[10px] font-bold text-slate-400 italic uppercase tracking-widest">Belum ada pegawai dalam kategori ini.</p></div>`;
        } else {
            category.employees.forEach(emp => {
                list.innerHTML += `
                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 group hover:border-indigo-200 hover:shadow-md transition-all">
                        <div class="flex items-center gap-4">
                            <input type="checkbox" name="employee_ids[]" value="${emp.id}" class="member-checkbox w-4 h-4 rounded border-slate-300 text-red-600 focus:ring-0 cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase">${emp.full_name.substring(0, 1)}</div>
                            <div>
                                <p class="text-[11px] font-black text-slate-900">${emp.full_name}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">NIP. ${emp.nip}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="/admin/categories/${category.id}/remove-member" method="POST" class="no-loader">
                                @csrf <input type="hidden" name="employee_id" value="${emp.id}">
                                <button type="submit" class="p-2 text-slate-300 hover:text-red-500 transition-colors"><i data-lucide="user-minus" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </div>
                `;
            });
        }
        
        document.getElementById('membersModal').classList.remove('hidden');
        lucide.createIcons();

        // Re-bind member checkboxes
        document.querySelectorAll('.member-checkbox').forEach(cb => {
            cb.addEventListener('change', updateMemberUI);
        });
        updateMemberUI();
    }

    const selectAllMembers = document.getElementById('selectAllMembers');
    const btnBulkRemove = document.getElementById('btnBulkRemove');
    const memberSelectedCount = document.getElementById('memberSelectedCount');

    selectAllMembers?.addEventListener('change', () => {
        document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = selectAllMembers.checked);
        updateMemberUI();
    });

    function updateMemberUI() {
        const checked = document.querySelectorAll('.member-checkbox:checked');
        if (memberSelectedCount) memberSelectedCount.innerText = checked.length;
        if (btnBulkRemove) btnBulkRemove.classList.toggle('hidden', checked.length === 0);
    }

    function confirmBulkRemoveMembers() {
        const checked = document.querySelectorAll('.member-checkbox:checked');
        Swal.fire({
            title: 'Keluarkan Pegawai?',
            text: `${checked.length} pegawai terpilih akan dikeluarkan dari kategori ini.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Ya, Keluarkan!',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkRemoveMembersForm').submit();
            }
        });
    }
</script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-[32px]' } });
    });
</script>
@endif
@endsection
