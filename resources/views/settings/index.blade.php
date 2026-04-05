@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('header-title', 'Konfigurasi Platform')

@section('content')
@php
    $widgetsList = [
        ['key' => 'widget_stats', 'label' => 'Statistik Utama', 'icon' => 'bar-chart-3'],
        ['key' => 'widget_employees', 'label' => 'Status Unit Kerja', 'icon' => 'users'],
        ['key' => 'widget_chart', 'label' => 'Grafik Distribusi', 'icon' => 'pie-chart'],
        ['key' => 'widget_activity', 'label' => 'Aktivitas Terkini', 'icon' => 'activity'],
        ['key' => 'widget_compliance', 'label' => 'Status Kepatuhan', 'icon' => 'shield-check'],
        ['key' => 'widget_feed', 'label' => 'Antrean Berkas', 'icon' => 'zap'],
    ];

    $enabledWidgets = collect($widgetsList)->filter(fn ($widget) => ($settings[$widget['key']] ?? 'on') === 'on')->count();
    $activeAnnouncementsCount = $announcements->filter(function ($announcement) {
        return $announcement->is_active
            && (!$announcement->starts_at || $announcement->starts_at->lte(now()))
            && (!$announcement->expires_at || $announcement->expires_at->gte(now()));
    })->count();
    $scheduledAnnouncementsCount = $announcements->filter(fn ($announcement) => $announcement->starts_at && $announcement->starts_at->isFuture())->count();
@endphp

<style>
    .settings-anchor {
        background: rgba(241, 245, 249, 0.9);
        border: 1px solid transparent;
        color: #64748b;
        transition: all 0.25s ease;
    }

    .settings-anchor:hover {
        color: #0F172A;
        border-color: #dbe4ee;
        background: #ffffff;
        box-shadow: 0 12px 24px -20px rgba(15, 23, 42, 0.22);
    }

    .settings-anchor.is-active {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        border-color: #0F172A;
        color: #ffffff;
        box-shadow: 0 24px 48px -26px rgba(59, 130, 246, 0.3);
    }

    .settings-pattern {
        background-image:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.14), transparent 34%),
            radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.2), transparent 30%);
    }

    .settings-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(171, 171, 171, 0.55) transparent;
    }

    .settings-scrollbar::-webkit-scrollbar {
        width: 7px;
    }

    .settings-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(171, 171, 171, 0.55);
        border-radius: 999px;
    }

    .banner-preview-track {
        animation: settings-banner-marquee linear infinite;
    }

    @keyframes settings-banner-marquee {
        0% {
            transform: translateX(0%);
        }
        100% {
            transform: translateX(-50%);
        }
    }
</style>

