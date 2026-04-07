<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo { position: absolute; left: 0; top: 0; height: 70px; }
        .kop { margin-left: 80px; }
        .kop h2 { margin: 0; font-size: 14px; }
        .kop h1 { margin: 5px 0; font-size: 16px; }
        .title { text-align: center; text-decoration: underline; font-size: 14px; font-weight: bold; margin-bottom: 20px; }
        .info-table { margin-bottom: 20px; }
        .info-table td { padding: 3px 5px; font-weight: bold; }
        table.main { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.main th, table.main td { border: 1px solid #000; padding: 8px; text-align: center; }
        table.main th { background-color: #f1f5f9; }
        .summary-box { border: 1px solid #ddd; padding: 15px; background: #fafafa; border-radius: 10px; margin-top: 20px; }
        .footer { margin-top: 50px; text-align: right; }
        .footer-space { height: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logo1.png') }}" class="logo">
        <div class="kop">
            <h2>KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI</h2>
            <h1>LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG</h1>
            <p style="margin:0; font-size: 10px;">Jl. KH. Wahid Hasyim No.151, Jombang, Jawa Timur</p>
        </div>
    </div>

    <div class="title">LAPORAN KEHADIRAN INDIVIDU PEGAWAI</div>

    <table class="info-table">
        <tr><td>NAMA LENGKAP</td><td>: {{ strtoupper($emp->full_name) }}</td></tr>
        <tr><td>NIP</td><td>: {{ $emp->nip }}</td></tr>
        <tr><td>JABATAN</td><td>: {{ strtoupper($emp->position) }}</td></tr>
        <tr><td>PERIODE</td><td>: {{ strtoupper($date->translatedFormat('F Y')) }}</td></tr>
    </table>

    <table class="main">
        <thead>
            <tr>
                <th>NO</th>
                <th>TANGGAL</th>
                <th>HARI</th>
                <th>MASUK</th>
                <th>PULANG</th>
                <th>TELAT (MENIT)</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l') }}</td>
                <td>{{ $log->check_in ? \Carbon\Carbon::parse($log->check_in)->format('H:i') : '--:--' }}</td>
                <td>{{ $log->check_out && $log->check_out != $log->check_in ? \Carbon\Carbon::parse($log->check_out)->format('H:i') : '--:--' }}</td>
                <td>{{ $log->late_minutes > 0 ? $log->late_minutes : '-' }}</td>
                <td style="font-weight: bold; color: {{ $log->status == 'late' ? '#b45309' : ($log->status == 'present' ? '#15803d' : '#dc2626') }}">
                    {{ strtoupper($log->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3 style="margin-top:0;">RINGKASAN BULANAN</h3>
        <table style="width: 100%;">
            <tr>
                <td>Total Hari Hadir</td><td>: {{ $logs->where('status', '!=', 'absent')->count() }} Hari</td>
                <td>Total Terlambat</td><td>: {{ $logs->sum('late_minutes') }} Menit</td>
            </tr>
            <tr>
                <td>Total Uang Makan</td><td colspan="3">: Rp {{ number_format($logs->sum('allowance_amount'), 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Jombang, {{ date('d F Y') }}</p>
        <p>Mengetahui,</p>
        <p>Kepala Lapas Jombang</p>
        <div class="footer-space"></div>
        <p>__________________________</p>
    </div>
</body>
</html>
