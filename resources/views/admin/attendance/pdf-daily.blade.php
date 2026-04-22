@extends('layouts.pdf_export')

@section('title', $reportTitle)
@section('report_title', 'LAPORAN KEHADIRAN HARIAN PEGAWAI')
@section('report_meta')
    Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }} | Unit Kerja: {{ $workUnit ? $workUnit->name : 'SELURUH UNIT' }}
@endsection

@section('content')
    <table class="main-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">NO</th>
                <th width="25%">NAMA LENGKAP</th>
                <th width="15%">NIP</th>
                <th width="15%" class="text-center">MASUK</th>
                <th width="15%" class="text-center">PULANG</th>
                <th width="10%" class="text-center">TELAT</th>
                <th width="15%" class="text-center">STATUS</th>
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
                    @if($log->status == 'late')
                        <span class="badge badge-warning">TERLAMBAT</span>
                    @elseif($log->status == 'present')
                        <span class="badge badge-success">HADIR</span>
                    @else
                        <span class="badge badge-danger">ABSEN</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <table style="width: 250px; font-size: 9px; border-collapse: collapse;">
            <tr>
                <td colspan="2" style="padding-bottom: 5px;"><strong>STATISTIK HARI INI:</strong></td>
            </tr>
            <tr>
                <td>Total Pegawai</td><td>: {{ $logs->count() }} Orang</td>
            </tr>
            <tr>
                <td>Hadir Tepat Waktu</td><td>: {{ $logs->where('status', 'present')->count() }}</td>
            </tr>
            <tr>
                <td>Terlambat</td><td>: {{ $logs->where('status', 'late')->count() }}</td>
            </tr>
            <tr>
                <td>Tanpa Keterangan</td><td>: {{ $logs->where('status', 'absent')->count() }}</td>
            </tr>
        </table>
    </div>
@endsection
