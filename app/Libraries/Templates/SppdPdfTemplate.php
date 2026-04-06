<?php

namespace App\Libraries\Templates;

use Dompdf\Dompdf;
use Dompdf\Options;

class SppdPdfTemplate
{
    private const TRANSPORT_LABELS = [
        'udara' => 'Pesawat',
        'darat' => 'Mobil',
        'laut'  => 'Kapal',
    ];

    /**
     * Generate and stream SPD .pdf
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

        // 2. Filter members
        $targetMembers = $members;
        if ($specificMemberId !== null) {
            $targetMembers = array_filter($members, function ($m) use ($specificMemberId) {
                $mid = $m->travel_member_id ?? $m->id;
                return (int) $mid === (int) $specificMemberId;
            });
        }

        // 3. Prepare Member Data (Enrich with level)
        foreach ($targetMembers as $member) {
            $member->tingkatBiaya = $this->resolveTingkat($member);
            $member->employee_name = $member->employee_name ?? '';
            $member->employee_nip = $member->employee_nip ?? '';
            $member->employee_jabatan = $member->employee_jabatan ?? '';
        }

        // 4. Global Variables
        $transportLabel = self::TRANSPORT_LABELS[strtolower((string) $travelRequest->transportation_type)] ?? strtoupper((string) $travelRequest->transportation_type);
        $tglBerangkat   = !empty($travelRequest->departure_date) ? $this->formatTanggal($travelRequest->departure_date) : '-';
        $tglKembali     = !empty($travelRequest->return_date)    ? $this->formatTanggal($travelRequest->return_date) : '-';
        $tglSurat       = !empty($travelRequest->tgl_surat_tugas) ? $this->formatTanggal($travelRequest->tgl_surat_tugas) : date('d F Y');

        // 5. Prepare Data for View
        $data = [
            'travelRequest'   => $travelRequest,
            'members'         => $targetMembers,
            'ppk'             => $ppk,
            'transportLabel'  => $transportLabel,
            'tglBerangkat'    => $tglBerangkat,
            'tglKembali'      => $tglKembali,
            'tglSurat'        => $tglSurat,
            'showBackPage'    => $showBackPage,
        ];

        // ── RENDER HTML ──
        $html = view('travel/pdf/sppd_pdf', $data);

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
        $filename = 'SPD_' . $travelRequest->id . '.pdf';
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
