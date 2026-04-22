@extends('layouts.pdf_export')

@section('title', 'Laporan Operasional Sinergi PAS')
@section('report_title', 'LAPORAN OPERASIONAL & ANALITIK SISTEM')
@section('report_meta', 'Periode: ' . date('d F Y'))

@section('content')
    <div style="margin-bottom: 20px;">
        <h4 style="border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #1e40af; text-transform: uppercase; font-size: 10px;">Ringkasan Statistik</h4>
        <table class="main-table">
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Nilai Data</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Personel Kepegawaian</td>
                    <td class="font-bold">{{ $totalEmployees }} Orang</td>
                    <td><span class="badge badge-success">Aktif</span></td>
                </tr>
                <tr>
                    <td>Volume Dokumen Terarsip</td>
                    <td class="font-bold">{{ $totalDocuments }} Berkas</td>
                    <td><span class="badge badge-info">Sinkron</span></td>
                </tr>
                <tr>
                    <td>Antrean Verifikasi Dokumen</td>
                    <td class="font-bold">{{ $pendingDocs }} Berkas</td>
                    <td><span class="badge {{ $pendingDocs > 0 ? 'badge-warning' : 'badge-success' }}">{{ $pendingDocs > 0 ? 'Perlu Tindakan' : 'Clear' }}</span></td>
                </tr>
                <tr>
                    <td>Laporan Isu/Kendala Terbuka</td>
                    <td class="font-bold">{{ $openIssues }} Laporan</td>
                    <td><span class="badge {{ $openIssues > 0 ? 'badge-danger' : 'badge-success' }}">{{ $openIssues > 0 ? 'Urgent' : 'Nihil' }}</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4 style="border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #1e40af; text-transform: uppercase; font-size: 10px;">Monitoring Kepatuhan Dokumen Wajib</h4>
        <table class="main-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Nama Pegawai</th>
                    <th width="20%">NIP</th>
                    <th width="25%">Progres</th>
                    <th width="15%">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nonCompliantEmployees->take(15) as $index => $emp)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $emp->full_name }}</td>
                    <td>{{ $emp->nip }}</td>
                    <td class="text-center">{{ $emp->uploaded_mandatory_count }}/{{ $emp->total_mandatory_count }}</td>
                    <td class="text-center font-bold" style="color: {{ $emp->compliance_percent < 100 ? '#b91c1c' : '#15803d' }}">
                        {{ number_format($emp->compliance_percent, 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($nonCompliantEmployees->count() > 15)
            <p style="font-size: 8px; font-style: italic; color: #64748b; margin-top: 5px;">* Menampilkan 15 dari {{ $nonCompliantEmployees->count() }} pegawai dengan kepatuhan terendah.</p>
        @endif
    </div>
@endsection
