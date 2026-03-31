@extends('layouts.app')

@section('title', 'Dokumen Saya')
@section('header-title', 'Daftar Dokumen Pribadi')

@section('content')
<div class="bg-white rounded-3xl border border-[#EFEFEF] shadow-sm overflow-hidden">
    <div class="p-8 border-b border-[#EFEFEF]">
        <h3 class="text-lg font-bold text-[#1E2432]">Dokumen Anda</h3>
        <p class="text-sm text-[#8A8A8A]">Klik tombol unduh untuk melihat detail dokumen.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FCFBF9]">
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Kategori</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Judul Dokumen</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider">Tanggal Terbit</th>
                    <th class="px-8 py-4 text-xs font-bold text-[#8A8A8A] uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EFEFEF]">
                @foreach($documents as $doc)
                <tr class="hover:bg-[#FCFBF9] transition-all">
                    <td class="px-8 py-5">
                        <span class="px-3 py-1 bg-red-50 text-[#E85A4F] text-[10px] font-bold rounded-full uppercase">{{ $doc->category->name }}</span>
                    </td>
                    <td class="px-8 py-5 text-sm font-semibold text-[#1E2432]">{{ $doc->title }}</td>
                    <td class="px-8 py-5 text-sm text-[#8A8A8A]">{{ $doc->created_at->format('d M Y') }}</td>
                    <td class="px-8 py-5 text-sm text-center">
                        <a href="{{ route('documents.download', $doc->id) }}" class="inline-flex items-center gap-2 bg-[#E85A4F] text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-[#d44d42] transition-all">
                            <i data-lucide="download" class="w-3 h-3"></i>
                            Unduh File
                        </a>
                    </td>
                </tr>
                @endforeach
                @if($documents->isEmpty())
                <tr>
                    <td colspan="4" class="px-8 py-10 text-center text-[#8A8A8A] text-sm italic">Belum ada dokumen untuk Anda saat ini.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
