@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header-title', 'Konfigurasi Platform')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        <!-- Dashboard Announcement -->
        <div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm p-10 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-[#E85A4F]">
                    <i data-lucide="megaphone" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#1E2432]">Pengumuman Dashboard</h3>
                    <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">Muncul di halaman utama seluruh user</p>
                </div>
            </div>
            <textarea name="announcement" rows="3" class="w-full px-6 py-4 rounded-3xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">{{ $settings['announcement'] ?? '' }}</textarea>
        </div>

        <!-- Widget Settings -->
        <div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm p-10 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600">
                    <i data-lucide="layout" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#1E2432]">Kustomisasi Widget</h3>
                    <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">Atur elemen yang muncul di dashboard</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Kartu Statistik Ringkasan</span>
                    <select name="widget_stats" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['widget_stats'] ?? 'on') == 'on' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="off" {{ ($settings['widget_stats'] ?? 'on') == 'off' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Daftar Pegawai Terbaru</span>
                    <select name="widget_employees" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['widget_employees'] ?? 'on') == 'on' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="off" {{ ($settings['widget_employees'] ?? 'on') == 'off' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Grafik Sebaran Dokumen</span>
                    <select name="widget_chart" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['widget_chart'] ?? 'on') == 'on' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="off" {{ ($settings['widget_chart'] ?? 'on') == 'off' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Log Aktivitas & Pengumuman</span>
                    <select name="widget_activity" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['widget_activity'] ?? 'on') == 'on' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="off" {{ ($settings['widget_activity'] ?? 'on') == 'off' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Analitik Kepatuhan Pegawai</span>
                    <select name="widget_compliance" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['widget_compliance'] ?? 'on') == 'on' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="off" {{ ($settings['widget_compliance'] ?? 'on') == 'off' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Antrean Verifikasi Cepat</span>
                    <select name="widget_feed" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['widget_feed'] ?? 'on') == 'on' ? 'selected' : '' }}>Tampilkan</option>
                        <option value="off" {{ ($settings['widget_feed'] ?? 'on') == 'off' ? 'selected' : '' }}>Sembunyikan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Watermark Settings -->
        <div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm p-10 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-600">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#1E2432]">Pengaturan Watermark</h3>
                    <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">Keamanan visual pada pratinjau dokumen</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                    <span class="text-sm font-bold text-[#1E2432]">Status Watermark</span>
                    <select name="watermark_enabled" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-xs font-bold outline-none">
                        <option value="on" {{ ($settings['watermark_enabled'] ?? 'on') == 'on' ? 'selected' : '' }}>Aktif</option>
                        <option value="off" {{ ($settings['watermark_enabled'] ?? 'on') == 'off' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1">Teks Watermark</label>
                    <input type="text" name="watermark_text" value="{{ $settings['watermark_text'] ?? 'SINERGI PAS JOMBANG' }}" class="w-full px-6 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:ring-2 focus:ring-[#E85A4F]">
                </div>
            </div>
        </div>

        <!-- Broadcast Announcements -->
        <div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm p-10 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-600">
                    <i data-lucide="megaphone" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#1E2432]">Siaran Pengumuman</h3>
                    <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">Kirim pesan penting ke seluruh pegawai</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Form Create -->
                <div class="md:col-span-1 border-r border-[#EFEFEF] pr-10">
                    <form action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Isi Pesan</label>
                            <textarea name="message" rows="3" required class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none" placeholder="Tulis pengumuman..."></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Tipe Tampilan</label>
                            <select name="type" class="w-full px-5 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none">
                                <option value="banner">Running Text (Banner)</option>
                                <option value="popup">Pop-up Modal</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-[#1E2432] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-[#E85A4F] transition-all shadow-lg">
                            Siarkan Sekarang
                        </button>
                    </form>
                </div>

                <!-- List Announcements -->
                <div class="md:col-span-2 space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($announcements as $ann)
                    <div class="p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 {{ $ann->type == 'popup' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600' }} text-[8px] font-black uppercase rounded-md border border-opacity-20">{{ $ann->type }}</span>
                                @if($ann->is_active)
                                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[8px] font-black uppercase rounded-md">Active</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-400 text-[8px] font-black uppercase rounded-md">Inactive</span>
                                @endif
                            </div>
                            <p class="text-xs font-bold text-[#1E2432] leading-relaxed">{{ $ann->message }}</p>
                            <p class="text-[8px] text-[#ABABAB] font-bold mt-2">Dibuat oleh: {{ $ann->user->name }} • {{ $ann->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-col gap-2 ml-4">
                            <form action="{{ route('announcements.toggle', $ann->id) }}" method="POST" class="no-loader">
                                @csrf
                                <button type="submit" class="p-2 bg-white rounded-xl border border-[#EFEFEF] text-[#1E2432] hover:bg-[#1E2432] hover:text-white transition-all shadow-sm">
                                    <i data-lucide="{{ $ann->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                                </button>
                            </form>
                            <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman?')" class="no-loader">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 bg-white rounded-xl border border-[#EFEFEF] text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10 opacity-40">
                        <i data-lucide="megaphone" class="w-10 h-10 mx-auto mb-3"></i>
                        <p class="text-xs font-bold uppercase tracking-widest">Belum ada pengumuman</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Kop Surat Settings -->
        <div class="bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm p-10">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#1E2432]">Konfigurasi Kop Surat</h3>
                    <p class="text-xs text-[#8A8A8A] font-bold uppercase tracking-widest mt-1">Pengaturan teks untuk ekspor PDF/Excel</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Nama Instansi (Baris 1)</label>
                    <input type="text" name="kop_line_1" value="{{ $settings['kop_line_1'] ?? 'LEMBAGA PEMASYARAKATAN JOMBANG' }}" class="w-full px-6 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Sub Judul (Baris 2)</label>
                    <input type="text" name="kop_line_2" value="{{ $settings['kop_line_2'] ?? 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR' }}" class="w-full px-6 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Alamat & Kontak</label>
                    <input type="text" name="kop_address" value="{{ $settings['kop_address'] ?? 'Jl. KH. Wahid Hasyim No. 123, Jombang' }}" class="w-full px-6 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm outline-none focus:ring-2 focus:ring-[#E85A4F]">
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <button type="submit" class="bg-[#1E2432] text-white px-10 py-4 rounded-2xl font-black hover:bg-[#343b4d] transition-all shadow-xl active:scale-[0.98]">
                    Simpan Konfigurasi
                </button>
            </div>
        </div>
    </form>
</div>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#1E2432' });
</script>
@endif
@endsection
