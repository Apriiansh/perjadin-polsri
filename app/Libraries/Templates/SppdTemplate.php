<?php

namespace App\Libraries\Templates;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class SppdTemplate
{
    private const TRANSPORT_LABELS = [
        'udara' => 'Pesawat',
        'darat' => 'Mobil',
        'laut'  => 'Kapal',
    ];

    /**
     * Generate and stream SPD .docx
     * 
     * @param object      $travelRequest
     * @param array       $members
     * @param object|null $ppk
     * @param int|null    $specificMemberId If set, only generate for this member
     * @param bool        $showBackPage     Whether to include the verification grid (Page 2)
     */
    public function generate(object $travelRequest, array $members, ?object $ppk = null, ?int $specificMemberId = null, bool $showBackPage = true): void
    {
        // 1. Fetch PPK if not provided
        if (!$ppk) {
            $signatoryModel = new \App\Models\SignatoriesModel();
            $allSignatories = $signatoryModel->getAllWithEmployee();
            foreach ($allSignatories as $s) {
                if ($s->is_active && (stripos($s->jabatan, 'PPK') !== false || stripos($s->jabatan, 'Pejabat Pembuat Komitmen') !== false)) {
                    $ppk = $s;
                    break;
                }
            }
        }

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $sectionStyle = [
            'paperSize'    => 'A4',
            'marginTop'    => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft'   => Converter::cmToTwip(2.5),
            'marginRight'  => Converter::cmToTwip(2.5),
        ];

        $bold   = ['bold' => true];
        $center = ['alignment' => Jc::CENTER];

        $transportLabel = self::TRANSPORT_LABELS[strtolower((string) $travelRequest->transportation_type)] ?? strtoupper((string) $travelRequest->transportation_type);
        $tujuan = $travelRequest->destination_city
            ? $travelRequest->destination_city . ', ' . $travelRequest->destination_province
            : $travelRequest->destination_province;
        $tglBerangkat = !empty($travelRequest->departure_date) ? $this->formatTanggal($travelRequest->departure_date) : '-';
        $tglKembali   = !empty($travelRequest->return_date)    ? $this->formatTanggal($travelRequest->return_date) : '-';
        $tglSurat     = !empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : date('d F Y');
        $tempatTerbit = $travelRequest->departure_place ?: 'Palembang';

        $fontSize = ['size' => 11];

        // Filter members if specific requested
        $targetMembers = $members;
        if ($specificMemberId !== null) {
            $targetMembers = array_filter($members, function ($m) use ($specificMemberId) {
                // Check both travel_member_id (from join) or id if it's already filtered
                $mid = $m->travel_member_id ?? $m->id;
                return (int) $mid === (int) $specificMemberId;
            });
        }

        // ── PAGE 1 LOOP (Per Member) ──
        foreach ($targetMembers as $idx => $member) {
            $section = $phpWord->addSection($sectionStyle);

            // ── KOP ──
            $section->addText('SURAT PERJALANAN DINAS (SPD)', array_merge($bold, ['size' => 14, 'underline' => 'single']), $center);
            $section->addTextBreak(1);

            // ── TABLE ISIAN ──
            $tableStyle = [
                'borderSize'  => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin'  => 60,
            ];
            $phpWord->addTableStyle('SPD_' . $idx, $tableStyle);
            $table = $section->addTable('SPD_' . $idx);

            $colNo    = 400;
            $colLabel = 3800;
            $colSep   = 300;
            $colValue = 5500;

            // Row 1 — PPK
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '1',
                'Pejabat Pembuat Komitmen',
                $ppk ? $ppk->employee_name : '___________________________',
                $fontSize,
                $bold
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'NIP',
                $ppk ? ($ppk->nip ?: '-') : '___________________________',
                $fontSize
            );

            // Row 2 — Pegawai
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '2',
                'Nama Pegawai yang melaksanakan perjalanan dinas',
                $member->employee_name,
                $fontSize,
                $bold
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'NIP',
                $member->employee_nip ?: '-',
                $fontSize
            );

