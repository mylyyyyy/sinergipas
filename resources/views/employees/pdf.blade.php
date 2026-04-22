@extends('layouts.pdf_export')

@section('title', 'Daftar Pegawai Lapas Jombang')
@section('report_title', 'DAFTAR PERSONEL KEPEGAWAIAN')
@section('report_meta', 'Total Personel: ' . $employees->count() . ' Orang | Per Tanggal: ' . date('d/m/Y'))

@section('content')
    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Lengkap</th>
                <th width="15%">NIP</th>
                <th width="15%">Pangkat/Gol</th>
                <th width="20%">Jabatan</th>
                <th width="20%">Unit Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $emp)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $emp->full_name }}</td>
                <td>{{ $emp->nip }}</td>
                <td>{{ $emp->rank_relation->name ?? '-' }}</td>
                <td>{{ $emp->position }}</td>
                <td>{{ $emp->work_unit->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