<div class="mx-auto max-w-7xl space-y-8 pb-24">
    @if ($errors->any())
        <div class="rounded-[32px] border border-red-100 bg-red-50 px-6 py-5 text-sm text-red-700 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-red-500 shadow-sm">
                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                </div>
                <div class="space-y-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-red-500">Perlu Diperiksa</p>
                    <p class="text-sm font-bold text-[#0F172A]">Beberapa input belum valid. Cek kembali bagian yang Anda ubah sebelum menyimpan.</p>
                    <ul class="space-y-1 text-xs font-semibold text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <section class="settings-pattern relative overflow-hidden rounded-[40px] bg-[#0F172A] px-8 py-8 text-white shadow-2xl shadow-slate-900/15 sm:px-10 sm:py-10">
        <div class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-52 w-52 translate-x-10 translate-y-14 rounded-full bg-[#EAB308]/30 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[10px] font-black uppercase tracking-[0.28em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#EAB308]"></span>
                    Panel Pengaturan Sistem
                </div>
                <h2 class="max-w-2xl text-3xl font-black tracking-tight sm:text-4xl">Konfigurasi inti platform.</h2>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <a href="{{ route('settings.health') }}" class="inline-flex items-center justify-center gap-3 rounded-[24px] border border-white/10 bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-[#0F172A] shadow-xl shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-[#F1F5F9]">
                    <i data-lucide="heart-pulse" class="h-4 w-4 text-[#EAB308]"></i>
                    Kesehatan Sistem
                </a>
                <a href="#broadcast" class="inline-flex items-center justify-center gap-3 rounded-[24px] border border-white/15 bg-white/5 px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white transition-all hover:border-white/25 hover:bg-white/10">
                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                    Kelola Siaran
                </a>
            </div>
        </div>

        <div class="relative z-10 mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-white/55">Modul Dashboard Aktif</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-4xl font-black tracking-tight">{{ $enabledWidgets }}</p>
                        <p class="mt-1 text-xs font-bold text-white/65">dari {{ count($widgetsList) }} modul utama</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                        <i data-lucide="layout-grid" class="h-5 w-5"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-white/55">Siaran Sedang Tayang</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-4xl font-black tracking-tight">{{ $activeAnnouncementsCount }}</p>
                        <p class="mt-1 text-xs font-bold text-white/65">{{ $scheduledAnnouncementsCount }} siaran terjadwal berikutnya</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                        <i data-lucide="radio" class="h-5 w-5"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-[28px] border border-white/10 bg-white/5 p-5 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-white/55">Master Data Tersedia</p>
                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-4xl font-black tracking-tight">{{ $positions->count() + $workUnits->count() }}</p>
                        <p class="mt-1 text-xs font-bold text-white/65">{{ $positions->count() }} jabatan, {{ $workUnits->count() }} unit kerja</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                        <i data-lucide="database" class="h-5 w-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="sticky top-24 z-[5]">
        <div class="overflow-x-auto rounded-[28px] border border-[#EFEFEF] bg-white/90 p-2 shadow-lg shadow-slate-900/5 backdrop-blur">
            <div class="flex min-w-max gap-2">
                <a href="#general" data-settings-anchor class="settings-anchor is-active inline-flex items-center gap-3 rounded-[20px] px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em]">
                    <i data-lucide="sliders-horizontal" class="h-4 w-4"></i>
                    Konfigurasi Inti
                </a>
                <a href="#broadcast" data-settings-anchor class="settings-anchor inline-flex items-center gap-3 rounded-[20px] px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em]">
                    <i data-lucide="megaphone" class="h-4 w-4"></i>
                    Siaran Pengumuman
                </a>
                <a href="#master" data-settings-anchor class="settings-anchor inline-flex items-center gap-3 rounded-[20px] px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em]">
                    <i data-lucide="blocks" class="h-4 w-4"></i>
                    Master Data
                </a>
            </div>
        </div>
    </div>

    <section id="general" class="scroll-mt-32">
        <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
            @csrf

            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[#EAB308]">Konfigurasi Inti</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight text-[#0F172A]">Konfigurasi inti.</h3>
                </div>
                <div class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] shadow-sm">
                    <i data-lucide="save" class="h-4 w-4 text-[#0F172A]"></i>
                    Simpan setelah selesai menyesuaikan
                </div>
            </div>

            <div class="grid gap-8 xl:grid-cols-3">
                <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm xl:col-span-2">
                    <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#0F172A] shadow-sm">
                                <i data-lucide="layout-grid" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Modul Dashboard</h4>
                            </div>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.24em] text-[#0F172A] shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ $enabledWidgets }} aktif
                        </div>
                    </div>

                    <div class="grid gap-4 p-6 sm:p-8 xl:grid-cols-2">
                        @foreach ($widgetsList as $widget)
                            @php $isChecked = ($settings[$widget['key']] ?? 'on') === 'on'; @endphp
                            <label class="group flex cursor-pointer items-center justify-between gap-4 rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5 transition-all hover:border-[#EAB308]/25 hover:bg-white hover:shadow-lg hover:shadow-red-100/20">
                                <div class="flex min-w-0 items-center gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#8A8A8A] transition-colors group-hover:text-[#EAB308]">
                                        <i data-lucide="{{ $widget['icon'] }}" class="h-5 w-5"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-[#0F172A]">{{ $widget['label'] }}</p>
                                            <span class="inline-flex rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-[0.2em] {{ $isChecked ? 'border-emerald-100 bg-emerald-50 text-emerald-600' : 'border-slate-200 bg-white text-slate-400' }}">
                                                {{ $isChecked ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-4">
                                    <span class="hidden text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A] sm:inline">Tampilkan</span>
                                    <div class="relative inline-flex shrink-0 items-center">
                                        <input type="hidden" name="{{ $widget['key'] }}" value="off">
                                        <input type="checkbox" name="{{ $widget['key'] }}" value="on" class="peer sr-only" {{ $isChecked ? 'checked' : '' }}>
                                        <div class="h-7 w-12 rounded-full bg-slate-200 transition-all peer-checked:bg-[#EAB308] after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:after:translate-x-5"></div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm">
                    <div class="border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#EAB308] shadow-sm">
                                <i data-lucide="shield-check" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Keamanan Dokumen</h4>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-8">
                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Status Watermark</label>
                            <select name="watermark_enabled" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                                <option value="on" {{ ($settings['watermark_enabled'] ?? 'on') === 'on' ? 'selected' : '' }}>Aktif</option>
                                <option value="off" {{ ($settings['watermark_enabled'] ?? 'on') === 'off' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Teks Pengaman</label>
                            <input type="text" name="watermark_text" value="{{ $settings['watermark_text'] ?? 'SINERGI PAS JOMBANG' }}" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                        </div>

                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Nomor WhatsApp Blast</label>
                            <input type="text" name="compliance_whatsapp_number" value="{{ $settings['compliance_whatsapp_number'] ?? '628123456789' }}" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5" placeholder="628xxxxxxxxxx">
                        </div>

                        <div class="rounded-[28px] border border-amber-100 bg-amber-50 px-5 py-4 text-sm font-medium leading-relaxed text-amber-800">
                            <div class="flex items-start gap-3">
                                <i data-lucide="badge-alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"></i>
                                <p>Gunakan teks watermark yang singkat, jelas, dan konsisten agar dokumen resmi mudah dikenali tanpa mengganggu keterbacaan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 xl:grid-cols-2">
                <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm">
                    <div class="border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-blue-600 shadow-sm">
                                <i data-lucide="monitor" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Running Banner</h4>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-8">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                                <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Warna Latar</label>
                                <input id="banner-bg-input" type="color" name="running_text_bg" value="{{ $settings['running_text_bg'] ?? '#0F172A' }}" class="mt-3 h-14 w-full cursor-pointer rounded-[20px] border border-[#EFEFEF] bg-white p-2">
                            </div>
                            <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                                <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Warna Teks</label>
                                <input id="banner-text-input" type="color" name="running_text_color" value="{{ $settings['running_text_color'] ?? '#FFFFFF' }}" class="mt-3 h-14 w-full cursor-pointer rounded-[20px] border border-[#EFEFEF] bg-white p-2">
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Kecepatan Banner</label>
                                </div>
                                <span id="banner-speed-display" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                                    {{ $settings['running_text_speed'] ?? '20' }} detik
                                </span>
                            </div>
                            <input id="banner-speed-input" type="number" min="5" name="running_text_speed" value="{{ $settings['running_text_speed'] ?? '20' }}" class="mt-4 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                        </div>

                        <div class="overflow-hidden rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9]">
                            <div class="border-b border-[#EFEFEF] px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Preview Banner</p>
                            </div>
                            <div id="banner-preview" class="overflow-hidden px-4 py-3 text-[10px] font-black uppercase tracking-[0.24em]" style="background-color: {{ $settings['running_text_bg'] ?? '#0F172A' }}; color: {{ $settings['running_text_color'] ?? '#FFFFFF' }};">
                                <div id="banner-preview-track" class="banner-preview-track flex min-w-max gap-10" style="animation-duration: {{ $settings['running_text_speed'] ?? '20' }}s;">
                                    <span>Sistem informasi pegawai dan arsip berjalan sinkron untuk seluruh unit kerja.</span>
                                    <span>Sistem informasi pegawai dan arsip berjalan sinkron untuk seluruh unit kerja.</span>
                                    <span>Sistem informasi pegawai dan arsip berjalan sinkron untuk seluruh unit kerja.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm">
                    <div class="border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-emerald-600 shadow-sm">
                                <i data-lucide="building-2" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Identitas Instansi</h4>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-8">
                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Baris Utama Kop</label>
                            <input id="kop-line-1-input" type="text" name="kop_line_1" value="{{ $settings['kop_line_1'] ?? 'LEMBAGA PEMASYARAKATAN JOMBANG' }}" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                        </div>

                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Baris Kedua Kop</label>
                            <input id="kop-line-2-input" type="text" name="kop_line_2" value="{{ $settings['kop_line_2'] ?? 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR' }}" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                        </div>

                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#0F172A] p-6 text-white">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/55">Preview Identitas</p>
                            <div class="mt-5 rounded-[24px] border border-white/10 bg-white/5 px-6 py-7 text-center">
                                <p id="kop-line-1-preview" class="text-sm font-black uppercase tracking-[0.2em]">{{ $settings['kop_line_1'] ?? 'LEMBAGA PEMASYARAKATAN JOMBANG' }}</p>
                                <div class="mx-auto my-4 h-px w-24 bg-white/20"></div>
                                <p id="kop-line-2-preview" class="text-xs font-bold uppercase tracking-[0.18em] text-white/70">{{ $settings['kop_line_2'] ?? 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 rounded-[32px] border border-[#EFEFEF] bg-white px-6 py-6 shadow-sm md:flex-row md:items-center md:justify-between md:px-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#F1F5F9] text-[#0F172A]">
                        <i data-lucide="check-check" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <p class="text-sm font-black text-[#0F172A]">Konfigurasi utama siap diperbarui.</p>
                    </div>
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-[22px] bg-[#0F172A] px-8 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-slate-900/10 transition-all hover:-translate-y-0.5 hover:bg-[#EAB308]">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Simpan Perubahan Utama
                </button>
            </div>
        </form>
    </section>

    <section id="broadcast" class="scroll-mt-32 space-y-8 border-t border-[#EFEFEF] pt-10">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[#EAB308]">Siaran Pengumuman</p>
                <h3 class="mt-2 text-3xl font-black tracking-tight text-[#0F172A]">Siaran pengumuman.</h3>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $activeAnnouncementsCount }} live
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    {{ $scheduledAnnouncementsCount }} terjadwal
                </span>
            </div>
        </div>

        <div class="grid gap-8 xl:grid-cols-[380px,minmax(0,1fr)]">
            <div class="h-fit overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm xl:sticky xl:top-36">
                <div class="border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#EAB308] shadow-sm">
                            <i data-lucide="send" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Buat Siaran Baru</h4>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 p-8">
                    <div class="overflow-hidden rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9]">
                        <div class="border-b border-[#EFEFEF] px-5 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Preview Mode</p>
                        </div>
                        <div class="space-y-4 p-5">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                                <i data-lucide="sparkles" class="h-4 w-4 text-[#EAB308]"></i>
                                <span id="announcement-type-badge">Running Text</span>
                            </div>
                            <p id="announcement-type-hint" class="text-sm font-medium text-[#8A8A8A]">Tampil di bagian atas aplikasi.</p>
                        </div>
                    </div>

                    <form action="{{ route('announcements.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Pesan Siaran</label>
                            <textarea name="message" rows="5" required class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5" placeholder="Tulis pesan resmi yang akan tampil kepada seluruh pengguna...">{{ old('message') }}</textarea>
                        </div>

                        <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                            <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Tipe Siaran</label>
                            <select id="announcement-type" name="type" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                                <option value="banner" {{ old('type', 'banner') === 'banner' ? 'selected' : '' }}>Running Text</option>
                                <option value="popup" {{ old('type') === 'popup' ? 'selected' : '' }}>Popup Modal</option>
                            </select>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                                <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Mulai Tayang</label>
                                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-4 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                            </div>
                            <div class="rounded-[28px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                                <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A]">Selesai Tayang</label>
                                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-4 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                            </div>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center gap-3 rounded-[22px] bg-[#EAB308] px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-red-100 transition-all hover:-translate-y-0.5 hover:bg-[#0F172A]">
                            <i data-lucide="send" class="h-4 w-4"></i>
                            Publikasikan Siaran
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Riwayat Siaran</h4>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] shadow-sm">
                        <i data-lucide="history" class="h-4 w-4 text-[#0F172A]"></i>
                        {{ $announcements->count() }} total siaran
                    </div>
                </div>

                <div class="settings-scrollbar max-h-[920px] space-y-4 overflow-y-auto p-8">
                    @forelse ($announcements as $announcement)
                        @php
                            $statusLabel = 'Tidak aktif';
                            $statusClasses = 'bg-slate-100 text-slate-500 border-slate-200';

                            if ($announcement->starts_at && $announcement->starts_at->isFuture()) {
                                $statusLabel = 'Terjadwal';
                                $statusClasses = 'bg-blue-50 text-blue-600 border-blue-100';
                            } elseif ($announcement->is_active && (!$announcement->expires_at || $announcement->expires_at->isFuture())) {
                                $statusLabel = 'Live';
                                $statusClasses = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                            }
                        @endphp

                        <div class="rounded-[32px] border border-[#EFEFEF] bg-[#F1F5F9] p-6 transition-all hover:-translate-y-0.5 hover:border-[#0F172A]/10 hover:bg-white hover:shadow-lg hover:shadow-slate-900/5">
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] {{ $announcement->type === 'banner' ? 'border-[#EFEFEF] bg-white text-[#0F172A]' : 'border-violet-100 bg-violet-50 text-violet-600' }}">
                                            {{ $announcement->type === 'banner' ? 'Running Text' : 'Popup Modal' }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] {{ $statusClasses }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    <p class="mt-4 text-base font-bold leading-relaxed text-[#0F172A]">
                                        {{ $announcement->message }}
                                    </p>

                                    <div class="mt-5 grid gap-3 text-sm font-medium text-[#8A8A8A] sm:grid-cols-3">
                                        <div class="rounded-[20px] border border-[#EFEFEF] bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Mulai</p>
                                            <p class="mt-2 font-bold text-[#0F172A]">{{ $announcement->starts_at ? $announcement->starts_at->format('d M Y, H:i') : 'Langsung tayang' }}</p>
                                        </div>
                                        <div class="rounded-[20px] border border-[#EFEFEF] bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Berakhir</p>
                                            <p class="mt-2 font-bold text-[#0F172A]">{{ $announcement->expires_at ? $announcement->expires_at->format('d M Y, H:i') : 'Sampai dimatikan' }}</p>
                                        </div>
                                        <div class="rounded-[20px] border border-[#EFEFEF] bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Dibuat</p>
                                            <p class="mt-2 font-bold text-[#0F172A]">{{ $announcement->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 gap-3 xl:flex-col">
                                    <form action="{{ route('announcements.toggle', $announcement->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#0F172A] shadow-sm transition-all hover:-translate-y-0.5 hover:bg-[#0F172A] hover:text-white" title="{{ $announcement->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i data-lucide="{{ $announcement->is_active ? 'eye-off' : 'eye' }}" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-red-100 bg-white text-red-500 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-red-500 hover:text-white" title="Hapus siaran">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[32px] border border-dashed border-[#E2E0DC] bg-[#F1F5F9] px-6 py-16 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-white text-[#ABABAB] shadow-sm">
                                <i data-lucide="radio-tower" class="h-7 w-7"></i>
                            </div>
                            <p class="mt-5 text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Belum ada siaran</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section id="master" class="scroll-mt-32 space-y-8 border-t border-[#EFEFEF] pt-10">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.28em] text-[#EAB308]">Master Data</p>
                <h3 class="mt-2 text-3xl font-black tracking-tight text-[#0F172A]">Master data.</h3>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    {{ $positions->count() }} jabatan
                </span>
                <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $workUnits->count() }} unit kerja
                </span>
            </div>
        </div>

        <div class="grid gap-8 xl:grid-cols-2">
            <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-indigo-600 shadow-sm">
                            <i data-lucide="briefcase" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Master Jabatan</h4>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                        {{ $positions->count() }} total
                    </span>
                </div>

                <div class="space-y-6 p-8">
                    <form action="{{ route('settings.positions.store') }}" method="POST" class="flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <input type="text" name="name" required placeholder="Tambah jabatan baru..." class="flex-1 rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-[22px] bg-indigo-600 px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-lg shadow-indigo-100 transition-all hover:-translate-y-0.5 hover:bg-indigo-700">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Tambah
                        </button>
                    </form>

                    <div class="settings-scrollbar max-h-[420px] space-y-3 overflow-y-auto pr-1">
                        @forelse ($positions as $position)
                            <div class="flex items-center justify-between gap-4 rounded-[24px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 transition-all hover:bg-white hover:shadow-md">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#0F172A]">{{ $position->name }}</p>
                                    <p class="mt-1 text-[11px] font-medium text-[#8A8A8A]">Slug: {{ $position->slug }}</p>
                                </div>
                                <form action="{{ route('settings.positions.destroy', $position->id) }}" method="POST" class="no-loader">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#ABABAB] transition-all hover:border-red-100 hover:bg-red-50 hover:text-red-500" title="Hapus jabatan">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-[28px] border border-dashed border-[#E2E0DC] bg-[#F1F5F9] px-6 py-12 text-center">
                                <p class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Belum ada data jabatan</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[36px] border border-[#EFEFEF] bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-emerald-600 shadow-sm">
                            <i data-lucide="blocks" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Master Unit Kerja</h4>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                        {{ $workUnits->count() }} total
                    </span>
                </div>

                <div class="space-y-6 p-8">
                    <form action="{{ route('settings.work-units.store') }}" method="POST" class="flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <input type="text" name="name" required placeholder="Tambah unit kerja baru..." class="flex-1 rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-[22px] bg-emerald-600 px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-lg shadow-emerald-100 transition-all hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Tambah
                        </button>
                    </form>

                    <div class="settings-scrollbar max-h-[420px] space-y-3 overflow-y-auto pr-1">
                        @forelse ($workUnits as $workUnit)
                            <div class="flex items-center justify-between gap-4 rounded-[24px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 transition-all hover:bg-white hover:shadow-md">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#0F172A]">{{ $workUnit->name }}</p>
                                    <p class="mt-1 text-[11px] font-medium text-[#8A8A8A]">Slug: {{ $workUnit->slug }}</p>
                                </div>
                                <form action="{{ route('settings.work-units.destroy', $workUnit->id) }}" method="POST" class="no-loader">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-[#ABABAB] transition-all hover:border-red-100 hover:bg-red-50 hover:text-red-500" title="Hapus unit kerja">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="rounded-[28px] border border-dashed border-[#E2E0DC] bg-[#F1F5F9] px-6 py-12 text-center">
                                <p class="text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Belum ada unit kerja</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#0F172A',
            customClass: { popup: 'rounded-[32px]' }
        });
    </script>
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const anchorLinks = Array.from(document.querySelectorAll('[data-settings-anchor]'));
            const sections = anchorLinks
                .map((link) => document.querySelector(link.getAttribute('href')))
                .filter(Boolean);

            const setActiveAnchor = (targetId) => {
                anchorLinks.forEach((link) => {
                    link.classList.toggle('is-active', link.getAttribute('href') === targetId);
                });
            };

            if (sections.length) {
                const observer = new IntersectionObserver((entries) => {
                    const visibleEntry = entries
                        .filter((entry) => entry.isIntersecting)
                        .sort((first, second) => second.intersectionRatio - first.intersectionRatio)[0];

                    if (visibleEntry) {
                        setActiveAnchor(`#${visibleEntry.target.id}`);
                    }
                }, {
                    rootMargin: '-20% 0px -55% 0px',
                    threshold: [0.2, 0.45, 0.7]
                });

                sections.forEach((section) => observer.observe(section));
            }

            anchorLinks.forEach((link) => {
                link.addEventListener('click', () => setActiveAnchor(link.getAttribute('href')));
            });

            const bannerBgInput = document.getElementById('banner-bg-input');
            const bannerTextInput = document.getElementById('banner-text-input');
            const bannerSpeedInput = document.getElementById('banner-speed-input');
            const bannerPreview = document.getElementById('banner-preview');
            const bannerPreviewTrack = document.getElementById('banner-preview-track');
            const bannerSpeedDisplay = document.getElementById('banner-speed-display');
            const kopLine1Input = document.getElementById('kop-line-1-input');
            const kopLine2Input = document.getElementById('kop-line-2-input');
            const kopLine1Preview = document.getElementById('kop-line-1-preview');
            const kopLine2Preview = document.getElementById('kop-line-2-preview');

            const syncBannerPreview = () => {
                if (!bannerPreview || !bannerPreviewTrack || !bannerBgInput || !bannerTextInput || !bannerSpeedInput) {
                    return;
                }

                const animationDuration = `${Math.max(5, Number(bannerSpeedInput.value || 20))}s`;
                bannerPreview.style.backgroundColor = bannerBgInput.value;
                bannerPreview.style.color = bannerTextInput.value;
                bannerPreviewTrack.style.animationDuration = animationDuration;
                bannerSpeedDisplay.textContent = `${Math.max(5, Number(bannerSpeedInput.value || 20))} detik`;
            };

            [bannerBgInput, bannerTextInput, bannerSpeedInput].forEach((input) => {
                if (input) {
                    input.addEventListener('input', syncBannerPreview);
                }
            });

            syncBannerPreview();

            const syncInstitutionPreview = () => {
                if (kopLine1Input && kopLine1Preview) {
                    kopLine1Preview.textContent = kopLine1Input.value || 'LEMBAGA PEMASYARAKATAN JOMBANG';
                }

                if (kopLine2Input && kopLine2Preview) {
                    kopLine2Preview.textContent = kopLine2Input.value || 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR';
                }
            };

            [kopLine1Input, kopLine2Input].forEach((input) => {
                if (input) {
                    input.addEventListener('input', syncInstitutionPreview);
                }
            });

            syncInstitutionPreview();

            const announcementType = document.getElementById('announcement-type');
            const announcementTypeBadge = document.getElementById('announcement-type-badge');
            const announcementTypeHint = document.getElementById('announcement-type-hint');

            const syncAnnouncementPreview = () => {
                if (!announcementType || !announcementTypeBadge || !announcementTypeHint) {
                    return;
                }

                if (announcementType.value === 'popup') {
                    announcementTypeBadge.textContent = 'Popup Modal';
                    announcementTypeHint.textContent = 'Siaran akan muncul sebagai modal yang perlu diperhatikan pengguna saat halaman dimuat.';
                    return;
                }

                announcementTypeBadge.textContent = 'Running Text';
                announcementTypeHint.textContent = 'Siaran akan tampil melintas pada bagian atas aplikasi dan cocok untuk informasi operasional singkat.';
            };

            if (announcementType) {
                announcementType.addEventListener('change', syncAnnouncementPreview);
                syncAnnouncementPreview();
            }
        });
    </script>
@endpush
@endsection
