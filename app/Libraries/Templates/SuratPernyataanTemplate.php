<?php

namespace App\Libraries\Templates;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class SuratPernyataanTemplate
{
    /**
     * Generate and stream Surat Pernyataan .docx
     *
     * @param object      $travelRequest
     * @param array       $members          Array of member objects
     * @param object|null $ppk
     * @param int|null    $specificMemberId If set, only generate for this member
     */
    public function generate(object $travelRequest, array $members, ?object $ppk = null, ?int $specificMemberId = null, ?string $customDate = null): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $sectionStyle = [
            'paperSize'    => 'A4',
            'marginTop'    => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(1.5),
            'marginLeft'   => Converter::cmToTwip(2.5),
            'marginRight'  => Converter::cmToTwip(2.5),
        ];

        // ── FONT STYLES ───────────────────────────────────────────────────────
        $tnr      = ['name' => 'Times New Roman'];
        $tnrBold  = ['name' => 'Times New Roman', 'bold' => true];
        $tnr10    = ['name' => 'Times New Roman', 'size' => 10];
        $tnr10url = ['name' => 'Times New Roman', 'size' => 10, 'color' => '0000FF'];

        // ── PARAGRAPH STYLES ──────────────────────────────────────────────────
        // line 360 = spasi 1,5 (240 = single, 480 = double)
        $spacing15 = ['line' => 360, 'lineRule' => 'auto'];

        $center   = ['alignment' => Jc::CENTER];
        $left15   = $spacing15;
        $center15 = array_merge(['alignment' => Jc::CENTER], $spacing15);
        $justify  = array_merge(['alignment' => Jc::BOTH],   $spacing15);

        // ── CELL TANPA BORDER ─────────────────────────────────────────────────
        $noBorder = [
            'borderTopSize'    => 0, 'borderTopColor'    => 'FFFFFF',
            'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF',
            'borderLeftSize'   => 0, 'borderLeftColor'   => 'FFFFFF',
            'borderRightSize'  => 0, 'borderRightColor'  => 'FFFFFF',
        ];

        // Filter members jika diminta spesifik
        $targetMembers = $members;
        if ($specificMemberId !== null) {
            $targetMembers = array_filter($members, fn($m) => (int) $m->travel_member_id === (int) $specificMemberId);
        }

