<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman rekapitulasi laporan presensi.
     */
    public function index(Request $request): View
    {
        $startDateStr = $request->query('start_date', Carbon::now('Asia/Makassar')->startOfMonth()->toDateString());
        $endDateStr   = $request->query('end_date', Carbon::now('Asia/Makassar')->toDateString());

        $startDate = Carbon::parse($startDateStr);
        $endDate   = Carbon::parse($endDateStr);
        $department = $request->query('department');

        $allReportData = ReportCalculationService::generateSummary($startDate, $endDate, $department);

        // Agregasi Global dari seluruh data
        $totalKaryawan = count($allReportData);
        $totalSemuaJamKerja = round(array_sum(array_column($allReportData, 'total_jam_kerja')), 1);
        $totalSemuaTerlambat = array_sum(array_column($allReportData, 'total_terlambat'));
        $totalSemuaIzinCuti = array_sum(array_column($allReportData, 'total_izin')) 
            + array_sum(array_column($allReportData, 'total_sakit')) 
            + array_sum(array_column($allReportData, 'total_cuti'));
        $totalSemuaAlpha = array_sum(array_column($allReportData, 'total_alpha'));

        // Paginasi 10 item per halaman
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($allReportData, ($currentPage - 1) * $perPage, $perPage);
        $reportData = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $totalKaryawan,
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $departments = User::where('role', 'karyawan')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('admin.reports.index', compact(
            'startDate',
            'endDate',
            'startDateStr',
            'endDateStr',
            'department',
            'departments',
            'reportData',
            'totalKaryawan',
            'totalSemuaJamKerja',
            'totalSemuaTerlambat',
            'totalSemuaIzinCuti',
            'totalSemuaAlpha'
        ));
    }

    /**
     * Mengekspor laporan ke format Microsoft Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDateStr = $request->query('start_date', Carbon::now('Asia/Makassar')->startOfMonth()->toDateString());
        $endDateStr   = $request->query('end_date', Carbon::now('Asia/Makassar')->toDateString());

        $startDate = Carbon::parse($startDateStr);
        $endDate   = Carbon::parse($endDateStr);
        $department = $request->query('department');

        $reportData = ReportCalculationService::generateSummary($startDate, $endDate, $department);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Presensi');

        // 1. Judul & Kop Dokumen
        $sheet->setCellValue('A1', 'PT. CAHAYA ANUGRAH KALIMANTAN');
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI PRESENSI & JAM KERJA KARYAWAN');
        $sheet->setCellValue('A3', 'Periode: ' . $startDate->format('d/m/Y') . ' s/d ' . $endDate->format('d/m/Y') . ($department ? ' | Dept: ' . $department : ''));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1565C0'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);

        // 2. Table Headers (Baris ke-5)
        $headers = [
            'A5' => 'No',
            'B5' => 'NIK',
            'C5' => 'Nama Karyawan',
            'D5' => 'Departemen',
            'E5' => 'Hadir Tepat',
            'F5' => 'Hadir Terlambat',
            'G5' => 'Total Hadir',
            'H5' => 'Akumulasi Telat (Menit)',
            'I5' => 'Izin',
            'J5' => 'Sakit',
            'K5' => 'Cuti',
            'L5' => 'Alpha',
            'M5' => 'Total Jam Kerja (Jam)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet->getStyle('A5:M5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(28);

        // 3. Isi Data Baris
        $rowNum = 6;
        foreach ($reportData as $index => $row) {
            $sheet->setCellValue("A{$rowNum}", $index + 1);
            $sheet->setCellValue("B{$rowNum}", $row['nik']);
            $sheet->setCellValue("C{$rowNum}", $row['name']);
            $sheet->setCellValue("D{$rowNum}", $row['department']);
            $sheet->setCellValue("E{$rowNum}", $row['total_hadir_tepat_waktu']);
            $sheet->setCellValue("F{$rowNum}", $row['total_terlambat']);
            $sheet->setCellValue("G{$rowNum}", $row['total_hadir']);
            $sheet->setCellValue("H{$rowNum}", $row['total_menit_terlambat']);
            $sheet->setCellValue("I{$rowNum}", $row['total_izin']);
            $sheet->setCellValue("J{$rowNum}", $row['total_sakit']);
            $sheet->setCellValue("K{$rowNum}", $row['total_cuti']);
            $sheet->setCellValue("L{$rowNum}", $row['total_alpha']);
            $sheet->setCellValue("M{$rowNum}", $row['total_jam_kerja']);

            // Styling baris data
            $rowStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E0E0E0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet->getStyle("A{$rowNum}:M{$rowNum}")->applyFromArray($rowStyle);

            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$rowNum}:M{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight baris selang-seling (zebra stripe)
            if ($index % 2 === 1) {
                $sheet->getStyle("A{$rowNum}:M{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8F9FA');
            }

            $rowNum++;
        }

        // 4. Auto-size Kolom
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Presensi_CAK_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Mengekspor laporan ke format PDF A4 Landscape.
     */
    public function exportPdf(Request $request): Response
    {
        $startDateStr = $request->query('start_date', Carbon::now('Asia/Makassar')->startOfMonth()->toDateString());
        $endDateStr   = $request->query('end_date', Carbon::now('Asia/Makassar')->toDateString());

        $startDate = Carbon::parse($startDateStr);
        $endDate   = Carbon::parse($endDateStr);
        $department = $request->query('department');

        $reportData = ReportCalculationService::generateSummary($startDate, $endDate, $department);

        $pdf = Pdf::loadView('admin.reports.pdf_template', compact(
            'startDate',
            'endDate',
            'department',
            'reportData'
        ))->setPaper('a4', 'landscape');

        $filename = 'Laporan_Presensi_CAK_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
