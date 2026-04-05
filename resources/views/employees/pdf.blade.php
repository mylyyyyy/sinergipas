<!DOCTYPE html>
<html>
<head>
    <title>Daftar Pegawai Lapas Jombang</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        
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
            width: 60px;
            height: auto;
        }
        .kop h1 { 
            margin: 0; 
            font-size: 13px; 
            font-weight: bold; 
            text-transform: uppercase;
            color: #0f172a; 
        }
        .kop h2 { 
            margin: 2px 0; 
            font-size: 15px; 
            font-weight: 800; 
            text-transform: uppercase;
            color: #0f172a;
        }
        .kop p { 
            margin: 2px 0; 
            font-size: 8px; 
            color: #64748b; 
        }
        
        .title { text-align: center; font-size: 14px; font-weight: bold; margin: 25px 0; text-transform: uppercase; text-decoration: underline; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #475569; text-transform: uppercase; font-size: 8px; }
        
        .footer { margin-top: 40px; }
        .footer-table { width: 100%; border: none; }
        .footer-table td { border: none; padding: 0; text-align: right; }
        .signature-space { height: 60px; }
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

    <div class="title">DAFTAR NOMINATIF PEGAWAI</div>

    <table>
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="15%">NIP</th>
                <th width="30%">NAMA LENGKAP</th>
                <th width="25%">JABATAN</th>
                <th width="27%">UNIT KERJA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $e)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $e->nip }}</td>
                <td style="font-weight: bold;">{{ $e->full_name }}</td>
                <td>{{ $e->position }}</td>
                <td>{{ $e->work_unit->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    <p style="font-size: 9px;">Jombang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="font-weight: bold; font-size: 9px;">ADMINISTRASI KEPEGAWAIAN</p>
                    <div class="signature-space"></div>
                    <p>__________________________</p>
                    <p style="font-size: 7px; color: #94a3b8;">Sinergi PAS Digital Registry System</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
