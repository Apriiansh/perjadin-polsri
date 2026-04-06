<?php

namespace App\Libraries\Templates;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BundleExcelTemplate
{
    private const TRANSPORT_LABELS = [
        'udara' => 'Pesawat',
        'darat' => 'Mobil',
        'laut'  => 'Kapal',
    ];

    /**
     * Generate and stream the complete bundle Excel.
     *
     * Sheet order:
     * 1. SPD                  — all members in one sheet
     * 2. Rincian Biaya        — Rincian + Perhitungan Rampung + Kuitansi, all members in one sheet
     * 3+. Surat Pernyataan    — one sheet per member
     * N-1. Daftar Kontrol
     * N.   Daftar Nominatif
     */
    public function generate(
        object $travelRequest,
        array  $members,
        ?object $ppk = null,
        ?object $bpp = null,
        ?object $bendahara = null,
    ): void {
        helper('terbilang');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $tujuan = $travelRequest->destination_city
            ? $travelRequest->destination_city . ', ' . $travelRequest->destination_province
            : $travelRequest->destination_province;

        $tglBerangkat = !empty($travelRequest->departure_date) ? $this->formatTanggal($travelRequest->departure_date) : '-';
        $tglKembali   = !empty($travelRequest->return_date) ? $this->formatTanggal($travelRequest->return_date) : '-';
        $tglSurat     = !empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : date('d F Y');
        $tempatTerbit = $travelRequest->departure_place ?: 'Palembang';
        $transportLabel = self::TRANSPORT_LABELS[strtolower((string) $travelRequest->transportation_type)] ?? strtoupper((string) $travelRequest->transportation_type);

        // ── Sheet 1: SPD (all members) ────────────────────────────────
        $sheetSpd = new Worksheet($spreadsheet, 'SPD');
        $spreadsheet->addSheet($sheetSpd);
        $this->buildSpdSheet($sheetSpd, $travelRequest, $members, $ppk, $tujuan, $tglBerangkat, $tglKembali, $tglSurat, $tempatTerbit, $transportLabel);

        // ── Sheet 2: Rincian Biaya Perjadin (all members) ─────────────
        $sheetRincian = new Worksheet($spreadsheet, 'Rincian Biaya Perjadin');
        $spreadsheet->addSheet($sheetRincian);
        $this->buildRincianSheet($sheetRincian, $travelRequest, $members, $ppk, $bpp, $bendahara, $tujuan, $tglSurat, $tempatTerbit);

        // ── Sheet 3+: Surat Pernyataan per member ─────────────────────
        foreach ($members as $idx => $member) {
            $name = $this->sheetName('Pernyataan ' . ($idx + 1) . ' - ' . $member->employee_name);
            $sheet = new Worksheet($spreadsheet, $name);
            $spreadsheet->addSheet($sheet);
            $this->buildPernyataanSheet($sheet, $travelRequest, $member, $ppk, $tempatTerbit, $tglSurat);
        }

        // ── Daftar Kontrol ────────────────────────────────────────────
        $sheetCtrl = new Worksheet($spreadsheet, 'Daftar Kontrol');
        $spreadsheet->addSheet($sheetCtrl);
        $this->buildKontrolSheet($sheetCtrl, $travelRequest, $members, $bpp);

        // ── Daftar Nominatif ──────────────────────────────────────────
        $sheetNom = new Worksheet($spreadsheet, 'Daftar Nominatif');
        $spreadsheet->addSheet($sheetNom);
        $this->buildNominatifSheet($sheetNom, $travelRequest, $members, $bpp);

        // ── Output ────────────────────────────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Bundle_Perjadin_' . $travelRequest->id . '.xlsx';
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '_', $filename);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  SHEET 1: SPD — all members stacked in one sheet
    // ═════════════════════════════════════════════════════════════════════

    private function buildSpdSheet(
        Worksheet $sheet,
        object $travelRequest,
        array $members,
        ?object $ppk,
        string $tujuan,
        string $tglBerangkat,
        string $tglKembali,
        string $tglSurat,
        string $tempatTerbit,
        string $transportLabel,
    ): void {
        // Columns: A(num) B(sub-prefix) C(label) D(val-prefix) E(val1) F(val2)
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(51);
        $sheet->getColumnDimension('D')->setWidth(5);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(27);

        $row = 1;

        foreach ($members as $idx => $member) {
            if ($idx > 0) {
                $row += 3;
            }
            $row = $this->writeSpdMember(
                $sheet,
                $row,
                $travelRequest,
                $member,
                $ppk,
                $tujuan,
                $tglBerangkat,
                $tglKembali,
                $tglSurat,
                $tempatTerbit,
                $transportLabel,
            );
        }

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
    }

