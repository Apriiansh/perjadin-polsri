<?php

namespace App\Libraries\Templates;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DaftarKontrolTemplate
{
    /**
     * Generate and stream Daftar Kontrol Pembayaran Excel
     */
    public function generate(object $travelRequest, array $members, ?object $bpp = null): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Kontrol');

        // Helper for terbilang
        helper('terbilang');

        // ── SET COLUMN WIDTHS ────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);   // No
        $sheet->getColumnDimension('B')->setWidth(35);  // Nama + ST
        $sheet->getColumnDimension('C')->setWidth(20);  // NIP
        $sheet->getColumnDimension('D')->setWidth(25);  // Pangkat/Gol
        $sheet->getColumnDimension('E')->setWidth(15);  // Tujuan
        $sheet->getColumnDimension('F')->setWidth(18);  // Tgl Berangkat
        $sheet->getColumnDimension('G')->setWidth(15);  // Lama
        $sheet->getColumnDimension('H')->setWidth(15);  // Tiket
        $sheet->getColumnDimension('I')->setWidth(15);  // Transport Darat
        $sheet->getColumnDimension('J')->setWidth(15);  // Transport Lokal
        $sheet->getColumnDimension('K')->setWidth(15);  // Penginapan
        $sheet->getColumnDimension('L')->setWidth(15);  // Uang Harian
        $sheet->getColumnDimension('M')->setWidth(18);  // Representasi
        $sheet->getColumnDimension('N')->setWidth(18);  // Jumlah
        $sheet->getColumnDimension('O')->setWidth(20);  // Rekening
        $sheet->getColumnDimension('P')->setWidth(15);  // Tanda Tangan

        // ── HEADER ───────────────────────────────────────────────────────────
        $sheet->setCellValue('A1', 'DAFTAR KONTROL PEMBAYARAN');
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A2', 'BIAYA PERJALANAN DINAS UANG HARIAN DAN TRANSPORT LOKAL');
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A3', 'MAK : ' . ($travelRequest->mak ?: ''));
        $sheet->mergeCells('A3:P3');

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A1:A3')->applyFromArray($headerStyle);

        // ── TABLE HEADER ─────────────────────────────────────────────────────
        $sheet->setCellValue('A5', 'No.');
        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('B5', 'Nama');
        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('C5', 'NIP');
        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('D5', 'Pangkat / Gol');
        $sheet->mergeCells('D5:D6');
        $sheet->setCellValue('E5', 'Tujuan');
        $sheet->mergeCells('E5:E6');
        $sheet->setCellValue('F5', 'Tanggal Berangkat');
        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('G5', 'Lama Perjalanan Dinas');
        $sheet->mergeCells('G5:G6');
        $sheet->setCellValue('H5', 'Biaya Perjalanan Dinas');
        $sheet->mergeCells('H5:N5');

        $sheet->setCellValue('H6', 'Tiket');
        $sheet->setCellValue('I6', 'Transport Darat');
        $sheet->setCellValue('J6', 'Transport Lokal');
        $sheet->setCellValue('K6', 'Penginapan');
        $sheet->setCellValue('L6', 'Uang Harian');
        $sheet->setCellValue('M6', 'Uang Representasi');
        $sheet->setCellValue('N6', 'Jumlah');

        $sheet->setCellValue('O5', 'Rekening');
        $sheet->mergeCells('O5:O6');
        $sheet->setCellValue('P5', 'Tanda tangan');
        $sheet->mergeCells('P5:P6');