        foreach ($targetMembers as $idx => $member) {
            $section = $phpWord->addSection($sectionStyle);

            // ── HEADER / KOP ──────────────────────────────────────────────────
            // Logo cell diperkecil (1500 → dari 2000) agar teks kop lebih ke tengah
            $headerTable = $section->addTable([
                'borderSize'  => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin'  => 0,
            ]);
            $headerTable->addRow();

            // Kolom kiri: Logo
            $logoPath = FCPATH . 'img/logo-polsri-bnw.jpg';
            if (file_exists($logoPath)) {
                $headerTable->addCell(1500, $noBorder)
                    ->addImage($logoPath, ['width' => 60, 'height' => 60, 'alignment' => Jc::CENTER]);
            } else {
                $headerTable->addCell(1500, $noBorder);
            }

            // Kolom kanan: Teks kop (diperlebar 8500 agar lebih center ke halaman)
            $textCell = $headerTable->addCell(8500, $noBorder);
            $textCell->addText('KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,', $tnr, $center);
            $textCell->addText('RISET, DAN TEKNOLOGI', $tnr, $center);
            $textCell->addText('POLITEKNIK NEGERI SRIWIJAYA', $tnrBold, $center);
            $textCell->addText('Jalan Srijaya Negara Bukit Besar – Palembang 30139', $tnr10, $center);
            $textCell->addText('Telp. 0711-353414 Fax. 0711-355918', $tnr10, $center);
            $textCell->addLink(
                'http://polsri.ac.id',
                'Laman : http://polsri.ac.id',
                $tnr10url,
                $center
            );

            // ── GARIS PEMBATAS KOP ────────────────────────────────────────────
            $section->addText('', null, [
                'borderBottomSize'  => 12,        // ~1.5 pt (satuan 1/8 pt)
                'borderBottomColor' => '000000',
                'spaceBefore'       => 0,
                'spaceAfter'        => 0,
            ]);

            $section->addTextBreak(1);

            // ── JUDUL ─────────────────────────────────────────────────────────
            $section->addText('SURAT PERNYATAAN', array_merge($tnrBold, ['underline' => 'single']), $center15);
            $section->addTextBreak(1);

            // ── PEMBUKA ───────────────────────────────────────────────────────
            $section->addText('Yang bertanda tangan di bawah ini :', $tnr, $left15);
            $section->addTextBreak(1);

            // ── DATA PEGAWAI ──────────────────────────────────────────────────
            $dataTable = $section->addTable([
                'borderSize'  => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin'  => 0,
            ]);

            $this->addSimpleRow($dataTable, 'Nama',    $member->employee_name,            $tnr, $tnrBold);
            $this->addSimpleRow($dataTable, 'NIP',     $member->employee_nip      ?: '-', $tnr, $tnrBold);
            $this->addSimpleRow($dataTable, 'Jabatan', $member->employee_jabatan   ?: '-', $tnr, $tnrBold);

            $section->addTextBreak(1);

            // ── ISI / KONTEN ──────────────────────────────────────────────────
            $tglSuratTugas = !empty($travelRequest->tgl_surat_tugas)
                ? $this->formatTanggal($travelRequest->tgl_surat_tugas)
                : '-';
            $noSuratTugas = $travelRequest->no_surat_tugas ?: '__________________';

            $contentPara = $section->addTextRun($justify);
            $contentPara->addText('Berdasarkan Surat Tugas tanggal ', $tnr);
            $contentPara->addText($tglSuratTugas . ' ', $tnr);
            $contentPara->addText('Nomor: ', $tnr);
            $contentPara->addText($noSuratTugas . ' ', $tnr);
            $contentPara->addText('dengan ini kami menyatakan dengan sesungguhnya bahwa :', $tnr);

            $section->addTextBreak(1);

            // ── POIN-POIN PERNYATAAN ──────────────────────────────────────────
            // Numbering unik per member agar angka selalu mulai dari 1
            $listStyleName = 'list_member_' . $idx;
            $phpWord->addNumberingStyle($listStyleName, [
                'type'   => 'multilevel',
                'levels' => [[
                    'format'  => 'decimal',
                    'text'    => '%1.',
                    'left'    => 360,
                    'hanging' => 360,
                    'tabPos'  => 360,
                ]],
            ]);

            $section->addListItem(
                'Bukti-bukti (Tiket / bukti transportasi, Boarding Pass, Kwitansi, Hotel bill / tagihan hotel '
                . 'dan sebagainya) yang dilampirkan dalam rangka melakukan perjalanan dinas adalah bukti-bukti '
                . 'asli dan benar yang dikeluarkan oleh perusahaan / instansi yang berwenang untuk menerbitkan '
                . 'bukti-bukti tersebut.',
                0, $tnr, $listStyleName, $justify
            );

            $section->addTextBreak(1);

            $section->addListItem(
                'Apabila dikemudian hari terdapat kesalahan atau temuan dari aparat pengawasan fungsional, '
                . 'kami bersedia untuk mempertanggungjawabkannya.',
                0, $tnr, $listStyleName, $justify
            );

            $section->addTextBreak(1);

            $section->addText(
                'Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipertanggungjawabkan sebagaimana mestinya.',
                $tnr,
                $justify
            );

            $section->addTextBreak(1);

            // ── TANDA TANGAN ──────────────────────────────────────────────────
            $signTable = $section->addTable([
                'borderSize'  => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin'  => 0,
            ]);
            $signTable->addRow();

            $cellLeft  = $signTable->addCell(5000, $noBorder);
            $cellRight = $signTable->addCell(5000, $noBorder);

            // Kiri: PPK
            $cellLeft->addText('Mengetahui/Menyetujui', $tnr, $left15);
            $cellLeft->addText('an. Kuasa Pengguna Anggaran', $tnr, $left15);
            $cellLeft->addText('Pejabat Pembuat Komitmen', $tnr, $left15);
            $cellLeft->addTextBreak(3);
            $cellLeft->addText($ppk ? $ppk->employee_name : '___________________________', $tnrBold, $left15);
            $cellLeft->addText('NIP. ' . ($ppk ? ($ppk->nip ?: '-') : '___________________________'), $tnr, $left15);

            // Kanan: Pelaksana
            $tempatTerbit   = $travelRequest->departure_place ?: 'Palembang';
            $tglTandaTangan = !empty($customDate) 
                ? $this->formatTanggal($customDate)
                : (!empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : date('d F Y'));

            $cellRight->addText($tempatTerbit . ', ' . $tglTandaTangan, $tnr, $left15);
            $cellRight->addText('Yang melakukan Perjalanan Dinas,', $tnr, $left15);
            $cellRight->addTextBreak(4);
            $cellRight->addText($member->employee_name, $tnrBold, $left15);
            $cellRight->addText('NIP. ' . ($member->employee_nip ?: '-'), $tnr, $left15);

            // ── FOOTER ────────────────────────────────────────────────────────
            $footer  = $section->addFooter();
            $isoPath = FCPATH . 'img/iso.png';
            if (file_exists($isoPath)) {
                $footer->addImage($isoPath, ['width' => 80, 'height' => 40]);
            }
        }

        // ── STREAM KE BROWSER ─────────────────────────────────────────────────
        $filename = 'Surat_Pernyataan_' . $travelRequest->id . '.docx';
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '_', $filename);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function addSimpleRow($table, string $label, string $value, ?array $labelFont = null, ?array $valueFont = null): void
    {
        $table->addRow();
        $table->addCell(1500)->addText($label, $labelFont);
        $table->addCell(300)->addText(':', $labelFont);
        $table->addCell(6000)->addText($value, $valueFont);
    }

    private function formatTanggal(string $date): string
    {
        $months = [
            1 => 'Januari',   2 => 'Februari', 3 => 'Maret',    4 => 'April',
            5 => 'Mei',       6 => 'Juni',      7 => 'Juli',     8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $ts = strtotime($date);
        return date('d', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}