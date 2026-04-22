@extends('layouts.pdf_export')

@section('title', $reportTitle)
@section('report_title', 'LAPORAN KEHADIRAN INDIVIDU PEGAWAI')
@section('report_meta')
    Nama: {{ strtoupper($emp->full_name) }} | NIP: {{ $emp->nip }} | Periode: {{ strtoupper(\Carbon\Carbon::parse($logs->first()?->date ?? now())->translatedFormat('F Y')) }}
@endsection

@section('content')
    <table class="main-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">NO</th>
                <th width="15%">TANGGAL</th>
                <th width="15%">HARI</th>
                <th width="15%" class="text-center">MASUK</th>
                <th width="15%" class="text-center">PULANG</th>
                <th width="15%" class="text-center">TELAT (M)</th>
                <th width="20%" class="text-center">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l') }}</td>
                <td class="text-center">{{ $log->check_in ? \Carbon\Carbon::parse($log->check_in)->format('H:i') : '--:--' }}</td>
                <td class="text-center">{{ $log->check_out && $log->check_out != $log->check_in ? \Carbon\Carbon::parse($log->check_out)->format('H:i') : '--:--' }}</td>
                <td class="text-center">{{ $log->late_minutes > 0 ? $log->late_minutes : '-' }}</td>
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

    <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 10px; background-color: #f8fafc;">
        <h4 style="margin: 0 0 10px 0; color: #1e40af; font-size: 10px; text-transform: uppercase;">Ringkasan Kehadiran</h4>
        <table style="width: 100%; font-size: 9px;">
            <tr>
                <td>Total Hari Hadir</td><td>: <strong>{{ $logs->where('status', '!=', 'absent')->count() }} Hari</strong></td>
                <td>Total Menit Terlambat</td><td>: <strong>{{ $logs->sum('late_minutes') }} Menit</strong></td>
            </tr>
            <tr>
                <td>Estimasi Uang Makan</td><td colspan="3">: <strong>Rp {{ number_format($logs->sum('allowance_amount'), 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>
@endsection
