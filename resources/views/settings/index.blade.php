@extends('layouts.app')

@section('title', 'Pengaturan')
@section('header-title', 'Pengaturan Sistem')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="md:w-64 shrink-0">
            <div class="sticky top-24 space-y-1">
                <a href="#umum" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold shadow-sm hover:border-blue-300 transition-all active-nav" data-nav="umum">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <span>Umum</span>
                </a>
                <a href="#siaran" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 font-semibold hover:bg-white hover:border-slate-200 border border-transparent transition-all" data-nav="siaran">
                    <i data-lucide="megaphone" class="w-4 h-4"></i>
                    <span>Siaran</span>
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
        <div class="flex-1 space-y-10">
            <!-- Umum -->
            <section id="umum" class="space-y-6">
                <div class="flex items-center gap-3 pb-2 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-900">Konfigurasi Umum</h3>
                </div>

                <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
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

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="text-sm font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <i data-lucide="building" class="w-4 h-4 text-slate-400"></i>
                            Identitas Kop Surat
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Baris Pertama</label>
                                <input type="text" name="kop_line_1" value="{{ $settings['kop_line_1'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none font-semibold text-sm transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Baris Kedua</label>
                                <input type="text" name="kop_line_2" value="{{ $settings['kop_line_2'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none font-semibold text-sm transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>

            <!-- Siaran -->
            <section id="siaran" class="space-y-6 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between pb-2">
                    <h3 class="text-lg font-bold text-slate-900">Siaran & Pengumuman</h3>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1">
                        <form action="{{ route('announcements.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                            @csrf
                            <h4 class="text-sm font-bold text-slate-900 mb-2">Buat Siaran Baru</h4>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tipe</label>
                                <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500">
                                    <option value="banner">Running Text</option>
                                    <option value="popup">Popup Modal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Pesan</label>
                                <textarea name="message" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500" placeholder="Ketik pengumuman..."></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition-all">
                                Publikasikan
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 space-y-4">
                        <h4 class="text-sm font-bold text-slate-900">Riwayat Siaran</h4>
                        <div class="space-y-3">
                            @forelse($announcements as $ann)
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-[10px] font-bold text-slate-500 uppercase">{{ $ann->type }}</span>
                                        <span class="px-2 py-0.5 rounded-full {{ $ann->is_active ? 'bg-green-50 text-green-600' : 'bg-slate-50 text-slate-400' }} text-[10px] font-bold uppercase">
                                            {{ $ann->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $ann->message }}</p>
                                    <p class="text-[10px] text-slate-400 mt-2 font-medium">{{ $ann->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('announcements.toggle', $ann->id) }}" method="POST" class="no-loader">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 text-slate-400 hover:text-blue-600 hover:bg-blue-50">
                                            <i data-lucide="{{ $ann->is_active ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST" class="no-loader">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 text-slate-400 hover:text-red-600 hover:bg-red-50">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada siaran</p>
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
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <h4 class="text-sm font-bold text-slate-900">Jabatan</h4>
                            <span class="text-[10px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-100">{{ $positions->count() }}</span>
                        </div>
                        <div class="p-5 space-y-4">
                            <form action="{{ route('settings.positions.store') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="name" required class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500" placeholder="Jabatan baru...">
                                <button type="submit" class="w-11 h-11 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-slate-800">
                                    <i data-lucide="plus" class="w-5 h-5"></i>
                                </button>
                            </form>
                            <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-2">
                                @foreach($positions as $p)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 group">
                                    <span class="text-xs font-bold text-slate-700">{{ $p->name }}</span>
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
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <h4 class="text-sm font-bold text-slate-900">Unit Kerja</h4>
                            <span class="text-[10px] font-bold text-slate-400 bg-white px-2 py-0.5 rounded-full border border-slate-100">{{ $workUnits->count() }}</span>
                        </div>
                        <div class="p-5 space-y-4">
                            <form action="{{ route('settings.work-units.store') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="name" required class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold outline-none focus:border-blue-500" placeholder="Unit baru...">
                                <button type="submit" class="w-11 h-11 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-slate-800">
                                    <i data-lucide="plus" class="w-5 h-5"></i>
                                </button>
                            </form>
                            <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-2">
                                @foreach($workUnits as $u)
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 group">
                                    <span class="text-xs font-bold text-slate-700">{{ $u->name }}</span>
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
