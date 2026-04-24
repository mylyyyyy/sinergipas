@extends('layouts.app')

@section('title', 'Manajemen Regu')
@section('header-title', 'Kelola Regu & P2U')

@section('content')
<div class="space-y-8 page-fade">
    <!-- Header & Tools -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex items-center gap-6">
            <a href="{{ route('admin.schedules.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors font-bold text-[10px] uppercase tracking-widest">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm">
                <input type="checkbox" id="selectAllSquads" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-0 cursor-pointer">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pilih Semua</span>
            </div>
            <button type="button" onclick="confirmBulkDelete()" id="btnDeleteSquads" class="hidden px-6 py-2.5 bg-red-50 text-red-600 border border-red-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-all shadow-sm">
                Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
        </div>

        <button onclick="document.getElementById('squadModal').classList.remove('hidden')" class="px-8 py-4 rounded-2xl bg-slate-900 text-white font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d flex items-center gap-3">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Tambah Unit Baru
        </button>
    </div>

    <!-- Squad Grid (Card-based for better premium feel) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($squads as $squad)
        <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-8 card-3d group relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6">
                <input type="checkbox" name="squad_ids[]" value="{{ $squad->id }}" class="squad-checkbox w-5 h-5 rounded-lg border-slate-200 text-blue-600 focus:ring-0 cursor-pointer transition-all">
            </div>

            <div class="flex items-start gap-6">
                <div class="w-16 h-16 rounded-[24px] flex items-center justify-center text-2xl font-black italic shadow-lg
                    {{ $squad->type === 'p2u' ? 'bg-indigo-600 text-white shadow-indigo-200' : 'bg-slate-900 text-white shadow-slate-200' }}">
                    {{ substr($squad->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ $squad->name }}</h3>
                        <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest border
                            {{ $squad->type === 'p2u' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                            {{ strtoupper($squad->type) }}
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ $squad->description ?? 'Unit Operasional' }}</p>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-slate-50 flex items-center justify-between">
                <div class="flex -space-x-3">
                    @foreach($squad->employees->take(5) as $emp)
                        <div class="w-10 h-10 rounded-2xl border-4 border-white bg-slate-100 flex items-center justify-center text-[10px] font-black uppercase overflow-hidden shadow-sm ring-1 ring-slate-100" title="{{ $emp->full_name }}">
                            @if($emp->photo)
                                <img src="{{ $emp->photo }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($emp->full_name, 0, 1) }}
                            @endif
                        </div>
                    @endforeach
                    @if($squad->employees_count > 5)
                        <div class="w-10 h-10 rounded-2xl border-4 border-white bg-slate-50 text-slate-400 flex items-center justify-center text-[10px] font-black shadow-sm ring-1 ring-slate-100">
                            +{{ $squad->employees_count - 5 }}
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <span class="block text-lg font-black text-slate-900 leading-none">{{ $squad->employees_count }}</span>
                    <span class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Personel</span>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3">
                <button onclick='openManageMembersModal(@json($squad->load("employees")), @json($unassignedEmployees))' class="py-3.5 rounded-2xl bg-slate-50 text-slate-600 text-[9px] font-black uppercase tracking-widest hover:bg-slate-900 hover:text-white transition-all border border-slate-100 shadow-sm flex items-center justify-center gap-2">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i> Anggota
                </button>
                <div class="flex gap-2">
                    <button onclick="openEditModal({{ json_encode($squad) }})" class="flex-1 py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-amber-600 hover:border-amber-200 transition-all flex items-center justify-center">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <form action="{{ route('admin.squads.destroy', $squad->id) }}" method="POST" class="flex-1 no-loader">
                        @csrf @method('DELETE')
                        <button type="button" onclick="confirmDelete(this.form)" class="w-full h-full py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 transition-all flex items-center justify-center">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="md:col-span-2 xl:col-span-3 py-24 text-center bg-white rounded-[40px] border border-slate-200 shadow-sm border-dashed">
            <i data-lucide="shield-off" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
            <h4 class="text-sm font-black text-slate-900 uppercase italic">Database Unit Kosong</h4>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Silakan tambahkan Regu Jaga atau Unit P2U baru</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="squadModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-6 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-[40px] p-10 shadow-2xl animate-in zoom-in duration-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        
        <h3 id="squadModalTitle" class="text-2xl font-black text-slate-900 mb-8 italic tracking-tight relative z-10">Konfigurasi Unit</h3>
        
        <form id="squadForm" action="{{ route('admin.squads.store') }}" method="POST" class="space-y-6 relative z-10">
            @csrf
            <input type="hidden" name="_method" id="squadMethod" value="POST">
            <input type="hidden" name="schedule_type_id" value="{{ $scheduleTypes->first()?->id ?? 1 }}">
            
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Unit/Regu</label>
                <input type="text" name="name" id="squad_name" required placeholder="Contoh: Regu A atau P2U-1" class="w-full px-6 py-4 rounded-2xl border border-slate-200 text-sm font-black focus:border-blue-500 bg-slate-50 outline-none transition-all uppercase">
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Tipe Penugasan</label>
                <select name="type" id="squad_type" required class="w-full px-6 py-4 rounded-2xl border border-slate-200 text-sm font-black focus:border-blue-500 bg-slate-50 outline-none transition-all">
                    <option value="regu">Regu Jaga Umum (Blok/Lingkungan)</option>
                    <option value="p2u">P2U (Pengamanan Pintu Utama)</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Deskripsi Tugas</label>
                <textarea name="description" id="squad_description" rows="3" placeholder="Jelaskan cakupan pengamanan unit ini..." class="w-full px-6 py-4 rounded-2xl border border-slate-200 text-sm font-bold focus:border-blue-500 bg-slate-50 outline-none transition-all"></textarea>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeSquadModal()" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-[10px] uppercase tracking-widest">Batal</button>
                <button type="submit" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all shadow-xl btn-3d">Simpan Konfigurasi</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Members Modal -->
<div id="membersModal" class="fixed inset-0 bg-slate-900/60 hidden flex items-center justify-center z-50 p-4 sm:p-10 backdrop-blur-sm">
    <div class="bg-white w-full max-w-6xl rounded-[48px] shadow-2xl animate-in zoom-in duration-300 flex flex-col max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="px-10 py-8 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
            <div class="flex items-center gap-6">
                <div id="squad_icon_header" class="w-16 h-16 rounded-[24px] bg-slate-900 text-white flex items-center justify-center text-3xl font-black italic shadow-2xl">A</div>
                <div>
                    <h3 id="members_squad_name" class="text-3xl font-black text-slate-900 italic tracking-tighter">Kelola Personel</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Atur penugasan petugas ke dalam unit operasional</p>
                </div>
            </div>
            <button onclick="document.getElementById('membersModal').classList.add('hidden')" class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Left: Current Members List -->
            <div class="flex-1 p-10 border-r border-slate-100 overflow-y-auto custom-scrollbar bg-white flex flex-col">
                <div class="flex items-center justify-between mb-8">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-blue-600"></i> Anggota Aktif Unit
                    </h4>
                    <div id="bulk_remove_ui" class="hidden flex items-center gap-4 animate-in fade-in slide-in-from-right duration-200">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" id="selectAllMembers" class="w-4 h-4 rounded border-slate-300 text-red-500 focus:ring-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600">Pilih Semua</span>
                        </label>
                        <button type="button" onclick="bulkRemoveMembers()" class="px-4 py-2 bg-red-50 text-red-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all flex items-center gap-2 border border-red-100">
                            <i data-lucide="user-minus" class="w-3 h-3"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
                
                <form id="removeMemberForm" method="POST" class="no-loader">
                    @csrf
                    <div id="members_list" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                </form>
            </div>

            <!-- Right: Add New Members -->
            <div class="lg:w-[420px] bg-slate-50 flex flex-col overflow-hidden">
                <div class="p-10 pb-6 shrink-0">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">Tambah Personel</h4>
                        <label id="select_all_unassigned_ui" class="hidden flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" id="selectAllUnassigned" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-0">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Pilih Semua</span>
                        </label>
                    </div>
                    <div class="relative group">
                        <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        <input type="text" id="memberSearch" placeholder="Cari nama atau NIP..." class="w-full pl-12 pr-6 py-4 rounded-2xl border border-slate-200 bg-white text-sm font-bold focus:border-blue-500 outline-none transition-all shadow-sm">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar px-10">
                    <form id="addMemberForm" method="POST" class="no-loader">
                        @csrf
                        <div id="unassigned_list" class="space-y-3 mb-8"></div>
                    </form>
                </div>

                <div class="p-10 pt-6 border-t border-slate-200 bg-white shrink-0">
                    <button type="button" onclick="document.getElementById('addMemberForm').submit()" class="w-full py-5 bg-blue-600 text-white rounded-[24px] font-black text-xs uppercase tracking-widest hover:bg-slate-900 transition-all shadow-2xl shadow-blue-100 btn-3d flex items-center justify-center gap-3">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Simpan Penugasan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateSelection() {
        const checked = document.querySelectorAll('.squad-checkbox:checked').length;
        document.getElementById('selectedCount').innerText = checked;
        document.getElementById('btnDeleteSquads').classList.toggle('hidden', checked === 0);
    }

    document.getElementById('selectAllSquads')?.addEventListener('change', function() {
        document.querySelectorAll('.squad-checkbox').forEach(cb => cb.checked = this.checked);
        updateSelection();
    });

    document.querySelectorAll('.squad-checkbox').forEach(cb => cb.addEventListener('change', updateSelection));

    function confirmDelete(form) {
        Swal.fire({ title: 'Hapus Unit?', text: "Data jadwal operasional unit ini akan hilang.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus!', customClass: { popup: 'rounded-[32px]' } }).then((result) => { if (result.isConfirmed) form.submit(); });
    }

    function confirmRemoveMember(form, name) {
        Swal.fire({ 
            title: 'Keluarkan Personel?', 
            text: `Yakin ingin mengeluarkan ${name} dari unit ini?`, 
            icon: 'question', 
            showCancelButton: true, 
            confirmButtonColor: '#EF4444', 
            cancelButtonColor: '#94A3B8',
            confirmButtonText: 'Ya, Keluarkan!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[32px]' } 
        }).then((result) => { 
            if (result.isConfirmed) form.submit(); 
        });
    }

    function openEditModal(squad) {
        document.getElementById('squadModalTitle').innerText = 'Edit Informasi Unit';
        document.getElementById('squad_name').value = squad.name;
        document.getElementById('squad_type').value = squad.type;
        document.getElementById('squad_description').value = squad.description || '';
        document.getElementById('squadForm').action = '/admin/squads/' + squad.id;
        document.getElementById('squadMethod').value = 'PUT';
        document.getElementById('squadModal').classList.remove('hidden');
    }

    function closeSquadModal() {
        document.getElementById('squadModal').classList.add('hidden');
        document.getElementById('squadForm').action = "{{ route('admin.squads.store') }}";
        document.getElementById('squadMethod').value = 'POST';
        document.getElementById('squadForm').reset();
    }

    function openManageMembersModal(squad, unassigned) {
        document.getElementById('members_squad_name').innerText = squad.name;
        document.getElementById('squad_icon_header').innerText = squad.name.substring(0, 1).toUpperCase();
        document.getElementById('addMemberForm').action = `/admin/squads/${squad.id}/add-member`;
        document.getElementById('removeMemberForm').action = `/admin/squads/${squad.id}/remove-member`;
        
        // Reset UI
        document.getElementById('selectAllMembers').checked = false;
        document.getElementById('selectAllUnassigned').checked = false;
        document.getElementById('bulk_remove_ui').classList.toggle('hidden', squad.employees.length === 0);
        document.getElementById('select_all_unassigned_ui').classList.toggle('hidden', unassigned.length === 0);

        // Members List (Left)
        const list = document.getElementById('members_list');
        list.innerHTML = squad.employees.length ? '' : '<div class="col-span-2 py-20 text-center text-slate-300 font-bold uppercase text-[10px] tracking-widest italic">Belum ada personel</div>';
        squad.employees.forEach(emp => {
            const photo = emp.photo ? `<img src="${emp.photo}" class="w-full h-full object-cover">` : `<span class="text-xs font-black text-slate-400 uppercase">${emp.full_name.substring(0, 1)}</span>`;
            list.innerHTML += `
                <div class="flex items-center gap-4 p-5 bg-slate-50 rounded-3xl border border-slate-100 group transition-all hover:bg-white hover:shadow-md cursor-pointer" onclick="const ck = this.querySelector('.member-remove-checkbox'); ck.checked = !ck.checked">
                    <input type="checkbox" name="employee_ids[]" value="${emp.id}" class="member-remove-checkbox w-5 h-5 rounded-lg border-slate-300 text-red-500 focus:ring-0" onclick="event.stopPropagation()">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shadow-sm ring-2 ring-white">
                            ${photo}
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-900">${emp.full_name}</p>
                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5">NIP. ${emp.nip}</p>
                        </div>
                    </div>
                </div>
            `;
        });

        // Available List (Right)
        const uList = document.getElementById('unassigned_list');
        uList.innerHTML = unassigned.length ? '' : '<div class="py-20 text-center text-slate-400 text-[10px] font-bold uppercase italic">Semua petugas sudah memiliki regu</div>';
        unassigned.forEach(emp => {
            const photo = emp.photo ? `<img src="${emp.photo}" class="w-full h-full object-cover">` : `<span class="text-xs font-black text-slate-400 uppercase">${emp.full_name.substring(0, 1)}</span>`;
            uList.innerHTML += `
                <label class="available-item flex items-center gap-4 p-5 bg-white rounded-3xl border border-slate-200 hover:border-blue-400 transition-all cursor-pointer group shadow-sm">
                    <input type="checkbox" name="employee_ids[]" value="${emp.id}" class="unassigned-checkbox w-6 h-6 rounded-xl border-slate-300 text-blue-600 focus:ring-0">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shadow-inner">
                        ${photo}
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 group-hover:text-blue-600 transition-colors text-left">${emp.full_name}</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5 text-left">${emp.nip}</p>
                    </div>
                </label>
            `;
        });

        document.getElementById('membersModal').classList.remove('hidden');
        
        // Clear search input on open
        const searchInput = document.getElementById('memberSearch');
        if (searchInput) {
            searchInput.value = '';
            document.querySelectorAll('.available-item').forEach(item => item.style.display = '');
        }

        lucide.createIcons();
    }

    // Bulk action handlers
    document.addEventListener('change', function(e) {
        if (e.target.id === 'selectAllMembers') {
            document.querySelectorAll('.member-remove-checkbox').forEach(ck => ck.checked = e.target.checked);
        }
        if (e.target.id === 'selectAllUnassigned') {
            document.querySelectorAll('.unassigned-checkbox').forEach(ck => ck.checked = e.target.checked);
        }
    });

    function bulkRemoveMembers() {
        const checked = document.querySelectorAll('.member-remove-checkbox:checked');
        if (checked.length === 0) {
            Swal.fire({ title: 'Pilih Personel', text: 'Silakan pilih minimal satu personel untuk dikeluarkan.', icon: 'info', confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-[32px]' } });
            return;
        }

        Swal.fire({ 
            title: 'Keluarkan Personel?', 
            text: `Yakin ingin mengeluarkan ${checked.length} personel terpilih dari unit ini?`, 
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#EF4444', 
            confirmButtonText: 'Ya, Keluarkan!',
            customClass: { popup: 'rounded-[32px]' } 
        }).then((result) => { 
            if (result.isConfirmed) document.getElementById('removeMemberForm').submit(); 
        });
    }

    // Member search functionality
    document.getElementById('memberSearch')?.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.available-item').forEach(item => {
            const text = item.innerText.toLowerCase();
            item.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ icon: 'success', title: 'Berhasil', text: "{{ session('success') }}", confirmButtonColor: '#0F172A', customClass: { popup: 'rounded-[32px]' } });
    });
</script>
@endif
@endsection
