<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laporan Sinergi PAS')</title>
    <style>
        @page { margin: 1cm 1.5cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #1e293b; 
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .header-container {
            border-bottom: 3px double #0f172a;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .logo-center {
            display: block;
            margin: 0 auto 10px auto;
            width: 65px;
            height: auto;
        }
        
        .kop-text h1 { 
            margin: 0; 
            font-size: 12px; 
            font-weight: bold; 
            text-transform: uppercase;
            color: #0f172a; 
            letter-spacing: 1px;
        }
        
        .kop-text h2 { 
            margin: 2px 0; 
            font-size: 16px; 
            font-weight: 900; 
            text-transform: uppercase;
            color: #1e40af;
        }
        
        .kop-text p { 
            margin: 1px 0; 
            font-size: 9px; 
            color: #475569; 
            font-weight: 500;
        }

        .report-title-box {
            text-align: center;
            margin: 20px 0;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 8px;
        }

        .report-title-box h3 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .report-meta {
            font-size: 8px;
            color: #64748b;
            margin-top: 4px;
            font-weight: bold;
        }

        table.main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        table.main-table th { 
            background-color: #1e293b; 
            color: #ffffff; 
            padding: 8px 10px; 
            text-align: left; 
            text-transform: uppercase;
            font-size: 8px;
            border: 1px solid #0f172a;
        }
        table.main-table td { 
            border: 1px solid #cbd5e1; 
            padding: 7px 10px; 
            vertical-align: middle;
            font-size: 9px;
        }
        table.main-table tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-info { background: #e0f2fe; color: #0369a1; }

        .footer { 
            margin-top: 35px;
            width: 100%;
        }
        .signature-table { 
            width: 100%; 
            border: none; 
        }
        .signature-table td { 
            border: none; 
            padding: 0; 
            width: 50%;
        }
        .sig-right { text-align: right; }
        .sig-left { text-align: left; }
        .signature-space { height: 60px; }
        
        .generated-at {
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            margin-top: 30px;
            padding-top: 5px;
            text-align: center;
        }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header-container">
        @php
            $logoPath = public_path('logo1.png');
            $logoData = '';
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
            }
        @endphp
        @if($logoData)
            <img src="data:image/png;base64,{{ $logoData }}" class="logo-center">
        @endif
        <div class="kop-text">
            <h1>{{ \App\Models\Setting::getValue('kop_line_1', 'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI') }}</h1>
            <h2>{{ \App\Models\Setting::getValue('kop_line_2', 'LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG') }}</h2>
            <p>{{ \App\Models\Setting::getValue('kop_address', 'Jl. KH. Wahid Hasyim No. 151, Jombang, Jawa Timur 61411') }}</p>
            <p>Telepon: (0321) 861054 | Email: lpjombang@gmail.com</p>
        </div>
    </div>

    <div class="report-title-box">
        <h3>@yield('report_title')</h3>
        <div class="report-meta">
            @yield('report_meta')
        </div>
    </div>

    @yield('content')

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td class="sig-left">
                    @yield('footer_left')
                </td>
                <td class="sig-right">
                    <p>Jombang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p><strong>@yield('signer_title', 'Kepala Lembaga Pemasyarakatan')</strong></p>
                    <div class="signature-space"></div>
                    <p><strong>@yield('signer_name', '__________________________')</strong></p>
                    <p>NIP. @yield('signer_nip', '..........................')</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="generated-at">
        Dokumen ini dibuat otomatis oleh Sistem Sinergi PAS - Lapas Kelas IIB Jombang pada {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
