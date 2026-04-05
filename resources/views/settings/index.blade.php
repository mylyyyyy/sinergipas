@extends('layouts.app')

@section('title', 'Pengaturan')
@section('header-title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-6xl mx-auto page-fade">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="md:w-64 shrink-0">
            <div class="sticky top-24 space-y-1">
                <a href="#umum" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-sm hover:border-blue-300 transition-all active-nav" data-nav="umum">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Umum & Kop</span>
                </a>
                <a href="#siaran" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 font-semibold hover:bg-white hover:border-slate-200 border border-transparent transition-all" data-nav="siaran">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>Siaran & Style</span>
                </a>
                <a href="#master" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 font-semibold hover:bg-white hover:border-slate-200 border border-transparent transition-all" data-nav="master">
                    <i data-lucide="database" class="w-4 h-4"></i>
                    <span>Master Data</span>
                </a>
                
                <div class="pt-6">
                    <a href="{{ route('settings.health') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs uppercase tracking-wider hover:bg-blue-100 transition-all">
                        <i data-lucide="heart-pulse" class="w-4 h-4"></i>
                        <span>System Health</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Settings Content -->
        <div class="flex-1 space-y-10 pb-20">
            <!-- Umum & Kop -->
            <section id="umum" class="space-y-6">
                <div class="flex items-center gap-3 pb-2 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Konfigurasi Umum & Identitas</h3>
                </div>

                <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <!-- Dashboard Widgets -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden card-3d">
                        <div class="p-6 border-b border-slate-50 bg-slate-50/50">
                            <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="layout" class="w-4 h-4 text-slate-400"></i>
                                Widget Dashboard
                            </h4>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @php
                                $widgets = [
                                    ['key' => 'widget_stats', 'label' => 'Statistik Utama', 'icon' => 'bar-chart-3'],
                                    ['key' => 'widget_employees', 'label' => 'Unit Kerja', 'icon' => 'users'],
                                    ['key' => 'widget_chart', 'label' => 'Grafik', 'icon' => 'pie-chart'],
                                    ['key' => 'widget_activity', 'label' => 'Aktivitas', 'icon' => 'activity'],
                                ];
                            @endphp
                            @foreach($widgets as $w)
                            <label class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-200 transition-all cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-blue-600 transition-colors">
                                        <i data-lucide="{{ $w['icon'] }}" class="w-5 h-5"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">{{ $w['label'] }}</span>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="hidden" name="{{ $w['key'] }}" value="off">
                                    <input type="checkbox" name="{{ $w['key'] }}" value="on" class="peer sr-only" {{ ($settings[$w['key']] ?? 'on') === 'on' ? 'checked' : '' }}>
                                    <div class="h-6 w-11 rounded-full bg-slate-200 transition-all peer-checked:bg-blue-600 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-5"></div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Kop Surat with Preview -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 card-3d">
                        <h4 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <i data-lucide="building" class="w-4 h-4 text-slate-400"></i>
                            Identitas Kop Surat & Export
                        </h4>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Baris Pertama (Instansi)</label>
                                    <input type="text" name="kop_line_1" id="kop_1" value="{{ $settings['kop_line_1'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none font-semibold text-sm transition-all" onkeyup="syncKop()">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Baris Kedua (Satuan Kerja)</label>
                                    <input type="text" name="kop_line_2" id="kop_2" value="{{ $settings['kop_line_2'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none font-semibold text-sm transition-all" onkeyup="syncKop()">
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-2xl border border-slate-100 p-6 flex flex-col items-center justify-center text-center">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-4">Preview Kop Surat</p>
                                <div class="flex items-center gap-4 border-b-2 border-slate-900 pb-4 w-full">
                                    <img src="{{ asset('logo1.png') }}" class="w-12 h-12 object-contain">
                                    <div class="text-left">
                                        <h2 id="preview_kop_1" class="text-xs font-bold text-slate-900 uppercase leading-tight">{{ $settings['kop_line_1'] ?? 'KEMENTERIAN HUKUM DAN HAM RI' }}</h2>
                                        <h3 id="preview_kop_2" class="text-sm font-extrabold text-slate-900 uppercase leading-tight">{{ $settings['kop_line_2'] ?? 'LAPAS KELAS IIB JOMBANG' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-10 py-3.5 bg-slate-900 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 shadow-lg shadow-slate-200 transition-all btn-3d">
                            Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </section>

            <!-- Siaran & Style -->
            <section id="siaran" class="space-y-6 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-lg font-bold text-slate-900">Siaran & Gaya Visual</h3>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Create New -->
                        <form action="{{ route('announcements.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 card-3d">
                            @csrf
                            <h4 class="text-sm font-bold text-slate-900 mb-2">Buat Siaran Baru</h4>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe Tampilan</label>
                                <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500 appearance-none bg-slate-50">
                                    <option value="banner">Running Text (Marquee)</option>
                                    <option value="popup">Popup Modal (Alert)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Isi Pesan</label>
                                <textarea name="message" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500 bg-slate-50" placeholder="Ketik pengumuman..."></textarea>
                            </div>
                            <button type="submit" class="w-full py-3.5 bg-blue-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition-all btn-3d">
                                Publikasikan Siaran
                            </button>
                        </form>

                        <!-- Global Visual Settings -->
                        <form action="{{ route('settings.update') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 card-3d">
                            @csrf
                            <h4 class="text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                                <i data-lucide="palette" class="w-4 h-4 text-amber-500"></i>
                                Style Siaran
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Warna Background</label>
                                    <input type="color" name="running_text_bg" value="{{ $settings['running_text_bg'] ?? '#0F172A' }}" class="w-full h-10 rounded-lg cursor-pointer border border-slate-200 p-1 bg-slate-50">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Warna Teks</label>
                                    <input type="color" name="running_text_color" value="{{ $settings['running_text_color'] ?? '#FFFFFF' }}" class="w-full h-10 rounded-lg cursor-pointer border border-slate-200 p-1 bg-slate-50">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Kecepatan (Detik)</label>
                                <input type="number" name="running_text_speed" value="{{ $settings['running_text_speed'] ?? '20' }}" min="5" max="100" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500 bg-slate-50">
                                <p class="text-[8px] text-slate-400 mt-1 italic">*Semakin besar nilai, semakin lambat.</p>
                            </div>
                            <button type="submit" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-blue-600 transition-all">
                                Update Visual
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <h4 class="text-sm font-bold text-slate-900">Riwayat & Kontrol Siaran</h4>
                        <div class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
                            @forelse($announcements as $ann)
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between gap-4 card-3d">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-[9px] font-bold text-slate-500 uppercase">{{ $ann->type }}</span>
                                        <span class="px-2 py-0.5 rounded-lg {{ $ann->is_active ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-slate-50 text-slate-400 border border-slate-100' }} text-[9px] font-bold uppercase">
                                            {{ $ann->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $ann->message }}</p>
                                    <p class="text-[9px] text-slate-400 mt-2 font-bold uppercase tracking-widest">{{ $ann->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('announcements.toggle', $ann->id) }}" method="POST" class="no-loader">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-100 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all shadow-sm">
                                            <i data-lucide="{{ $ann->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST" class="no-loader">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus siaran ini?')" class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-100 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all shadow-sm">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-20 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                                <i data-lucide="megaphone-off" class="w-10 h-10 text-slate-200 mx-auto mb-4"></i>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada riwayat siaran</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <!-- Master Data -->
            <section id="master" class="space-y-6 pt-6 border-t border-slate-200">
                <div class="flex items-center gap-3 pb-2">
                    <h3 class="text-lg font-bold text-slate-900">Master Data</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Jabatan -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d flex flex-col h-[400px]">
                        <div class="p-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center shrink-0">
                            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Daftar Jabatan</h4>
                            <span class="text-[10px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-100">{{ $positions->count() }}</span>
                        </div>
                        <div class="p-5 flex-1 flex flex-col min-h-0">
                            <form action="{{ route('settings.positions.store') }}" method="POST" class="flex gap-2 mb-4 shrink-0">
                                @csrf
                                <input type="text" name="name" required class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500 bg-slate-50" placeholder="Jabatan baru...">
                                <button type="submit" class="w-11 h-11 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-blue-600 transition-all btn-3d">
                                    <i data-lucide="plus" class="w-5 h-5"></i>
                                </button>
                            </form>
                            <div class="overflow-y-auto custom-scrollbar space-y-2 flex-1 pr-2">
                                @foreach($positions as $p)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-blue-200 transition-all">
                                    <span class="text-xs font-bold text-slate-700 uppercase">{{ $p->name }}</span>
                                    <form action="{{ route('settings.positions.destroy', $p->id) }}" method="POST" class="no-loader">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Unit Kerja -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden card-3d flex flex-col h-[400px]">
                        <div class="p-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center shrink-0">
                            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Daftar Unit Kerja</h4>
                            <span class="text-[10px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-100">{{ $workUnits->count() }}</span>
                        </div>
                        <div class="p-5 flex-1 flex flex-col min-h-0">
                            <form action="{{ route('settings.work-units.store') }}" method="POST" class="flex gap-2 mb-4 shrink-0">
                                @csrf
                                <input type="text" name="name" required class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500 bg-slate-50" placeholder="Unit baru...">
                                <button type="submit" class="w-11 h-11 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-blue-600 transition-all btn-3d">
                                    <i data-lucide="plus" class="w-5 h-5"></i>
                                </button>
                            </form>
                            <div class="overflow-y-auto custom-scrollbar space-y-2 flex-1 pr-2">
                                @foreach($workUnits as $u)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-blue-200 transition-all">
                                    <span class="text-xs font-bold text-slate-700 uppercase">{{ $u->name }}</span>
                                    <form action="{{ route('settings.work-units.destroy', $u->id) }}" method="POST" class="no-loader">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

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

@push('scripts')
<script>
    function syncKop() {
        const kop1 = document.getElementById('kop_1').value;
        const kop2 = document.getElementById('kop_2').value;
        document.getElementById('preview_kop_1').innerText = kop1 || 'KEMENTERIAN HUKUM DAN HAM RI';
        document.getElementById('preview_kop_2').innerText = kop2 || 'LAPAS KELAS IIB JOMBANG';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('[data-nav]');
        const sections = document.querySelectorAll('section[id]');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 120) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('bg-white', 'border-slate-200', 'text-slate-700', 'shadow-sm');
                link.classList.add('text-slate-500', 'border-transparent');
                if (link.getAttribute('data-nav') === current) {
                    link.classList.remove('text-slate-500', 'border-transparent');
                    link.classList.add('bg-white', 'border-slate-200', 'text-slate-700', 'shadow-sm');
                }
            });
        });
    });
</script>
@endpush
@endsection
