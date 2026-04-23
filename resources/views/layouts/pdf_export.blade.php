<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laporan Sinergi PAS')</title>
    <style>
        @page { margin: 0.5cm 1cm; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9px; 
            color: #000; 
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        
        .header-container {
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .logo-center {
            margin: 0 auto;
            width: 50px;
            height: auto;
        }
        
        .kop-text h1 { margin: 0; font-size: 10px; font-weight: bold; }
        .kop-text h2 { margin: 1px 0; font-size: 12px; font-weight: bold; color: #1e40af; }
        .kop-text p { margin: 0; font-size: 7px; color: #333; }

        .report-title-box {
            text-align: center;
            margin: 10px 0;
            padding: 5px;
            background: #eee;
        }

        .report-title-box h3 {
            margin: 0;
            font-size: 11px;
            font-weight: bold;
            text-decoration: underline;
        }

        .report-meta { font-size: 7px; margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; }
        .main-table th { 
            background-color: #333; 
            color: #fff; 
            padding: 4px; 
            font-size: 7px;
            border: 1px solid #000;
        }
        .main-table td { 
            border: 1px solid #ccc; 
            padding: 3px; 
            font-size: 7px;
        }

        .footer { margin-top: 20px; width: 100%; }
        .signature-table { width: 100%; border: none; }
        .signature-table td { width: 50%; border: none; }
        .signature-space { height: 40px; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header-container">
        @php
            try {
                $logoPath = public_path('logo1.png');
                if (file_exists($logoPath)) {
                    echo '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) . '" class="logo-center">';
                }
            } catch (\Exception $e) {}
        @endphp
        <div class="kop-text">
            <h1>{{ \App\Models\Setting::getValue('kop_line_1', 'KEMENTERIAN HUKUM DAN HAK ASASI MANUSIA RI') }}</h1>
            <h2>{{ \App\Models\Setting::getValue('kop_line_2', 'LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG') }}</h2>
            <p>{{ \App\Models\Setting::getValue('kop_address', 'Jl. KH. Wahid Hasyim No. 151, Jombang, Jawa Timur 61411') }}</p>
            <p>Telepon: (0321) 861054 | Email: lpjombang@gmail.com</p>
        </div>
    </div>

    <div class="report-title-box">
        <h3>@yield('report_title')</h3>
        <div class="report-meta">@yield('report_meta')</div>
    </div>

    @yield('content')

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>@yield('footer_left')</td>
                <td class="text-right">
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
        Sinergi PAS Platform - {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