    private function writeSpdMember(
        Worksheet $sheet,
        int $startRow,
        object $travelRequest,
        object $member,
        ?object $ppk,
        string $tujuan,
        string $tglBerangkat,
        string $tglKembali,
        string $tglSurat,
        string $tempatTerbit,
        string $transportLabel,
    ): int {
        $bold   = ['font' => ['bold' => true]];

        $ppkName = $ppk ? $ppk->employee_name : '___________________________';
        $ppkNip  = $ppk ? ($ppk->nip ?: '-') : '___________________________';

        $pangkatGol = trim(($member->nama_golongan ?? '') . '/' . ($member->kode_golongan ?? ''), '/');
        $tingkat = $this->resolveTingkat($member);

        $maksud = $travelRequest->perihal_surat_rujukan
            ? 'Mengikuti kegiatan ' . $travelRequest->perihal_surat_rujukan
            : '-';

        $durasi = ($travelRequest->duration_days ?? '-') . ' (' . $this->numberToWords($travelRequest->duration_days ?? 0) . ') Hari';

        $row = $startRow;

        // ── Empty row above ───────────────────────────────────────────
        $row++;

        // ── Title ─────────────────────────────────────────────────────
        $sheet->setCellValue('A' . $row, 'SURAT PERJALANAN DINAS');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // ── Bordered table ────────────────────────────────────────────
        $tableStart = $row;

        // Item groups: [no, [[labelPrefix, labelText, valuePrefix, valueText], ...]]
        $items = [
            ['1', [
                ['', 'Pejabat Pembuat Komitmen', '', $ppkName],
            ]],
            ['2', [
                ['', 'Nama Pegawai yang melaksanakan perjalanan dinas', '', $member->employee_name],
                ['', 'NIP', '', $member->employee_nip ?: '-'],
            ]],
            ['3', [
                ['a.', 'Pangkat dan golongan', 'a.', $pangkatGol ?: '-'],
                ['b.', 'Jabatan/Instansi', 'b.', $member->employee_jabatan ?? '-'],
                ['c.', 'Tingkat Biaya Perjalanan Dinas', 'c.', $tingkat],
            ]],
            ['4', [
                ['', 'Maksud  perjalanan dinas', '', $maksud],
            ]],
            ['5', [
                ['', 'Alat angkutan yang dipergunakan', '', $transportLabel],
            ]],
            ['6', [
                ['a.', 'Tempat berangkat', 'a.', $travelRequest->departure_place ?: 'Palembang'],
                ['b.', 'Tempat tujuan', 'b.', $tujuan],
            ]],
            ['7', [
                ['a.', 'Lamanya Perjalanan Dinas', '', $durasi],
                ['b.', 'Tanggal Berangkat', '', $tglBerangkat],
                ['c.', 'Tanggal harus kembali/tiba ditempat baru *)', '', $tglKembali],
            ]],
            ['8', [
                ['', 'Pengikut : Nama', '', ''],
                ['1.', '', '', ''],
                ['2.', '', '', ''],
            ]],
            ['9', [
                ['', 'Pembebanan Anggaran', '', ''],
                ['a.', 'Instansi', '', $travelRequest->budget_burden_by ?: 'Politeknik Negeri Sriwijaya'],
                ['b.', 'Akun', '', $travelRequest->mak ?: ''],
            ]],
            ['10', [
                ['', 'Keterangan lain-lain', '', ''],
            ]],
        ];

        foreach ($items as $group) {
            $no        = $group[0];
            $groupRows = $group[1];
            $dataCount = count($groupRows);
            $groupStartRow = $row;

            foreach ($groupRows as $ri => $r) {
                // Number only on the first row of the group
                $sheet->setCellValue('A' . $row, ($ri === 0) ? $no : '');
                $sheet->setCellValue('B' . $row, $r[0]);
                $sheet->setCellValue('C' . $row, $r[1]);
                $sheet->setCellValue('D' . $row, $r[2]);
                $sheet->setCellValue('E' . $row, $r[3]);

                // Long values: merge E:F and wrap
                if ($no === '4' || $no === '10' || ($no === '9' && $ri === 0)) {
                    $sheet->mergeCells('E' . $row . ':F' . $row);
                    $sheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
                    if ($no === '4' && $ri === 0) {
                        $sheet->getRowDimension($row)->setRowHeight(80);
                    }
                }

                $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $row++;
            }

            // Ensure group has a minimum height or padding if needed
            // But for SPD, we usually want to follow the content

            $groupEndRow = $row - 1;
            $this->applySpdGroupBorders($sheet, $groupStartRow, $groupEndRow);
        }

        $tableEnd = $row - 1;

        // ── Signature below table ─────────────────────────────────────
        $row = $tableEnd + 2;

        $sheet->setCellValue('B' . $row, 'Tembusan lain-lain kepada :');
        $sheet->mergeCells('B' . $row . ':C' . $row);

        $sheet->setCellValue('D' . $row, 'DIKELUARKAN DI');
        $sheet->setCellValue('E' . $row, ': ' . strtoupper($tempatTerbit));
        $sheet->mergeCells('E' . $row . ':F' . $row);
        $sheet->getStyle('D' . $row . ':F' . $row)->applyFromArray($bold);
        $row++;

        $sheet->setCellValue('D' . $row, 'PADA TANGGAL');
        $sheet->setCellValue('E' . $row, ': ' . $tglSurat);
        $sheet->mergeCells('E' . $row . ':F' . $row);
        $sheet->getStyle('D' . $row . ':F' . $row)->applyFromArray($bold);
        $row += 2;

        $sheet->setCellValue('D' . $row, 'PEJABAT PEMBUAT KOMITMEN');
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $sheet->getStyle('D' . $row)->applyFromArray($bold);
        $row += 5;

        $sheet->setCellValue('D' . $row, $ppkName);
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $sheet->getStyle('D' . $row)->applyFromArray($bold);
        $row++;
        $sheet->setCellValue('D' . $row, 'NIP. ' . $ppkNip);
        $sheet->mergeCells('D' . $row . ':F' . $row);

        // ── Empty row below ───────────────────────────────────────────
        $row++;

        return $row;
    }

