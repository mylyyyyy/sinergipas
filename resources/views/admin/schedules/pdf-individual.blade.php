@extends('layouts.pdf_export')

@section('title', 'Jadwal Piket Individu')

@section('report_title', 'LAPORAN PENUGASAN PIKET INDIVIDU')
@section('report_meta', 'Periode: ' . strtoupper($date->translatedFormat('F Y')))

@section('content')
    <style>
        .main-table th { background: #333; color: #fff; padding: 6px; font-size: 8px; }
        .main-table td { padding: 4px; font-size: 8px; border: 1px solid #ccc; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .shift-label { font-weight: bold; text-transform: uppercase; font-size: 7px; }
    </style>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th style="width: 100px;">HARI / TANGGAL</th>
                <th>NAMA PEGAWAI</th>
                <th style="width: 120px;">NIP</th>
                <th style="width: 80px;">SHIFT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $index => $s)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($s->date)->translatedFormat('l') }}, 
                        <span class="font-bold">{{ \Carbon\Carbon::parse($s->date)->format('d/m/Y') }}</span>
                    </td>
                    <td class="font-bold">{{ strtoupper($s->employee->full_name) }}</td>
                    <td class="text-center">{{ $s->employee->nip }}</td>
                    <td class="text-center">
                        <span class="shift-label">{{ $s->shift->name }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 30px; color: #94a3b8;">Belum ada data penugasan individu untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 25px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60%; vertical-align: top; text-align: left; border: none; padding: 0;">
                    <div style="font-size: 9px; font-weight: bold; color: #0f172a; margin-bottom: 5px; text-decoration: underline;">KETERANGAN JADWAL:</div>
                    <table style="font-size: 8px; color: #475569; border: none;">
                        <tr>
                            <td style="border: none; padding: 2px 0; text-align: left; width: 100px;">1. Jenis Penugasan</td>
                            <td style="border: none; padding: 2px 0; text-align: left;">: Piket / Plot Individu (Luar Regu Jaga Utama)</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; text-align: left;">2. Kehadiran</td>
                            <td style="border: none; padding: 2px 0; text-align: left;">: Wajib hadir tepat waktu sesuai shift yang ditentukan</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; text-align: left;">3. Perubahan</td>
                            <td style="border: none; padding: 2px 0; text-align: left;">: Perubahan jadwal wajib melalui persetujuan atasan</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 2px 0; text-align: left;">4. Catatan</td>
                            <td style="border: none; padding: 2px 0; text-align: left;">: Melaksanakan tugas sesuai tupoksi penugasan piket</td>
                        </tr>
                    </table>
                </td>
                <td style="border: none;"></td>
            </tr>
        </table>
    </div>
@endsection

@section('footer_left')
    <div style="font-size: 8px; color: #64748b; margin-top: 10px;">
        * Dokumen ini digenerate secara otomatis melalui Sistem Sinergi PAS Jombang.
    </div>
@endsection

@section('signer_title', 'Kepala Kesatuan Pengamanan')
