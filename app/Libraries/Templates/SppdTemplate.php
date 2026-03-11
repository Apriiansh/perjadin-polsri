<?php

namespace App\Libraries\Templates;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class SppdTemplate
{
    /**
     * Generate and stream SPPD .docx
     */
    public function generate(object $travelRequest, array $members, ?object $ppk = null, ?object $kpa = null): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'marginTop'    => Converter::cmToTwip(3),
            'marginBottom' => Converter::cmToTwip(2.5),
            'marginLeft'   => Converter::cmToTwip(4),
            'marginRight'  => Converter::cmToTwip(3),
        ]);

        $bold   = ['bold' => true];
        $center = ['alignment' => Jc::CENTER];

        // ── KOP SURAT ──
        $section->addText('KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI', array_merge($bold, ['size' => 12]), $center);
        $section->addText('POLITEKNIK NEGERI SRIWIJAYA', array_merge($bold, ['size' => 14]), $center);
        $section->addText('Jl. Srijaya Negara, Bukit Besar, Palembang 30139', ['size' => 10], $center);
        $section->addText('Telepon: (0711) 353414, Fax.: (0711) 355918', ['size' => 10], $center);
        $section->addLine(['weight' => 2, 'width' => Converter::cmToEmu(14), 'height' => 0, 'color' => '000000']);
        $section->addTextBreak(1);

        // ── JUDUL ──
        $section->addText('SURAT PERINTAH PERJALANAN DINAS (SPPD)', array_merge($bold, ['size' => 14]), $center);
        // Use first member's no_sppd as document number (all members share same request)
        $sppdNo = '___/___/___/___';
        if (!empty($members) && !empty($members[0]->no_sppd)) {
            $sppdNo = $members[0]->no_sppd;
        }
        $section->addText('Nomor: ' . $sppdNo, ['size' => 12], $center);
        $section->addTextBreak(1);

        // ── TABEL ANGGOTA ──
        $tabelStyle = ['borderSize' => 6, 'borderColor' => '000000', 'unit' => 'pct', 'width' => 5000];
        $cellHdrStyle = ['bgColor' => 'D9D9D9'];
        $table = $section->addTable($tabelStyle);

        $tableHeaders = ['No', 'Nama / NIP', 'Pangkat/Gol', 'Jabatan', 'Tujuan', 'Tgl Berangkat', 'Tgl Kembali', 'Lama (Hari)'];
        $table->addRow();
        foreach ($tableHeaders as $header) {
            $cell = $table->addCell(null, $cellHdrStyle);
            $cell->addText($header, $bold, $center);
        }

        $no = 1;
        $dest = $travelRequest->destination_province . ($travelRequest->destination_city ? ', ' . $travelRequest->destination_city : '');
        foreach ($members as $member) {
            $table->addRow();
            $table->addCell(300)->addText((string) $no++, ['size' => 11], $center);
            $nameCell = $table->addCell(2000);
            $nameCell->addText($member->employee_name, ['size' => 11, 'bold' => true]);
            $nameCell->addText('NIP. ' . ($member->employee_nip ?? '-'), ['size' => 10]);
            $table->addCell(800)->addText($member->employee_golongan ?? '-', ['size' => 11], $center);
            $table->addCell(1200)->addText($member->jabatan ?? '-', ['size' => 11]);
            $table->addCell(1200)->addText($dest, ['size' => 11]);
            $table->addCell(900)->addText(date('d/m/Y', strtotime($travelRequest->departure_date)), ['size' => 11], $center);
            $table->addCell(900)->addText(date('d/m/Y', strtotime($travelRequest->return_date)), ['size' => 11], $center);
            $table->addCell(500)->addText((string) $travelRequest->duration_days, ['size' => 11], $center);
        }

        $section->addTextBreak(1);

        // ── KETERANGAN ──
        $detailRows = [
            ['Keperluan', $travelRequest->perihal_surat_rujukan ?: '-'],
            ['Transportasi', strtoupper((string) $travelRequest->transportation_type)],
            ['Beban Anggaran', $travelRequest->budget_burden_by ?: 'DIPA Polsri'],
            ['MAK', $travelRequest->mak ?: '-'],
        ];
        foreach ($detailRows as [$label, $value]) {
            $run = $section->addTextRun(['alignment' => Jc::BOTH]);
            $run->addText(str_pad($label, 18), $bold);
            $run->addText(': ' . $value);
        }

        $section->addTextBreak(2);

        // ── TANDA TANGAN ──
        $tglSurat = date('d F Y');
        if (!empty($members) && !empty($members[0]->tgl_sppd)) {
            $tglSurat = date('d F Y', strtotime($members[0]->tgl_sppd));
        }

        $tableSign = $section->addTable(['unit' => 'pct', 'width' => 5000]);
        $tableSign->addRow();
        $cellL = $tableSign->addCell(5000);
        $cellR = $tableSign->addCell(5000);

        if ($ppk) {
            $cellL->addText('Yang Memerintahkan,', ['size' => 12], 'center');
            $cellL->addText($ppk->jabatan ?? 'PPK', ['size' => 12], 'center');
            $cellL->addTextBreak(3);
            $cellL->addText($ppk->employee_name, $bold, 'center');
            $cellL->addText('NIP. ' . ($ppk->nip ?? ''), ['size' => 11], 'center');
        }

        $cellR->addText('Palembang, ' . $tglSurat, ['size' => 12], 'center');
        $kpaJab = $kpa ? ($kpa->jabatan ?? 'Direktur') : 'Direktur';
        $cellR->addText($kpaJab, ['size' => 12], 'center');
        $cellR->addTextBreak(3);
        $namaKpa = $kpa ? $kpa->employee_name : '___________________________';
        $nipKpa  = $kpa ? ($kpa->nip ?? '') : '';
        $cellR->addText($namaKpa, $bold, 'center');
        if ($nipKpa) {
            $cellR->addText('NIP. ' . $nipKpa, ['size' => 11], 'center');
        }

        // ── STREAM ──
        $filename = 'SPPD_' . $travelRequest->id . '.docx';
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '_', $filename);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }
}