        $tableHeaderStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];
        $sheet->getStyle('A5:P6')->applyFromArray($tableHeaderStyle);

        // ── TABLE CONTENT ────────────────────────────────────────────────────
        $row = 7;
        $tglST = !empty($travelRequest->tgl_surat_tugas) ? date('d F Y', strtotime($travelRequest->tgl_surat_tugas)) : '-';
        $noST = $travelRequest->no_surat_tugas ?: '-';

        $totalTiket = 0;
        $totalDarat = 0;
        $totalLokal = 0;
        $totalHotel = 0;
        $totalHarian = 0;
        $totalRep = 0;
        $grandTotal = 0;

        foreach ($members as $idx => $member) {
            $sheet->setCellValue('A' . $row, $idx + 1);

            // Nama + ST info with formatting
            $fullName = $member->employee_name . "\n\nST No. " . $noST . "\nTanggal " . $tglST;
            $sheet->setCellValue('B' . $row, $fullName);
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('C' . $row, $member->employee_nip ? "'" . $member->employee_nip : '-');

            $gol = ($member->nama_golongan ?? '') . (($member->nama_golongan && $member->kode_golongan) ? '/' : '') . ($member->kode_golongan ?? '');
            $sheet->setCellValue('D' . $row, $gol);

            $sheet->setCellValue('E' . $row, $travelRequest->departure_place ?: 'Palembang');
            $sheet->setCellValue('F' . $row, !empty($travelRequest->departure_date) . ' - ' . !empty($travelRequest->return_date) ? date('d', strtotime($travelRequest->departure_date)) . ' - ' . date('d/m/Y', strtotime($travelRequest->return_date)) : '-');
            $sheet->setCellValue('G' . $row, $travelRequest->duration_days . ' Hari');

            // Biaya
            $sheet->setCellValue('H' . $row, $member->tiket ?? 0);
            $sheet->setCellValue('I' . $row, $member->transport_darat ?? 0);
            $sheet->setCellValue('J' . $row, $member->transport_lokal ?? 0);
            $sheet->setCellValue('K' . $row, $member->penginapan ?? 0);
            $sheet->setCellValue('L' . $row, $member->uang_harian ?? 0);
            $sheet->setCellValue('M' . $row, $member->uang_representasi ?? 0);
            $sheet->setCellValue('N' . $row, $member->total_biaya ?? 0);

            $sheet->setCellValue('O' . $row, $member->rekening_bank ? "'" . $member->rekening_bank : '-');
            $sheet->setCellValue('P' . $row, ''); // Tanda tangan blank

            // Sums
            $totalTiket += ($member->tiket ?? 0);
            $totalDarat += ($member->transport_darat ?? 0);
            $totalLokal += ($member->transport_lokal ?? 0);
            $totalHotel += ($member->penginapan ?? 0);
            $totalHarian += ($member->uang_harian ?? 0);
            $totalRep += ($member->uang_representasi ?? 0);
            $grandTotal += ($member->total_biaya ?? 0);

            $row++;
        }

        // ── FOOTER TOTALS ────────────────────────────────────────────────────
        $sheet->setCellValue('A' . $row, 'J U M L A H');
        $sheet->mergeCells('A' . $row . ':G' . $row);

        $sheet->setCellValue('H' . $row, $totalTiket);
        $sheet->setCellValue('I' . $row, $totalDarat);
        $sheet->setCellValue('J' . $row, $totalLokal);
        $sheet->setCellValue('K' . $row, $totalHotel);
        $sheet->setCellValue('L' . $row, $totalHarian);
        $sheet->setCellValue('M' . $row, $totalRep);
        $sheet->setCellValue('N' . $row, $grandTotal);
        $sheet->mergeCells('O' . $row . ':P' . $row);

        $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Row styles for alignment
        $sheet->getStyle('A7:P' . ($row - 1))->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Format Currency
        $sheet->getStyle('H7:N' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        // ── TERBILANG ────────────────────────────────────────────────────────
        $sheet->setCellValue('A' . $row, 'TERBILANG');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, terbilang_rupiah($grandTotal));
        $sheet->mergeCells('H' . $row . ':P' . $row);

        $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;

        // ── SIGNATORIES ──────────────────────────────────────────────────────
        $sigStartRow = $row;

        // Left Column: BPP
        $sheet->setCellValue('B' . $row, 'Mengetahui,');
        $sheet->setCellValue('B' . ($row + 1), 'yang Membayar,');
        $row += 4;
        $sheet->setCellValue('B' . $row, $bpp ? $bpp->employee_name : '________________________');
        $sheet->setCellValue('B' . ($row + 1), 'NIP. ' . ($bpp ? ($bpp->nip ?: '-') : '________________________'));

        // Right Column: Receiver
        $row = $sigStartRow;
        $tglSekarang = date('j F Y');
        $sheet->setCellValue('L' . $row, 'Palembang, ' . $tglSekarang);
        $sheet->setCellValue('L' . ($row + 1), 'Yang Menerima,');
        $row += 4;
        $sheet->setCellValue('L' . $row, '________________________');
        $sheet->setCellValue('L' . ($row + 1), 'NIP.');

        // Format signatories
        $sheet->getStyle('B' . $sigStartRow . ':P' . ($row + 1))->getFont()->setSize(10);

        // ── PREPARE DOWNLOAD ─────────────────────────────────────────────────
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Daftar_Kontrol_' . $travelRequest->id . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