    private function applySpdGroupBorders(Worksheet $sheet, int $startRow, int $endRow): void
    {
        // Outer border for the group
        $sheet->getStyle('A' . $startRow . ':F' . $endRow)
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // Vertical separators
        $sheet->getStyle('A' . $startRow . ':A' . $endRow)
            ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('C' . $startRow . ':C' . $endRow)
            ->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

        // We specifically DO NOT apply internal horizontal borders within the group
        // But we DO need to make sure the bottom of the group has a border
        $sheet->getStyle('A' . $endRow . ':F' . $endRow)
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  SHEET 2: Rincian Biaya + Perhitungan Rampung + Kuitansi
    //           — all members stacked in one sheet
    // ═════════════════════════════════════════════════════════════════════

    private function buildRincianSheet(
        Worksheet $sheet,
        object $travelRequest,
        array $members,
        ?object $ppk,
        ?object $bpp,
        ?object $bendahara,
        string $tujuan,
        string $tglSurat,
        string $tempatTerbit,
    ): void {
        // Column widths matching example: A-K
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(3);
        $sheet->getColumnDimension('F')->setWidth(4);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(7);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(5);
        $sheet->getColumnDimension('K')->setWidth(42);

        $row = 1;

        foreach ($members as $idx => $member) {
            if ($idx > 0) {
                $row += 3;
            }
            $row = $this->writeRincianMember(
                $sheet,
                $row,
                $travelRequest,
                $member,
                $ppk,
                $bpp,
                $bendahara,
                $tujuan,
                $tglSurat,
                $tempatTerbit,
            );
        }

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
    }

    private function writeRincianMember(
        Worksheet $sheet,
        int $startRow,
        object $travelRequest,
        object $member,
        ?object $ppk,
        ?object $bpp,
        ?object $bendahara,
        string $tujuan,
        string $tglSurat,
        string $tempatTerbit,
    ): int {
        $bold = ['font' => ['bold' => true]];

        $ppkName = $ppk ? $ppk->employee_name : '___________________________';
        $ppkNip  = $ppk ? ($ppk->nip ?: '-') : '___________________________';
        $bendaharaName = $bendahara ? $bendahara->employee_name : '___________________________';
        $bendaharaNip  = $bendahara ? ($bendahara->nip ?: '-') : '___________________________';

        $totalBiaya = $member->total_biaya ?? 0;
        $terbilangText = ucfirst(trim(terbilang($totalBiaya))) . ' rupiah,-';

        // Fetch expense items
        $expenseItemModel = new \App\Models\TravelExpenseItemModel();
        $expenseItems = $expenseItemModel
            ->where('travel_member_id', $member->travel_member_id)
            ->orderBy('category', 'ASC')
            ->findAll();

        $noSppd = $member->no_sppd ?? '';
        $tglSppd = !empty($member->tgl_sppd) ? $this->formatTanggal($member->tgl_sppd) : $tglSurat;
        $noSppdDisplay = $noSppd ?: ('          /SPPD/BLU/' . ($travelRequest->tahun_anggaran ?: date('Y')));

        $row = $startRow;

        // ═══════════════════════════════════════════════════════════════
        // SECTION 1: RINCIAN BIAYA PERJALANAN DINAS
        // ═══════════════════════════════════════════════════════════════

        $sheet->setCellValue('A' . $row, 'RINCIAN BIAYA PERJALANAN DINAS');
        $sheet->mergeCells('A' . $row . ':K' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // Lampiran header
        $sheet->setCellValue('B' . $row, 'Lampiran SPPD Nomor');
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, ':');
        $sheet->setCellValue('I' . $row, $noSppdDisplay);
        $sheet->mergeCells('I' . $row . ':K' . $row);
        $sheet->getStyle('B' . $row)->applyFromArray($bold);
        $row++;

        $sheet->setCellValue('B' . $row, 'Tanggal');
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, ':');
        $sheet->setCellValue('I' . $row, $tglSppd);
        $sheet->mergeCells('I' . $row . ':K' . $row);
        $sheet->getStyle('B' . $row)->applyFromArray($bold);
        $row += 2;

        // ── Expense Table ─────────────────────────────────────────────
        $tableHeaderRow = $row;

        // Header row
        $sheet->setCellValue('A' . $row, 'NO.');
        $sheet->setCellValue('B' . $row, 'Perincian Biaya');
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, 'Jumlah');
        $sheet->mergeCells('H' . $row . ':I' . $row);
        $sheet->setCellValue('J' . $row, 'Keterangan');
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $row++;

        // Data rows
        $dataStartRow = $row;
        $itemNo = 1;

        if (!empty($expenseItems)) {
            foreach ($expenseItems as $item) {
                $sheet->setCellValue('A' . $row, $itemNo);
                $sheet->setCellValue('B' . $row, $item->item_name);
                $sheet->mergeCells('B' . $row . ':G' . $row);
                $sheet->setCellValue('H' . $row, 'Rp.');
                $sheet->setCellValue('I' . $row, $item->amount);
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->mergeCells('J' . $row . ':K' . $row);
                $itemNo++;
                $row++;
            }
        } else {
            // Fallback: summary categories
            $dailyRate = ($travelRequest->duration_days > 0 && ($member->uang_harian ?? 0) > 0)
                ? $member->uang_harian / $travelRequest->duration_days
                : 0;
            $categories = [];
            if (($member->uang_harian ?? 0) > 0) {
                $categories[] = [
                    'Uang Harian ' . $travelRequest->duration_days . ' (' . $this->numberToWords($travelRequest->duration_days ?? 0) . ') hari @ Rp ' . number_format($dailyRate, 0, ',', '.') . ',-',
                    $member->uang_harian,
                ];
            }
            if (($member->tiket ?? 0) > 0) {
                $categories[] = [
                    'Tiket ' . ($travelRequest->departure_place ?: 'Palembang') . '-' . ($travelRequest->destination_city ?: $tujuan) . ' (PP)',
                    $member->tiket,
                ];
            }
            if (($member->penginapan ?? 0) > 0) {
                $categories[] = ['Penginapan', $member->penginapan];
            }
            if (($member->transport_darat ?? 0) > 0) {
                $categories[] = ['Transport Darat/taksi (PP)', $member->transport_darat];
            }
            if (($member->transport_lokal ?? 0) > 0) {
                $categories[] = ['Transport Lokal/taksi (PP)', $member->transport_lokal];
            }
            if (($member->uang_representasi ?? 0) > 0) {
                $categories[] = ['Uang Representasi', $member->uang_representasi];
            }

            foreach ($categories as $cat) {
                $sheet->setCellValue('A' . $row, $itemNo);
                $sheet->setCellValue('B' . $row, $cat[0]);
                $sheet->mergeCells('B' . $row . ':G' . $row);
                $sheet->setCellValue('H' . $row, 'Rp.');
                $sheet->setCellValue('I' . $row, $cat[1]);
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->mergeCells('J' . $row . ':K' . $row);
                $itemNo++;
                $row++;
            }
        }

