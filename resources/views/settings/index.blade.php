@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header-title', 'Konfigurasi Platform')

@section('content')
<div class="max-w-5xl mx-auto pb-20">
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        
        <!-- Grid for Configuration Cards -->
        <div class="grid grid-cols-1 gap-10">
            
            <!-- Section 1: Dashboard Personalization -->
            <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm overflow-hidden transition-all hover:shadow-2xl hover:shadow-gray-100/50">
                <div class="bg-[#1E2432] p-10 text-white relative">
                    <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                    <div class="relative flex items-center gap-6">
                        <div class="w-16 h-16 bg-white/10 rounded-[24px] flex items-center justify-center border border-white/20">
                            <i data-lucide="layout" class="w-8 h-8 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black italic tracking-tight">Kustomisasi Dashboard</h3>
                            <p class="text-[10px] font-black opacity-60 uppercase tracking-[0.3em] mt-1">Personalisasi tampilan untuk seluruh pengguna</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @php
                            $widgetsList = [
                                ['key' => 'widget_stats', 'label' => 'Kartu Statistik', 'icon' => 'bar-chart-3'],
                                ['key' => 'widget_employees', 'label' => 'Pegawai Terbaru', 'icon' => 'users'],
                                ['key' => 'widget_chart' , 'label' => 'Grafik Sebaran', 'icon' => 'pie-chart'],
                                ['key' => 'widget_activity', 'label' => 'Log Aktivitas', 'icon' => 'activity'],
                                ['key' => 'widget_compliance', 'label' => 'Analitik Kepatuhan', 'icon' => 'shield-check'],
                                ['key' => 'widget_feed', 'label' => 'Antrean Verifikasi', 'icon' => 'zap'],
                            ];
                        @endphp

                        @foreach($widgetsList as $w)
                        <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] group hover:border-[#E85A4F] transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#8A8A8A] group-hover:text-[#E85A4F] transition-all shadow-sm">
                                    <i data-lucide="{{ $w['icon'] }}" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-bold text-[#1E2432]">{{ $w['label'] }}</span>
                            </div>
                            <select name="{{ $w['key'] }}" class="bg-white border border-[#EFEFEF] rounded-xl px-3 py-2 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-[#E85A4F] cursor-pointer">
                                <option value="on" {{ ($settings[$w['key']] ?? 'on') == 'on' ? 'selected' : '' }}>On</option>
                                <option value="off" {{ ($settings[$w['key']] ?? 'on') == 'off' ? 'selected' : '' }}>Off</option>
                            </select>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 2: Security & Visuals -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Watermark -->
                <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm p-10 flex flex-col transition-all hover:shadow-2xl hover:shadow-gray-100/50">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 bg-yellow-50 rounded-[24px] flex items-center justify-center text-yellow-600 shadow-sm">
                            <i data-lucide="shield" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-[#1E2432]">Sistem Watermark</h3>
                            <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Keamanan Pratinjau Dokumen</p>
                        </div>
                    </div>
                    
                    <div class="space-y-8 flex-1">
                        <div class="flex items-center justify-between p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF]">
                            <span class="text-sm font-bold text-[#1E2432]">Status Proteksi</span>
                            <select name="watermark_enabled" class="bg-white border border-[#EFEFEF] rounded-xl px-4 py-2 text-[10px] font-black uppercase outline-none focus:ring-2 focus:ring-yellow-500 cursor-pointer">
                                <option value="on" {{ ($settings['watermark_enabled'] ?? 'on') == 'on' ? 'selected' : '' }}>Aktif</option>
                                <option value="off" {{ ($settings['watermark_enabled'] ?? 'on') == 'off' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="space-y-3 px-2">
                            <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Teks Watermark</label>
                            <input type="text" name="watermark_text" value="{{ $settings['watermark_text'] ?? 'SINERGI PAS JOMBANG' }}" class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold text-[#1E2432] focus:ring-4 focus:ring-yellow-500/5 focus:border-yellow-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- Running Text -->
                <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm p-10 flex flex-col transition-all hover:shadow-2xl hover:shadow-gray-100/50">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 bg-blue-50 rounded-[24px] flex items-center justify-center text-blue-600 shadow-sm">
                            <i data-lucide="move-horizontal" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-[#1E2432]">Banner Berjalan</h3>
                            <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Konfigurasi Running Text</p>
                        </div>
                    </div>
                    
                    <div class="space-y-6 flex-1">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Kecepatan (Detik)</label>
                                <input type="number" name="running_text_speed" value="{{ $settings['running_text_speed'] ?? '20' }}" class="w-full px-5 py-3 rounded-xl border border-[#EFEFEF] bg-[#FCFBF9] text-xs font-bold outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex flex-col justify-end">
                                <p class="text-[8px] text-[#ABABAB] font-bold uppercase italic leading-tight">*Kecil = Cepat</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="flex-1 space-y-2">
                                    <label class="text-[9px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Latar</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="running_text_bg" id="color_bg" value="{{ $settings['running_text_bg'] ?? '#1E2432' }}" class="w-10 h-10 rounded-lg border border-[#EFEFEF] bg-white p-1 cursor-pointer">
                                        <input type="text" id="color_bg_text" value="{{ $settings['running_text_bg'] ?? '#1E2432' }}" readonly class="flex-1 px-4 py-2 rounded-lg border border-[#EFEFEF] bg-gray-50 text-[10px] font-mono font-bold">
                                    </div>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <label class="text-[9px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Teks</label>
                                    <div class="flex gap-2">
                                        <input type="color" name="running_text_color" id="color_text" value="{{ $settings['running_text_color'] ?? '#FFFFFF' }}" class="w-10 h-10 rounded-lg border border-[#EFEFEF] bg-white p-1 cursor-pointer">
                                        <input type="text" id="color_text_text" value="{{ $settings['running_text_color'] ?? '#FFFFFF' }}" readonly class="flex-1 px-4 py-2 rounded-lg border border-[#EFEFEF] bg-gray-50 text-[10px] font-mono font-bold">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Document Header (Kop) -->
            <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm p-10 transition-all hover:shadow-2xl hover:shadow-gray-100/50">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-14 h-14 bg-purple-50 rounded-[24px] flex items-center justify-center text-purple-600 shadow-sm">
                        <i data-lucide="file-text" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-[#1E2432]">Identitas Instansi</h3>
                        <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Konfigurasi Kop Surat Ekspor PDF/Excel</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Nama Instansi (Baris 1)</label>
                            <input type="text" name="kop_line_1" value="{{ $settings['kop_line_1'] ?? 'LEMBAGA PEMASYARAKATAN JOMBANG' }}" class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold focus:ring-4 focus:ring-purple-500/5 focus:border-purple-500 outline-none transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Sub Judul (Baris 2)</label>
                            <input type="text" name="kop_line_2" value="{{ $settings['kop_line_2'] ?? 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR' }}" class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold focus:ring-4 focus:ring-purple-500/5 focus:border-purple-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#1E2432] uppercase tracking-[0.2em] ml-1">Alamat & Kontak Resmi</label>
                        <textarea name="kop_address" rows="5" class="w-full px-6 py-4 rounded-[24px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold focus:ring-4 focus:ring-purple-500/5 focus:border-purple-500 outline-none transition-all">{{ $settings['kop_address'] ?? 'Jl. KH. Wahid Hasyim No. 123, Jombang' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Final Save Button for General Settings -->
            <div class="flex justify-center -mt-4 mb-10">
                <button type="submit" class="bg-[#1E2432] text-white px-16 py-6 rounded-[32px] font-black text-lg hover:bg-[#E85A4F] transition-all shadow-2xl active:scale-95 flex items-center gap-4">
                    Simpan Seluruh Perubahan
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="h-px bg-[#EFEFEF] my-10"></div>

    <!-- Section 4: Broadcast Announcements (Separate Form Handling) -->
    <div class="bg-white rounded-[56px] border border-[#EFEFEF] shadow-sm p-10 transition-all hover:shadow-2xl hover:shadow-gray-100/50">
        <div class="flex items-center gap-4 mb-10">
            <div class="w-14 h-14 bg-red-50 rounded-[24px] flex items-center justify-center text-red-600 shadow-sm">
                <i data-lucide="megaphone" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-[#1E2432]">Pusat Siaran</h3>
                <p class="text-[10px] font-bold text-[#8A8A8A] uppercase tracking-widest mt-1">Kelola Pengumuman Banner & Pop-up</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Create Form -->
            <div class="lg:col-span-1 border-r border-[#EFEFEF] lg:pr-12">
                <form action="{{ route('announcements.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Pesan Pengumuman</label>
                        <textarea name="message" rows="4" required class="w-full px-6 py-5 rounded-[24px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-bold outline-none focus:border-[#E85A4F] transition-all" placeholder="Tulis pengumuman resmi..."></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-[#8A8A8A] uppercase tracking-widest ml-1">Gaya Tampilan</label>
                        <select name="type" class="w-full px-6 py-4 rounded-[20px] border border-[#EFEFEF] bg-[#FCFBF9] text-sm font-black uppercase tracking-widest outline-none appearance-none cursor-pointer">
                            <option value="banner">Running Banner</option>
                            <option value="popup">Important Pop-up</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-[#E85A4F] text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-[0.2em] hover:bg-[#d44d42] transition-all shadow-xl shadow-red-100">
                        Siarkan Sekarang
                    </button>
                </form>
            </div>

            <!-- List History -->
            <div class="lg:col-span-2 flex flex-col">
                <h4 class="text-[10px] font-black text-[#ABABAB] uppercase tracking-[0.3em] mb-6 px-2">Riwayat Siaran Terakhir</h4>
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-4 custom-scrollbar flex-1">
                    @forelse($announcements as $ann)
                    <div class="p-6 bg-[#FCFBF9] rounded-[32px] border border-[#EFEFEF] group transition-all hover:bg-white hover:border-[#E85A4F] flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-3 py-1 {{ $ann->type == 'popup' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }} text-[8px] font-black uppercase rounded-lg border border-opacity-10">{{ $ann->type }}</span>
                                @if($ann->is_active)
                                    <span class="flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-600 text-[8px] font-black uppercase rounded-lg">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Aktif
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-gray-200 text-gray-500 text-[8px] font-black uppercase rounded-lg">Nonaktif</span>
                                @endif
                            </div>
                            <p class="text-sm font-bold text-[#1E2432] leading-relaxed italic">"{{ $ann->message }}"</p>
                            <p class="text-[9px] text-[#ABABAB] font-bold mt-4 uppercase tracking-tighter">Disiarkan oleh {{ $ann->user->name }} • {{ $ann->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex gap-2 ml-6 opacity-0 group-hover:opacity-100 transition-all">
                            <form action="{{ route('announcements.toggle', $ann->id) }}" method="POST" class="no-loader">
                                @csrf
                                <button type="submit" class="w-10 h-10 bg-white rounded-xl border border-[#EFEFEF] text-[#1E2432] hover:bg-[#1E2432] hover:text-white transition-all shadow-sm flex items-center justify-center">
                                    <i data-lucide="{{ $ann->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                                </button>
                            </form>
                            <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?')" class="no-loader">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-10 h-10 bg-white rounded-xl border border-[#EFEFEF] text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm flex items-center justify-center">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-20 opacity-30">
                        <i data-lucide="megaphone-off" class="w-16 h-16 mb-4"></i>
                        <p class="text-xs font-black uppercase tracking-widest">Belum ada riwayat siaran</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Realtime Color Text Preview
    const bgInp = document.getElementById('color_bg');
    const bgTxt = document.getElementById('color_bg_text');
    const txInp = document.getElementById('color_text');
    const txTxt = document.getElementById('color_text_text');

    if(bgInp) {
        bgInp.addEventListener('input', (e) => bgTxt.value = e.target.value.toUpperCase());
        txInp.addEventListener('input', (e) => txTxt.value = e.target.value.toUpperCase());
    }
</script>

@if(session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Konfigurasi Tersimpan!', 
        text: "{{ session('success') }}", 
        confirmButtonColor: '#1E2432',
        customClass: { popup: 'rounded-[40px]' }
    });
</script>
@endif
@endsection
