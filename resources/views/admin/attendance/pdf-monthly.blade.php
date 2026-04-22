@extends('layouts.pdf_export')

@section('title', $reportTitle)
@section('report_title', 'REKAPITULASI KEHADIRAN BULANAN')
@section('report_meta')
    Bulan: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('F Y') }} | Unit Kerja: {{ $workUnit ? $workUnit->name : 'SELURUH UNIT' }}
@endsection

@section('content')
    <table class="main-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">NO</th>
                <th width="25%">NAMA PEGAWAI</th>
                <th width="15%">NIP</th>
                <th width="10%" class="text-center">HADIR</th>
                <th width="10%" class="text-center">TELAT</th>
                <th width="10%" class="text-center">ABSEN</th>
                <th width="25%" class="text-center">ESTIMASI UANG MAKAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $log->full_name }}</td>
                <td>{{ $log->nip }}</td>
                <td class="text-center">{{ $log->present_count }}</td>
                <td class="text-center">{{ $log->late_count }}</td>
                <td class="text-center text-red-600">{{ $log->absent_count }}</td>
                <td class="text-right font-bold">Rp {{ number_format($log->total_allowance, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #1e293b; color: white; font-weight: bold;">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ $logs->sum('present_count') }}</td>
                <td class="text-center">{{ $logs->sum('late_count') }}</td>
                <td class="text-center">{{ $logs->sum('absent_count') }}</td>
                <td class="text-right">Rp {{ number_format($logs->sum('total_allowance'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 8px; color: #64748b; font-style: italic;">
        * Rekapitulasi ini dihitung berdasarkan data fingerprint yang masuk ke sistem Sinergi PAS.
    </div>
@endsection