        $dataEndRow = $row - 1;

        // Apply borders to data rows
        for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
            $sheet->getStyle('A' . $r)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => [
                    'left'   => ['borderStyle' => Border::BORDER_THIN],
                    'right'  => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $sheet->getStyle('B' . $r . ':G' . $r)->applyFromArray([
                'borders' => [
                    'right'  => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $sheet->getStyle('H' . $r . ':I' . $r)->applyFromArray([
                'borders' => [
                    'right'  => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $sheet->getStyle('J' . $r . ':K' . $r)->applyFromArray([
                'borders' => [
                    'right'  => ['borderStyle' => Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
        }

        // Total row
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, 'Rp.');
        $sheet->setCellValue('I' . $row, $totalBiaya);
        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H' . $row . ':I' . $row)->applyFromArray($bold);
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('I' . $row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $row++;

        // Terbilang
        $sheet->setCellValue('B' . $row, 'Terbilang  :');
        $sheet->mergeCells('B' . $row . ':C' . $row);
        $sheet->getStyle('B' . $row)->applyFromArray($bold);
        $sheet->setCellValue('D' . $row, $terbilangText);
        $sheet->mergeCells('D' . $row . ':K' . $row);
        $sheet->getStyle('D' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'italic' => true],
            'alignment' => ['wrapText' => true],
        ]);
        $row += 2;

        // ── Signatures: Telah dibayar / Telah menerima ────────────────
        // Right side — date
        $sheet->setCellValue('H' . $row, $tempatTerbit . ', ' . $tglSurat);
        $sheet->mergeCells('H' . $row . ':K' . $row);
        $row++;

        // Left: Telah dibayar sejumlah | Right: Telah menerima
        $sheet->setCellValue('B' . $row, 'Telah dibayar sejumlah');
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, 'Telah menerima jumlah uang sebesar');
        $sheet->mergeCells('H' . $row . ':K' . $row);
        $row++;

        // Left: amount
        $sheet->setCellValue('B' . $row, 'Rp.');
        $sheet->setCellValue('C' . $row, number_format($totalBiaya, 0, ',', '.'));
        $sheet->setCellValue('D' . $row, ',-');
        $sheet->getStyle('B' . $row . ':D' . $row)->applyFromArray($bold);
        // Right: amount
        $sheet->setCellValue('H' . $row, 'Rp.');
        $sheet->setCellValue('I' . $row, number_format($totalBiaya, 0, ',', '.'));
        $sheet->setCellValue('J' . $row, ',-');
        $sheet->getStyle('H' . $row . ':J' . $row)->applyFromArray($bold);
        $row++;

        // Right: terbilang
        $sheet->setCellValue('H' . $row, $terbilangText);
        $sheet->mergeCells('H' . $row . ':K' . $row);
        $sheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
        $row += 2;

        // Bendahara / Yang menerima
        $sheet->setCellValue('B' . $row, 'Bendahara Pengeluaran');
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->setCellValue('J' . $row, 'Yang menerima,');
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $row += 4;

        // Names
        $sheet->setCellValue('B' . $row, $bendaharaName);
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->getStyle('B' . $row)->applyFromArray($bold);
        $sheet->setCellValue('J' . $row, $member->employee_name);
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $sheet->getStyle('J' . $row)->applyFromArray($bold);
        $row++;

        $sheet->setCellValue('B' . $row, 'NIP. ' . $bendaharaNip);
        $sheet->mergeCells('B' . $row . ':G' . $row);
        $sheet->setCellValue('J' . $row, 'NIP ' . ($member->employee_nip ?: '-'));
        $sheet->mergeCells('J' . $row . ':K' . $row);

        // ═══════════════════════════════════════════════════════════════
        // SECTION 2: PERHITUNGAN SPPD RAMPUNG
        // ═══════════════════════════════════════════════════════════════

        $row += 2;

        // Divider line
        $sheet->getStyle('A' . $row . ':K' . $row)
            ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $row += 2;

        $sheet->setCellValue('B' . $row, 'PERHITUNGAN SPPD RAMPUNG');
        $sheet->mergeCells('B' . $row . ':K' . $row);
        $sheet->getStyle('B' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // Ditetapkan
        $sheet->setCellValue('B' . $row, 'Ditetapkan sejumlah');
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, ':');
        $sheet->setCellValue('G' . $row, number_format($totalBiaya, 0, ',', '.'));
        $sheet->getStyle('G' . $row)->applyFromArray(['font' => ['bold' => true, 'underline' => true]]);
        $sheet->setCellValue('H' . $row, ',-');
        $row++;

        // Yang telah dibayar semula
        $sheet->setCellValue('B' . $row, 'Yang telah dibayar semula');
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, ':');
        $sheet->setCellValue('G' . $row, number_format($totalBiaya, 0, ',', '.'));
        $sheet->getStyle('G' . $row)->applyFromArray(['font' => ['bold' => true, 'underline' => true]]);
        $sheet->getStyle('G' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->setCellValue('H' . $row, ',-');
        $row++;

        // Sisa
        $sheet->setCellValue('B' . $row, 'Sisa kurang / lebih');
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, ':');
        $sheet->setCellValue('G' . $row, ' NIHIL');
        $sheet->getStyle('G' . $row)->applyFromArray($bold);
        $sheet->setCellValue('H' . $row, ',--');
        $row += 2;

        // PPK signature
        $sheet->setCellValue('G' . $row, 'Pejabat Pembuat Komitmen');
        $sheet->mergeCells('G' . $row . ':I' . $row);
        $row += 5;
        $sheet->setCellValue('G' . $row, $ppkName);
        $sheet->mergeCells('G' . $row . ':I' . $row);
        $sheet->getStyle('G' . $row)->applyFromArray($bold);
        $row++;
        $sheet->setCellValue('G' . $row, 'NIP. ' . $ppkNip);
        $sheet->mergeCells('G' . $row . ':I' . $row);

        // Beban MAK etc.
        $row += 2;
        $sheet->setCellValue('I' . $row, 'Beban  MAK');
        $sheet->setCellValue('K' . $row, ': ' . ($travelRequest->mak ?: ''));
        $row++;
        $sheet->setCellValue('I' . $row, 'Bukti Kas  No.');
        $sheet->setCellValue('K' . $row, ':');
        $row++;
        $sheet->setCellValue('I' . $row, 'Tahun Anggaran');
        $sheet->setCellValue('K' . $row, ': ' . ($travelRequest->tahun_anggaran ?: date('Y')));

        // ═══════════════════════════════════════════════════════════════
        // SECTION 3: KUITANSI
        // ═══════════════════════════════════════════════════════════════

        $row += 3;

        $sheet->setCellValue('A' . $row, 'K U I T A N S I');
        $sheet->mergeCells('A' . $row . ':K' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 2;

        // Kuitansi data
        $kuitansiItems = [
            ['Sudah Terima dari', 'Bendaharawan Politeknik Negeri Sriwijaya'],
            ['Uang Sebesar', 'Rp.      ' . number_format($totalBiaya, 0, ',', '.') . '  ,-'],
            ['Untuk Pembayaran', 'Biaya perjalanan dinas'],
            ['Berdasarkan SPPD', $travelRequest->budget_burden_by ?: 'Direktur Politeknik Negeri Sriwijaya'],
            ['Nomor', $noSppdDisplay],
            ['Tanggal', $tglSppd],
            ['Untuk perjalanan dinas dari', ($travelRequest->departure_place ?: 'Palembang') . '-' . ($travelRequest->destination_city ?: $tujuan)],
            ['Terbilang', $terbilangText],
        ];

        foreach ($kuitansiItems as $ki) {
            $sheet->setCellValue('B' . $row, $ki[0]);
            $sheet->mergeCells('B' . $row . ':D' . $row);
            $sheet->setCellValue('E' . $row, ':');
            $sheet->setCellValue('F' . $row, $ki[1]);
            $sheet->mergeCells('F' . $row . ':K' . $row);

            if ($ki[0] === 'Uang Sebesar') {
                $sheet->getStyle('F' . $row)->applyFromArray($bold);
            }
            if ($ki[0] === 'Terbilang') {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'font'      => ['bold' => true, 'italic' => true],
                    'alignment' => ['wrapText' => true],
                ]);
            }
            $row++;
        }

        // Kuitansi signatures
        $row += 2;

        $sheet->setCellValue('B' . $row, 'Dibukukan tanggal');
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('J' . $row, $tempatTerbit . ', ' . $tglSurat);
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $row++;

        $sheet->setCellValue('B' . $row, 'Bendahara Pengeluaran');
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('J' . $row, 'Yang menerima,');
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $row += 4;

        // Names
        $sheet->setCellValue('B' . $row, $bendaharaName);
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->getStyle('B' . $row)->applyFromArray($bold);
        $sheet->setCellValue('J' . $row, $member->employee_name);
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $sheet->getStyle('J' . $row)->applyFromArray($bold);
        $row++;

        $sheet->setCellValue('B' . $row, 'NIP. ' . $bendaharaNip);
        $sheet->mergeCells('B' . $row . ':D' . $row);
        $sheet->setCellValue('J' . $row, 'NIP ' . ($member->employee_nip ?: '-'));
        $sheet->mergeCells('J' . $row . ':K' . $row);
        $row += 2;

        // Setuju dibayar
        $sheet->setCellValue('G' . $row, 'Setuju dibayar');
        $sheet->mergeCells('G' . $row . ':I' . $row);
        $row++;
        $sheet->setCellValue('G' . $row, 'Pejabat Pembuat Komitmen,');
        $sheet->mergeCells('G' . $row . ':I' . $row);
        $row += 5;

        $sheet->setCellValue('G' . $row, $ppkName);
        $sheet->mergeCells('G' . $row . ':I' . $row);
        $sheet->getStyle('G' . $row)->applyFromArray($bold);
        $row++;
        $sheet->setCellValue('G' . $row, 'NIP. ' . $ppkNip);
        $sheet->mergeCells('G' . $row . ':I' . $row);

        return $row;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  SHEET 3+: Surat Pernyataan — separate sheet per member
    // ═════════════════════════════════════════════════════════════════════

    private function buildPernyataanSheet(
        Worksheet $sheet,
        object $travelRequest,
        object $member,
        ?object $ppk,
        string $tempatTerbit,
        string $tglSurat,
    ): void {
        $bold   = ['font' => ['bold' => true]];
        $center = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(3);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(35);

        $row = 1;

        // KOP
        $sheet->setCellValue('A' . $row, 'KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($center);
        $row++;
        $sheet->setCellValue('A' . $row, 'RISET, DAN TEKNOLOGI');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($center);
        $row++;
        $sheet->setCellValue('A' . $row, 'POLITEKNIK NEGERI SRIWIJAYA');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray(array_merge($bold, $center));
        $row++;
        $sheet->setCellValue('A' . $row, 'Jalan Srijaya Negara Bukit Besar – Palembang 30139');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($center);
        $sheet->getStyle('A' . $row)->getFont()->setSize(10);
        $row++;
        $sheet->setCellValue('A' . $row, 'Telp. 0711-353414 Fax. 0711-355918');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($center);
        $sheet->getStyle('A' . $row)->getFont()->setSize(10);
        $row++;
        $sheet->setCellValue('A' . $row, 'Laman : http://polsri.ac.id');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray($center);
        $sheet->getStyle('A' . $row)->getFont()->setSize(10);

        // Line
        $row++;
        $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'SURAT PERNYATAAN');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Yang bertanda tangan di bawah ini :');
        $sheet->mergeCells('A' . $row . ':D' . $row);

        $row += 2;
        $fields = [
            ['Nama', $member->employee_name],
            ['NIP', $member->employee_nip ?: '-'],
            ['Jabatan', $member->employee_jabatan ?? '-'],
        ];
        foreach ($fields as $f) {
            $sheet->setCellValue('A' . $row, $f[0]);
            $sheet->setCellValue('B' . $row, ':');
            $sheet->setCellValue('C' . $row, $f[1]);
            $sheet->getStyle('C' . $row)->applyFromArray($bold);
            $row++;
        }

        $row++;
        $tglSuratTugas = !empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : '-';
        $noSuratTugas = $travelRequest->no_surat_tugas ?: '__________________';

        $sheet->setCellValue('A' . $row, 'Berdasarkan Surat Tugas tanggal ' . $tglSuratTugas . ' Nomor: ' . $noSuratTugas . ' dengan ini kami menyatakan dengan sesungguhnya bahwa :');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(40);

        $row += 2;
        $sheet->setCellValue('A' . $row, '1.');
        $sheet->setCellValue('C' . $row, 'Bukti-bukti (Tiket / bukti transportasi, Boarding Pass, Kwitansi, Hotel bill / tagihan hotel dan sebagainya) yang dilampirkan dalam rangka melakukan perjalanan dinas adalah bukti-bukti asli dan benar yang dikeluarkan oleh perusahaan / instansi yang berwenang untuk menerbitkan bukti-bukti tersebut.');
        $sheet->mergeCells('C' . $row . ':D' . $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(70);

        $row += 2;
        $sheet->setCellValue('A' . $row, '2.');
        $sheet->setCellValue('C' . $row, 'Apabila dikemudian hari terdapat kesalahan atau temuan dari aparat pengawasan fungsional, kami bersedia untuk mempertanggungjawabkannya.');
        $sheet->mergeCells('C' . $row . ':D' . $row);
        $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(40);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipertanggungjawabkan sebagaimana mestinya.');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);

        // Signatures
        $row += 2;
        $sigRow = $row;

        $sheet->setCellValue('A' . $row, 'Mengetahui/Menyetujui');
        $row++;
        $sheet->setCellValue('A' . $row, 'an. Kuasa Pengguna Anggaran');
        $row++;
        $sheet->setCellValue('A' . $row, 'Pejabat Pembuat Komitmen');

        $row = $sigRow + 6;
        $sheet->setCellValue('A' . $row, $ppk ? $ppk->employee_name : '___________________________');
        $sheet->getStyle('A' . $row)->applyFromArray($bold);
        $row++;
        $sheet->setCellValue('A' . $row, 'NIP. ' . ($ppk ? ($ppk->nip ?: '-') : '___________________________'));

        // Right side
        $rRow = $sigRow;
        $sheet->setCellValue('C' . $rRow, $tempatTerbit . ', ' . $tglSurat);
        $sheet->mergeCells('C' . $rRow . ':D' . $rRow);
        $rRow++;
        $sheet->setCellValue('C' . $rRow, 'Yang melakukan Perjalanan Dinas,');
        $sheet->mergeCells('C' . $rRow . ':D' . $rRow);

        $rRow = $sigRow + 6;
        $sheet->setCellValue('C' . $rRow, $member->employee_name);
        $sheet->mergeCells('C' . $rRow . ':D' . $rRow);
        $sheet->getStyle('C' . $rRow)->applyFromArray($bold);
        $rRow++;
        $sheet->setCellValue('C' . $rRow, 'NIP. ' . ($member->employee_nip ?: '-'));
        $sheet->mergeCells('C' . $rRow . ':D' . $rRow);

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Daftar Kontrol
    // ═════════════════════════════════════════════════════════════════════

    private function buildKontrolSheet(Worksheet $sheet, object $travelRequest, array $members, ?object $bpp): void
    {
        $cols = [
            'A' => 5,
            'B' => 35,
            'C' => 20,
            'D' => 20,
            'E' => 25,
            'F' => 15,
            'G' => 18,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 18,
            'N' => 18,
            'O' => 18,
            'P' => 18,
            'Q' => 18,
            'R' => 20,
            'S' => 15
        ];
        foreach ($cols as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        $sheet->setCellValue('A1', 'DAFTAR KONTROL PEMBAYARAN');
        $sheet->mergeCells('A1:S1');
        $sheet->setCellValue('A2', 'BIAYA PERJALANAN DINAS UANG HARIAN DAN TRANSPORT LOKAL');
        $sheet->mergeCells('A2:S2');
        $sheet->setCellValue('A3', 'Mata Anggaran Kegiatan (MAK) : ' . ($travelRequest->mak ?: '-'));
        $sheet->mergeCells('A3:S3');

        $sheet->getStyle('A1:A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Table header
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

        $sheet->getStyle('A5:S6')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $row = 7;
        $tglST = !empty($travelRequest->tgl_surat_tugas) ? date('d F Y', strtotime($travelRequest->tgl_surat_tugas)) : '-';
        $noST = $travelRequest->no_surat_tugas ?: '-';

        $totals = ['tiket' => 0, 'darat' => 0, 'lokal' => 0, 'hotel' => 0, 'harian' => 0, 'rep' => 0, 'grand' => 0];

        foreach ($members as $idx => $member) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $member->employee_name . "\n\nST No. " . $noST . "\nTanggal " . $tglST);
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);

            $nik = $member->employee_nik ?: '-';
            $nip = $member->employee_nip ?: '-';

            $sheet->setCellValue('C' . $row, $nik);
            $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('D' . $row, $nip);
            $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);

            $gol = ($member->nama_golongan ?? '') . (($member->nama_golongan && $member->kode_golongan) ? '/' : '') . ($member->kode_golongan ?? '');
            $sheet->setCellValue('E' . $row, $gol);
            $sheet->setCellValue('F' . $row, $travelRequest->lokasi ?: ($travelRequest->destination_city ?: '-'));

            $dep = !empty($travelRequest->departure_date) ? date('d', strtotime($travelRequest->departure_date)) : '-';
            $ret = !empty($travelRequest->return_date) ? date('d/m/Y', strtotime($travelRequest->return_date)) : '-';
            $sheet->setCellValue('G' . $row, $dep . ' - ' . $ret);
            $sheet->setCellValue('H' . $row, $travelRequest->duration_days . ' Hari');

            $sheet->setCellValue('I' . $row, $member->tiket ?? 0);
            $sheet->setCellValue('J' . $row, $member->transport_darat ?? 0);
            $sheet->setCellValue('K' . $row, $member->transport_lokal ?? 0);
            $sheet->setCellValue('L' . $row, $member->penginapan ?? 0);
            $sheet->setCellValue('M' . $row, $member->uang_harian ?? 0);
            $sheet->setCellValue('N' . $row, $member->uang_representasi ?? 0);
            $sheet->setCellValue('O' . $row, $member->total_biaya ?? 0);
            $sheet->setCellValue('P' . $row, '');
            $sheet->setCellValue('Q' . $row, '');
            $sheet->setCellValue("R{$row}", $member->rekening_bank ? "$member->rekening_bank" : '-');
            $sheet->setCellValue('S' . $row, '');

            $totals['tiket']  += ($member->tiket ?? 0);
            $totals['darat']  += ($member->transport_darat ?? 0);
            $totals['lokal']  += ($member->transport_lokal ?? 0);
            $totals['hotel']  += ($member->penginapan ?? 0);
            $totals['harian'] += ($member->uang_harian ?? 0);
            $totals['rep']    += ($member->uang_representasi ?? 0);
            $totals['grand']  += ($member->total_biaya ?? 0);

            $row++;
        }

        $sheet->getStyle('A7:S' . ($row - 1))->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('I7:Q' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue('A' . $row, 'J U M L A H');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, $totals['tiket']);
        $sheet->setCellValue('J' . $row, $totals['darat']);
        $sheet->setCellValue('K' . $row, $totals['lokal']);
        $sheet->setCellValue('L' . $row, $totals['hotel']);
        $sheet->setCellValue('M' . $row, $totals['harian']);
        $sheet->setCellValue('N' . $row, $totals['rep']);
        $sheet->setCellValue('O' . $row, $totals['grand']);
        $sheet->setCellValue('P' . $row, '');
        $sheet->setCellValue('Q' . $row, '');
        $sheet->mergeCells('R' . $row . ':S' . $row);

        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('I' . $row . ':Q' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        $sheet->setCellValue('A' . $row, 'TERBILANG');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, terbilang_rupiah($totals['grand']));
        $sheet->mergeCells('I' . $row . ':S' . $row);
        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
            'font'    => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;
        $sigStart = $row;
        $sheet->setCellValue('B' . $row, 'Mengetahui,');
        $sheet->setCellValue('B' . ($row + 1), 'yang Membayar,');
        $row += 4;
        $sheet->setCellValue('B' . $row, $bpp ? $bpp->employee_name : '________________________');
        $sheet->getStyle('B' . $row)->applyFromArray(['font' => ['bold' => true]]);
        $sheet->setCellValue('B' . ($row + 1), 'NIP. ' . ($bpp ? ($bpp->nip ?: '-') : '________________________'));

        $row = $sigStart;
        $sheet->setCellValue('R' . $row, 'Palembang, ' . date('j F Y'));
        $sheet->setCellValue('R' . ($row + 1), 'Yang Menerima,');
        $row += 4;
        $sheet->setCellValue('R' . $row, '________________________');
        $sheet->setCellValue('R' . ($row + 1), 'NIP.');

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    }
    // ═════════════════════════════════════════════════════════════════════
    //  Daftar Nominatif
    // ═════════════════════════════════════════════════════════════════════

    private function buildNominatifSheet(Worksheet $sheet, object $travelRequest, array $members, ?object $bpp): void
    {
        $cols = [
            'A' => 5,
            'B' => 35,
            'C' => 20,
            'D' => 20,
            'E' => 25,
            'F' => 15,
            'G' => 18,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 18,
            'O' => 18,
            'P' => 20,
            'Q' => 15
        ];
        foreach ($cols as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        $sheet->setCellValue('A1', 'DAFTAR NOMINATIF PERJALANAN DINAS');
        $sheet->mergeCells('A1:Q1');
        $sheet->setCellValue('A2', 'BIAYA PERJALANAN DINAS UANG HARIAN DAN TRANSPORT LOKAL');
        $sheet->mergeCells('A2:Q2');
        $sheet->setCellValue('A3', 'Mata Anggaran Kegiatan (MAK) : ' . ($travelRequest->mak ?: '-'));
        $sheet->mergeCells('A3:Q3');

        $sheet->getStyle('A1:A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Table header
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
        $sheet->mergeCells('I5:O5');

        $sheet->setCellValue('I6', 'Tiket');
        $sheet->setCellValue('J6', 'Transport Darat');
        $sheet->setCellValue('K6', 'Transport Lokal');
        $sheet->setCellValue('L6', 'Penginapan');
        $sheet->setCellValue('M6', 'Uang Harian');
        $sheet->setCellValue('N6', 'Uang Representasi');
        $sheet->setCellValue('O6', 'Jumlah');

        $sheet->setCellValue('P5', 'Rekening');
        $sheet->mergeCells('P5:P6');
        $sheet->setCellValue('Q5', 'Tanda tangan');
        $sheet->mergeCells('Q5:Q6');

        $sheet->getStyle('A5:Q6')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $row = 7;
        $tglST = !empty($travelRequest->tgl_surat_tugas) ? date('d F Y', strtotime($travelRequest->tgl_surat_tugas)) : '-';
        $noST = $travelRequest->no_surat_tugas ?: '-';

        $totals = ['tiket' => 0, 'darat' => 0, 'lokal' => 0, 'hotel' => 0, 'harian' => 0, 'rep' => 0, 'grand' => 0];

        foreach ($members as $idx => $member) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $member->employee_name . "\n\nST No. " . $noST . "\nTanggal " . $tglST);
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);

            $nik = $member->employee_nik ?: '-';
            $nip = $member->employee_nip ?: '-';

            $sheet->setCellValue('C' . $row, $nik);
            $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $sheet->setCellValue('D' . $row, $nip);
            $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);

            $gol = ($member->nama_golongan ?? '') . (($member->nama_golongan && $member->kode_golongan) ? '/' : '') . ($member->kode_golongan ?? '');
            $sheet->setCellValue('E' . $row, $gol);
            $sheet->setCellValue('F' . $row, $travelRequest->lokasi ?: ($travelRequest->destination_city ?: '-'));

            $dep = !empty($travelRequest->departure_date) ? date('d', strtotime($travelRequest->departure_date)) : '-';
            $ret = !empty($travelRequest->return_date) ? date('d/m/Y', strtotime($travelRequest->return_date)) : '-';
            $sheet->setCellValue('G' . $row, $dep . ' - ' . $ret);
            $sheet->setCellValue('H' . $row, $travelRequest->duration_days . ' Hari');

            $sheet->setCellValue('I' . $row, $member->tiket ?? 0);
            $sheet->setCellValue('J' . $row, $member->transport_darat ?? 0);
            $sheet->setCellValue('K' . $row, $member->transport_lokal ?? 0);
            $sheet->setCellValue('L' . $row, $member->penginapan ?? 0);
            $sheet->setCellValue('M' . $row, $member->uang_harian ?? 0);
            $sheet->setCellValue('N' . $row, $member->uang_representasi ?? 0);
            $sheet->setCellValue('O' . $row, $member->total_biaya ?? 0);
            $sheet->setCellValue("P{$row}", $member->rekening_bank ? "$member->rekening_bank" : '-');
            $sheet->setCellValue('Q' . $row, '');

            $totals['tiket']  += ($member->tiket ?? 0);
            $totals['darat']  += ($member->transport_darat ?? 0);
            $totals['lokal']  += ($member->transport_lokal ?? 0);
            $totals['hotel']  += ($member->penginapan ?? 0);
            $totals['harian'] += ($member->uang_harian ?? 0);
            $totals['rep']    += ($member->uang_representasi ?? 0);
            $totals['grand']  += ($member->total_biaya ?? 0);

            $row++;
        }

        $sheet->getStyle('A7:Q' . ($row - 1))->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('I7:O' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue('A' . $row, 'J U M L A H');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, $totals['tiket']);
        $sheet->setCellValue('J' . $row, $totals['darat']);
        $sheet->setCellValue('K' . $row, $totals['lokal']);
        $sheet->setCellValue('L' . $row, $totals['hotel']);
        $sheet->setCellValue('M' . $row, $totals['harian']);
        $sheet->setCellValue('N' . $row, $totals['rep']);
        $sheet->setCellValue('O' . $row, $totals['grand']);
        $sheet->mergeCells('P' . $row . ':Q' . $row);

        $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('I' . $row . ':O' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        $sheet->setCellValue('A' . $row, 'TERBILANG');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, terbilang_rupiah($totals['grand']));
        $sheet->mergeCells('I' . $row . ':Q' . $row);
        $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
            'font'    => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row += 2;
        $sigStart = $row;
        $sheet->setCellValue('B' . $row, 'Mengetahui,');
        $sheet->setCellValue('B' . ($row + 1), 'yang Membayar,');
        $row += 4;
        $sheet->setCellValue('B' . $row, $bpp ? $bpp->employee_name : '________________________');
        $sheet->getStyle('B' . $row)->applyFromArray(['font' => ['bold' => true]]);
        $sheet->setCellValue('B' . ($row + 1), 'NIP. ' . ($bpp ? ($bpp->nip ?: '-') : '________________________'));

        $row = $sigStart;
        $sheet->setCellValue('P' . $row, 'Palembang, ' . date('j F Y'));
        $sheet->setCellValue('P' . ($row + 1), 'Yang Menerima,');
        $row += 4;
        $sheet->setCellValue('P' . $row, '________________________');
        $sheet->setCellValue('P' . ($row + 1), 'NIP.');

        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    }
    // ═════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═════════════════════════════════════════════════════════════════════

    private function sheetName(string $name): string
    {
        $name = preg_replace('/[\[\]\*\?\/\\\\:]/', '', $name);
        return mb_substr($name, 0, 31);
    }

    private function formatTanggal(string $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $ts = strtotime($date);
        return date('d', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }

    private function numberToWords(int $number): string
    {
        $words = [
            0 => 'Nol',
            1 => 'Satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
            10 => 'Sepuluh',
            11 => 'Sebelas',
            12 => 'Dua Belas',
            13 => 'Tiga Belas',
            14 => 'Empat Belas',
            15 => 'Lima Belas',
        ];
        return $words[$number] ?? (string) $number;
    }

    private function resolveTingkat(object $member): string
    {
        $golSrc = $member->kode_golongan ?: ($member->employee_golongan ?? '');
        if (!$golSrc) {
            return '-';
        }
        $gol = strtoupper($golSrc);
        if (strpos($gol, 'IV') !== false) return 'A';
        if (strpos($gol, 'III') !== false) return 'B';
        if (strpos($gol, 'II') !== false) return 'C';
        if (strpos($gol, 'I') !== false) return 'D';
        return '-';
    }
}
