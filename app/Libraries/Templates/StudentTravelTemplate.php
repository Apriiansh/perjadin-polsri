<?php

namespace App\Libraries\Templates;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StudentTravelTemplate
{
    private const LAST_COL = 'K'; // A–K (11 columns)

    public function generate(
        object  $travelRequest,
        array   $members,
        ?object $ppk       = null,
        ?object $bendahara = null
    ): void {
        helper('terbilang');
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Penerimaan');

        $spreadsheet->getDefaultStyle()->getFont()
            ->setName('Arial')
            ->setSize(10);

        // ── Column widths ────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(13);
        $sheet->getColumnDimension('I')->setWidth(13);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(20);

        $row = 1;

        // ── Letterhead ───────────────────────────────────────────────
        $letterhead = [
            1 => 'KEMENTERIAN PENDIDIKAN TINGGI, SAINS DAN TEKNOLOGI',
            2 => 'POLITEKNIK NEGERI SRIWIJAYA',
            3 => 'PALEMBANG',
        ];
        foreach ($letterhead as $r => $text) {
            $sheet->mergeCells('A' . $r . ':' . self::LAST_COL . $r);
            $sheet->setCellValue('A' . $r, $text);
            $sheet->getStyle('A' . $r)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $r)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        // Garis bawah letterhead
        $sheet->getStyle('A3:' . self::LAST_COL . '3')->getBorders()
            ->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // ── TAHUN ANGGARAN / NOMOR ───────────────────────────────────
        $row = 5;
        $tahun = $travelRequest->tahun_anggaran ?? date('Y');
        $nomor = $travelRequest->nomor         ?? '';
        $mak   = $travelRequest->mak           ?? '';

        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'TAHUN ANGGARAN');
        $sheet->setCellValue('C' . $row, ': ' . $tahun);
        $sheet->mergeCells('I' . $row . ':' . self::LAST_COL . $row);
        $sheet->setCellValue('H' . $row, 'NOMOR:');
        $sheet->setCellValue('I' . $row, $nomor);
        $sheet->getStyle('I' . $row . ':' . self::LAST_COL . $row)->getBorders()
            ->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'M.A.K');
        $sheet->setCellValue('C' . $row, ': ' . $mak);
        $row += 2; // baris kosong

        // ── DAFTAR ───────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->setCellValue('A' . $row, 'DAFTAR');
        $sheet->mergeCells('C' . $row . ':' . self::LAST_COL . $row);
        $sheet->setCellValue('C' . $row, ': ' . strtoupper($travelRequest->perihal));
        $sheet->getStyle('C' . $row)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(55);
        $row += 2; // baris kosong setelah daftar

        // ── Header tabel (2 baris) ───────────────────────────────────
        $headerRow1 = $row;
        $headerRow2 = $row + 1;

        $headers = [
            'A' => 'No.',
            'B' => 'NAMA',
            'C' => 'JURUSAN/ PROGRAM STUDI',
            'D' => 'JUMLAH HARI',
            'E' => 'UANG SAKU',
            'F' => 'TRANSPORT LOKAL/TAXI',
            'G' => 'TIKET PLG-JAKARTA (Pp)',
            'H' => 'AKOMODASI',
            'I' => 'LAIN-LAIN',
            'J' => 'JUMLAH PENERIMAAN',
            'K' => 'TANDA TANGAN',
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . $headerRow1, $text);
        }

        // Kolom tanpa sub-header → merge row1+row2
        foreach (['A', 'B', 'C', 'D', 'H', 'K'] as $col) {
            $sheet->mergeCells($col . $headerRow1 . ':' . $col . $headerRow2);
        }

        // Sub-header "Rp." di row 2
        foreach (['E', 'F', 'G', 'I', 'J'] as $col) {
            $sheet->setCellValue($col . $headerRow2, 'Rp.');
        }

        $sheet->getStyle('A' . $headerRow1 . ':' . self::LAST_COL . $headerRow2)->getFont()->setBold(true);
        $sheet->getStyle('A' . $headerRow1 . ':' . self::LAST_COL . $headerRow2)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A' . $headerRow1 . ':' . self::LAST_COL . $headerRow2)->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension($headerRow1)->setRowHeight(32);
        $sheet->getRowDimension($headerRow2)->setRowHeight(14);

        $row = $headerRow2 + 1;
        $startDataRow = $row;
        $totalNominal = 0;
        $otherNotes   = [];

        // ── Baris data (2 baris per anggota) ────────────────────────
        foreach ($members as $idx => $m) {
            $expensesByCat = [];
            $memberTotal   = 0;
            $memberOthers  = [];
            foreach ($m->expenses as $e) {
                if ($e->category === 'other') {
                    $memberOthers[] = $e->item_name;
                    $expensesByCat['other'] = ($expensesByCat['other'] ?? 0) + $e->amount;
                } else {
                    $expensesByCat[$e->category] = $e->amount;
                }
                $memberTotal += $e->amount;
            }

            if (!empty($memberOthers)) {
                $otherNotes[] = $m->name . ': ' . implode(', ', $memberOthers);
            }

            $totalNominal += $memberTotal;
            $days = $m->days ?? $travelRequest->duration_days ?? $travelRequest->days ?? 0;
            if ($days == 0 && !empty($travelRequest->departure_date) && !empty($travelRequest->return_date)) {
                $start = new \DateTime($travelRequest->departure_date);
                $end   = new \DateTime($travelRequest->return_date);
                $days  = (int) $start->diff($end)->days + 1;
            }

            $r1 = $row;
            $r2 = $row + 1;

            // Merge semua kolom kecuali B (nama=row1, nim=row2)
            foreach (['A', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $col) {
                $sheet->mergeCells($col . $r1 . ':' . $col . $r2);
            }

            $sheet->setCellValue('A' . $r1, $idx + 1);
            $sheet->setCellValue('B' . $r1, $m->name);
            $sheet->setCellValue('B' . $r2, $m->nim ?? '');
            $sheet->setCellValue('C' . $r1, ($m->jurusan ?? '') . '/' . ($m->prodi ?? ''));
            $sheet->setCellValue('D' . $r1, $days);
            $sheet->setCellValue('E' . $r1, $expensesByCat['pocket_money']  ?? 0);
            $sheet->setCellValue('F' . $r1, $expensesByCat['transport']     ?? 0);
            $sheet->setCellValue('G' . $r1, $expensesByCat['ticket']        ?? 0);
            $sheet->setCellValue('H' . $r1, $expensesByCat['accommodation'] ?? 0);
            $sheet->setCellValue('I' . $r1, $expensesByCat['other']         ?? 0);
            $sheet->setCellValue('J' . $r1, '=SUM(E' . $r1 . ':I' . $r1 . ')');
            $sheet->setCellValue('K' . $r1, ($idx + 1) . '.');

            // Format angka
            $sheet->getStyle('E' . $r1 . ':J' . $r1)->getNumberFormat()
                ->setFormatCode('#,##0;-#,##0;-');

            // Alignment per kolom
            $sheet->getStyle('A' . $r1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $r1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $r1 . ':J' . $r1)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $r1)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle('K' . $r1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $r1 . ':' . self::LAST_COL . $r2)->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getRowDimension($r1)->setRowHeight(18);
            $sheet->getRowDimension($r2)->setRowHeight(14);

            $row += 2;
        }

        $endDataRow = $row - 1;

        // ── Baris Jumlah ─────────────────────────────────────────────
        $jumlahRow = $row;
        $sheet->mergeCells('A' . $jumlahRow . ':D' . $jumlahRow);
        $sheet->setCellValue('A' . $jumlahRow, 'Jumlah');
        foreach (['E', 'F', 'G', 'H', 'I', 'J'] as $col) {
            $sheet->setCellValue(
                $col . $jumlahRow,
                '=SUM(' . $col . $startDataRow . ':' . $col . $endDataRow . ')'
            );
            $sheet->getStyle($col . $jumlahRow)->getNumberFormat()
                ->setFormatCode('#,##0;-#,##0;-');
            $sheet->getStyle($col . $jumlahRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        $sheet->getStyle('A' . $jumlahRow . ':' . self::LAST_COL . $jumlahRow)->getFont()->setBold(true);
        $row++;

        // ── Baris Terbilang ──────────────────────────────────────────
        $terbilangRow = $row;
        $sheet->mergeCells('A' . $terbilangRow . ':D' . $terbilangRow);
        $sheet->setCellValue('A' . $terbilangRow, 'Terbilang');
        $sheet->mergeCells('E' . $terbilangRow . ':' . self::LAST_COL . $terbilangRow);
        $sheet->setCellValue('E' . $terbilangRow, ': ' . terbilang_rupiah($totalNominal));
        $sheet->getStyle('A' . $terbilangRow)->getFont()->setBold(true);
        $sheet->getStyle('E' . $terbilangRow)->getFont()->setItalic(true);
        $row++;

        // ── Border seluruh tabel ─────────────────────────────────────
        $sheet->getStyle('A' . $headerRow1 . ':' . self::LAST_COL . ($row - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ── Keterangan Lain-lain (di luar tabel utama) ──────────────────
        if (!empty($otherNotes)) {
            $noteRow = $headerRow1;
            $sheet->setCellValue('M' . $noteRow, 'KETERANGAN LAIN-LAIN:');
            $sheet->getStyle('M' . $noteRow)->getFont()->setBold(true)->setUnderline(true);
            foreach ($otherNotes as $note) {
                $noteRow++;
                $sheet->setCellValue('M' . $noteRow, '- ' . $note);
                $sheet->getStyle('M' . $noteRow)->getFont()->setSize(9)->setItalic(true);
            }
        }

        // ── Footer (tanda tangan) ────────────────────────────────────
        $row += 1;

        // Baris 1 footer
        $sheet->setCellValue('B' . $row, 'Dibukukan tanggal :');
        $sheet->mergeCells('I' . $row . ':' . self::LAST_COL . $row);
        $sheet->setCellValue('I' . $row, 'Lunas Bayar Tgl :');
        $row++;

        // Baris 2 footer
        $sheet->setCellValue('B' . $row, 'Bendahara Pengeluaran,');
        $sheet->mergeCells('I' . $row . ':' . self::LAST_COL . $row);
        $sheet->setCellValue('I' . $row, 'BPP RUTIN/BOPTN');

        // Spasi tanda tangan (4 baris)
        $row += 5;

        // Nama Bendahara & BPP
        $sheet->setCellValue('B' . $row, $bendahara ? $bendahara->name : '');
        $sheet->mergeCells('I' . $row . ':' . self::LAST_COL . $row);
        $sheet->setCellValue('I' . $row, 'Asmanidar S.E.');
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row)->getFont()->setBold(true);
        $row++;

        // NIP Bendahara & BPP
        $sheet->setCellValue('B' . $row, 'NIP ' . ($bendahara ? $bendahara->nip : ''));
        $sheet->mergeCells('I' . $row . ':' . self::LAST_COL . $row);
        $sheet->setCellValue('I' . $row, 'NIP 197204051994032001');
        $row++;

        // Gap 2 baris untuk PPK (seperti permintaan user)
        $row += 2;

        // PPK Section (Setuju bayar)
        $sheet->mergeCells('F' . $row . ':G' . $row);
        $sheet->setCellValue('F' . $row, 'Setuju bayar,');
        $row++;
        $sheet->mergeCells('F' . $row . ':G' . $row);
        $sheet->setCellValue('F' . $row, 'Pejabat Pembuat Komitmen,');

        $row += 5; // Spasi tanda tangan PPK

        $sheet->mergeCells('F' . $row . ':G' . $row);
        $sheet->setCellValue('F' . $row, $ppk ? $ppk->name : '');
        $sheet->getStyle('F' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->mergeCells('F' . $row . ':G' . $row);
        $sheet->setCellValue('F' . $row, 'NIP ' . ($ppk ? $ppk->nip : ''));

        // Left align all footer signatures
        $sheet->getStyle('A' . ($row - 15) . ':' . self::LAST_COL . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // ── Output ───────────────────────────────────────────────────
        $filename = 'SPJ_Perjadin_Mahasiswa_' . $travelRequest->id . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
