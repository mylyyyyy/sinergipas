<!DOCTYPE html>
<html>
<head>
    <title>Laporan Dashboard Sinergi PAS</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        
        /* Kop Surat Styles */
        .kop { 
            position: relative;
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #0f172a; 
            padding-bottom: 15px; 
        }
        .kop-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 70px;
            height: auto;
        }
        .kop-text {
            padding-left: 20px;
            padding-right: 20px;
        }
        .kop h1 { 
            margin: 0; 
            font-size: 14px; 
            font-weight: bold; 
            text-transform: uppercase;
            color: #0f172a; 
            letter-spacing: 0.5px;
        }
        .kop h2 { 
            margin: 2px 0; 
            font-size: 16px; 
            font-weight: 800; 
            text-transform: uppercase;
            color: #0f172a;
        }
        .kop p { 
            margin: 2px 0; 
            font-size: 9px; 
            color: #64748b; 
        }
        
        .report-title {
            text-align: center;
            margin: 30px 0;
        }
        .report-title h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .report-meta {
            text-align: center;
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
        }

        .section-header { 
            background: #f1f5f9; 
            color: #0f172a; 
            padding: 8px 12px; 
            font-weight: bold; 
            font-size: 12px;
            margin-top: 20px; 
            text-transform: uppercase; 
            border-left: 4px solid #0f172a;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px 12px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #475569; width: 45%; }
        td { color: #1e293b; font-weight: 600; }
        
        .footer { 
            margin-top: 60px; 
            width: 100%;
        }
        .footer-table { width: 100%; border: none; }
        .footer-table td { border: none; padding: 0; text-align: right; font-weight: normal; }
        .signature-space { height: 80px; }
    </style>
</head>
<body>
    <div class="kop">
        @php
            $logoPath = public_path('logo1.png');
            $logoData = '';
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
            }
        @endphp
        @if($logoData)
            <img src="data:image/png;base64,{{ $logoData }}" class="kop-logo">
        @endif
        <div class="kop-text">
            <h1>{{ \App\Models\Setting::getValue('kop_line_1', 'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI') }}</h1>
            <h2>{{ \App\Models\Setting::getValue('kop_line_2', 'LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG') }}</h2>
            <p>{{ \App\Models\Setting::getValue('kop_address', 'Jl. KH. Wahid Hasyim No. 123, Jombang, Jawa Timur 61411') }}</p>
        </div>
    </div>

    <div class="report-title">
        <h3>LAPORAN RINGKASAN EKSEKUTIF</h3>
        <div class="report-meta">
            ID Laporan: #{{ time() }} | Periode: {{ date('F Y') }}
        </div>
    </div>

    <div class="section-header">Indikator Kinerja Utama</div>
    <table>
        <tr>
            <th>Total Sumber Daya Manusia (Pegawai)</th>
            <td>{{ $totalEmployees }} Orang</td>
        </tr>
        <tr>
            <th>Volume Arsip Digital Tersimpan</th>
            <td>{{ $totalDocuments }} Berkas</td>
        </tr>
        <tr>
            <th>Aktivitas Dokumen Hari Ini</th>
            <td>{{ $docsToday }} Berkas Baru</td>
        </tr>
        <tr>
            <th>Berkas Menunggu Verifikasi Admin</th>
            <td>{{ $pendingDocs }} Berkas</td>
        </tr>
        <tr>
            <th>Total Laporan Masalah / Helpdesk</th>
            <td>{{ $openIssues }} Laporan Aktif</td>
        </tr>
        <tr>
            <th>Utilisasi Penyimpanan Server</th>
            <td>{{ number_format($storageUsed, 2) }} MB Terpakai</td>
        </tr>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    <p>Jombang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p><strong>Administrator Sistem Sinergi PAS</strong></p>
                    <div class="signature-space"></div>
                    <p>__________________________</p>
                    <p style="font-size: 8px; color: #94a3b8; margin-top: 5px;">Generated automatically by Sinergi PAS Platform</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
