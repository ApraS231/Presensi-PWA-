<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Presensi Karyawan - PT. CAK</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #1C1B1F;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #1565C0;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 15pt;
            font-weight: bold;
            color: #1565C0;
            text-transform: uppercase;
        }

        .company-subtitle {
            font-size: 9pt;
            color: #49454F;
        }

        .report-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .report-meta {
            text-align: center;
            font-size: 9pt;
            color: #49454F;
            margin-bottom: 15px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th, .data-table td {
            border: 1px solid #CAC4D0;
            padding: 6px 8px;
            font-size: 8.5pt;
        }

        .data-table th {
            background-color: #E8DEF8;
            color: #1D192B;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 40%;
        }

        .signature-space {
            height: 55px;
        }
    </style>
</head>
<body>

    <!-- Header / Kop Surat Perusahaan -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="company-name">PT. Cahaya Anugrah Kalimantan</div>
                <div class="company-subtitle">
                    Jl. Pupuk Raya No. 45, Bontang, Kalimantan Timur 75313<br>
                    Telp: (0548) 23456 | Email: hrd@ptcak.com | Web: www.ptcak.com
                </div>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: bottom;">
                <div style="font-size: 8pt; color: #79747E;">
                    Dicetak pada:<br>
                    <b>{{ \Carbon\Carbon::now('Asia/Makassar')->isoFormat('D MMMM Y, HH:mm') }} WITA</b>
                </div>
            </td>
        </tr>
    </table>

    <!-- Judul & Parameter Periode Laporan -->
    <div class="report-title">Laporan Rekapitulasi Presensi & Jam Kerja Karyawan</div>
    <div class="report-meta">
        Periode: <b>{{ $startDate->isoFormat('D MMMM Y') }}</b> s/d <b>{{ $endDate->isoFormat('D MMMM Y') }}</b>
        @if($department)
            &nbsp;|&nbsp; Departemen: <b>{{ $department }}</b>
        @endif
    </div>

    <!-- Tabel Data Rekapitulasi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 70px;">NIK</th>
                <th>Nama Karyawan</th>
                <th style="width: 110px;">Departemen</th>
                <th style="width: 45px;">Hadir (Hari)</th>
                <th style="width: 45px;">Terlambat (Hari)</th>
                <th style="width: 50px;">Akumulasi Telat (Mnt)</th>
                <th style="width: 40px;">Izin</th>
                <th style="width: 40px;">Sakit</th>
                <th style="width: 40px;">Cuti</th>
                <th style="width: 40px;">Alpha</th>
                <th style="width: 60px;">Total Jam Kerja</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row['nik'] }}</td>
                    <td><b>{{ $row['name'] }}</b></td>
                    <td>{{ $row['department'] }}</td>
                    <td class="text-center">{{ $row['total_hadir'] }}</td>
                    <td class="text-center">{{ $row['total_terlambat'] }}</td>
                    <td class="text-center">{{ $row['total_menit_terlambat'] }}</td>
                    <td class="text-center">{{ $row['total_izin'] }}</td>
                    <td class="text-center">{{ $row['total_sakit'] }}</td>
                    <td class="text-center">{{ $row['total_cuti'] }}</td>
                    <td class="text-center" style="{{ $row['total_alpha'] > 0 ? 'color: #B3261E; font-weight: bold;' : '' }}">
                        {{ $row['total_alpha'] }}
                    </td>
                    <td class="text-center" style="font-weight: bold; background-color: #F7F2FA;">
                        {{ $row['total_jam_kerja'] }} Jam
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #79747E;">
                        Tidak ada data presensi karyawan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Blok Tanda Tangan Pengesahan -->
    <table class="signature-table">
        <tr>
            <td class="signature-box" style="width: 50%;">
                <div>Mengetahui,</div>
                <div style="font-weight: bold;">HRD & Kepegawaian</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">( HRD Manager )</div>
                <div style="font-size: 8pt; color: #79747E;">PT. Cahaya Anugrah Kalimantan</div>
            </td>
            <td style="width: 20%;"></td>
            <td class="signature-box" style="width: 30%;">
                <div>Bontang, {{ \Carbon\Carbon::now('Asia/Makassar')->isoFormat('D MMMM Y') }}</div>
                <div style="font-weight: bold;">General Manager / Direksi</div>
                <div class="signature-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">( Pimpinan Perusahaan )</div>
                <div style="font-size: 8pt; color: #79747E;">PT. Cahaya Anugrah Kalimantan</div>
            </td>
        </tr>
    </table>

</body>
</html>
