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
    public function generate(object $travelRequest, array $members, ?object $bendahara = null): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Kontrol');

        // Helper for terbilang
        helper('terbilang');

        // ── SET COLUMN WIDTHS ────────────────────────────────────────────────
        $cols = ['A' => 5, 'B' => 35, 'C' => 20, 'D' => 20, 'E' => 25, 'F' => 15,
                 'G' => 18, 'H' => 15, 'I' => 15, 'J' => 15, 'K' => 15, 'L' => 15,
                 'M' => 18, 'N' => 18, 'O' => 18, 'P' => 18, 'Q' => 18, 'R' => 20, 'S' => 15];
        foreach ($cols as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // ── HEADER ───────────────────────────────────────────────────────────
        $sheet->setCellValue('A1', 'DAFTAR KONTROL PEMBAYARAN');
        $sheet->mergeCells('A1:S1');
        $sheet->setCellValue('A2', 'BIAYA PERJALANAN DINAS UANG HARIAN DAN TRANSPORT LOKAL');
        $sheet->mergeCells('A2:S2');
        $sheet->setCellValue('A3', 'Mata Anggaran Kegiatan (MAK) : ' . ($travelRequest->mak ?: '-'));
        $sheet->mergeCells('A3:S3');

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
        $sheet->setCellValue('C5', 'NIK');
        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('D5', 'NIP');
        $sheet->mergeCells('D5:D6');
        $sheet->setCellValue('E5', 'Pangkat / Gol');
        $sheet->mergeCells('E5:E6');
        $sheet->setCellValue('F5', 'Tujuan');
        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('G5', 'Tanggal Berangkat');
        $sheet->mergeCells('G5:G6');
        $sheet->setCellValue('H5', 'Lama Perjalanan Dinas');
        $sheet->mergeCells('H5:H6');
        $sheet->setCellValue('I5', 'Biaya Perjalanan Dinas');
        $sheet->mergeCells('I5:Q5');

        $sheet->setCellValue('I6', 'Tiket');
        $sheet->setCellValue('J6', 'Transport Darat');
        $sheet->setCellValue('K6', 'Transport Lokal');
        $sheet->setCellValue('L6', 'Penginapan');
        $sheet->setCellValue('M6', 'Uang Harian');
        $sheet->setCellValue('N6', 'Uang Representasi');
        $sheet->setCellValue('O6', 'Jumlah');
        $sheet->setCellValue('P6', 'Dibayar Ke Pegawai');
        $sheet->setCellValue('Q6', 'Dibayar Ke Pihak Lainnya');

        $sheet->setCellValue('R5', 'Rekening');
        $sheet->mergeCells('R5:R6');
        $sheet->setCellValue('S5', 'Tanda tangan');
        $sheet->mergeCells('S5:S6');

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
        $sheet->getStyle('A5:S6')->applyFromArray($tableHeaderStyle);

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

            $nik = $member->employee_nik ?: '-';
            $nip = $member->employee_nip ?: '-';
            $sheet->setCellValue('C' . $row, $nik);
            $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('D' . $row, $nip);
            $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);

            $gol = ($member->nama_golongan ?? '') . (($member->nama_golongan && $member->kode_golongan) ? '/' : '') . ($member->kode_golongan ?? '');
            $sheet->setCellValue('E' . $row, $gol);

            $sheet->setCellValue('F' . $row, $travelRequest->destination_province ?: '-');
            
            $departureDateStr = !empty($travelRequest->departure_date) ? date('d', strtotime($travelRequest->departure_date)) : '-';
            $returnDateStr = !empty($travelRequest->return_date) ? date('d/m/Y', strtotime($travelRequest->return_date)) : '-';
            $sheet->setCellValue('G' . $row, $departureDateStr . ' - ' . $returnDateStr);
            
            $sheet->setCellValue('H' . $row, $travelRequest->duration_days . ' Hari');

            // Biaya
            $sheet->setCellValue('I' . $row, $member->tiket ?? 0);
            $sheet->setCellValue('J' . $row, $member->transport_darat ?? 0);
            $sheet->setCellValue('K' . $row, $member->transport_lokal ?? 0);
            $sheet->setCellValue('L' . $row, $member->penginapan ?? 0);
            $sheet->setCellValue('M' . $row, $member->uang_harian ?? 0);
            $sheet->setCellValue('N' . $row, $member->uang_representasi ?? 0);
            $sheet->setCellValue('O' . $row, $member->total_biaya ?? 0);
            $sheet->setCellValue('P' . $row, ''); // Dibayar Ke Pegawai
            $sheet->setCellValue('Q' . $row, ''); // Dibayar Ke Pihak Lainnya

            $sheet->setCellValue("R{$row}", $member->rekening_bank ? "{$member->rekening_bank}" : '-');
            $sheet->setCellValue('S' . $row, ''); // Tanda tangan blank

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
        $sheet->mergeCells('A' . $row . ':H' . $row);

        $sheet->setCellValue('I' . $row, $totalTiket);
        $sheet->setCellValue('J' . $row, $totalDarat);
        $sheet->setCellValue('K' . $row, $totalLokal);
        $sheet->setCellValue('L' . $row, $totalHotel);
        $sheet->setCellValue('M' . $row, $totalHarian);
        $sheet->setCellValue('N' . $row, $totalRep);
        $sheet->setCellValue('O' . $row, $grandTotal);
        $sheet->setCellValue('P' . $row, '');
        $sheet->setCellValue('Q' . $row, '');
        $sheet->mergeCells('R' . $row . ':S' . $row);

        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Row styles for alignment
        $sheet->getStyle('A7:S' . ($row - 1))->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Format Currency
        $sheet->getStyle('I7:Q' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        // ── TERBILANG ────────────────────────────────────────────────────────
        $sheet->setCellValue('A' . $row, 'TERBILANG');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, terbilang_rupiah($grandTotal));
        $sheet->mergeCells('I' . $row . ':S' . $row);

        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;

        // ── SIGNATORIES ──────────────────────────────────────────────────────
        $sigStartRow = $row;

        // Left Column: Bendahara
        $sheet->setCellValue('B' . $row, 'Mengetahui,');
        $sheet->setCellValue('B' . ($row + 1), 'yang Membayar,');
        $row += 4;
        $sheet->setCellValue('B' . $row, $bendahara ? $bendahara->employee_name : '________________________');
        $sheet->getStyle('B' . $row)->applyFromArray(['font' => ['bold' => true]]);
        $sheet->setCellValue('B' . ($row + 1), 'NIP. ' . ($bendahara ? ($bendahara->nip ?: '-') : '________________________'));

        // Right Column: Receiver
        $row = $sigStartRow;
        $tglSekarang = date('j F Y');
        $sheet->setCellValue('R' . $row, 'Palembang, ' . $tglSekarang);
        $sheet->setCellValue('R' . ($row + 1), 'Yang Menerima,');
        $row += 4;
        $sheet->setCellValue('R' . $row, '________________________');
        $sheet->setCellValue('R' . ($row + 1), 'NIP.');

        // Format signatories
        $sheet->getStyle('B' . $sigStartRow . ':S' . ($row + 1))->getFont()->setSize(10);

        // ── PREPARE DOWNLOAD ─────────────────────────────────────────────────
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Daftar_Kontrol_' . $travelRequest->id . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
