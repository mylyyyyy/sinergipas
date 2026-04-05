@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Pengaturan Akun')

@section('content')
<div class="max-w-5xl mx-auto page-fade">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden mb-10 card-3d">
            <!-- Decorative Header -->
            <div class="h-40 bg-slate-900 relative">
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                <div class="absolute bottom-0 left-0 w-full h-20 bg-gradient-to-t from-white/10 to-transparent"></div>
            </div>
            
            <div class="px-8 pb-10">
                <div class="relative -mt-16 mb-10 flex flex-col md:flex-row items-end gap-6">
                    <!-- Photo Upload with Preview -->
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-3xl border-4 border-white bg-slate-100 overflow-hidden shadow-xl flex items-center justify-center text-slate-400">
                            @if($employee && $employee->photo)
                                <img id="avatar-preview" src="{{ $employee->photo }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div id="avatar-placeholder" class="text-center">
                                    <i data-lucide="user" class="w-12 h-12 mx-auto opacity-20"></i>
                                </div>
                                <img id="avatar-preview" class="hidden w-full h-full object-cover">
                            @endif
                        </div>

                        <label for="photoInput" class="absolute -bottom-2 -right-2 bg-amber-600 p-2 rounded-xl shadow-lg cursor-pointer hover:bg-amber-700 transition-all hover:scale-110 border-2 border-white">
                            <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                            <input type="file" id="photoInput" name="photo" class="hidden" onchange="previewImage(this)">
                        </label>

                        @if($employee && $employee->photo)
                        <button type="button" onclick="confirmDeletePhoto()" class="absolute -top-2 -right-2 bg-white p-1.5 rounded-lg shadow-lg text-red-500 hover:bg-red-50 transition-all border border-red-100" title="Hapus Foto">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                        @endif
                    </div>

                    <div class="flex-1 pb-2">
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $user->name }}</h2>
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[9px] font-bold uppercase tracking-widest rounded-lg border border-slate-200">
                                {{ $user->role }}
                            </span>
                            <span class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5"></i> {{ $user->email }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Section: Personal Info -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center border border-blue-100">
                                <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Informasi Dasar</h4>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ $user->name }}" required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:border-blue-500 outline-none transition-all">
                        </div>

                        @if($employee)
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">NIP</label>
                                <input type="text" value="{{ $employee->nip }}" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 text-sm font-semibold outline-none cursor-not-allowed italic">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Jabatan</label>
                                <input type="text" value="{{ $employee->position }}" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 text-sm font-semibold outline-none cursor-not-allowed">
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Section: Security -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center border border-amber-100">
                                <i data-lucide="lock" class="w-4 h-4 text-amber-600"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Keamanan Akun</h4>
                        </div>

                        <div class="space-y-2 relative">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Password Baru</label>
                            <input type="password" name="password" id="password" placeholder="••••••••••••"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:border-blue-500 outline-none transition-all pr-12">
                            <button type="button" onclick="togglePassword('password', 'eye-1')" class="absolute right-4 top-[34px] text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" id="eye-1" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="space-y-2 relative">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••••••"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:border-blue-500 outline-none transition-all pr-12">
                            <button type="button" onclick="togglePassword('password_confirmation', 'eye-2')" class="absolute right-4 top-[34px] text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" id="eye-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        
                        <p class="text-[10px] text-slate-400 font-medium leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 italic">
                            <i data-lucide="shield-info" class="w-3 h-3 inline mr-1"></i>
                            Kosongkan jika tidak ingin mengubah password.
                        </p>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Update Terakhir: {{ $user->updated_at->format('d M Y, H:i') }}</p>
                    <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-10 py-3.5 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg active:scale-[0.98] btn-3d flex items-center justify-center gap-2">
                        Simpan Perubahan
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Report Issue Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 hover-lift">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-600 border border-red-100">
                    <i data-lucide="message-square" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Lapor Koreksi Data</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Hubungi Admin Sistem</p>
                </div>
            </div>

            <form action="{{ route('profile.report') }}" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Subjek</label>
                    <input type="text" name="subject" required placeholder="Contoh: Salah Penulisan NIP" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:border-red-500">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Pesan</label>
                    <textarea name="message" rows="3" required placeholder="Detail kesalahan data..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:border-red-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-red-600 transition-all btn-3d">
                    Kirim Laporan
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 flex flex-col h-full hover-lift">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 border border-blue-100">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">Riwayat Laporan</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Status Tanggapan</p>
                </div>
            </div>

            <div class="space-y-4 flex-1 overflow-y-auto custom-scrollbar pr-2 max-h-[320px]">
                @forelse($myIssues as $issue)
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 transition-all hover:bg-white group">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-slate-900 truncate pr-4">{{ $issue->subject }}</span>
                        @if($issue->status === 'open')
                            <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[8px] font-bold uppercase rounded-md border border-red-200">Open</span>
                        @elseif($issue->status === 'resolved')
                            <span class="px-2 py-0.5 bg-green-100 text-green-600 text-[8px] font-bold uppercase rounded-md border border-green-200">Resolved</span>
                        @else
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[8px] font-bold uppercase rounded-md border border-slate-300">Closed</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">{{ $issue->message }}</p>
                    
                    @if($issue->admin_note)
                    <div class="mt-3 p-3 bg-white rounded-xl border border-blue-50 border-l-4 border-l-blue-500">
                        <p class="text-[9px] font-bold text-blue-600 uppercase tracking-widest mb-1 italic">Tanggapan Admin:</p>
                        <p class="text-[10px] font-semibold text-slate-700 leading-relaxed">{{ $issue->admin_note }}</p>
                    </div>
                    @endif
                    <p class="text-[8px] text-slate-400 font-bold uppercase mt-3">{{ $issue->created_at->diffForHumans() }}</p>
                </div>
                @empty
                <div class="text-center py-10">
                    <p class="text-xs font-bold text-slate-300 italic uppercase tracking-widest">Belum ada laporan</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                const placeholder = document.getElementById('avatar-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if(placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }

    function confirmDeletePhoto() {
        Swal.fire({
            title: 'Hapus Foto Profil?',
            text: "Media profil akan dimusnahkan secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B45309',
            cancelButtonColor: '#0F172A',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('profile.photo.destroy') }}";
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil', 
            text: "{{ session('success') }}", 
            confirmButtonColor: '#0F172A',
            customClass: { popup: 'rounded-2xl' }
        });
    });
</script>
@endif
@endsection
