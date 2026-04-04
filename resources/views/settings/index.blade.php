@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header-title', 'Konfigurasi Platform')

@section('content')
<style>
    .settings-nav-link { 
        @apply flex items-center gap-3 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-transparent;
    }
    .settings-nav-link.active {
        @apply bg-[#1E2432] text-white shadow-xl border-[#1E2432];
    }
    .settings-nav-link:not(.active) {
        @apply text-[#8A8A8A] hover:bg-white hover:border-[#EFEFEF] hover:text-[#1E2432];
    }
    .config-card {
        @apply bg-white rounded-[40px] border border-[#EFEFEF] shadow-sm overflow-hidden transition-all duration-500;
    }
    .input-field {
        @apply w-full px-6 py-4 rounded-2xl border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] outline-none focus:border-[#E85A4F] focus:ring-4 focus:ring-red-500/5 transition-all;
    }
    .label-caps {
        @apply text-[9px] font-black text-[#8A8A8A] uppercase tracking-[0.2em] ml-1 mb-2 block;
    }
</style>

<div class="max-w-6xl mx-auto pb-24">
    <!-- Secondary Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-6 mb-12">
        <div class="flex bg-[#F5F4F2] p-1.5 rounded-[24px] border border-[#EFEFEF] shadow-inner">
            <a href="#general" class="settings-nav-link active">Konfigurasi Umum</a>
            <a href="#broadcast" class="settings-nav-link">Pusat Siaran</a>
            <a href="#master" class="settings-nav-link">Master Data</a>
        </div>
        
        <a href="{{ route('settings.health') }}" class="bg-white border border-[#EFEFEF] px-8 py-4 rounded-[24px] text-[10px] font-black uppercase tracking-widest text-[#1E2432] hover:bg-[#1E2432] hover:text-white transition-all shadow-sm flex items-center gap-3 group">
            <i data-lucide="heart-pulse" class="w-4 h-4 text-[#E85A4F]"></i> 
            Kesehatan Sistem 
            <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-10" id="general">
        @csrf
        
        <!-- Grid: Interface & Protection -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Widgets Toggle -->
            <div class="lg:col-span-2 config-card">
                <div class="p-10 border-b border-[#F5F4F2] bg-[#FCFBF9]/50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-[#EFEFEF] shadow-sm text-[#1E2432]">
                        <i data-lucide="layout-grid" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1E2432] tracking-tight">Modul Dashboard</h3>
                        <p class="text-[9px] font-bold text-[#8A8A8A] uppercase tracking-widest">Aktifkan atau nonaktifkan widget informasi</p>
                    </div>
                </div>
                <div class="p-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            $widgetsList = [
                                ['key' => 'widget_stats', 'label' => 'Statistik Utama', 'icon' => 'bar-chart-3'],
                                ['key' => 'widget_employees', 'label' => 'Status Unit Kerja', 'icon' => 'users'],
                                ['key' => 'widget_chart' , 'label' => 'Grafik Distribusi', 'icon' => 'pie-chart'],
                                ['key' => 'widget_activity', 'label' => 'Aktivitas Terkini', 'icon' => 'activity'],
                                ['key' => 'widget_compliance', 'label' => 'Status Kepatuhan', 'icon' => 'shield-check'],
                                ['key' => 'widget_feed', 'label' => 'Antrean Berkas', 'icon' => 'zap'],
                            ];
                        @endphp
                        @foreach($widgetsList as $w)
                        <label class="flex items-center justify-between p-6 rounded-2xl border border-[#EFEFEF] hover:border-[#E85A4F]/30 hover:bg-[#FCFBF9] transition-all cursor-pointer group">
                            <div class="flex items-center gap-4">
                                <i data-lucide="{{ $w['icon'] }}" class="w-4 h-4 text-[#ABABAB] group-hover:text-[#E85A4F]"></i>
                                <span class="text-[10px] font-black text-[#1E2432] uppercase tracking-tighter">{{ $w['label'] }}</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="{{ $w['key'] }}" value="off">
                                <input type="checkbox" name="{{ $w['key'] }}" value="on" class="sr-only peer" {{ ($settings[$w['key']] ?? 'on') == 'on' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E85A4F]"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Watermark Settings -->
            <div class="config-card">
                <div class="p-10 border-b border-[#F5F4F2] bg-[#FCFBF9]/50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-[#EFEFEF] shadow-sm text-yellow-600">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1E2432] tracking-tight">Proteksi</h3>
                        <p class="text-[9px] font-bold text-[#8A8A8A] uppercase tracking-widest">Keamanan Dokumen</p>
                    </div>
                </div>
                <div class="p-10 space-y-8">
                    <div>
                        <span class="label-caps">Status Watermark</span>
                        <select name="watermark_enabled" class="input-field appearance-none cursor-pointer">
                            <option value="on" {{ ($settings['watermark_enabled'] ?? 'on') == 'on' ? 'selected' : '' }}>AKTIF</option>
                            <option value="off" {{ ($settings['watermark_enabled'] ?? 'on') == 'off' ? 'selected' : '' }}>NONAKTIF</option>
                        </select>
                    </div>
                    <div>
                        <span class="label-caps">Teks Pengaman</span>
                        <input type="text" name="watermark_text" value="{{ $settings['watermark_text'] ?? 'SINERGI PAS JOMBANG' }}" class="input-field">
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner & Institutional Identity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Running Text Config -->
            <div class="config-card">
                <div class="p-10 border-b border-[#F5F4F2] bg-[#FCFBF9]/50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-[#EFEFEF] shadow-sm text-blue-600">
                        <i data-lucide="monitor" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1E2432] tracking-tight">Running Banner</h3>
                        <p class="text-[9px] font-bold text-[#8A8A8A] uppercase tracking-widest">Gaya Visual Pengumuman</p>
                    </div>
                </div>
                <div class="p-10 space-y-8">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <span class="label-caps">Warna Latar</span>
                            <div class="flex gap-3">
                                <input type="color" name="running_text_bg" value="{{ $settings['running_text_bg'] ?? '#1E2432' }}" class="w-12 h-12 rounded-xl border border-[#EFEFEF] bg-white p-1 cursor-pointer">
                                <input type="text" value="{{ $settings['running_text_bg'] ?? '#1E2432' }}" readonly class="flex-1 px-4 py-3 rounded-xl border border-[#EFEFEF] bg-gray-50 text-[10px] font-mono font-bold uppercase flex items-center">
                            </div>
                        </div>
                        <div>
                            <span class="label-caps">Warna Teks</span>
                            <div class="flex gap-3">
                                <input type="color" name="running_text_color" value="{{ $settings['running_text_color'] ?? '#FFFFFF' }}" class="w-12 h-12 rounded-xl border border-[#EFEFEF] bg-white p-1 cursor-pointer">
                                <input type="text" value="{{ $settings['running_text_color'] ?? '#FFFFFF' }}" readonly class="flex-1 px-4 py-3 rounded-xl border border-[#EFEFEF] bg-gray-50 text-[10px] font-mono font-bold uppercase flex items-center">
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="label-caps">Kecepatan Gerak (Detik)</span>
                        <input type="number" name="running_text_speed" value="{{ $settings['running_text_speed'] ?? '20' }}" class="input-field">
                    </div>
                </div>
            </div>

            <!-- Kop Surat / Identity -->
            <div class="config-card">
                <div class="p-10 border-b border-[#F5F4F2] bg-[#FCFBF9]/50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center border border-[#EFEFEF] shadow-sm text-purple-600">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#1E2432] tracking-tight">Identitas Instansi</h3>
                        <p class="text-[9px] font-bold text-[#8A8A8A] uppercase tracking-widest">Detail Kop Dokumen Resmi</p>
                    </div>
                </div>
                <div class="p-10 space-y-6">
                    <div>
                        <span class="label-caps">Nama Instansi</span>
                        <input type="text" name="kop_line_1" value="{{ $settings['kop_line_1'] ?? 'LEMBAGA PEMASYARAKATAN JOMBANG' }}" class="input-field">
                    </div>
                    <div>
                        <span class="label-caps">Sub-Instansi / Wilayah</span>
                        <input type="text" name="kop_line_2" value="{{ $settings['kop_line_2'] ?? 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR' }}" class="input-field">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center pt-6">
            <button type="submit" class="bg-[#1E2432] text-white px-12 py-5 rounded-[24px] font-black text-xs uppercase tracking-[0.2em] hover:bg-[#E85A4F] transition-all shadow-xl shadow-gray-200 active:scale-95 flex items-center gap-4 group">
                Simpan Perubahan
                <i data-lucide="check-circle" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            </button>
        </div>
    </form>

    <!-- Broadcast Section -->
    <div class="mt-20 pt-20 border-t border-[#EFEFEF]" id="broadcast">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8 mb-12">
            <div>
                <h3 class="text-2xl font-black text-[#1E2432] tracking-tight italic">Siaran Pengumuman</h3>
                <p class="text-[9px] font-black text-[#8A8A8A] uppercase tracking-[0.4em] mt-2">Komunikasi Langsung ke Seluruh Pegawai</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Create Form -->
            <div class="config-card h-fit">
                <div class="p-10">
                    <form action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <span class="label-caps">Pesan Informasi</span>
                            <textarea name="message" rows="4" required class="input-field py-5 leading-relaxed" placeholder="Tulis pengumuman..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <span class="label-caps">Tipe Tampilan</span>
                                <select name="type" class="input-field appearance-none cursor-pointer">
                                    <option value="banner">Running Text</option>
                                    <option value="popup">Popup Modal</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="label-caps">Mulai</span>
                                <input type="datetime-local" name="starts_at" class="input-field !px-4 !text-[10px]">
                            </div>
                            <div>
                                <span class="label-caps">Berakhir</span>
                                <input type="datetime-local" name="expires_at" class="input-field !px-4 !text-[10px]">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-widest hover:bg-[#1E2432] transition-all shadow-lg active:scale-95 flex items-center justify-center gap-3">
                            Publikasikan <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="lg:col-span-2 space-y-6 max-h-[700px] overflow-y-auto pr-4 custom-scrollbar">
                @php $announcements = \App\Models\Announcement::latest()->get(); @endphp
                @forelse($announcements as $ann)
                <div class="bg-white p-8 rounded-[32px] border border-[#EFEFEF] group transition-all hover:border-[#1E2432] flex justify-between items-start gap-6">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-4">
                            <span class="px-3 py-1 bg-[#F5F4F2] text-[#1E2432] text-[8px] font-black uppercase rounded-lg border border-[#EFEFEF]">{{ $ann->type }}</span>
                            @if($ann->starts_at && $ann->starts_at > now())
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[8px] font-black uppercase rounded-lg border border-blue-100">Scheduled</span>
                            @elseif($ann->is_active && (!$ann->expires_at || $ann->expires_at > now()))
                                <span class="px-3 py-1 bg-green-50 text-green-600 text-[8px] font-black uppercase rounded-lg border border-green-100">Live</span>
                            @else
                                <span class="px-3 py-1 bg-gray-50 text-gray-400 text-[8px] font-black uppercase rounded-lg border border-gray-100">Inactive</span>
                            @endif
                        </div>
                        <p class="text-sm font-bold text-[#1E2432] leading-relaxed italic">"{{ $ann->message }}"</p>
                        <div class="mt-6 flex items-center gap-2 text-[8px] font-black text-[#ABABAB] uppercase tracking-widest">
                            <i data-lucide="clock" class="w-3 h-3"></i>
                            {{ $ann->starts_at ? $ann->starts_at->format('d/m/y H:i') : 'Start Now' }} 
                            → 
                            {{ $ann->expires_at ? $ann->expires_at->format('d/m/y H:i') : 'Always' }}
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all">
                        <form action="{{ route('announcements.toggle', $ann->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-10 h-10 bg-white border border-[#EFEFEF] rounded-xl flex items-center justify-center text-[#1E2432] hover:bg-[#1E2432] hover:text-white transition-all shadow-sm">
                                <i data-lucide="{{ $ann->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                            </button>
                        </form>
                        <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-10 h-10 bg-white border border-[#EFEFEF] rounded-xl flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center py-20 opacity-30 italic font-black text-[10px] uppercase tracking-widest">Belum ada riwayat siaran</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Master Data Section -->
    <div class="mt-20 pt-20 border-t border-[#EFEFEF]" id="master">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Position -->
            <div class="config-card p-10">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-base font-black text-[#1E2432] uppercase tracking-tight">Master Jabatan</h4>
                </div>
                <form action="{{ route('settings.positions.store') }}" method="POST" class="flex gap-3 mb-8">
                    @csrf
                    <input type="text" name="name" required placeholder="Tambah Jabatan..." class="input-field !py-3">
                    <button type="submit" class="bg-indigo-600 text-white px-6 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg active:scale-90 flex items-center justify-center">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </button>
                </form>
                <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($positions as $pos)
                    <div class="flex justify-between items-center p-4 bg-[#FCFBF9] rounded-xl border border-[#EFEFEF] group">
                        <span class="text-[10px] font-black text-[#1E2432] uppercase tracking-tighter">{{ $pos->name }}</span>
                        <form action="{{ route('settings.positions.destroy', $pos->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[#ABABAB] hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Work Units -->
            <div class="config-card p-10">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <i data-lucide="grid" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-base font-black text-[#1E2432] uppercase tracking-tight">Master Unit Kerja</h4>
                </div>
                <form action="{{ route('settings.work-units.store') }}" method="POST" class="flex gap-3 mb-8">
                    @csrf
                    <input type="text" name="name" required placeholder="Tambah Unit Kerja..." class="input-field !py-3">
                    <button type="submit" class="bg-emerald-600 text-white px-6 rounded-2xl hover:bg-emerald-700 transition-all shadow-lg active:scale-90 flex items-center justify-center">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </button>
                </form>
                <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($workUnits as $unit)
                    <div class="flex justify-between items-center p-4 bg-[#FCFBF9] rounded-xl border border-[#EFEFEF] group">
                        <span class="text-[10px] font-black text-[#1E2432] uppercase tracking-tighter">{{ $unit->name }}</span>
                        <form action="{{ route('settings.work-units.destroy', $unit->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[#ABABAB] hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonColor: '#1E2432', customClass: { popup: 'rounded-[32px]' } });
</script>
@endif
@endsection
