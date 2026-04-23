@extends('layouts.pdf_export')

@section('title', $reportTitle)
@section('report_title', 'REKAPITULASI KEHADIRAN')
@section('report_meta')
    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | Unit: {{ $workUnit ? $workUnit->name : 'SEMUA' }}
@endsection

@section('content')
    <style>
        .main-table th { background: #333; color: #fff; padding: 5px; font-size: 8px; }
        .main-table td { padding: 4px; font-size: 8px; border: 1px solid #ccc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .total-row { background: #eee; font-weight: bold; }
    </style>

    <table class="main-table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>NAMA PEGAWAI</th>
                <th width="100">NIP</th>
                <th width="50" class="text-center">HADIR</th>
                <th width="50" class="text-center">TELAT</th>
                <th width="50" class="text-center">ABSEN</th>
                <th width="100" class="text-center">UANG MAKAN</th>
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
                <td class="text-center" style="color: #b91c1c;">{{ $log->absent_count }}</td>
                <td class="text-right font-bold">Rp {{ number_format($log->total_allowance, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ $logs->sum('present_count') }}</td>
                <td class="text-center">{{ $logs->sum('late_count') }}</td>
                <td class="text-center">{{ $logs->sum('absent_count') }}</td>
                <td class="text-right">Rp {{ number_format($logs->sum('total_allowance'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
