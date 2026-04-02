<!DOCTYPE html>
<html>
<head>
    <title>Laporan Dashboard Sinergi PAS</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .kop { text-align: center; margin-bottom: 30px; border-bottom: 3px double #1E2432; padding-bottom: 15px; }
        .kop h1 { margin: 0; font-size: 18px; font-weight: bold; color: #1E2432; }
        .kop h2 { margin: 2px 0; font-size: 14px; font-weight: normal; }
        .kop p { margin: 2px 0; font-size: 10px; font-style: italic; color: #666; }
        
        .section-title { background: #1E2432; color: white; padding: 10px; font-weight: bold; margin-top: 20px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #fcfcfc; font-weight: bold; width: 40%; }
        
        .summary-grid { margin-top: 20px; }
        .metric-box { border: 1px solid #eee; padding: 15px; margin-bottom: 10px; }
        .metric-label { font-size: 10px; color: #888; text-transform: uppercase; font-weight: bold; }
        .metric-value { font-size: 20px; font-weight: bold; color: #1E2432; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>{{ \App\Models\Setting::getValue('kop_line_1', 'LEMBAGA PEMASYARAKATAN JOMBANG') }}</h1>
        <h2>{{ \App\Models\Setting::getValue('kop_line_2', 'KANTOR WILAYAH KEMENTERIAN HUKUM DAN HAM JAWA TIMUR') }}</h2>
        <p>{{ \App\Models\Setting::getValue('kop_address', 'Jl. KH. Wahid Hasyim No. 123, Jombang') }}</p>
    </div>

    <h2 style="text-align: center;">LAPORAN RINGKASAN SISTEM</h2>
    <p style="text-align: center; font-size: 10px; color: #888;">Periode Laporan: {{ date('F Y') }} | Dicetak pada: {{ date('d M Y, H:i') }}</p>

    <div class="section-title">Statistik Utama</div>
    <table>
        <tr>
            <th>Total Pegawai Terdaftar</th>
            <td>{{ $totalEmployees }} Orang</td>
        </tr>
        <tr>
            <th>Total Dokumen Digital</th>
            <td>{{ $totalDocuments }} File</td>
        </tr>
        <tr>
            <th>Dokumen Baru Hari Ini</th>
            <td>{{ $docsToday }} File</td>
        </tr>
        <tr>
            <th>Antrean Verifikasi</th>
            <td>{{ $pendingDocs }} File</td>
        </tr>
        <tr>
            <th>Laporan Masalah Aktif</th>
            <td>{{ $openIssues }} Laporan</td>
        </tr>
        <tr>
            <th>Penggunaan Penyimpanan Server</th>
            <td>{{ $storageUsed }} MB</td>
        </tr>
    </table>

    <div class="footer" style="margin-top: 50px; text-align: right;">
        <p>Jombang, {{ date('d F Y') }}</p>
        <br><br><br>
        <p><strong>ADMINISTRATOR SISTEM</strong></p>
        <p style="font-size: 9px; color: #aaa;">Sinergi PAS - Internal Registry Report</p>
    </div>
</body>
</html>
