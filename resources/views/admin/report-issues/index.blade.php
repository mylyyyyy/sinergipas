@extends('layouts.app')

@section('title', 'Manajemen Laporan')
@section('header-title', 'Helpdesk Support Center')

@section('content')
@php
    $currentIssues = $issues->getCollection();
    $openCount = $issueStats['open'];
    $resolvedCount = $issueStats['resolved'];
    $closedCount = $issueStats['closed'];
    $searchActive = request()->anyFilled(['search', 'status', 'date', 'work_unit_id']);
@endphp

<style>
    .issue-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }

    .issue-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 48px -32px rgba(30, 36, 50, 0.25);
    }
</style>

<div class="space-y-8">
    <section class="overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
        <div class="border-b border-[#F2F1EE] bg-[#F1F5F9] px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#EAB308]">Laporan Masalah</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-[#0F172A]">Antrean laporan.</h2>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                        <i data-lucide="message-square" class="h-4 w-4 text-[#EAB308]"></i>
                        {{ $issueStats['total'] }} total
                    </span>
                    @if($searchActive)
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-amber-700 shadow-sm">
                            <i data-lucide="filter" class="h-4 w-4"></i>
                            Filter aktif
                        </span>
                    @endif
                    <button type="button" id="deleteAllIssuesBtn" class="inline-flex items-center gap-3 rounded-[20px] border border-red-100 bg-red-50 px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em] text-red-600 transition-all hover:bg-red-600 hover:text-white">
                        <i data-lucide="flame" class="h-4 w-4"></i>
                        Delete All
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-4 px-6 py-6 sm:px-8 md:grid-cols-3">
            <div class="rounded-[24px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Open</p>
                <p class="mt-3 text-3xl font-black text-[#0F172A]">{{ $openCount }}</p>
            </div>
            <div class="rounded-[24px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Resolved</p>
                <p class="mt-3 text-3xl font-black text-[#0F172A]">{{ $resolvedCount }}</p>
            </div>
            <div class="rounded-[24px] border border-[#EFEFEF] bg-[#F1F5F9] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Closed</p>
                <p class="mt-3 text-3xl font-black text-[#0F172A]">{{ $closedCount }}</p>
            </div>
        </div>
    </section>

        <section class="overflow-hidden rounded-[40px] border border-[#EFEFEF] bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-[#F2F1EE] bg-[#F1F5F9] px-8 py-7 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#EAB308]">Daftar Laporan</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight text-[#0F172A]">Daftar laporan.</h3>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#EFEFEF] bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A] shadow-sm">
                        <i data-lucide="message-square" class="h-4 w-4 text-[#EAB308]"></i>
                        {{ $currentIssues->count() }} laporan di halaman ini
                    </span>
                </div>
            </div>

            <div class="border-b border-[#F2F1EE] bg-white px-8 py-7">
                <form action="{{ route('admin.report-issues.index') }}" method="GET" class="grid gap-4 xl:grid-cols-[minmax(0,1fr),180px,200px,220px,auto,auto]">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-5 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8A8A8A]"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari subjek, isi laporan, nama, email, atau NIP..." class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] py-4 pl-12 pr-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                    </div>
                    <select name="status" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                        <option value="">Semua status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                    <select name="work_unit_id" class="w-full rounded-[22px] border border-[#EFEFEF] bg-[#F1F5F9] px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                        <option value="">Semua unit kerja</option>
                        @foreach($workUnits as $workUnit)
                            <option value="{{ $workUnit->id }}" {{ (string) request('work_unit_id') === (string) $workUnit->id ? 'selected' : '' }}>
                                {{ $workUnit->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center gap-3 rounded-[22px] bg-[#0F172A] px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white transition-all hover:bg-[#EAB308]">
                        Terapkan
                    </button>
                    @if($searchActive)
                        <a href="{{ route('admin.report-issues.index') }}" class="inline-flex items-center justify-center gap-3 rounded-[22px] border border-[#EFEFEF] bg-white px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-[#8A8A8A] transition-all hover:bg-[#F1F5F9]">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <div id="bulkActionBar" class="hidden border-b border-[#F2F1EE] bg-[#FFF5F4] px-8 py-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-[#EAB308] shadow-sm">
                            <i data-lucide="check-check" class="h-4 w-4"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#EAB308]">Bulk Action Aktif</p>
                            <p class="mt-1 text-sm font-bold text-[#0F172A]"><span id="selectedIssuesCount">0</span> laporan dipilih pada halaman ini.</p>
                        </div>
                    </div>
                    <button type="button" id="bulkDeleteBtn" class="inline-flex items-center justify-center gap-3 rounded-[22px] bg-red-500 px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-red-100 transition-all hover:bg-[#0F172A]">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        Bulk Delete
                    </button>
                </div>
            </div>

            <div class="divide-y divide-[#F2F1EE]">
                @forelse($issues as $issue)
                    @php
                        $employee = $issue->user?->employee;
                        $issueData = array_merge($issue->toArray(), [
                            'user' => $issue->user,
                            'employee' => $employee,
                        ]);
                    @endphp

                    <div class="issue-card px-8 py-7 hover:bg-[#F1F5F9]">
                        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                            <div class="flex min-w-0 flex-1 gap-4">
                                <div class="pt-1">
                                    <input type="checkbox" name="ids[]" value="{{ $issue->id }}" class="issue-checkbox h-5 w-5 rounded-lg border-[#D7D3CF] text-[#EAB308] focus:ring-0">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#EFEFEF] bg-white text-sm font-black text-[#0F172A] shadow-sm">
                                                {{ substr($issue->user->name ?? 'S', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-[#0F172A]">{{ $issue->user->name ?? 'User tidak tersedia' }}</p>
                                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">{{ $issue->user->email ?? '-' }}</p>
                                            </div>
                                        </div>

                                        <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] {{ $issue->status === 'open' ? 'border-red-100 bg-red-50 text-red-600' : ($issue->status === 'resolved' ? 'border-emerald-100 bg-emerald-50 text-emerald-600' : 'border-slate-200 bg-slate-50 text-slate-500') }}">
                                            {{ $issue->status }}
                                        </span>
                                    </div>

                                    <div class="mt-5">
                                        <h4 class="text-lg font-black tracking-tight text-[#0F172A]">{{ $issue->subject }}</h4>
                                        <p class="mt-3 max-w-3xl text-sm font-medium leading-relaxed text-[#8A8A8A]">{{ $issue->message }}</p>
                                    </div>

                                    <div class="mt-5 grid gap-3 text-sm font-medium text-[#8A8A8A] sm:grid-cols-3">
                                        <div class="rounded-[22px] border border-[#EFEFEF] bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Pengirim</p>
                                            <p class="mt-2 font-bold text-[#0F172A]">{{ $issue->user->name ?? 'User tidak tersedia' }}</p>
                                        </div>
                                        <div class="rounded-[22px] border border-[#EFEFEF] bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Waktu Kirim</p>
                                            <p class="mt-2 font-bold text-[#0F172A]">{{ $issue->created_at->format('d M Y, H:i') }}</p>
                                        </div>
                                        <div class="rounded-[22px] border border-[#EFEFEF] bg-white px-4 py-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Catatan Admin</p>
                                            <p class="mt-2 font-bold text-[#0F172A]">{{ $issue->admin_note ? 'Sudah diisi' : 'Belum ada tanggapan' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex shrink-0 gap-3 xl:flex-col">
                                <button type="button" onclick="openDetailModal({{ \Illuminate\Support\Js::from($issueData) }})" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm transition-all hover:bg-blue-600 hover:text-white">
                                    <i data-lucide="eye" class="h-4 w-4"></i>
                                </button>
                                <form id="deleteIssue-{{ $issue->id }}" action="{{ route('admin.report-issues.destroy', $issue->id) }}" method="POST" class="no-loader">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteIssue({{ $issue->id }})" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-red-100 bg-red-50 text-red-500 shadow-sm transition-all hover:bg-red-500 hover:text-white">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-8 py-20 text-center">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-[#F1F5F9] text-[#ABABAB]">
                            <i data-lucide="inbox" class="h-9 w-9"></i>
                        </div>
                        <p class="mt-5 text-sm font-black uppercase tracking-[0.22em] text-[#0F172A]">Belum ada laporan masuk</p>
                    </div>
                @endforelse
            </div>

            @if($currentIssues->isNotEmpty())
                <div class="border-t border-[#F2F1EE] bg-[#F1F5F9] px-8 py-5">
                    <label class="inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.22em] text-[#0F172A]">
                        <input type="checkbox" id="selectAllIssues" class="h-5 w-5 rounded-lg border-[#D7D3CF] text-[#EAB308] focus:ring-0">
                        Pilih semua laporan di halaman ini
                    </label>
                </div>
            @endif

            <div class="border-t border-[#F2F1EE] bg-[#F1F5F9] px-8 py-6">
                {{ $issues->links() }}
            </div>
        </section>
</div>

<form id="bulkDeleteForm" action="{{ route('admin.report-issues.bulk-destroy') }}" method="POST" class="hidden no-loader">
    @csrf
    @method('DELETE')
</form>

<form id="deleteAllIssuesForm" action="{{ route('admin.report-issues.destroy-all') }}" method="POST" class="hidden no-loader">
    @csrf
    @method('DELETE')
</form>

<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/55 p-6 backdrop-blur-md">
    <div class="w-full max-w-4xl overflow-hidden rounded-[44px] border border-[#EFEFEF] bg-white shadow-2xl">
        <div class="bg-[#0F172A] px-8 py-7 text-white">
            <div class="flex items-center justify-between gap-6">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-white/55">Detail Penanganan</p>
                    <h3 class="mt-2 text-2xl font-black tracking-tight">Buka konteks laporan dan kirim tanggapan admin.</h3>
                </div>
                <button type="button" onclick="document.getElementById('detailModal').classList.add('hidden')" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-white transition-all hover:bg-white/20">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
        </div>

        <form id="updateForm" method="POST" class="grid gap-8 p-8 lg:grid-cols-[320px,minmax(0,1fr)]">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="rounded-[32px] border border-[#EFEFEF] bg-[#F1F5F9] p-6 text-center">
                    <div id="detail_photo_container" class="mx-auto mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-[24px] border-2 border-white bg-white shadow-lg">
                        <i data-lucide="user" class="h-10 w-10 text-gray-300"></i>
                    </div>
                    <h4 id="detail_name" class="text-sm font-black text-[#0F172A]"></h4>
                    <p id="detail_nip" class="mt-1 text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]"></p>
                </div>

                <div class="space-y-4 rounded-[32px] border border-[#EFEFEF] bg-white p-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Email</p>
                        <p id="detail_email" class="mt-2 text-sm font-bold text-[#0F172A]"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Jabatan</p>
                        <p id="detail_position" class="mt-2 text-sm font-bold text-[#0F172A]"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#ABABAB]">Dikirim</p>
                        <p id="detail_date" class="mt-2 text-sm font-bold text-[#0F172A]"></p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-[32px] border border-[#EFEFEF] bg-[#F1F5F9] p-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#EAB308]">Subjek Laporan</p>
                    <h4 id="detail_subject" class="mt-3 text-xl font-black tracking-tight text-[#0F172A]"></h4>
                    <div id="detail_message" class="mt-5 rounded-[24px] border border-[#EFEFEF] bg-white px-5 py-5 text-sm font-medium leading-relaxed text-[#0F172A]"></div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Status Penanganan</label>
                        <select name="status" id="detail_status" class="mt-3 w-full rounded-[20px] border border-[#EFEFEF] bg-white px-5 py-4 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5">
                            <option value="open">Open</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="ml-1 block text-[10px] font-black uppercase tracking-[0.22em] text-[#8A8A8A]">Catatan Admin</label>
                    <textarea name="admin_note" id="detail_note" rows="5" class="mt-3 w-full rounded-[24px] border border-[#EFEFEF] bg-white px-6 py-5 text-sm font-bold text-[#0F172A] outline-none transition-all focus:border-[#EAB308] focus:ring-4 focus:ring-red-500/5" placeholder="Berikan tanggapan atau langkah tindak lanjut untuk laporan ini..."></textarea>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center gap-3 rounded-[24px] bg-[#EAB308] px-6 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-red-100 transition-all hover:bg-[#0F172A]">
                    Simpan Penanganan
                    <i data-lucide="save" class="h-4 w-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Perubahan Tersimpan',
            text: "{{ session('success') }}",
            confirmButtonColor: '#0F172A',
            customClass: { popup: 'rounded-[32px]' }
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Aksi Gagal',
            text: "{{ session('error') }}",
            confirmButtonColor: '#EAB308',
            customClass: { popup: 'rounded-[32px]' }
        });
    </script>
@endif

<script>
    const selectAllIssues = document.getElementById('selectAllIssues');
    const issueCheckboxes = Array.from(document.querySelectorAll('.issue-checkbox'));
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedIssuesCount = document.getElementById('selectedIssuesCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const deleteAllIssuesBtn = document.getElementById('deleteAllIssuesBtn');
    const deleteAllIssuesForm = document.getElementById('deleteAllIssuesForm');

    function syncIssueSelectionState() {
        const checkedCount = issueCheckboxes.filter((checkbox) => checkbox.checked).length;

        if (selectedIssuesCount) {
            selectedIssuesCount.textContent = checkedCount;
        }

        if (bulkActionBar) {
            bulkActionBar.classList.toggle('hidden', checkedCount === 0);
        }

        if (selectAllIssues) {
            selectAllIssues.checked = issueCheckboxes.length > 0 && checkedCount === issueCheckboxes.length;
            selectAllIssues.indeterminate = checkedCount > 0 && checkedCount < issueCheckboxes.length;
        }
    }

    if (selectAllIssues) {
        selectAllIssues.addEventListener('change', function () {
            issueCheckboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
            syncIssueSelectionState();
        });
    }

    issueCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncIssueSelectionState);
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedCount = issueCheckboxes.filter((checkbox) => checkbox.checked).length;

            Swal.fire({
                title: 'Bulk delete laporan?',
                text: `${checkedCount} laporan yang dipilih di halaman ini akan dihapus permanen.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EAB308',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Ya, hapus terpilih',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-[32px]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach((input) => input.remove());
                    issueCheckboxes.filter((checkbox) => checkbox.checked).forEach((checkbox) => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'ids[]';
                        hiddenInput.value = checkbox.value;
                        bulkDeleteForm.appendChild(hiddenInput);
                    });
                    bulkDeleteForm.submit();
                }
            });
        });
    }

    if (deleteAllIssuesBtn) {
        deleteAllIssuesBtn.addEventListener('click', function () {
            Swal.fire({
                title: 'Delete all laporan?',
                text: 'Semua laporan masalah di database akan dihapus permanen. Aksi ini tidak bisa dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EAB308',
                cancelButtonColor: '#0F172A',
                confirmButtonText: 'Ya, hapus semua',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-[32px]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteAllIssuesForm.submit();
                }
            });
        });
    }

    function confirmDeleteIssue(id) {
        Swal.fire({
            title: 'Hapus laporan ini?',
            text: 'Laporan yang dihapus tidak dapat dipulihkan kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EAB308',
            cancelButtonColor: '#0F172A',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[32px]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`deleteIssue-${id}`).submit();
            }
        });
    }

    function openDetailModal(data) {
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModal').classList.add('flex');
        document.getElementById('updateForm').action = `/admin/report-issues/${data.id}`;

        document.getElementById('detail_name').innerText = data.user.name;
        document.getElementById('detail_email').innerText = data.user.email;
        document.getElementById('detail_nip').innerText = data.employee ? `NIP. ${data.employee.nip}` : 'Data pegawai tidak ditemukan';
        document.getElementById('detail_position').innerText = data.employee ? data.employee.position : 'Tidak tersedia';
        document.getElementById('detail_date').innerText = new Date(data.created_at).toLocaleString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });

        const photoContainer = document.getElementById('detail_photo_container');
        if (data.employee && data.employee.photo) {
            photoContainer.innerHTML = `<img src="${data.employee.photo}" class="h-full w-full object-cover">`;
        } else {
            photoContainer.innerHTML = '<i data-lucide="user" class="h-10 w-10 text-gray-300"></i>';
        }

        document.getElementById('detail_subject').innerText = data.subject;
        document.getElementById('detail_message').innerText = data.message;
        document.getElementById('detail_status').value = data.status;
        document.getElementById('detail_note').value = data.admin_note || '';

        lucide.createIcons();
    }

    syncIssueSelectionState();
</script>
@endsection
