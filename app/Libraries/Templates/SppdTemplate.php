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
     * Generate and stream SPD .docx — one page per member.
     */
    public function generate(object $travelRequest, array $members, ?object $ppk = null): void
    {
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

        foreach ($members as $idx => $member) {
            // Each member gets a new section (= new page)
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

            $colNo    = 400;   // No column
            $colLabel = 3800;  // Label column
            $colSep   = 300;   // ":" separator
            $colValue = 5500;  // Value column

            $fontSize = ['size' => 11];

            // Row 1 — Pejabat Pembuat Komitmen
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '1',
                'Pejabat Pembuat Komitmen',
                $ppk ? $ppk->employee_name : '___________________________',
                $fontSize, $bold
            );
            // NIP PPK
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'NIP',
                $ppk ? ($ppk->nip ?: '-') : '___________________________',
                $fontSize
            );

            // Row 2 — Nama/NIP Pegawai
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '2',
                'Nama Pegawai yang melaksanakan perjalanan dinas',
                $member->employee_name,
                $fontSize, $bold
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'NIP',
                $member->employee_nip ?: '-',
                $fontSize
            );

            // Row 3 — Pangkat, Golongan, Jabatan, Tingkat Biaya
            $pangkatGol = trim(($member->nama_golongan ?? '') . ' / ' . ($member->kode_golongan ?? ''), ' /');
            $jabatan    = $member->employee_jabatan ?? '-';
            $tingkat    = $member->tingkat_biaya ?? '-';

            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '3',
                'a. Pangkat dan Golongan',
                $pangkatGol ?: '-',
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'b. Jabatan/Instansi',
                $jabatan,
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'c. Tingkat Biaya Perjalanan Dinas',
                $tingkat,
                $fontSize
            );

            // Row 4 — Maksud Perjalanan Dinas
            $maksud = $travelRequest->perihal_surat_rujukan
                ? 'Mengikuti kegiatan ' . $travelRequest->perihal_surat_rujukan
                : '-';
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '4',
                'Maksud Perjalanan Dinas',
                $maksud,
                $fontSize
            );

            // Row 5 — Alat angkutan
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '5',
                'Alat angkutan yang dipergunakan',
                $transportLabel,
                $fontSize
            );

            // Row 6 — Tempat Berangkat & Tujuan
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '6',
                'a. Tempat Berangkat',
                $travelRequest->departure_place ?: 'Palembang',
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'b. Tempat Tujuan',
                $tujuan,
                $fontSize
            );

            // Row 7 — Waktu Perjalanan
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '7',
                'a. Lamanya Perjalanan Dinas',
                ($travelRequest->duration_days ?? '-') . ' Hari',
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'b. Tanggal Berangkat',
                $tglBerangkat,
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'c. Tanggal harus kembali',
                $tglKembali,
                $fontSize
            );

            // Row 8 — Pengikut
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '8',
                'Pengikut : Nama',
                '-',
                $fontSize
            );

            // Row 9 — Pembebanan Anggaran
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '9',
                'Pembebanan Anggaran',
                '',
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'a. Instansi',
                $travelRequest->budget_burden_by ?: 'Politeknik Negeri Sriwijaya',
                $fontSize
            );
            $this->addSubRow($table, $colNo, $colLabel, $colSep, $colValue, '',
                'b. Akun',
                $travelRequest->mak ?: '-',
                $fontSize
            );

            // Row 10 — Keterangan lain-lain
            $this->addRow($table, $colNo, $colLabel, $colSep, $colValue, '10',
                'Keterangan lain-lain',
                '-',
                $fontSize
            );

            $section->addTextBreak(1);

            // ── TANDA TANGAN ──
            $phpWord->addTableStyle('SIGN_' . $idx, ['borderSize' => 0, 'borderColor' => 'FFFFFF']);
            $tableSign = $section->addTable('SIGN_' . $idx);
            $tableSign->addRow();
            $cellL = $tableSign->addCell(5000);
            $cellR = $tableSign->addCell(5000);

            // Left cell — empty or future use
            $cellL->addText('Tembusan lain-lain kepada :');

            // Right cell — PPK signature
            $cellR->addText('Dikeluarkan di : ' . $tempatTerbit, $fontSize, $center);
            $cellR->addText('Pada Tanggal   : ' . $tglSurat, $fontSize, $center);
            $cellR->addTextBreak(1);
            $cellR->addText('Pejabat Pembuat Komitmen,', $fontSize, $center);
            $cellR->addTextBreak(3);
            $cellR->addText($ppk ? $ppk->employee_name : '___________________________', array_merge($bold, $fontSize), $center);
            if ($ppk && $ppk->nip) {
                $cellR->addText('NIP. ' . $ppk->nip, $fontSize, $center);
            }
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
     * Format date to Indonesian locale string.
     */
    private function formatTanggal(string $date): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $ts = strtotime($date);
        return date('d', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}
