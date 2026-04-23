@extends('layouts.pdf_export')

@section('title', $reportTitle)
@section('report_title', 'KEHADIRAN HARIAN')
@section('report_meta')
    Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }} | Unit: {{ $workUnit ? $workUnit->name : 'SEMUA' }}
@endsection

@section('content')
    <style>
        .main-table th { background: #333; color: #fff; padding: 5px; font-size: 8px; }
        .main-table td { padding: 4px; font-size: 8px; border: 1px solid #ccc; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>

    <table class="main-table">
        <thead>
            <tr>
                <th width="30" class="text-center">NO</th>
                <th>NAMA PEGAWAI</th>
                <th width="100">NIP</th>
                <th width="60" class="text-center">MASUK</th>
                <th width="60" class="text-center">PULANG</th>
                <th width="40" class="text-center">TELAT</th>
                <th width="80" class="text-center">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $log->employee->full_name }}</td>
                <td>{{ $log->employee->nip }}</td>
                <td class="text-center">{{ $log->check_in ? \Carbon\Carbon::parse($log->check_in)->format('H:i') : '--:--' }}</td>
                <td class="text-center">{{ $log->check_out && $log->check_out != $log->check_in ? \Carbon\Carbon::parse($log->check_out)->format('H:i') : '--:--' }}</td>
                <td class="text-center">{{ $log->late_minutes > 0 ? $log->late_minutes . 'm' : '-' }}</td>
                <td class="text-center">
                    {{ strtoupper($log->status === 'present' ? 'HADIR' : ($log->status === 'late' ? 'TERLAMBAT' : ($log->status === 'absent' ? 'ABSEN' : $log->status))) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