            // Row 3 — Golongan/Jabatan
            $pangkatGol = trim(($member->nama_golongan ?? '') . ' / ' . ($member->kode_golongan ?? ''), ' /');
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '3',
                'a. Pangkat dan Golongan',
                $pangkatGol ?: '-',
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'b. Jabatan/Instansi',
                $member->employee_jabatan ?? '-',
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'c. Tingkat Biaya Perjalanan Dinas',
                $member->tingkat_biaya ?? '-',
                $fontSize
            );

            // Row 4 — Maksud
            $maksud = $travelRequest->perihal_surat_rujukan
                ? 'Mengikuti kegiatan ' . $travelRequest->perihal_surat_rujukan
                : '-';
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '4',
                'Maksud Perjalanan Dinas',
                $maksud,
                $fontSize
            );

            // Row 5 — Alat angkutan
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '5',
                'Alat angkutan yang dipergunakan',
                $transportLabel,
                $fontSize
            );

            // Row 6 — Tempat
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '6',
                'a. Tempat Berangkat',
                $travelRequest->departure_place ?: 'Palembang',
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'b. Tempat Tujuan',
                $tujuan,
                $fontSize
            );

            // Row 7 — Waktu
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '7',
                'a. Lamanya Perjalanan Dinas',
                ($travelRequest->duration_days ?? '-') . ' Hari',
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'b. Tanggal Berangkat',
                $tglBerangkat,
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'c. Tanggal harus kembali',
                $tglKembali,
                $fontSize
            );

            // Row 8 — Pengikut
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '8',
                'Pengikut : Nama',
                '-',
                $fontSize
            );

            // Row 9 — Anggaran
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '9',
                'Pembebanan Anggaran',
                '',
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'a. Instansi',
                $travelRequest->budget_burden_by ?: 'Politeknik Negeri Sriwijaya',
                $fontSize
            );
            $this->addSubRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '',
                'b. Akun',
                $travelRequest->mak ?: '-',
                $fontSize
            );

            // Row 10 — Keterangan
            $this->addRow(
                $table,
                $colNo,
                $colLabel,
                $colSep,
                $colValue,
                '10',
                'Keterangan lain-lain',
                '-',
                $fontSize
            );

            $section->addTextBreak(1);

            // Tanda Tangan Page 1
            $phpWord->addTableStyle('SIGN_' . $idx, ['borderSize' => 0, 'borderColor' => 'FFFFFF']);
            $tableSign = $section->addTable('SIGN_' . $idx);
            $tableSign->addRow();
            $cellL = $tableSign->addCell(5000);
            $cellR = $tableSign->addCell(5000);

            $cellL->addText('Tembusan lain-lain kepada :');

            $cellR->addText('Dikeluarkan di : ' . $tempatTerbit, $fontSize, $center);
            $cellR->addText('Pada Tanggal   : ' . $tglSurat, $fontSize, $center);
            $cellR->addTextBreak(1);
            $cellR->addText('Pejabat Pembuat Komitmen,', $fontSize, $center);
            $cellR->addTextBreak(3);
            $cellR->addText($ppk ? $ppk->employee_name : '___________________________', array_merge($bold, $fontSize), $center);
            if ($ppk && ($ppk->nip ?? null)) {
                $cellR->addText('NIP. ' . $ppk->nip, $fontSize, $center);
            }
        }

        if ($showBackPage) {
            // ── PAGE 2 (Page Terakhir - Sesuai Request User) ──
            $section = $phpWord->addSection($sectionStyle);
            $tablePage2Style = [
                'borderSize'  => 6,
                'borderColor' => '000000',
                'cellMargin'  => 80,
            ];
            $phpWord->addTableStyle('PAGE_TERAKHIR_SPD', $tablePage2Style);
            $table2 = $section->addTable('PAGE_TERAKHIR_SPD');

            // Row I — Berangkat dari
            $table2->addRow();
            $cell1A = $table2->addCell(5000);
            $cell1B = $table2->addCell(5000);

            $cell1B->addText('Berangkat dari : ' . ($travelRequest->departure_place ?: 'Palembang'), $fontSize);
            $cell1B->addText('Pada Tanggal   : ' . $tglBerangkat, $fontSize);
            $cell1B->addText('Tujuan ke      : ' . $tujuan, $fontSize);
            $cell1B->addTextBreak(2);
            $cell1B->addText('(................................................)', $fontSize);

            // Dynamic Leg Rows - One per member (at least 2 for layout consistency if desired)
            $romanNumerals = ['I.', 'II.', 'III.', 'IV.', 'V.', 'VI.', 'VII.', 'VIII.', 'IX.', 'X.'];
            foreach ($targetMembers as $i => $member) {
                $this->addLegRow(
                    $table2,
                    $romanNumerals[$i] ?? (($i + 1) . '.'),
                    $fontSize,
                    $member->employee_name,
                    $member->employee_nip ?: '-'
                );
            }

            // If only 1 member, add an extra empty leg row for layout
            if (count($targetMembers) < 2) {
                $this->addLegRow($table2, 'II.', $fontSize);
            }

            // Row Final — Tiba Kembali
            $table2->addRow();
            $cell5A = $table2->addCell(5000);
            $cell5B = $table2->addCell(5000);

            $cell5A->addText('Tiba di tempat kedudukan : ' . ($travelRequest->departure_place ?: 'Palembang'), $fontSize);
            $cell5A->addText('Pada Tanggal   : ' . $tglKembali, $fontSize);
            $cell5A->addTextBreak(1);
            $cell5A->addText('PEJABAT PEMBUAT KOMITMEN', array_merge($bold, $fontSize), ['alignment' => Jc::CENTER]);
            $cell5A->addTextBreak(3);
            $cell5A->addText($ppk ? $ppk->employee_name : '(................................................)', array_merge($bold, $fontSize), ['alignment' => Jc::CENTER]);
            $cell5A->addText('NIP. ' . ($ppk ? ($ppk->nip ?: '.........................................') : '.........................................'), $fontSize, ['alignment' => Jc::CENTER]);

            $cell5B->addText('Telah diperiksa dengan keterangan bahwa perjalanan tersebut atas perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya.', $fontSize);
            $cell5B->addTextBreak(1);
            $cell5B->addText('PEJABAT PEMBUAT KOMITMEN', array_merge($bold, $fontSize), ['alignment' => Jc::CENTER]);
            $cell5B->addTextBreak(3);
            $cell5B->addText($ppk ? $ppk->employee_name : '(................................................)', array_merge($bold, $fontSize), ['alignment' => Jc::CENTER]);
            $cell5B->addText('NIP. ' . ($ppk ? ($ppk->nip ?: '.........................................') : '.........................................'), $fontSize, ['alignment' => Jc::CENTER]);

            $table2->addRow();
            $cellFooter = $table2->addCell(10000, ['gridSpan' => 2]);
            $cellFooter->addText('V. CATATAN LAIN-LAIN', $fontSize);

            $section->addTextBreak(1);
            $section->addText('VI. PERHATIAN', array_merge($bold, $fontSize));
            $section->addText('    PPK yang menerbitkan SPD, pegawai yang melaksanakan perjalanan dinas, para pejabat yang mengesahkan tanggal keberangkatan / tiba serta Bendahara pengeluaran bertanggung jawab berdasarkan peraturan-peraturan keuangan apabila Negara menderita rugi akibat kesalahan, kelalaian, dan kealfaannya.', $fontSize);
        }

        // ── STREAM ──
        $filename = 'SPD_' . $travelRequest->id . '.docx';
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '_', $filename);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }

    /**
     * Add a numbered row to the SPD table.
     */
    private function addRow($table, int $colNo, int $colLabel, int $colSep, int $colValue, string $no, string $label, string $value, array $fontSize, ?array $valueFontExtra = null): void
    {
        $table->addRow();
        $table->addCell($colNo)->addText($no, $fontSize);
        $table->addCell($colLabel)->addText($label, $fontSize);
        $table->addCell($colSep)->addText(':', $fontSize);
        $valueFont = $valueFontExtra ? array_merge($fontSize, $valueFontExtra) : $fontSize;
        $table->addCell($colValue)->addText($value, $valueFont);
    }

    /**
     * Add a sub-row (no number) to the SPD table.
     */
    private function addSubRow($table, int $colNo, int $colLabel, int $colSep, int $colValue, string $no, string $label, string $value, array $fontSize): void
    {
        $table->addRow();
        $table->addCell($colNo)->addText($no, $fontSize);
        $table->addCell($colLabel)->addText($label, $fontSize);
        $table->addCell($colSep)->addText(':', $fontSize);
        $table->addCell($colValue)->addText($value, $fontSize);
    }

    /**
     * Add a travel leg row (Tiba at/Berangkat from) to Page 2.
     */
    private function addLegRow($table, string $no, array $fontSize, ?string $name = null, ?string $nip = null): void
    {
        $table->addRow();
        $cellA = $table->addCell(5000);
        $cellB = $table->addCell(5000);

        // Left: Tiba di
        $cellA->addText($no . ' Tiba di     :', $fontSize);
        $cellA->addText('   Pada tanggal :', $fontSize);
        $cellA->addTextBreak(1);
        $cellA->addText('   Kepala       :', $fontSize);
        $cellA->addTextBreak(2);
        if ($name) {
            $cellA->addText('   (' . $name . ')', array_merge($fontSize, ['bold' => true]));
            $cellA->addText('   NIP. ' . $nip, $fontSize);
        } else {
            $cellA->addText('   (___________________________)', $fontSize);
            $cellA->addText('   NIP. ', $fontSize);
        }

        // Right: Berangkat dari
        $cellB->addText('   Berangkat dari :', $fontSize);
        $cellB->addText('   Tujuan ke      :', $fontSize);
        $cellB->addText('   Pada tanggal   :', $fontSize);
        $cellB->addText('   Kepala         :', $fontSize);
        $cellB->addTextBreak(2);
        if ($name) {
            $cellB->addText('   (' . $name . ')', array_merge($fontSize, ['bold' => true]));
            $cellB->addText('   NIP. ' . $nip, $fontSize);
        } else {
            $cellB->addText('   (___________________________)', $fontSize);
            $cellB->addText('   NIP. ', $fontSize);
        }
    }

    /**
     * Format date to Indonesian locale string.
     */
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
}
