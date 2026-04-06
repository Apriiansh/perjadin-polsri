<?php

namespace App\Libraries\Templates;

use Dompdf\Dompdf;
use Dompdf\Options;

class SuratPernyataanPdfTemplate
{
    /**
     * Generate and stream Surat Pernyataan .pdf
     *
     * @param object      $travelRequest
     * @param array       $members          Array of member objects
     * @param object|null $ppk
     * @param int|null    $specificMemberId If set, only generate for this member
     */
    public function generate(object $travelRequest, array $members, ?object $ppk = null, ?int $specificMemberId = null): void
    {
        // Filter members
        $targetMembers = $members;
        if ($specificMemberId !== null) {
            $targetMembers = array_filter($members, fn($m) => (int) $m->travel_member_id === (int) $specificMemberId);
        }

        // Prepare Data for View
        $data = [
            'travelRequest'   => $travelRequest,
            'members'         => $targetMembers,
            'ppk'             => $ppk,
            'tglSuratTugas'   => !empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : '-',
            'tglTandaTangan' => !empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : date('d F Y'),
        ];

        // ── RENDER HTML ──
        $html = view('travel/pdf/surat_pernyataan_pdf', $data);

        // ── DOMPDF OPTIONS ──
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Times New Roman');

        // ── GENERATE PDF ──
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // ── STREAM OUTPUT ──
        $filename = 'Surat_Pernyataan_' . $travelRequest->id . '.pdf';
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '_', $filename);

        $dompdf->stream($filename, ['Attachment' => 1]);
        exit;
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
}
