<!DOCTYPE html>
<html>
<head>
    <title>Slip Tunkin - {{ $employee->full_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #334155; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
        .logo { position: absolute; left: 0; top: 0; width: 65px; }
        .kop-1 { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
        .kop-2 { font-size: 17px; font-weight: bold; color: #1e40af; margin-bottom: 2px; }
        .kop-3 { font-size: 10px; font-style: italic; }
        .title { text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 30px; text-decoration: underline; }
        
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .info-label { width: 120px; font-weight: bold; }
        .info-separator { width: 20px; text-align: center; }
        
        .payroll-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .payroll-table th { background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 12px 15px; text-align: left; font-size: 10px; }
        .payroll-table td { border: 1px solid #cbd5e1; padding: 12px 15px; }
        
        .total-box { background-color: #1e293b; color: #ffffff; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .total-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; opacity: 0.8; }
        .total-amount { font-size: 20px; font-weight: bold; }
        
        .footer { margin-top: 50px; }
        .signature { float: right; width: 250px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo">
        @endif
        <div class="kop-1">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN RI</div>
        <div class="kop-2">LEMBAGA PEMASYARAKATAN KELAS IIB JOMBANG</div>
        <div class="kop-3">Jl. KH. Wahid Hasyim No. 151, Jombang | Telp: (0321) 861114</div>
    </div>

    <div class="title">SLIP PENGHASILAN PEGAWAI (ESTIMASI)</div>

    <table class="info-table">
        <tr>
            <td class="info-label">Nama Pegawai</td>
            <td class="info-separator">:</td>
            <td><strong>{{ $employee->full_name }}</strong></td>
            <td class="info-label">Periode</td>
            <td class="info-separator">:</td>
            <td>{{ $date->translatedFormat('F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">NIP</td>
            <td class="info-separator">:</td>
            <td>{{ $employee->nip }}</td>
            <td class="info-label">Kelas Jabatan</td>
            <td class="info-separator">:</td>
            <td>Grade {{ $employee->tunkin->grade ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jabatan</td>
            <td class="info-separator">:</td>
            <td>{{ $employee->position }}</td>
            <td class="info-label">Unit Kerja</td>
            <td class="info-separator">:</td>
            <td>{{ $employee->work_unit->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="payroll-table">
        <thead>
            <tr>
                <th width="70%">KOMPONEN PENGHASILAN</th>
                <th width="30%" style="text-align: right;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Tunjangan Kinerja Dasar</strong>
                    @if($employee->is_cpns)
                        <span style="font-size: 8px; background: #000; color: #fff; padding: 1px 4px; border-radius: 3px; margin-left: 5px;">CPNS 80%</span>
                    @endif
                    <br>
                    <small style="color: #64748b;">
                        Besaran standar sesuai Kelas Jabatan {{ $employee->tunkin->grade }}
                        @if($employee->is_cpns)
                            (Pagu 80% dari Rp {{ number_format($employee->tunkin->nominal ?? 0, 0, ',', '.') }})
                        @endif
                    </small>
                </td>
                <td style="text-align: right;">{{ number_format($baseTunkin, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>
                    <strong>Uang Makan</strong><br>
                    <small style="color: #64748b;">Kehadiran: {{ $attendances }} Hari &times; Rp {{ number_format($mealAllowancePerDay, 0, ',', '.') }}</small>
                </td>
                <td style="text-align: right;">{{ number_format($totalMealAllowance, 0, ',', '.') }}</td>
            </tr>
            <tr style="color: #ef4444;">
                <td>
                    <strong>Total Potongan Absensi</strong><br>
                    <small>Berdasarkan tingkat keterlambatan dan ketidakhadiran</small>
                </td>
                <td style="text-align: right;">- {{ number_format($potongan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-label">Take Home Pay (THP)</div>
        <div class="total-amount">Rp {{ number_format($totalTerima, 0, ',', '.') }}</div>
    </div>

    <div class="footer">
        <div style="float: left; font-size: 9px; color: #94a3b8; width: 300px; margin-top: 100px;">
            * Dokumen ini dihasilkan secara otomatis oleh sistem Sinergi PAS Lapas Jombang.<br>
            * Segala bentuk manipulasi data dalam dokumen ini merupakan pelanggaran hukum.
        </div>
        <div class="signature">
            Jombang, {{ now()->translatedFormat('d F Y') }}<br>
            Bendahara Pengeluaran,<br><br><br><br><br>
            <strong>__________________________</strong><br>
            NIP. .........................
        </div>
    </div>
</body>
</html>
